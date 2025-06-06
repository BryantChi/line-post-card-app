<?php

namespace App\Repositories\Admin;

use App\Models\Admin\CardTemplates;
use App\Repositories\BaseRepository;

class CardTemplatesRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'description',
        'preview_image',
        'template_schema'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CardTemplates::class;
    }
}
