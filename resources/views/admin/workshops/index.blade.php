@extends('layouts.admin')

@section('title', 'Workshops')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Workshops</h2>
        <p class="text-muted mb-0">Create and manage your workshop sessions.</p>
    </div>
    <a href="{{ route('admin.workshops.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Create Workshop
    </a>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Title</th>
                    <th>Date & Time</th>
                    <th>Location</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workshops as $workshop)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $workshop->title }}</div>
                            <div class="small text-muted">{{ Str::limit($workshop->description, 50) }}</div>
                        </td>
                        <td>
                            <div class="small fw-semibold">{{ ($workshop->starts_at ?? \Carbon\Carbon::parse($workshop->date))->format('M d, Y') }}</div>
                            @if($workshop->starts_at)
                                <div class="small text-muted">{{ $workshop->starts_at->format('h:i A') }}</div>
                            @else
                                <div class="small text-muted">TBD</div>
                            @endif
                        </td>
                        <td class="small">
                            @if($workshop->location_link)
                                <a href="{{ $workshop->location_link }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-geo-alt"></i> {{ $workshop->location }}
                                </a>
                            @else
                                {{ $workshop->location }}
                            @endif
                        </td>
                        <td>
                            <div class="small">Max: {{ $workshop->max_seats }}</div>
                        </td>
                        <td>
                            @if($workshop->completed_at)
                                <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 px-3" style="background-color: rgba(111, 66, 193, 0.1) !important; color: #6f42c1 !important; border-color: rgba(111, 66, 193, 0.25) !important;">Completed</span>
                            @elseif($workshop->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                @if(!$workshop->completed_at)
                                    <form action="{{ route('admin.workshops.complete', $workshop) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this workshop as completed? This will generate and send certificates to all checked-in attendees.')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Complete & Send Certificates">
                                            <i class="bi bi-check-circle"></i> Complete
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.workshops.edit', $workshop) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.workshops.destroy', $workshop) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this workshop?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No workshops found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workshops->hasPages())
        <div class="p-4 border-top border-secondary border-opacity-25">
            {{ $workshops->links() }}
        </div>
    @endif
</div>
@endsection
