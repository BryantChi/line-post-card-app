<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkBusinessCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->isSuperAdmin() || $user->isMainUser());
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'selectTemplates' => [
                'template_ids' => ['required', 'array', 'min:1', 'max:10'],
                'template_ids.*' => ['integer', 'exists:card_templates,id'],
            ],
            'downloadTemplate' => [
                'template_ids' => ['required', 'array', 'min:1', 'max:10'],
                'template_ids.*' => ['integer', 'exists:card_templates,id'],
            ],
            'preview', 'execute' => [
                'excel' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'template_ids.required' => '請至少選擇一張模板',
            'template_ids.max' => '最多選擇 10 張模板',
            'excel.required' => '請上傳填好的 Excel 檔',
            'excel.mimes' => '檔案格式必須為 .xlsx',
            'excel.max' => '檔案大小不可超過 5MB',
        ];
    }
}
