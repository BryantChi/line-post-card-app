<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'card_templates';

    protected $fillable = [
        'name',
        'description',
        'preview_image',
        'template_schema',
        'editable_fields',
        'active'
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'preview_image' => 'string',
        'template_schema' => 'array',
        'editable_fields' => 'array',
        'active' => 'boolean'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'preview_image' => 'nullable|string',
        'template_schema' => 'required|json',
        'editable_fields' => 'nullable|json',
        'active' => 'boolean'
    ];

    /**
     * 此模板關聯的所有電子名片
     */
    public function cardbubbles()
    {
        return $this->hasMany(CardBubble::class, 'template_id');
    }

    /**
     * 檢查用戶是否有權限編輯此模板
     */
    public function canBeEditedBy(User $user)
    {
        // 只有超級管理員和主帳號可以編輯模板
        return $user->isSuperAdmin() || $user->isMainUser();
    }

    /**
     * 獲取模板中定義的可編輯欄位
     *
     * @return array
     */
    public function getEditableFields()
    {
        return $this->editable_fields ?? [];
    }

    /**
     * 檢查指定欄位是否可編輯
     *
     * @param string $fieldName
     * @return bool
     */
    public function isFieldEditable($fieldName)
    {
        $editableFields = $this->getEditableFields();

        return isset($editableFields[$fieldName]);
    }

    /**
     * 獲取可編輯欄位的設定（標籤、類型、驗證規則等）
     *
     * @param string $fieldName
     * @return array|null
     */
    public function getFieldSettings($fieldName)
    {
        $editableFields = $this->getEditableFields();

        return $editableFields[$fieldName] ?? null;
    }
}
