# 弱點掃描快速檢查清單

**檢查日期**: ___________  
**檢查人員**: ___________  

---

## ✅ 安全設置檢查

- [x] **CSP 已啟用正式模式** (`SecurityHeaders.php` Line 47)
- [x] **Nonce 機制已實作** (`@cspNonce` 和 `csp_nonce()`)
- [x] **Cookie 安全設定** (Secure, HttpOnly, SameSite)
- [x] **CORS 白名單** (僅 LINE LIFF)
- [x] **HTTPS 強制導向** (.htaccess 301)
- [x] **安全標頭完整** (X-Frame-Options, X-Content-Type-Options 等)
- [ ] **HSTS 延長時間** (目前 300 秒 → 改為 31536000)
- [ ] **jQuery UI 升級** (1.12.1 → 1.13.3)

---

## 🎯 三項中等風險功能測試

### 1. script-src 移除 'unsafe-eval' 測試

**後台功能**:
- [ ] 名片列表 - DataTables 排序/搜尋/分頁
- [ ] 卡片管理 - jQuery UI 拖曳排序
- [ ] 子帳號管理 - Select2 下拉選單
- [ ] 子帳號管理 - 日期選擇器

**前台功能**:
- [ ] 首頁 - Swiper 輪播
- [ ] 首頁 - Slick Carousel
- [ ] 分享頁 - Flex Message 渲染
- [ ] LIFF 頁 - LIFF SDK 初始化

**檢查方法**: 開啟瀏覽器 Console,操作功能,確認無 CSP 違規錯誤


### 2. script-src 移除 'unsafe-inline' 測試

**內聯腳本功能**:
- [ ] 後台登出功能
- [ ] 前台 Swiper 初始化
- [ ] 操作導覽 (Shepherd.js)
- [ ] LIFF 分享功能
- [ ] 複製連結功能

**檢查方法**: 測試各功能是否正常執行


### 3. style-src 移除 'unsafe-inline' 測試

**頁面樣式**:
- [ ] 首頁版面配置正常
- [ ] 分享頁樣式正確
- [ ] LIFF 頁樣式正確
- [ ] 後台 Shepherd.js 導覽樣式正常
- [ ] 所有自訂樣式有套用

**檢查方法**: 視覺檢查排版是否正常

---

## 📋 完整功能測試清單

### 後台管理 (`/admin`)

#### 名片管理
- [ ] `/admin/business-cards` - 列表顯示
- [ ] `/admin/business-cards` - 排序功能
- [ ] `/admin/business-cards` - 搜尋功能
- [ ] `/admin/business-cards` - 分頁功能
- [ ] `/admin/business-cards/create` - 新增名片
- [ ] `/admin/business-cards/{id}/edit` - 編輯名片
- [ ] `/admin/business-cards/{id}` - 查看名片
- [ ] 刪除名片功能
- [ ] 操作導覽 (Shepherd.js)

#### 卡片管理
- [ ] `/admin/business-cards/{id}/bubbles` - 卡片列表
- [ ] 拖曳排序功能 (jQuery UI)
- [ ] Flex Message 預覽
- [ ] 新增卡片
- [ ] 編輯卡片
- [ ] 刪除卡片
- [ ] 顏色選擇器 (Pickr)

#### 子帳號管理
- [ ] `/admin/sub-users` - 子帳號列表
- [ ] 新增子帳號
- [ ] 編輯子帳號
- [ ] 到期日選擇器
- [ ] 刪除子帳號

#### 其他後台功能
- [ ] 個人資料編輯
- [ ] 登出功能
- [ ] 側邊欄導航
- [ ] 快取清除頁面

### 前台頁面

#### 公開頁面
- [ ] `/` - 首頁
- [ ] `/` - Swiper 輪播
- [ ] `/` - Slick Carousel
- [ ] `/` - AOS 動畫
- [ ] `/` - 返回頂部按鈕
- [ ] `/features` - 功能介紹
- [ ] `/cases` - 案例展示
- [ ] `/learning-center` - 學習中心
- [ ] `/application` - 申請頁面

#### 分享功能
- [ ] `/share/{uuid}` - 分享頁載入
- [ ] `/share/{uuid}` - Flex Message 渲染
- [ ] `/share/{uuid}` - 複製連結功能
- [ ] `/share/{uuid}` - LINE 分享按鈕
- [ ] `/share/{uuid}` - QR Code 顯示
- [ ] `/share/{uuid}` - 點閱數統計

#### LIFF 整合
- [ ] `/liff/{uuid}` - LIFF 初始化
- [ ] `/liff/{uuid}` - 卡片渲染
- [ ] `/liff/{uuid}` - 分享功能
- [ ] `/liff/{uuid}` - 在 LINE 中開啟

### 認證功能
- [ ] `/login` - 登入
- [ ] `/register` - 註冊
- [ ] `/password/reset` - 重設密碼

---

## 🐛 錯誤記錄

如有 CSP 違規錯誤,請記錄如下:

### 錯誤 1
- **頁面**: ___________
- **錯誤訊息**: ___________
- **違規類型**: [ ] eval [ ] inline-script [ ] inline-style [ ] 其他
- **影響功能**: ___________
- **處理方式**: ___________

### 錯誤 2
- **頁面**: ___________
- **錯誤訊息**: ___________
- **違規類型**: [ ] eval [ ] inline-script [ ] inline-style [ ] 其他
- **影響功能**: ___________
- **處理方式**: ___________

---

## 📊 測試結果摘要

- **測試項目總數**: _____ 項
- **通過項目**: _____ 項
- **失敗項目**: _____ 項
- **發現 CSP 違規**: _____ 處
- **發現功能異常**: _____ 處

---

## ✅ 結論

- [ ] **所有功能正常運作**
- [ ] **無 CSP 違規錯誤**
- [ ] **畫面顯示正常**
- [ ] **可以將 HSTS 延長至 1 年**
- [ ] **可以進行生產環境部署**

或

- [ ] **發現問題需修正** (請參考錯誤記錄)

---

**測試完成日期**: ___________  
**簽核**: ___________
