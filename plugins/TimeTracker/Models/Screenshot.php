<?php

namespace Plugins\TimeTracker\Models;

use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    protected $fillable = [
        'user_id',
        'screenshot_path',
        'captured_at',
        'filename', // Store original filename
        'file_size', // Store file size in bytes
        'metadata', // Store additional metadata as JSON
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'metadata' => 'array', // Automatically cast metadata to array
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    /**
     * Get the URL of the screenshot image.
     * @return string
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->screenshot_path);
    }
    /**
     * Get the formatted captured date.
     * @return string
     */
}
