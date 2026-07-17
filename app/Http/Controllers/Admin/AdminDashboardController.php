<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // System-wide counts using aggregate functions
        $stats = DB::select("
            SELECT
                (SELECT COUNT(*) FROM USERS
                 WHERE ROLE = 'student') AS TOTAL_STUDENTS,
                (SELECT COUNT(*) FROM USERS
                 WHERE ROLE = 'company') AS TOTAL_COMPANIES,
                (SELECT COUNT(*) FROM INTERNSHIPS)
                    AS TOTAL_INTERNSHIPS,
                (SELECT COUNT(*) FROM INTERNSHIPS
                 WHERE STATUS = 'Open') AS OPEN_INTERNSHIPS,
                (SELECT COUNT(*) FROM APPLICATIONS)
                    AS TOTAL_APPLICATIONS,
                (SELECT COUNT(*) FROM APPLICATIONS
                 WHERE STATUS = 'Accepted') AS TOTAL_PLACEMENTS,
                (SELECT COUNT(*) FROM INTERVIEWS)
                    AS TOTAL_INTERVIEWS
            FROM DUAL
        ")[0];

        // Recent applications (last 5) — JOIN across 3 tables
        $recentApplications = DB::select("
            SELECT * FROM (
                SELECT
                    a.APPLICATION_ID,
                    a.STATUS,
                    a.APPLIED_AT,
                    s.FIRST_NAME || ' ' || s.LAST_NAME AS STUDENT_NAME,
                    i.TITLE AS INTERNSHIP_TITLE,
                    c.COMPANY_NAME
                FROM APPLICATIONS a
                INNER JOIN STUDENTS    s ON a.STUDENT_ID    = s.STUDENT_ID
                INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
                INNER JOIN COMPANIES   c ON i.COMPANY_ID    = c.COMPANY_ID
                ORDER BY a.APPLIED_AT DESC
            ) WHERE ROWNUM <= 5
        ");

        $recentApplications = array_map(fn($r) => (object)[
            'APPLICATION_ID'    => $r->application_id,
            'STATUS'            => $r->status,
            'APPLIED_AT'        => $r->applied_at,
            'STUDENT_NAME'      => $r->student_name,
            'INTERNSHIP_TITLE'  => $r->internship_title,
            'COMPANY_NAME'      => $r->company_name,
        ], $recentApplications);

        // Top 5 most applied internships — aggregate subquery
        $topInternships = DB::select("
            SELECT * FROM (
                SELECT
                    i.TITLE,
                    c.COMPANY_NAME,
                    COUNT(a.APPLICATION_ID) AS APPLICATION_COUNT
                FROM INTERNSHIPS i
                INNER JOIN COMPANIES c ON i.COMPANY_ID = c.COMPANY_ID
                LEFT JOIN APPLICATIONS a ON i.INTERNSHIP_ID = a.INTERNSHIP_ID
                GROUP BY i.INTERNSHIP_ID, i.TITLE, c.COMPANY_NAME
                ORDER BY APPLICATION_COUNT DESC
            ) WHERE ROWNUM <= 5
        ");

        $topInternships = array_map(fn($r) => (object)[
            'TITLE'             => $r->title,
            'COMPANY_NAME'      => $r->company_name,
            'APPLICATION_COUNT' => $r->application_count,
        ], $topInternships);

        return view('admin.dashboard', compact(
            'stats', 'recentApplications', 'topInternships'
        ));
    }

     public function regenerateRecommendations()
    {
        try {
            $pdo = DB::getPdo();

            $sql = "BEGIN SP_GENERATE_RECOMMENDATIONS(NULL, :result); END;";
            $stmt = $pdo->prepare($sql);

            $result = '';
            $stmt->bindParam(':result', $result, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 500);
            $stmt->execute();

            if (str_starts_with($result, 'ERROR')) {
                return redirect()->route('admin.dashboard')
                    ->with('error', $result);
            }

            return redirect()->route('admin.dashboard')
                ->with('success', 'Recommendations regenerated: ' . $result);

        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}