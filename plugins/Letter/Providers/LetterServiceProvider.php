<?php

namespace Plugins\Letter\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LetterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'letters');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'letter');
        $this->publishes([
            __DIR__ . '/../public/js' => public_path('assets/js/letter-plugin'),
        ], ['letter-assets', 'public']);

        // Optional logging for plugin version on load
        if (file_exists(__DIR__ . '/../plugin.json')) {
            $pluginJson = json_decode(file_get_contents(__DIR__ . '/../plugin.json'), true);
            Log::info("✅ Letter Plugin Loaded - Version: " . ($pluginJson['version'] ?? 'unknown'));
        }

        // Attach plugin's scheduled task cleanly
    }

    public function register(): void
    {

    }
}
