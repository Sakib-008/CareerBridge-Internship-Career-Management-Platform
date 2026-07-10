<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function index()
    {
        $studentId = $this->getStudentId();

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, a.STATUS, a.APPLIED_AT, a.INTERNSHIP_ID,
                    i.TITLE, c.COMPANY_NAME
             FROM APPLICATIONS a
             INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
             INNER JOIN COMPANIES c ON i.COMPANY_ID = c.COMPANY_ID
             WHERE a.STUDENT_ID = :student_id
             ORDER BY a.APPLIED_AT DESC",
            ['student_id' => $studentId]
        );

        $applications = collect(array_map(fn($r) => (object)[
            'APPLICATION_ID' => $r->application_id,
            'STATUS'         => $r->status,
            'APPLIED_AT'     => $r->applied_at,
            'INTERNSHIP_ID'  => $r->internship_id,
            'internship' => (object)[
                'TITLE'        => $r->title,
                'company' => (object)[
                    'COMPANY_NAME' => $r->company_name,
                ],
            ],
        ], $rows));

        return view('student.applications.index', ['applications' => collect($applications)]);
    }

    public function create($internshipId)
    {
        $studentId = $this->getStudentId();

        $internshipRow = DB::select(
            "SELECT i.*, c.COMPANY_NAME
            FROM INTERNSHIPS i
            INNER JOIN COMPANIES c ON i.COMPANY_ID = c.COMPANY_ID
            WHERE i.INTERNSHIP_ID = :internship_id AND ROWNUM = 1",
            ['internship_id' => $internshipId]
        );

        if (empty($internshipRow)) abort(404);
        $r = $internshipRow[0];

        // Guards
        $alreadyApplied = DB::select(
                "SELECT COUNT(*) AS CNT FROM APPLICATIONS
                WHERE INTERNSHIP_ID = :internship_id AND STUDENT_ID = :student_id",
                ['internship_id' => $internshipId, 'student_id' => $studentId]
            )[0]->cnt > 0;
        if ($alreadyApplied) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'You have already applied to this internship.');
        }

        if ($r->status !== 'Open' || $r->application_deadline < now()->format('Y-m-d')) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'This internship is no longer accepting applications.');
        }

        $internship = (object)[
            'INTERNSHIP_ID' => $r->internship_id,
            'TITLE'         => $r->title,
            'LOCATION'      => $r->location,
            'company' => (object)['COMPANY_NAME' => $r->company_name],
        ];

        return view('student.applications.create', compact('internship'));
    }

    public function store(Request $request, $internshipId)
    {
        $validated = $request->validate([
            'cover_letter' => 'nullable|string|max:2000',
        ]);

        $studentId = $this->getStudentId();

        // Re-check guards
       $internshipRow = DB::select(
            "SELECT STATUS, APPLICATION_DEADLINE, TITLE, COMPANY_ID
            FROM INTERNSHIPS WHERE INTERNSHIP_ID = :internship_id AND ROWNUM = 1",
            ['internship_id' => $internshipId]
        );
        if (empty($internshipRow)) abort(404);
        $i = $internshipRow[0];

        if ($i->status !== 'Open' || $i->application_deadline < now()->format('Y-m-d')) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'This internship is no longer accepting applications.');
        }

        $alreadyApplied = DB::select(
            "SELECT COUNT(*) AS CNT FROM APPLICATIONS
            WHERE INTERNSHIP_ID = :internship_id AND STUDENT_ID = :student_id",
            ['internship_id' => $internshipId, 'student_id' => $studentId]
        )[0]->cnt > 0;

        if ($alreadyApplied) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'You have already applied to this internship.');
        }

        try {
            DB::transaction(function () use ($studentId, $internshipId, $validated, $i) {
                DB::insert(
                    "INSERT INTO APPLICATIONS (INTERNSHIP_ID, STUDENT_ID, COVER_LETTER, STATUS)
                     VALUES (:internship_id, :student_id, :cover_letter, 'Pending')",
                    [
                        'internship_id' => $internshipId,
                        'student_id'    => $studentId,
                        'cover_letter'  => $validated['cover_letter'] ?? null,
                    ]
                );

                // Get company user ID for notification
                $companyUser = DB::select(
                    "SELECT u.USER_ID FROM USERS u
                    INNER JOIN COMPANIES c ON u.USER_ID = c.USER_ID
                    WHERE c.COMPANY_ID = :company_id AND ROWNUM = 1",
                    ['company_id' => $i->company_id]
                );

                if (!empty($companyUser)) {
                    $studentName = DB::select(
                        "SELECT FIRST_NAME || ' ' || LAST_NAME AS FULL_NAME
                        FROM STUDENTS WHERE STUDENT_ID = :student_id AND ROWNUM = 1",
                        ['student_id' => $studentId]
                    )[0]->full_name;

                    DB::insert(
                        "INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE) VALUES (:user_id, :message)",
                        [
                            'user_id' => $companyUser[0]->user_id,
                            'message' => "New application received for \"{$i->title}\" from {$studentName}.",
                        ]
                    );
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'You have already applied to this internship.');
        }

        return redirect()->route('student.applications')
            ->with('success', 'Application submitted successfully!');
    }

    public function destroy($applicationId)
    {
        $studentId = $this->getStudentId();

        $rows = DB::select(
            "SELECT STATUS FROM APPLICATIONS
            WHERE APPLICATION_ID = :application_id AND STUDENT_ID = :student_id AND ROWNUM = 1",
            ['application_id' => $applicationId, 'student_id' => $studentId]
        );

        if (empty($rows)) abort(404);

        if ($rows[0]->status !== 'Pending') {
            return redirect()->route('student.applications')
                ->with('error', 'You can only withdraw Pending applications.');
        }

        DB::delete(
            "DELETE FROM APPLICATIONS
            WHERE APPLICATION_ID = :application_id AND STUDENT_ID = :student_id",
            ['application_id' => $applicationId, 'student_id' => $studentId]
        );

        return redirect()->route('student.applications')
            ->with('success', 'Application withdrawn successfully.');
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