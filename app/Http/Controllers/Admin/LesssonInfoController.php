<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateLesssonInfoRequest;
use App\Http\Requests\Admin\UpdateLesssonInfoRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Admin\LesssonInfoRepository;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Flash;

class LesssonInfoController extends AppBaseController
{
    /** @var LesssonInfoRepository $lesssonInfoRepository*/
    private $lesssonInfoRepository;

    public function __construct(LesssonInfoRepository $lesssonInfoRepo)
    {
        $this->lesssonInfoRepository = $lesssonInfoRepo;
    }

    /**
     * Display a listing of the LesssonInfo.
     */
    public function index(Request $request)
    {
        $lesssonInfos = $this->lesssonInfoRepository->paginate(10);

        return view('admin.lessson_infos.index')
            ->with('lesssonInfos', $lesssonInfos);
    }

    /**
     * Show the form for creating a new LesssonInfo.
     */
    public function create()
    {
        return view('admin.lessson_infos.create');
    }

    /**
     * Store a newly created LesssonInfo in storage.
     */
    public function store(CreateLesssonInfoRequest $request)
    {
        $input = $request->all();

        $input['image'] = $this->processImage($request->file('image'), 'cover_front_image');

        $lesssonInfo = $this->lesssonInfoRepository->create($input);

        Flash::success('Lessson Info saved successfully.');

        return redirect(route('admin.lessonInfos.index'));
    }

    /**
     * Display the specified LesssonInfo.
     */
    public function show($id)
    {
        $lesssonInfo = $this->lesssonInfoRepository->find($id);

        if (empty($lesssonInfo)) {
            Flash::error('Lessson Info not found');

            return redirect(route('admin.lessonInfos.index'));
        }

        return view('admin.lessson_infos.show')->with('lesssonInfo', $lesssonInfo);
    }

    /**
     * Show the form for editing the specified LesssonInfo.
     */
    public function edit($id)
    {
        $lesssonInfo = $this->lesssonInfoRepository->find($id);

        if (empty($lesssonInfo)) {
            Flash::error('Lessson Info not found');

            return redirect(route('admin.lessonInfos.index'));
        }

        return view('admin.lessson_infos.edit')->with('lesssonInfo', $lesssonInfo);
    }

    /**
     * Update the specified LesssonInfo in storage.
     */
    public function update($id, UpdateLesssonInfoRequest $request)
    {
        $lesssonInfo = $this->lesssonInfoRepository->find($id);

        if (empty($lesssonInfo)) {
            Flash::error('Lessson Info not found');

            return redirect(route('admin.lessonInfos.index'));
        }

        $input = $request->all();

        $input['image'] = $this->handleImageUpload($request->file('image'), $lesssonInfo['image'], 'cover_front_image');

        $lesssonInfo = $this->lesssonInfoRepository->update($input, $id);

        Flash::success('Lessson Info updated successfully.');

        return redirect(route('admin.lessonInfos.index'));
    }

    /**
     * Remove the specified LesssonInfo from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $lesssonInfo = $this->lesssonInfoRepository->find($id);

        if (empty($lesssonInfo)) {
            Flash::error('Lessson Info not found');

            return redirect(route('admin.lessonInfos.index'));
        }

        $this->lesssonInfoRepository->delete($id);

        Flash::success('Lessson Info deleted successfully.');

        return redirect(route('admin.lessonInfos.index'));
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
