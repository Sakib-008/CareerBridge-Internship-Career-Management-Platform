@extends('layouts.app')

@section('title', 'Skill Catalog')
@section('page-title', 'Skill Catalog')

@section('content')

<div class="row g-4">
    {{-- Add Skill --}}
    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-plus-circle me-2"></i>Add New Skill
            </h6>
            <form method="POST" action="{{ route('admin.skills.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Skill Name</label>
                    <input type="text" name="skill_name" class="form-control"
                           placeholder="e.g. Django" value="{{ old('skill_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <input type="text" name="category" class="form-control"
                           placeholder="e.g. Web Development" value="{{ old('category') }}" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg"></i> Add Skill
                </button>
            </form>
        </div>
    </div>

    {{-- Skills Table --}}
    <div class="col-md-8">
        <div class="card p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="small text-muted">
                            <th class="ps-4">Skill</th>
                            <th>Category</th>
                            <th class="text-center">Students</th>
                            <th class="text-center">Internships</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($skills as $skill)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $skill->SKILL_NAME }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $skill->CATEGORY }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $skill->STUDENT_COUNT }}</td>
                                <td class="text-center">{{ $skill->INTERNSHIP_COUNT }}</td>
                                <td class="text-end pe-4">
                                    @if($skill->STUDENT_COUNT == 0 && $skill->INTERNSHIP_COUNT == 0)
                                        <form method="POST"
                                              action="{{ route('admin.skills.destroy', $skill->SKILL_ID) }}"
                                              onsubmit="return confirm('Delete this skill?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">In use</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No skills found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection