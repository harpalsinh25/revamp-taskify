<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\PluginHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PluginManagerController extends Controller
{
    public function index()
    {
        return view('plugins.index', [
            'plugins' => PluginHelper::all(),
        ]);
    }

    public function enable($slug)
    {
        try {
            $plugin = PluginHelper::get($slug);
            if (!$plugin) {
                throw new Exception("Plugin not found: {$slug}");
            }

            // Update status
            PluginHelper::updateStatus($slug, true);

            // Optionally re-publish plugin assets if publish_tag exists
            if (!empty($plugin['publish_tag'])) {
                Artisan::call('vendor:publish', [
                    '--tag' => $plugin['publish_tag'],
                    '--force' => true,
                ]);
                Log::info("✅ Published assets for plugin: {$slug}");
            }

            Log::info("✅ Plugin enabled: {$slug}");

            return formatApiResponse(false, "Plugin enabled successfully: {$slug}", ['slug' => $slug]);
        } catch (Exception $e) {
            Log::error("❌ Error enabling plugin {$slug}: " . $e->getMessage());
            return formatApiResponse(true, "Error enabling plugin {$slug}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    public function disable($slug)
    {
        try {
            $plugin = PluginHelper::get($slug);
            if (!$plugin) {
                throw new Exception("Plugin not found: {$slug}");
            }

            PluginHelper::updateStatus($slug, false);

            Log::info("🔌 Plugin disabled: {$slug}");

            return formatApiResponse(false, "Plugin disabled successfully: {$slug}", ['slug' => $slug]);
        } catch (Exception $e) {
            Log::error("❌ Error disabling plugin {$slug}: " . $e->getMessage());
            return formatApiResponse(true, "Error disabling plugin {$slug}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    public function uninstall($slug)
    {
        try {
            $plugin = PluginHelper::get($slug);
            if (!$plugin) {
                throw new Exception("Plugin not found: {$slug}");
            }

            PluginHelper::delete($slug);

            Log::info("🗑️ Plugin uninstalled: {$slug}");

            return formatApiResponse(false, "Plugin uninstalled successfully: {$slug}", ['slug' => $slug]);
        } catch (Exception $e) {
            Log::error("❌ Error uninstalling plugin {$slug}: " . $e->getMessage());
            return formatApiResponse(true, "Error uninstalling plugin {$slug}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
