<?php

namespace App\Http\Controllers;

use App\Repositories\Admin\SeoSettingRepository;
use Illuminate\Http\Request;

class FeaturesController extends Controller
{
    //

    public function index()
    {
        $seoInfo = SeoSettingRepository::getInfo('/features');
        return view('features', compact('seoInfo'));
    }
}
