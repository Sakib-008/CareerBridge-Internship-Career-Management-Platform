@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">
                        Welcome back, {{ $student->FIRST_NAME }} {{ $student->LAST_NAME }}!
                    </h5>
                    <p class="text-muted mb-0">{{ $student->UNIVERSITY }} — {{ $student->DEPARTMENT }}</p>
                </div>
                <a href="{{ route('student.profile') }}" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Profile Completeness --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between mb-2">
                <span class="fw-semibold">Profile Completeness</span>
                <span class="fw-bold {{ $completeness == 100 ? 'text-success' : 'text-warning' }}">
                    {{ $completeness }}%
                </span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar {{ $completeness == 100 ? 'bg-success' : 'bg-warning' }}"
                     role="progressbar" style="width: {{ $completeness }}%"></div>
            </div>
            @if($completeness < 100)
                <small class="text-muted mt-2">
                    <i class="bi bi-info-circle"></i>
                    Complete your profile, add skills, and upload a CV to improve your visibility to companies.
                </small>
            @endif
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row g-4">
    <div class="col-md-4">
        <div class="card stat-card blue p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Skills Added</div>
                    <div class="fs-3 fw-bold">{{ $skillCount }}</div>
                </div>
                <i class="bi bi-stars fs-1 text-primary opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card orange p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Applications Submitted</div>
                    <div class="fs-3 fw-bold">{{ $applicationCount }}</div>
                </div>
                <i class="bi bi-file-earmark-text fs-1 text-warning opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card green p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">CV Status</div>
                    <div class="fs-5 fw-bold">
                        @if($student->CV_FILE_PATH)
                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> Uploaded</span>
                        @else
                            <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Missing</span>
                        @endif
                    </div>
                </div>
                <i class="bi bi-file-earmark-pdf fs-1 text-success opacity-25"></i>
            </div>
        </div>
    </div>
</div>

@endsection