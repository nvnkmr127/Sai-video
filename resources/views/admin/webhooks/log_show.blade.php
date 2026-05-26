@extends('layouts.admin')

@section('title', 'Webhook Log Detail')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-light btn-sm mb-3">
        <i class="bi bi-arrow-left"></i> Back to Logs
    </a>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Webhook Attempt #{{ $log->id }}</h2>
            <p class="text-muted mb-0">Detailed breakdown of the transmission and response.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.webhooks.log-replay', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to replay this webhook log?');">
                @csrf
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6">
                    <i class="bi bi-arrow-clockwise me-1"></i> Replay Webhook
                </button>
            </form>
            @if($log->response_status >= 200 && $log->response_status < 300)
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 fs-6">
                    <i class="bi bi-check-circle-fill me-2"></i> Success ({{ $log->response_status }})
                </span>
            @else
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-4 py-2 fs-6">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Failed ({{ $log->response_status }})
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card p-4 h-100">
            <h5 class="fw-bold mb-4">Meta Data</h5>
            <div class="mb-4">
                <label class="small text-muted fw-bold text-uppercase d-block mb-1">Timestamp</label>
                <div class="text-dark">{{ $log->sent_at->format('M d, Y H:i:s') }}</div>
                <div class="small text-muted">{{ $log->sent_at->diffForHumans() }}</div>
            </div>
            <div class="mb-4">
                <label class="small text-muted fw-bold text-uppercase d-block mb-1">Webhook Config</label>
                <div class="text-dark fw-bold">{{ $log->webhookConfig->name ?? 'N/A' }}</div>
                <div class="small text-muted text-break">{{ $log->webhookConfig->url ?? 'URL missing' }}</div>
            </div>
            @if($log->registration)
            <div class="mb-4">
                <label class="small text-muted fw-bold text-uppercase d-block mb-1">Associated Attendee</label>
                <a href="{{ route('admin.registrations.index', ['search' => $log->registration->full_name]) }}" class="text-primary text-decoration-none fw-bold">
                    {{ $log->registration->full_name }}
                </a>
                <div class="small text-muted">{{ $log->registration->phone }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card p-4 mb-4">
            <h5 class="fw-bold mb-4">Payload Sent</h5>
            <div class="bg-light rounded-3 p-3">
                <pre class="mb-0 text-dark" style="font-family: 'Courier New', Courier, monospace; font-size: 0.85rem;">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>

        <div class="content-card p-4">
            <h5 class="fw-bold mb-4">Response Body</h5>
            <div class="bg-dark rounded-3 p-3">
                <pre class="mb-0 text-info" style="font-family: 'Courier New', Courier, monospace; font-size: 0.85rem;">{{ $log->response_body ?: 'No response body returned.' }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
