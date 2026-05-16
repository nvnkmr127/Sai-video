@extends('layouts.app')

@section('content')
<div style="text-align: center; max-width: 600px; margin: 0 auto; padding: 2rem;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
    <h1>QR Code Validator</h1>
    <p>Verifying attendee credentials for entry.</p>

    <div style="background: #f8f9fa; padding: 2rem; border-radius: 1rem; border: 1px solid #e2e8f0; margin: 2rem 0; text-align: left;">
        <div style="margin-bottom: 1.5rem;">
            <strong>Attendee Name</strong>
            <div style="font-size: 1.25rem; font-weight: 700;">{{ $registration->full_name }}</div>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <strong>Email / Phone</strong>
            <div>{{ $registration->email }}</div>
            <div>{{ $registration->phone }}</div>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <strong>Workshop</strong>
            <div>{{ $registration->workshop->title ?? 'N/A' }}</div>
        </div>
        <div>
            <strong>Status</strong>
            @if($registration->checked_in_at)
                <div style="margin-top: 0.5rem;">✅ VALIDATED / CHECKED IN</div>
                <div style="color: #666; font-size: 0.9rem;">{{ $registration->checked_in_at->format('M d, Y H:i:s') }}</div>
            @else
                <div style="margin-top: 0.5rem;">⏳ PENDING CHECK-IN</div>
            @endif
        </div>
    </div>

    @if(!$registration->checked_in_at)
        <form action="{{ route('admin.checkin', $registration->qr_code_token) }}" method="POST">
            @csrf
            <button type="submit" style="width: 100%; background: #10b981; color: white; border: none; padding: 1.25rem; border-radius: 0.5rem; font-weight: 700; font-size: 1rem; cursor: pointer;">CONFIRM ENTRY</button>
        </form>
    @else
        <div style="padding: 1.25rem; border-radius: 0.5rem; background: #f1f5f9; color: #666; font-weight: 700; border: 1px solid #e2e8f0;">
            ALREADY VALIDATED
        </div>
    @endif

    <div style="margin-top: 2rem;">
        <a href="{{ route('admin.dashboard') }}" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">← Back to Dashboard</a>
    </div>
</div>
@endsection
