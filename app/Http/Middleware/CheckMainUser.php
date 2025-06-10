<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMainUser
{
    /**
     * 檢查是否為主帳號
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 檢查當前用戶是否存在且角色為主帳號或超級管理員
        // dd(auth()->user()->role);
        if (!auth()->user() || (auth()->user()->role !== 'main_user' && auth()->user()->role !== 'super_admin')) {
            // dd('您沒有主帳號權限，請聯繫管理員。');
            // 如果不是主帳號或超級管理員，則重定向到管理頁面並顯示錯誤訊息
            return redirect('/admin')->with('error', '您沒有主帳號權限');
        }
        // 如果是主帳號或超級管理員，則繼續處理請求
        if (auth()->user()->role === 'main_user') {
            // 檢查主帳號是否活躍
            if (!auth()->user()->active) {
                return redirect('/admin')->with('error', '您的主帳號已停用，請聯繫管理員');
            }
            // dd('主帳號權限檢查通過，繼續處理請求。');
        }
        // 如果是超級管理員，則不需要進一步檢查
        if (auth()->user()->role === 'super_admin') {
            return $next($request);
        }

        return $next($request);
    }
}
