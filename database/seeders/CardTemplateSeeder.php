<?php

namespace Database\Seeders;

use App\Models\Admin\CardTemplates;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CardTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 範例：一個最基本的 Flex Bubble 範本，內含欄位 {{title}}、{{subtitle}}、{{profile_image}}
        $template1 = [
            "type" => "bubble",
            "hero" => [
                "type" => "image",
                "url" => "{{profile_image}}",
                "size" => "full",
                "aspectRatio" => "1:1",
                "aspectMode" => "cover"
            ],
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "{{title}}",
                        "weight" => "bold",
                        "size" => "xl"
                    ],
                    [
                        "type" => "text",
                        "text" => "{{subtitle}}",
                        "size" => "sm",
                        "color" => "#666666",
                        "wrap" => true
                    ]
                ]
            ],
            "footer" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "style" => "link",
                        "height" => "sm",
                        "action" => [
                            "type" => "uri",
                            "label" => "立即前往",
                            "uri" => "{{link_url}}"
                        ]
                    ]
                ],
                "flex" => 0
            ]
        ];

        CardTemplates::create([
            'name' => '基礎一欄式',
            'description' => '單張輪廓+標題、副標題',
            'preview_image' => 'templates/basic1.png', // 事先準備好縮圖放 public/img
            'template_schema' => json_encode($template1),
        ]);

        // 再加幾個不同結構的範例...
    }
}
