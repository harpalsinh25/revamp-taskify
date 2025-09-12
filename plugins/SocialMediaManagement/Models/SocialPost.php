<?php

namespace Plugins\SocialMediaManagement\Models;

use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialPost extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'caption',
        'platforms',
        'scheduled_at',
        'status',
        'response_logs'
    ];

    protected $casts = [
        'platforms' => 'array',
        'scheduled_at' => 'datetime',
        'response_logs'=> 'array',
    ];


    public function registerMediaCollections(): void
    {
        $media_storage_settings = get_settings('media_storage_settings');
        $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';

        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('social-media')->useDisk('s3');
        } else {
            $this->addMediaCollection('social-media')->useDisk('public');
        }
    }


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getMediaFiles(){
        return $this->getMedia('social-media');
    }

    public function getPlatformStatusAttribute(){
        if(!$this->response_logs){
            return [];
        }

        $status = [];

        foreach($this->platforms as $platform){
            $status[$platform] = $this->response_logs[$platform]['status'] ?? 'pending';
        }
        return $status;
    }


    /**
     * Get platforms that were successfully published to
     */
    public function getSuccessfulPlatforms()
    {
        if (!$this->response_logs) {
            return [];
        }

        return array_keys(array_filter($this->response_logs, function ($log) {
            return isset($log['success']) && $log['success'] === true;
        }));
    }

    /**
     * Get platforms that failed to publish
     */
    public function getFailedPlatforms()
    {
        if (!$this->response_logs) {
            return [];
        }

        return array_keys(array_filter($this->response_logs, function ($log) {
            return isset($log['success']) && $log['success'] === false;
        }));
    }


    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Set default status when creating
        static::creating(function ($post) {
            if (!$post->status) {
                $post->status = 'pending';
            }
        });

        // Log status changes
        static::updating(function ($post) {
            if ($post->isDirty('status')) {
                Log::info('Post status changed', [
                    'post_id' => $post->id,
                    'old_status' => $post->getOriginal('status'),
                    'new_status' => $post->status,
                    'user_id' => auth()->id()
                ]);
            }
        });
    }
}
