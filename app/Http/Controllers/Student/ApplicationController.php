<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    // ─── List My Applications ───────────────────────────────────────────
    public function index()
    {
        $student = Auth::user()->student;

        $applications = $student->applications()
            ->with('internship.company')
            ->orderBy('APPLIED_AT', 'desc')
            ->get();

        return view('student.applications.index', compact('applications'));
    }

    // ─── Show Apply Form ─────────────────────────────────────────────────
    public function create($internshipId)
    {
        $internship = Internship::with(['company', 'skills'])
            ->where('INTERNSHIP_ID', $internshipId)
            ->firstOrFail();

        $student = Auth::user()->student;

        // Guard: already applied
        $alreadyApplied = $student->applications()
            ->where('INTERNSHIP_ID', $internshipId)
            ->exists();

        if ($alreadyApplied) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'You have already applied to this internship.');
        }

        // Guard: deadline passed or internship closed
        if ($internship->STATUS !== 'Open' || $internship->APPLICATION_DEADLINE < now()->format('Y-m-d')) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'This internship is no longer accepting applications.');
        }

        return view('student.applications.create', compact('internship'));
    }

    // ─── Submit Application ─────────────────────────────────────────────
    public function store(Request $request, $internshipId)
    {
        $validated = $request->validate([
            'cover_letter' => 'nullable|string|max:2000',
        ]);

        $student = Auth::user()->student;

        $internship = Internship::where('INTERNSHIP_ID', $internshipId)->firstOrFail();

        // Re-check guards server-side (defense in depth)
        if ($internship->STATUS !== 'Open' || $internship->APPLICATION_DEADLINE < now()->format('Y-m-d')) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'This internship is no longer accepting applications.');
        }

        $alreadyApplied = $student->applications()
            ->where('INTERNSHIP_ID', $internshipId)
            ->exists();

        if ($alreadyApplied) {
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'You have already applied to this internship.');
        }

        try {
            DB::transaction(function () use ($student, $internshipId, $validated, $internship) {
                Application::create([
                    'INTERNSHIP_ID' => $internshipId,
                    'STUDENT_ID'    => $student->STUDENT_ID,
                    'COVER_LETTER'  => $validated['cover_letter'] ?? null,
                    'STATUS'        => 'Pending',
                ]);

                // Notify the company's user account
                $companyUserId = $internship->company->USER_ID;

                Notification::create([
                    'USER_ID' => $companyUserId,
                    'MESSAGE' => "New application received for \"{$internship->TITLE}\" from {$student->FIRST_NAME} {$student->LAST_NAME}.",
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Safety net for the UNIQUE(INTERNSHIP_ID, STUDENT_ID) constraint
            return redirect()->route('internships.show', $internshipId)
                ->with('error', 'You have already applied to this internship.');
        }

        return redirect()->route('student.applications')
            ->with('success', 'Application submitted successfully!');
    }

    // ─── Withdraw Application (only if still Pending) ──────────────────
    public function destroy($applicationId)
    {
        $student = Auth::user()->student;

        $application = $student->applications()
            ->where('APPLICATION_ID', $applicationId)
            ->firstOrFail();

        if ($application->STATUS !== 'Pending') {
            return redirect()->route('student.applications')
                ->with('error', 'You can only withdraw applications that are still Pending.');
        }

        $application->delete();

        return redirect()->route('student.applications')
            ->with('success', 'Application withdrawn successfully.');
    }
}