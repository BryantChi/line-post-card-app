<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    /**
     * 檢查用戶是否為活躍狀態
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        // 超級管理員和主帳號不受期限限制
        if ($user->role == 'super_admin' || $user->role == 'main_user') {
            return $next($request);
        }

        // 子帳號：檢查是否停用（停用直接登出）
        if (!$user->active) {
            auth()->logout();
            return redirect('/login')->with('error', '您的帳號已停用，請聯繫管理員');
        }

        // 子帳號：帳號已過期 → 只允許存取續約頁面，其他頁面重導到續約頁面
        if ($user->expires_at && $user->expires_at->isPast()) {
            // 精確比對：只放行 renewal 相關路由
            if ($request->is('admin/renewal*')) {
                return $next($request);
            }
            return redirect()->route('renewal.index')
                ->with('warning', '您的帳號已過期，請先完成續約後才能使用其他功能');
        }

        return $next($request);
    }
}
