@extends('layouts.app')

@section('title', 'My Internships')
@section('page-title', 'My Internships')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">{{ $internships->count() }} internship(s) posted</span>
    <a href="{{ route('company.internships.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Post New Internship
    </a>
</div>

<div class="card p-4">
    @if($internships->isEmpty())
        <p class="text-muted text-center my-4">
            You haven't posted any internships yet.
            <a href="{{ route('company.internships.create') }}">Create your first one</a>.
        </p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="small text-muted">
                        <th>Title</th>
                        <th>Type</th>
                        <th>Deadline</th>
                        <th>Vacancies</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($internships as $internship)
                        <tr>
                            <td class="fw-semibold">{{ $internship->TITLE }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $internship->INTERNSHIP_TYPE }}</span></td>
                            <td class="small">{{ \Carbon\Carbon::parse($internship->APPLICATION_DEADLINE)->format('d M Y') }}</td>
                            <td>{{ $internship->VACANCIES }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $internship->applications_count }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('company.internships.status', $internship->INTERNSHIP_ID) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm"
                                            onchange="this.form.submit()" style="width: auto;">
                                        @foreach(['Open','Closed','Paused'] as $status)
                                            <option value="{{ $status }}"
                                                {{ $internship->STATUS === $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('company.internships.edit', $internship->INTERNSHIP_ID) }}"
                                   class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('company.internships.destroy', $internship->INTERNSHIP_ID) }}"
                                      class="d-inline" onsubmit="return confirm('Delete this internship? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection