@extends('layouts.app')

@section('title', 'Company Dashboard')
@section('page-title', 'Company Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card p-4">
            <h5 class="fw-bold">
                <i class="bi bi-building-fill text-success me-2"></i>
                Welcome, {{ Auth::user()->company->COMPANY_NAME ?? 'Company' }}!
            </h5>
            <p class="text-muted mb-0">
                Your internship postings and applicant tools will be available here.
            </p>
        </div>
    </div>
</div>
@endsection