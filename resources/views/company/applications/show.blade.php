@extends('layouts.app')

@section('title', 'Application Details')
@section('page-title', 'Application Details')

@section('content')

<div class="row g-4">
    <div class="col-md-8">
        <div class="card p-4">
            <h5 class="fw-bold mb-1">{{ $application->student->FIRST_NAME }} {{ $application->student->LAST_NAME }}</h5>
            <p class="text-muted small mb-3">
                Applied for: <strong>{{ $application->internship->TITLE }}</strong>
                on {{ \Carbon\Carbon::parse($application->APPLIED_AT)->format('d M Y, h:i A') }}
            </p>

            <hr>

            <h6 class="fw-bold">Cover Letter</h6>
            <p class="small">
                {{ $application->COVER_LETTER ?: 'No cover letter submitted.' }}
            </p>

            <h6 class="fw-bold mt-4">Skills</h6>
            <div class="d-flex flex-wrap gap-2">
                @forelse($application->student->studentSkills as $ss)
                    <span class="badge bg-light text-dark border">
                        {{ $ss->skill->SKILL_NAME }} ({{ $ss->PROFICIENCY }})
                    </span>
                @empty
                    <span class="text-muted small">No skills listed.</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Applicant Info</h6>
            <ul class="list-unstyled small">
                <li class="mb-2"><i class="bi bi-mortarboard text-muted"></i> {{ $application->student->UNIVERSITY }}</li>
                <li class="mb-2"><i class="bi bi-journal-bookmark text-muted"></i> {{ $application->student->DEPARTMENT }}</li>
                @if($application->student->GPA)
                    <li class="mb-2"><i class="bi bi-award text-muted"></i> GPA: {{ $application->student->GPA }}</li>
                @endif
                @if($application->student->PHONE)
                    <li class="mb-2"><i class="bi bi-telephone text-muted"></i> {{ $application->student->PHONE }}</li>
                @endif
            </ul>

            @if($application->student->CV_FILE_PATH)
                <a href="{{ Storage::url($application->student->CV_FILE_PATH) }}" target="_blank"
                   class="btn btn-outline-dark w-100 mb-3">
                    <i class="bi bi-file-earmark-pdf"></i> View CV
                </a>
            @endif

            <form method="POST" action="{{ route('company.applications.status', $application->APPLICATION_ID) }}">
                @csrf
                @method('PATCH')
                <label class="form-label small fw-semibold">Update Status</label>
                <select name="status" class="form-select mb-2">
                    @foreach(['Pending','Reviewed','Shortlisted','Interview','Accepted','Rejected'] as $status)
                        <option value="{{ $status }}" {{ $application->STATUS === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary w-100">Update</button>
            </form>
        </div>
    </div>
</div>

@endsection