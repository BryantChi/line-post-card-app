<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Image;
use Flash;

class SubscriptionPlanController extends AppBaseController
{
    /**
     * 圖示上傳的目錄（位於 public/uploads/images/ 下）
     */
    protected const ICON_DIR = 'subscription_plan_icons';

    public function index(Request $request)
    {
        $query = SubscriptionPlan::query()
            ->withCount('renewalOrders')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->has('active') && $request->active !== '') {
            $query->where('active', (bool) $request->active);
        }

        $plans = $query->paginate(15)->appends($request->all());

        return view('admin.subscription_plans.index')->with('plans', $plans);
    }

    public function create()
    {
        return view('admin.subscription_plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateAndPrepare($request);

        // 處理新上傳的圖示
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->processImage($request->file('icon'), self::ICON_DIR);
        } else {
            $data['icon'] = null;
        }

        SubscriptionPlan::create($data);

        Flash::success('訂閱方案已建立');
        return redirect(route('admin.subscriptionPlans.index'));
    }

    public function show($id)
    {
        return redirect(route('admin.subscriptionPlans.edit', $id));
    }

    public function edit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription_plans.edit')->with('plan', $plan);
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $data = $this->validateAndPrepare($request);

        // 處理圖示：移除 / 替換 / 保留
        if ($request->boolean('remove_icon') && !$request->hasFile('icon')) {
            $this->deleteIcon($plan->icon);
            $data['icon'] = null;
        } elseif ($request->hasFile('icon')) {
            $data['icon'] = $this->handleImageUpload($request->file('icon'), $plan->icon, self::ICON_DIR);
        } else {
            unset($data['icon']); // 保留原值
        }

        $plan->update($data);

        Flash::success('訂閱方案已更新');
        return redirect(route('admin.subscriptionPlans.index'));
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // 有使用中的訂單時不可刪除
        if ($plan->renewalOrders()->whereIn('status', ['pending', 'paid'])->exists()) {
            Flash::error('此方案有進行中或已付款的訂單，無法刪除');
            return redirect(route('admin.subscriptionPlans.index'));
        }

        $this->deleteIcon($plan->icon);
        $plan->delete();

        Flash::success('訂閱方案已刪除');
        return redirect(route('admin.subscriptionPlans.index'));
    }

    /**
     * 驗證並準備寫入資料（store/update 共用，不含 icon 檔案處理）
     */
    protected function validateAndPrepare(Request $request): array
    {
        $tierKeys = array_keys(SubscriptionPlan::TIER_OPTIONS);

        $request->validate([
            'name'            => 'required|string|max:100',
            'plan_tier'       => 'nullable|in:' . implode(',', $tierKeys),
            'page_limit'      => 'nullable|integer|min:1|max:999',
            'icon'            => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'description'     => 'nullable|string|max:5000',
            'target_audience' => 'nullable|string|max:255',
            'price'           => 'required|integer|min:1|max:999999',
            'duration_days'   => 'required|integer|min:1|max:3650',
            'active'          => 'required|in:0,1',
            'is_popular'      => 'nullable|in:0,1',
            'is_featured'     => 'nullable|in:0,1',
            'sort_order'      => 'required|integer|min:0|max:999',
        ]);

        return [
            'name'            => $request->input('name'),
            'plan_tier'       => $request->input('plan_tier') ?: null,
            'page_limit'      => $request->filled('page_limit') ? (int) $request->input('page_limit') : null,
            'description'     => $request->input('description'),
            'target_audience' => $request->input('target_audience'),
            'price'           => (int) $request->input('price'),
            'duration_days'   => (int) $request->input('duration_days'),
            'active'          => (bool) $request->input('active'),
            'is_popular'      => $request->boolean('is_popular'),
            'is_featured'     => $request->boolean('is_featured'),
            'sort_order'      => (int) $request->input('sort_order'),
        ];
    }

    /**
     * 上傳並壓縮新圖示（沿用專案既有 pattern）
     * 回傳資料庫儲存路徑（不含 uploads 前綴）
     */
    protected function processImage($image, string $uploadDir, int $resizeWidth = 400, int $quality = 80): string
    {
        $path = public_path('uploads/images/' . $uploadDir) . '/';
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $image->getClientOriginalName());

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        Image::make($image)
            ->orientate()
            ->resize($resizeWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('jpg', $quality)
            ->save($path . $filename);

        return 'images/' . $uploadDir . '/' . $filename;
    }

    /**
     * 更新時：有新檔則替換並刪舊；無新檔保留原路徑
     */
    protected function handleImageUpload($newImage, ?string $existingImagePath, string $uploadDir, int $resizeWidth = 400, int $quality = 80): ?string
    {
        if (!$newImage) {
            return $existingImagePath;
        }

        $this->deleteIcon($existingImagePath);
        return $this->processImage($newImage, $uploadDir, $resizeWidth, $quality);
    }

    /**
     * 刪除既有圖示檔
     */
    protected function deleteIcon(?string $path): void
    {
        if (!empty($path) && File::exists(public_path('uploads/' . $path))) {
            File::delete(public_path('uploads/' . $path));
        }
    }
}
