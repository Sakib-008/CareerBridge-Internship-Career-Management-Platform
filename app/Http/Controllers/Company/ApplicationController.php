<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // ─── List all applications across this company's internships ───────
    public function index(Request $request)
    {
        $company = Auth::user()->company;

        $internshipIds = $company->internships()->get()->pluck('INTERNSHIP_ID')->toArray();

        $query = Application::with(['student', 'internship'])
            ->whereIn('INTERNSHIP_ID', $internshipIds);

        if ($request->filled('internship_id')) {
            $query->where('INTERNSHIP_ID', $request->internship_id);
        }

        if ($request->filled('status')) {
            $query->where('STATUS', $request->status);
        }

        $applications = $query->orderBy('APPLIED_AT', 'desc')->get();

        $internships = $company->internships()->orderBy('TITLE')->get();

        return view('company.applications.index', compact('applications', 'internships'));
    }

    // ─── Show single application detail ──────────────────────────────────
    public function show($id)
    {
        $company = Auth::user()->company;
        $internshipIds = $company->internships()->get()->pluck('INTERNSHIP_ID')->toArray();

        $application = Application::with(['student.studentSkills.skill', 'internship'])
            ->whereIn('INTERNSHIP_ID', $internshipIds)
            ->where('APPLICATION_ID', $id)
            ->firstOrFail();

        return view('company.applications.show', compact('application'));
    }

    // ─── Update application status ───────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Reviewed,Shortlisted,Interview,Accepted,Rejected',
        ]);

        $company = Auth::user()->company;
        $internshipIds = $company->internships()->get()->pluck('INTERNSHIP_ID')->toArray();

        $application = Application::with('student', 'internship')
            ->whereIn('INTERNSHIP_ID', $internshipIds)
            ->where('APPLICATION_ID', $id)
            ->firstOrFail();

        $application->update(['STATUS' => $request->status]);

        // Notify the student of the status change
        Notification::create([
            'USER_ID' => $application->student->USER_ID,
            'MESSAGE' => "Your application for \"{$application->internship->TITLE}\" is now: {$request->status}.",
        ]);

        return redirect()->back()
            ->with('success', "Application status updated to {$request->status}.");
    }
}