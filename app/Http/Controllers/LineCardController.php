<?php

namespace App\Http\Controllers;

use App\Models\BusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session; // 確認已引用

class LineCardController extends Controller
{
    /**
     * 顯示數位名片的 LIFF 頁面
     */
    public function liff($uuid = null)
    {
        // 如果沒有提供 UUID 參數
        if (!$uuid) {
            // 從 URL 參數中獲取 UUID
            $uuid = request()->query('uuid');

            // 如果仍然沒有 UUID，顯示錯誤訊息
            if (!$uuid) {
                Log::warning('LIFF 頁面打開時缺少 UUID 參數', [
                    'user_agent' => request()->userAgent(),
                    'referrer' => request()->header('referer')
                ]);

                return view('liff.error', [
                    'message' => '找不到指定的數位名片，請確認您的連結是否包含正確的識別碼。',
                    'debug_info' => [
                        'url' => request()->fullUrl(),
                        'user_agent' => request()->userAgent(),
                        'referrer' => request()->header('referer')
                    ]
                ]);
            }
        }

        try {
            $businessCard = BusinessCard::where('uuid', $uuid)->firstOrFail();

            if (!$businessCard->active) {
                return view('card_preview.inactive');
            }

            // 獲取所有啟用的氣泡卡片
            $bubbles = $businessCard->activeBubbles()->orderBy('order')->get();

            // 檢查是否有 flex_json
            if (empty($businessCard->flex_json)) {
                Log::warning("LIFF 頁面中缺少 flex_json", [
                    'uuid' => $uuid,
                    'card_id' => $businessCard->id
                ]);
            }

            // 記錄日誌，幫助調試問題
            Log::info('成功載入 LIFF 頁面', [
                'uuid' => $uuid,
                'card_id' => $businessCard->id,
                'user_agent' => request()->userAgent()
            ]);

            return view('liff.card', compact('businessCard', 'bubbles'));
        } catch (\Exception $e) {
            Log::error('LIFF 頁面錯誤', [
                'uuid' => $uuid,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_agent' => request()->userAgent()
            ]);

            return view('liff.error', [
                'message' => '無法顯示指定的數位名片，請確認連結是否正確或聯繫卡片發送者。',
                'debug_info' => [
                    'uuid' => $uuid,
                    'error' => $e->getMessage(),
                    'user_agent' => request()->userAgent()
                ]
            ]);
        }
    }

    /**
     * 發送數位名片到 LINE 聊天
     */
    public function send(Request $request, $uuid)
    {
        try {
            $businessCard = BusinessCard::where('uuid', $uuid)->firstOrFail();

            if (!$businessCard->active) {
                return response()->json([
                    'success' => false,
                    'message' => '此數位名片已停用'
                ]);
            }

            // 檢查是否有 flex_json
            if (empty($businessCard->flex_json)) {
                return response()->json([
                    'success' => false,
                    'message' => '此數位名片尚未設定 Flex 訊息格式'
                ]);
            }

            // 這個方法只能在 LIFF 環境中使用，所以實際發送邏輯在前端處理
            // 返回更完整的資料
            return response()->json([
                'success' => true,
                'card' => [
                    'title' => $businessCard->title,
                    'subtitle' => $businessCard->subtitle,
                    'uuid' => $businessCard->uuid,
                    'shareUrl' => url('/share/' . $uuid),
                    'flex_json' => $businessCard->flex_json
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('發送卡片錯誤: ' . $e->getMessage(), [
                'uuid' => $uuid,
                'exception' => get_class($e)
            ]);

            return response()->json([
                'success' => false,
                'message' => '發生錯誤，無法發送數位名片'
            ]);
        }
    }

    /**
     * 使用網頁方式分享數位名片
     */
    public function share($uuid)
    {
        try {
            $businessCard = BusinessCard::where('uuid', $uuid)->firstOrFail();

            if (!$businessCard->active) {
                return view('card_preview.inactive');
            }

            // 獲取所有啟用的氣泡卡片
            $bubbles = $businessCard->activeBubbles()->orderBy('order')->get();

            // 檢查是否有 flex_json
            if (empty($businessCard->flex_json)) {
                Log::warning("分享卡片缺少 flex_json: {$uuid}");
            }

            // 確保 URL 使用完整的絕對路徑
            $shareUrl = url('/share/' . $uuid);

            // 生成分享用的 LINE URL - 添加更多資訊以提高分享內容吸引力
            $lineShareText = $businessCard->title;
            if (!empty($businessCard->subtitle)) {
                $lineShareText .= ' - ' . $businessCard->subtitle;
            }
            $lineShareText .= ' - 數位名片';

            $lineShareUrl = 'https://social-plugins.line.me/lineit/share?url=' .
                            urlencode($shareUrl) .
                            '&text=' . urlencode($lineShareText);

            // 記錄成功分享的日誌
            Log::info('成功生成分享卡片', [
                'uuid' => $uuid,
                'card_id' => $businessCard->id,
                'share_url' => $lineShareUrl
            ]);

            // Increment view count with throttling and ip address
            $ipAddress = request()->ip();
            $sessionKey = "business_card_views_{$uuid}_{$ipAddress}";
            $throttleMinutes = 30; // 設定查看次數的節流時間，30分鐘
            // 獲取最後查看時間
            $lastViewed = Session::get($sessionKey);

            if (!$lastViewed || now()->diffInMinutes($lastViewed) > $throttleMinutes) {
                $businessCard->increment('views');
                // 更新 session 中的最後查看時間
                Session::put($sessionKey, now());
            }

            return view('card_preview.share', compact('businessCard', 'bubbles', 'lineShareUrl'));
        } catch (\Exception $e) {
            // 記錄詳細錯誤並顯示友好的錯誤頁面
            Log::error('分享卡片錯誤: ' . $e->getMessage(), [
                'uuid' => $uuid,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return view('errors.custom', [
                'title' => '無法分享數位名片',
                'message' => '找不到指定的數位名片或發生錯誤，請確認連結是否正確。'
            ]);
        }
    }
}
