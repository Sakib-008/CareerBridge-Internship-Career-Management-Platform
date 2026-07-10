<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;


class InternshipController extends Controller
{
    public function index(Request $request)
    {
        // Build dynamic WHERE clause
        $where  = "WHERE i.STATUS = 'Open' AND i.APPLICATION_DEADLINE >= TRUNC(SYSDATE)";
        $params = [];

        if ($request->filled('keyword')) {
            $where .= " AND UPPER(i.TITLE) LIKE UPPER(:keyword)";
            $params['keyword'] = '%' . $request->keyword . '%';
        }
        if ($request->filled('location')) {
            $where .= " AND UPPER(i.LOCATION) LIKE UPPER(:location)";
            $params['location'] = '%' . $request->location . '%';
        }
        if ($request->filled('type')) {
            $where .= " AND i.INTERNSHIP_TYPE = :type";
            $params['type'] = $request->type;
        }
        if ($request->filled('skill_id')) {
            $where .= " AND EXISTS (
                SELECT 1 FROM INTERNSHIP_SKILLS ins2
                WHERE ins2.INTERNSHIP_ID = i.INTERNSHIP_ID
                AND ins2.SKILL_ID = :skill_id
            )";
            $params['skill_id'] = $request->skill_id;
        }

        // Total count for paginator
        $total = DB::select(
            "SELECT COUNT(*) AS CNT FROM INTERNSHIPS i $where",
            $params
        )[0]->cnt;

        $perPage     = 9;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $offset      = ($currentPage - 1) * $perPage;

        // Fetch current page rows using Oracle ROW_NUMBER()
        $rows = DB::select(
            "SELECT * FROM (
                SELECT i.INTERNSHIP_ID, i.TITLE, i.LOCATION, i.INTERNSHIP_TYPE,
                    i.STIPEND, i.APPLICATION_DEADLINE, i.STATUS, i.DESCRIPTION,
                    c.COMPANY_NAME, c.INDUSTRY,
                    ROW_NUMBER() OVER (ORDER BY i.CREATED_AT DESC) AS RN
                FROM INTERNSHIPS i
                INNER JOIN COMPANIES c ON i.COMPANY_ID = c.COMPANY_ID
                $where
            ) WHERE RN > :offset AND RN <= :limit",
            array_merge($params, [
                'offset' => $offset,
                'limit'  => $offset + $perPage,
            ])
        );

        // Map to UPPERCASE objects for Blade compatibility
        $mapped = collect(array_map(fn($r) => (object)[
            'INTERNSHIP_ID'        => $r->internship_id,
            'TITLE'                => $r->title,
            'LOCATION'             => $r->location,
            'INTERNSHIP_TYPE'      => $r->internship_type,
            'STIPEND'              => $r->stipend,
            'APPLICATION_DEADLINE' => $r->application_deadline,
            'STATUS'               => $r->status,
            'DESCRIPTION'          => $r->description,
            'company' => (object)[
                'COMPANY_NAME' => $r->company_name,
                'INDUSTRY'     => $r->industry,
            ],
        ], $rows));

        // Wrap in LengthAwarePaginator — gives us ->links() in the view
        $internships = new LengthAwarePaginator(
            $mapped,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Applied internship IDs for current student
        $appliedInternshipIds = [];
        if (Auth::check() && Auth::user()->isStudent()) {
            $studentRow = DB::select(
                "SELECT STUDENT_ID FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
                ['user_id' => Auth::id()]
            );
            if (!empty($studentRow)) {
                $applied = DB::select(
                    "SELECT INTERNSHIP_ID FROM APPLICATIONS WHERE STUDENT_ID = :student_id",
                    ['student_id' => $studentRow[0]->student_id]
                );
                $appliedInternshipIds = array_column($applied, 'internship_id');
            }
        }

        $skillRows = DB::select(
            "SELECT SKILL_ID, SKILL_NAME FROM SKILLS ORDER BY SKILL_NAME"
        );
        $skills = collect(array_map(fn($r) => (object)[
            'SKILL_ID'   => $r->skill_id,
            'SKILL_NAME' => $r->skill_name,
        ], $skillRows));

        return view('internships.index', compact(
            'internships', 'skills', 'appliedInternshipIds'
        ));
    }

    public function show($id)
    {
        $rows = DB::select(
            "SELECT i.*, c.COMPANY_NAME, c.INDUSTRY
            FROM INTERNSHIPS i
            INNER JOIN COMPANIES c ON i.COMPANY_ID = c.COMPANY_ID
            WHERE i.INTERNSHIP_ID = :internship_id AND ROWNUM = 1",
            ['internship_id' => $id]
        );


        if (empty($rows)) abort(404);
        $r = $rows[0];

        $skillRows = DB::select(
            "SELECT sk.SKILL_NAME, ins.IS_MANDATORY
            FROM INTERNSHIP_SKILLS ins
            INNER JOIN SKILLS sk ON ins.SKILL_ID = sk.SKILL_ID
            WHERE ins.INTERNSHIP_ID = :internship_id",
            ['internship_id' => $id]
        );

        $internship = (object)[
            'INTERNSHIP_ID'        => $r->internship_id,
            'TITLE'                => $r->title,
            'DESCRIPTION'          => $r->description,
            'LOCATION'             => $r->location,
            'INTERNSHIP_TYPE'      => $r->internship_type,
            'DURATION_MONTHS'      => $r->duration_months,
            'STIPEND'              => $r->stipend,
            'VACANCIES'            => $r->vacancies,
            'APPLICATION_DEADLINE' => $r->application_deadline,
            'STATUS'               => $r->status,
            'company' => (object)[
                'COMPANY_NAME' => $r->company_name,
                'INDUSTRY'     => $r->industry,
            ],
            'skills' => array_map(fn($s) => (object)[
                'SKILL_NAME' => $s->skill_name,
                'pivot'      => (object)['IS_MANDATORY' => $s->is_mandatory],
            ], $skillRows),
        ];

        $hasApplied = false;
        if (Auth::check() && Auth::user()->isStudent()) {
            $studentRow = DB::select(
                "SELECT STUDENT_ID FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
                ['user_id' => Auth::id()]
            );
            if (!empty($studentRow)) {
            $appCheck = DB::select(
                "SELECT COUNT(*) AS CNT FROM APPLICATIONS
                WHERE INTERNSHIP_ID = :internship_id AND STUDENT_ID = :student_id",
                ['internship_id' => $id, 'student_id' => $studentRow[0]->student_id]
            );
                $hasApplied = $appCheck[0]->cnt > 0;
            }
        }

        return view('internships.show', compact('internship', 'hasApplied'));
    }
}