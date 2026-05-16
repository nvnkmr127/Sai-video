<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
class RetryFailedWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:retry-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed webhook deliveries from the last 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting retry for failed webhooks...');

        $failedLogs = \App\Models\WebhookLog::whereNotIn('response_status', [200, 201, 202])
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        if ($failedLogs->isEmpty()) {
            $this->info('No failed webhooks found in the last 24 hours.');
            return;
        }

        $count = 0;
        foreach ($failedLogs as $log) {
            $registration = \App\Models\Registration::find($log->registration_id);
            if ($registration) {
                \App\Jobs\SendWebhookJob::dispatch($registration, $log->webhook_config_id);
                $count++;
                $this->line("Re-dispatched SendWebhookJob for Registration ID: {$log->registration_id} to Config ID: {$log->webhook_config_id}");
            }
        }

        $this->info("Successfully re-dispatched {$count} jobs.");
    }
}
