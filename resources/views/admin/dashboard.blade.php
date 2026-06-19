@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card p-4">
            <h5 class="fw-bold">
                <i class="bi bi-shield-lock-fill text-danger me-2"></i>
                Admin Panel
            </h5>
            <p class="text-muted mb-0">
                System overview, user management, and reports will be available here.
            </p>
        </div>
    </div>
</div>
@endsection