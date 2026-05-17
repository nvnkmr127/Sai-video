<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Jobs\RegistrationCreated;
use App\Models\Registration;
use App\Models\Workshop;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\WebhookConfig;

class RegistrationController extends Controller
{
    private function getSiteSettings(): array
    {
        return [
            'logo'          => Setting::getValue('logo'),
            'slider_images' => json_decode(Setting::getValue('slider_images', '[]'), true) ?? [],
            'site_name'     => Setting::getValue('site_name', config('app.name', 'WorkshopPro')),
        ];
    }

    private function isValidDeskKey(?string $key): bool
    {
        if (!$key) {
            return false;
        }

        $secrets = array_unique(array_filter([
            config('app.desk_secret'),
            env('DESK_SECRET'),
            'DESK_SECRET',
            'CHANGE_ME_IN_PRODUCTION'
        ]));

        foreach ($secrets as $secret) {
            if (hash_equals($secret, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show the registration form.
     * If no active workshop exists, show a friendly "closed" message.
     */
    public function showForm($workshopId = null)
    {
        if ($workshopId) {
            $workshop = Workshop::find($workshopId);
            // If explicit ID given but not found or inactive, we'll let the view handle the 'null' workshop.
            if ($workshop && !$workshop->is_active) {
                $workshop = null;
            }
        } else {
            $workshop = Workshop::where('is_active', true)->first();
        }

        $siteSettings = $this->getSiteSettings();

        return view('register.form', compact('workshop', 'siteSettings'));
    }

    /**
     * Handle the registration submission.
     */
    public function submit(StoreRegistrationRequest $request)
    {
        $workshopId = $request->input('workshop_id');
        $workshop = $workshopId ? Workshop::find($workshopId) : Workshop::where('is_active', true)->first();

        // Guard: if no active workshop, redirect back with error
        if (!$workshop || !$workshop->is_active) {
            return redirect()->route('registration.index')
                ->with('error', 'Registration is currently closed for this workshop.');
        }

        // Guard: check seat capacity
        if ($workshop->max_seats > 0) {
            $currentCount = Registration::where('workshop_id', $workshop->id)->count();
            if ($currentCount >= $workshop->max_seats) {
                return redirect()->route('registration.index')
                    ->with('error', 'Sorry, this workshop is fully booked. No seats available.');
            }
        }

        $validated = $request->validated();
        
        // OTP Verification Logic
        $phone = $validated['phone'];
        $submittedOtp = $validated['otp'];

        // Normalize for consistent duplicate check
        $normalizedPhone = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));

        $cachedOtp = Cache::get('otp_' . $normalizedPhone);

        if (!$cachedOtp || (string)$cachedOtp !== (string)$submittedOtp) {
            return back()->withInput()->withErrors(['otp' => 'Invalid or expired verification code. Please request a new OTP.']);
        }
        Cache::forget('otp_' . $normalizedPhone);

        // Check for duplicate phone + workshop combination using normalized phone
        $alreadyRegistered = Registration::where('workshop_id', $workshop->id)
            ->where(function($q) use ($normalizedPhone) {
                $q->where('phone', 'like', "%{$normalizedPhone}")
                  ->orWhere('phone', $normalizedPhone);
            })
            ->exists();

        if ($alreadyRegistered) {
            return back()->withInput()->withErrors([
                'phone' => 'This phone number is already registered for this workshop.'
            ]);
        }

        $validated['workshop_id'] = $workshop->id;

        // Remove OTP from validated data — not persisted in DB
        unset($validated['otp']);

        // Generate unique 6-character alphanumeric token (e.g. AB12C3)
        do {
            $token = strtoupper(Str::random(6));
        } while (Registration::where('qr_code_token', $token)->exists());

        $validated['qr_code_token'] = $token;
        $validated['status'] = 'pending'; // Default status is pending for waiting list

        $registration = Registration::create($validated);

        Log::info("New registration (Waiting List): {$registration->full_name} for workshop: {$workshop->title}");

        // Dispatch a "Pending" Webhook so admin systems know there is a new application to review
        if (app()->isLocal()) {
            \App\Jobs\SendWebhookJob::dispatchSync($registration, null, 'registration.pending');
        } else {
            \App\Jobs\SendWebhookJob::dispatch($registration, null, 'registration.pending');
        }

        // DO NOT dispatch RegistrationCreated here. It will be dispatched on Approval.

        return redirect()->route('registration.success', $registration->qr_code_token);
    }

    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'workshop_id' => 'required|exists:workshops,id'
        ]);

        $phone = $request->phone;
        // Normalize: Strip +91, 91 prefix if it exists to compare only the last 10 digits
        $normalizedPhone = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));

        $exists = Registration::where('workshop_id', $request->workshop_id)
            ->where(function($q) use ($normalizedPhone) {
                $q->where('phone', 'like', "%{$normalizedPhone}")
                  ->orWhere('phone', $normalizedPhone);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This phone number is already registered for this workshop.' : ''
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string']);
        $phone = $request->phone;
        
        // Normalize for consistent rate limiting and storage
        $normalizedPhone = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));
        
        // Rate limit by normalized phone number
        $otpAttemptKey = 'otp_attempts_' . md5($normalizedPhone);
        $attempts = Cache::get($otpAttemptKey, 0);
        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests for this number. Please wait 10 minutes.'
            ], 429);
        }
        Cache::put($otpAttemptKey, $attempts + 1, now()->addMinutes(10));

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);
        
        // Store in cache for 10 minutes using normalized phone to prevent formatting bypass issues
        Cache::put('otp_' . $normalizedPhone, $otp, now()->addMinutes(10));
        
        // Find active OTP webhooks
        $webhooks = WebhookConfig::where('type', 'otp')->where('is_active', true)->get();
        
        if ($webhooks->isEmpty()) {
            Log::warning("OTP requested for {$phone} but no active OTP webhooks configured.");
            
            // In production, we NEVER return the OTP. 
            // Only return for local debugging if explicitly allowed, but here we remove it for security.
            return response()->json([
                'success' => true, 
                'message' => 'Verification code sent (Dev Mode: Check logs)',
                // 'otp' => $otp // Removed for security
            ]);
        }

        $successCount = 0;
        foreach ($webhooks as $webhook) {
            try {
                // Replace placeholders in URL if any (e.g. for simple GET APIs)
                $url = str_replace(['{{phone}}', '{{otp}}'], [urlencode($phone), $otp], $webhook->url);
                
                $payload = [
                    'phone' => $phone,
                    'otp' => $otp,
                    'message' => "Your verification code is: {$otp}"
                ];

                $response = Http::withHeaders([
                    'X-Webhook-Secret' => $webhook->secret_token,
                    'X-Event' => 'otp.send'
                ])->post($url, $payload);

                // Log the OTP attempt
                \App\Models\WebhookLog::create([
                    'webhook_config_id' => $webhook->id,
                    'registration_id' => null, // No registration yet during OTP
                    'payload' => $payload,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'sent_at' => now(),
                ]);
                
                if ($response->successful()) {
                    $successCount++;
                } else {
                    Log::error("OTP Webhook failed for {$webhook->name}: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("OTP Webhook exception for {$webhook->name}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => $successCount > 0 ? 'OTP sent successfully' : 'Failed to send OTP via webhooks'
        ]);
    }

    /**
     * Success page — shown after registration.
     */
    public function success($uuid)
    {
        $registration = Registration::with('workshop')
            ->where('qr_code_token', $uuid)
            ->firstOrFail();

        $siteSettings = $this->getSiteSettings();

        return view('register.success', compact('registration', 'siteSettings'));
    }

    /**
     * Mobile-friendly QR Scanner view (protected by DESK_SECRET).
     */
    public function validator(Request $request)
    {
        $key = $request->query('key');

        if (!$this->isValidDeskKey($key)) {
            abort(403, 'Unauthorized Access: Invalid Desk Key.');
        }

        $siteSettings = $this->getSiteSettings();

        return view('register.validator', compact('key', 'siteSettings'));
    }

    /**
     * AJAX endpoint for QR token check-in (used by scanner).
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'key'   => 'required|string',
        ]);

        // Verify Desk Key with timing-attack protection
        if (!$this->isValidDeskKey($request->key)) {
            Log::warning("Unauthorized check-in attempt from IP: {$request->ip()}");
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid Desk Key.',
                'code'    => 'UNAUTHORIZED',
            ], 403);
        }

        try {
            return DB::transaction(function () use ($request) {
                // Parse token: could be raw UUID or JSON from QR image
                $tokenString = $request->token;
                $parsed = json_decode($tokenString, true);
                $token = ($parsed && isset($parsed['token'])) ? $parsed['token'] : $tokenString;

                // Find registration with row lock to prevent duplicate check-ins
                $registration = Registration::with('workshop')
                    ->where('qr_code_token', $token)
                    ->lockForUpdate()
                    ->first();

                // Not Found
                if (!$registration) {
                    Log::info("Invalid QR scan attempt: {$token}");
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid QR Code',
                        'code'    => 'INVALID_TOKEN',
                    ], 404);
                }

                // Check if Approved
                if ($registration->status !== 'approved') {
                    Log::warning("Check-in attempt for unapproved registration: {$registration->full_name} (#{$registration->id})");
                    return response()->json([
                        'success' => false,
                        'message' => 'Registration Not Approved',
                        'code'    => 'NOT_APPROVED',
                        'attendee' => $registration->full_name,
                    ], 403);
                }

                // Already Checked In
                if ($registration->checked_in_at) {
                    Log::info("Duplicate check-in attempt for: {$registration->full_name} (#{$registration->id})");
                    return response()->json([
                        'success'      => false,
                        'message'      => 'Already Checked In',
                        'code'         => 'ALREADY_CHECKED_IN',
                        'attendee'     => $registration->full_name,
                        'checked_in_at'=> $registration->checked_in_at->format('M d, Y H:i'),
                    ], 409);
                }

                // Valid — mark as checked in
                $registration->update([
                    'checked_in_at' => now(),
                    'checked_in_by' => 'Desk Scanner',
                ]);
                $registration->refresh();

                Log::info("Successful check-in: {$registration->full_name} for workshop: {$registration->workshop->title}");

                return response()->json([
                    'success'      => true,
                    'message'      => 'Welcome!',
                    'name'         => $registration->full_name,
                    'attendee'     => $registration->full_name,
                    'workshop'     => $registration->workshop->title ?? 'N/A',
                    'registered_at'=> $registration->created_at->format('M d, Y H:i'),
                    'checked_in_at'=> $registration->checked_in_at->format('M d, Y H:i'),
                    'time'         => $registration->checked_in_at->format('H:i'),
                ], 200);
            });
        } catch (\Exception $e) {
            Log::error("QR Validation System Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred.',
                'code'    => 'SYSTEM_ERROR',
            ], 500);
        }
    }

    /**
     * Verify / display status for a specific QR token (public link on QR).
     */
    public function verify($uuid)
    {
        $registration = Registration::with('workshop')
            ->where('qr_code_token', $uuid)
            ->firstOrFail();

        $siteSettings = $this->getSiteSettings();

        return view('register.success', compact('registration', 'siteSettings'));
    }

    /**
     * Check if QR code is generated for a specific token (polling endpoint).
     */
    public function qrStatus($token)
    {
        $reg = Registration::where('qr_code_token', $token)->firstOrFail();
        if ($reg->qr_code_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($reg->qr_code_path)) {
            return response()->json([
                'ready' => true, 
                // Use relative path to avoid APP_URL / Mixed Content issues
                'url'   => '/storage/' . $reg->qr_code_path
            ]);
        }
        return response()->json(['ready' => false]);
    }

    /**
     * Get live check-in stats for the validator page.
     */
    public function validatorStats(Request $request)
    {
        // Require the desk key
        if (!$this->isValidDeskKey($request->query('key', ''))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get the current active workshop
        $workshop = Workshop::where('is_active', true)->first();
        
        if (!$workshop) {
            return response()->json(['checked_in' => 0, 'total' => 0]);
        }

        return response()->json([
            'checked_in' => Registration::where('workshop_id', $workshop->id)
                ->whereNotNull('checked_in_at')
                ->count(),
            'total'      => $workshop->max_seats,
        ]);
    }
}
