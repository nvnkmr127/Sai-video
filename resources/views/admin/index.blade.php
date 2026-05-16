@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Admin Dashboard</h1>
            <p class="subtitle">Overview of workshop registrations and check-ins.</p>
        </div>
        <a href="{{ route('admin.list') }}" class="btn">View All Registrations</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 2rem; border-radius: 1rem; border: 1px solid var(--border); text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">{{ $stats['total'] }}</div>
            <div style="color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem;">Total Registered</div>
        </div>
        <div style="background: white; padding: 2rem; border-radius: 1rem; border: 1px solid var(--border); text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;">{{ $stats['checked_in'] }}</div>
            <div style="color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem;">Checked In</div>
        </div>
        <div style="background: white; padding: 2rem; border-radius: 1rem; border: 1px solid var(--border); text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b;">{{ $stats['pending'] }}</div>
            <div style="color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem;">Pending</div>
        </div>
    </div>

    <div style="background: #f1f5f9; padding: 2rem; border-radius: 1rem; border: 1px dashed var(--border); text-align: center;">
        <p style="font-weight: 600; margin-bottom: 1rem;">QR Code Scanning</p>
        <p class="subtitle" style="margin-bottom: 0;">Use the QR Code Validator by scanning attendee passes. The system will automatically mark them as checked in.</p>
    </div>
</div>
@endsection
