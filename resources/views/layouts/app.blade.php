<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CareerBridge — @yield('title', 'Internship & Career Platform')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body        { background-color: #f8f9fa; }
        .sidebar    { min-height: 100vh; background-color: #1a1a2e; }
        .sidebar .nav-link        { color: #adb5bd; padding: 10px 20px; }
        .sidebar .nav-link:hover  { color: #ffffff; background-color: #16213e; }
        .sidebar .nav-link.active { color: #ffffff; background-color: #0f3460; }
        .sidebar .brand           { color: #e94560; font-weight: 700; font-size: 1.3rem; }
        .main-content { padding: 30px; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .stat-card    { border-left: 4px solid; }
        .stat-card.blue   { border-color: #0d6efd; }
        .stat-card.green  { border-color: #198754; }
        .stat-card.orange { border-color: #fd7e14; }
        .stat-card.red    { border-color: #dc3545; }
        .badge-pending     { background-color: #6c757d; }
        .badge-reviewed    { background-color: #0dcaf0; }
        .badge-shortlisted { background-color: #0d6efd; }
        .badge-interview   { background-color: #fd7e14; }
        .badge-accepted    { background-color: #198754; }
        .badge-rejected    { background-color: #dc3545; }
    </style>

    @stack('styles')
</head>
<body>

@auth
<div class="container-fluid">
    <div class="row">

        {{-- Sidebar --}}
        <div class="col-md-2 sidebar p-0">
            <div class="p-3 border-bottom border-secondary">
                <span class="brand">
                    <i class="bi bi-briefcase-fill"></i> CareerBridge
                </span>
            </div>

            <nav class="nav flex-column mt-2">
                @if(Auth::user()->isStudent())
                    @include('partials.sidebar_student')
                @elseif(Auth::user()->isCompany())
                    @include('partials.sidebar_company')
                @elseif(Auth::user()->isAdmin())
                    @include('partials.sidebar_admin')
                @endif
            </nav>

            <div class="p-3 mt-auto border-top border-secondary position-absolute bottom-0 w-100">
                <small class="text-muted d-block mb-2">
                    {{ Auth::user()->EMAIL }}
                </small>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-md-10 main-content">

            {{-- Top Bar --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-bold text-dark">@yield('page-title', 'Dashboard')</h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-secondary text-capitalize">
                        {{ Auth::user()->ROLE }}
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-calendar3"></i>
                        {{ now()->format('D, d M Y') }}
                    </span>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Page Content --}}
            @yield('content')
        </div>
    </div>
</div>
@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>