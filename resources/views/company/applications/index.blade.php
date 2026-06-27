@extends('layouts.app')

@section('title', 'Applications')
@section('page-title', 'Manage Applications')

@section('content')

<div class="card p-4 mb-4">
    <form method="GET" action="{{ route('company.applications') }}" class="row g-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold">Internship</label>
            <select name="internship_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Internships</option>
                @foreach($internships as $i)
                    <option value="{{ $i->INTERNSHIP_ID }}" {{ request('internship_id') == $i->INTERNSHIP_ID ? 'selected' : '' }}>
                        {{ $i->TITLE }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach(['Pending','Reviewed','Shortlisted','Interview','Accepted','Rejected'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="card p-4">
    @if($applications->isEmpty())
        <p class="text-muted text-center my-4">No applications match your filters.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="small text-muted">
                        <th>Applicant</th>
                        <th>Internship</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        <tr>
                            <td class="fw-semibold">{{ $app->student->FIRST_NAME }} {{ $app->student->LAST_NAME }}</td>
                            <td>{{ $app->internship->TITLE }}</td>
                            <td class="small">{{ \Carbon\Carbon::parse($app->APPLIED_AT)->format('d M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('company.applications.status', $app->APPLICATION_ID) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm"
                                            onchange="this.form.submit()" style="width: auto;">
                                        @foreach(['Pending','Reviewed','Shortlisted','Interview','Accepted','Rejected'] as $status)
                                            <option value="{{ $status }}" {{ $app->STATUS === $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('company.applications.show', $app->APPLICATION_ID) }}"
                                   class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection