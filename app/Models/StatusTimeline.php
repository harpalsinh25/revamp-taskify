<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTimeline extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = ['entity_id', 'entity_type', 'status', 'previous_status', 'new_color', 'old_color', 'changed_at'];
    public function entity()
    {
        return $this->morphTo();
    }
}
