<?php

namespace App\Repositories\Admin;

use App\Models\Admin\LesssonInfo;
use App\Repositories\BaseRepository;

class LesssonInfoRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'content',
        'image',
        'views',
        'num',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return LesssonInfo::class;
    }
}
