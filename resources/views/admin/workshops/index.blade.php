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
                            <div class="small fw-semibold">{{ \Carbon\Carbon::parse($workshop->date)->format('M d, Y') }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($workshop->date)->format('H:i') }}</div>
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
                            @if($workshop->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
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
