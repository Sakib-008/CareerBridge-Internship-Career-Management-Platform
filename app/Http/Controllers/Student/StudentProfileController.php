<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    // Show Profile (View/Edit Form)
    public function show()
    {
        $student = Auth::user()->student;

        return view('student.profile', compact('student'));
    }

    // Update Profile Info
    public function update(Request $request)
    {
        $student = Auth::user()->student;

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

        $student->update([
            'FIRST_NAME'       => $validated['first_name'],
            'LAST_NAME'        => $validated['last_name'],
            'PHONE'            => $validated['phone'] ?? null,
            'DATE_OF_BIRTH'    => $validated['date_of_birth'] ?? null,
            'UNIVERSITY'       => $validated['university'],
            'DEPARTMENT'       => $validated['department'],
            'GPA'              => $validated['gpa'] ?? null,
            'GRADUATION_YEAR'  => $validated['graduation_year'] ?? null,
            'PROFILE_SUMMARY'  => $validated['profile_summary'] ?? null,
        ]);

        return redirect()->route('student.profile')
            ->with('success', 'Profile updated successfully.');
    }

    // Upload / Replace CV
    public function uploadCv(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $student = Auth::user()->student;

        // Delete old CV file if it exists
        if ($student->CV_FILE_PATH && Storage::disk('public')->exists($student->CV_FILE_PATH)) {
            Storage::disk('public')->delete($student->CV_FILE_PATH);
        }

        $path = $request->file('cv_file')->store('cvs', 'public');

        $student->update(['CV_FILE_PATH' => $path]);

        return redirect()->route('student.profile')
            ->with('success', 'CV uploaded successfully.');
    }

    // Delete CV
    public function deleteCv()
    {
        $student = Auth::user()->student;

        if ($student->CV_FILE_PATH && Storage::disk('public')->exists($student->CV_FILE_PATH)) {
            Storage::disk('public')->delete($student->CV_FILE_PATH);
        }

        $student->update(['CV_FILE_PATH' => null]);

        return redirect()->route('student.profile')
            ->with('success', 'CV removed successfully.');
    }
}