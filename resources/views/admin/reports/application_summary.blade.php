@extends('layouts.app')

@section('title', 'Application Pipeline Report')
@section('page-title', 'Application Pipeline Report')

@section('content')

<div class="mb-3">
    <small class="text-muted">
        <i class="bi bi-database me-1"></i>
        Data sourced from Oracle view: <code>VW_APPLICATION_SUMMARY</code>
    </small>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr class="small">
                    <th class="ps-4">Internship</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Reviewed</th>
                    <th class="text-center">Shortlisted</th>
                    <th class="text-center">Interview</th>
                    <th class="text-center">Accepted</th>
                    <th class="text-center">Rejected</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report as $row)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $row->TITLE }}</td>
                        <td>{{ $row->COMPANY_NAME }}</td>
                        <td>
                            <span class="badge {{ $row->INTERNSHIP_STATUS === 'Open' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $row->INTERNSHIP_STATUS }}
                            </span>
                        </td>
                        <td class="text-center fw-bold">{{ $row->TOTAL_APPLICATIONS }}</td>
                        <td class="text-center text-muted">{{ $row->PENDING }}</td>
                        <td class="text-center text-info">{{ $row->REVIEWED }}</td>
                        <td class="text-center text-primary">{{ $row->SHORTLISTED }}</td>
                        <td class="text-center text-warning">{{ $row->INTERVIEW_COUNT }}</td>
                        <td class="text-center text-success fw-semibold">{{ $row->ACCEPTED }}</td>
                        <td class="text-center text-danger">{{ $row->REJECTED }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No data available.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($report->count() > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="ps-4" colspan="3">Totals</td>
                        <td class="text-center">{{ $report->sum('TOTAL_APPLICATIONS') }}</td>
                        <td class="text-center">{{ $report->sum('PENDING') }}</td>
                        <td class="text-center">{{ $report->sum('REVIEWED') }}</td>
                        <td class="text-center">{{ $report->sum('SHORTLISTED') }}</td>
                        <td class="text-center">{{ $report->sum('INTERVIEW_COUNT') }}</td>
                        <td class="text-center text-success">{{ $report->sum('ACCEPTED') }}</td>
                        <td class="text-center text-danger">{{ $report->sum('REJECTED') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection