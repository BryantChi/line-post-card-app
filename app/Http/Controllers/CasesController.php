<?php

namespace App\Http\Controllers;

use App\Models\Admin\CaseInfo;
use App\Repositories\Admin\SeoSettingRepository;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    //
    public function index()
    {
        $seoInfo = SeoSettingRepository::getInfo('/cases');
        $cases = CaseInfo::with('businessCard')->where('status', true)->orderBy('created_at', 'desc')->paginate(10);

        return view('cases', compact('seoInfo', 'cases'));
    }
}
