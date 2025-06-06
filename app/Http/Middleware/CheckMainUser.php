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
        if (!auth()->user() || (auth()->user()->role !== 'main_user' && auth()->user()->role !== 'super_admin')) {
            // 如果不是主帳號或超級管理員，則重定向到管理頁面並顯示錯誤訊息
            return redirect('/admin')->with('error', '此功能僅限主帳號使用');
        }

        return $next($request);
    }
}
