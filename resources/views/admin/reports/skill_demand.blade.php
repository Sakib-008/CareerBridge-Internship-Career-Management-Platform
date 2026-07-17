@extends('layouts.app')

@section('title', 'Skill Demand Report')
@section('page-title', 'Skill Demand Report')

@section('content')

<div class="mb-3">
    <small class="text-muted">
        <i class="bi bi-database me-1"></i>
        Data sourced from Oracle view: <code>VW_SKILL_DEMAND</code>
        — also demonstrates INTERSECT and MINUS set operations
    </small>
</div>

<div class="row g-4 mb-4">
    {{-- Main skill demand table --}}
    <div class="col-md-8">
        <div class="card p-0">
            <div class="card-header p-3 fw-semibold">
                <i class="bi bi-tags me-2"></i>Skill Demand Overview
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="small text-muted">
                            <th class="ps-4">Skill</th>
                            <th>Category</th>
                            <th class="text-center">Internships Require</th>
                            <th class="text-center">Students Have</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $row)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $row->SKILL_NAME }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $row->CATEGORY }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">
                                        {{ $row->REQUIRED_BY_INTERNSHIPS }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">
                                        {{ $row->STUDENTS_WITH_SKILL }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- INTERSECT result --}}
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-1">
                <i class="bi bi-intersect text-success me-2"></i>Matched Skills
            </h6>
            <small class="text-muted d-block mb-3">
                SQL: <code>INTERSECT</code> — skills students have AND internships require
            </small>
            @forelse($matchedSkills as $skill)
                <span class="badge bg-success me-1 mb-1">{{ $skill->SKILL_NAME }}</span>
            @empty
                <p class="text-muted small">None found.</p>
            @endforelse
        </div>

        {{-- MINUS result --}}
        <div class="card p-4">
            <h6 class="fw-bold mb-1">
                <i class="bi bi-dash-circle text-warning me-2"></i>Surplus Skills
            </h6>
            <small class="text-muted d-block mb-3">
                SQL: <code>MINUS</code> — skills students have but no internship requires
            </small>
            @forelse($surplusSkills as $skill)
                <span class="badge bg-warning text-dark me-1 mb-1">{{ $skill->SKILL_NAME }}</span>
            @empty
                <p class="text-muted small">None found.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection