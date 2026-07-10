<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    public function show()
    {
        $rows = DB::select(
            "SELECT * FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        );

        if (empty($rows)) abort(404);

        $s = $rows[0];
        $student = $this->mapStudent($s);

        return view('student.profile', compact('student'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:50',
            'last_name'       => 'required|string|max:50',
            'phone'           => 'nullable|string|max:20',
            'date_of_birth'   => 'nullable|date|before:today',
            'university'      => 'required|string|max:100',
            'department'      => 'required|string|max:100',
            'gpa'             => 'nullable|numeric|min:0|max:4',
            'graduation_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 10),
            'profile_summary' => 'nullable|string|max:1000',
        ]);

        $studentId = $this->getStudentId();

        DB::update(
            "UPDATE STUDENTS SET
                FIRST_NAME      = :first_name,
                LAST_NAME       = :last_name,
                PHONE           = :phone,
                DATE_OF_BIRTH   = :dob,
                UNIVERSITY      = :university,
                DEPARTMENT      = :department,
                GPA             = :gpa,
                GRADUATION_YEAR = :grad_year,
                PROFILE_SUMMARY = :summary
             WHERE STUDENT_ID   = :student_id",
            [
                'first_name'  => $validated['first_name'],
                'last_name'   => $validated['last_name'],
                'phone'       => $validated['phone'] ?? null,
                'dob'         => $validated['date_of_birth'] ?? null,
                'university'  => $validated['university'],
                'department'  => $validated['department'],
                'gpa'         => $validated['gpa'] ?? null,
                'grad_year'   => $validated['graduation_year'] ?? null,
                'summary'     => $validated['profile_summary'] ?? null,
                'student_id'  => $studentId,
            ]
        );

        return redirect()->route('student.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function uploadCv(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $studentId = $this->getStudentId();

        $rows = DB::select(
            "SELECT CV_FILE_PATH FROM STUDENTS WHERE STUDENT_ID = :id AND ROWNUM = 1",
            ['id' => $studentId]
        );

        $oldPath = $rows[0]->cv_file_path ?? null;

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('cv_file')->store('cvs', 'public');

        DB::update(
            "UPDATE STUDENTS SET CV_FILE_PATH = :path WHERE STUDENT_ID = :id",
            ['path' => $path, 'id' => $studentId]
        );

        return redirect()->route('student.profile')
            ->with('success', 'CV uploaded successfully.');
    }

    public function deleteCv()
    {
        $studentId = $this->getStudentId();

        $rows = DB::select(
            "SELECT CV_FILE_PATH FROM STUDENTS WHERE STUDENT_ID = :id AND ROWNUM = 1",
            ['id' => $studentId]
        );

        $path = $rows[0]->cv_file_path ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        DB::update(
            "UPDATE STUDENTS SET CV_FILE_PATH = NULL WHERE STUDENT_ID = :id",
            ['id' => $studentId]
        );

        return redirect()->route('student.profile')
            ->with('success', 'CV removed successfully.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────
    private function getStudentId(): int
    {
        $row = DB::select(
            "SELECT STUDENT_ID FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        );
        return (int) $row[0]->student_id;
    }

    private function mapStudent(object $s): object
    {
        return (object) [
            'STUDENT_ID'      => $s->student_id,
            'FIRST_NAME'      => $s->first_name,
            'LAST_NAME'       => $s->last_name,
            'PHONE'           => $s->phone,
            'DATE_OF_BIRTH'   => $s->date_of_birth,
            'UNIVERSITY'      => $s->university,
            'DEPARTMENT'      => $s->department,
            'GPA'             => $s->gpa,
            'GRADUATION_YEAR' => $s->graduation_year,
            'CV_FILE_PATH'    => $s->cv_file_path,
            'PROFILE_SUMMARY' => $s->profile_summary,
        ];
    }
}