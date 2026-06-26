<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternshipController extends Controller
{
    // ─── List all internships for this company ─────────────────────────
    public function index()
    {
        $company = Auth::user()->company;

        $internships = $company->internships()
            ->withCount('applications')
            ->orderBy('CREATED_AT', 'desc')
            ->get();

        return view('company.internships.index', compact('internships'));
    }

    // ─── Show create form ────────────────────────────────────────────────
    public function create()
    {
        $skills = Skill::orderBy('CATEGORY')->orderBy('SKILL_NAME')->get();
        return view('company.internships.create', compact('skills'));
    }

    // ─── Store new internship ────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $this->validateInternship($request);

        $company = Auth::user()->company;

        DB::transaction(function () use ($validated, $company, $request) {
            $internship = Internship::create([
                'COMPANY_ID'            => $company->COMPANY_ID,
                'TITLE'                 => $validated['title'],
                'DESCRIPTION'           => $validated['description'],
                'LOCATION'              => $validated['location'],
                'INTERNSHIP_TYPE'       => $validated['internship_type'],
                'DURATION_MONTHS'       => $validated['duration_months'],
                'STIPEND'               => $validated['stipend'] ?? 0,
                'VACANCIES'             => $validated['vacancies'],
                'APPLICATION_DEADLINE'  => $validated['application_deadline'],
                'STATUS'                => 'Open',
            ]);

            $this->syncSkills($internship, $request);
        });

        return redirect()->route('company.internships')
            ->with('success', 'Internship posted successfully.');
    }

    // ─── Show edit form ───────────────────────────────────────────────────
    public function edit($id)
    {
        $company = Auth::user()->company;

        $internship = $company->internships()
            ->where('INTERNSHIP_ID', $id)
            ->firstOrFail();

        $skills = Skill::orderBy('CATEGORY')->orderBy('SKILL_NAME')->get();

        $selectedSkillIds = $internship->skills()->get()->pluck('SKILL_ID')->toArray();
        $mandatorySkillIds = $internship->skills()
            ->get()
            ->filter(fn ($s) => (string) $s->pivot->IS_MANDATORY === '1')
            ->pluck('SKILL_ID')
            ->toArray();

        return view('company.internships.edit', compact(
            'internship', 'skills', 'selectedSkillIds', 'mandatorySkillIds'
        ));
    }

    // ─── Update internship ────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $company = Auth::user()->company;

        $internship = $company->internships()
            ->where('INTERNSHIP_ID', $id)
            ->firstOrFail();

        $validated = $this->validateInternship($request);

        DB::transaction(function () use ($internship, $validated, $request) {
            $internship->update([
                'TITLE'                 => $validated['title'],
                'DESCRIPTION'           => $validated['description'],
                'LOCATION'              => $validated['location'],
                'INTERNSHIP_TYPE'       => $validated['internship_type'],
                'DURATION_MONTHS'       => $validated['duration_months'],
                'STIPEND'               => $validated['stipend'] ?? 0,
                'VACANCIES'             => $validated['vacancies'],
                'APPLICATION_DEADLINE'  => $validated['application_deadline'],
            ]);

            $this->syncSkills($internship, $request);
        });

        return redirect()->route('company.internships')
            ->with('success', 'Internship updated successfully.');
    }

    // ─── Update status only (Open / Closed / Paused) ──────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Open,Closed,Paused',
        ]);

        $company = Auth::user()->company;

        $internship = $company->internships()
            ->where('INTERNSHIP_ID', $id)
            ->firstOrFail();

        $internship->update(['STATUS' => $request->status]);

        return redirect()->route('company.internships')
            ->with('success', "Internship marked as {$request->status}.");
    }

    // ─── Delete internship ──────────────────────────────────────────────
    public function destroy($id)
    {
        $company = Auth::user()->company;

        $internship = $company->internships()
            ->where('INTERNSHIP_ID', $id)
            ->firstOrFail();

        $internship->delete();

        return redirect()->route('company.internships')
            ->with('success', 'Internship deleted successfully.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────
    private function validateInternship(Request $request): array
    {
        return $request->validate([
            'title'                 => 'required|string|max:150',
            'description'            => 'required|string|max:3000',
            'location'               => 'required|string|max:100',
            'internship_type'        => 'required|in:Remote,On-site,Hybrid',
            'duration_months'        => 'required|integer|min:1|max:24',
            'stipend'                => 'nullable|numeric|min:0',
            'vacancies'              => 'required|integer|min:1',
            'application_deadline'   => 'required|date|after:today',
            'skill_ids'              => 'required|array|min:1',
            'skill_ids.*'            => 'integer|exists:SKILLS,SKILL_ID',
            'mandatory_skill_ids'    => 'nullable|array',
            'mandatory_skill_ids.*'  => 'integer',
        ]);
    }

    private function syncSkills(Internship $internship, Request $request): void
    {
        $skillIds = $request->input('skill_ids', []);
        $mandatoryIds = $request->input('mandatory_skill_ids', []);

        $syncData = [];
        foreach ($skillIds as $skillId) {
            $syncData[$skillId] = [
                'IS_MANDATORY' => in_array($skillId, $mandatoryIds) ? 1 : 0,
            ];
        }

        $internship->skills()->sync($syncData);
    }
}