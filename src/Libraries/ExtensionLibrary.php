<?php

namespace RoyalPanel\RoyalAtelier\Libraries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RoyalPanel\RoyalAtelier\Models\RxExtension;

class ExtensionLibrary
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = base_path('.rx/extensions');
    }

    public function dbGet(string $table, string $key, mixed $default = null): mixed
    {
        $row = DB::table('rx_settings')->where('key', "{$table}::{$key}")->first();
        if (!$row) return $default;
        try { return unserialize($row->value); } catch (\Exception) { return $row->value; }
    }

    public function dbSet(string $table, string $key, mixed $value): void
    {
        $fullKey = "{$table}::{$key}";
        DB::table('rx_settings')->updateOrInsert(
            ['key' => $fullKey],
            ['value' => serialize($value), 'updated_at' => now()]
        );
    }

    public function dbGetMany(string $table, array $keys = []): array
    {
        if (empty($keys)) {
            $rows = DB::table('rx_settings')->where('key', 'like', "{$table}::%")->get();
        } else {
            $rows = DB::table('rx_settings')
                ->whereIn('key', array_map(fn($k) => "{$table}::{$k}", $keys))
                ->get();
        }
        $result = [];
        foreach ($rows as $row) {
            $localKey = str_replace("{$table}::", '', $row->key);
            try { $result[$localKey] = unserialize($row->value); } catch (\Exception) { $result[$localKey] = $row->value; }
        }
        return $result;
    }

    public function dbSetMany(string $table, array $data): void
    {
        foreach ($data as $key => $value) {
            $this->dbSet($table, $key, $value);
        }
    }

    public function extensions(bool $onlyInstalled = true): array
    {
        $query = RxExtension::query();
        if ($onlyInstalled) $query->where('installed', true);
        return $query->pluck('extension_id')->toArray();
    }

    public function extensionConfig(?string $id = null): ?array
    {
        if (!$id) return null;
        $ext = RxExtension::where('extension_id', $id)->first();
        return $ext ? $ext->toArray() : null;
    }

    public function extensionPath(?string $id = null): string
    {
        return $id ? "{$this->basePath}/{$id}" : $this->basePath;
    }

    public function extensionsConfigs(): array
    {
        $configs = [];
        foreach ($this->extensions() as $id) {
            $config = $this->extensionConfig($id);
            if ($config) $configs[$id] = $config;
        }
        return $configs;
    }

    public function install(string $packagePath): array
    {
        if (!File::exists($packagePath)) {
            return ['success' => false, 'error' => 'Package file not found'];
        }

        $zip = new \ZipArchive();
        if ($zip->open($packagePath) !== true) {
            return ['success' => false, 'error' => 'Invalid package file'];
        }

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        if (!$manifest || !isset($manifest['id'])) {
            $zip->close();
            return ['success' => false, 'error' => 'Invalid manifest.json'];
        }

        $id = $manifest['id'];
        $targetPath = "{$this->basePath}/{$id}";

        if (File::exists($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        $zip->extractTo($targetPath);
        $zip->close();

        $existing = RxExtension::where('extension_id', $id)->first();
        if ($existing) {
            $existing->update([
                'version' => $manifest['version'] ?? '1.0.0',
                'name' => $manifest['name'] ?? $id,
                'author' => $manifest['author'] ?? null,
                'description' => $manifest['description'] ?? null,
                'icon' => $manifest['icon'] ?? null,
                'website' => $manifest['website'] ?? null,
                'installed' => true,
                'enabled' => true,
            ]);
        } else {
            RxExtension::create([
                'extension_id' => $id,
                'name' => $manifest['name'] ?? $id,
                'version' => $manifest['version'] ?? '1.0.0',
                'author' => $manifest['author'] ?? null,
                'description' => $manifest['description'] ?? null,
                'icon' => $manifest['icon'] ?? null,
                'website' => $manifest['website'] ?? null,
                'installed' => true,
                'enabled' => true,
            ]);
        }

        return ['success' => true, 'id' => $id];
    }

    public function uninstall(string $id): array
    {
        $targetPath = "{$this->basePath}/{$id}";
        if (File::exists($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        RxExtension::where('extension_id', $id)->update(['installed' => false, 'enabled' => false]);
        return ['success' => true];
    }

    public function importStylesheet(string $url): string
    {
        return '<link rel="stylesheet" href="' . e($url) . '">';
    }
}
