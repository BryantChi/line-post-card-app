<?php

namespace App\Repositories\Admin;

use App\Models\Admin\BusinessCards;
use App\Repositories\BaseRepository;

class BusinessCardsRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_id',
        'template_id',
        'title',
        'subtitle',
        'profile_image',
        'content',
        'flex_json'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return BusinessCards::class;
    }
}
