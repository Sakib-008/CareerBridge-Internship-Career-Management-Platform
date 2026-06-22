<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\StudentSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentSkillController extends Controller
{
    // Show Skills Page
    public function index()
    {
        $student = Auth::user()->student;

        $mySkills = $student->skills()->orderBy('CATEGORY')->get();

        // Get all skills NOT already added by this student
        $addedSkillIds = $student->studentSkills()->get()->pluck('SKILL_ID')->toArray();

        $availableSkills = empty($addedSkillIds)
            ? Skill::orderBy('CATEGORY')->orderBy('SKILL_NAME')->get()
            : Skill::whereNotIn('SKILL_ID', $addedSkillIds)
                   ->orderBy('CATEGORY')->orderBy('SKILL_NAME')->get();

        return view('student.skills', compact('mySkills', 'availableSkills'));
    }

    // Add a Skill
    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_id'    => 'required|integer|exists:SKILLS,SKILL_ID',
            'proficiency' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        $student = Auth::user()->student;

        // Prevent duplicate
        $exists = StudentSkill::where('STUDENT_ID', $student->STUDENT_ID)
            ->where('SKILL_ID', $validated['skill_id'])
            ->exists();

        if ($exists) {
            return redirect()->route('student.skills')
                ->with('error', 'You have already added this skill.');
        }

        try {
            StudentSkill::create([
                'STUDENT_ID'  => $student->STUDENT_ID,
                'SKILL_ID'    => $validated['skill_id'],
                'PROFICIENCY' => $validated['proficiency'],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Catches the Oracle UNIQUE constraint violation
            return redirect()->route('student.skills')
                ->with('error', 'This skill could not be added (already exists).');
        }

        return redirect()->route('student.skills')
            ->with('success', 'Skill added successfully.');
    }

    // Update Proficiency
    public function update(Request $request, $studentSkillId)
    {
        $validated = $request->validate([
            'proficiency' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        $student = Auth::user()->student;

        $studentSkill = StudentSkill::where('STUDENT_SKILL_ID', $studentSkillId)
            ->where('STUDENT_ID', $student->STUDENT_ID)
            ->firstOrFail();

        $studentSkill->update(['PROFICIENCY' => $validated['proficiency']]);

        return redirect()->route('student.skills')
            ->with('success', 'Proficiency updated successfully.');
    }

    // Remove a Skill
    public function destroy($studentSkillId)
    {
        $student = Auth::user()->student;

        $studentSkill = StudentSkill::where('STUDENT_SKILL_ID', $studentSkillId)
            ->where('STUDENT_ID', $student->STUDENT_ID)
            ->firstOrFail();

        $studentSkill->delete();

        return redirect()->route('student.skills')
            ->with('success', 'Skill removed successfully.');
    }
}