<?php

namespace App\Http\Controllers;  // 宣告此檔案所屬命名空間

use App\Models\Admin\CaseInfo;  // 引入 CaseInfo Eloquent 模型
use App\Repositories\Admin\SeoSettingRepository;  // 引入 SEO 設定儲存庫
use Illuminate\Http\Request;  // 引入 Laravel 的 HTTP 請求類別

class CasesController extends Controller  // 宣告 CasesController 並繼承基底 Controller
{
    // 這裡可放共用邏輯 (目前留空)

    public function index()  // 定義顯示案例列表初始頁面的方法
    {
        $seoInfo = SeoSettingRepository::getInfo('/cases');  // 依路徑取得 SEO 設定資料
        $cases = CaseInfo::with('businessCard')->where('status', true)->orderBy('created_at', 'desc')->limit(9)->get();  // 載入關聯名片，篩選啟用狀態，按建立時間新到舊取前 9 筆
        $totalCases = CaseInfo::where('status', true)->count();  // 計算啟用狀態案例總數
        $hasMore = $totalCases > 9;  // 判斷是否還有更多案例可載入

        return view('cases', compact('seoInfo', 'cases', 'totalCases', 'hasMore'));  // 回傳 Blade 視圖並帶入變數
    }

    public function loadMore(Request $request)  // 定義載入更多案例 (AJAX 分頁) 的方法
    {
        $page = $request->get('page', 1);  // 從請求取得頁碼，預設第 1 頁
        $offset = ($page - 1) * 9;  // 計算查詢位移量 (每頁 9 筆)

        $cases = CaseInfo::with('businessCard')  // 查詢案例並預先載入 businessCard 關聯
            ->where('status', true)  // 篩選啟用案例
            ->orderBy('created_at', 'desc')  // 依建立時間倒序
            ->skip($offset)  // 略過前面頁次資料
            ->limit(9)  // 限制取 9 筆
            ->get();  // 執行查詢並取回集合

        $totalCases = CaseInfo::where('status', true)->count();  // 再次取總數 (可考慮快取)
        $hasMore = ($offset + 9) < $totalCases;  // 判斷目前頁後是否仍有資料

        if ($request->ajax()) {  // 如果是 AJAX 請求
            $casesWithUrls = $cases->map(function($case) {  // 轉換資料格式供前端使用
                return [
                    'id' => $case->id,  // 案例主鍵
                    'name' => $case->name,  // 案例名稱
                    'business_card' => [  // 嵌套名片相關資訊
                        'profile_image' => $case->businessCard->profile_image,  // 名片頭像
                        'views' => $case->businessCard->views,  // 名片瀏覽數
                        'share_url' => $case->businessCard->getShareUrl()  // 產生分享 URL (模型方法)
                    ]
                ];
            });

            return response()->json([  // 回傳 JSON 結構
                'cases' => $casesWithUrls,  // 案例資料陣列
                'hasMore' => $hasMore  // 是否還有下一頁
            ]);
        }

        return redirect()->route('cases');  // 若非 AJAX 直接導回主列表路由
    }
}
