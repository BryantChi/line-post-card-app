<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI;


class AiContentGeneratorService
{
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        // 從環境變數加載 AI API 金鑰
        $this->apiKey = config('services.openai.key');
        if (!$this->apiKey) {
            Log::error('OpenAI API key not configured.');
            // 可以選擇拋出異常或讓服務在沒有金鑰的情況下以受限模式運行
            // throw new \Exception('OpenAI API key not configured.');
        } else {
            $this->client = OpenAI::client($this->apiKey);
        }
    }

    /**
     * 根據標題和副標題生成名片描述。
     *
     * @param string $title
     * @param string|null $subtitle
     * @return string|null
     */
    public function generateCardDescription(string $title, ?string $subtitle): ?string
    {
        if (!$this->client) {
            Log::warning('OpenAI client not initialized due to missing API key.');
            return "AI 服務未配置，無法生成內容。請檢查 API 金鑰設定。"; // 或返回 null
        }

        // 建立提示 (Prompt)
        $prompt = "為一張AI數位名片生成一段吸引人的簡介。\n";
        $prompt .= "名片標題：「{$title}」\n";
        if ($subtitle) {
            $prompt .= "名片副標題：「{$subtitle}」\n";
        }
        $prompt .= "簡介內容應簡潔、專業（約50-100字），並突出其核心價值。請直接提供簡介文字，不要包含任何額外的開頭或結尾語句。";

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o', // 或其他適合的模型，例如 'gpt-4o'
                'messages' => [
                    ['role' => 'system', 'content' => '你是一位專業的文案撰寫員，擅長為AI數位名片撰寫簡潔有力的介紹。'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 150, // 限制生成長度
                'temperature' => 0.7, // 控制創意程度 (0.0 - 2.0)
            ]);

            if (isset($response->choices[0]->message->content)) {
                return trim($response->choices[0]->message->content);
            }

            Log::error('OpenAI content generation failed: No content in response', ['response' => $response->toArray()]);
            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI API call error: ' . $e->getMessage(), [
                'title' => $title,
                'subtitle' => $subtitle,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
