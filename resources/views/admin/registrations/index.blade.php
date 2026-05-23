@extends('layouts.admin')

@section('title', 'Registration Management')

@section('content')
<!-- Summary Stats Bar -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0" style="width: 40px; height: 40px; font-size: 1rem;">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <div class="small text-muted">Total Entries</div>
                <div class="fw-bold" id="statTotal">{{ $scopedStats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0 text-warning" style="width: 40px; height: 40px; font-size: 1rem; background: rgba(245, 158, 11, 0.1);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="small text-muted">Waiting List</div>
                <div class="fw-bold" id="statWaiting">{{ $scopedStats['waiting'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0 text-info" style="width: 40px; height: 40px; font-size: 1rem; background: rgba(6, 182, 212, 0.1);">
                <i class="bi bi-patch-check"></i>
            </div>
            <div>
                <div class="small text-muted">Approved</div>
                <div class="fw-bold" id="statApproved">{{ $scopedStats['approved'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-3 d-flex align-items-center gap-3">
            <div class="stat-icon m-0 text-success" style="width: 40px; height: 40px; font-size: 1rem; background: rgba(16, 185, 129, 0.1);">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="small text-muted">Checked In</div>
                <div class="fw-bold" id="statCheckedIn">{{ $scopedStats['checked_in'] }}</div>
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
                <input type="text" name="search" class="form-control border-start-0 border-secondary bg-transparent" placeholder="Search by name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted small">Workshop</label>
            <select name="workshop_id" class="form-select bg-transparent border-secondary">
                <option value="">All Workshops</option>
                @foreach($workshops as $workshop)
                    <option value="{{ $workshop->id }}" {{ request('workshop_id') == $workshop->id ? 'selected' : '' }}>
                        {{ $workshop->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-muted small">Status</label>
            <select name="status" class="form-select bg-transparent border-secondary">
                <option value="">All Status</option>
                <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting List</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
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
    <div class="p-4 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 border-bottom border-secondary border-opacity-25">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
            <h5 class="fw-bold mb-0">Attendee List</h5>
            <div class="small text-muted">
                <span id="bulkSelectedCount">0</span> selected
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100 w-lg-auto">
            <form id="bulkActionsForm" method="POST" action="{{ route('admin.registrations.bulk') }}" class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                @csrf
                <input type="hidden" name="redirect" value="{{ url()->full() }}">
                <select id="bulkAction" name="action" class="form-select form-select-sm border-secondary bg-transparent" required>
                    <option value="" selected disabled>Bulk action...</option>
                    <option value="approve">Approve</option>
                    <option value="checkin">Mark Checked-in</option>
                    <option value="uncheckin">Undo Check-in</option>
                    <option value="resend_webhook">Resend Webhook</option>
                    <option value="delete">Delete</option>
                </select>
                <button id="bulkApplyBtn" type="submit" class="btn btn-sm btn-primary" disabled>Apply</button>
            </form>
            <a href="{{ route('admin.registrations.export', request()->all()) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-download me-2"></i> Export to CSV
            </a>
        </div>
    </div>
    <div class="d-block d-md-none p-3">
        @forelse($registrations as $reg)
            <div class="content-card p-3 mb-3" data-registration-id="{{ $reg->id }}">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.85rem;">{{ substr($reg->full_name, 0, 1) }}</div>
                        <div class="overflow-hidden">
                            <a href="{{ route('admin.registrations.show', $reg->id) }}" class="fw-bold text-decoration-none attendee-link d-block text-truncate">
                                {{ $reg->full_name }}
                            </a>
                            <div class="small text-muted text-break">{{ $reg->phone }}</div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 d-flex align-items-start gap-2">
                        <input class="form-check-input mt-1" type="checkbox" data-bulk-id="{{ $reg->id }}" aria-label="Select {{ $reg->full_name }}">
                        <div data-role="status">
                            @if($reg->status === 'approved')
                                @if($reg->checked_in_at)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                        <i class="bi bi-check2-all me-1"></i> Checked In
                                    </span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3">
                                        <i class="bi bi-patch-check me-1"></i> Approved
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">
                                    <i class="bi bi-hourglass-split me-1"></i> Waiting
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="small text-muted" data-role="created-at">
                        <i class="bi bi-clock me-1"></i> {{ $reg->created_at->format('M d, H:i') }}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($reg->status === 'pending')
                            <form method="POST" action="{{ route('admin.registrations.approve', $reg->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.registrations.show', $reg->id) }}?edit=1" class="btn btn-sm btn-outline-secondary" aria-label="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="{{ route('admin.registrations.show', $reg->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <form method="POST"
                              action="{{ route('admin.registrations.destroy', $reg->id) }}"
                              onsubmit="return confirm('Delete registration for {{ addslashes($reg->full_name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                No registrations found matching your filters.
            </div>
        @endforelse
    </div>
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 40px;">
                        <input id="bulkSelectAll" class="form-check-input" type="checkbox" aria-label="Select all">
                    </th>
                    <th>Attendee</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                    <tr data-registration-id="{{ $reg->id }}">
                        <td class="ps-4">
                            <input class="form-check-input" type="checkbox" data-bulk-id="{{ $reg->id }}" aria-label="Select {{ $reg->full_name }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">{{ substr($reg->full_name, 0, 1) }}</div>
                                <div>
                                    <a href="{{ route('admin.registrations.show', $reg->id) }}" class="fw-bold text-decoration-none attendee-link">
                                        {{ $reg->full_name }}
                                    </a>
                                </div>
                            </div>
                        <td class="small text-muted">{{ $reg->phone }}</td>
                        <td>
                            <div class="small" data-role="created-at">{{ $reg->created_at->format('M d, H:i') }}</div>
                        </td>
                        <td data-role="status">
                            @if($reg->status === 'approved')
                                @if($reg->checked_in_at)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                        <i class="bi bi-check2-all me-1"></i> Checked In
                                    </span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3">
                                        <i class="bi bi-patch-check me-1"></i> Approved
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">
                                    <i class="bi bi-hourglass-split me-1"></i> Waiting List
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4 d-flex gap-2 justify-content-end align-items-center">
                            @if($reg->status === 'pending')
                                <form method="POST" action="{{ route('admin.registrations.approve', $reg->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.registrations.show', $reg->id) }}?edit=1" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
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

<script>
    (function () {
        const bulkForm = document.getElementById('bulkActionsForm');
        const bulkAction = document.getElementById('bulkAction');
        const bulkApplyBtn = document.getElementById('bulkApplyBtn');
        const bulkSelectedCount = document.getElementById('bulkSelectedCount');
        const bulkSelectAll = document.getElementById('bulkSelectAll');

        function getAllIds() {
            const all = Array.from(document.querySelectorAll('input[type="checkbox"][data-bulk-id]'))
                .map((cb) => String(cb.getAttribute('data-bulk-id') || '').trim())
                .filter((v) => v && /^\d+$/.test(v));
            return Array.from(new Set(all));
        }

        function getSelectedIds() {
            const selected = Array.from(document.querySelectorAll('input[type="checkbox"][data-bulk-id]:checked'))
                .map((cb) => String(cb.getAttribute('data-bulk-id') || '').trim())
                .filter((v) => v && /^\d+$/.test(v));
            return Array.from(new Set(selected));
        }

        function setAll(checked) {
            document.querySelectorAll('input[type="checkbox"][data-bulk-id]').forEach((cb) => {
                cb.checked = checked;
            });
        }

        function updateBulkUi() {
            const all = getAllIds();
            const selected = getSelectedIds();

            if (bulkSelectedCount) bulkSelectedCount.textContent = String(selected.length);
            if (bulkApplyBtn) bulkApplyBtn.disabled = selected.length === 0 || !bulkAction?.value;

            if (bulkSelectAll) {
                bulkSelectAll.checked = all.length > 0 && selected.length === all.length;
                bulkSelectAll.indeterminate = selected.length > 0 && selected.length < all.length;
            }
        }

        if (bulkSelectAll) {
            bulkSelectAll.addEventListener('change', () => {
                setAll(bulkSelectAll.checked);
                updateBulkUi();
            });
        }

        document.addEventListener('change', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            if (target.matches('input[type="checkbox"][data-bulk-id]')) updateBulkUi();
            if (target === bulkAction) updateBulkUi();
        });

        if (bulkForm) {
            bulkForm.addEventListener('submit', (e) => {
                const selected = getSelectedIds();
                if (!selected.length) {
                    e.preventDefault();
                    return;
                }

                const actionValue = String(bulkAction?.value || '');
                if (actionValue === 'delete') {
                    if (!confirm('Delete selected registrations? This cannot be undone.')) {
                        e.preventDefault();
                        return;
                    }
                }

                bulkForm.querySelectorAll('input[name="ids[]"]').forEach((el) => el.remove());
                selected.forEach((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkForm.appendChild(input);
                });
            });
        }

        updateBulkUi();

        function getVisibleRegistrationIds() {
            const ids = Array.from(document.querySelectorAll('[data-registration-id]'))
                .map((el) => Number(el.getAttribute('data-registration-id')))
                .filter((id) => Number.isFinite(id) && id > 0);
            return Array.from(new Set(ids));
        }

        function renderStatusBadge(data) {
            if (data.status === 'approved') {
                if (data.checked_in_at) {
                    return `
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                            <i class="bi bi-check2-all me-1"></i> Checked In
                        </span>
                    `;
                }
                return `
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3">
                        <i class="bi bi-patch-check me-1"></i> Approved
                    </span>
                `;
            }

            return `
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">
                    <i class="bi bi-hourglass-split me-1"></i> Waiting List
                </span>
            `;
        }

        function applyUpdates(registrations) {
            document.querySelectorAll('[data-registration-id]').forEach((row) => {
                const id = String(row.getAttribute('data-registration-id') || '');
                const data = registrations?.[id];
                if (!data) return;

                const statusEl = row.querySelector('[data-role="status"]');
                if (statusEl) {
                    const nextKey = `${data.status}:${data.checked_in_at ? '1' : '0'}`;
                    const currentKey = row.getAttribute('data-live-status-key');
                    if (currentKey !== nextKey) {
                        statusEl.innerHTML = renderStatusBadge(data);
                        row.setAttribute('data-live-status-key', nextKey);
                    }
                }
            });
        }

        function applyStats(stats) {
            if (!stats) return;
            const elTotal = document.getElementById('statTotal');
            const elWaiting = document.getElementById('statWaiting');
            const elApproved = document.getElementById('statApproved');
            const elCheckedIn = document.getElementById('statCheckedIn');

            if (elTotal) elTotal.textContent = String(stats.total ?? '');
            if (elWaiting) elWaiting.textContent = String(stats.waiting ?? '');
            if (elApproved) elApproved.textContent = String(stats.approved ?? '');
            if (elCheckedIn) elCheckedIn.textContent = String(stats.checked_in ?? '');
        }

        async function pollStats() {
            const res = await fetch(`{{ route('admin.registrations.live-stats') }}${window.location.search}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) return;

            const json = await res.json();
            if (!json?.success) return;

            applyStats(json.stats);
        }

        async function poll() {
            const ids = getVisibleRegistrationIds();
            if (!ids.length) return;

            const params = new URLSearchParams();
            params.set('ids', ids.join(','));

            const res = await fetch(`{{ route('admin.registrations.live') }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) return;

            const json = await res.json();
            if (!json?.success) return;

            applyUpdates(json.registrations);
        }

        let timer = null;
        function start() {
            if (timer) return;
            poll();
            pollStats();
            timer = window.setInterval(() => {
                poll();
                pollStats();
            }, 5000);
        }

        function stop() {
            if (!timer) return;
            window.clearInterval(timer);
            timer = null;
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stop();
            else start();
        });

        start();
    })();
</script>

<style>
    .form-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='gray' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); }
    .pagination { margin: 0; justify-content: center; }
    .page-link { background: transparent; border-color: var(--border); color: var(--text-muted); }
    .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: white; }
    .attendee-link { color: var(--text-main); }
    .attendee-link:hover { color: var(--primary); }
</style>
@endsection
