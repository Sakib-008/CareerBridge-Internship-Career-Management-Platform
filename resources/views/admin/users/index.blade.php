@extends('layouts.app')

@section('title', 'Manage Users')
@section('page-title', 'Manage Users')

@section('content')

{{-- Filter --}}
<div class="card p-4 mb-4">
    <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Search Email</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Search by email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                <option value="company" {{ request('role') === 'company' ? 'selected' : '' }}>Company</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr class="small text-muted">
                    <th>Name / Company</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department / Industry</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->DISPLAY_NAME }}</td>
                        <td class="small text-muted">{{ $user->EMAIL }}</td>
                        <td>
                            <span class="badge {{ $user->ROLE === 'student' ? 'bg-primary' : 'bg-success' }}">
                                {{ ucfirst($user->ROLE) }}
                            </span>
                        </td>
                        <td class="small">{{ $user->EXTRA_INFO ?? '—' }}</td>
                        <td class="small">
                            {{ \Carbon\Carbon::parse($user->CREATED_AT)->format('d M Y') }}
                        </td>
                        <td>
                            @if((string)$user->IS_ACTIVE === '1')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST"
                                  action="{{ route('admin.users.toggle', $user->USER_ID) }}"
                                  onsubmit="return confirm('Toggle this account status?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="btn btn-sm {{ (string)$user->IS_ACTIVE === '1' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ (string)$user->IS_ACTIVE === '1' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection