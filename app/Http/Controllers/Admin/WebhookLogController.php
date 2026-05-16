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
}
