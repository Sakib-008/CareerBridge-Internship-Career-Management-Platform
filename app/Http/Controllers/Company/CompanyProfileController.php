<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyProfileController extends Controller
{
    public function show()
    {
        $company = Auth::user()->company;
        return view('company.profile', compact('company'));
    }

    public function update(Request $request)
    {
        $company = Auth::user()->company;

        $validated = $request->validate([
            'company_name'   => 'required|string|max:100',
            'industry'       => 'required|string|max:50',
            'company_size'   => 'nullable|in:1-10,11-50,51-200,201-500,500+',
            'location'       => 'required|string|max:100',
            'website'        => 'nullable|url|max:100',
            'description'    => 'nullable|string|max:2000',
            'contact_person' => 'nullable|string|max:100',
            'contact_email'  => 'nullable|email|max:100',
        ]);

        $company->update([
            'COMPANY_NAME'   => $validated['company_name'],
            'INDUSTRY'       => $validated['industry'],
            'COMPANY_SIZE'   => $validated['company_size'] ?? null,
            'LOCATION'       => $validated['location'],
            'WEBSITE'        => $validated['website'] ?? null,
            'DESCRIPTION'    => $validated['description'] ?? null,
            'CONTACT_PERSON' => $validated['contact_person'] ?? null,
            'CONTACT_EMAIL'  => $validated['contact_email'] ?? null,
        ]);

        return redirect()->route('company.profile')
            ->with('success', 'Company profile updated successfully.');
    }
}