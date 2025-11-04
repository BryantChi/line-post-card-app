<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
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
        // 先生成 CSP nonce 供 View 與 Vite 使用,避免渲染階段無法取得
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        if (class_exists(Vite::class)) {
            // 告知 Vite 產生的 <script> 標籤注入同一組 nonce
            Vite::useCspNonce($nonce);
        }

        $response = $next($request);

        // Content Security Policy (CSP)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net https://stackpath.bootstrapcdn.com https://static.line-scdn.net https://kit.fontawesome.com https://kit-free.fontawesome.com https://code.jquery.com",
            "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.datatables.net https://stackpath.bootstrapcdn.com https://kit.fontawesome.com https://kit-free.fontawesome.com",
            "img-src 'self' data: blob: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com https://fonts.gstatic.com https://*.line.me https://*.line-scdn.net https://ka-f.fontawesome.com https://kit-free.fontawesome.com https://api.qrserver.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://ka-f.fontawesome.com https://kit-free.fontawesome.com",
            "connect-src 'self' https://api.line.me https://access.line.me https://liff.line.me https://*.line-scdn.net https://api.openai.com https://cdn.jsdelivr.net https://ka-f.fontawesome.com https://kit-free.fontawesome.com",
            "frame-src 'self' https://liff.line.me https://www.youtube.com https://www.youtube-nocookie.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
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
