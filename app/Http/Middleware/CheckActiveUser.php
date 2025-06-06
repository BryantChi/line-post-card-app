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

        // 子帳號需檢查是否活躍及是否過期
        if (!$user->active || ($user->expires_at && $user->expires_at < now())) {
            auth()->logout();
            return redirect('/login')->with('error', '您的帳號已停用或已過期，請聯繫管理員');
        }

        return $next($request);
    }
}
