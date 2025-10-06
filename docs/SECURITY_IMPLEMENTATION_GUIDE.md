# 安全性弱點修正實作指南

此文件說明已完成的應用層安全修正,以及後續需執行的步驟。

---

## ✅ 已完成的修正項目

### 1. SecurityHeaders Middleware (app/Http/Middleware/SecurityHeaders.php)

**功能**: 為所有 HTTP 回應自動加入安全標頭

**已實作標頭**:
- ✅ `Content-Security-Policy-Report-Only`: CSP 測試模式
- ✅ `X-Frame-Options: DENY`: 防止點擊劫持
- ✅ `X-Content-Type-Options: nosniff`: 防止 MIME 嗅探
- ✅ `Referrer-Policy: strict-origin-when-cross-origin`: 控制 Referrer 資訊
- ✅ `Permissions-Policy`: 禁用不必要的瀏覽器功能
- ✅ `X-XSS-Protection: 1; mode=block`: 舊瀏覽器 XSS 防護

**CSP 白名單**:
- 預設: `'self'` (同源)
- Script/Style: `cdn.jsdelivr.net`, `cdnjs.cloudflare.com`, `fonts.googleapis.com`
- 連線: `api.line.me`, `api.openai.com`
- Frame: `liff.line.me`

**重要**: Middleware 已註冊在 `app/Http/Kernel.php:24`

---

### 2. Session Cookie 安全強化 (config/session.php)

**修改項目**:
```php
'secure' => env('SESSION_SECURE_COOKIE', true),  // 強制 HTTPS
'http_only' => true,                              // 防止 JS 存取
'same_site' => 'lax',                             // CSRF 防護
```

**環境變數設定** (.env):
```env
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
```

---

### 3. CORS 精準白名單 (config/cors.php)

**修改項目**:
```php
'allowed_origins' => [
    'https://liff.line.me',
    'https://access.line.me',
],
'supports_credentials' => true,
'max_age' => 600,
```

**彈性配置** (.env):
```env
# 可選:透過環境變數覆蓋
CORS_ALLOWED_ORIGINS=https://liff.line.me,https://access.line.me
```

---

### 4. HTTPS 強制導轉 (public/.htaccess)

**已新增規則**:
```apache
# 強制 HTTP → HTTPS (301)
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# HSTS 標頭 (測試模式:300秒)
Header always set Strict-Transport-Security "max-age=300; includeSubDomains" env=HTTPS

# 隱藏 X-Powered-By
Header always unset X-Powered-By
```

**漸進式 HSTS 部署**:
1. **階段 1** (測試 1 週): `max-age=300`
2. **階段 2** (上線): `max-age=31536000` (1 年)

---

### 5. Vite 配置 - SRI 支援 (vite.config.js)

**修改項目**:
```javascript
build: {
    rollupOptions: {
        output: {
            entryFileNames: 'assets/[name].[hash].js',
            chunkFileNames: 'assets/[name].[hash].js',
            assetFileNames: 'assets/[name].[hash].[ext]'
        }
    },
    manifest: true,
}
```

**用途**:
- 產生帶 hash 的檔名
- Laravel Vite helper 可自動加入 `integrity` 屬性

---

### 6. 清理開發註解 (resources/views/layouts_main/master.blade.php)

**已移除**:
- ✅ 註解掉的 HTML 片段
- ✅ 未使用的 CSS 引用註解
- ✅ 註解掉的 JavaScript 配置

---

## 🔧 後續需執行的步驟

### 步驟 1: 測試 CSP (Content Security Policy)

**目的**: 確保 CSP 不影響前端功能

**執行**:
1. 開啟瀏覽器開發者工具 (F12)
2. 訪問網站所有主要頁面
3. 檢查 Console 是否有 CSP 違規錯誤:
   ```
   Content Security Policy: 阻止載入 [URL] ...
   ```

**如果發現違規**:
- 記錄被阻擋的資源 URL
- 更新 `app/Http/Middleware/SecurityHeaders.php` 的 CSP 白名單

**正式啟用 CSP**:
```php
// 將 SecurityHeaders.php 中的:
$response->headers->set('Content-Security-Policy-Report-Only', $csp);

// 改為:
$response->headers->set('Content-Security-Policy', $csp);
```

---

### 步驟 2: jQuery UI 升級或移除

**問題**: 目前使用 jQuery UI v1.12.1 (2018),有已知漏洞

**選項 A - 升級到最新版本**:
```bash
# 下載最新版 jQuery UI (1.13.x)
cd public/assets/js
wget https://code.jquery.com/ui/1.13.3/jquery-ui.min.js
mv jquery-ui.min.js jquery-ui-1.13.3.min.js

# 更新 Blade 模板引用
# resources/views/layouts_main/master.blade.php
<script src="{{ asset('assets/js/jquery-ui-1.13.3.min.js') }}"></script>

# 同步更新 CSS
cd public/assets/css
wget https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.min.css
mv jquery-ui.min.css jquery-ui-1.13.3.min.css
```

**選項 B - 移除並改用現代替代方案** (建議):
```bash
# 檢查專案中實際使用 jQuery UI 的功能
grep -r "\.slider\|\.datepicker\|\.draggable\|\.sortable" resources/views/
grep -r "jquery-ui" public/assets/js/

# 如果僅使用 Slider,可改用原生 HTML5 <input type="range">
# 如果使用 Datepicker,可改用 bootstrap-datepicker (已引入)
```

**檢查依賴**:
```bash
# 搜尋使用 jQuery UI 的程式碼
grep -r "\.slider" resources/views/admin/card_bubbles/
```

**我們的建議**:
- 如果僅 `card_bubbles` 使用 slider → 升級至 1.13.3
- 如果功能可替代 → 改用現代 UI 框架 (Bootstrap, Alpine.js)

---

### 步驟 3: 更新環境變數 (.env)

**必要設定**:
```env
# 強制 HTTPS Session Cookie
SESSION_SECURE_COOKIE=true

# (選用) 自訂 CORS 白名單
CORS_ALLOWED_ORIGINS=https://liff.line.me,https://access.line.me

# (選用) 應用層 HSTS (如果主機商未設定)
# 在 SecurityHeaders.php 中可讀取此變數
```

---

### 步驟 4: 重建前端資源

**目的**: 產生帶 hash 的檔案,啟用 SRI

**執行**:
```bash
# 清除舊快取
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 重新編譯前端資源
npm run build

# 檢查產生的檔案
ls -la public/build/assets/
# 應看到類似: app.a1b2c3d4.js, app.e5f6g7h8.css
```

**Blade 模板使用**:
```blade
{{-- 使用 @vite helper,會自動加入 integrity 屬性 --}}
@vite(['resources/sass/app.scss', 'resources/js/app.js'])
```

---

### 步驟 5: 與主機商協調伺服器層級設定

**交付文件**: `docs/SECURITY_SERVER_CONFIG.md`

**主要項目**:
1. ✅ SSL 憑證續期 (Let's Encrypt + Certbot)
2. ✅ OCSP Stapling
3. ✅ Apache ServerTokens Prod
4. ✅ 修正 No-SNI 預設 vhost

**協調流程**:
1. 將 `SECURITY_SERVER_CONFIG.md` 提供給主機商
2. 確認主機商環境 (Apache 版本、模組支援)
3. 約定 Staging 環境測試時間
4. 正式環境部署 (建議離峰時段)

---

## ✅ 驗收標準

### ZAP by Checkmarx 掃描結果

**預期改善**:
- ✅ 無 CSP 缺失警告
- ✅ X-Frame-Options 已設定
- ✅ Cookie Secure/HttpOnly 屬性完整
- ✅ X-Content-Type-Options 已設定
- ✅ Referrer-Policy 已設定
- ✅ 無跨網域資源策略錯誤

**手動驗證**:
```bash
# 檢查安全標頭
curl -Is https://business.cheni.tw | grep -E "(Content-Security-Policy|X-Frame-Options|X-Content-Type-Options|Referrer-Policy)"

# 檢查 Cookie 屬性
# 瀏覽器開發者工具 > Application > Cookies
# 應看到: Secure, HttpOnly, SameSite=Lax
```

---

## 📊 風險等級對照

| 修正項目 | 原風險等級 | 修正後 | 檔案位置 |
|---------|-----------|--------|---------|
| CSP 缺失 | 中 | ✅ 低 | SecurityHeaders.php |
| 點擊劫持 | 中 | ✅ 低 | SecurityHeaders.php |
| Cookie 屬性 | 低 | ✅ 無 | session.php |
| MIME 嗅探 | 低 | ✅ 無 | SecurityHeaders.php |
| jQuery UI 漏洞 | 中 | ⚠️ 待處理 | (需升級) |
| CORS 錯誤 | 低-中 | ✅ 低 | cors.php |

---

## 🚨 重要提醒

### 測試環境驗證
**務必先在 Staging 環境測試以下功能**:
- [ ] 使用者登入/登出
- [ ] LINE LIFF 功能
- [ ] 名片編輯 (特別是 slider 功能)
- [ ] 圖片上傳
- [ ] 第三方 API 呼叫 (LINE, OpenAI)

### CSP 漸進式啟用
1. **第 1 週**: `Content-Security-Policy-Report-Only` (監控模式)
2. **第 2 週**: 修正所有違規後,改為 `Content-Security-Policy`

### HSTS 注意事項
- **測試期間**: `max-age=300` (5 分鐘)
- **正式上線**: `max-age=31536000` (1 年)
- ⚠️ **一旦啟用 HSTS,瀏覽器會強制 HTTPS,無法輕易回退**

---

## 📞 問題排查

### 問題 1: 前端資源無法載入 (CSP 阻擋)

**症狀**: Console 顯示 CSP 違規錯誤

**解決**:
```php
// 在 SecurityHeaders.php 中加入允許的來源
"script-src 'self' https://新的CDN網址.com",
```

### 問題 2: Cookie 無法設定

**症狀**: 使用者無法登入或 Session 遺失

**檢查**:
1. 確認網站已全站 HTTPS
2. `.env` 設定 `SESSION_SECURE_COOKIE=true`
3. 檢查瀏覽器是否阻擋 Cookie

**臨時解決** (不建議):
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', false), // 改為 false
```

### 問題 3: CORS 錯誤

**症狀**: 前端呼叫 API 時顯示 CORS 錯誤

**檢查**:
```bash
# 確認 CORS 白名單是否包含前端網址
# config/cors.php
'allowed_origins' => [
    'https://liff.line.me',  // 確認此網址是否正確
],
```

---

## 📝 變更記錄

| 日期 | 版本 | 修改內容 |
|------|------|---------|
| 2025-10-06 | 1.0 | 初始版本,完成應用層安全修正 |

---

**下一步**: 請按照「後續需執行的步驟」進行測試與部署,並定期回報進度。
