<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\User;
use App\Models\Status;
use App\Models\Priority;
use App\Models\Setting;
use App\Models\Language;
use Illuminate\Support\Facades\View;
use App\Models\Client;
use App\Models\CustomField;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use App\Services\CustomPathGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PathGenerator::class, CustomPathGenerator::class);
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();
        // Attach data to modal views
        $this->composeModalViews();
        // Load database configurations and settings
        $this->loadDatabaseSettings();
    }
    /**
     * Attach data to specific views.
     */
    private function composeModalViews()
    {
        View::composer('modals', function ($view) {
            $view->with('toSelectWorkspaceUsers', User::select('id', 'first_name', 'last_name')->get())
                ->with('toSelectWorkspaceClients', Client::select('id', 'first_name', 'last_name')->get());
        });
    }
    /**
     * Load configurations and share global data from the database.
     */
    private function loadDatabaseSettings()
    {
        try {
            DB::connection()->getPdo();
            // General Settings
            $generalSettings = $this->loadGeneralSettings();
            // Pusher, Email, and Media Storage Settings
            $pusherSettings = $this->loadPusherSettings();
            $emailSettings = $this->loadEmailSettings();
            $mediaStorageSettings = $this->loadMediaStorageSettings();
            $google_calendar_settings = $this->loadGoogleCalendarSettings();
            // Other Settings
            $dateFormats = $this->parseDateFormats($generalSettings['date_format'] ?? 'DD-MM-YYYY|d-m-Y');
            $customFields = $this->loadCustomFields();
            $sharedData = [
                'general_settings' => $generalSettings,
                'email_settings' => $emailSettings,
                'pusher_settings' => $pusherSettings,
                'media_storage_settings' => $mediaStorageSettings,
                'languages' => Language::all(),
                'statuses' => Status::all(),
                'tags' => Tag::all(),
                'priorities' => Priority::all(),
                'js_date_format' => $dateFormats['js'],
                'php_date_format' => $dateFormats['php'],
                'taskCustomFields' => $customFields['task'],
                'projectCustomFields' => $customFields['project'],
                'google_calendar_settings' => $google_calendar_settings,
                'company_info' => $this->loadCompanyInfo(),
                'ai_model_settings' => $this->loadAIModels(),
            ];
            // Share data globally with all views
            view()->share($sharedData);
            // Configure application defaults
            $this->configureAppDefaults($generalSettings, $pusherSettings, $emailSettings, $mediaStorageSettings);
        } catch (\Exception $e) {
            // Handle exceptions silently or log them if needed
        }
    }
    /**
     * Load general settings and apply defaults.
     */
    private function loadGeneralSettings()
    {
        $general_settings = get_settings('general_settings') ?? [];
        $defaults = [
            'full_logo' => 'storage/logos/default_full_logo.png',
            'half_logo' => 'storage/logos/default_half_logo.png',
            'favicon' => 'storage/logos/default_favicon.png',
            'footer_logo' => 'storage/logos/footer_logo.png',
            'company_title' => 'Taskify - SaaS',
            'currency_symbol' => '₹',
            'currency_full_form' => 'Indian Rupee',
            'currency_code' => 'INR',
            'date_format' => 'DD-MM-YYYY|d-m-Y',
            'toast_time_out' => '5',
            'toast_position' => 'toast-top-right',
            'allowed_file_types' => '.png,.jpg,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt',
            'max_files_allowed' => '10',
            'allowed_max_upload_size' => '512',
        ];
        foreach ($defaults as $key => $value) {
            if (!isset($general_settings[$key]) || empty($general_settings[$key])) {
                $general_settings[$key] = $value;
            } elseif (in_array($key, ['full_logo', 'half_logo', 'favicon', 'footer_logo'])) {
                $general_settings[$key] = 'storage/' . $general_settings[$key];
            }
        }
        return $general_settings;
    }
    /**
     * Load Pusher settings and apply defaults.
     */
    private function loadPusherSettings()
    {
        return get_settings('pusher_settings') ?? [
            'pusher_app_id' => '',
            'pusher_app_key' => '',
            'pusher_app_secret' => '',
            'pusher_app_cluster' => '',
        ];
    }
    /**
     * Load email settings and apply defaults.
     */
    private function loadEmailSettings()
    {
        return get_settings('email_settings') ?? [
            'email' => '',
            'password' => '',
            'smtp_host' => '',
            'smtp_port' => '',
            'email_content_type' => '',
            'smtp_encryption' => '',
        ];
    }
    /**
     * Load media storage settings and apply defaults.
     */
    private function loadMediaStorageSettings()
    {
        return get_settings('media_storage_settings') ?? [
            'media_storage_type' => '',
            's3_key' => '',
            's3_secret' => '',
            's3_region' => '',
            's3_bucket' => '',
        ];
    }
    /**
     * Parse date formats into JS and PHP formats.
     */
    private function parseDateFormats($dateFormat)
    {
        $formats = explode('|', $dateFormat);
        return [
            'js' => $formats[0] ?? 'DD-MM-YYYY',
            'php' => $formats[1] ?? 'd-m-Y',
        ];
    }
    /**
     * Load custom fields for tasks and projects.
     */
    private function loadCustomFields()
    {
        return [
            'task' => CustomField::where('module', 'task')->get(),
            'project' => CustomField::where('module', 'project')->get(),
        ];
    }
    /**
     * Load company information and apply defaults.
     */
    private function loadCompanyInfo()
    {
        return array_merge([
            'companyEmail' => '',
            'companyPhone' => '',
            'companyAddress' => '',
            'companyCity' => '',
            'companyState' => '',
            'companyCountry' => '',
            'companyZip' => '',
            'companyWebsite' => '',
            'companyVatNumber' => '',
        ], get_settings('company_information') ?? []);
    }
    /**
     * Configure application defaults based on settings.
     */
    private function configureAppDefaults($generalSettings, $pusherSettings, $emailSettings, $mediaStorageSettings)
    {
        config()->set('app.timezone', $generalSettings['timezone'] ?? 'UTC');
        config()->set('media-library.max_file_size', 1024 * 1024 * $generalSettings['allowed_max_upload_size']);
        // Pusher
        config()->set('chatify.pusher', [
            'key' => $pusherSettings['pusher_app_key'],
            'secret' => $pusherSettings['pusher_app_secret'],
            'app_id' => $pusherSettings['pusher_app_id'],
            'options' => ['cluster' => $pusherSettings['pusher_app_cluster']],
        ]);
        // Mail
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp', [
            'host' => $emailSettings['smtp_host'],
            'port' => $emailSettings['smtp_port'],
            'transport' => 'smtp',
            'encryption' => $emailSettings['smtp_encryption'],
            'username' => $emailSettings['email'],
            'password' => $emailSettings['password'],
        ]);
        config()->set('mail.from', [
            'name' => $generalSettings['company_title'],
            'address' => $emailSettings['email'],
        ]);
        // Filesystem
        config()->set('filesystems.disks.s3', [
            'key' => $mediaStorageSettings['s3_key'],
            'secret' => $mediaStorageSettings['s3_secret'],
            'region' => $mediaStorageSettings['s3_region'],
            'bucket' => $mediaStorageSettings['s3_bucket'],
        ]);
    }
    /**
     * Load AI Models and apply defaults.
     */
    private function loadAIModels()
    {
        $ai_model_settings = get_settings('ai_model_settings') ?? [];
        return array_merge([
            "openrouter_endpoint" => "https://openrouter.ai/api/v1/chat/completions",
            "openrouter_system_prompt" => "You are a helpful assistant that writes concise, professional project or task descriptions.",
            "openrouter_temperature" => "0.7",
            "openrouter_max_tokens" => "1024",
            "openrouter_top_p" => "0.95",
            "gemini_endpoint" => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent",
            "gemini_temperature" => "0.7",
            "gemini_top_k" => "40",
            "gemini_top_p" => "0.95",
            "gemini_max_output_tokens" => "1024",
            "rate_limit_per_minute" => "15",
            "rate_limit_per_day" => "1500",
            "max_retries" => "2",
            "retry_delay" => "1",
            "request_timeout" => "15",
            "max_prompt_length" => "1000",
            "enable_fallback" => "1",
            "fallback_provider" => "openrouter",
            "openrouter_api_key" => "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",
            "gemini_api_key" => "AIzaSyDkFbg7NJIRGw66VGLI6ZHupFSNOOrc9xc",
            "is_active" => "gemini",
            "openrouter_model" => "nousresearch/deephermes-3-mistral-24b-preview:free",
            "openrouter_frequency_penalty" => "0",
            "openrouter_presence_penalty" => "0",
            "gemini_model" => "gemini-2.0-flash",
        ], $ai_model_settings);
    }
    /**
     * Load Google Calendar Settings and apply defaults.
     */
    private function loadGoogleCalendarSettings()
    {
        $google_calendar_settings = get_settings('google_calendar_settings') ?? [];
        return array_merge([
            'api_key' => '',
            'calendar_id' => '',
            'calendar_name' => '',
        ], $google_calendar_settings);
    }
}
