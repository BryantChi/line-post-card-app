<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 權限與歸屬在 Controller 驗證
    }

    public function rules(): array
    {
        return [
            'amount'              => 'required|integer|min:1',
            'reason'              => 'required|string|max:255',
            'action'              => 'nullable|in:refund,void,manual',
            'rollback_expiration' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => '請輸入退款金額',
            'amount.min'      => '退款金額需大於 0',
            'reason.required' => '請填寫退款原因',
        ];
    }
}
