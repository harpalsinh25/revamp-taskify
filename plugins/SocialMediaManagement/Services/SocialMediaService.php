<?php
namespace Plugins\SocialMediaManagement\Services;

use Carbon\Carbon;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Plugins\SocialMediaManagement\Models\SocialPost;
use Plugins\SocialMediaManagement\Services\SocialMedia\FacebookService;
use Plugins\SocialMediaManagement\Services\SocialMedia\LinkedInService;
use Plugins\SocialMediaManagement\Services\SocialMedia\InstagramService;
use Plugins\SocialMediaManagement\Services\SocialMedia\PinterestService;

class SocialMediaService
{
    protected $socialSettings;
    protected $platformServices = [];

    public function __construct()
    {
        $settings = Setting::where('variable', 'social_settings')->first();
        $this->socialSettings = $settings ? json_decode($settings->value, true) : [];

        $this->initializePlatformServices();
    }

    private function initializePlatformServices()
    {
        $this->platformServices = [
            'facebook' => new FacebookService($this->socialSettings),
            'instagram' => new InstagramService($this->socialSettings),
            'linkedin' => new LinkedInService($this->socialSettings),
            'pinterest' => new PinterestService($this->socialSettings),
        ];
    }

    // In App/Services/SocialMediaService.php
    public function publishPost(SocialPost $post)
    {
        Log::info("=== STARTING PUBLISH POST ===", [
            'post_id' => $post->id,
            'platforms' => $post->platforms
        ]);

        $responses = [];
        $hasSuccess = false;
        $hasFailure = false;

        foreach ($post->platforms as $platform) {
            try {
                $response = $this->publishToPlatform($platform, $post);
                $responses[$platform] = $response;

                if ($response['success'] === true) {
                    $hasSuccess = true;
                } else {
                    $hasFailure = true;
                }
            } catch (\Exception $e) {
                $responses[$platform] = [
                    'success' => false,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'error_code' => 'SERVICE_EXCEPTION',
                    'failed_at' => now()->toISOString(),
                    'retry_count' => 0,
                    'exception' => true
                ];
                $hasFailure = true;
            }
        }

        // Determine final status
        $finalStatus = 'failed';
        if ($hasSuccess && !$hasFailure) {
            $finalStatus = 'published';
        } elseif ($hasSuccess && $hasFailure) {
            $finalStatus = 'partially_published';
        }

        // STORE RESPONSE_LOGS HERE
        $post->update([
            'response_logs' => $responses,  // Store detailed logs
            'status' => $finalStatus,
            'published_at' => $hasSuccess ? now() : null
        ]);

        return $responses;
    }

    // Alternative more robust version if you want to preserve line breaks:
    private function cleanCaption($caption)
    {
        if (empty($caption)) {
            return $caption;
        }

        // First, convert some HTML tags to text equivalents
        $caption = str_replace(['<br>', '<br/>', '<br />'], "\n", $caption);
        $caption = str_replace('</p>', "\n\n", $caption);

        // Strip remaining HTML tags
        $caption = strip_tags($caption);

        // Decode HTML entities
        $caption = html_entity_decode($caption, ENT_QUOTES, 'UTF-8');

        // Clean up whitespace but preserve intentional line breaks
        $caption = preg_replace('/[ \t]+/', ' ', $caption); // Convert multiple spaces/tabs to single space
        $caption = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $caption); // Convert multiple newlines to double newline
        $caption = trim($caption);

        return $caption;
    }

    protected function publishToPlatform($platform, SocialPost $post)
    {
        $mediaFiles = $post->getMedia('social-media');
        if ($mediaFiles->isEmpty()) {
            throw new \Exception("No media files found for post ID {$post->id}");
        }

        // Clean caption before publishing
        $cleanPost = clone $post;
        $cleanPost->caption = $this->cleanCaption($post->caption);


        // Check if platform service exists
        if (!isset($this->platformServices[$platform])) {
            throw new \Exception("Unsupported platform: {$platform}");
        }

        $service = $this->platformServices[$platform];

        // Validate platform settings
        if (!$service->validateSettings()) {
            throw new \Exception("{$platform} credentials are missing or invalid");
        }

        return $service->publish($cleanPost, $mediaFiles);
    }

    public function verifyCredentials($platform)
    {
        try {
            if (!isset($this->platformServices[$platform])) {
                return false;
            }

            return $this->platformServices[$platform]->verifyCredentials();
        } catch (\Exception $e) {
            Log::error("Error verifying {$platform} credentials: " . $e->getMessage());
            return false;
        }
    }
}
