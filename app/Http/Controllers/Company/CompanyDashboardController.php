<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $companyRow = DB::select(
            "SELECT * FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => $userId]
        )[0];

        $companyId = $companyRow->company_id;

        $stats = DB::select(
            "SELECT
                COUNT(i.INTERNSHIP_ID) AS TOTAL_INTERNSHIPS,
                SUM(CASE WHEN i.STATUS = 'Open' THEN 1 ELSE 0 END) AS OPEN_INTERNSHIPS,
                COUNT(a.APPLICATION_ID) AS TOTAL_APPLICATIONS
             FROM INTERNSHIPS i
             LEFT JOIN APPLICATIONS a ON i.INTERNSHIP_ID = a.INTERNSHIP_ID
             WHERE i.COMPANY_ID = :company_id",
            ['company_id' => $companyId]
        )[0];

        $company = (object) [
            'COMPANY_ID'   => $companyRow->company_id,
            'COMPANY_NAME' => $companyRow->company_name,
            'INDUSTRY'     => $companyRow->industry,
            'LOCATION'     => $companyRow->location,
        ];

        $totalInternships  = $stats->total_internships;
        $openInternships   = $stats->open_internships ?? 0;
        $totalApplications = $stats->total_applications;

        $profileComplete = $companyRow->company_name !== 'New Company'
            && $companyRow->industry !== 'Not Set'
            && $companyRow->location !== 'Not Set';

        return view('company.dashboard', compact(
            'company', 'totalInternships', 'openInternships',
            'totalApplications', 'profileComplete'
        ));
    }
}