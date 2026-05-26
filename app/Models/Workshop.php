<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'starts_at',
        'location',
        'location_link',
        'max_seats',
        'is_active',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'starts_at' => 'datetime',
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Check if the workshop is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get the registrations for the workshop.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
