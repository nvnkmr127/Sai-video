<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Workshop;
use App\Jobs\SendWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total'         => Registration::count(),
            'checked_in'    => Registration::whereNotNull('checked_in_at')->count(),
            'pending'       => Registration::whereNull('checked_in_at')->count(),
            'webhooks_sent' => Registration::whereNotNull('webhook_sent_at')->count(),
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
            } elseif ($request->status === 'pending') {
                $query->whereNull('checked_in_at');
            }
        }

        // Calculate Scoped Stats (After filtering, before pagination)
        $scopedStats = [
            'total'      => (clone $query)->count(),
            'checked_in' => (clone $query)->whereNotNull('checked_in_at')->count(),
            'pending'    => (clone $query)->whereNull('checked_in_at')->count(),
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
            } elseif ($request->status === 'pending') {
                $query->whereNull('checked_in_at');
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

    /**
     * Manual check-in via AJAX — uses DB lock to prevent race conditions.
     */
    public function manualCheckin($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $registration = Registration::lockForUpdate()->findOrFail($id);

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

        SendWebhookJob::dispatch($registration);

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

                if ($registration->checked_in_at) {
                    return back()->with('error', 'Attendee already checked in at ' . $registration->checked_in_at->format('M d, Y H:i'));
                }

                $registration->update([
                    'checked_in_at' => now(),
                    'checked_in_by' => auth('admin')->user()->name ?? 'Admin QR Scan',
                ]);

                Log::info("Admin QR check-in: {$registration->full_name} (#{$registration->id})");

                return back()->with('success', "{$registration->full_name} has been checked in successfully!");
            });
        } catch (\Exception $e) {
            Log::error("Admin QR check-in error: " . $e->getMessage());
            return back()->with('error', 'Check-in failed. Please try again.');
        }
    }
}
