<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Models\Workshop;
use App\Jobs\GenerateAndSendQrCode;
use App\Jobs\SendWebhookJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RunFullFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:run-full-flow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes a full registration flow (Creation -> QR Generation -> Webhook) synchronously.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting End-to-End Registration Flow Test...");

        // 1. Setup Workshop
        $workshop = Workshop::first() ?? Workshop::create([
            'title' => 'E2E Test Workshop',
            'description' => 'Used for automated testing.',
            'date' => now()->addDays(1),
            'location' => 'Virtual',
            'max_seats' => 100,
            'is_active' => true
        ]);
        $this->comment("Step 1: Workshop ready: {$workshop->title}");

        // 2. Create Registration
        $email = 'e2e-' . time() . '@example.com';
        $registration = Registration::create([
            'full_name' => 'E2E Test User',
            'email' => $email,
            'phone' => '0000000000',
            'workshop_id' => $workshop->id,
            'qr_code_token' => (string) Str::uuid(),
            'organization' => 'E2E Testing Lab'
        ]);
        $this->info("Step 2: Registration created for {$email} (ID: {$registration->id})");

        // 3. Trigger QR Generation Synchronously
        $this->comment("Step 3: Generating QR Code synchronously...");
        try {
            (new GenerateAndSendQrCode($registration))->handle();
            $registration->refresh();
            if ($registration->qr_code_path) {
                $this->info("   [SUCCESS] QR Code generated: {$registration->qr_code_path}");
            } else {
                $this->error("   [FAILED] QR Code path is still empty.");
            }
        } catch (\Exception $e) {
            $this->error("   [ERROR] QR Generation failed: " . $e->getMessage());
        }

        // 4. Trigger Webhook Dispatch Synchronously
        $this->comment("Step 4: Dispatching Webhooks synchronously...");
        try {
            (new SendWebhookJob($registration))->handle();
            $registration->refresh();
            if ($registration->webhook_sent_at) {
                $this->info("   [SUCCESS] Webhook sent at: {$registration->webhook_sent_at}");
            } else {
                $this->warn("   [NOTICE] Webhook sent status is null (Check if any active webhooks exist).");
            }
        } catch (\Exception $e) {
            $this->error("   [ERROR] Webhook dispatch failed: " . $e->getMessage());
        }

        $this->info("--- Flow Test Completed ---");
        $this->table(['Field', 'Value'], [
            ['Token', $registration->qr_code_token],
            ['QR Path', $registration->qr_code_path ?? 'FAILED'],
            ['Webhook Sent', $registration->webhook_sent_at ?? 'NONE/FAILED'],
        ]);
    }
}
