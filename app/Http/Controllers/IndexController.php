<?php

namespace App\Http\Controllers;

use App\Models\Admin\CaseInfo;
use App\Models\Admin\NewsInfo;
use App\Models\Admin\Product;
use App\Repositories\Admin\SeoSettingRepository;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    //
    public function index()
    {
        $seoInfo = SeoSettingRepository::getInfo('/*');
        $cases = CaseInfo::with('businessCard')->where('status', true)->orderBy('created_at', 'desc')->limit(6)->get();
        return view('index')
            ->with('seoInfo', $seoInfo)
            ->with('cases', $cases);
    }
}
