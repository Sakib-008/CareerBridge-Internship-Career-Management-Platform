<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterviewController extends Controller
{
    public function index()
    {
        $studentId = $this->getStudentId();

        $rows = DB::select(
            "SELECT a.APPLICATION_ID, a.STATUS,
                    i.TITLE AS INTERNSHIP_TITLE,
                    c.COMPANY_NAME,
                    iv.INTERVIEW_ID, iv.SCHEDULED_DATE, iv.SCHEDULED_TIME,
                    iv.INTERVIEW_MODE, iv.LOCATION_OR_LINK, iv.NOTES
             FROM APPLICATIONS a
             INNER JOIN INTERNSHIPS i  ON a.INTERNSHIP_ID  = i.INTERNSHIP_ID
             INNER JOIN COMPANIES c    ON i.COMPANY_ID     = c.COMPANY_ID
             INNER JOIN INTERVIEWS iv  ON a.APPLICATION_ID = iv.APPLICATION_ID
             WHERE a.STUDENT_ID = :student_id
             AND a.STATUS = 'Interview'
             ORDER BY iv.SCHEDULED_DATE ASC",
            ['student_id' => $studentId]
        );

        $interviews = collect(array_map(fn($r) => (object)[
            'APPLICATION_ID' => $r->application_id,
            'STATUS'         => $r->status,
            'internship' => (object)[
                'TITLE'   => $r->internship_title,
                'company' => (object)['COMPANY_NAME' => $r->company_name],
            ],
            'interview' => (object)[
                'SCHEDULED_DATE'   => $r->scheduled_date,
                'SCHEDULED_TIME'   => $r->scheduled_time,
                'INTERVIEW_MODE'   => $r->interview_mode,
                'LOCATION_OR_LINK' => $r->location_or_link,
                'NOTES'            => $r->notes,
            ],
        ], $rows));

        return view('student.interviews.index', ['interviews' => collect($interviews)]);
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