<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CompanyDashboardController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;

        $totalInternships = $company->internships()->count();
        $openInternships  = $company->internships()->where('STATUS', 'Open')->count();

        // Total applications across all of this company's internships
        $totalApplications = $company->internships()
            ->get()
            ->sum(fn ($internship) => $internship->applications()->count());

        $profileComplete = $company->COMPANY_NAME !== 'New Company'
            && $company->INDUSTRY !== 'Not Set'
            && $company->LOCATION !== 'Not Set';

        return view('company.dashboard', compact(
            'company', 'totalInternships', 'openInternships',
            'totalApplications', 'profileComplete'
        ));
    }
}