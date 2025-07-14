<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class PluginHelper
{
    /**
     * Get all plugins installed in the system.
     */
    public static function all()
    {
        $plugins = [];

        $pluginsPath = base_path('plugins');
        if (!File::exists($pluginsPath)) {
            return $plugins;
        }

        foreach (File::directories($pluginsPath) as $dir) {
            $json = $dir . '/plugin.json';
            if (File::exists($json)) {
                $data = json_decode(File::get($json), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("❌ Invalid JSON in plugin.json: {$json}");
                    continue;
                }
                $data['slug'] = basename($dir);
                $data['path'] = $dir;
                $plugins[] = $data;
            }
        }

        return $plugins;
    }

    /**
     * Retrieve a single plugin by slug.
     */
    public static function get(string $slug): ?array
    {
        $path = base_path("plugins/{$slug}");
        $json = "{$path}/plugin.json";
        if (!File::exists($json)) {
            return null;
        }

        $data = json_decode(File::get($json), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("❌ Invalid JSON in plugin.json: {$json}");
            return null;
        }
        $data['slug'] = $slug;
        $data['path'] = $path;
        return $data;
    }

    /**
     * Update enabled/disabled status in plugin.json.
     */
    public static function updateStatus(string $slug, bool $enabled): void
    {
        $plugin = self::get($slug);
        if (!$plugin) {
            throw new Exception("Plugin not found: {$slug}");
        }

        $pluginJsonPath = "{$plugin['path']}/plugin.json";
        $plugin['enabled'] = $enabled;

        File::put($pluginJsonPath, json_encode($plugin, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        Log::info("🔄 Updated plugin status: {$slug} => " . ($enabled ? "enabled" : "disabled"));
    }

    /**
     * Delete the plugin safely.
     */
    public static function delete(string $slug): bool
    {
        $plugin = self::get($slug);
        if (!$plugin) {
            throw new Exception("Plugin not found: {$slug}");
        }

        if (!empty($plugin['enabled'])) {
            throw new Exception("Cannot uninstall plugin while enabled. Please disable it first.");
        }

        $deleted = File::deleteDirectory($plugin['path']);

        if ($deleted) {
            Log::info("🗑️ Plugin deleted: {$slug}");
        } else {
            Log::error("❌ Failed to delete plugin: {$slug}");
        }

        return $deleted;
    }

    public static function getPluginLabels()
    {
        $pluginLabels = [];

        $pluginDirs = File::directories(base_path('plugins'));

        foreach ($pluginDirs as $pluginDir) {
            $labelFile = $pluginDir . '/Resources/lang/plugin_labels.php';

            if (File::exists($labelFile)) {
                $labels = include $labelFile;
                if (is_array($labels)) {
                    $pluginLabels = array_merge($pluginLabels, $labels);
                }
            }
        }

        // ksort($pluginLabels);

        return $pluginLabels;
    }
}
