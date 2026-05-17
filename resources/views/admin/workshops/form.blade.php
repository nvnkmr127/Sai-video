@extends('layouts.admin')

@section('title', $workshop->exists ? 'Edit Workshop' : 'Create Workshop')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.workshops.index') }}" class="text-decoration-none text-muted small fw-bold">
        <i class="bi bi-arrow-left"></i> BACK TO LIST
    </a>
    <h2 class="fw-bold mt-2">{{ $workshop->exists ? 'Edit Workshop' : 'Create Workshop' }}</h2>
</div>

<div class="card col-lg-8">
    <div class="card-body p-4">
        <form action="{{ $workshop->exists ? route('admin.workshops.update', $workshop) : route('admin.workshops.store') }}" method="POST">
            @csrf
            @if($workshop->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="title" class="form-label fw-bold">Workshop Title</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $workshop->title) }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $workshop->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date" class="form-label fw-bold">Date & Time</label>
                    <input type="datetime-local" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $workshop->date ? \Carbon\Carbon::parse($workshop->date)->format('Y-m-d\TH:i') : '') }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="max_seats" class="form-label fw-bold">Max Seats</label>
                    <input type="number" class="form-control @error('max_seats') is-invalid @enderror" id="max_seats" name="max_seats" value="{{ old('max_seats', $workshop->max_seats) }}" required min="1">
                    @error('max_seats')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label fw-bold">Location</label>
                <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $workshop->location) }}" required>
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="location_link" class="form-label fw-bold">Location Link (Google Maps / Custom Link)</label>
                <input type="url" class="form-control @error('location_link') is-invalid @enderror" id="location_link" name="location_link" value="{{ old('location_link', $workshop->location_link) }}" placeholder="https://maps.google.com/...">
                @error('location_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $workshop->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="is_active">Is Active (Visible for registration)</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    {{ $workshop->exists ? 'Update Workshop' : 'Create Workshop' }}
                </button>
                <a href="{{ route('admin.workshops.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
