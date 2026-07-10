<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentSkillController extends Controller
{
    public function index()
    {
        $studentId = $this->getStudentId();

        // My skills with skill details — INNER JOIN
        $mySkillRows = DB::select(
            "SELECT ss.STUDENT_SKILL_ID, ss.PROFICIENCY, ss.ADDED_AT,
                    sk.SKILL_ID, sk.SKILL_NAME, sk.CATEGORY
             FROM STUDENT_SKILLS ss
             INNER JOIN SKILLS sk ON ss.SKILL_ID = sk.SKILL_ID
             WHERE ss.STUDENT_ID = :student_id
             ORDER BY sk.CATEGORY, sk.SKILL_NAME",
            ['student_id' => $studentId]
        );

        $mySkills = collect(array_map(fn($r) => (object)[
            'STUDENT_SKILL_ID' => $r->student_skill_id,
            'SKILL_ID'         => $r->skill_id,
            'SKILL_NAME'       => $r->skill_name,
            'CATEGORY'         => $r->category,
            'PROFICIENCY'      => $r->proficiency,
        ], $mySkillRows));

        // Skills the student has NOT yet added — MINUS / NOT IN subquery
        $addedIds = array_column($mySkillRows, 'skill_id');

        if (empty($addedIds)) {
            $availableRows = DB::select(
                "SELECT SKILL_ID, SKILL_NAME, CATEGORY
                 FROM SKILLS ORDER BY CATEGORY, SKILL_NAME"
            );
        } else {
            $placeholders = implode(',', array_fill(0, count($addedIds), '?'));
            $availableRows = DB::select(
                "SELECT SKILL_ID, SKILL_NAME, CATEGORY
                 FROM SKILLS
                 WHERE SKILL_ID NOT IN ($placeholders)
                 ORDER BY CATEGORY, SKILL_NAME",
                $addedIds
            );
        }

        $availableSkills = collect(array_map(fn($r) => (object)[
            'SKILL_ID'   => $r->skill_id,
            'SKILL_NAME' => $r->skill_name,
            'CATEGORY'   => $r->category,
        ], $availableRows));

        // Group available skills by category for the dropdown
        $grouped = [];
        foreach ($availableSkills as $skill) {
            $grouped[$skill->CATEGORY][] = $skill;
        }

        return view('student.skills', [
            'mySkills'        => $mySkills,
            'availableSkills' => collect($availableSkills),
            'groupedSkills'   => $grouped,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_id'    => 'required|integer',
            'proficiency' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        $studentId = $this->getStudentId();

        // Check for duplicate
        $exists = DB::select(
            "SELECT COUNT(*) AS CNT FROM STUDENT_SKILLS
             WHERE STUDENT_ID = :student_id AND SKILL_ID = :skid",
            ['student_id' => $studentId, 'skid' => $validated['skill_id']]
        )[0]->cnt;

        if ($exists > 0) {
            return redirect()->route('student.skills')
                ->with('error', 'You have already added this skill.');
        }

        try {
            DB::insert(
                "INSERT INTO STUDENT_SKILLS (STUDENT_ID, SKILL_ID, PROFICIENCY)
                 VALUES (:student_id, :skill_id, :proficiency)",
                [
                    'student_id'  => $studentId,
                    'skill_id'    => $validated['skill_id'],
                    'proficiency' => $validated['proficiency'],
                ]
            );
        } catch (\Exception $e) {
            return redirect()->route('student.skills')
                ->with('error', 'This skill could not be added.');
        }

        return redirect()->route('student.skills')
            ->with('success', 'Skill added successfully.');
    }

    public function update(Request $request, $studentSkillId)
    {
        $validated = $request->validate([
            'proficiency' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        $studentId = $this->getStudentId();

        DB::update(
            "UPDATE STUDENT_SKILLS SET PROFICIENCY = :proficiency
            WHERE STUDENT_SKILL_ID = :student_skill_id AND STUDENT_ID = :student_id",
            [
                'proficiency'      => $validated['proficiency'],
                'student_skill_id' => $studentSkillId,
                'student_id'       => $studentId,
            ]
        );

        return redirect()->route('student.skills')
            ->with('success', 'Proficiency updated.');
    }

    public function destroy($studentSkillId)
    {
        $studentId = $this->getStudentId();

        DB::delete(
            "DELETE FROM STUDENT_SKILLS
            WHERE STUDENT_SKILL_ID = :student_skill_id AND STUDENT_ID = :student_id",
            ['student_skill_id' => $studentSkillId, 'student_id' => $studentId]
        );

        return redirect()->route('student.skills')
            ->with('success', 'Skill removed successfully.');
    }

    private function getStudentId(): int
    {
        $row = DB::select(
            "SELECT STUDENT_ID FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        );
        return (int) $row[0]->student_id;
    }
}