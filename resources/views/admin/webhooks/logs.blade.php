@extends('layouts.admin')

@section('title', 'Webhook Logs')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Webhook Logs</h2>
        <p class="text-muted mb-0">Monitor all outgoing webhook delivery attempts and statuses.</p>
    </div>
</div>

<!-- Filters -->
<div class="content-card mb-4 p-4">
    <form action="{{ route('admin.webhooks.logs') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
            <select name="status" class="form-select border-0 bg-light rounded-3">
                <option value="">All Statuses</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success (2xx)</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Webhook Config</label>
            <select name="webhook_config_id" class="form-select border-0 bg-light rounded-3">
                <option value="">All Configs</option>
                @foreach($configs as $config)
                    <option value="{{ $config->id }}" {{ request('webhook_config_id') == $config->id ? 'selected' : '' }}>
                        {{ $config->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-light w-100" title="Reset Filters">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Timestamp</th>
                    <th>Webhook Config</th>
                    <th>Event / Target</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold small text-dark">{{ $log->sent_at->format('M d, H:i:s') }}</div>
                            <div class="text-muted small">{{ $log->sent_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $log->webhookConfig->name ?? 'Deleted Config' }}</div>
                            <div class="badge bg-light text-dark border small fw-normal">{{ strtoupper($log->webhookConfig->type ?? 'N/A') }}</div>
                        </td>
                        <td>
                            @if($log->registration)
                                <div class="small fw-bold">Registration: {{ $log->registration->full_name }}</div>
                                <div class="small text-muted">ID: #{{ $log->registration->id }}</div>
                            @else
                                <div class="small text-primary">System / OTP / Test</div>
                                <div class="small text-muted">{{ $log->payload['event'] ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($log->response_status >= 200 && $log->response_status < 300)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                    {{ $log->response_status }} OK
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">
                                    {{ $log->response_status }} Error
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.webhooks.log-show', $log) }}" class="btn btn-sm btn-light border">
                                <i class="bi bi-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No webhook logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="p-4 border-top">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
