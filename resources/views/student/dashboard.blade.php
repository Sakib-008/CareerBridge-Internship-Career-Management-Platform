@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card p-4">
            <h5 class="fw-bold">
                <i class="bi bi-mortarboard-fill text-primary me-2"></i>
                Welcome, {{ Auth::user()->student->FIRST_NAME ?? 'Student' }}!
            </h5>
            <p class="text-muted mb-0">
                Your profile and internship tools will be available here.
                Complete your profile to get started.
            </p>
        </div>
    </div>
</div>
@endsection