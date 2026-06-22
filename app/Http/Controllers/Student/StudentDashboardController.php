<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $skillCount       = $student->studentSkills()->count();
        $applicationCount = $student->applications()->count();

        // Profile completeness calculation
        $fields = [
            $student->FIRST_NAME && $student->FIRST_NAME !== 'New',
            $student->LAST_NAME && $student->LAST_NAME !== 'Student',
            $student->UNIVERSITY && $student->UNIVERSITY !== 'Not Set',
            $student->DEPARTMENT && $student->DEPARTMENT !== 'Not Set',
            $student->GPA !== null,
            $student->GRADUATION_YEAR !== null,
            $student->CV_FILE_PATH !== null,
            $skillCount > 0,
        ];

        $completeness = (int) round((count(array_filter($fields)) / count($fields)) * 100);

        return view('student.dashboard', compact(
            'student', 'skillCount', 'applicationCount', 'completeness'
        ));
    }
}