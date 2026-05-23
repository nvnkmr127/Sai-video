<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Workshop;
use App\Jobs\SendWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    private function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));
        return preg_replace('/\D+/', '', (string) $normalized);
    }

    private function isUniqueViolation(QueryException $e, string $constraintName): bool
    {
        $message = $e->getMessage();
        return str_contains($message, $constraintName) || str_contains($message, 'UNIQUE constraint failed');
    }

    public function dashboard()
    {
        $stats = [
            'total'            => Registration::count(),
            'waiting_approval' => Registration::where('status', 'pending')->count(),
            'approved'         => Registration::where('status', 'approved')->count(),
            'checked_in'       => Registration::whereNotNull('checked_in_at')->count(),
        ];

        $workshopCapacity = Workshop::withCount([
            'registrations',
            'registrations as checked_in_count' => fn($q) => $q->whereNotNull('checked_in_at'),
        ])->get();

        $recentRegistrations = Registration::with('workshop')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRegistrations', 'workshopCapacity'));
    }

    /**
     * Delete a registration.
     */
    public function destroy($id)
    {
        $registration = Registration::findOrFail($id);
        $name = $registration->full_name;
        $registration->delete();
        Log::info("Admin deleted registration #{$id} ({$name}) by: " . (auth('admin')->user()->name ?? 'Admin'));
        return redirect()->route('admin.registrations.index')
            ->with('success', "Registration for \"{$name}\" has been deleted.");
    }

    public function registrations(Request $request)
    {
        $query = Registration::with('workshop');

        // Apply Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('workshop_id')) {
            $query->where('workshop_id', $request->workshop_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($request->status === 'approved') {
                $query->where('status', 'approved')->whereNull('checked_in_at');
            } elseif ($request->status === 'waiting') {
                $query->where('status', 'pending');
            }
        }

        // Calculate Scoped Stats (After filtering, before pagination)
        $scopedStats = [
            'total'      => (clone $query)->count(),
            'checked_in' => (clone $query)->whereNotNull('checked_in_at')->count(),
            'approved'   => (clone $query)->where('status', 'approved')->whereNull('checked_in_at')->count(),
            'waiting'    => (clone $query)->where('status', 'pending')->count(),
        ];

        $registrations = $query->latest()->paginate(25)->withQueryString();
        $workshops = Workshop::all();

        return view('admin.registrations.index', compact('registrations', 'workshops', 'scopedStats'));
    }

    public function exportRegistrations(Request $request)
    {
        $query = Registration::with('workshop');

        // Apply same filters as list
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('workshop_id')) {
            $query->where('workshop_id', $request->workshop_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($request->status === 'approved') {
                $query->where('status', 'approved')->whereNull('checked_in_at');
            } elseif ($request->status === 'waiting') {
                $query->where('status', 'pending');
            }
        }

        $filename = 'registrations_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['ID', 'Full Name', 'Phone', 'Workshop', 'Address', 'Registered At', 'Checked In At', 'Checked In By'];

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Use cursor() to prevent memory exhaustion for large datasets
            foreach ($query->latest()->cursor() as $reg) {
                fputcsv($file, [
                    $reg->id,
                    $reg->full_name,
                    $reg->phone,
                    $reg->workshop->title ?? 'N/A',
                    $reg->address ?? '',
                    $reg->created_at->format('Y-m-d H:i:s'),
                    $reg->checked_in_at ? $reg->checked_in_at->format('Y-m-d H:i:s') : 'N/A',
                    $reg->checked_in_by ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $registration = Registration::with(['workshop', 'webhookLogs'])->findOrFail($id);
        return view('admin.registrations.show', compact('registration'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:100',
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{7,20}$/'],
            'organization' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($id, $validated) {
                $registration = Registration::lockForUpdate()->findOrFail($id);

                $normalizedPhone = $this->normalizePhone((string) $validated['phone']);

                $registration->update([
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'normalized_phone' => $normalizedPhone,
                    'organization' => $validated['organization'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);

                Log::info("Registration updated by admin: {$registration->full_name} (#{$registration->id}) by: " . (auth('admin')->user()->name ?? 'Admin'));

                return redirect()
                    ->route('admin.registrations.show', $registration->id)
                    ->with('success', 'Registration updated.');
            });
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e, 'registrations_workshop_normalized_phone_unique')) {
                return back()
                    ->withInput()
                    ->withErrors(['phone' => 'This phone number is already registered for this workshop.']);
            }

            throw $e;
        }
    }

    public function liveRegistrations(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required',
        ]);

        $ids = collect(explode(',', (string) $validated['ids']))
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        if (!$ids) {
            return response()->json([
                'success' => true,
                'now' => now()->toIso8601String(),
                'registrations' => [],
            ]);
        }

        $rows = Registration::query()
            ->whereIn('id', $ids)
            ->get(['id', 'status', 'checked_in_at', 'checked_in_by', 'webhook_sent_at', 'full_name', 'phone', 'created_at']);

        $registrations = $rows->mapWithKeys(function (Registration $r) {
            return [
                (string) $r->id => [
                    'id' => $r->id,
                    'status' => $r->status,
                    'checked_in_at' => $r->checked_in_at?->toIso8601String(),
                    'checked_in_at_human' => $r->checked_in_at?->format('M d, H:i'),
                    'checked_in_by' => $r->checked_in_by,
                    'webhook_sent_at' => $r->webhook_sent_at?->toIso8601String(),
                    'webhook_sent_at_human' => $r->webhook_sent_at?->format('M d, H:i'),
                    'full_name' => $r->full_name,
                    'phone' => $r->phone,
                    'created_at_human' => $r->created_at?->format('M d, H:i'),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'now' => now()->toIso8601String(),
            'registrations' => $registrations,
        ]);
    }

    public function liveRegistrationsStats(Request $request)
    {
        $query = Registration::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('workshop_id')) {
            $query->where('workshop_id', $request->workshop_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($request->status === 'approved') {
                $query->where('status', 'approved')->whereNull('checked_in_at');
            } elseif ($request->status === 'waiting') {
                $query->where('status', 'pending');
            }
        }

        $scopedStats = [
            'total' => (clone $query)->count(),
            'checked_in' => (clone $query)->whereNotNull('checked_in_at')->count(),
            'approved' => (clone $query)->where('status', 'approved')->whereNull('checked_in_at')->count(),
            'waiting' => (clone $query)->where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'now' => now()->toIso8601String(),
            'stats' => $scopedStats,
        ]);
    }

    public function liveRegistration($id)
    {
        $registration = Registration::findOrFail($id);

        return response()->json([
            'success' => true,
            'now' => now()->toIso8601String(),
            'registration' => [
                'id' => $registration->id,
                'status' => $registration->status,
                'checked_in_at' => $registration->checked_in_at?->toIso8601String(),
                'checked_in_at_human' => $registration->checked_in_at?->format('M d, Y H:i:s'),
                'checked_in_by' => $registration->checked_in_by,
                'webhook_sent_at' => $registration->webhook_sent_at?->toIso8601String(),
                'webhook_sent_at_human' => $registration->webhook_sent_at?->format('M d, H:i'),
                'full_name' => $registration->full_name,
                'phone' => $registration->phone,
                'organization' => $registration->organization,
                'address' => $registration->address,
                'created_at_human' => $registration->created_at?->format('M d, Y H:i'),
            ],
        ]);
    }

    /**
     * Manual check-in via AJAX — uses DB lock to prevent race conditions.
     */
    public function manualCheckin($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $registration = Registration::lockForUpdate()->findOrFail($id);

                if ($registration->status !== 'approved') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Attendee is not approved yet. Please approve them first.',
                    ], 403);
                }

                if ($registration->checked_in_at) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Attendee is already checked in.',
                        'checked_in_at' => $registration->checked_in_at->format('M d, Y H:i'),
                        'checked_in_by' => $registration->checked_in_by,
                    ], 409);
                }

                $registration->update([
                    'checked_in_at' => now(),
                    'checked_in_by' => auth('admin')->user()->name ?? 'Admin',
                ]);
                $registration->refresh();

                Log::info("Manual check-in by admin for: {$registration->full_name} (#{$registration->id})");

                // Notify external systems about the check-in
                SendWebhookJob::dispatch($registration, null, 'registration.checked_in');

                return response()->json([
                    'success'       => true,
                    'checked_in_at' => $registration->checked_in_at->format('M d, Y H:i'),
                    'checked_in_by' => $registration->checked_in_by,
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Manual check-in failed for ID {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    public function manualUncheckin($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $registration = Registration::lockForUpdate()->findOrFail($id);

                if ($registration->status !== 'approved') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Attendee is not approved.',
                    ], 403);
                }

                if (!$registration->checked_in_at) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Attendee is not checked in.',
                    ], 409);
                }

                $registration->update([
                    'checked_in_at' => null,
                    'checked_in_by' => null,
                ]);

                Log::info("Manual uncheck-in by admin for: {$registration->full_name} (#{$registration->id})");

                return response()->json([
                    'success' => true,
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Manual uncheck-in failed for ID {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Re-dispatch webhook — with 60 second cooldown.
     */
    public function resendWebhook($id)
    {
        $registration = Registration::findOrFail($id);

        // Cooldown check (60 seconds)
        if ($registration->webhook_sent_at && $registration->webhook_sent_at->gt(now()->subMinute())) {
            return back()->with('error', 'Please wait 60 seconds before resending another webhook.');
        }

        $event = 'registration.pending';
        if ($registration->status === 'approved') {
            $event = $registration->checked_in_at ? 'registration.checked_in' : 'registration.approved';
        }

        SendWebhookJob::dispatch($registration, null, $event);

        return back()->with('success', 'Webhook delivery re-dispatched successfully.');
    }

    /**
     * QR-based check-in (used from admin panel QR URL).
     */
    public function checkin($uuid)
    {
        try {
            return DB::transaction(function () use ($uuid) {
                $registration = Registration::where('qr_code_token', $uuid)->lockForUpdate()->firstOrFail();

                if ($registration->status !== 'approved') {
                    return back()->with('error', 'This registration is still on the Waiting List and has not been approved yet.');
                }

                if ($registration->checked_in_at) {
                    return back()->with('error', 'Attendee already checked in at ' . $registration->checked_in_at->format('M d, Y H:i'));
                }

                $registration->update([
                    'checked_in_at' => now(),
                    'checked_in_by' => auth('admin')->user()->name ?? 'Admin QR Scan',
                ]);

                Log::info("Admin QR check-in: {$registration->full_name} (#{$registration->id})");

                // Notify external systems about the check-in
                SendWebhookJob::dispatch($registration, null, 'registration.checked_in');

                return back()->with('success', "{$registration->full_name} has been checked in successfully!");
            });
        } catch (\Exception $e) {
            Log::error("Admin QR check-in error: " . $e->getMessage());
            return back()->with('error', 'Check-in failed. Please try again.');
        }
    }

    /**
     * Approve a registration from the waiting list.
     */
    public function approve($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $registration = Registration::lockForUpdate()->findOrFail($id);

                if ($registration->status === 'approved') {
                    return back()->with('info', 'Attendee is already approved.');
                }

                $workshop = Workshop::find($registration->workshop_id);
                if ($workshop && $workshop->max_seats > 0) {
                    $approvedCount = Registration::where('workshop_id', $workshop->id)
                        ->where('status', 'approved')
                        ->count();

                    if ($approvedCount >= $workshop->max_seats) {
                        return back()->with('error', 'Workshop capacity reached. Cannot approve more attendees.');
                    }
                }

                $registration->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);

                // Dispatch the pipeline only on approval
                if (app()->isLocal()) {
                    \App\Jobs\RegistrationCreated::dispatchSync($registration);
                } else {
                    \App\Jobs\RegistrationCreated::dispatch($registration);
                }

                Log::info("Registration approved by admin: {$registration->full_name} (#{$id})");

                return back()->with('success', "{$registration->full_name} has been approved and the QR code has been generated.");
            });
        } catch (\Exception $e) {
            Log::error("Approval failed for ID {$id}: " . $e->getMessage());
            return back()->with('error', 'An error occurred during approval.');
        }
    }
}
