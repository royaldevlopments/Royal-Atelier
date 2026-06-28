<?php

namespace RoyalPanel\RoyalAtelier\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RoyalPanel\RoyalAtelier\Models\RxExtension;
use RoyalPanel\RoyalAtelier\Libraries\ExtensionLibrary;
use Symfony\Component\Yaml\Yaml;

class RxInstallExtensionCommand extends Command
{
    protected $signature = 'rx:install-ext {package : Path to .blueprint package file}';
    protected $description = 'Install a Royal Extension package with full lifecycle';

    private string $basePath;
    private string $extPath;
    private string $tmpPath;
    private array $config;
    private string $identifier;
    private string $tmpExtPath;

    public function handle(ExtensionLibrary $library): int
    {
        $packageArg = $this->argument('package');
        $this->basePath = base_path();
        $this->extPath = base_path('.rx/extensions');
        $this->tmpPath = base_path('.rx/tmp');

        // Clean tmp
        if (File::exists($this->tmpPath)) File::deleteDirectory($this->tmpPath);

        // Handle developer build mode
        if ($packageArg === '[developer-build]') {
            $devPath = base_path('.rx/dev');
            if (!File::exists($devPath)) {
                $this->error('Development directory not found');
                return Command::FAILURE;
            }
            File::copyDirectory($devPath, $this->tmpPath);
            $this->identifier = 'dev';
        } else {
            // Extract .blueprint package
            $packagePath = $packageArg;
            if (!str_ends_with($packagePath, '.blueprint')) {
                $packagePath .= '.blueprint';
            }
            if (!File::exists($packagePath)) {
                $this->error("Package not found: {$packagePath}");
                return Command::FAILURE;
            }

            $zip = new \ZipArchive();
            if ($zip->open($packagePath) !== true) {
                $this->error('Invalid .blueprint package');
                return Command::FAILURE;
            }
            $zip->extractTo($this->tmpPath);
            $zip->close();

            // Extract identifier from filename
            $this->identifier = pathinfo(basename($packageArg), PATHINFO_FILENAME);
            if (str_ends_with($this->identifier, '.blueprint')) {
                $this->identifier = substr($this->identifier, 0, -10);
            }
        }

        // Parse conf.yml
        $confFile = "{$this->tmpPath}/conf.yml";
        if (!File::exists($confFile)) {
            $this->error('conf.yml not found');
            File::deleteDirectory($this->tmpPath);
            return Command::FAILURE;
        }

        $this->config = Yaml::parseFile($confFile);
        $this->identifier = $this->config['identifier'] ?? $this->identifier;
        $name = $this->config['name'] ?? $this->identifier;
        $version = $this->config['version'] ?? '1.0.0';

        $this->info("Installing {$name} ({$this->identifier}) v{$version}...");

        // Create extension directory
        $this->tmpExtPath = "{$this->tmpPath}/extension";
        if (!File::exists($this->tmpExtPath)) {
            $this->tmpExtPath = $this->tmpPath;
        }

        // Process placeholders in extension files
        $this->replacePlaceholders($this->tmpExtPath);

        // Copy to permanent location
        $permPath = "{$this->extPath}/{$this->identifier}";
        if (File::exists($permPath)) File::deleteDirectory($permPath);
        File::ensureDirectoryExists(dirname($permPath));
        File::copyDirectory($this->tmpExtPath, $permPath);

        // React components
        $this->installReactComponents($permPath);

        // Routes
        $this->installRoutes($permPath);

        // Blade wrappers
        $this->installWrappers($permPath, 'dashboard');
        $this->installWrappers($permPath, 'admin');

        // Admin UI hooks
        $this->installAdminHooks($permPath);

        // Run migrations
        $this->runMigrations($permPath);

        // Run post-install command
        $this->runPostInstallCommand($permPath);

        // Update database
        RxExtension::updateOrCreate(
            ['extension_id' => $this->identifier],
            [
                'name' => $name,
                'version' => $version,
                'author' => $this->config['author'] ?? null,
                'description' => $this->config['description'] ?? null,
                'icon' => $this->config['icon'] ?? null,
                'website' => $this->config['website'] ?? null,
                'installed' => true,
                'enabled' => true,
            ]
        );

        // Rebuild panel assets
        $this->info('Rebuilding panel assets...');
        chdir($this->basePath);
        passthru('node node_modules/webpack/bin/webpack.js --mode production 2>&1', $buildResult);

        // Clean up
        File::deleteDirectory($this->tmpPath);

        if ($buildResult !== 0) {
            $this->warn('Webpack build completed with warnings');
        }

        $this->info("Extension {$name} installed successfully");
        return Command::SUCCESS;
    }

    private function replacePlaceholders(string $path): void
    {
        $files = File::allFiles($path);
        $identifier = $this->config['identifier'] ?? $this->identifier;
        $name = $this->config['name'] ?? $identifier;
        $version = $this->config['version'] ?? '1.0.0';

        $replacements = [
            '{identifier}' => $identifier,
            '{name}' => $name,
            '{version}' => $version,
            '{author}' => $this->config['author'] ?? '',
            '{description}' => $this->config['description'] ?? '',
            '{root}' => $this->basePath,
            '{engine}' => 'rx',
            '{viewcontext}' => 'rx',
            '{appcontext}' => $this->basePath,
            '!{identifier}' => '{identifier}',
            '!{name}' => '{name}',
            '!{version}' => '{version}',
        ];

        foreach ($files as $file) {
            if (!in_array($file->getExtension(), ['php', 'ts', 'tsx', 'js', 'jsx', 'blade.php', 'yml', 'yaml', 'json', 'css', 'scss'])) {
                continue;
            }
            $content = File::get($file->getPathname());
            $original = $content;

            foreach ($replacements as $search => $replace) {
                $content = str_replace($search, $replace, $content);
            }

            // Handle conditional blocks: {is_target}...{/is_target}
            $content = preg_replace('/\{is_target\}(.*?)\{\/is_target\}/s', '$1', $content);
            $content = preg_replace('/\{is_not_target\}(.*?)\{\/is_not_target\}/s', '', $content);

            if ($content !== $original) {
                File::put($file->getPathname(), $content);
            }
        }
    }

    private function installReactComponents(string $permPath): void
    {
        $componentsSource = "{$permPath}/components";
        if (!File::exists($componentsSource)) return;

        $componentsTarget = base_path("resources/scripts/blueprint/extensions/{$this->identifier}");
        if (File::exists($componentsTarget)) File::deleteDirectory($componentsTarget);
        File::ensureDirectoryExists(dirname($componentsTarget));
        File::copyDirectory($componentsSource, $componentsTarget);

        // Get component mapping from config
        $components = $this->config['Components']['dashboard_components'] ?? $this->config['dashboard_components'] ?? [];
        if (empty($components) && File::exists("{$permPath}/Components.yml")) {
            $components = Yaml::parseFile("{$permPath}/Components.yml");
        }

        // Inject into placeholder files
        $this->injectReactComponents($components, $permPath);
    }

    private function injectReactComponents(array $components, string $permPath): void
    {
        $componentFiles = File::glob(base_path('resources/scripts/blueprint/components/**/*.tsx'));

        // Build component mapping from config
        $mapping = [];
        foreach ($components as $section => $hooks) {
            foreach ($hooks as $hookPoint => $componentFile) {
                $path = str_replace('.', '/', $hookPoint) . '.tsx';
                $mapping[$path] = $componentFile;
            }
        }

        foreach ($mapping as $placeholderPath => $componentFile) {
            $placeholderFullPath = base_path("resources/scripts/blueprint/components/{$placeholderPath}");
            if (!File::exists($placeholderFullPath)) continue;

            $content = File::get($placeholderFullPath);
            $componentName = $this->identifier . 'Component';
            $componentName = str_replace('-', '', ucwords($this->identifier, '-'));

            // Remove old imports for this extension
            $content = preg_replace(
                "/import {$componentName} from '@blueprint\/extensions\/{$this->identifier}\/[^']+';/",
                '',
                $content
            );
            $content = str_replace("<{$componentName} />", '', $content);

            // Add new import and component
            $componentImport = $componentFile;
            if (File::exists("{$permPath}/components/{$componentFile}") || File::exists(base_path("resources/scripts/blueprint/extensions/{$this->identifier}/{$componentFile}"))) {
                $content = preg_replace(
                    '/\/\* blueprint\/import \*\//',
                    "/* blueprint/import */import {$componentName} from '@blueprint/extensions/{$this->identifier}/{$componentImport}';",
                    $content,
                    1
                );
                $content = preg_replace(
                    '/\{\/\* blueprint\/react \*\/\}/',
                    "{/* blueprint/react */}<{$componentName} />",
                    $content,
                    1
                );
            }

            File::put($placeholderFullPath, $content);
        }

        // Handle navigation routes
        $routesConfig = $components['Navigation']['Routes'] ?? [];
        if (!empty($routesConfig)) {
            $this->injectNavigationRoutes($routesConfig, $permPath);
        }
    }

    private function injectNavigationRoutes(array $routes, string $permPath): void
    {
        $routesFile = base_path('resources/scripts/blueprint/extends/routers/routes.ts');
        if (!File::exists($routesFile)) return;

        $content = File::get($routesFile);
        $componentName = str_replace('-', '', ucwords($this->identifier, '-'));
        $routeImport = $this->identifier;

        foreach ($routes as $type => $routeDefs) {
            if ($type === 'account' || $type === 'server') {
                foreach ($routeDefs as $def) {
                    $importStatement = "import {$componentName}Route from '@blueprint/extensions/{$this->identifier}/{$def['component']}';";
                    $routeDefinition = $this->buildRouteDefinition($def, $componentName);

                    $content = preg_replace(
                        '/\/\* blueprint\/import \*\//',
                        "/* blueprint/import */{$importStatement}",
                        $content,
                        1
                    );

                    $marker = "/* routes/{$type} */";
                    $content = str_replace(
                        $marker,
                        "{$routeDefinition},\n    {$marker}",
                        $content
                    );
                }
            }
        }

        File::put($routesFile, $content);
    }

    private function buildRouteDefinition(array $def, string $componentName): string
    {
        $path = $def['path'] ?? '/';
        $name = isset($def['name']) ? "'{$def['name']}'" : 'undefined';
        $permission = $def['permission'] ?? null;
        $adminOnly = isset($def['adminOnly']) && $def['adminOnly'] ? 'true' : 'false';

        if ($permission) {
            if (is_array($permission)) {
                $permStr = '[' . implode(', ', array_map(fn($p) => "'{$p}'", $permission)) . ']';
            } else {
                $permStr = "'{$permission}'";
            }
            return "  {{$componentName}Route} as ServerRouteDefinition";
        }

        return "  { path: '{$path}', name: {$name}, component: {$componentName}Route, adminOnly: {$adminOnly}, identifier: '{$this->identifier}' }";
    }

    private function installRoutes(string $permPath): void
    {
        $routeTypes = [
            'web' => 'routes/blueprint/web',
            'client' => 'routes/blueprint/client',
            'application' => 'routes/blueprint/application',
        ];

        $routerFiles = [
            'web' => "{$permPath}/routers/web.php",
            'client' => "{$permPath}/routers/client.php",
            'application' => "{$permPath}/routers/application.php",
        ];

        foreach ($routeTypes as $type => $targetDir) {
            $sourceFile = $routerFiles[$type];
            if (!File::exists($sourceFile)) continue;

            $symlinkTarget = base_path("{$targetDir}/{$this->identifier}.php");
            File::ensureDirectoryExists(dirname($symlinkTarget));

            // Remove old symlink if exists
            if (File::exists($symlinkTarget)) File::delete($symlinkTarget);

            // Create symlink
            symlink($sourceFile, $symlinkTarget);
        }
    }

    private function installWrappers(string $permPath, string $type): void
    {
        $source = "{$permPath}/wrappers/{$type}.blade.php";
        if (!File::exists($source)) return;

        $targetDir = base_path("resources/views/blueprint/{$type}/wrappers");
        File::ensureDirectoryExists($targetDir);

        $target = "{$targetDir}/{$this->identifier}.blade.php";
        if (File::exists($target)) File::delete($target);

        File::copy($source, $target);
    }

    private function installAdminHooks(string $permPath): void
    {
        // Admin CSS
        $cssSource = "{$permPath}/assets/admin.css";
        if (File::exists($cssSource)) {
            $cssTarget = public_path("rx-assets/css/{$this->identifier}.css");
            File::ensureDirectoryExists(dirname($cssTarget));
            File::copy($cssSource, $cssTarget);
        }

        // Public assets
        $publicSource = "{$permPath}/assets/public";
        if (File::exists($publicSource)) {
            $publicTarget = public_path("rx-assets/extensions/{$this->identifier}");
            if (File::exists($publicTarget)) File::deleteDirectory($publicTarget);
            File::copyDirectory($publicSource, $publicTarget);
        }

        // Console commands
        $commandsSource = "{$permPath}/commands";
        if (File::exists($commandsSource)) {
            $commandsTarget = base_path(".rx/extensions/{$this->identifier}/commands");
            File::ensureDirectoryExists(dirname($commandsTarget));
            if (File::exists($commandsTarget)) File::deleteDirectory($commandsTarget);
            File::copyDirectory($commandsSource, $commandsTarget);
        }
    }

    private function runMigrations(string $permPath): void
    {
        $migrationsDir = "{$permPath}/database/migrations";
        if (!File::exists($migrationsDir)) return;

        foreach (File::files($migrationsDir) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = File::get($file->getPathname());

            // Wrap in a temporary migration class and run
            $tempFile = database_path("migrations/temp_rx_{$this->identifier}_{$file->getBasename()}");
            File::put($tempFile, $content);

            $this->call('migrate', ['--force' => true, '--path' => str_replace(base_path(), '', $tempFile)]);

            File::delete($tempFile);
        }
    }

    private function runPostInstallCommand(string $permPath): void
    {
        $command = $this->config['install']['command'] ?? null;
        if (!$command) return;

        $this->info("Running post-install command: {$command}");
        chdir($this->basePath);
        passthru($command, $result);
    }
}
