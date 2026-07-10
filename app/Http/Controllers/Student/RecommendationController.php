<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    public function index()
    {
        $studentId = $this->getStudentId();

        $this->generateRecommendations($studentId);

        $rows = DB::select(
            "SELECT r.MATCH_SCORE, r.INTERNSHIP_ID,
                    i.TITLE, i.LOCATION, i.INTERNSHIP_TYPE,
                    i.STIPEND, i.APPLICATION_DEADLINE,
                    c.COMPANY_NAME
             FROM RECOMMENDATIONS r
             INNER JOIN INTERNSHIPS i ON r.INTERNSHIP_ID = i.INTERNSHIP_ID
             INNER JOIN COMPANIES c   ON i.COMPANY_ID    = c.COMPANY_ID
             WHERE r.STUDENT_ID = :student_id
             ORDER BY r.MATCH_SCORE DESC",
            ['student_id' => $studentId]
        );

        $recommendations = collect(array_map(fn($r) => (object)[
            'MATCH_SCORE'  => $r->match_score,
            'INTERNSHIP_ID'=> $r->internship_id,
            'internship'   => (object)[
                'INTERNSHIP_ID'        => $r->internship_id,
                'TITLE'                => $r->title,
                'LOCATION'             => $r->location,
                'INTERNSHIP_TYPE'      => $r->internship_type,
                'STIPEND'              => $r->stipend,
                'APPLICATION_DEADLINE' => $r->application_deadline,
                'company' => (object)['COMPANY_NAME' => $r->company_name],
            ],
        ], $rows));

        return view('student.recommendations', ['recommendations' => collect($recommendations)]);
    }

    private function generateRecommendations(int $studentId): void
    {
        // Core skill-match SQL using aggregate COUNT with CASE WHEN
        $results = DB::select(
            "SELECT
                i.INTERNSHIP_ID,
                ROUND(
                    COUNT(CASE WHEN ss.SKILL_ID IS NOT NULL THEN 1 END) * 100.0
                    / NULLIF(COUNT(ins.SKILL_ID), 0),
                2) AS MATCH_SCORE
             FROM INTERNSHIPS i
             INNER JOIN INTERNSHIP_SKILLS ins ON i.INTERNSHIP_ID = ins.INTERNSHIP_ID
             LEFT JOIN STUDENT_SKILLS ss
                 ON ins.SKILL_ID = ss.SKILL_ID
                 AND ss.STUDENT_ID = :student_id
             WHERE i.STATUS = 'Open'
             AND i.APPLICATION_DEADLINE >= TRUNC(SYSDATE)
             GROUP BY i.INTERNSHIP_ID
             HAVING ROUND(
                 COUNT(CASE WHEN ss.SKILL_ID IS NOT NULL THEN 1 END) * 100.0
                 / NULLIF(COUNT(ins.SKILL_ID), 0),
             2) > 0",
            ['student_id' => $studentId]
        );

        $activeIds = [];

        foreach ($results as $row) {
            $internshipId = $row->internship_id;
            $matchScore   = $row->match_score;
            $activeIds[]  = $internshipId;
            
            $exists = DB::select(
                "SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS
                WHERE STUDENT_ID = :student_id AND INTERNSHIP_ID = :internship_id",
                ['student_id' => $studentId, 'internship_id' => $internshipId]
            )[0]->cnt;

            if ($exists > 0) {
                DB::update(
                    "UPDATE RECOMMENDATIONS SET MATCH_SCORE = :match_score
                    WHERE STUDENT_ID = :student_id AND INTERNSHIP_ID = :internship_id",
                    [
                        'match_score'   => $matchScore,
                        'student_id'    => $studentId,
                        'internship_id' => $internshipId,
                    ]
                );
            } else {
                DB::insert(
                    "INSERT INTO RECOMMENDATIONS (STUDENT_ID, INTERNSHIP_ID, MATCH_SCORE)
                    VALUES (:student_id, :internship_id, :match_score)",
                    [
                        'student_id'    => $studentId,
                        'internship_id' => $internshipId,
                        'match_score'   => $matchScore,
                    ]
                );
            }
        }

        // Remove stale recommendations
        if (!empty($activeIds)) {
            $placeholders = implode(',', array_fill(0, count($activeIds), '?'));
            DB::delete(
                "DELETE FROM RECOMMENDATIONS
                WHERE STUDENT_ID = :student_id
                AND INTERNSHIP_ID NOT IN ($placeholders)",
                array_merge(['student_id' => $studentId], $activeIds)
            );
        } else {
            DB::delete(
                "DELETE FROM RECOMMENDATIONS WHERE STUDENT_ID = :student_id",
                ['student_id' => $studentId]
            );
        }
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