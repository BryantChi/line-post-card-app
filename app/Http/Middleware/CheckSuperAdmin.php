<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * 檢查是否為超級管理員
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 如果當前用戶不是超級管理員，則重定向到管理頁面並顯示錯誤訊息
        if (!auth()->user() || auth()->user()->role !== 'super_admin') {
            return redirect('/admin')->with('error', '您沒有超級管理員權限');
        }

        return $next($request);
    }
}
