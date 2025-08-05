<?php

namespace App\Http\Controllers;

use App\Models\Admin\LesssonInfo;
use App\Repositories\Admin\SeoSettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LearningCenterController extends Controller
{
    //
    public function index()
    {
        $seoInfo = SeoSettingRepository::getInfo('/learning-center');
        $lessons = LesssonInfo::where('status', true)->orderBy('num')->paginate(10);

        return view('learning-center', ['seoInfo' => $seoInfo, 'lessons' => $lessons]);
    }

    public function show(Request $request, $id)
    {
        $seoInfo = SeoSettingRepository::getInfo('/learning-center');
        $lessonInfo = LesssonInfo::find($id);

        $ip = $request->ip();
        $cacheKey = 'lessons_viewed_' . $id . '_' . $ip;

        // 檢查是否已經瀏覽過此案例
        if (!Cache::has($cacheKey)) {
            // 紀錄到快取中，防止短時間內重複紀錄，設置緩存，1小時後過期
            Cache::put($cacheKey, true, now()->addHour());
            // 增加瀏覽次數
            $lessonInfo->increment('views');

            // cache()->put($cacheKey, true, 3600);
        }

        return view('learning-center-details')
            ->with('seoInfo', $seoInfo)
            ->with('lessonInfo', $lessonInfo);
    }
}
