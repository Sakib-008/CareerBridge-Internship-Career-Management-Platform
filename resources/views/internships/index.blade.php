@extends('layouts.app')

@section('title', 'Browse Internships')
@section('page-title', 'Browse Internships')

@section('content')

{{-- Search & Filters --}}
<div class="card p-4 mb-4">
    <form method="GET" action="{{ route('internships.index') }}">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Keyword</label>
                <input type="text" name="keyword" class="form-control"
                       placeholder="Job title..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Location</label>
                <input type="text" name="location" class="form-control"
                       placeholder="City..." value="{{ request('location') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Type</label>
                <select name="type" class="form-select">
                    <option value="">Any</option>
                    @foreach(['Remote','On-site','Hybrid'] as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Skill</label>
                <select name="skill_id" class="form-select">
                    <option value="">Any</option>
                    @foreach($skills as $skill)
                        <option value="{{ $skill->SKILL_ID }}" {{ request('skill_id') == $skill->SKILL_ID ? 'selected' : '' }}>
                            {{ $skill->SKILL_NAME }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Results --}}
<div class="row g-4">
    @forelse($internships as $internship)
        <div class="col-md-4">
            <div class="card p-3 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between">
                    <span class="badge bg-light text-dark border mb-2">{{ $internship->INTERNSHIP_TYPE }}</span>
                    @if(in_array($internship->INTERNSHIP_ID, $appliedInternshipIds))
                        <span class="badge bg-success">Applied</span>
                    @endif
                </div>
                <h6 class="fw-bold">{{ $internship->TITLE }}</h6>
                <p class="text-muted small mb-1">
                    <i class="bi bi-building"></i> {{ $internship->company->COMPANY_NAME }}
                </p>
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt"></i> {{ $internship->LOCATION }}
                </p>
                <p class="small mb-3 flex-grow-1">
                    {{ \Illuminate\Support\Str::limit($internship->DESCRIPTION, 100) }}
                </p>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                    <span><i class="bi bi-cash"></i> ${{ number_format($internship->STIPEND, 0) }}/mo</span>
                    <span><i class="bi bi-calendar-x"></i> {{ \Carbon\Carbon::parse($internship->APPLICATION_DEADLINE)->format('d M') }}</span>
                </div>
                <a href="{{ route('internships.show', $internship->INTERNSHIP_ID) }}" class="btn btn-outline-dark btn-sm w-100">
                    View Details
                </a>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card p-4 text-center text-muted">
                No internships match your search criteria.
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $internships->links() }}
</div>

@endsection