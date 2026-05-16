<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_config_id',
        'registration_id',
        'payload',
        'response_status',
        'response_body',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the webhook configuration that this log belongs to.
     */
    public function webhookConfig(): BelongsTo
    {
        return $this->belongsTo(WebhookConfig::class);
    }

    /**
     * Get the registration that this log belongs to.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
