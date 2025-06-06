<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateBusinessCardsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|max:2048',
            'content' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => '請輸入卡片組名稱',
            'title.max' => '卡片組名稱不能超過 255 個字元',
            'subtitle.max' => '副標題不能超過 255 個字元',
            'profile_image.image' => '請上傳有效的圖片檔案',
            'profile_image.max' => '圖片大小不能超過 2MB',
        ];
    }
}
