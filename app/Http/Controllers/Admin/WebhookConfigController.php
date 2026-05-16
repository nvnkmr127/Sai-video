<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookConfigController extends Controller
{
    public function index()
    {
        $configs = WebhookConfig::latest()->paginate(10);
        return view('admin.webhooks.index', compact('configs'));
    }

    public function create()
    {
        return view('admin.webhooks.form', ['config' => new WebhookConfig()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:registration,otp',
            'url' => 'required|url',
            'secret_token' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        WebhookConfig::create($data);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook configuration created successfully.');
    }

    public function edit(WebhookConfig $webhook)
    {
        return view('admin.webhooks.form', ['config' => $webhook]);
    }

    public function update(Request $request, WebhookConfig $webhook)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:registration,otp',
            'url' => 'required|url',
            'secret_token' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        $webhook->update($data);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook configuration updated successfully.');
    }

    public function test(WebhookConfig $webhook)
    {
        try {
            $response = Http::withHeaders([
                'X-Webhook-Secret' => $webhook->secret_token,
                'X-Event' => 'test.ping'
            ])->post($webhook->url, [
                'event' => 'test.ping',
                'timestamp' => now()->toIso8601String(),
                'message' => 'This is a test notification from WorkshopPro.'
            ]);

            return response()->json([
                'status' => $response->status(),
                'success' => $response->successful()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(WebhookConfig $webhook)
    {
        $webhook->delete();
        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook configuration deleted successfully.');
    }
}
