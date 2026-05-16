@extends('layouts.admin')

@section('title', 'Registration Management')

@section('content')
<!-- Summary Stats Bar -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-filter"></i>
            </div>
            <div>
                <div class="small text-muted">Filtered Total</div>
                <div class="fw-bold">{{ $scopedStats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0 text-success" style="width: 40px; height: 40px; font-size: 1rem; background: rgba(16, 185, 129, 0.1);">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="small text-muted">Filtered Checked-In</div>
                <div class="fw-bold">{{ $scopedStats['checked_in'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0 text-warning" style="width: 40px; height: 40px; font-size: 1rem; background: rgba(245, 158, 11, 0.1);">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="small text-muted">Filtered Pending</div>
                <div class="fw-bold">{{ $scopedStats['pending'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="content-card p-4 mb-4">
    <form action="{{ route('admin.registrations.index') }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <label class="form-label text-muted small">Search Attendee</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 border-secondary"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 border-secondary bg-transparent text-white" placeholder="Search by name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Workshop</label>
            <select name="workshop_id" class="form-select bg-transparent border-secondary text-white">
                <option value="" class="bg-dark">All Workshops</option>
                @foreach($workshops as $workshop)
                    <option value="{{ $workshop->id }}" {{ request('workshop_id') == $workshop->id ? 'selected' : '' }} class="bg-dark">
                        {{ $workshop->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Status</label>
            <select name="status" class="form-select bg-transparent border-secondary text-white">
                <option value="" class="bg-dark">All Status</option>
                <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }} class="bg-dark">Checked In</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }} class="bg-dark">Pending</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Apply Filters</button>
            <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="content-card">
    <div class="p-4 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25">
        <h5 class="fw-bold mb-0">Attendee List</h5>
        <a href="{{ route('admin.registrations.export', request()->all()) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-2"></i> Export to CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Attendee</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">{{ substr($reg->full_name, 0, 1) }}</div>
                                <div>
                                    <div class="fw-bold">{{ $reg->full_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="small text-muted">{{ $reg->phone }}</td>
                        <td>
                            <div class="small">{{ $reg->created_at->format('M d, H:i') }}</div>
                        </td>
                        <td>
                            @if($reg->checked_in_at)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">Checked In</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">Pending</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 d-flex gap-2 justify-content-end align-items-center">
                            <a href="{{ route('admin.registrations.show', $reg->id) }}" 
                               class="btn btn-sm btn-outline-primary">View</a>
                            <form method="POST" 
                                  action="{{ route('admin.registrations.destroy', $reg->id) }}"
                                  onsubmit="return confirm('Delete registration for {{ addslashes($reg->full_name) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                            No registrations found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($registrations->hasPages())
        <div class="p-4 border-top border-secondary border-opacity-25">
            {{ $registrations->links() }}
        </div>
    @endif
</div>

<style>
    .form-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='gray' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); }
    .pagination { margin: 0; justify-content: center; }
    .page-link { background: transparent; border-color: var(--border); color: var(--text-muted); }
    .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: white; }
</style>
@endsection
