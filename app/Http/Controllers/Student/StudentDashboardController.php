<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $studentRow = DB::select(
            "SELECT * FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => $userId]
        );

        if (empty($studentRow)) {
            abort(404, 'Student profile not found.');
        }

        $s = $studentRow[0];

        $skillCount = DB::select(
            "SELECT COUNT(*) AS CNT FROM STUDENT_SKILLS WHERE STUDENT_ID = :id",
            ['id' => $s->student_id]
        )[0]->cnt;

        $applicationCount = DB::select(
            "SELECT COUNT(*) AS CNT FROM APPLICATIONS WHERE STUDENT_ID = :id",
            ['id' => $s->student_id]
        )[0]->cnt;

        $fields = [
            !empty($s->first_name) && $s->first_name !== 'New',
            !empty($s->last_name)  && $s->last_name  !== 'Student',
            !empty($s->university) && $s->university  !== 'Not Set',
            !empty($s->department) && $s->department  !== 'Not Set',
            !is_null($s->gpa),
            !is_null($s->graduation_year),
            !is_null($s->cv_file_path),
            $skillCount > 0,
        ];

        $completeness = (int) round(
            (count(array_filter($fields)) / count($fields)) * 100
        );

        // Map to UPPERCASE for Blade compatibility
        $student = (object) [
            'STUDENT_ID'      => $s->student_id,
            'FIRST_NAME'      => $s->first_name,
            'LAST_NAME'       => $s->last_name,
            'UNIVERSITY'      => $s->university,
            'DEPARTMENT'      => $s->department,
            'GPA'             => $s->gpa,
            'GRADUATION_YEAR' => $s->graduation_year,
            'CV_FILE_PATH'    => $s->cv_file_path,
        ];

        return view('student.dashboard', compact(
            'student', 'skillCount', 'applicationCount', 'completeness'
        ));
    }
}