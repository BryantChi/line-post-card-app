<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use App\Models\CardBubble;
use App\Models\CardTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Flash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use App\Services\CustomFlexMessageBuilder;

class CardBubblesController extends Controller
{
    /** @var CustomFlexMessageBuilder */
    protected $flexBuilder;

    public function __construct(CustomFlexMessageBuilder $flexBuilder)
    {
        $this->flexBuilder = $flexBuilder;
    }

    /**
     * 顯示特定電子名片的所有氣泡
     */
    public function index($businessCardId)
    {
        $card = BusinessCard::findOrFail($businessCardId);

        // 檢查權限
        if (!$card->canBeViewedBy(Auth::user())) {
            Flash::error('您沒有權限查看此電子名片的卡片');
            return redirect()->route('admin.businessCards.index');
        }

        $bubbles = $card->bubbles()->orderBy('order')->get();

        return view('admin.card_bubbles.index', compact('card', 'bubbles'));
    }

    /**
     * 創建新氣泡表單
     */
    public function create($businessCardId)
    {
        $card = BusinessCard::findOrFail($businessCardId);

        // 確認氣泡卡片上限最多 10 個
        // 如果已經有 10 個氣泡卡片，則不允許再添加
        if ($card->bubbles()->count() >= 10) {
            // 使用 Flash 提示用戶
            Flash::error('電子名片-卡片數量已達上限');
            return redirect()->route('admin.businessCards.bubbles.index', $card->id);
        }

        // 檢查權限
        if (!$card->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限添加電子名片-卡片');
            return redirect()->route('admin.businessCards.index');
        }

        $templates = CardTemplate::where('active', true)->get();

        $shareUrl = $card->getShareUrl();

        return view('admin.card_bubbles.create', compact('card', 'templates', 'shareUrl'));
    }

    /**
     * 儲存新氣泡
     */
    public function store(Request $request, $businessCardId)
    {
        $card = BusinessCard::findOrFail($businessCardId);

        // 檢查權限
        if (!$card->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限添加電子名片-卡片');
            return redirect()->route('admin.businessCards.index');
        }

        $validated = $request->validate([
            'template_id' => 'required|exists:card_templates,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'content' => 'nullable|string',
        ]);

        // 處理圖片上傳
        if ($request->hasFile('image')) {
            $validated['image'] = $this->handleImageUpload(
                $request->file('image'),
                null,
                'card_bubbles'
            );
        }

        // 取得當前最大的排序值
        $maxOrder = CardBubble::where('card_id', $card->id)->max('order') ?? 0;

        // 使用 Flex Message Builder 生成 JSON
        $bubbleData = $request->except(['_token', 'template_id', 'title', 'subtitle', 'image', 'content']);
        // 處理動態圖片欄位
        foreach ($request->allFiles() as $fieldName => $file) {
            // 跳過已處理的主圖片
            if ($fieldName === 'image') continue;

            // 處理動態圖片欄位
            if (is_file($file) && $file->isValid()) {
                $imagePath = $this->handleImageUpload(
                    $file,
                    null,
                    'card_bubbles'
                );

                // 記錄完整的圖片URL (與現有的handleImageUpload方法保持一致)
                $bubbleData[$fieldName] = url('/uploads/' . $imagePath);
            }
        }
        $jsonContent = $this->flexBuilder->buildBubbleJson(
            $request->template_id,
            array_merge($validated, $bubbleData)
        );

        // 創建氣泡卡片
        $bubble = new CardBubble([
            'card_id' => $card->id,
            'template_id' => $validated['template_id'],
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'image' => $validated['image'] ?? null,
            'content' => $validated['content'] ?? null,
            'bubble_data' => $bubbleData,
            'json_content' => $jsonContent,
            'order' => $maxOrder + 1,
            'active' => true
        ]);

        $bubble->save();

        // 更新主卡片的 flex_json (組合所有氣泡)
        $this->updateCardFlexJson($card);

        Flash::success('電子名片-卡片創建成功');

        return redirect()->route('admin.businessCards.bubbles.index', $card->id);
    }

    /**
     * 顯示氣泡卡片詳情
     */
    public function show($businessCardId, $bubbleId)
    {
        $card = BusinessCard::findOrFail($businessCardId);
        $bubble = CardBubble::where('card_id', $businessCardId)
                           ->where('id', $bubbleId)
                           ->firstOrFail();

        // 檢查權限
        if (!$bubble->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限查看此氣泡卡片');
            return redirect()->route('admin.businessCards.index');
        }

        return view('admin.card_bubbles.show', compact('card', 'bubble'));
    }

    /**
     * 編輯氣泡卡片表單
     */
    public function edit($businessCardId, $bubbleId)
    {
        $card = BusinessCard::findOrFail($businessCardId);
        $bubble = CardBubble::where('card_id', $businessCardId)
                           ->where('id', $bubbleId)
                           ->firstOrFail();

        // 檢查權限
        if (!$bubble->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限編輯此電子名片-卡片');
            return redirect()->route('admin.businessCards.index');
        }

        $templates = CardTemplate::where('active', true)->get();

        $shareUrl = $card->getShareUrl();

        return view('admin.card_bubbles.edit', compact('card', 'bubble', 'templates', 'shareUrl'));
    }

    /**
     * 更新氣泡卡片
     */
    public function update(Request $request, $businessCardId, $bubbleId)
    {
        $card = BusinessCard::findOrFail($businessCardId);
        $bubble = CardBubble::where('card_id', $businessCardId)
                           ->where('id', $bubbleId)
                           ->firstOrFail();

        // 檢查權限
        if (!$bubble->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限更新此電子名片-卡片');
            return redirect()->route('admin.businessCards.index');
        }

        $validated = $request->validate([
            'template_id' => 'required|exists:card_templates,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'content' => 'nullable|string',
            'active' => 'boolean',
        ]);

        // 處理圖片上傳
        if ($request->hasFile('image')) {
            $validated['image'] = $this->handleImageUpload(
                $request->file('image'),
                $bubble->image,
                'card_bubbles'
            );
        }

        // 處理表單提交的所有欄位數據
        $bubbleData = $request->except(['_token', '_method', 'template_id', 'title', 'subtitle', 'image', 'content', 'active']);

        // 保留現有的bubble_data資料
        $existingBubbleData = $bubble->bubble_data ?? [];

        // 處理動態圖片欄位
        foreach ($request->allFiles() as $fieldName => $file) {
            // 跳過已處理的主圖片
            if ($fieldName === 'image') continue;

            // 取得現有圖片路徑（如果存在）
            $existingImagePath = isset($existingBubbleData[$fieldName]) ? $existingBubbleData[$fieldName] : null;

            // 處理動態圖片欄位
            if (is_file($file) && $file->isValid()) {
                $imagePath = $this->handleImageUpload(
                    $file,
                    $existingImagePath,
                    'card_bubbles'
                );

                $bubbleData[$fieldName] = url('/uploads/' . $imagePath);
            }
        }

        // 合併原有未修改的bubble_data
        $bubbleData = array_merge($existingBubbleData, $bubbleData);

        // 使用 Flex Message Builder 生成 JSON
        $jsonContent = $this->flexBuilder->buildBubbleJson(
            $validated['template_id'],
            array_merge($validated, $bubbleData)
        );

        // 更新氣泡卡片
        $bubble->template_id = $validated['template_id'];
        $bubble->title = $validated['title'];
        $bubble->subtitle = $validated['subtitle'] ?? null;
        if (isset($validated['image'])) {
            $bubble->image = $validated['image'];
        }
        $bubble->content = $validated['content'] ?? null;
        $bubble->bubble_data = $bubbleData;
        $bubble->json_content = $jsonContent;
        $bubble->active = $request->has('active');
        $bubble->save();

        // 更新主卡片的 flex_json (組合所有氣泡)
        $this->updateCardFlexJson($card);

        Flash::success('氣泡卡片更新成功');

        return redirect()->route('admin.businessCards.bubbles.index', $card->id);
    }

    /**
     * 刪除氣泡卡片
     */
    public function destroy($businessCardId, $bubbleId)
    {
        $card = BusinessCard::findOrFail($businessCardId);
        $bubble = CardBubble::where('card_id', $businessCardId)
                           ->where('id', $bubbleId)
                           ->firstOrFail();

        // 檢查權限
        if (!$bubble->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限刪除此電子名片-卡片');
            return redirect()->route('admin.businessCards.index');
        }

        // 如果有圖片，刪除圖片檔案
        if ($bubble->image && File::exists(public_path('uploads/' . $bubble->image))) {
            File::delete(public_path('uploads/' . $bubble->image));
        }

        $bubble->delete();

        // 更新其他氣泡的排序
        $this->reorderBubbles($card);

        // 更新主卡片的 flex_json
        $this->updateCardFlexJson($card);

        Flash::success('電子名片-卡片刪除成功');

        return redirect()->route('admin.businessCards.bubbles.index', $card->id);
    }

    /**
     * 調整氣泡卡片排序
     */
    public function reorder(Request $request, $businessCardId)
    {
        $card = BusinessCard::findOrFail($businessCardId);

        // 檢查權限
        if (!$card->canBeEditedBy(Auth::user())) {
            return response()->json(['success' => false, 'message' => '您沒有權限調整排序']);
        }

        $validated = $request->validate([
            'bubbles' => 'required|array',
            'bubbles.*' => 'exists:card_bubbles,id'
        ]);

        // 更新排序
        foreach ($validated['bubbles'] as $index => $bubbleId) {
            CardBubble::where('id', $bubbleId)
                    ->where('card_id', $card->id)
                    ->update(['order' => $index + 1]);
        }

        // 更新主卡片的 flex_json (組合所有氣泡)
        $this->updateCardFlexJson($card);

        // 獲取更新後的卡片
        $card = BusinessCard::find($businessCardId);

        return response()->json([
            'success' => true,
            'message' => '排序成功',
            'flex_json' => $card->flex_json // 返回更新後的 flex_json
        ]);
    }

    /**
     * 處理上傳圖片
     */
    private function handleImageUpload($newImage, $existingImagePath, $uploadDir, $resizeWidth = 800, $quality = 75)
    {
        if ($newImage) {
            $path = public_path('uploads/images/' . $uploadDir . '/');
            $filename = time() . '_' . $newImage->getClientOriginalName();

            // 確保目錄存在
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            // 若已有圖片，刪除舊圖片
            if (!empty($existingImagePath) && File::exists(public_path('uploads/' . $existingImagePath))) {
                File::delete(public_path('uploads/' . $existingImagePath));
            }

            // 壓縮並保存新圖片
            $image = Image::make($newImage)
                ->orientate()
                ->resize($resizeWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', $quality); // 設定 JPG 格式和品質
            $image->save($path . $filename);

            return 'images/' . $uploadDir . '/' . $filename;
        }

        // 若無新圖片，返回舊圖片路徑
        return $existingImagePath;
    }

    /**
     * 重新排序氣泡卡片
     */
    private function reorderBubbles($card)
    {
        $bubbles = $card->bubbles()->orderBy('order')->get();
        foreach ($bubbles as $index => $bubble) {
            $bubble->order = $index + 1;
            $bubble->save();
        }
    }

    /**
     * 更新主卡片的 flex_json
     */
    private function updateCardFlexJson($card)
    {
        // 獲取所有啟用的氣泡卡片
        $bubbles = $card->bubbles()
                      ->where('active', true)
                      ->orderBy('order')
                      ->get();

        // 如果沒有氣泡，清空 flex_json
        if ($bubbles->isEmpty()) {
            $card->flex_json = null;
            $card->save();
            return;
        }

        // 建立 Carousel 容器
        $carouselJson = [
            'type' => 'carousel',
            'contents' => []
        ];

        // 添加每個氣泡的 JSON
        foreach ($bubbles as $bubble) {
            if (is_array($bubble->json_content)) {
                $carouselJson['contents'][] = $bubble->json_content;
            } elseif (is_string($bubble->json_content) && !empty($bubble->json_content)) {
                try {
                    $bubbleJson = json_decode($bubble->json_content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $carouselJson['contents'][] = $bubbleJson;
                    }
                } catch (\Exception $e) {
                    // 忽略無效的 JSON
                }
            }
        }

        // 如果只有一個氣泡，直接使用該氣泡的 JSON
        if (count($carouselJson['contents']) === 1) {
            $card->flex_json = $carouselJson['contents'][0];
        } else {
            $card->flex_json = $carouselJson;
        }

        $card->save();
    }
}
