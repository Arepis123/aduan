<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'emails',
        'is_active',
        'department_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'emails' => 'array',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
