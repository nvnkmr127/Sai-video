<?php

namespace App\Jobs;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateAndSendQrCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max attempts before the job is marked as failed.
     */
    public int $tries = 3;

    /**
     * Backoff strategy in seconds.
     */
    public array $backoff = [30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Registration $registration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if QR already generated (idempotency guard)
        if ($this->registration->qr_code_path && Storage::disk('public')->exists($this->registration->qr_code_path)) {
            Log::info("QR already exists for registration #{$this->registration->id}, dispatching webhook only.");
            if (app()->isLocal()) {
                SendWebhookJob::dispatchSync($this->registration);
            } else {
                SendWebhookJob::dispatch($this->registration);
            }
            return;
        }

        // 1. Build QR data payload — encode as JSON so scanner can parse structured data
        $qrData = json_encode([
            'token'           => $this->registration->qr_code_token,
            'registration_id' => $this->registration->id,
            'workshop_id'     => $this->registration->workshop_id,
            'name'            => $this->registration->full_name,
        ]);

        // 2. Generate QR Code Image (SVG format — no imagick dependency)
        $qrCodeImage = QrCode::format('svg')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrData);

        // 3. Save to storage/app/public/qrcodes/{token}.svg
        $filePath = "qrcodes/{$this->registration->qr_code_token}.svg";
        Storage::disk('public')->put($filePath, $qrCodeImage);

        // 4. Update the registration record with QR path
        $this->registration->update([
            'qr_code_path' => $filePath,
        ]);

        Log::info("QR Code generated for registration #{$this->registration->id}: {$filePath}");

        // 5. Dispatch the Webhook Job
        if (app()->isLocal()) {
            SendWebhookJob::dispatchSync($this->registration);
        } else {
            SendWebhookJob::dispatch($this->registration);
        }
    }

    /**
     * Handle permanent failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateAndSendQrCode permanently failed for Registration #{$this->registration->id}: " . $exception->getMessage());
    }
}
