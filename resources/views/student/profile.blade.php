@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')

<div class="row g-4">

    {{-- Profile Form --}}
    <div class="col-md-8">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Personal & Academic Info</h6>

            <form method="POST" action="{{ route('student.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">First Name</label>
                        <input type="text" name="first_name" class="form-control"
                               value="{{ old('first_name', $student->FIRST_NAME) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                               value="{{ old('last_name', $student->LAST_NAME) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $student->PHONE) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control"
                               value="{{ old('date_of_birth', $student->DATE_OF_BIRTH ? \Carbon\Carbon::parse($student->DATE_OF_BIRTH)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">University</label>
                        <input type="text" name="university" class="form-control"
                               value="{{ old('university', $student->UNIVERSITY) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Department</label>
                        <input type="text" name="department" class="form-control"
                               value="{{ old('department', $student->DEPARTMENT) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">GPA (out of 4.0)</label>
                        <input type="number" step="0.01" min="0" max="4" name="gpa" class="form-control"
                               value="{{ old('gpa', $student->GPA) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Graduation Year</label>
                        <input type="number" min="2000" max="{{ date('Y') + 10 }}" name="graduation_year" class="form-control"
                               value="{{ old('graduation_year', $student->GRADUATION_YEAR) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Profile Summary</label>
                        <textarea name="profile_summary" rows="4" class="form-control"
                                  placeholder="A short bio about your goals, interests, and experience...">{{ old('profile_summary', $student->PROFILE_SUMMARY) }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- CV Upload --}}
    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>CV / Resume</h6>

            @if($student->CV_FILE_PATH)
                <div class="alert alert-success d-flex align-items-center justify-content-between p-2">
                    <span class="small">
                        <i class="bi bi-file-earmark-check-fill"></i> CV uploaded
                    </span>
                    <a href="{{ Storage::url($student->CV_FILE_PATH) }}" target="_blank" class="btn btn-sm btn-outline-success">
                        View
                    </a>
                </div>

                <form method="POST" action="{{ route('student.profile.cv.delete') }}" class="mb-3"
                      onsubmit="return confirm('Remove your current CV?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash"></i> Remove CV
                    </button>
                </form>
            @else
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle"></i> No CV uploaded yet.
                </div>
            @endif

            <form method="POST" action="{{ route('student.profile.cv.upload') }}" enctype="multipart/form-data">
                @csrf
                <label class="form-label small fw-semibold">
                    {{ $student->CV_FILE_PATH ? 'Replace CV' : 'Upload CV' }}
                </label>
                <input type="file" name="cv_file" class="form-control mb-2" accept=".pdf,.doc,.docx" required>
                <small class="text-muted d-block mb-3">PDF, DOC, or DOCX. Max 2MB.</small>
                <button type="submit" class="btn btn-dark w-100">
                    <i class="bi bi-upload"></i> Upload
                </button>
            </form>
        </div>
    </div>

</div>

@endsection