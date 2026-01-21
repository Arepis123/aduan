<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'emails',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'emails' => 'array',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
