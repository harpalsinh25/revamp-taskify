<?php

namespace App\Http\Controllers;

use Exception;
use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class PluginInstallerController extends Controller
{
    public function showForm()
    {
        return view('plugins.install');
    }

    public function install(Request $request)
    {
        $request->validate([
            'plugin_zip.*' => 'required|file|mimes:zip',
        ]);

        $uploadedZip = $request->file('plugin_zip')[0];
        $tmpDir = storage_path('app/tmp_plugin_' . uniqid());

        File::ensureDirectoryExists($tmpDir);

        $zip = new ZipArchive;
        if ($zip->open($uploadedZip->getRealPath()) !== true) {
            return response()->json(['error' => true, 'message' => 'Unable to open zip file.']);
        }

        $zip->extractTo($tmpDir);
        $zip->close();

        $pluginFolder = collect(File::directories($tmpDir))->first();
        if (!$pluginFolder || !File::exists($pluginFolder . '/plugin.json')) {
            File::deleteDirectory($tmpDir);
            return response()->json(['error' => true, 'message' => 'plugin.json not found in the uploaded plugin.']);
        }

        $pluginData = json_decode(File::get($pluginFolder . '/plugin.json'), true);
        $pluginSlug = $pluginData['slug'] ?? basename($pluginFolder);
        $pluginNamespace = str_replace('-', '', ucwords($pluginSlug, '-'));
        $destination = base_path('plugins/' . $pluginNamespace);

        try {
            DB::beginTransaction();

            File::ensureDirectoryExists(base_path('plugins'));

            if (File::exists($destination)) {
                $existingJson = json_decode(File::get($destination . '/plugin.json'), true);
                if (version_compare($pluginData['version'], $existingJson['version'], '<=')) {
                    File::deleteDirectory($tmpDir);
                    return response()->json([
                        'error' => true,
                        'message' => 'Plugin already installed with the same or higher version.'
                    ]);
                }

                $backupDir = storage_path('app/plugin_backups/' . $pluginNamespace . '_' . now()->format('YmdHis'));
                File::ensureDirectoryExists(dirname($backupDir));
                File::moveDirectory($destination, $backupDir);
                Log::info("🔄 Plugin backed up to: {$backupDir}");
            }

            if (!File::moveDirectory($pluginFolder, $destination)) {
                throw new Exception("Failed to move plugin to: {$destination}");
            }
            Log::info("✅ Plugin moved to: {$destination}");

            $provider = $pluginData['provider'] ?? "Plugins\\{$pluginNamespace}\\Providers\\{$pluginNamespace}ServiceProvider";

            $providerPath = $destination . '/Providers/' . class_basename(str_replace('\\', '/', $provider)) . '.php';
            if (File::exists($providerPath)) {
                require_once $providerPath;
            }

            if (class_exists($provider)) {
                app()->register($provider);
                Log::info("✅ Service provider registered: {$provider}");
            } else {
                throw new Exception("Provider class '{$provider}' not found.");
            }

            $migrationPath = "plugins/{$pluginNamespace}/Database/Migrations";
            if (File::exists(base_path($migrationPath))) {
                Artisan::call('migrate', [
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
                Log::info("✅ Migrations executed for plugin: {$pluginNamespace}");
            }

            $updateScript = base_path("plugins/{$pluginNamespace}/update.php");
            if (File::exists($updateScript)) {
                include_once $updateScript;
                Log::info("✅ update.php executed for plugin: {$pluginNamespace}");
            }

            DB::commit();

            File::deleteDirectory($tmpDir);

            if (!empty($pluginData['publish_tag'])) {
                Artisan::call('vendor:publish', [
                    '--tag' => $pluginData['publish_tag'],
                    '--force' => true,
                ]);
                Log::info("✅ Published plugin assets with tag: {$pluginData['publish_tag']}");
            } else {
                Log::info("ℹ️ No publish_tag defined in plugin.json, skipping vendor:publish.");
            }

            Log::info("✅ Plugin '{$pluginNamespace}' installed/updated successfully.");

            return response()->json(['error' => false, 'message' => 'Plugin installed/updated successfully.']);
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("❌ Plugin installation failed: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            File::deleteDirectory($tmpDir);
            return response()->json(['error' => true, 'message' => 'Plugin installation failed: ' . $e->getMessage()]);
        }
    }
}
