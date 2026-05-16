@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Registrations</h1>
            <p class="subtitle">Manage and check-in attendees.</p>
        </div>
        <form action="{{ route('admin.list') }}" method="GET" style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" placeholder="Search name, email..." value="{{ request('search') }}" style="width: 250px;">
            <button type="submit" class="btn" style="padding: 0.75rem 1rem;">Search</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $reg)
                <tr>
                    <td>
                        <div style="font-weight: 700;">{{ $reg->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $reg->organization ?? 'No Organization' }}</div>
                    </td>
                    <td>
                        <div>{{ $reg->email }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $reg->phone }}</div>
                    </td>
                    <td>
                        @if($reg->checked_in_at)
                            <span class="badge badge-success">Checked In</span>
                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">{{ $reg->checked_in_at->format('M d, H:i') }}</div>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if(!$reg->checked_in_at)
                            <form action="{{ route('admin.checkin', $reg->uuid) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: #10b981;">Check In</button>
                            </form>
                        @else
                            <button class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: var(--text-muted); cursor: not-allowed;" disabled>Done</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">No registrations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 2rem;">
        {{ $registrations->links() }}
    </div>
</div>
@endsection
