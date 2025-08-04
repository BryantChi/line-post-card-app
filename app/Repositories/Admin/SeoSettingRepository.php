<?php

namespace App\Repositories\Admin;

use App\Models\Admin\SeoSetting;
use App\Repositories\BaseRepository;

/**
 * Class SeoSettingRepository
 * @package App\Repositories\Admin
 * @version September 24, 2024, 5:04 pm UTC
*/

class SeoSettingRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
        'og_site_name',
        'ga_header',
        'ga_body'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SeoSetting::class;
    }

    public static function getInfo($url = '/*')
    {
        $seo = SeoSetting::where('url', $url)->first();

        if (empty($seo)) {
            $seo = SeoSetting::where('url', '/*')->first();
        }

        return $seo;
    }
}
