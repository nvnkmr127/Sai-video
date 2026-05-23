@extends('layouts.admin')

@section('title', 'Registration Details')

@section('content')
<div class="mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
    <a href="{{ route('admin.registrations.index') }}" class="text-decoration-none text-muted small fw-bold hover-light">
        <i class="bi bi-arrow-left"></i> BACK TO ATTENDEES
    </a>
    <form method="POST" 
          action="{{ route('admin.registrations.destroy', $registration->id) }}"
          onsubmit="return confirm('Permanently delete this registration?')"
          class="d-inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger ms-sm-3 mt-1 mt-sm-0">
            <i class="bi bi-trash me-1"></i> Delete Registration
        </button>
    </form>
</div>

<div class="row g-4">
    <!-- Attendee Profile -->
    <div class="col-lg-8">
        <div class="content-card p-3 p-sm-4 p-lg-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4 mb-lg-5">
                <div class="d-flex align-items-start align-items-sm-center gap-3 gap-lg-4">
                    <div class="avatar flex-shrink-0" style="width: 80px; height: 80px; font-size: 2rem;">{{ substr($registration->full_name, 0, 1) }}</div>
                    <div>
                        <h2 class="fw-bold mb-1">{{ $registration->full_name }}</h2>
                    </div>
                </div>
                <div id="statusBadge" class="align-self-start align-self-md-center">
                    @if($registration->status === 'approved')
                        @if($registration->checked_in_at)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 fs-6">Checked In</span>
                        @else
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-4 py-2 fs-6">Approved</span>
                        @endif
                    @else
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-4 py-2 fs-6">Waiting List</span>
                    @endif
                </div>
            </div>

            <div class="row g-5">
                <div class="col-md-6">
                    <label class="nav-label mb-2 d-block">Workshop Experience</label>
                    <div class="fs-5 fw-bold text-primary">{{ $registration->workshop->title ?? 'N/A' }}</div>
                    <div class="small text-muted mt-1">
                        @if($registration->workshop && $registration->workshop->location_link)
                            <a href="{{ $registration->workshop->location_link }}" target="_blank" class="text-decoration-none text-break">
                                <i class="bi bi-geo-alt"></i> {{ $registration->workshop->location ?? '' }}
                            </a>
                        @else
                            <span class="text-break">{{ $registration->workshop->location ?? '' }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="nav-label mb-2 d-block">Phone Contact</label>
                    <div class="fs-5 fw-semibold text-break">{{ $registration->phone }}</div>
                </div>
                <div class="col-md-6">
                    <label class="nav-label mb-2 d-block">Mailing Address</label>
                    <div class="fs-5 fw-semibold text-break">{{ $registration->address ?? 'Not Specified' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="nav-label mb-2 d-block">Registration Date</label>
                    <div class="fs-5 fw-semibold">{{ $registration->created_at->format('M d, Y H:i') }}</div>
                </div>
                
                <div class="col-12 border-top border-secondary border-opacity-25 pt-4 pt-lg-5 mt-4 mt-lg-5">
                    <label class="nav-label mb-4 d-block">Check-in Verification</label>
                    <div id="checkInData">
                        @if($registration->status === 'pending')
                            <div class="p-4 rounded-4 bg-warning bg-opacity-5 border border-warning border-opacity-10 mb-3">
                                <div class="text-warning fw-bold d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-shield-lock"></i> Pending Verification
                                </div>
                                <p class="small text-muted mb-3">This attendee is currently in the waiting list. You must approve them before they can receive their QR code and check in.</p>
                                <form method="POST" action="{{ route('admin.registrations.approve', $registration->id) }}" class="d-grid d-sm-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="bi bi-check-lg me-2"></i> Approve Registration
                                    </button>
                                </form>
                            </div>
                        @elseif($registration->checked_in_at)
                            <div class="p-4 rounded-4 bg-success bg-opacity-5 border border-success border-opacity-10">
                                <div class="text-success fw-bold d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-patch-check-fill"></i> Successfully Verified
                                </div>
                                <div class="small text-muted">Checked in on {{ $registration->checked_in_at->format('M d, Y H:i:s') }}</div>
                                <div class="small text-muted">Verified by: {{ $registration->checked_in_by }}</div>
                                <div class="mt-3 d-flex flex-column flex-sm-row gap-2">
                                    <button id="manualUncheckInBtn" class="btn btn-outline-danger px-4 align-self-start">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i> Undo Check-In
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <button id="manualCheckInBtn" class="btn btn-primary px-4">
                                    <i class="bi bi-person-check me-2"></i> Confirm Manual Check-In
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Webhook Log -->
        <div class="content-card mt-4">
            <div class="p-4 border-bottom border-secondary border-opacity-25 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <h5 class="fw-bold mb-0">Webhook Synchronization</h5>
                <form action="{{ route('admin.registrations.resend-webhook', $registration->id) }}" method="POST" class="w-100 d-grid d-sm-block">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-broadcast me-2"></i> Trigger Manual Sync
                    </button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Attempt Time</th>
                            <th>Status</th>
                            <th>Response Body</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registration->webhookLogs as $log)
                            <tr>
                                <td class="ps-4 small">{{ $log->sent_at->format('M d, H:i:s') }}</td>
                                <td>
                                    @if($log->response_status >= 200 && $log->response_status < 300)
                                        <span class="badge bg-success-subtle text-success small">{{ $log->response_status }} OK</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger small">{{ $log->response_status }} ERR</span>
                                    @endif
                                </td>
                                <td>
                                    <code class="small text-muted d-inline-block text-break" title="{{ $log->response_body }}">{{ Str::limit($log->response_body, 60) }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted small">No sync attempts recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- QR Sidebar -->
    <div class="col-lg-4">
        <div class="content-card p-3 p-sm-4 text-center">
            <label class="nav-label mb-4 d-block">Digital Entry Pass</label>
            <div class="bg-white p-3 rounded-4 mb-4 d-inline-block">
                @if($registration->qr_code_path)
                        <img src="{{ Storage::disk('public')->url($registration->qr_code_path) }}" class="img-fluid rounded-3" alt="QR Code" style="width: 220px; max-width: 100%;">
                @else
                    <div class="py-5 px-4 text-dark small fw-bold bg-light rounded-3">
                        <i class="bi bi-qr-code fs-1 d-block mb-2"></i>
                        Pending Generation
                    </div>
                @endif
            </div>
            <div class="text-muted small mb-1">Access Token</div>
            <code class="d-block bg-dark bg-opacity-50 p-2 rounded-3 border border-secondary border-opacity-25 small mb-4 text-break">{{ $registration->qr_code_token }}</code>
            
            <div class="border-top border-secondary border-opacity-25 pt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Metadata ID</span>
                    <span class="small fw-bold">#{{ $registration->id }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Sync Status</span>
                    <span class="small fw-bold {{ $registration->webhook_sent_at ? 'text-success' : 'text-warning' }}">
                        {{ $registration->webhook_sent_at ? 'Completed' : 'Pending' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function bindManualCheckIn() {
    const btn = document.getElementById('manualCheckInBtn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        if (!confirm('Confirm manual check-in for this attendee?')) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch('{{ route("admin.registrations.checkin", $registration->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) return response.json().then(err => { throw err; });
            return response.json();
        })
        .then(data => {
            if (!data.success) return;

            document.getElementById('statusBadge').innerHTML = '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 fs-6">Checked In</span>';
            document.getElementById('checkInData').innerHTML = `
                <div class="p-4 rounded-4 bg-success bg-opacity-5 border border-success border-opacity-10">
                    <div class="text-success fw-bold d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-patch-check-fill"></i> Successfully Verified
                    </div>
                    <div class="small text-muted">Checked in on ${data.checked_in_at}</div>
                    <div class="small text-muted">Verified by: ${data.checked_in_by}</div>
                    <div class="mt-3 d-flex flex-column flex-sm-row gap-2">
                        <button id="manualUncheckInBtn" class="btn btn-outline-danger px-4 align-self-start">
                            <i class="bi bi-arrow-counterclockwise me-2"></i> Undo Check-In
                        </button>
                    </div>
                </div>
            `;
            bindManualUncheckIn();
        })
        .catch(error => {
            alert(error.message || 'Verification failed. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check me-2"></i> Confirm Manual Check-In';
        });
    }, { once: true });
}

function bindManualUncheckIn() {
    const btn = document.getElementById('manualUncheckInBtn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        if (!confirm('Undo check-in for this attendee?')) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch('{{ route("admin.registrations.uncheckin", $registration->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) return response.json().then(err => { throw err; });
            return response.json();
        })
        .then(data => {
            if (!data.success) return;

            document.getElementById('statusBadge').innerHTML = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-4 py-2 fs-6">Approved</span>';
            document.getElementById('checkInData').innerHTML = `
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <button id="manualCheckInBtn" class="btn btn-primary px-4">
                        <i class="bi bi-person-check me-2"></i> Confirm Manual Check-In
                    </button>
                </div>
            `;
            bindManualCheckIn();
        })
        .catch(error => {
            alert(error.message || 'Uncheck failed. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-2"></i> Undo Check-In';
        });
    }, { once: true });
}

bindManualCheckIn();
bindManualUncheckIn();
</script>

<style>
    .hover-light:hover { color: var(--text-main) !important; }
</style>
@endsection
