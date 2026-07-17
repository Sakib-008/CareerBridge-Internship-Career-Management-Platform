@extends('layouts.app')

@section('title', 'Student Placement Report')
@section('page-title', 'Student Placement Report')

@section('content')

<div class="mb-3">
    <small class="text-muted">
        <i class="bi bi-database me-1"></i>
        Data sourced from Oracle view: <code>VW_STUDENT_PLACEMENT</code>
    </small>
</div>

{{-- Summary Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card p-3 text-center">
            <div class="text-muted small">Students</div>
            <div class="fs-4 fw-bold">{{ $summary->total_students }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card p-3 text-center">
            <div class="text-muted small">Applications</div>
            <div class="fs-4 fw-bold">{{ $summary->total_applications }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card p-3 text-center">
            <div class="text-muted small">Placements</div>
            <div class="fs-4 fw-bold text-success">{{ $summary->total_placements }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card p-3 text-center">
            <div class="text-muted small">Avg Rate</div>
            <div class="fs-4 fw-bold">{{ $summary->avg_placement_rate }}%</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card p-3 text-center">
            <div class="text-muted small">Highest GPA</div>
            <div class="fs-4 fw-bold">{{ $summary->highest_gpa ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card p-3 text-center">
            <div class="text-muted small">Avg GPA</div>
            <div class="fs-4 fw-bold">{{ $summary->avg_gpa ?? '—' }}</div>
        </div>
    </div>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr class="small">
                    <th class="ps-4">Student</th>
                    <th>Department</th>
                    <th>University</th>
                    <th class="text-center">GPA</th>
                    <th class="text-center">Applied</th>
                    <th class="text-center">Placed</th>
                    <th class="text-center">Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report as $row)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $row->STUDENT_NAME }}</td>
                        <td>{{ $row->DEPARTMENT }}</td>
                        <td class="small text-muted">{{ $row->UNIVERSITY }}</td>
                        <td class="text-center">{{ $row->GPA ?? '—' }}</td>
                        <td class="text-center">{{ $row->TOTAL_APPLIED }}</td>
                        <td class="text-center text-success fw-semibold">{{ $row->PLACEMENTS }}</td>
                        <td class="text-center">
                            <span class="badge {{ $row->PLACEMENT_RATE_PCT >= 50 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $row->PLACEMENT_RATE_PCT }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection