<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    // ─── Report 1: Application Pipeline (from VW_APPLICATION_SUMMARY) ──
    public function applicationSummary()
    {
        $rows = DB::select("
            SELECT *
            FROM VW_APPLICATION_SUMMARY
            ORDER BY TOTAL_APPLICATIONS DESC
        ");

        $report = collect(array_map(fn($r) => (object)[
            'INTERNSHIP_ID'      => $r->internship_id,
            'TITLE'              => $r->title,
            'INTERNSHIP_STATUS'  => $r->internship_status,
            'COMPANY_NAME'       => $r->company_name,
            'TOTAL_APPLICATIONS' => $r->total_applications,
            'PENDING'            => $r->pending,
            'REVIEWED'           => $r->reviewed,
            'SHORTLISTED'        => $r->shortlisted,
            'INTERVIEW_COUNT'    => $r->interview_count,
            'ACCEPTED'           => $r->accepted,
            'REJECTED'           => $r->rejected,
        ], $rows));

        return view('admin.reports.application_summary', compact('report'));
    }

    // ─── Report 2: Student Placement (from VW_STUDENT_PLACEMENT) ───────
    public function studentPlacement()
    {
        $rows = DB::select("
            SELECT *
            FROM VW_STUDENT_PLACEMENT
            ORDER BY PLACEMENTS DESC, TOTAL_APPLIED DESC
        ");

        // Aggregate stats across all students — demonstrating SUM/AVG/MAX
        $summary = DB::select("
            SELECT
                COUNT(*)                                AS TOTAL_STUDENTS,
                SUM(TOTAL_APPLIED)                      AS TOTAL_APPLICATIONS,
                SUM(PLACEMENTS)                         AS TOTAL_PLACEMENTS,
                ROUND(AVG(PLACEMENT_RATE_PCT), 1)       AS AVG_PLACEMENT_RATE,
                MAX(GPA)                                AS HIGHEST_GPA,
                ROUND(AVG(GPA), 2)                      AS AVG_GPA
            FROM VW_STUDENT_PLACEMENT
        ")[0];

        $report = collect(array_map(fn($r) => (object)[
            'STUDENT_ID'         => $r->student_id,
            'STUDENT_NAME'       => $r->student_name,
            'DEPARTMENT'         => $r->department,
            'UNIVERSITY'         => $r->university,
            'GPA'                => $r->gpa,
            'TOTAL_APPLIED'      => $r->total_applied,
            'PLACEMENTS'         => $r->placements,
            'PLACEMENT_RATE_PCT' => $r->placement_rate_pct,
        ], $rows));

        return view('admin.reports.student_placement',
            compact('report', 'summary'));
    }

    // ─── Report 3: Skill Demand (from VW_SKILL_DEMAND) ─────────────────
    public function skillDemand()
    {
        $rows = DB::select("
            SELECT *
            FROM VW_SKILL_DEMAND
            ORDER BY REQUIRED_BY_INTERNSHIPS DESC, STUDENTS_WITH_SKILL DESC
        ");

        $report = collect(array_map(fn($r) => (object)[
            'SKILL_ID'                 => $r->skill_id,
            'SKILL_NAME'               => $r->skill_name,
            'CATEGORY'                 => $r->category,
            'REQUIRED_BY_INTERNSHIPS'  => $r->required_by_internships,
            'STUDENTS_WITH_SKILL'      => $r->students_with_skill,
        ], $rows));

        // Set operations demo — skills students have but no internship requires
        $surplusSkills = DB::select("
            SELECT sk.SKILL_NAME, sk.CATEGORY
            FROM SKILLS sk
            INNER JOIN STUDENT_SKILLS ss ON sk.SKILL_ID = ss.SKILL_ID
            MINUS
            SELECT sk.SKILL_NAME, sk.CATEGORY
            FROM SKILLS sk
            INNER JOIN INTERNSHIP_SKILLS ins ON sk.SKILL_ID = ins.SKILL_ID
        ");

        $surplusSkills = collect(array_map(fn($r) => (object)[
            'SKILL_NAME' => $r->skill_name,
            'CATEGORY'   => $r->category,
        ], $surplusSkills));

        // Skills both students have AND internships require — INTERSECT demo
        $matchedSkills = DB::select("
            SELECT sk.SKILL_NAME, sk.CATEGORY
            FROM SKILLS sk
            INNER JOIN STUDENT_SKILLS ss ON sk.SKILL_ID = ss.SKILL_ID
            INTERSECT
            SELECT sk.SKILL_NAME, sk.CATEGORY
            FROM SKILLS sk
            INNER JOIN INTERNSHIP_SKILLS ins ON sk.SKILL_ID = ins.SKILL_ID
        ");

        $matchedSkills = collect(array_map(fn($r) => (object)[
            'SKILL_NAME' => $r->skill_name,
            'CATEGORY'   => $r->category,
        ], $matchedSkills));

        return view('admin.reports.skill_demand',
            compact('report', 'surplusSkills', 'matchedSkills'));
    }

    // ─── Report 4: Company Activity ─────────────────────────────────────
    public function companyActivity()
    {
        // Multi-table JOIN with aggregates — correlated subquery for interview count
        $rows = DB::select("
            SELECT
                c.COMPANY_ID,
                c.COMPANY_NAME,
                c.INDUSTRY,
                c.LOCATION,
                COUNT(DISTINCT i.INTERNSHIP_ID)    AS TOTAL_POSTINGS,
                COUNT(DISTINCT a.APPLICATION_ID)   AS TOTAL_APPLICATIONS,
                SUM(CASE WHEN i.STATUS = 'Open'
                    THEN 1 ELSE 0 END)             AS OPEN_POSTINGS,
                SUM(CASE WHEN a.STATUS = 'Accepted'
                    THEN 1 ELSE 0 END)             AS TOTAL_ACCEPTED,
                (SELECT COUNT(*)
                 FROM INTERVIEWS iv
                 INNER JOIN APPLICATIONS a2
                     ON iv.APPLICATION_ID = a2.APPLICATION_ID
                 INNER JOIN INTERNSHIPS i2
                     ON a2.INTERNSHIP_ID = i2.INTERNSHIP_ID
                 WHERE i2.COMPANY_ID = c.COMPANY_ID) AS TOTAL_INTERVIEWS
            FROM COMPANIES c
            LEFT JOIN INTERNSHIPS  i ON c.COMPANY_ID    = i.COMPANY_ID
            LEFT JOIN APPLICATIONS a ON i.INTERNSHIP_ID = a.INTERNSHIP_ID
            GROUP BY c.COMPANY_ID, c.COMPANY_NAME, c.INDUSTRY, c.LOCATION
            ORDER BY TOTAL_APPLICATIONS DESC
        ");

        $report = collect(array_map(fn($r) => (object)[
            'COMPANY_ID'         => $r->company_id,
            'COMPANY_NAME'       => $r->company_name,
            'INDUSTRY'           => $r->industry,
            'LOCATION'           => $r->location,
            'TOTAL_POSTINGS'     => $r->total_postings,
            'TOTAL_APPLICATIONS' => $r->total_applications,
            'OPEN_POSTINGS'      => $r->open_postings,
            'TOTAL_ACCEPTED'     => $r->total_accepted,
            'TOTAL_INTERVIEWS'   => $r->total_interviews,
        ], $rows));

        return view('admin.reports.company_activity', compact('report'));
    }
}