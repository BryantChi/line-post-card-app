<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy (CSP)
        // 先使用 Report-Only 模式,測試無誤後改為 Content-Security-Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "connect-src 'self' https://api.line.me https://api.openai.com",
            "frame-src 'self' https://liff.line.me",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        // 階段性部署:先用 Report-Only 測試
        // $response->headers->set('Content-Security-Policy-Report-Only', $csp);
        // 測試無誤後,將上行註解並啟用下行:
        $response->headers->set('Content-Security-Policy', $csp);

        // 防止點擊劫持 (Clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // 防止 MIME 類型嗅探
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy - 跨站時僅送出 origin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy - 禁用不必要的瀏覽器功能
        $permissionsPolicy = implode(', ', [
            'geolocation=()',
            'camera=()',
            'microphone=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]);
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // X-XSS-Protection (雖然現代瀏覽器已棄用,但為了舊瀏覽器保留)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
