<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
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
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'max_seats' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

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
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'max_seats' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

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
}
