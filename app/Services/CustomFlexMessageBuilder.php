<?php

namespace App\Services;

use App\Models\Admin\CardTemplates;
use LINE\LINEBot\Flex\ContainerBuilder;   // 若要更方便組 Flex 可用此套件
use LINE\LINEBot\Flex\ComponentBuilder;
use LINE\LINEBot\Flex\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class CustomFlexMessageBuilder
{
    /**
     * 依據模板 ID 與表單欄位，生成最終 Flex JSON 結構
     * @param int $templateId
     * @param array $input 整個表單輸入陣列
     * @return array  Flex JSON 陣列
     */
    public function buildFlexJson(int $templateId, array $input): array
    {
        // 1. 取出對應模板的 schema (JSON 格式)
        $template = CardTemplates::findOrFail($templateId);
        $schema = json_decode($template->template_schema, true);
        // schema 內通常包含：氣泡樣式（bubble）、欄位佈局與可編輯欄位位置

        // 2. 將 $input 塞入 schema 對映的位置
        //    這邊因人而異，可依照您的 schema 格式做遞迴替換
        $finalFlex = $this->replaceSchemaWithInput($schema, $input);

        return $finalFlex;
    }

    /**
     * 範例：遞迴將 schema 中形如 {{field_name}} 的地方，以 $input['field_name'] 替換
     */
    private function replaceSchemaWithInput(array $schema, array $input): array
    {
        array_walk_recursive($schema, function (&$value) use ($input) {
            if (is_string($value) && preg_match('/\{\{(\w+)\}\}/', $value, $matches)) {
                $field = $matches[1];
                if (isset($input[$field])) {
                    $value = $input[$field];
                } else {
                    $value = '';
                }
            }
        });
        return $schema;
    }

    /**
     * 依據模板 ID 與表單欄位，生成單個氣泡的 Flex JSON 結構
     * @param int $templateId
     * @param array $input 整個表單輸入陣列
     * @return array 氣泡的 Flex JSON 陣列
     */
    public function buildBubbleJson(int $templateId, array $input): array
    {
        // 1. 取出對應模板的 schema
        $template = CardTemplates::findOrFail($templateId);
        // 將 JSON 字串解析為陣列
        $schema = json_decode($template->template_schema, true);

        if (!is_array($schema)) {
            throw new \Exception('模板結構無效，無法解析為陣列');
        }

        // 2. 處理 schema 中的變數替換
        $this->processJsonVariables($schema, $input);

        return $schema;
    }

    /**
     * 遞迴處理 JSON 中的變數替換
     * @param array &$json 要處理的 JSON 陣列 (參照)
     * @param array $input 表單輸入資料
     */
    private function processJsonVariables(&$json, $input)
    {
        if (!is_array($json)) {
            return;
        }

        foreach ($json as $key => &$value) {
            if (is_array($value)) {
                $this->processJsonVariables($value, $input);
            } elseif (is_string($value)) {
                // 替換 /{/{變數名/}/} 格式的字串
                $pattern = '/\{\{([^}]+)\}\}/';
                if (preg_match_all($pattern, $value, $matches)) {
                    foreach ($matches[1] as $index => $varName) {
                        $varName = trim($varName);
                        $placeholder = $matches[0][$index];

                        if (isset($input[$varName])) {
                            // 如果是圖片，使用完整 URL
                            if (strpos($varName, 'image') !== false && strpos($input[$varName], '/') !== false) {
                                $imageUrl = url('uploads/' . $input[$varName]);
                                $value = str_replace($placeholder, $imageUrl, $value);
                            } else {
                                $value = str_replace($placeholder, $input[$varName], $value);
                            }
                        }
                    }
                }
            }
        }
    }
}
