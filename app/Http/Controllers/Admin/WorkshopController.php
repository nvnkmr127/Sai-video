<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Jobs\SendWebhookJob;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index()
    {
        $workshops = Workshop::latest()->paginate(10);
        return view('admin.workshops.index', compact('workshops'));
    }

    public function create()
    {
        return view('admin.workshops.form', ['workshop' => new Workshop()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starts_at' => 'required|date',
            'location' => 'required|string|max:255',
            'location_link' => 'nullable|string|max:1000',
            'max_seats' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['date'] = \Carbon\Carbon::parse($data['starts_at'])->toDateString();

        Workshop::create($data);

        return redirect()->route('admin.workshops.index')->with('success', 'Workshop created successfully.');
    }

    public function edit(Workshop $workshop)
    {
        return view('admin.workshops.form', compact('workshop'));
    }

    public function update(Request $request, Workshop $workshop)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starts_at' => 'required|date',
            'location' => 'required|string|max:255',
            'location_link' => 'nullable|string|max:1000',
            'max_seats' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['date'] = \Carbon\Carbon::parse($data['starts_at'])->toDateString();

        $workshop->update($data);

        return redirect()->route('admin.workshops.index')->with('success', 'Workshop updated successfully.');
    }

    public function destroy(Workshop $workshop)
    {
        if ($workshop->registrations()->exists()) {
            return redirect()->route('admin.workshops.index')->with('error', 'Cannot delete workshop with existing registrations. Deactivate it instead.');
        }

        $workshop->delete();
        return redirect()->route('admin.workshops.index')->with('success', 'Workshop deleted successfully.');
    }

    public function complete(Workshop $workshop)
    {
        if ($workshop->isCompleted()) {
            return redirect()->route('admin.workshops.index')->with('error', 'This workshop has already been completed.');
        }

        $workshop->update([
            'completed_at' => now(),
            'is_active' => false,
        ]);

        $checkedInAttendees = $workshop->registrations()
            ->where('status', 'approved')
            ->whereNotNull('checked_in_at')
            ->get();

        $count = 0;
        foreach ($checkedInAttendees as $registration) {
            if (app()->isLocal()) {
                SendWebhookJob::dispatchSync($registration, null, 'certificate.sent');
            } else {
                SendWebhookJob::dispatch($registration, null, 'certificate.sent');
            }
            $count++;
        }

        return redirect()->route('admin.workshops.index')->with('success', "Workshop '{$workshop->title}' marked as completed. Certificate webhooks have been dispatched for {$count} checked-in attendee(s).");
    }
}
