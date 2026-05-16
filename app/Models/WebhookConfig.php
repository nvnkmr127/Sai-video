<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'url',
        'secret_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the webhook logs for this configuration.
     */
    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }
}
