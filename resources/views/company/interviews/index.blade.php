@extends('layouts.app')

@section('title', 'Interviews')
@section('page-title', 'Scheduled Interviews')

@section('content')

@if($interviews->isEmpty())
    <div class="card p-4 text-center text-muted">
        <i class="bi bi-calendar-x fs-2 mb-2"></i>
        <p class="mb-0">No interviews scheduled yet.</p>
    </div>
@else
    <div class="card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="small text-muted">
                        <th class="ps-4">Applicant</th>
                        <th>Internship</th>
                        <th>Date & Time</th>
                        <th>Mode</th>
                        <th>Location / Link</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interviews as $application)
                        <tr>
                            <td class="ps-4 fw-semibold">
                                {{ $application->student->FIRST_NAME }} {{ $application->student->LAST_NAME }}
                            </td>
                            <td>{{ $application->internship->TITLE }}</td>
                            <td class="small">
                                {{ \Carbon\Carbon::parse($application->interview->SCHEDULED_DATE)->format('d M Y') }}
                                at {{ $application->interview->SCHEDULED_TIME }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $application->interview->INTERVIEW_MODE }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $application->interview->LOCATION_OR_LINK ?? '—' }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('company.applications.show', $application->APPLICATION_ID) }}"
                                   class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection