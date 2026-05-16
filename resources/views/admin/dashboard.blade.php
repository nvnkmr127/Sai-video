@extends('layouts.admin')

@section('title', 'System Overview')

@section('content')
<div class="row g-4 mb-5">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-label">Total Registrations</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
            <div class="mt-3 small text-muted">
                Combined event entries
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-label">Waiting List</div>
            <div class="stat-value">{{ number_format($stats['waiting_approval']) }}</div>
            <div class="mt-3 small text-muted">
                Awaiting admin review
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                <i class="bi bi-patch-check"></i>
            </div>
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ number_format($stats['approved']) }}</div>
            <div class="mt-3 small text-muted">
                QR Codes generated
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="stat-label">Checked In</div>
            <div class="stat-value">{{ number_format($stats['checked_in']) }}</div>
            <div class="mt-3 small text-muted">
                Verified at the desk
            </div>
        </div>
    </div>
</div>

<div class="content-card p-4 mb-4">
    <h5 class="fw-bold mb-4">Workshop Capacity</h5>
    @forelse($workshopCapacity as $ws)
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold">{{ $ws->title }}</span>
                <span class="small text-muted">
                    {{ $ws->registrations_count }} / {{ $ws->max_seats }} registered
                    &nbsp;·&nbsp;
                    <span class="text-success">{{ $ws->checked_in_count }} checked in</span>
                </span>
            </div>
            @php $regPct = $ws->max_seats > 0 ? min(100, round($ws->registrations_count / $ws->max_seats * 100)) : 0; @endphp
            @php $checkinPct = $ws->max_seats > 0 ? min(100, round($ws->checked_in_count / $ws->max_seats * 100)) : 0; @endphp
            <div class="progress" style="height: 8px; border-radius: 4px; background: rgba(255,255,255,0.05);">
                <div class="progress-bar bg-primary bg-opacity-50" style="width: {{ $regPct }}%"></div>
            </div>
            <div class="progress mt-1" style="height: 4px; border-radius: 4px; background: rgba(255,255,255,0.05);">
                <div class="progress-bar bg-success" style="width: {{ $checkinPct }}%"></div>
            </div>
            <div class="small text-muted mt-1">{{ $regPct }}% seats filled · {{ $checkinPct }}% checked in</div>
        </div>
    @empty
        <p class="text-muted small">No workshops configured.</p>
    @endforelse
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Recent Registrations</h5>
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Attendee</th>
                            <th>Workshop</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRegistrations as $reg)
                            <tr>
                                <td>
                                    <div class="fw-bold text-white">{{ $reg->full_name }}</div>
                                    <div class="small text-muted">{{ $reg->phone }}</div>
                                </td>
                                <td>{{ $reg->workshop->title ?? 'N/A' }}</td>
                                <td>
                                    @if($reg->status === 'approved')
                                        @if($reg->checked_in_at)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Checked In</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">Approved</span>
                                        @endif
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Waiting List</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $reg->created_at->format('M d, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No recent registrations found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="content-card p-4 h-100">
            <h5 class="fw-bold mb-4">System Quick Actions</h5>
            <div class="d-grid gap-3">
                <a href="{{ route('registration.validator', ['key' => env('DESK_SECRET', 'DESK_SECRET')]) }}" class="btn btn-outline-info py-4 d-flex flex-column align-items-center gap-2" style="border-radius: 0.75rem; border-style: dashed;">
                    <i class="bi bi-qr-code-scan fs-2"></i>
                    <span class="fw-bold">Scanner Mode</span>
                </a>
                <div class="p-4 rounded-4 bg-secondary bg-opacity-10 border border-secondary border-opacity-10">
                    <div class="small text-muted mb-2 uppercase fw-bold" style="letter-spacing: 1px;">Infrastructure Status</div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="p-1 rounded-circle bg-success"></div>
                        <span class="small">Webhook Queue: **Active**</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="p-1 rounded-circle bg-success"></div>
                        <span class="small">QR Storage: **Connected**</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1 rounded-circle bg-success"></div>
                        <span class="small">OTP Engine: **Operational**</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
