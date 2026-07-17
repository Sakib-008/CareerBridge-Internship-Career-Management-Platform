@extends('layouts.app')

@section('title', 'Company Activity Report')
@section('page-title', 'Company Activity Report')

@section('content')

<div class="mb-3">
    <small class="text-muted">
        <i class="bi bi-database me-1"></i>
        Multi-table JOIN with aggregates and correlated subquery for interview count
    </small>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr class="small">
                    <th class="ps-4">Company</th>
                    <th>Industry</th>
                    <th>Location</th>
                    <th class="text-center">Postings</th>
                    <th class="text-center">Open</th>
                    <th class="text-center">Applications</th>
                    <th class="text-center">Accepted</th>
                    <th class="text-center">Interviews</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report as $row)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $row->COMPANY_NAME }}</td>
                        <td class="small">{{ $row->INDUSTRY }}</td>
                        <td class="small text-muted">{{ $row->LOCATION }}</td>
                        <td class="text-center">{{ $row->TOTAL_POSTINGS }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $row->OPEN_POSTINGS }}</span>
                        </td>
                        <td class="text-center">{{ $row->TOTAL_APPLICATIONS }}</td>
                        <td class="text-center text-success fw-semibold">
                            {{ $row->TOTAL_ACCEPTED }}
                        </td>
                        <td class="text-center">{{ $row->TOTAL_INTERVIEWS }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No company data available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection