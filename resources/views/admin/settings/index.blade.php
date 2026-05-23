@extends('layouts.admin')

@section('title', 'General Settings')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold mb-1">General Settings</h2>
    <p class="text-muted mb-0">Configure your site logo, slider images, and footer.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Logo Section -->
            <div class="content-card p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-image me-2 text-primary"></i> Site Logo</h4>
                <div class="row align-items-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        @if($settings['logo'])
                            <img src="{{ Storage::url($settings['logo']) }}" class="img-thumbnail bg-light" style="max-height: 100px;">
                        @else
                            <div class="img-thumbnail bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                                <span class="text-muted small">No Logo</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Upload New Logo</label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                        <div class="form-text">Recommended size: 200x50px. Transparent PNG preferred.</div>
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="content-card p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-fonts me-2 text-primary"></i> Footer Text</h4>
                <div class="mb-3">
                    <label class="form-label">Single line footer text</label>
                    <input type="text" name="footer_text" class="form-control @error('footer_text') is-invalid @enderror" value="{{ old('footer_text', $settings['footer_text']) }}">
                    @error('footer_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Slider Section -->
            <div class="content-card p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-images me-2 text-primary"></i> Slider Images</h4>
                <div class="mb-3">
                    <label class="form-label">Add Slider Images (Multiple)</label>
                    <input type="file" name="slider_images[]" class="form-control @error('slider_images.*') is-invalid @enderror" multiple>
                    <div class="form-text">Upload high-quality images for the registration background slider.</div>
                    @error('slider_images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if(!empty($settings['slider_images']))
                    <div class="row mt-4">
                        @foreach($settings['slider_images'] as $image)
                            <div class="col-md-3 mb-3">
                                <div class="position-relative group">
                                    <img src="{{ Storage::url($image) }}" class="img-thumbnail w-100" style="height: 120px; object-fit: cover;">
                                    <div class="position-absolute top-0 end-0 p-1">
                                        <button type="button" class="btn btn-danger btn-sm p-1 rounded-circle" onclick="removeImage('{{ $image }}')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="content-card p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-camera-reels me-2 text-primary"></i> Success Pass Background</h4>
                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        @if($settings['success_background'])
                            <img src="{{ Storage::url($settings['success_background']) }}" class="img-thumbnail w-100" style="height: 140px; object-fit: cover;">
                        @else
                            <div class="img-thumbnail bg-light d-flex align-items-center justify-content-center" style="height: 140px;">
                                <span class="text-muted small">No Image</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Upload Background Image</label>
                        <input type="file" name="success_background" class="form-control @error('success_background') is-invalid @enderror">
                        <div class="form-text">Used as the background for the success pass page. If empty, slider images are used.</div>
                        @error('success_background') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-5 py-3 rounded-4 fw-bold shadow">
                    <i class="bi bi-cloud-arrow-up me-2"></i> SAVE ALL SETTINGS
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="content-card p-4">
            <h5 class="fw-bold mb-3">Usage Instructions</h5>
            <ul class="text-muted small">
                <li><strong>Logo</strong> appears on the top of registration form and email templates.</li>
                <li><strong>Footer</strong> text is displayed at the bottom of every public page.</li>
                <li><strong>Slider Images</strong> rotate in the background of the registration portal.</li>
            </ul>
        </div>
    </div>
</div>

<form id="remove-slider-form" action="{{ route('admin.settings.remove-slider') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="path" id="remove-image-path">
</form>

<script>
function removeImage(path) {
    if(confirm('Are you sure you want to remove this slider image?')) {
        document.getElementById('remove-image-path').value = path;
        document.getElementById('remove-slider-form').submit();
    }
}
</script>

<style>
.group:hover .btn-danger { opacity: 1; }
.btn-danger { opacity: 0.8; transition: opacity 0.2s; }
</style>
@endsection
