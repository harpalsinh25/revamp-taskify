<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LeadSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
    ];
    protected static function booted(): void
    {
        static::addGlobalScope('defaultOnly', function (Builder $builder) {
            $builder->where('is_default', true)->whereNull('workspace_id');
        });
    }
    public function leads()
    {
        return $this->hasMany(Lead::class, 'source_id');
    }
}
