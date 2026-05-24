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
            'type' => 'required|in:registration,registration_pending,registration_approved,registration_checked_in,otp,workshop_link',
            'url' => 'required|url',
            'secret_token' => 'required|string|max:255',
            'is_active' => 'boolean',
            'link' => 'required_if:type,workshop_link|nullable|url',
            'workshop_title' => 'required_if:type,workshop_link|nullable|string|max:255',
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
            'type' => 'required|in:registration,registration_pending,registration_approved,registration_checked_in,otp,workshop_link',
            'url' => 'required|url',
            'secret_token' => 'required|string|max:255',
            'is_active' => 'boolean',
            'link' => 'required_if:type,workshop_link|nullable|url',
            'workshop_title' => 'required_if:type,workshop_link|nullable|string|max:255',
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
            } elseif ($webhook->type === 'workshop_link') {
                $event = 'registration.approved';
                $payload = [
                    'name' => 'John Doe',
                    'number' => '+919876543210',
                    'link' => $webhook->link,
                    'workshop_title' => $webhook->workshop_title,
                    'is_test' => true
                ];
            } elseif (str_starts_with($webhook->type, 'registration')) {
                $event = 'registration.created';
                if ($webhook->type === 'registration_pending') $event = 'registration.pending';
                if ($webhook->type === 'registration_approved') $event = 'registration.approved';
                if ($webhook->type === 'registration_checked_in') $event = 'registration.checked_in';

                $payload = [
                    'event' => $event,
                    'status' => ($webhook->type === 'registration_pending') ? 'pending' : 'approved',
                    'timestamp' => now()->toIso8601String(),
                    'registration_id' => 999,
                    'workshop_id' => 1,
                    'workshop_title' => 'Sample Workshop (Test)',
                    'workshop_location' => 'Sample Location (Test)',
                    'workshop_location_link' => 'https://maps.google.com/sample',
                    'full_name' => 'John Doe',
                    'phone' => '+919876543210',
                    'address' => '123 Test St, Sector 4, Sample City',
                    'organization' => 'Test Organization',
                    'qr_code_token' => (string) \Illuminate\Support\Str::uuid(),
                    'qr_code_image_base64' => ($event !== 'registration.pending') ? 'base64_encoded_image_sample' : null,
                    'qr_code_image_url' => ($event !== 'registration.pending') ? 'https://example.com/sample-qr.png' : null,
                    'checked_in_at' => ($event === 'registration.checked_in') ? now()->toIso8601String() : null,
                    'checked_in_by' => ($event === 'registration.checked_in') ? 'Desk Scanner' : null,
                    'is_test' => true
                ];

                if ($event === 'registration.approved') {
                    $payload['event_location_link'] = 'https://maps.google.com/sample';
                    $payload['online_pass_url'] = route('registration.success', ['uuid' => 'sample-uuid']);
                    $payload['online_view_of_pass'] = route('registration.success', ['uuid' => 'sample-uuid']);
                }
            }

            $url = $webhook->url;
            if ($webhook->type === 'workshop_link') {
                $url = str_replace(
                    ['{{name}}', '{{number}}', '{{phone}}', '{{link}}', '{{workshop_title}}'],
                    [urlencode('John Doe'), urlencode('+919876543210'), urlencode('+919876543210'), urlencode($webhook->link ?? ''), urlencode($webhook->workshop_title ?? '')],
                    $url
                );
            }

            $response = Http::withHeaders([
                'X-Webhook-Secret' => $webhook->secret_token,
                'X-Event' => $event
            ])->post($url, $payload);

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
