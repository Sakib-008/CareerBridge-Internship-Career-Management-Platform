@extends('layouts.app')

@section('title', 'Schedule Interview')
@section('page-title', 'Schedule Interview')

@section('content')

<div class="card p-4" style="max-width: 650px;">
    <div class="mb-4">
        <h6 class="fw-bold mb-1">{{ $application->student->FIRST_NAME }} {{ $application->student->LAST_NAME }}</h6>
        <p class="text-muted small mb-0">
            Internship: <strong>{{ $application->internship->TITLE }}</strong>
        </p>
    </div>

    @if($existingInterview)
        <div class="alert alert-info small">
            <i class="bi bi-info-circle"></i>
            An interview is already scheduled. Submitting this form will reschedule it.
        </div>
    @endif

    <form method="POST" action="{{ route('company.interviews.store', $application->APPLICATION_ID) }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Date</label>
                <input type="date" name="scheduled_date" class="form-control" required
                       value="{{ old('scheduled_date', $existingInterview?->SCHEDULED_DATE
                           ? \Carbon\Carbon::parse($existingInterview->SCHEDULED_DATE)->format('Y-m-d')
                           : '') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Time</label>
                <input type="time" name="scheduled_time" class="form-control" required
                       value="{{ old('scheduled_time', $existingInterview?->SCHEDULED_TIME ?? '') }}">
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Interview Mode</label>
                <select name="interview_mode" class="form-select" required>
                    @foreach(['In-person','Video','Phone'] as $mode)
                        <option value="{{ $mode }}"
                            {{ old('interview_mode', $existingInterview?->INTERVIEW_MODE ?? '') === $mode ? 'selected' : '' }}>
                            {{ $mode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">
                    Location / Meeting Link
                    <span class="text-muted fw-normal">(optional)</span>
                </label>
                <input type="text" name="location_or_link" class="form-control"
                       placeholder="e.g. Office address or Zoom link"
                       value="{{ old('location_or_link', $existingInterview?->LOCATION_OR_LINK ?? '') }}">
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">
                    Notes
                    <span class="text-muted fw-normal">(optional)</span>
                </label>
                <textarea name="notes" rows="3" class="form-control"
                          placeholder="Any special instructions for the candidate...">{{ old('notes', $existingInterview?->NOTES ?? '') }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-calendar-check"></i>
                {{ $existingInterview ? 'Reschedule Interview' : 'Schedule Interview' }}
            </button>
            <a href="{{ route('company.applications.show', $application->APPLICATION_ID) }}"
               class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection