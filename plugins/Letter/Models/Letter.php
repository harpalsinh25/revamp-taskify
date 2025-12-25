<?php

namespace Plugins\Letter\Models;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'template_id',
        'title',
        'content',
        'letter_date',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'letter_date' => 'date',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function template()
    {
        return $this->belongsTo(LetterTemplate::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
