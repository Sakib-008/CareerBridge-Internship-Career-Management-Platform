<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternshipController extends Controller
{
    public function index()
    {
        $companyId = $this->getCompanyId();

        $rows = DB::select(
            "SELECT i.*,
                    COUNT(a.APPLICATION_ID) AS APPLICATIONS_COUNT
             FROM INTERNSHIPS i
             LEFT JOIN APPLICATIONS a ON i.INTERNSHIP_ID = a.INTERNSHIP_ID
             WHERE i.COMPANY_ID = :company_id
             GROUP BY i.INTERNSHIP_ID, i.COMPANY_ID, i.TITLE, i.DESCRIPTION,
                      i.LOCATION, i.INTERNSHIP_TYPE, i.DURATION_MONTHS,
                      i.STIPEND, i.VACANCIES, i.APPLICATION_DEADLINE,
                      i.STATUS, i.CREATED_AT, i.UPDATED_AT
             ORDER BY i.CREATED_AT DESC",
            ['company_id' => $companyId]
        );

        $internships = collect(array_map(fn($r) => (object)[
            'INTERNSHIP_ID'        => $r->internship_id,
            'TITLE'                => $r->title,
            'INTERNSHIP_TYPE'      => $r->internship_type,
            'APPLICATION_DEADLINE' => $r->application_deadline,
            'VACANCIES'            => $r->vacancies,
            'STATUS'               => $r->status,
            'applications_count'   => $r->applications_count,
        ], $rows));

        return view('company.internships.index', ['internships' => collect($internships)]);
    }

    public function create()
    {
        $skills = $this->getAllSkillsGrouped();
        return view('company.internships.create', compact('skills'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInternship($request);
        $companyId = $this->getCompanyId();

        DB::transaction(function () use ($validated, $companyId, $request) {
            DB::insert(
                "INSERT INTO INTERNSHIPS
                    (COMPANY_ID, TITLE, DESCRIPTION, LOCATION, INTERNSHIP_TYPE,
                     DURATION_MONTHS, STIPEND, VACANCIES, APPLICATION_DEADLINE, STATUS)
                 VALUES
                    (:company_id, :title, :description, :location, :internship_type,
                     :duration_months, :stipend, :vacancies, :deadline, 'Open')",
                [
                    'company_id'      => $companyId,
                    'title'           => $validated['title'],
                    'description'     => $validated['description'],
                    'location'        => $validated['location'],
                    'internship_type' => $validated['internship_type'],
                    'duration_months' => $validated['duration_months'],
                    'stipend'         => $validated['stipend'] ?? 0,
                    'vacancies'       => $validated['vacancies'],
                    'deadline'        => $validated['application_deadline'],
                ]
            );

            // Get the newly created internship ID
            $row = DB::select(
                "SELECT *
                FROM (
                    SELECT INTERNSHIP_ID
                    FROM INTERNSHIPS
                    WHERE COMPANY_ID = :company_id
                    ORDER BY CREATED_AT DESC
                )
                WHERE ROWNUM = 1",
                ['company_id' => $companyId]
            );

            $internshipId = $row[0]->internship_id;
            $this->syncSkills($internshipId, $request);
        });

        return redirect()->route('company.internships')
            ->with('success', 'Internship posted successfully.');
    }

    public function edit($id)
    {
        $companyId = $this->getCompanyId();

        $rows = DB::select(
            "SELECT * FROM INTERNSHIPS
            WHERE INTERNSHIP_ID = :internship_id AND COMPANY_ID = :company_id AND ROWNUM = 1",
            ['internship_id' => $id, 'company_id' => $companyId]
        );

        if (empty($rows)) abort(404);

        $r = $rows[0];
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
        ];

        $skills = $this->getAllSkillsGrouped();

        $attachedRows = DB::select(
            "SELECT SKILL_ID, IS_MANDATORY FROM INTERNSHIP_SKILLS
            WHERE INTERNSHIP_ID = :internship_id",
            ['internship_id' => $id]
        );

        $selectedSkillIds  = array_column($attachedRows, 'skill_id');
        $mandatorySkillIds = array_map(
            fn($r) => $r->skill_id,
            array_filter($attachedRows, fn($r) => (string)$r->is_mandatory === '1')
        );

        return view('company.internships.edit', compact(
            'internship', 'skills', 'selectedSkillIds', 'mandatorySkillIds'
        ));
    }

    public function update(Request $request, $id)
    {
        $companyId  = $this->getCompanyId();
        $validated  = $this->validateInternship($request);

        DB::transaction(function () use ($id, $companyId, $validated, $request) {
            DB::update(
                "UPDATE INTERNSHIPS SET
                    TITLE                = :title,
                    DESCRIPTION          = :description,
                    LOCATION             = :location,
                    INTERNSHIP_TYPE      = :internship_type,
                    DURATION_MONTHS      = :duration_months,
                    STIPEND              = :stipend,
                    VACANCIES            = :vacancies,
                    APPLICATION_DEADLINE = :deadline
                WHERE INTERNSHIP_ID = :internship_id AND COMPANY_ID = :company_id",
                [
                    'title'           => $validated['title'],
                    'description'     => $validated['description'],
                    'location'        => $validated['location'],
                    'internship_type' => $validated['internship_type'],
                    'duration_months' => $validated['duration_months'],
                    'stipend'         => $validated['stipend'] ?? 0,
                    'vacancies'       => $validated['vacancies'],
                    'deadline'        => $validated['application_deadline'],
                    'internship_id'   => $id,
                    'company_id'      => $companyId,
                ]
            );

            $this->syncSkills($id, $request);
        });

        return redirect()->route('company.internships')
            ->with('success', 'Internship updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Open,Closed,Paused']);
        $companyId = $this->getCompanyId();

        DB::update(
            "UPDATE INTERNSHIPS SET STATUS = :status
            WHERE INTERNSHIP_ID = :internship_id AND COMPANY_ID = :company_id",
            ['status' => $request->status, 'internship_id' => $id, 'company_id' => $companyId]
        );

        return redirect()->route('company.internships')
            ->with('success', "Internship marked as {$request->status}.");
    }

    public function destroy($id)
    {
        $companyId = $this->getCompanyId();

       DB::delete(
            "DELETE FROM INTERNSHIPS
            WHERE INTERNSHIP_ID = :internship_id AND COMPANY_ID = :company_id",
            ['internship_id' => $id, 'company_id' => $companyId]
        );
        return redirect()->route('company.internships')
            ->with('success', 'Internship deleted successfully.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────
    private function getCompanyId(): int
    {
        $row = DB::select(
            "SELECT COMPANY_ID FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        );
        return (int) $row[0]->company_id;
    }

    private function getAllSkillsGrouped(): array
    {
        $rows = DB::select(
            "SELECT SKILL_ID, SKILL_NAME, CATEGORY
             FROM SKILLS ORDER BY CATEGORY, SKILL_NAME"
        );

        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r->category][] = (object)[
                'SKILL_ID'   => $r->skill_id,
                'SKILL_NAME' => $r->skill_name,
                'CATEGORY'   => $r->category,
            ];
        }
        return $grouped;
    }

    private function syncSkills(int $internshipId, Request $request): void
    {
        $skillIds     = $request->input('skill_ids', []);
        $mandatoryIds = $request->input('mandatory_skill_ids', []);

        // Delete existing skill links for this internship
        DB::delete(
            "DELETE FROM INTERNSHIP_SKILLS WHERE INTERNSHIP_ID = :internship_id",
            ['internship_id' => $internshipId]
        );

        // Re-insert
        foreach ($skillIds as $skillId) {
            $isMandatory = in_array($skillId, $mandatoryIds) ? 1 : 0;
            DB::insert(
                "INSERT INTO INTERNSHIP_SKILLS (INTERNSHIP_ID, SKILL_ID, IS_MANDATORY)
                 VALUES (:internship_id, :skill_id, :is_mandatory)",
                [
                    'internship_id' => $internshipId,
                    'skill_id'      => $skillId,
                    'is_mandatory'  => $isMandatory,
                ]
            );
        }
    }

    private function validateInternship(Request $request): array
    {
        return $request->validate([
            'title'                => 'required|string|max:150',
            'description'          => 'required|string|max:3000',
            'location'             => 'required|string|max:100',
            'internship_type'      => 'required|in:Remote,On-site,Hybrid',
            'duration_months'      => 'required|integer|min:1|max:24',
            'stipend'              => 'nullable|numeric|min:0',
            'vacancies'            => 'required|integer|min:1',
            'application_deadline' => 'required|date|after:today',
            'skill_ids'            => 'required|array|min:1',
            'skill_ids.*'          => 'integer',
        ]);
    }
}