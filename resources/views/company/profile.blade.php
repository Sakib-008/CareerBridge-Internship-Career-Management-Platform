@extends('layouts.app')

@section('title', 'Company Profile')
@section('page-title', 'Company Profile')

@section('content')

<div class="card p-4" style="max-width: 800px;">
    <h6 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Company Information</h6>

    <form method="POST" action="{{ route('company.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Company Name</label>
                <input type="text" name="company_name" class="form-control"
                       value="{{ old('company_name', $company->COMPANY_NAME) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Industry</label>
                <input type="text" name="industry" class="form-control"
                       value="{{ old('industry', $company->INDUSTRY) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Company Size</label>
                <select name="company_size" class="form-select">
                    <option value="">Select size...</option>
                    @foreach(['1-10','11-50','51-200','201-500','500+'] as $size)
                        <option value="{{ $size }}"
                            {{ old('company_size', $company->COMPANY_SIZE) === $size ? 'selected' : '' }}>
                            {{ $size }} employees
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Location</label>
                <input type="text" name="location" class="form-control"
                       value="{{ old('location', $company->LOCATION) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Website</label>
                <input type="url" name="website" class="form-control" placeholder="https://"
                       value="{{ old('website', $company->WEBSITE) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Contact Email</label>
                <input type="email" name="contact_email" class="form-control"
                       value="{{ old('contact_email', $company->CONTACT_EMAIL) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Contact Person</label>
                <input type="text" name="contact_person" class="form-control"
                       value="{{ old('contact_person', $company->CONTACT_PERSON) }}">
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Company Description</label>
                <textarea name="description" rows="4" class="form-control"
                          placeholder="Tell students about your company...">{{ old('description', $company->DESCRIPTION) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">
            <i class="bi bi-save"></i> Save Changes
        </button>
    </form>
</div>

@endsection