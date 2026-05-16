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
            'type' => 'required|in:registration,registration_pending,registration_approved,otp',
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
            'type' => 'required|in:registration,registration_pending,registration_approved,otp',
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
            $payload = [
                'event' => 'test.ping',
                'timestamp' => now()->toIso8601String(),
                'message' => 'This is a test notification from WorkshopPro.',
                'trace_id' => (string) \Illuminate\Support\Str::uuid()
            ];

            $event = 'test.ping';

            if ($webhook->type === 'otp') {
                $event = 'otp.send';
                $payload = [
                    'phone' => '+919876543210',
                    'otp' => '123456',
                    'message' => 'Your verification code is: 123456',
                    'is_test' => true
                ];
            } elseif (str_starts_with($webhook->type, 'registration')) {
                $event = 'registration.created';
                if ($webhook->type === 'registration_pending') $event = 'registration.pending';
                if ($webhook->type === 'registration_approved') $event = 'registration.approved';

                $payload = [
                    'event' => $event,
                    'status' => ($webhook->type === 'registration_pending') ? 'pending' : 'approved',
                    'timestamp' => now()->toIso8601String(),
                    'registration_id' => 999,
                    'workshop_id' => 1,
                    'workshop_title' => 'Sample Workshop (Test)',
                    'full_name' => 'John Doe',
                    'phone' => '+919876543210',
                    'address' => '123 Test St, Sector 4, Sample City',
                    'organization' => 'Test Organization',
                    'qr_code_token' => (string) \Illuminate\Support\Str::uuid(),
                    'qr_code_image_base64' => ($event === 'registration.approved') ? 'base64_encoded_image_sample' : null,
                    'qr_code_image_url' => ($event === 'registration.approved') ? 'https://example.com/sample-qr.png' : null,
                    'is_test' => true
                ];
            }

            $response = Http::withHeaders([
                'X-Webhook-Secret' => $webhook->secret_token,
                'X-Event' => $event
            ])->post($webhook->url, $payload);

            // Log the test attempt
            \App\Models\WebhookLog::create([
                'webhook_config_id' => $webhook->id,
                'registration_id' => null, // No registration for test
                'payload' => $payload,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'sent_at' => now(),
            ]);

            return response()->json([
                'status' => $response->status(),
                'success' => $response->successful(),
                'payload_sent' => $payload
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
