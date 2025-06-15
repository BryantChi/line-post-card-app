<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\AiContentGeneratorService; // 引入服務

class AiController extends Controller
{
    protected $aiGeneratorService;

    public function __construct(AiContentGeneratorService $aiGeneratorService)
    {
        $this->aiGeneratorService = $aiGeneratorService;
    }

    public function generateBusinessCardContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '輸入驗證失敗', 'errors' => $validator->errors()], 422);
        }

        $title = $request->input('title');
        $subtitle = $request->input('subtitle');

        // 使用服務生成內容
        $generatedContent = $this->aiGeneratorService->generateCardDescription($title, $subtitle);

        if ($generatedContent) {
            return response()->json(['success' => true, 'content' => $generatedContent]);
        } else {
            return response()->json(['success' => false, 'message' => 'AI 未能生成內容或發生錯誤，請檢查日誌。']);
        }
    }
}
