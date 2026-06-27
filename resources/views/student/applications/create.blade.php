@extends('layouts.app')

@section('title', 'Apply')
@section('page-title', 'Apply for Internship')

@section('content')

<div class="card p-4" style="max-width: 700px;">
    <h6 class="fw-bold mb-1">{{ $internship->TITLE }}</h6>
    <p class="text-muted small mb-4">
        {{ $internship->company->COMPANY_NAME }} · {{ $internship->LOCATION }}
    </p>

    <form method="POST" action="{{ route('student.applications.store', $internship->INTERNSHIP_ID) }}">
        @csrf

        <label class="form-label small fw-semibold">Cover Letter (optional)</label>
        <textarea name="cover_letter" rows="8" class="form-control"
                  placeholder="Tell the company why you're a great fit for this internship...">{{ old('cover_letter') }}</textarea>
        <small class="text-muted">Max 2000 characters.</small>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Submit Application
            </button>
            <a href="{{ route('internships.show', $internship->INTERNSHIP_ID) }}" class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection