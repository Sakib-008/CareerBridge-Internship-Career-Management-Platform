@extends('layouts.app')

@section('title', 'Company Dashboard')
@section('page-title', 'Company Dashboard')

@section('content')

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-building-fill text-success me-2"></i>
                        {{ $company->COMPANY_NAME }}
                    </h5>
                    <p class="text-muted mb-0">{{ $company->INDUSTRY }} — {{ $company->LOCATION }}</p>
                </div>
                <a href="{{ route('company.profile') }}" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </a>
            </div>

            @if(!$profileComplete)
                <div class="alert alert-warning mt-3 mb-0 py-2 small">
                    <i class="bi bi-exclamation-triangle"></i>
                    Complete your company profile to start posting internships with full visibility.
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card blue p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Internships</div>
                    <div class="fs-3 fw-bold">{{ $totalInternships }}</div>
                </div>
                <i class="bi bi-briefcase fs-1 text-primary opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card green p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Open Postings</div>
                    <div class="fs-3 fw-bold">{{ $openInternships }}</div>
                </div>
                <i class="bi bi-door-open fs-1 text-success opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card orange p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Applications</div>
                    <div class="fs-3 fw-bold">{{ $totalApplications }}</div>
                </div>
                <i class="bi bi-people fs-1 text-warning opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="card p-4">
    <a href="{{ route('company.internships.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Post a New Internship
    </a>
    <a href="{{ route('company.internships') }}" class="btn btn-outline-dark ms-2">
        <i class="bi bi-list-ul"></i> View All Internships
    </a>
</div>

@endsection