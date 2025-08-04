<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CreateSeoSettingRequest;
use App\Http\Requests\Admin\UpdateSeoSettingRequest;
use App\Repositories\Admin\SeoSettingRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use HTMLPurifier;
use HTMLPurifier_Config;
use Response;

class SeoSettingController extends AppBaseController
{
    /** @var SeoSettingRepository $seoSettingRepository*/
    private $seoSettingRepository;

/*************  ✨ Codeium Command ⭐  *************/
    /**
     * Create a new controller instance.
     *
     * @param SeoSettingRepository $seoSettingRepo
     */
/******  d056104a-7c0f-45e4-b6e7-47a586d0f0c3  *******/
    public function __construct(SeoSettingRepository $seoSettingRepo)
    {
        $this->seoSettingRepository = $seoSettingRepo;
    }

    /**
     * Display a listing of the SeoSetting.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $seoSettings = $this->seoSettingRepository->paginate(10);

        return view('admin.seo_settings.index')
            ->with('seoSettings', $seoSettings);
    }

    /**
     * Show the form for creating a new SeoSetting.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.seo_settings.create');
    }

    /**
     * Store a newly created SeoSetting in storage.
     *
     * @param CreateSeoSettingRequest $request
     *
     * @return Response
     */
    public function store(CreateSeoSettingRequest $request)
    {
        $input = $request->all();

        $input['ga_header'] = $this->purifier($input['ga_header']);
        $input['ga_body']   = $this->purifier($input['ga_body']);

        $seoSetting = $this->seoSettingRepository->create($input);

        Flash::success('SEO設定儲存成功。');

        return redirect(route('admin.seoSettings.index'));
    }

    /**
     * Display the specified SeoSetting.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $seoSetting = $this->seoSettingRepository->find($id);

        if (empty($seoSetting)) {
            Flash::error('Seo Setting not found');

            return redirect(route('admin.seoSettings.index'));
        }

        return view('admin.seo_settings.show')->with('seoSetting', $seoSetting);
    }

    /**
     * Show the form for editing the specified SeoSetting.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $seoSetting = $this->seoSettingRepository->find($id);

        if (empty($seoSetting)) {
            Flash::error('Seo Setting not found');

            return redirect(route('admin.seoSettings.index'));
        }

        return view('admin.seo_settings.edit')->with('seoSetting', $seoSetting);
    }

    /**
     * Update the specified SeoSetting in storage.
     *
     * @param int $id
     * @param UpdateSeoSettingRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSeoSettingRequest $request)
    {
        $seoSetting = $this->seoSettingRepository->find($id);

        if (empty($seoSetting)) {
            Flash::error('Seo Setting not found');

            return redirect(route('admin.seoSettings.index'));
        }

        $input = $request->all();

        $input['ga_header'] = $this->purifier($input['ga_header']);
        $input['ga_body']   = $this->purifier($input['ga_body']);

        $seoSetting = $this->seoSettingRepository->update($input, $id);

        Flash::success('SEO設定更新成功。');

        return redirect(route('admin.seoSettings.index'));
    }

    /**
     * Remove the specified SeoSetting from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $seoSetting = $this->seoSettingRepository->find($id);

        if (empty($seoSetting)) {
            Flash::error('Seo Setting not found');

            return redirect(route('admin.seoSettings.index'));
        }

        $this->seoSettingRepository->delete($id);

        Flash::success('SEO設定刪除成功。');

        return redirect(route('admin.seoSettings.index'));
    }

    /**
     * AJAX 即時預覽：接收原始程式碼並回傳清洗後結果
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
        ]);

        $purifier = $this->purifier($data['code']);

        return response()->json(['clean_code' => $purifier]);
    }

    public function purifier($code)
    {
        $config = HTMLPurifier_Config::createDefault();

        // 必須先指定自訂定義 ID 與版本
        $config->set('HTML.DefinitionID', 'custom');
        $config->set('HTML.DefinitionRev', 1);

        $config->set('HTML.Trusted', true);

        // 允許的標籤與屬性：包含 script 和 iframe（你可以根據需求加入其他元素）
        $config->set('HTML.Allowed', 'script[async|src|type],iframe[src|width|height|frameborder|allowfullscreen],div,b,strong,i,em,a[href|title],p,br');

        // 擴充 HTML 定義：允許 script 的 async 和 iframe 的 allowfullscreen 屬性
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('script', 'async', 'Bool');
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        }

        $purifier = new HTMLPurifier($config);

        $cleanCode = $purifier->purify($code);

        return $cleanCode;
    }
}
