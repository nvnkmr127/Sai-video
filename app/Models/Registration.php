<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'full_name',
        'phone',
        'address',
        'organization',
        'qr_code_token',
        'qr_code_path',
        'webhook_sent_at',
        'checked_in_at',
        'checked_in_by',
    ];

    protected $casts = [
        'webhook_sent_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Get the workshop that this registration belongs to.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get the webhook logs for this registration.
     */
    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Scope a query to only include checked-in registrations.
     */
    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->whereNotNull('checked_in_at');
    }

    /**
     * Scope a query to only include pending registrations (not checked in).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('checked_in_at');
    }
}
