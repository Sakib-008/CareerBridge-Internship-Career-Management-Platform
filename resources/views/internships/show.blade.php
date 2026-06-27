@extends('layouts.app')

@section('title', $internship->TITLE)
@section('page-title', 'Internship Details')

@section('content')

<div class="row g-4">
    <div class="col-md-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="fw-bold mb-1">{{ $internship->TITLE }}</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-building"></i> {{ $internship->company->COMPANY_NAME }}
                        — {{ $internship->company->INDUSTRY }}
                    </p>
                </div>
                <span class="badge bg-{{ $internship->STATUS === 'Open' ? 'success' : 'secondary' }}">
                    {{ $internship->STATUS }}
                </span>
            </div>

            <hr>

            <h6 class="fw-bold">Description</h6>
            <p>{{ $internship->DESCRIPTION }}</p>

            <h6 class="fw-bold mt-4">Required Skills</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($internship->skills as $skill)
                    <span class="badge {{ (string)$skill->pivot->IS_MANDATORY === '1' ? 'bg-danger' : 'bg-secondary' }}">
                        {{ $skill->SKILL_NAME }}
                        {{ (string)$skill->pivot->IS_MANDATORY === '1' ? '(Required)' : '(Preferred)' }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Quick Info</h6>
            <ul class="list-unstyled small">
                <li class="mb-2"><i class="bi bi-geo-alt text-muted"></i> {{ $internship->LOCATION }}</li>
                <li class="mb-2"><i class="bi bi-laptop text-muted"></i> {{ $internship->INTERNSHIP_TYPE }}</li>
                <li class="mb-2"><i class="bi bi-clock text-muted"></i> {{ $internship->DURATION_MONTHS }} month(s)</li>
                <li class="mb-2"><i class="bi bi-cash text-muted"></i> ${{ number_format($internship->STIPEND, 2) }}/month</li>
                <li class="mb-2"><i class="bi bi-person-badge text-muted"></i> {{ $internship->VACANCIES }} vacancy(ies)</li>
                <li class="mb-2"><i class="bi bi-calendar-x text-muted"></i> Apply by {{ \Carbon\Carbon::parse($internship->APPLICATION_DEADLINE)->format('d M Y') }}</li>
            </ul>

            @auth
                @if(Auth::user()->isStudent())
                    @if($hasApplied)
                        <button class="btn btn-success w-100" disabled>
                            <i class="bi bi-check-circle"></i> Already Applied
                        </button>
                    @elseif($internship->STATUS !== 'Open' || $internship->APPLICATION_DEADLINE < now()->format('Y-m-d'))
                        <button class="btn btn-secondary w-100" disabled>
                            Applications Closed
                        </button>
                    @else
                        <a href="{{ route('student.applications.create', $internship->INTERNSHIP_ID) }}"
                           class="btn btn-primary w-100">
                            <i class="bi bi-send"></i> Apply Now
                        </a>
                    @endif
                @endif
            @endauth
        </div>
    </div>
</div>

@endsection