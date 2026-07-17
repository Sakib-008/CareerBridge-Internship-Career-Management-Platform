<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        $where  = "WHERE i.COMPANY_ID = :company_id";
        $params = ['company_id' => $companyId];

        if ($request->filled('internship_id')) {
            $where .= " AND a.INTERNSHIP_ID = :internship_id";
            $params['internship_id'] = $request->internship_id;
        }
        if ($request->filled('status')) {
            $where .= " AND a.STATUS = :status";
            $params['status'] = $request->status;
        }

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, a.STATUS, a.APPLIED_AT,
                    s.FIRST_NAME, s.LAST_NAME,
                    i.TITLE AS INTERNSHIP_TITLE, i.INTERNSHIP_ID
             FROM APPLICATIONS a
             INNER JOIN STUDENTS s ON a.STUDENT_ID = s.STUDENT_ID
             INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
             $where
             ORDER BY a.APPLIED_AT DESC",
            $params
        );

        $applications = collect(array_map(fn($r) => (object)[
            'APPLICATION_ID' => $r->application_id,
            'STATUS'         => $r->status,
            'APPLIED_AT'     => $r->applied_at,
            'student' => (object)[
                'FIRST_NAME' => $r->first_name,
                'LAST_NAME'  => $r->last_name,
            ],
            'internship' => (object)[
                'INTERNSHIP_ID' => $r->internship_id,
                'TITLE'         => $r->internship_title,
            ],
        ], $rows));

        $internshipRows = DB::select(
            "SELECT INTERNSHIP_ID, TITLE FROM INTERNSHIPS
             WHERE COMPANY_ID = :company_id ORDER BY TITLE",
            ['company_id' => $companyId]
        );
        $internships = collect(array_map(fn($r) => (object)[
            'INTERNSHIP_ID' => $r->internship_id,
            'TITLE'         => $r->title,
        ], $internshipRows));

        return view('company.applications.index', [
            'applications' => collect($applications),
            'internships'  => collect($internships),
        ]);
    }

    public function show($id)
    {
        $companyId = $this->getCompanyId();

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, a.STATUS, a.APPLIED_AT, a.COVER_LETTER,
                    s.STUDENT_ID, s.FIRST_NAME, s.LAST_NAME, s.UNIVERSITY,
                    s.DEPARTMENT, s.GPA, s.PHONE, s.CV_FILE_PATH,
                    i.TITLE AS INTERNSHIP_TITLE
            FROM APPLICATIONS a
            INNER JOIN STUDENTS s ON a.STUDENT_ID = s.STUDENT_ID
            INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
            WHERE a.APPLICATION_ID = :application_id AND i.COMPANY_ID = :company_id
            AND ROWNUM = 1",
            ['application_id' => $id, 'company_id' => $companyId]
        );

        if (empty($rows)) abort(404);
        $r = $rows[0];

        // Load student skills with skill names
        $skillRows = DB::select(
            "SELECT ss.STUDENT_SKILL_ID, ss.PROFICIENCY, sk.SKILL_NAME
            FROM STUDENT_SKILLS ss
            INNER JOIN SKILLS sk ON ss.SKILL_ID = sk.SKILL_ID
            WHERE ss.STUDENT_ID = :student_id",
            ['student_id' => $r->student_id]
        );
        // Load interview if exists
        $interviewRows = DB::select(
            "SELECT * FROM INTERVIEWS
            WHERE APPLICATION_ID = :application_id AND ROWNUM = 1",
            ['application_id' => $id]
        );
        $interview = !empty($interviewRows) ? (object)[
            'INTERVIEW_ID'     => $interviewRows[0]->interview_id,
            'SCHEDULED_DATE'   => $interviewRows[0]->scheduled_date,
            'SCHEDULED_TIME'   => $interviewRows[0]->scheduled_time,
            'INTERVIEW_MODE'   => $interviewRows[0]->interview_mode,
            'LOCATION_OR_LINK' => $interviewRows[0]->location_or_link,
            'NOTES'            => $interviewRows[0]->notes,
        ] : null;

        $application = (object)[
            'APPLICATION_ID' => $r->application_id,
            'STATUS'         => $r->status,
            'APPLIED_AT'     => $r->applied_at,
            'COVER_LETTER'   => $r->cover_letter,
            'interview'      => $interview,
            'student' => (object)[
                'STUDENT_ID'   => $r->student_id,
                'FIRST_NAME'   => $r->first_name,
                'LAST_NAME'    => $r->last_name,
                'UNIVERSITY'   => $r->university,
                'DEPARTMENT'   => $r->department,
                'GPA'          => $r->gpa,
                'PHONE'        => $r->phone,
                'CV_FILE_PATH' => $r->cv_file_path,
                'studentSkills' => array_map(fn($s) => (object)[
                    'PROFICIENCY' => $s->proficiency,
                    'skill' => (object)['SKILL_NAME' => $s->skill_name],
                ], $skillRows),
                'USER_ID' => DB::select(
                    "SELECT USER_ID FROM STUDENTS WHERE STUDENT_ID = :student_id AND ROWNUM = 1",
                    ['student_id' => $r->student_id]
                )[0]->user_id,
            ],
            'internship' => (object)[
                'TITLE' => $r->internship_title,
            ],
        ];

        return view('company.applications.show', compact('application'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Reviewed,Shortlisted,Interview,Accepted,Rejected',
        ]);

        $companyId = $this->getCompanyId();

        // Verify this application belongs to this company
        $check = DB::select(
            "SELECT a.APPLICATION_ID, i.TITLE
            FROM APPLICATIONS a
            INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
            WHERE a.APPLICATION_ID = :application_id
            AND i.COMPANY_ID = :company_id AND ROWNUM = 1",
            ['application_id' => $id, 'company_id' => $companyId]
        );

        if (empty($check)) abort(404);

        // Call the stored procedure via PDO
        try {
            $pdo = DB::getPdo();

            $sql = "BEGIN SP_UPDATE_APPLICATION_STATUS(
                        :application_id,
                        :new_status,
                        :changed_by,
                        :result
                    ); END;";

            $stmt = $pdo->prepare($sql);

            $applicationId = (int) $id;
            $newStatus     = $request->status;
            $changedBy     = (int) Auth::id();
            $result        = '';

            $stmt->bindParam(':application_id', $applicationId, \PDO::PARAM_INT);
            $stmt->bindParam(':new_status',     $newStatus,     \PDO::PARAM_STR);
            $stmt->bindParam(':changed_by',     $changedBy,     \PDO::PARAM_INT);
            $stmt->bindParam(':result',         $result,        \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

            $stmt->execute();

            if (str_starts_with($result, 'ERROR')) {
                return redirect()->back()->with('error', $result);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update status: ' . $e->getMessage());
        }

        return redirect()->back()
            ->with('success', "Application status updated to {$request->status}.");
    }
    private function getCompanyId(): int
    {
        $row = DB::select(
            "SELECT COMPANY_ID FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        );
        return (int) $row[0]->company_id;
    }
}