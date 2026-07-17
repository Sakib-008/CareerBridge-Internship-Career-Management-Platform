<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $where  = "WHERE u.ROLE != 'admin'";
        $params = [];

        if ($request->filled('role')) {
            $where .= " AND u.ROLE = :role";
            $params['role'] = $request->role;
        }

        if ($request->filled('search')) {
            $where .= " AND UPPER(u.EMAIL) LIKE UPPER(:search)";
            $params['search'] = '%' . $request->search . '%';
        }

        $rows = DB::select("
            SELECT
                u.USER_ID,
                u.EMAIL,
                u.ROLE,
                u.IS_ACTIVE,
                u.CREATED_AT,
                CASE
                    WHEN u.ROLE = 'student'
                    THEN s.FIRST_NAME || ' ' || s.LAST_NAME
                    WHEN u.ROLE = 'company'
                    THEN c.COMPANY_NAME
                    ELSE 'N/A'
                END AS DISPLAY_NAME,
                CASE
                    WHEN u.ROLE = 'student' THEN s.DEPARTMENT
                    WHEN u.ROLE = 'company' THEN c.INDUSTRY
                    ELSE NULL
                END AS EXTRA_INFO
            FROM USERS u
            LEFT JOIN STUDENTS  s ON u.USER_ID = s.USER_ID
            LEFT JOIN COMPANIES c ON u.USER_ID = c.USER_ID
            $where
            ORDER BY u.CREATED_AT DESC
        ", $params);

        $users = collect(array_map(fn($r) => (object)[
            'USER_ID'      => $r->user_id,
            'EMAIL'        => $r->email,
            'ROLE'         => $r->role,
            'IS_ACTIVE'    => $r->is_active,
            'CREATED_AT'   => $r->created_at,
            'DISPLAY_NAME' => $r->display_name,
            'EXTRA_INFO'   => $r->extra_info,
        ], $rows));

        return view('admin.users.index', compact('users'));
    }

    public function toggleActive($userId)
    {
        $row = DB::select(
            "SELECT IS_ACTIVE FROM USERS WHERE USER_ID = :user_id AND ROWNUM = 1",
            ['user_id' => $userId]
        );

        if (empty($row)) abort(404);

        $newStatus = (string)$row[0]->is_active === '1' ? 0 : 1;

        DB::update(
            "UPDATE USERS SET IS_ACTIVE = :is_active WHERE USER_ID = :user_id",
            ['is_active' => $newStatus, 'user_id' => $userId]
        );

        $label = $newStatus === 1 ? 'activated' : 'deactivated';

        return redirect()->route('admin.users')
            ->with('success', "User account {$label} successfully.");
    }
}