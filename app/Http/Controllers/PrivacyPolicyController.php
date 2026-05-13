<?php

namespace App\Http\Controllers;

use App\Repositories\Admin\SeoSettingRepository;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $seoInfo = SeoSettingRepository::getInfo('/privacy-policy');
        return view('privacy-policy', compact('seoInfo'));
    }
}
