<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterviewController extends Controller
{
    public function create($applicationId)
    {
        $companyId = $this->getCompanyId();

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, a.STATUS,
                    s.FIRST_NAME, s.LAST_NAME,
                    i.TITLE AS INTERNSHIP_TITLE
            FROM APPLICATIONS a
            INNER JOIN STUDENTS s ON a.STUDENT_ID = s.STUDENT_ID
            INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
            WHERE a.APPLICATION_ID = :application_id AND i.COMPANY_ID = :company_id AND ROWNUM = 1",
            ['application_id' => $applicationId, 'company_id' => $companyId]
        );

        if (empty($rows)) abort(404);
        $r = $rows[0];

        if (!in_array($r->status, ['Shortlisted', 'Interview'])) {
            return redirect()->route('company.applications.show', $applicationId)
                ->with('error', 'Only shortlisted applicants can be scheduled for an interview.');
        }

        $application = (object)[
            'APPLICATION_ID' => $r->application_id,
            'STATUS'         => $r->status,
            'student' => (object)[
                'FIRST_NAME' => $r->first_name,
                'LAST_NAME'  => $r->last_name,
            ],
            'internship' => (object)['TITLE' => $r->internship_title],
        ];

        $interviewRows = DB::select(
            "SELECT * FROM INTERVIEWS WHERE APPLICATION_ID = :application_id AND ROWNUM = 1",
            ['application_id' => $applicationId]
        );

        $existingInterview = !empty($interviewRows) ? (object)[
            'SCHEDULED_DATE'   => $interviewRows[0]->scheduled_date,
            'SCHEDULED_TIME'   => $interviewRows[0]->scheduled_time,
            'INTERVIEW_MODE'   => $interviewRows[0]->interview_mode,
            'LOCATION_OR_LINK' => $interviewRows[0]->location_or_link,
            'NOTES'            => $interviewRows[0]->notes,
        ] : null;

        return view('company.interviews.create', compact('application', 'existingInterview'));
    }

    public function store(Request $request, $applicationId)
    {
        $validated = $request->validate([
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'scheduled_time'   => 'required|string|max:10',
            'interview_mode'   => 'required|in:In-person,Video,Phone',
            'location_or_link' => 'nullable|string|max:200',
            'notes'            => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCompanyId();

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, s.USER_ID AS STUDENT_USER_ID,
                    s.FIRST_NAME, s.LAST_NAME, i.TITLE
            FROM APPLICATIONS a
            INNER JOIN STUDENTS s ON a.STUDENT_ID = s.STUDENT_ID
            INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
            WHERE a.APPLICATION_ID = :application_id AND i.COMPANY_ID = :company_id AND ROWNUM = 1",
            ['application_id' => $applicationId, 'company_id' => $companyId]
        );

        if (empty($rows)) abort(404);
        $r = $rows[0];

        $existingCount = DB::select(
            "SELECT COUNT(*) AS CNT FROM INTERVIEWS WHERE APPLICATION_ID = :application_id",
            ['application_id' => $applicationId]
        )[0]->cnt;

        if ($existingCount > 0) {
            DB::update(
                "UPDATE INTERVIEWS SET
                    SCHEDULED_DATE   = :scheduled_date,
                    SCHEDULED_TIME   = :scheduled_time,
                    INTERVIEW_MODE   = :interview_mode,
                    LOCATION_OR_LINK = :location_or_link,
                    NOTES            = :notes
                WHERE APPLICATION_ID = :application_id",
                [
                    'scheduled_date'   => $validated['scheduled_date'],
                    'scheduled_time'   => $validated['scheduled_time'],
                    'interview_mode'   => $validated['interview_mode'],
                    'location_or_link' => $validated['location_or_link'] ?? null,
                    'notes'            => $validated['notes'] ?? null,
                    'application_id'   => $applicationId,
                ]
            );
            $msg = "Your interview for \"{$r->title}\" has been rescheduled to {$validated['scheduled_date']} at {$validated['scheduled_time']}.";
        } else {
            DB::insert(
                "INSERT INTO INTERVIEWS
                    (APPLICATION_ID, SCHEDULED_DATE, SCHEDULED_TIME,
                     INTERVIEW_MODE, LOCATION_OR_LINK, NOTES)
                 VALUES
                    (:application_id, :scheduled_date, :scheduled_time,
                     :interview_mode, :location_or_link, :notes)",
                [
                    'application_id'   => $applicationId,
                    'scheduled_date'   => $validated['scheduled_date'],
                    'scheduled_time'   => $validated['scheduled_time'],
                    'interview_mode'   => $validated['interview_mode'],
                    'location_or_link' => $validated['location_or_link'] ?? null,
                    'notes'            => $validated['notes'] ?? null,
                ]
            );
            $msg = "Your interview for \"{$r->title}\" has been scheduled on {$validated['scheduled_date']} at {$validated['scheduled_time']}.";
        }

        DB::update(
            "UPDATE APPLICATIONS SET STATUS = 'Interview' WHERE APPLICATION_ID = :application_id",
            ['application_id' => $applicationId]
        );

        DB::insert(
            "INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE) VALUES (:user_id, :message)",
            ['user_id' => $r->student_user_id, 'message' => $msg]
        );

        return redirect()->route('company.applications.show', $applicationId)
            ->with('success', 'Interview scheduled successfully.');
    }

    public function destroy($applicationId)
    {
        $companyId = $this->getCompanyId();

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, s.USER_ID AS STUDENT_USER_ID, i.TITLE
            FROM APPLICATIONS a
            INNER JOIN STUDENTS s ON a.STUDENT_ID = s.STUDENT_ID
            INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
            WHERE a.APPLICATION_ID = :application_id AND i.COMPANY_ID = :company_id AND ROWNUM = 1",
            ['application_id' => $applicationId, 'company_id' => $companyId]
        );
        if (empty($rows)) abort(404);
        $r = $rows[0];

        DB::delete(
            "DELETE FROM INTERVIEWS WHERE APPLICATION_ID = :application_id",
            ['application_id' => $applicationId]
        );

        DB::update(
            "UPDATE APPLICATIONS SET STATUS = 'Shortlisted' WHERE APPLICATION_ID = :application_id",
            ['application_id' => $applicationId]
        );

        DB::insert(
            "INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE) VALUES (:user_id, :message)",
            [
                'user_id' => $r->student_user_id,
                'message' => "Your interview for \"{$r->title}\" has been cancelled.",
            ]
        );

        return redirect()->route('company.applications.show', $applicationId)
            ->with('success', 'Interview cancelled.');
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