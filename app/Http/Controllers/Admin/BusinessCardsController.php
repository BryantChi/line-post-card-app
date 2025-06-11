<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateBusinessCardsRequest;
use App\Http\Requests\Admin\UpdateBusinessCardsRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\CardTemplate;
use App\Repositories\Admin\BusinessCardsRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use App\Models\BusinessCard;
use App\Services\CustomFlexMessageBuilder;
use Illuminate\Support\Facades\Bus;

class BusinessCardsController extends AppBaseController
{
    /** @var BusinessCardsRepository $businessCardsRepository*/
    private $businessCardsRepository;

    /** @var CustomFlexMessageBuilder */
    protected $flexBuilder;

    public function __construct(BusinessCardsRepository $businessCardsRepo, CustomFlexMessageBuilder $flexBuilder)
    {
        $this->businessCardsRepository = $businessCardsRepo;
        $this->flexBuilder = $flexBuilder;
    }

    /**
     * Display a listing of the BusinessCards.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 根據用戶角色決定顯示哪些卡片
        if ($user->isSuperAdmin()) {
            // 超級管理員可以看到所有卡片
            $businessCards = BusinessCard::with('user')
                               ->latest()
                               ->paginate(10);
        } elseif ($user->isMainUser()) {
            // 主帳號可以看到自己和子帳號的卡片
            $subUserIds = $user->subUsers->pluck('id')->toArray();
            $userIds = array_merge([$user->id], $subUserIds);
            $businessCards = BusinessCard::whereIn('user_id', $userIds)
                               ->with('user')
                               ->latest()
                               ->paginate(10);
        } else {
            // 子帳號只能看到自己的卡片
            $businessCards = BusinessCard::where('user_id', $user->id)
                               ->latest()
                               ->paginate(10);
        }


        return view('admin.business_cards.index')
            ->with('businessCards', $businessCards);
    }

    /**
     * Show the form for creating a new BusinessCards.
     */
    public function create()
    {
        return view('admin.business_cards.create');
    }

    /**
     * Store a newly created BusinessCards in storage.
     */
    public function store(CreateBusinessCardsRequest $request)
    {
        $user = Auth::user();
        if ($user->isSubUser() && $user->businessCards()->count() >= 1) {
            Flash::error('子帳號只能建立一組數位名片');
            return redirect()->back();
        }

        $input = $request->all();
        $input['user_id'] = $user->id;
        $input['uuid'] = (string) \Illuminate\Support\Str::uuid(); // 新增這一行

        // 處理上傳的圖片
        if ($request->hasFile('profile_image')) {
            $input['profile_image'] = $this->handleImageUpload(
                $request->file('profile_image'),
                null,
                'cards/images'
            );
        }

        // 建立數位名片
        $businessCard = BusinessCard::create($input);

        Flash::success('數位名片建立成功！請繼續添加氣泡卡片。');

        // 重定向到氣泡卡片管理頁面
        return redirect(route('admin.businessCards.bubbles.index', $businessCard->id));
    }

    /**
     * Display the specified BusinessCards.
     */
    public function show($id)
    {
        $businessCard = BusinessCard::findOrFail($id);

        // 檢查權限
        if (!$businessCard->canBeViewedBy(Auth::user())) {
            Flash::error('您沒有權限檢視此數位名片');
            return redirect(route('admin.businessCards.index'));
        }

        // 獲取所有關聯的氣泡卡片
        $bubbles = $businessCard->bubbles()->orderBy('order')->get();

        return view('admin.business_cards.show', compact('businessCard', 'bubbles'));
    }

    /**
     * Show the form for editing the specified BusinessCards.
     */
    public function edit($id)
    {
        $businessCard = BusinessCard::findOrFail($id);

        // 檢查權限
        if (!$businessCard->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限編輯此數位名片');
            return redirect(route('admin.businessCards.index'));
        }

        return view('admin.business_cards.edit', compact('businessCard'));
    }

    /**
     * Update the specified BusinessCards in storage.
     */
    public function update($id, UpdateBusinessCardsRequest $request)
    {
        $businessCard = BusinessCard::findOrFail($id);

        // 檢查權限
        if (!$businessCard->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限更新此數位名片');
            return redirect(route('admin.businessCards.index'));
        }

        $input = $request->all();

        // 處理上傳的圖片
        if ($request->hasFile('profile_image')) {
            $input['profile_image'] = $this->handleImageUpload(
                $request->file('profile_image'),
                $businessCard->profile_image,
                'cards/images'
            );
        }

        // 更新數位名片
        $businessCard = $businessCard->update($input);

        // 如果需要，重新產生 flex_json
        if ($request->has('regenerate_flex') && $request->regenerate_flex) {
            $businessCard->updateFlexJson();
        }

        Flash::success('數位名片更新成功');

        return redirect(route('admin.businessCards.index'));
    }

    /**
     * Remove the specified BusinessCards from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $businessCard = BusinessCard::findOrFail($id);

        // 檢查權限
        if (!$businessCard->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限刪除此數位名片');
            return redirect(route('admin.businessCards.index'));
        }

        // 先刪除所有相關的氣泡卡片
        foreach ($businessCard->bubbles as $bubble) {
            // 刪除氣泡卡片的圖片
            if ($bubble->image && File::exists(public_path('uploads/' . $bubble->image))) {
                File::delete(public_path('uploads/' . $bubble->image));
            }
            $bubble->delete();
        }

        // 刪除主數位名片的圖片
        if ($businessCard->profile_image && File::exists(public_path('uploads/' . $businessCard->profile_image))) {
            File::delete(public_path('uploads/' . $businessCard->profile_image));
        }

        $businessCard->delete();

        Flash::success('數位名片刪除成功');

        return redirect(route('admin.businessCards.index'));
    }

    /**
     * 主帳號查看所有子帳號的數位名片
     */
    public function allCards()
    {
        $user = Auth::user();

        if (!$user->isMainUser() && !$user->isSuperAdmin()) {
            Flash::error('您沒有權限查看所有數位名片');
            return redirect(route('admin.businessCards.index'));
        }

        // 如果是主帳號，顯示該主帳號所有子帳號的卡片
        if ($user->isMainUser()) {
            $subUserIds = $user->subUsers->pluck('id')->toArray();
            $cards = BusinessCard::whereIn('user_id', $subUserIds)
                               ->with('user')
                               ->latest()
                               ->paginate(10);
        } else {
            // 如果是超級管理員，顯示所有卡片
            $cards = BusinessCard::with('user')
                               ->latest()
                               ->paginate(10);
        }

        return view('admin.business_cards.all_cards', compact('cards'));
    }

    /**
     * 預覽數位名片 (公開分享頁面)
     */
    public function preview($uuid)
    {
        $businessCard = BusinessCard::where('uuid', $uuid)->firstOrFail();

        if (!$businessCard->active) {
            return view('card_preview.inactive');
        }

        $bubbles = $businessCard->activeBubbles()->orderBy('order')->get();

        return view('card_preview.show', compact('businessCard', 'bubbles'));
    }

    /**
     * 重新生成數位名片的 Flex JSON
     */
    public function regenerateFlexJson($id)
    {
        $businessCard = BusinessCard::findOrFail($id);

        // 檢查權限
        if (!$businessCard->canBeEditedBy(Auth::user())) {
            Flash::error('您沒有權限更新此數位名片');
            return redirect(route('admin.businessCards.index'));
        }

        $businessCard->updateFlexJson();

        Flash::success('Flex JSON 已重新生成');

        return redirect(route('admin.businessCards.show', $businessCard->id));
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
     * API endpoint to increment share count.
     * Note: You need to add a route for this method, e.g., in routes/api.php:
     * Route::post('/cards/{uuid}/increment-share', [App\Http\Controllers\Admin\BusinessCardsController::class, 'incrementShareCountApi']);
     */
    public function incrementShareCountApi(Request $request, $uuid)
    {
        $businessCard = BusinessCard::where('uuid', $uuid)->first();

        if ($businessCard) {
            $businessCard->increment('shares');
            $businessCard->save();
            return response()->json(['success' => true, 'message' => 'Share count incremented.']);
        }

        return response()->json(['success' => false, 'message' => 'Business card not found.'], 404);
    }
}
