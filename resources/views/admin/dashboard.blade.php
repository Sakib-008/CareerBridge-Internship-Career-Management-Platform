@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')

{{-- Stats Row --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card blue p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Students</div>
                    <div class="fs-3 fw-bold">{{ $stats->total_students }}</div>
                </div>
                <i class="bi bi-mortarboard fs-1 text-primary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card green p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Companies</div>
                    <div class="fs-3 fw-bold">{{ $stats->total_companies }}</div>
                </div>
                <i class="bi bi-building fs-1 text-success opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card orange p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Open Internships</div>
                    <div class="fs-3 fw-bold">{{ $stats->open_internships }}</div>
                </div>
                <i class="bi bi-briefcase fs-1 text-warning opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card red p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Placements</div>
                    <div class="fs-3 fw-bold">{{ $stats->total_placements }}</div>
                </div>
                <i class="bi bi-trophy fs-1 text-danger opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="card p-3 mb-4 d-flex flex-row align-items-center justify-content-between">
    <div>
        <h6 class="fw-bold mb-0">Recommendation Engine</h6>
        <small class="text-muted">
            Calls <code>SP_GENERATE_RECOMMENDATIONS</code> — regenerates skill-match
            scores for all students.
        </small>
    </div>
    <form method="POST" action="{{ route('admin.recommendations.regenerate') }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary">
            <i class="bi bi-arrow-repeat"></i> Regenerate All Recommendations
        </button>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">Total Applications</div>
            <div class="fs-4 fw-bold">{{ $stats->total_applications }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">Total Internships</div>
            <div class="fs-4 fw-bold">{{ $stats->total_internships }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <div class="text-muted small">Interviews Scheduled</div>
            <div class="fs-4 fw-bold">{{ $stats->total_interviews }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Recent Applications --}}
    <div class="col-md-8">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-clock-history me-2"></i>Recent Applications
            </h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead>
                        <tr class="text-muted">
                            <th>Student</th>
                            <th>Internship</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentApplications as $app)
                            <tr>
                                <td class="fw-semibold">{{ $app->STUDENT_NAME }}</td>
                                <td>{{ $app->INTERNSHIP_TITLE }}</td>
                                <td>{{ $app->COMPANY_NAME }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($app->STATUS) }}">
                                        {{ $app->STATUS }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($app->APPLIED_AT)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Internships --}}
    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-bar-chart me-2"></i>Most Applied Internships
            </h6>
            @foreach($topInternships as $i)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">{{ \Illuminate\Support\Str::limit($i->TITLE, 30) }}</span>
                        <span class="text-muted">{{ $i->APPLICATION_COUNT }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary"
                             style="width: {{ $topInternships[0]->APPLICATION_COUNT > 0
                                 ? ($i->APPLICATION_COUNT / $topInternships[0]->APPLICATION_COUNT * 100)
                                 : 0 }}%">
                        </div>
                    </div>
                    <small class="text-muted">{{ $i->COMPANY_NAME }}</small>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection