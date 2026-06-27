@extends('layouts.app')

@section('title', 'My Applications')
@section('page-title', 'My Applications')

@section('content')

<div class="card p-4">
    @if($applications->isEmpty())
        <p class="text-muted text-center my-4">
            You haven't applied to any internships yet.
            <a href="{{ route('internships.index') }}">Browse internships</a>.
        </p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="small text-muted">
                        <th>Internship</th>
                        <th>Company</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        <tr>
                            <td class="fw-semibold">{{ $app->internship->TITLE }}</td>
                            <td>{{ $app->internship->company->COMPANY_NAME }}</td>
                            <td class="small">{{ \Carbon\Carbon::parse($app->APPLIED_AT)->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-{{ strtolower($app->STATUS) }}">
                                    {{ $app->STATUS }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('internships.show', $app->INTERNSHIP_ID) }}"
                                   class="btn btn-sm btn-outline-dark">View</a>
                                @if($app->STATUS === 'Pending')
                                    <form method="POST" action="{{ route('student.applications.destroy', $app->APPLICATION_ID) }}"
                                          class="d-inline" onsubmit="return confirm('Withdraw this application?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Withdraw</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection