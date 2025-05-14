<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadStage extends Model
{
    use HasFactory;
    protected $fillable = [
        'workspace_id',
        'name',
        'order',
        'color'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('defaultOnly', function (Builder $builder) {
            $builder->where('is_default', true)->whereNull('workspace_id');
        });
    }
    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }
}
