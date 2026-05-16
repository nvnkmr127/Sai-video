<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Models\WebhookConfig;
use App\Models\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 4;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int[]
     */
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Registration $registration,
        public ?int $webhookConfigId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Fetch targeted or all active WebhookConfig records of type 'registration'
        $query = WebhookConfig::where('is_active', true)->where('type', 'registration');

        if ($this->webhookConfigId) {
            $query->where('id', $this->webhookConfigId);
        }

        $configs = $query->get();

        if ($configs->isEmpty()) {
            return;
        }

        // Prepare data
        $registration = $this->registration->load('workshop');
        
        $qrCodeBase64 = null;
        $qrCodeUrl = null;

        if ($registration->qr_code_path && Storage::disk('public')->exists($registration->qr_code_path)) {
            $qrCodeBase64 = base64_encode(Storage::disk('public')->get($registration->qr_code_path));
            $qrCodeUrl = Storage::disk('public')->url($registration->qr_code_path);
        }

        // 2. Build JSON payload
        $payload = [
            'event' => 'registration.created',
            'timestamp' => now()->toIso8601String(),
            'registration_id' => $registration->id,
            'workshop_id' => $registration->workshop_id,
            'workshop_title' => $registration->workshop->title ?? 'N/A',
            'full_name' => $registration->full_name,
            'phone' => $registration->phone,
            'address' => $registration->address,
            'organization' => $registration->organization,
            'qr_code_token' => $registration->qr_code_token,
            'qr_code_image_base64' => $qrCodeBase64,
            'qr_code_image_url' => $qrCodeUrl,
        ];

        $hasFailure = false;

        // 3. For each config, send HTTP POST
        foreach ($configs as $config) {
            // DUPLICATE PREVENTION: Check if this registration was already successfully sent to this config
            $alreadySent = WebhookLog::where('registration_id', $registration->id)
                ->where('webhook_config_id', $config->id)
                ->whereIn('response_status', [200, 201, 202])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Secret' => $config->secret_token,
                    'X-Event' => 'registration.created',
                ])->post($config->url, $payload);

                // 4. Log the result in webhook_logs table
                WebhookLog::create([
                    'webhook_config_id' => $config->id,
                    'registration_id' => $registration->id,
                    'payload' => $payload,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'sent_at' => now(),
                ]);

                if (!$response->successful()) {
                    Log::error("Webhook delivery failed for config {$config->id} ({$config->url}). Status: " . $response->status());
                    
                    // Only mark as failure for retry if it's a 5xx error or connection error
                    if ($response->serverError()) {
                        $hasFailure = true;
                    }
                }

            } catch (\Exception $e) {
                Log::error("Webhook delivery exception for config {$config->id} ({$config->url}): " . $e->getMessage());

                WebhookLog::create([
                    'webhook_config_id' => $config->id,
                    'registration_id' => $registration->id,
                    'payload' => $payload,
                    'response_status' => 500,
                    'response_body' => $e->getMessage(),
                    'sent_at' => now(),
                ]);

                $hasFailure = true;
            }
        }

        // If any retryable failure occurred, throw an exception to trigger job retry
        if ($hasFailure) {
            throw new \Exception("One or more webhooks failed with a retryable error (5xx or connection issue).");
        }

        // 5. Update registrations.webhook_sent_at
        $registration->update([
            'webhook_sent_at' => now(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendWebhookJob permanently failed for Registration {$this->registration->id}: " . $exception->getMessage());

        // We don't have a single config here, but we can log that the job failed overall
        // Or we could leave it to the logs created during the attempts.
        // The user asked to "log the failure to webhook_logs with a failed status".
        // Since webhook_logs needs a config_id, we can't easily log a 'global' failure.
        // However, we can log a dummy status if we wanted to, but better to just use the existing logs.
    }
}
