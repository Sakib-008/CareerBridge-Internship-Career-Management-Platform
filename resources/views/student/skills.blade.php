@extends('layouts.app')

@section('title', 'My Skills')
@section('page-title', 'My Skills')

@section('content')

<div class="row g-4">

    {{-- Add Skill Form --}}
    <div class="col-md-5">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2"></i>Add a Skill</h6>

            @if($availableSkills->isEmpty())
                <p class="text-muted small">You've added all available skills from the catalog.</p>
            @else
                <form method="POST" action="{{ route('student.skills.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Skill</label>
                        <select name="skill_id" class="form-select" required>
                            <option value="" disabled selected>Choose a skill...</option>
                            @foreach($groupedSkills as $category => $skills)
                                <optgroup label="{{ $category }}">
                                    @foreach($skills as $skill)
                                        <option value="{{ $skill->SKILL_ID }}">{{ $skill->SKILL_NAME }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Proficiency Level</label>
                        <select name="proficiency" class="form-select" required>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg"></i> Add Skill
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- My Skills List --}}
    <div class="col-md-7">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-stars me-2"></i>My Skills ({{ $mySkills->count() }})</h6>

            @if($mySkills->isEmpty())
                <p class="text-muted small">You haven't added any skills yet. Add skills to receive better internship recommendations.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="small text-muted">
                                <th>Skill</th>
                                <th>Category</th>
                                <th>Proficiency</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mySkills as $skill)
                                <tr>
                                    <td class="fw-semibold">{{ $skill->SKILL_NAME }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $skill->CATEGORY }}</span></td>
                                    <td>
                                        <form method="POST"
                                            action="{{ route('student.skills.update', $skill->STUDENT_SKILL_ID) }}"
                                            class="d-flex align-items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="proficiency" class="form-select form-select-sm"
                                                    onchange="this.form.submit()" style="width: auto;">
                                                @foreach(['Beginner','Intermediate','Advanced'] as $level)
                                                    <option value="{{ $level }}"
                                                        {{ $skill->PROFICIENCY === $level ? 'selected' : '' }}>
                                                        {{ $level }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST"
                                            action="{{ route('student.skills.destroy', $skill->STUDENT_SKILL_ID) }}"
                                            onsubmit="return confirm('Remove this skill?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection