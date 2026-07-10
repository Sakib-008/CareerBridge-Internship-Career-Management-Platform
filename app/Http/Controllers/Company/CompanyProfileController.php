<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
    public function show()
    {
        $row = DB::select(
            "SELECT * FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        )[0];

        $company = $this->mapCompany($row);
        return view('company.profile', compact('company'));
    }

    public function update(Request $request)
    {
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

        $companyId = $this->getCompanyId();

        // update() method
        DB::update(
            "UPDATE COMPANIES SET
                COMPANY_NAME   = :company_name,
                INDUSTRY       = :industry,
                COMPANY_SIZE   = :company_size,
                LOCATION       = :location,
                WEBSITE        = :website,
                DESCRIPTION    = :description,
                CONTACT_PERSON = :contact_person,
                CONTACT_EMAIL  = :contact_email
            WHERE COMPANY_ID  = :company_id",
            [
                'company_name'   => $validated['company_name'],
                'industry'       => $validated['industry'],
                'company_size'   => $validated['company_size'] ?? null,
                'location'       => $validated['location'],
                'website'        => $validated['website'] ?? null,
                'description'    => $validated['description'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'contact_email'  => $validated['contact_email'] ?? null,
                'company_id'     => $companyId,
            ]
        );
        return redirect()->route('company.profile')
            ->with('success', 'Company profile updated successfully.');
    }

    private function getCompanyId(): int
    {
        $row = DB::select(
            "SELECT COMPANY_ID FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => Auth::id()]
        );
        return (int) $row[0]->company_id;
    }

    private function mapCompany(object $r): object
    {
        return (object) [
            'COMPANY_ID'     => $r->company_id,
            'COMPANY_NAME'   => $r->company_name,
            'INDUSTRY'       => $r->industry,
            'COMPANY_SIZE'   => $r->company_size,
            'LOCATION'       => $r->location,
            'WEBSITE'        => $r->website,
            'DESCRIPTION'    => $r->description,
            'CONTACT_PERSON' => $r->contact_person,
            'CONTACT_EMAIL'  => $r->contact_email,
        ];
    }
}