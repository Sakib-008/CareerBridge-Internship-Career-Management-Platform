@extends('layouts.app')

@section('title', 'Recommendations')
@section('page-title', 'Recommended Internships')

@section('content')

@if($recommendations->isEmpty())
    <div class="card p-4 text-center text-muted">
        <i class="bi bi-lightbulb fs-2 mb-2"></i>
        <p class="mb-1">No recommendations yet.</p>
        <small>
            Add more skills to your profile to get matched with relevant internships.
            <a href="{{ route('student.skills') }}">Add skills</a>
        </small>
    </div>
@else
    <p class="text-muted small mb-3">
        Showing {{ $recommendations->count() }} internship(s) matched to your skills.
        Scores are recalculated each time you visit this page.
    </p>

    <div class="row g-4">
        @foreach($recommendations as $rec)
            <div class="col-md-6">
                <div class="card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">{{ $rec->internship->TITLE }}</h6>
                        {{-- Match score badge --}}
                        <span class="badge {{ $rec->MATCH_SCORE >= 75 ? 'bg-success' : ($rec->MATCH_SCORE >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ $rec->MATCH_SCORE }}% match
                        </span>
                    </div>

                    <p class="text-muted small mb-1">
                        <i class="bi bi-building me-1"></i>
                        {{ $rec->internship->company->COMPANY_NAME }}
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt me-1"></i>{{ $rec->internship->LOCATION }}
                        &nbsp;·&nbsp;
                        <i class="bi bi-laptop me-1"></i>{{ $rec->internship->INTERNSHIP_TYPE }}
                        &nbsp;·&nbsp;
                        <i class="bi bi-cash me-1"></i>${{ number_format($rec->internship->STIPEND, 0) }}/mo
                    </p>

                    {{-- Match score progress bar --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Skill Match</span>
                            <span>{{ $rec->MATCH_SCORE }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar
                                {{ $rec->MATCH_SCORE >= 75 ? 'bg-success' : ($rec->MATCH_SCORE >= 50 ? 'bg-warning' : 'bg-secondary') }}"
                                style="width: {{ $rec->MATCH_SCORE }}%">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <small class="text-muted">
                            <i class="bi bi-calendar-x me-1"></i>
                            Apply by {{ \Carbon\Carbon::parse($rec->internship->APPLICATION_DEADLINE)->format('d M Y') }}
                        </small>
                        <a href="{{ route('internships.show', $rec->internship->INTERNSHIP_ID) }}"
                           class="btn btn-sm btn-outline-dark">View</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection