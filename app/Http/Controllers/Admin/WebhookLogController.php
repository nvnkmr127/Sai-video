<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    /**
     * Display a listing of the webhook logs.
     */
    public function index(Request $request)
    {
        $query = WebhookLog::with(['webhookConfig', 'registration'])
            ->latest();

        // Filter by Status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'success') {
                $query->whereBetween('response_status', [200, 299]);
            } elseif ($status === 'failed') {
                $query->whereNotBetween('response_status', [200, 299]);
            }
        }

        // Filter by Config
        if ($request->filled('webhook_config_id')) {
            $query->where('webhook_config_id', $request->webhook_config_id);
        }

        $logs = $query->paginate(50)->withQueryString();
        
        $configs = \App\Models\WebhookConfig::all();

        return view('admin.webhooks.logs', compact('logs', 'configs'));
    }

    /**
     * Display the specified webhook log.
     */
    public function show(WebhookLog $log)
    {
        return view('admin.webhooks.log_show', compact('log'));
    }

    /**
     * Replay the specified webhook log.
     */
    public function replay(WebhookLog $log)
    {
        $registration = $log->registration;
        if (!$registration) {
            return back()->with('error', 'Associated registration not found.');
        }

        $event = $log->payload['event'] ?? 'registration.created';

        try {
            \App\Jobs\SendWebhookJob::dispatchSync($registration, $log->webhook_config_id, $event, true);
            
            $newLog = WebhookLog::where('registration_id', $registration->id)
                ->where('webhook_config_id', $log->webhook_config_id)
                ->latest('id')
                ->first();
            
            if ($newLog && $newLog->response_status >= 200 && $newLog->response_status < 300) {
                return redirect()->route('admin.webhooks.log-show', $newLog)
                    ->with('success', "Webhook replayed successfully! Response status: {$newLog->response_status}");
            } elseif ($newLog) {
                return redirect()->route('admin.webhooks.log-show', $newLog)
                    ->with('error', "Webhook replayed but returned error. Response status: {$newLog->response_status}");
            }

            return back()->with('success', 'Webhook replayed.');
        } catch (\Exception $e) {
            $newLog = WebhookLog::where('registration_id', $registration->id)
                ->where('webhook_config_id', $log->webhook_config_id)
                ->latest('id')
                ->first();
                
            if ($newLog && $newLog->id !== $log->id) {
                return redirect()->route('admin.webhooks.log-show', $newLog)
                    ->with('error', "Webhook replay failed: {$e->getMessage()} (Response status: {$newLog->response_status})");
            }

            return back()->with('error', 'Webhook replay failed: ' . $e->getMessage());
        }
    }
}
