@extends('layouts.app')

@section('title', 'Application Details')
@section('page-title', 'Application Details')

@section('content')

<div class="row g-4">
    {{-- Left: Application Info --}}
    <div class="col-md-8">
        <div class="card p-4 mb-4">
            <h5 class="fw-bold mb-1">
                {{ $application->student->FIRST_NAME }} {{ $application->student->LAST_NAME }}
            </h5>
            <p class="text-muted small mb-3">
                Applied for: <strong>{{ $application->internship->TITLE }}</strong>
                on {{ \Carbon\Carbon::parse($application->APPLIED_AT)->format('d M Y, h:i A') }}
            </p>

            <hr>

            <h6 class="fw-bold">Cover Letter</h6>
            <p class="small">{{ $application->COVER_LETTER ?: 'No cover letter submitted.' }}</p>

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

        {{-- Interview Details (if scheduled) --}}
        @if($application->interview)
            <div class="card p-4 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-camera-video text-primary me-2"></i>Scheduled Interview
                    </h6>
                    <form method="POST"
                          action="{{ route('company.interviews.destroy', $application->APPLICATION_ID) }}"
                          onsubmit="return confirm('Cancel this interview?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Cancel Interview
                        </button>
                    </form>
                </div>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-calendar-event text-muted me-2"></i>
                        {{ \Carbon\Carbon::parse($application->interview->SCHEDULED_DATE)->format('l, d M Y') }}
                        at {{ $application->interview->SCHEDULED_TIME }}
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-laptop text-muted me-2"></i>
                        {{ $application->interview->INTERVIEW_MODE }}
                    </li>
                    @if($application->interview->LOCATION_OR_LINK)
                        <li class="mb-2">
                            <i class="bi bi-geo-alt text-muted me-2"></i>
                            {{ $application->interview->LOCATION_OR_LINK }}
                        </li>
                    @endif
                    @if($application->interview->NOTES)
                        <li>
                            <i class="bi bi-sticky text-muted me-2"></i>
                            {{ $application->interview->NOTES }}
                        </li>
                    @endif
                </ul>
                <a href="{{ route('company.interviews.create', $application->APPLICATION_ID) }}"
                   class="btn btn-sm btn-outline-primary mt-3">
                    <i class="bi bi-pencil"></i> Reschedule
                </a>
            </div>
        @endif
    </div>

    {{-- Right: Sidebar --}}
    <div class="col-md-4">
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">Applicant Info</h6>
            <ul class="list-unstyled small">
                <li class="mb-2">
                    <i class="bi bi-mortarboard text-muted"></i>
                    {{ $application->student->UNIVERSITY }}
                </li>
                <li class="mb-2">
                    <i class="bi bi-journal-bookmark text-muted"></i>
                    {{ $application->student->DEPARTMENT }}
                </li>
                @if($application->student->GPA)
                    <li class="mb-2">
                        <i class="bi bi-award text-muted"></i>
                        GPA: {{ $application->student->GPA }}
                    </li>
                @endif
                @if($application->student->PHONE)
                    <li class="mb-2">
                        <i class="bi bi-telephone text-muted"></i>
                        {{ $application->student->PHONE }}
                    </li>
                @endif
            </ul>

            @if($application->student->CV_FILE_PATH)
                <a href="{{ Storage::url($application->student->CV_FILE_PATH) }}"
                   target="_blank" class="btn btn-outline-dark w-100 mb-3">
                    <i class="bi bi-file-earmark-pdf"></i> View CV
                </a>
            @endif

            {{-- Status Update --}}
            <form method="POST"
                  action="{{ route('company.applications.status', $application->APPLICATION_ID) }}">
                @csrf
                @method('PATCH')
                <label class="form-label small fw-semibold">Update Status</label>
                <select name="status" class="form-select mb-2">
                    @foreach(['Pending','Reviewed','Shortlisted','Interview','Accepted','Rejected'] as $status)
                        <option value="{{ $status }}"
                            {{ $application->STATUS === $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary w-100">Update Status</button>
            </form>
        </div>

        {{-- Schedule Interview Button --}}
        @if(in_array($application->STATUS, ['Shortlisted', 'Interview']))
            <div class="card p-4">
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-calendar-check text-success me-2"></i>Interview
                </h6>
                <p class="text-muted small">
                    {{ $application->interview ? 'Reschedule or cancel this interview.' : 'Schedule an interview for this applicant.' }}
                </p>
                <a href="{{ route('company.interviews.create', $application->APPLICATION_ID) }}"
                   class="btn btn-success w-100">
                    <i class="bi bi-calendar-plus"></i>
                    {{ $application->interview ? 'Reschedule Interview' : 'Schedule Interview' }}
                </a>
            </div>
        @endif
    </div>
</div>

@endsection