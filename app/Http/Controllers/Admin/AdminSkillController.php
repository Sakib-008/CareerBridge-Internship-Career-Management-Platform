<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSkillController extends Controller
{
    public function index()
    {
        $rows = DB::select("
            SELECT
                sk.SKILL_ID,
                sk.SKILL_NAME,
                sk.CATEGORY,
                sk.CREATED_AT,
                COUNT(DISTINCT ss.STUDENT_ID)    AS STUDENT_COUNT,
                COUNT(DISTINCT ins.INTERNSHIP_ID) AS INTERNSHIP_COUNT
            FROM SKILLS sk
            LEFT JOIN STUDENT_SKILLS     ss  ON sk.SKILL_ID = ss.SKILL_ID
            LEFT JOIN INTERNSHIP_SKILLS  ins ON sk.SKILL_ID = ins.SKILL_ID
            GROUP BY sk.SKILL_ID, sk.SKILL_NAME, sk.CATEGORY, sk.CREATED_AT
            ORDER BY sk.CATEGORY, sk.SKILL_NAME
        ");

        $skills = collect(array_map(fn($r) => (object)[
            'SKILL_ID'          => $r->skill_id,
            'SKILL_NAME'        => $r->skill_name,
            'CATEGORY'          => $r->category,
            'CREATED_AT'        => $r->created_at,
            'STUDENT_COUNT'     => $r->student_count,
            'INTERNSHIP_COUNT'  => $r->internship_count,
        ], $rows));

        return view('admin.skills.index', compact('skills'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'skill_name' => 'required|string|max:100',
            'category'   => 'required|string|max:50',
        ]);

        // Check uniqueness
        $exists = DB::select(
            "SELECT COUNT(*) AS CNT FROM SKILLS
             WHERE UPPER(SKILL_NAME) = UPPER(:skill_name)",
            ['skill_name' => $request->skill_name]
        )[0]->cnt;

        if ($exists > 0) {
            return redirect()->route('admin.skills')
                ->with('error', 'A skill with this name already exists.');
        }

        DB::insert(
            "INSERT INTO SKILLS (SKILL_NAME, CATEGORY)
             VALUES (:skill_name, :category)",
            ['skill_name' => $request->skill_name, 'category' => $request->category]
        );

        return redirect()->route('admin.skills')
            ->with('success', "Skill \"{$request->skill_name}\" added successfully.");
    }

    public function destroy($skillId)
    {
        // Check if skill is in use
        $inUse = DB::select(
            "SELECT
                (SELECT COUNT(*) FROM STUDENT_SKILLS
                 WHERE SKILL_ID = :skill_id) +
                (SELECT COUNT(*) FROM INTERNSHIP_SKILLS
                 WHERE SKILL_ID = :skill_id2) AS TOTAL_USE
             FROM DUAL",
            ['skill_id' => $skillId, 'skill_id2' => $skillId]
        )[0]->total_use;

        if ($inUse > 0) {
            return redirect()->route('admin.skills')
                ->with('error', 'Cannot delete a skill that is currently in use by students or internships.');
        }

        DB::delete(
            "DELETE FROM SKILLS WHERE SKILL_ID = :skill_id",
            ['skill_id' => $skillId]
        );

        return redirect()->route('admin.skills')
            ->with('success', 'Skill deleted successfully.');
    }
}