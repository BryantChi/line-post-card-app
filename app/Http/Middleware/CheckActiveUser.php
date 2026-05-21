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

        // 子帳號：帳號已過期
        if ($user->expires_at && $user->expires_at->isPast()) {
            // 該用戶無法使用續約功能時（停用或非測試帳號），僅允許看停用訊息
            if (!\App\Models\SystemSetting::canUserAccessRenewal($user->id)) {
                if ($request->route()?->getName() === 'renewal.index') {
                    return $next($request);
                }
                return redirect()->route('renewal.index')
                    ->with('warning', '您的帳號已過期，續約功能目前暫停開放，請聯繫管理員');
            }

            // 續約功能可用時，允許存取續約相關路由
            $allowedRoutes = [
                'renewal.index',
                'renewal.create-order',
                'renewal.payment-redirect',
                'renewal.bank-transfer',
                'renewal.upload-receipt',
                'renewal.history',
                'renewal.order-detail',
                'renewal.cancel-order',
            ];
            if (in_array($request->route()?->getName(), $allowedRoutes)) {
                return $next($request);
            }
            return redirect()->route('renewal.index')
                ->with('warning', '您的帳號已過期，請先完成續約後才能使用其他功能');
        }

        return $next($request);
    }
}
