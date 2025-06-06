<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CardTemplate;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 先新增 editable_fields 欄位
        Schema::table('card_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('card_templates', 'editable_fields')) {
                // 新增 editable_fields 欄位，類型為 JSON
                $table->json('editable_fields')->nullable()->comment('JSON 結構：定義可編輯欄位')->after('template_schema');
            }
        });

        // 然後將資料從 template_schema.editable_fields 移動到新欄位
        $templates = DB::table('card_templates')->get();
        foreach ($templates as $template) {
            $templateSchema = json_decode($template->template_schema, true);
            $editableFields = $templateSchema['editable_fields'] ?? [];

            // 如果有可編輯欄位，將其移動到新欄位，並從原欄位移除
            if (!empty($editableFields)) {
                // 更新 editable_fields 欄位
                DB::table('card_templates')
                    ->where('id', $template->id)
                    ->update(['editable_fields' => json_encode($editableFields)]);

                // 從 template_schema 中移除 editable_fields
                unset($templateSchema['editable_fields']);
                DB::table('card_templates')
                    ->where('id', $template->id)
                    ->update(['template_schema' => json_encode($templateSchema)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 將資料從 editable_fields 移回 template_schema.editable_fields
        $templates = DB::table('card_templates')->get();
        foreach ($templates as $template) {
            $templateSchema = json_decode($template->template_schema, true);
            $editableFields = json_decode($template->editable_fields, true) ?? [];

            // 如果有可編輯欄位，將其移回原欄位
            if (!empty($editableFields)) {
                $templateSchema['editable_fields'] = $editableFields;

                // 更新 template_schema 欄位
                DB::table('card_templates')
                    ->where('id', $template->id)
                    ->update(['template_schema' => json_encode($templateSchema)]);
            }
        }

        // 然後刪除 editable_fields 欄位
        Schema::table('card_templates', function (Blueprint $table) {
            $table->dropColumn('editable_fields');
        });
    }
};
