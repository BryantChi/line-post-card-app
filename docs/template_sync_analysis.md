# 名片模板更新同步問題分析報告

## 一、現況分析

### 1.1 資料結構關係

**三層架構：**
```
CardTemplate (卡片模板)
    ↓ template_id
CardBubble (氣泡卡片) 
    ↓ card_id
BusinessCard (名片)
```

**關鍵欄位：**
- `card_templates.template_schema` - 模板的 JSON 結構定義
- `card_templates.editable_fields` - 可編輯欄位設定
- `card_bubbles.template_id` - 指向使用的模板 (外鍵)
- `card_bubbles.bubble_data` - 用戶填入的欄位資料
- `card_bubbles.json_content` - 已生成的 Flex JSON (快取)
- `business_cards.flex_json` - 組合所有 bubbles 的最終 JSON

### 1.2 目前的運作流程

**建立/編輯氣泡時：**
1. 用戶選擇模板 (`template_id`)
2. 填入模板定義的可編輯欄位 → 存入 `bubble_data`
3. `CustomFlexMessageBuilder::buildBubbleJson()` 將模板 schema + bubble_data 合併
4. 生成的 JSON 存入 `json_content` (快照)
5. `BusinessCard::updateFlexJson()` 組合所有 bubbles 成最終 `flex_json`

**問題核心：**
- `json_content` 是**靜態快照**，不會隨模板更新而變動
- 模板更新後，已建立的 bubbles 仍使用舊版 `json_content`

---

## 二、問題影響範圍

### 2.1 受影響的場景

1. ✅ **新建的 bubble** - 會使用最新模板，無問題
2. ❌ **既有的 bubble** - 使用舊版快照，不會同步更新
3. ❌ **顯示效果** - 用戶看到的卡片仍是舊樣式
4. ❌ **一致性** - 同模板的卡片可能呈現不同樣式

### 2.2 可能的使用情境

**情境 A：修正模板錯誤**
- 模板有錯字或設計問題
- 更新模板後，希望所有使用該模板的卡片立即生效

**情境 B：模板功能升級**
- 新增欄位或調整佈局
- 希望既有卡片也能享有新功能

**情境 C：品牌統一更新**
- 公司 Logo、色彩改版
- 需要所有卡片同步更新

---

## 三、解決方案規劃

### 方案 A：即時重建模式 (Real-time Rebuild)

**原理：**
不儲存 `json_content` 快照，每次顯示時即時生成

**優點：**
✅ 模板更新立即生效
✅ 資料一致性最高
✅ 不需額外同步機制

**缺點：**
❌ 每次查看都要重新生成，效能較差
❌ 模板被刪除或改壞會影響所有卡片
❌ 無法保留歷史版本

**實作要點：**
- 移除 `json_content` 欄位儲存
- 在 `BusinessCard::updateFlexJson()` 中即時呼叫 builder
- 或使用 Accessor 動態生成

**適用情境：**
- 卡片數量不多 (< 1000)
- 模板變更頻繁
- 重視即時性

---

### 方案 B：版本控制模式 (Version Control)

**原理：**
模板加入版本號，bubble 記錄使用的版本

**優點：**
✅ 可追蹤歷史變更
✅ 支援回溯特定版本
✅ 不影響既有卡片

**缺點：**
❌ 資料庫結構需大幅調整
❌ 實作複雜度高
❌ 仍需主動更新機制

**資料表調整：**
```sql
-- card_templates 新增
ALTER TABLE card_templates ADD version INT DEFAULT 1;
ALTER TABLE card_templates ADD parent_version_id INT NULL;

-- card_bubbles 新增
ALTER TABLE card_bubbles ADD template_version INT;
```

**適用情境：**
- 需要嚴格的變更追蹤
- 多人協作編輯模板
- 監管要求需保留歷史

---

### 方案 C：手動同步模式 (Manual Sync)

**原理：**
模板更新時，提供「同步到所有卡片」功能

**優點：**
✅ 實作簡單
✅ 用戶可控制是否更新
✅ 保留舊版快照

**缺點：**
❌ 需要手動操作
❌ 可能遺漏更新
❌ 批量更新時效能問題

**實作要點：**
1. 模板編輯頁面增加「同步」按鈕
2. 後端批量重建使用該模板的所有 bubbles
3. 可選：提供預覽比對功能

**程式碼架構：**
```php
// CardTemplatesController.php
public function syncToCards($templateId)
{
    $template = CardTemplate::findOrFail($templateId);
    $bubbles = CardBubble::where('template_id', $templateId)->get();
    
    foreach ($bubbles as $bubble) {
        $jsonContent = $this->flexBuilder->buildBubbleJson(
            $templateId,
            array_merge([
                'title' => $bubble->title,
                'subtitle' => $bubble->subtitle,
                // ...
            ], $bubble->bubble_data ?? [])
        );
        
        $bubble->json_content = $jsonContent;
        $bubble->save();
        
        // 更新主卡片
        $bubble->businessCard->updateFlexJson();
    }
    
    Flash::success("已同步 {$bubbles->count()} 張卡片");
}
```

**適用情境：**
- 模板更新不頻繁
- 需要人工審核變更
- 希望保持靈活性

---

### 方案 D：自動同步 + 佇列模式 (Auto Sync with Queue)

**原理：**
模板更新時自動觸發，使用佇列處理大量更新

**優點：**
✅ 自動化，無需人工介入
✅ 非同步處理，不影響操作體驗
✅ 可處理大量資料

**缺點：**
❌ 需要 Queue 系統
❌ 同步有延遲
❌ 需要通知機制

**實作要點：**

1. **Observer 監聽模板更新**
```php
// CardTemplateObserver.php
public function updated(CardTemplate $template)
{
    if ($template->isDirty('template_schema')) {
        SyncTemplateToCards::dispatch($template);
    }
}
```

2. **Queue Job 處理同步**
```php
// Jobs/SyncTemplateToCards.php
public function handle()
{
    $bubbles = CardBubble::where('template_id', $this->template->id)
        ->with('businessCard')
        ->chunk(100, function($bubbles) {
            foreach ($bubbles as $bubble) {
                $this->rebuildBubble($bubble);
            }
        });
}
```

3. **新增同步狀態追蹤**
```sql
ALTER TABLE card_bubbles ADD last_synced_at TIMESTAMP NULL;
ALTER TABLE card_bubbles ADD sync_status ENUM('pending', 'syncing', 'synced', 'failed');
```

**適用情境：**
- 卡片數量龐大 (> 1000)
- 需要自動化流程
- 有 Queue 系統支援

---

### 方案 E：混合模式 (Hybrid)

**原理：**
結合快取 + 智慧重建

**策略：**
1. 正常情況使用快照 (效能)
2. 偵測模板更新時標記需重建
3. 首次訪問時重建並快取

**優點：**
✅ 平衡效能與一致性
✅ 不需立即處理大量更新
✅ 按需重建，節省資源

**缺點：**
❌ 邏輯較複雜
❌ 仍有短暫不一致期

**資料表調整：**
```sql
ALTER TABLE card_bubbles ADD template_updated_at TIMESTAMP NULL;
ALTER TABLE card_bubbles ADD needs_rebuild BOOLEAN DEFAULT 0;
```

**實作邏輯：**
```php
// CardTemplate 更新時
public function updated(CardTemplate $template)
{
    CardBubble::where('template_id', $template->id)
        ->update([
            'needs_rebuild' => true,
            'template_updated_at' => $template->updated_at
        ]);
}

// BusinessCard 顯示時
public function getFlexJsonAttribute($value)
{
    if ($this->bubbles()->where('needs_rebuild', true)->exists()) {
        $this->rebuildAllBubbles();
    }
    return $value;
}
```

---

## 四、建議方案

### 推薦：**方案 C (手動同步) + 方案 E (混合模式) 的組合**

**階段一：快速實作 (方案 C)**
1. 新增「同步到所有卡片」按鈕
2. 提供單張卡片的「重新生成」功能
3. 顯示受影響卡片數量

**階段二：優化體驗 (方案 E)**
1. 加入自動標記機制
2. 前端提示「模板已更新，點擊重建」
3. 記錄同步狀態

**階段三：進階功能 (選配)**
- 預覽比對功能
- 批次選擇性更新
- 版本歷史查詢

---

## 五、實作檢查清單

### 5.1 資料庫變更
- [ ] `card_bubbles` 新增 `needs_rebuild` 欄位
- [ ] `card_bubbles` 新增 `template_updated_at` 欄位
- [ ] `card_bubbles` 新增 `last_synced_at` 欄位
- [ ] (選用) 新增 index 提升查詢效能

### 5.2 Model 調整
- [ ] `CardTemplate` 加入同步相關方法
- [ ] `CardBubble` 加入重建邏輯
- [ ] (選用) Observer 自動標記

### 5.3 Controller 功能
- [ ] `CardTemplatesController::sync()` - 同步功能
- [ ] `CardTemplatesController::preview()` - 預覽比對
- [ ] `CardBubblesController::rebuild()` - 單張重建

### 5.4 前端介面
- [ ] 模板編輯頁：同步按鈕 + 影響數量
- [ ] 氣泡列表頁：重建按鈕 + 狀態標示
- [ ] (選用) 比對預覽彈窗

### 5.5 測試項目
- [ ] 模板更新 → 標記正確
- [ ] 同步功能 → JSON 正確重建
- [ ] 效能測試 → 大量卡片處理
- [ ] 錯誤處理 → 模板損壞時的回退

---

## 六、風險評估

### 6.1 技術風險

| 風險 | 影響 | 機率 | 應對措施 |
|------|------|------|----------|
| 批量更新效能問題 | 高 | 中 | 使用 chunk 分批、Queue 非同步 |
| 模板格式錯誤破壞卡片 | 高 | 低 | 更新前驗證、保留舊版快照 |
| 同步過程中斷 | 中 | 低 | 交易處理、狀態記錄 |
| 資料不一致 | 中 | 中 | 定期檢查腳本、自動修復 |

### 6.2 業務風險

| 風險 | 影響 | 機率 | 應對措施 |
|------|------|------|----------|
| 用戶不知道需要同步 | 中 | 高 | 明確提示、自動標記 |
| 更新破壞既有設計 | 高 | 中 | 提供預覽、允許回退 |
| 頻繁更新造成混亂 | 中 | 低 | 版本管理、變更記錄 |

---

## 七、時程估算

### 方案 C (手動同步)
- 後端開發：2-3 天
- 前端介面：1-2 天
- 測試調整：1 天
- **總計：4-6 天**

### 方案 E (混合模式)
- 在方案 C 基礎上
- 自動標記機制：1 天
- 智慧重建邏輯：1-2 天
- **總計：6-9 天**

### 方案 D (佇列自動)
- Queue 設定：0.5 天
- Job 開發：1-2 天
- Observer：1 天
- 狀態追蹤：1 天
- **總計：3.5-5.5 天**

---

## 八、結論

**立即建議：**
採用**方案 C (手動同步)**作為第一階段實作，原因：
1. 實作快速 (4-6 天)
2. 風險可控
3. 符合目前需求
4. 可逐步演進

**後續規劃：**
觀察使用頻率後，評估是否升級至方案 E 或 D
