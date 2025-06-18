<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateCardTemplatesRequest;
use App\Http\Requests\Admin\UpdateCardTemplatesRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Admin\CardTemplatesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Flash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use App\Models\CardTemplate;

class CardTemplatesController extends AppBaseController
{
    /** @var CardTemplatesRepository $cardTemplatesRepository*/
    private $cardTemplatesRepository;

    public function __construct(CardTemplatesRepository $cardTemplatesRepo)
    {
        $this->cardTemplatesRepository = $cardTemplatesRepo;
        // 只允許超級管理員和主帳號訪問
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->isMainUser()) {
                Flash::error('您沒有權限管理卡片模板');
                return redirect(route('admin.dashboard'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the CardTemplates.
     */
    public function index(Request $request)
    {
        $cardTemplates = CardTemplate::paginate(10);

        return view('admin.card_templates.index')
            ->with('cardTemplates', $cardTemplates);
    }

    /**
     * Show the form for creating a new CardTemplates.
     */
    public function create()
    {
        return view('admin.card_templates.create');
    }

    /**
     * Store a newly created CardTemplates in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preview_image' => 'nullable|image|max:2048|mimes:png,jpg,jpeg',
            'template_schema' => 'required|json',
            'editable_fields' => 'required|array',
            'active' => 'nullable|boolean',
        ]);

        // 處理可編輯欄位格式
        if (isset($validated['editable_fields'])) {
            foreach ($validated['editable_fields'] as $key => $field) {
                // 確保欄位識別碼是欄位的 key
                $fieldKey = $field['key'] ?? $key;

                // 如果 key 與陣列索引不同，需要重新組織資料
                if ($fieldKey != $key) {
                    $validated['editable_fields'][$fieldKey] = $field;
                    unset($validated['editable_fields'][$key]);
                }

                // 移除多餘的 key 欄位
                if (isset($validated['editable_fields'][$fieldKey]['key'])) {
                    unset($validated['editable_fields'][$fieldKey]['key']);
                }

                // 處理必填欄位
                $validated['editable_fields'][$fieldKey]['required'] =
                    $validated['editable_fields'][$fieldKey]['required'] == '1';
            }
        }

        // 處理模板基本結構，解析 JSON 字串為陣列
        if (isset($validated['template_schema']) && is_string($validated['template_schema'])) {
            try {
                $templateSchema = json_decode($validated['template_schema'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $validated['template_schema'] = $templateSchema;
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['template_schema' => '模板 JSON 格式不正確']);
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['template_schema' => '解析模板時發生錯誤']);
            }
        }

        // 處理預覽圖片上傳
        if ($request->hasFile('preview_image')) {
            // $path = $request->file('preview_image')->store('card_templates', 'public');
            // $validated['preview_image'] = $path;
            $validated['preview_image'] = $this->processImage($request->file('preview_image'), 'card_templates');
        }

        // 設定啟用狀態
        $validated['active'] = $validated['active'] ?? false;

        // 創建模板
        $cardTemplate = CardTemplate::create($validated);

        Flash::success('卡片模板建立成功');

        return redirect(route('admin.cardTemplates.index'));
    }

    /**
     * Display the specified CardTemplates.
     */
    public function show($id)
    {
        $cardTemplate = CardTemplate::findOrFail($id);

        return view('admin.card_templates.show')->with('cardTemplate', $cardTemplate);
    }

    /**
     * Show the form for editing the specified CardTemplates.
     */
    public function edit($id)
    {
        $cardTemplate = CardTemplate::findOrFail($id);

        return view('admin.card_templates.edit')->with('cardTemplate', $cardTemplate);
    }

    /**
     * Update the specified CardTemplates in storage.
     */
    public function update($id, Request $request)
    {
        $cardTemplate = CardTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preview_image' => 'nullable|image|max:2048|mimes:png,jpg,jpeg',
            'template_schema' => 'required|json',
            'editable_fields' => 'required|array',
            'active' => 'nullable|boolean',
        ]);

        // 處理可編輯欄位格式
        if (isset($validated['editable_fields'])) {
            foreach ($validated['editable_fields'] as $key => $field) {
                // 確保欄位識別碼是欄位的 key
                $fieldKey = $field['key'] ?? $key;

                // 如果 key 與陣列索引不同，需要重新組織資料
                if ($fieldKey != $key) {
                    $validated['editable_fields'][$fieldKey] = $field;
                    unset($validated['editable_fields'][$key]);
                }

                // 移除多餘的 key 欄位
                if (isset($validated['editable_fields'][$fieldKey]['key'])) {
                    unset($validated['editable_fields'][$fieldKey]['key']);
                }

                // 處理必填欄位
                $validated['editable_fields'][$fieldKey]['required'] =
                    $validated['editable_fields'][$fieldKey]['required'] == '1';
            }
        }

        // 處理模板基本結構，解析 JSON 字串為陣列
        if (isset($validated['template_schema']) && is_string($validated['template_schema'])) {
            try {
                $templateSchema = json_decode($validated['template_schema'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $validated['template_schema'] = $templateSchema;
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['template_schema' => '模板 JSON 格式不正確']);
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['template_schema' => '解析模板時發生錯誤']);
            }
        }

        // 處理預覽圖片上傳
        if ($request->hasFile('preview_image')) {
            // 刪除舊圖片
            // if ($cardTemplate->preview_image) {
            //     Storage::disk('public')->delete($cardTemplate->preview_image);
            // }

            // $path = $request->file('preview_image')->store('card_templates', 'public');
            // $validated['preview_image'] = $path;

            $validated['preview_image'] = $this->handleImageUpload(
                $request->file('preview_image'),
                $cardTemplate->preview_image,
                'card_templates'
            );
        }

        // 設定啟用狀態
        $validated['active'] = $validated['active'] ?? false;

        // 更新模板
        $cardTemplate->update($validated);

        Flash::success('卡片模板更新成功');

        return redirect(route('admin.cardTemplates.index'));
    }

    /**
     * Remove the specified CardTemplates from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cardTemplate = CardTemplate::findOrFail($id);

        // 檢查是否有關聯的名片
        if ($cardTemplate->cardbubbles()->count() > 0) {
            Flash::error('此模板已經被使用，無法刪除');
            return redirect(route('admin.cardTemplates.index'));
        }

        // 刪除預覽圖片
        if ($cardTemplate->preview_image) {
            Storage::disk('public')->delete($cardTemplate->preview_image);
        }

        $cardTemplate->delete();

        Flash::success('卡片模板刪除成功');

        return redirect(route('admin.cardTemplates.index'));
    }

    /**
     * 複製指定的卡片模板
     */
    public function duplicate($id)
    {
        $original = CardTemplate::findOrFail($id);

        $new = $original->replicate();
        // 名稱加上「複製」字樣避免重複
        $new->name = $original->name . ' (複製)';
        // 若有預覽圖片，複製一份圖片
        if ($original->preview_image && file_exists(public_path('uploads/' . $original->preview_image))) {
            $ext = pathinfo($original->preview_image, PATHINFO_EXTENSION);
            $newImageName = 'images/card_templates/' . time() . '_copy.' . $ext;
            $src = public_path('uploads/' . $original->preview_image);
            $dst = public_path('uploads/' . $newImageName);
            copy($src, $dst);
            $new->preview_image = $newImageName;
        }
        $new->save();

        Flash::success('卡片模板複製成功');
        return redirect(route('admin.cardTemplates.index'));
    }

    /**
     * 切換模板啟用狀態
     */
    public function toggleActive($id)
    {
        $cardTemplate = CardTemplate::findOrFail($id);
        $cardTemplate->active = !$cardTemplate->active;
        $cardTemplate->save();

        $status = $cardTemplate->active ? '啟用' : '停用';
        Flash::success("模板已{$status}");

        return redirect(route('admin.cardTemplates.index'));
    }

    // 共用的圖片處理函式
    function processImage($image, $uploadDir, $resizeWidth = 800, $quality = 75)
    {
        if ($image) {
            $path = public_path('uploads/images/' . $uploadDir) . '/';
            $filename = time() . '_' . $image->getClientOriginalName();

            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            // 壓縮圖片
            $image = Image::make($image)
                ->orientate()
                ->resize($resizeWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', $quality); // 設定 JPG 格式和品質
            $image->save($path . $filename);

            return 'images/' . $uploadDir . '/' . $filename;
        }

        return '';
    }

    // 共用圖片處理函式
    function handleImageUpload($newImage, $existingImagePath, $uploadDir, $resizeWidth = 800, $quality = 75)
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
}
