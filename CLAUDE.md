# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 專案概述

這是一個基於 Laravel 的 LINE AI數位名片應用程式,允許用戶透過 LINE 訊息平台建立、管理和分享 AI 驅動的數位名片。系統包含多角色用戶管理 (超級管理員、主帳號、子帳號) 並整合 LINE Bot SDK 和 OpenAI 服務。

## 開發指令

### 後端 (Laravel/PHP)
- `php artisan serve` - 啟動開發伺服器
- `php artisan migrate` - 執行資料庫遷移
- `php artisan cache:clear` - 清除應用程式快取
- `php artisan route:clear` - 清除路由快取
- `php artisan config:clear` - 清除設定快取
- `php artisan view:clear` - 清除視圖快取
- `composer install` - 安裝 PHP 依賴套件
- `composer dump-autoload` - 重新產生 autoloader (新增檔案後必須執行)

### 前端 (Vite/JS)
- `npm run dev` - 啟動 Vite 開發伺服器 (含熱重載)
- `npm run build` - 建置生產環境資源
- `npm install` - 安裝 Node.js 依賴套件

### 測試
- `php artisan test` - 執行 PHPUnit 測試
- `vendor/bin/phpunit` - 執行測試的替代方式

### 程式碼品質
- `vendor/bin/pint` - 執行 Laravel Pint 程式碼格式化工具 (如有安裝)

## 架構概覽

### 用戶角色系統
- **超級管理員 (Super Admin)**: 完整系統存取權限,管理所有用戶和 SEO 設定
- **主帳號 (Main Users)**: 可建立/管理子帳號並查看其所有名片
- **子帳號 (Sub Users)**: 僅能管理自己的名片和個人資料

### 核心元件

#### Models
- `BusinessCard`: 主要名片實體,包含 AI 生成內容和統計數據
- `BusinessCardStatistic`: 每日統計數據 (點閱數/分享數)
- `CardTemplate`: 可重複使用的名片模板
- `CardBubble`: 名片內的互動式卡片元素
- `User`: 多角色用戶系統,支援階層關係與署名繼承機制
- `UserLoginLog`: 用戶登入記錄追蹤

#### Controllers 結構
- `Admin/*`: 後台管理控制器
  - `AdminAccountController`: 超級管理員帳號管理
  - `BusinessCardsController`: 名片 CRUD + 報表下載功能
  - `CardBubblesController`: 氣泡卡片管理
  - `AiController`: AI 內容生成
  - `SubUserProfileController`: 個人資料管理
  - `UserLoginReportController`: 登入紀錄報表
  - `LoginLogsController`: 登入紀錄管理
- `SuperAdmin/*`: 超級管理員專屬控制器
  - `MainUserController`: 主帳號管理
- `LineCardController`: LINE Bot 整合與 LIFF 應用處理
- `SubUserController`: 子帳號管理
- 前台控制器: `IndexController`, `FeaturesController`, `ApplicationController`, `CasesController`, `LearningCenterController`

#### 主要功能
- **AI 整合**: 使用 OpenAI API 生成內容 (`app/Http/Controllers/Admin/AiController.php`)
- **LINE Bot 整合**: 完整 LINE 訊息平台整合,支援 Flex Message
- **LIFF App**: LINE Front-end Framework 應用內體驗
- **多模板系統**: 彈性的名片模板管理
- **公開分享**: 基於 UUID 的公開名片分享系統
- **報表下載**: 支援本週/本月/自訂區間的點閱與分享統計報表 (Excel 格式)
- **署名管理**: 支援自訂署名功能,子帳號自動繼承父帳號署名
- **登入紀錄**: 完整的登入次數追蹤與登入記錄管理系統
- **配額管理**: 主帳號可為子帳號設定名片與氣泡卡片數量上限

### 路由組織
- `/admin/*`: 後台管理介面,具備角色權限控制
  - `/admin/adminUsers`: 超級管理員帳號管理 (僅超級管理員)
  - `/admin/main-users`: 主帳號管理 (僅超級管理員)
  - `/admin/sub-users`: 子帳號管理 (主帳號與超級管理員)
  - `/admin/business-cards`: 名片管理
  - `/admin/business-cards/{id}/bubbles`: 氣泡卡片管理
  - `/admin/business-cards/{id}/report/*`: 報表下載路由
  - `/admin/profile`: 個人資料管理
  - `/admin/login-logs`: 登入紀錄管理 (僅超級管理員)
- `/share/{uuid}`: 公開名片分享頁面
- `/liff/{uuid?}`: LINE LIFF 應用整合
- API 端點: `/api/cards/{uuid}/increment-share` - 分享計數 API

### 前端技術
- Vite 用於資源編譯
- AdminLTE 3.1.0 作為後台介面框架
- Bootstrap 4.6.0 提供樣式
- jQuery 處理互動功能

### 資料庫
- 建議使用 MySQL/MariaDB
- 包含完整的名片系統遷移檔案
- 支援用戶階層與角色權限
- `business_card_statistics` 表用於每日統計追蹤

### 關鍵整合套件
- **LINE Bot SDK**: 訊息與 LIFF 功能
- **OpenAI API**: AI 驅動的內容生成
- **Laravel Excel (maatwebsite/excel)**: Excel 報表匯出
- **Image Intervention**: 圖片處理與壓縮
- **DataTables**: 後台資料管理
- **HTML Purifier**: 內容清理與安全防護

## 開發注意事項

### 快取管理
- 應用程式包含網頁路由 (`/clear-cache`, `/migrate`) 用於快取清除和遷移
- 變更設定後務必清除快取
- 新增檔案後執行 `composer dump-autoload`

### 權限系統
- Middleware: `check.active`, `check.super.admin`, `check.main`
- 路由依權限層級組織於 `routes/web.php`
- 使用 `canBeViewedBy()` 和 `canBeEditedBy()` 方法檢查名片存取權限

### LINE 整合
- 需要設定 LIFF 環境才能使用 LINE 應用整合
- 名片會生成 LINE Flex Message JSON 用於豐富訊息展示
- 公開分享功能獨立於 LINE 驗證系統運作

### 報表系統
- 點閱記錄位置: `LineCardController::share()` (line 187)
- 分享記錄位置: `BusinessCardsController::incrementShareCountApi()` (line 338)
- 使用 `BusinessCardStatistic::recordView()` 和 `recordShare()` 記錄統計
- 報表服務: `app/Services/BusinessCardReportService.php`
- Excel 匯出: `app/Exports/BusinessCardReportExport.php` (雙工作表格式)

### 服務模式
- `CustomFlexMessageBuilder`: Flex Message 建構服務
- `BusinessCardReportService`: 報表生成服務,支援週報/月報/自訂區間

### 署名系統
- 超級管理員、主帳號和子帳號都可設定自訂署名 (最多 100 字元)
- 署名顯示格式: `Design by {署名內容}`,前綴 "Design by " 固定不變
- 繼承規則:
  - 用戶有設定署名時使用自己的署名
  - 子帳號未設定時自動繼承父帳號署名
  - 無任何設定時使用預設值 "誠翊資訊網路應用事業"
- 管理方式:
  - 超級管理員可透過 `/admin/adminUsers` 管理所有超級管理員署名
  - 超級管理員可透過 `/admin/main-users` 管理所有主帳號署名
  - 超級管理員可透過 `/admin/sub-users` 管理所有子帳號署名
  - 主帳號可透過 `/admin/sub-users` 管理自己的子帳號署名
  - 所有用戶可透過 `/admin/profile` 管理自己的署名
- 實作位置: `User::getSignature()` 方法處理署名邏輯與繼承

## 通用規則
- 使用繁體中文回答
- 新增類別後務必執行 `composer dump-autoload`
- 修改路由後建議執行 `php artisan route:clear`
