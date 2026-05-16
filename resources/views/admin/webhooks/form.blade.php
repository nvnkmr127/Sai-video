@extends('layouts.admin')

@section('title', $config->exists ? 'Edit Webhook Config' : 'Create Webhook Config')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.webhooks.index') }}" class="text-decoration-none text-muted small fw-bold">
        <i class="bi bi-arrow-left"></i> BACK TO LIST
    </a>
    <h2 class="fw-bold mt-2">{{ $config->exists ? 'Edit Webhook Config' : 'Create Webhook Config' }}</h2>
</div>

<div class="card col-lg-8">
    <div class="card-body p-4">
        <form action="{{ $config->exists ? route('admin.webhooks.update', $config) : route('admin.webhooks.store') }}" method="POST">
            @csrf
            @if($config->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label fw-bold">Endpoint Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $config->name) }}" placeholder="e.g. Production Webhook" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="type" class="form-label fw-bold">Webhook Type</label>
                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                    <option value="registration" {{ old('type', $config->type) == 'registration' ? 'selected' : '' }}>Registration Webhook (JSON payload)</option>
                    <option value="otp" {{ old('type', $config->type) == 'otp' ? 'selected' : '' }}>OTP Webhook (SMS/WhatsApp Provider)</option>
                </select>
                <div class="form-text mt-2">
                    <div class="d-flex gap-2 align-items-center mb-1">
                        <span class="badge bg-primary-subtle text-primary small">Registration</span>
                        <span class="small text-muted">Sends <code>registration.pending</code> (on signup) and <code>registration.approved</code> (on approval).</span>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-info-subtle text-info small">OTP</span>
                        <span class="small text-muted">Sends <code>test.ping</code> or <code>otp.send</code> for phone verification.</span>
                    </div>
                </div>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="url" class="form-label fw-bold">Target URL</label>
                <input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $config->url) }}" placeholder="https://your-server.com/webhook" required>
                <div class="form-text">Must be a valid HTTPS URL for production.</div>
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="secret_token" class="form-label fw-bold">Secret Token</label>
                <div class="input-group">
                    <input type="password" class="form-control @error('secret_token') is-invalid @enderror" id="secret_token" name="secret_token" value="{{ old('secret_token', $config->secret_token) }}" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleTokenBtn"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-outline-secondary" type="button" id="generateTokenBtn">Generate</button>
                    @error('secret_token')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-text">This secret is sent in the <code>X-Webhook-Secret</code> header.</div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $config->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="is_active">Is Active (Deliver notifications)</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    {{ $config->exists ? 'Update Config' : 'Save Configuration' }}
                </button>
                <a href="{{ route('admin.webhooks.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('toggleTokenBtn').addEventListener('click', function() {
    const input = document.getElementById('secret_token');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

document.getElementById('generateTokenBtn').addEventListener('click', function() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let token = '';
    for (let i = 0; i < 32; i++) {
        token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('secret_token').value = token;
});
</script>
@endsection
