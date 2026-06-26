@extends('layouts.app')

@section('title', 'Post Internship')
@section('page-title', 'Post a New Internship')

@section('content')

<div class="card p-4" style="max-width: 900px;">
    <form method="POST" action="{{ route('company.internships.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label small fw-semibold">Internship Title</label>
                <input type="text" name="title" class="form-control"
                       value="{{ old('title') }}" placeholder="e.g. Backend Developer Intern" required>
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Description</label>
                <textarea name="description" rows="5" class="form-control" required
                          placeholder="Describe responsibilities, requirements, and what the intern will learn...">{{ old('description') }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Location</label>
                <input type="text" name="location" class="form-control"
                       value="{{ old('location') }}" placeholder="e.g. Dhaka, Bangladesh" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Work Mode</label>
                <select name="internship_type" class="form-select" required>
                    <option value="" disabled selected>Select mode...</option>
                    @foreach(['Remote','On-site','Hybrid'] as $type)
                        <option value="{{ $type }}" {{ old('internship_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Duration (months)</label>
                <input type="number" name="duration_months" class="form-control"
                       min="1" max="24" value="{{ old('duration_months', 3) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Monthly Stipend</label>
                <input type="number" name="stipend" class="form-control"
                       min="0" step="0.01" value="{{ old('stipend', 0) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Vacancies</label>
                <input type="number" name="vacancies" class="form-control"
                       min="1" value="{{ old('vacancies', 1) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Application Deadline</label>
                <input type="date" name="application_deadline" class="form-control"
                       value="{{ old('application_deadline') }}" required>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3"><i class="bi bi-stars me-2"></i>Required Skills</h6>
        <p class="text-muted small">Select the skills needed for this internship, and check "Mandatory" for must-have skills.</p>

        <div class="row g-2">
            @foreach($skills->groupBy('CATEGORY') as $category => $categorySkills)
                <div class="col-12">
                    <small class="text-muted fw-semibold text-uppercase">{{ $category }}</small>
                </div>
                @foreach($categorySkills as $skill)
                    <div class="col-md-4">
                        <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input skill-checkbox" type="checkbox"
                                       name="skill_ids[]" value="{{ $skill->SKILL_ID }}"
                                       id="skill_{{ $skill->SKILL_ID }}"
                                       {{ in_array($skill->SKILL_ID, old('skill_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="skill_{{ $skill->SKILL_ID }}">
                                    {{ $skill->SKILL_NAME }}
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mandatory-checkbox" type="checkbox"
                                       name="mandatory_skill_ids[]" value="{{ $skill->SKILL_ID }}"
                                       title="Mandatory?"
                                       {{ in_array($skill->SKILL_ID, old('mandatory_skill_ids', [])) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
        @error('skill_ids')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn btn-primary mt-4">
            <i class="bi bi-send"></i> Post Internship
        </button>
        <a href="{{ route('company.internships') }}" class="btn btn-outline-secondary mt-4">
            Cancel
        </a>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // Auto-check the skill checkbox if mandatory toggle is switched on
    document.querySelectorAll('.mandatory-checkbox').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            if (this.checked) {
                const skillCheckbox = document.getElementById('skill_' + this.value);
                if (skillCheckbox) skillCheckbox.checked = true;
            }
        });
    });
</script>
@endpush