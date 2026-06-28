<?php

namespace RoyalPanel\RoyalAtelier\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RoyalPanel\RoyalAtelier\Models\RxExtension;

class RxRemoveExtensionCommand extends Command
{
    protected $signature = 'rx:remove-ext {id : Extension ID to remove}';
    protected $description = 'Remove a Royal Extension and all its hooks';

    public function handle(): int
    {
        $id = $this->argument('id');

        if (!RxExtension::where('extension_id', $id)->where('installed', true)->exists()) {
            $this->error("Extension {$id} not found");
            return Command::FAILURE;
        }

        $this->info("Removing extension {$id}...");

        // Remove React component symlinks
        $reactTarget = base_path("resources/scripts/blueprint/extensions/{$id}");
        if (File::exists($reactTarget)) File::deleteDirectory($reactTarget);

        // Remove route symlinks
        foreach (['web', 'client', 'application'] as $type) {
            $routeFile = base_path("routes/blueprint/{$type}/{$id}.php");
            if (File::exists($routeFile)) File::delete($routeFile);
        }

        // Remove blade wrappers
        foreach (['dashboard', 'admin'] as $type) {
            $wrapperFile = base_path("resources/views/blueprint/{$type}/wrappers/{$id}.blade.php");
            if (File::exists($wrapperFile)) File::delete($wrapperFile);
        }

        // Remove extension directory
        $extPath = base_path(".rx/extensions/{$id}");
        if (File::exists($extPath)) File::deleteDirectory($extPath);

        // Clean React placeholders (remove imports/components from placeholder files)
        $this->cleanPlaceholders($id);

        // Clean navigation routes
        $this->cleanNavigationRoutes($id);

        // Update database
        RxExtension::where('extension_id', $id)->update(['installed' => false, 'enabled' => false]);

        $this->info("Extension {$id} removed");
        return Command::SUCCESS;
    }

    private function cleanPlaceholders(string $id): void
    {
        $placeholderFiles = File::glob(base_path('resources/scripts/blueprint/components/**/*.tsx'));
        $componentName = str_replace('-', '', ucwords($id, '-'));

        foreach ($placeholderFiles as $file) {
            $content = File::get($file);
            $original = $content;

            $content = preg_replace(
                "/import {$componentName} from '@blueprint\/extensions\/{$id}\/[^']+';/",
                '',
                $content
            );
            $content = preg_replace(
                "/\s*<{$componentName} \/>/",
                '',
                $content
            );

            if ($content !== $original) {
                File::put($file, $content);
            }
        }
    }

    private function cleanNavigationRoutes(string $id): void
    {
        $routesFile = base_path('resources/scripts/blueprint/extends/routers/routes.ts');
        if (!File::exists($routesFile)) return;

        $content = File::get($routesFile);
        $original = $content;
        $componentName = str_replace('-', '', ucwords($id, '-'));

        $content = preg_replace(
            "/import {$componentName}Route from '@blueprint\/extensions\/{$id}\/[^']+';\n/",
            '',
            $content
        );
        $content = preg_replace(
            "/.*\b{$componentName}Route\b.*\n/",
            '',
            $content
        );

        if ($content !== $original) {
            File::put($routesFile, $content);
        }
    }
}
