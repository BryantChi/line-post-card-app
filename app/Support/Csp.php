<?php

namespace App\Support;

class Csp
{
    /**
     * 取得當前請求的 CSP nonce。
     */
    public static function nonce(): string
    {
        return request()->attributes->get('csp_nonce', '');
    }

    /**
     * 將 nonce 套用到提供的 HTML 片段（僅限 inline script/style）。
     */
    public static function applyNonce(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $nonce = e(static::nonce());

        $injectNonce = static function (array $matches) use ($nonce): string {
            $tag = $matches[0];

            if (stripos($tag, 'nonce=') !== false) {
                return $tag;
            }

            return preg_replace('/>$/', ' nonce="' . $nonce . '">', $tag, 1);
        };

        $html = preg_replace_callback('/<script\\b(?![^>]*\\bsrc\\b)[^>]*>/i', $injectNonce, $html);
        $html = preg_replace_callback('/<style\\b[^>]*>/i', $injectNonce, $html);

        return $html ?? '';
    }
}
