@extends('layouts.app')

@section('title', 'My Interviews')
@section('page-title', 'My Interviews')

@section('content')

@if($interviews->isEmpty())
    <div class="card p-4 text-center text-muted">
        <i class="bi bi-camera-video fs-2 mb-2"></i>
        <p class="mb-0">No interviews scheduled yet. Keep applying!</p>
    </div>
@else
    <div class="row g-4">
        @foreach($interviews as $application)
            <div class="col-md-6">
                <div class="card p-4 h-100 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $application->internship->TITLE }}</h6>
                            <p class="text-muted small mb-0">
                                {{ $application->internship->company->COMPANY_NAME }}
                            </p>
                        </div>
                        <span class="badge bg-primary">Interview</span>
                    </div>

                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="bi bi-calendar-event text-primary me-2"></i>
                            <strong>
                                {{ \Carbon\Carbon::parse($application->interview->SCHEDULED_DATE)->format('l, d M Y') }}
                            </strong>
                            at {{ $application->interview->SCHEDULED_TIME }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-laptop text-muted me-2"></i>
                            {{ $application->interview->INTERVIEW_MODE }}
                        </li>
                        @if($application->interview->LOCATION_OR_LINK)
                            <li class="mb-2">
                                <i class="bi bi-geo-alt text-muted me-2"></i>
                                {{ $application->interview->LOCATION_OR_LINK }}
                            </li>
                        @endif
                        @if($application->interview->NOTES)
                            <li class="text-muted fst-italic">
                                <i class="bi bi-sticky me-2"></i>
                                {{ $application->interview->NOTES }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection