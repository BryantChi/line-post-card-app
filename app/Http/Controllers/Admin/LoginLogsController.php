<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;

class LoginLogsController extends Controller
{
    /**
     * 顯示登入紀錄列表
     */
    public function index(Request $request)
    {
        $query = UserLoginLog::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'sub_user');
            });

        // 根據權限篩選
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            // 超級管理員可以看到所有子帳號的紀錄
        } elseif ($user->isMainUser()) {
            // 主帳號只能看到自己的子帳號的紀錄
            $subUserIds = $user->subUsers()->pluck('id')->toArray();
            $query->whereIn('user_id', $subUserIds);
        } else {
            // 子帳號只能看到自己的紀錄
            $query->where('user_id', $user->id);
        }

        // 日期範圍篩選
        if ($request->filled('start_date')) {
            $query->where('logged_in_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->filled('end_date')) {
            $query->where('logged_in_at', '<=', $request->end_date . ' 23:59:59');
        }

        // 用戶篩選
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // IP 篩選
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $logs = $query->orderBy('logged_in_at', 'desc')
            ->paginate(50)
            ->appends($request->all());

        // 獲取可篩選的用戶列表 (只顯示子帳號)
        $usersQuery = \App\Models\User::where('role', 'sub_user');
        if ($user->isSuperAdmin()) {
            // 超級管理員可以看到所有子帳號
        } elseif ($user->isMainUser()) {
            // 主帳號只能看到自己的子帳號
            $subUserIds = $user->subUsers()->pluck('id')->toArray();
            $usersQuery->whereIn('id', $subUserIds);
        } else {
            // 子帳號只能看到自己
            $usersQuery->where('id', $user->id);
        }
        $users = $usersQuery->orderBy('name')->get();

        return view('admin.login_logs.index', compact('logs', 'users'));
    }

    /**
     * 匯出登入紀錄為 Excel
     */
    public function export(Request $request)
    {
        $query = UserLoginLog::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'sub_user');
            });

        // 根據權限篩選
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            // 超級管理員可以看到所有子帳號的紀錄
        } elseif ($user->isMainUser()) {
            // 主帳號只能看到自己的子帳號的紀錄
            $subUserIds = $user->subUsers()->pluck('id')->toArray();
            $query->whereIn('user_id', $subUserIds);
        } else {
            // 子帳號只能看到自己的紀錄
            $query->where('user_id', $user->id);
        }

        // 應用相同的篩選條件
        if ($request->filled('start_date')) {
            $query->where('logged_in_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->filled('end_date')) {
            $query->where('logged_in_at', '<=', $request->end_date . ' 23:59:59');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $logs = $query->orderBy('logged_in_at', 'desc')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LoginLogsExport($logs),
            '登入紀錄_' . date('YmdHis') . '.xlsx'
        );
    }
}
