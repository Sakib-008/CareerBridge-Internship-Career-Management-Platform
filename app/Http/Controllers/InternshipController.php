<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipController extends Controller
{
    // ─── Browse / Search Internships ───────────────────────────────────
    public function index(Request $request)
    {
        $query = Internship::with('company')
            ->where('STATUS', 'Open')
            ->where('APPLICATION_DEADLINE', '>=', now()->format('Y-m-d'));

        // Filter by keyword (title search)
        if ($request->filled('keyword')) {
            $query->where('TITLE', 'LIKE', '%' . $request->keyword . '%');
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('LOCATION', 'LIKE', '%' . $request->location . '%');
        }

        // Filter by internship type
        if ($request->filled('type')) {
            $query->where('INTERNSHIP_TYPE', $request->type);
        }

        // Filter by required skill
        if ($request->filled('skill_id')) {
            $skillId = $request->skill_id;
            $query->whereHas('skills', function ($q) use ($skillId) {
                $q->where('SKILLS.SKILL_ID', $skillId);
            });
        }

        $internships = $query->orderBy('CREATED_AT', 'desc')->paginate(9)->withQueryString();

        $skills = Skill::orderBy('SKILL_NAME')->get();

        // If logged in as student, mark which internships they've already applied to
        $appliedInternshipIds = [];
        if (Auth::check() && Auth::user()->isStudent()) {
            $student = Auth::user()->student;
            $appliedInternshipIds = $student->applications()->get()->pluck('INTERNSHIP_ID')->toArray();
        }

        return view('internships.index', compact('internships', 'skills', 'appliedInternshipIds'));
    }

    // ─── Show Single Internship Detail ─────────────────────────────────
    public function show($id)
    {
        $internship = Internship::with(['company', 'skills'])
            ->where('INTERNSHIP_ID', $id)
            ->firstOrFail();

        $hasApplied = false;
        if (Auth::check() && Auth::user()->isStudent()) {
            $student = Auth::user()->student;
            $hasApplied = $student->applications()
                ->where('INTERNSHIP_ID', $id)
                ->exists();
        }

        return view('internships.show', compact('internship', 'hasApplied'));
    }
}