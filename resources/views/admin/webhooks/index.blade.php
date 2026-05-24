@extends('layouts.admin')

@section('title', 'Webhook Configurations')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Webhook Configs</h2>
        <p class="text-muted mb-0">External endpoints for registration notifications.</p>
    </div>
    <a href="{{ route('admin.webhooks.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Add Config
    </a>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Name</th>
                    <th>Type</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($configs as $config)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $config->name }}</div>
                        </td>
                        <td>
                            @if($config->type === 'otp')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">OTP</span>
                            @elseif($config->type === 'workshop_link')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">Workshop Link</span>
                            @else
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3">Registration</span>
                            @endif
                        </td>
                        <td>
                            <code class="small">{{ Str::limit($config->url, 50) }}</code>
                        </td>
                        <td>
                            @if($config->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3">Inactive</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $config->created_at->format('M d, Y') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-info test-webhook-btn" data-id="{{ $config->id }}">
                                    <i class="bi bi-send"></i> Test
                                </button>
                                <a href="{{ route('admin.webhooks.edit', $config) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.webhooks.destroy', $config) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this configuration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div id="test-result-{{ $config->id }}" class="small mt-1 d-none"></div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No webhook configurations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($configs->hasPages())
        <div class="p-4 border-top border-secondary border-opacity-25">
            {{ $configs->links() }}
        </div>
    @endif
</div>

<script>
document.querySelectorAll('.test-webhook-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const resultDiv = document.getElementById(`test-result-${id}`);
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
        
        resultDiv.classList.remove('d-none', 'text-success', 'text-danger');
        resultDiv.classList.add('text-muted');
        resultDiv.textContent = 'Sending ping...';

        fetch(`/admin/webhooks/${id}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send"></i> Test';
            
            resultDiv.classList.remove('text-muted');
            if (data.success) {
                resultDiv.classList.add('text-success');
                resultDiv.textContent = `Success: HTTP ${data.status}`;
            } else {
                resultDiv.classList.add('text-danger');
                resultDiv.textContent = `Failed: HTTP ${data.status}`;
            }
        })
        .catch(error => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send"></i> Test';
            resultDiv.classList.add('text-danger');
            resultDiv.textContent = 'Error: Connection failed';
        });
    });
});
</script>
@endsection
