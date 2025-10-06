# GoDaddy cPanel 環境安全優化指南

此文件專門針對 **GoDaddy cPanel 共享主機環境** 提供安全性優化方案,考量到 cPanel 環境的權限限制。

---

## 🎯 GoDaddy cPanel 環境特性

### 優勢
- ✅ Let's Encrypt 免費 SSL 憑證(內建支援)
- ✅ Apache 已啟用 mod_rewrite, mod_headers
- ✅ 自動 HTTPS 導向(可透過介面設定)
- ✅ cPanel 介面友善

### 限制
- ❌ 無法直接修改 Apache 全域配置(`httpd.conf`, `ssl.conf`)
- ❌ 無法執行 `a2enmod` 等系統指令
- ❌ 某些進階功能需透過 cPanel 介面操作

---

## 📋 可在 cPanel 直接完成的設定

### 1. SSL/TLS 憑證管理

#### 1.1 啟用 Let's Encrypt 免費憑證

**操作路徑**: cPanel > Security > SSL/TLS Status

**步驟**:
1. 登入 cPanel
2. 進入 **SSL/TLS Status**
3. 勾選您的網域 `business.cheni.tw`
4. 點選 **Run AutoSSL** → 自動安裝 Let's Encrypt 憑證
5. 憑證會自動每 90 天續期

**驗收**:
- 檢查憑證狀態顯示 **Valid**
- 到期日期應為 3 個月後

---

#### 1.2 強制 HTTPS 導向

**方法 A - cPanel 介面設定** (推薦):

**路徑**: cPanel > Domains > Domains (或 SSL/TLS)

**步驟**:
1. 找到您的網域 `business.cheni.tw`
2. 開啟 **Force HTTPS Redirect** (強制 HTTPS 重新導向)
3. 儲存設定

**驗收**:
```bash
curl -I http://business.cheni.tw
# 預期: HTTP/1.1 301 Moved Permanently
# Location: https://business.cheni.tw/
```

**方法 B - .htaccess 手動設定** (已完成):

我們已在 `public/.htaccess` 中加入強制 HTTPS 規則:
```apache
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**注意**: 如果 cPanel 已啟用強制 HTTPS,可移除 `.htaccess` 中的重複規則,避免雙重導向。

---

### 2. HSTS (HTTP Strict Transport Security)

**問題**: cPanel 無法直接設定 HSTS

**解決方案**: 透過 `.htaccess` 設定 (已完成)

**已加入的設定** (`public/.htaccess`):
```apache
<IfModule mod_headers.c>
    # HSTS - 測試階段 (300 秒)
    Header always set Strict-Transport-Security "max-age=300; includeSubDomains" env=HTTPS
</IfModule>
```

**漸進式部署**:

**階段 1 (測試 1 週)**:
```apache
Header always set Strict-Transport-Security "max-age=300; includeSubDomains" env=HTTPS
```

**階段 2 (正式上線)**:
```apache
# 修改 .htaccess 第 30 行
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains" env=HTTPS
```

**驗收**:
```bash
curl -Is https://business.cheni.tw | grep -i strict
# 預期: Strict-Transport-Security: max-age=31536000; includeSubDomains
```

---

### 3. 隱藏伺服器版本資訊

**問題**: cPanel 環境無法修改 `ServerTokens` 全域設定

**解決方案**: 透過 `.htaccess` 移除部分資訊 (已完成)

**已加入的設定** (`public/.htaccess`):
```apache
<IfModule mod_headers.c>
    Header always unset X-Powered-By
    Header unset X-Powered-By
</IfModule>
```

**限制**:
- ❌ 無法完全隱藏 `Server: Apache` 標頭
- ✅ 可移除 `X-Powered-By: PHP/x.x.x`

**部分解決**: 聯絡 GoDaddy 技術支援,請求他們協助設定:
```apache
ServerSignature Off
ServerTokens Prod
```

---

### 4. PHP 版本與安全設定

#### 4.1 選擇 PHP 版本

**路徑**: cPanel > Software > Select PHP Version

**建議**:
- ✅ 選擇 **PHP 8.1** 或 **8.2** (專案需求 PHP ≥ 8.1)
- ❌ 避免使用 PHP 7.x (已停止安全更新)

---

#### 4.2 PHP 擴展檢查

**路徑**: cPanel > Software > Select PHP Version > Extensions

**必要擴展**:
- [x] `curl` (LINE Bot SDK)
- [x] `gd` (圖片處理)
- [x] `intl` (多語系)
- [x] `json`
- [x] `mbstring`
- [x] `mysqli` (資料庫)
- [x] `openssl` (加密)
- [x] `pdo`
- [x] `pdo_mysql`
- [x] `zip`

---

#### 4.3 PHP 設定調整

**路徑**: cPanel > Software > Select PHP Version > Options

**建議設定**:

| 參數 | 建議值 | 說明 |
|------|--------|------|
| `memory_limit` | `256M` | Laravel 記憶體需求 |
| `max_execution_time` | `300` | 長時間任務(OpenAI API) |
| `upload_max_filesize` | `20M` | 名片圖片上傳 |
| `post_max_size` | `25M` | 表單提交大小 |
| `display_errors` | `Off` | 正式環境隱藏錯誤 |
| `log_errors` | `On` | 記錄錯誤到檔案 |

---

### 5. 資料庫安全

#### 5.1 MySQL 資料庫設定

**路徑**: cPanel > Databases > MySQL Databases

**安全檢查**:
- [ ] 資料庫使用者名稱不是 `root` 或簡單名稱
- [ ] 密碼強度:至少 16 字元,混合大小寫+數字+符號
- [ ] 僅授予必要權限 (SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP)
- [ ] 移除 `FILE`, `SUPER` 等危險權限

**驗收**:
```bash
# 檢查 .env 中的資料庫設定
grep "DB_" .env
```

---

#### 5.2 啟用 phpMyAdmin 存取限制

**路徑**: cPanel > Databases > phpMyAdmin

**建議**:
- ✅ 使用完畢後立即登出
- ✅ 避免儲存 phpMyAdmin 密碼
- ✅ 定期修改資料庫密碼

---

### 6. 檔案權限設定

**路徑**: cPanel > Files > File Manager

**Laravel 專案權限建議**:

```bash
# 透過 cPanel File Manager 設定權限

# 一般檔案
檔案: 644 (rw-r--r--)
目錄: 755 (rwxr-xr-x)

# 需寫入的目錄
storage/          : 775 (rwxrwxr-x)
storage/logs/     : 775
storage/framework/: 775
bootstrap/cache/  : 775

# 公開目錄
public/           : 755
public/storage/   : 755 (如有)

# 敏感檔案 (僅所有者可讀)
.env              : 600 (rw-------)
```

**設定方式**:
1. File Manager → 選取檔案/目錄
2. 右鍵 → Change Permissions
3. 輸入數字權限值

---

### 7. Cron Jobs 設定

**路徑**: cPanel > Advanced > Cron Jobs

#### 7.1 Laravel 排程任務

**設定**:
```bash
# 時間: * * * * * (每分鐘執行)
# 指令:
cd /home/your_username/public_html/line-post-card-app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**路徑替換**:
- `your_username`: 您的 cPanel 使用者名稱
- `public_html/line-post-card-app`: 實際專案路徑

---

#### 7.2 SSL 憑證到期檢查 (選做)

**設定**:
```bash
# 時間: 0 8 * * * (每天早上 8:00)
# 指令:
/usr/bin/php /home/your_username/scripts/check-ssl-expiry.php
```

**腳本範例** (`scripts/check-ssl-expiry.php`):
```php
<?php
$domain = 'business.cheni.tw';
$cert = openssl_x509_parse(
    file_get_contents("ssl:///{$domain}:443")
);

$expiryDate = date('Y-m-d', $cert['validTo_time_t']);
$daysLeft = floor(($cert['validTo_time_t'] - time()) / 86400);

if ($daysLeft < 30) {
    // 發送告警 (Email/LINE Notify)
    mail('admin@example.com', 'SSL 憑證即將到期', "剩餘 {$daysLeft} 天");
}
```

---

## 🔧 需手動執行的優化項目

### 1. 確認 mod_headers 已啟用

**檢查方式**:
```bash
# 上傳 phpinfo.php 到 public/
<?php phpinfo(); ?>

# 訪問: https://business.cheni.tw/phpinfo.php
# 搜尋 "Loaded Modules" 中是否有 "mod_headers"
```

**如果未啟用**:
- 聯絡 GoDaddy 技術支援請求啟用
- 或透過 cPanel > Software > MultiPHP INI Editor → 無法直接啟用(需主機商)

---

### 2. OCSP Stapling

**問題**: cPanel 環境無法直接設定 OCSP Stapling

**解決方案**:
1. **方法 A**: 聯絡 GoDaddy 技術支援,詢問是否已在伺服器層級啟用
2. **方法 B**: 升級至 VPS/Dedicated Server (有完整權限)

**驗收** (檢查現況):
```bash
echo | openssl s_client -connect business.cheni.tw:443 -status 2>&1 | grep "OCSP Response Status"

# 如果顯示 "successful",表示已啟用
# 如果顯示 "No OCSP response received",表示未啟用
```

---

### 3. HTTP/2 支援

**檢查**:
```bash
curl -I --http2 https://business.cheni.tw
# 檢查是否包含: HTTP/2 200
```

**說明**:
- GoDaddy 大部分主機已預設支援 HTTP/2
- 如果不支援,需聯絡技術支援或升級方案

---

## 📝 GoDaddy cPanel 完整設定檢查清單

### 必做項目 (立即執行)

- [ ] 1. 啟用 Let's Encrypt SSL 憑證
- [ ] 2. 開啟 **Force HTTPS Redirect**
- [ ] 3. PHP 版本設為 8.1 或 8.2
- [ ] 4. 檢查必要的 PHP 擴展
- [ ] 5. 設定檔案權限 (storage, bootstrap/cache)
- [ ] 6. 確認 .env 檔案權限為 600
- [ ] 7. 資料庫密碼強度檢查
- [ ] 8. 設定 Laravel Cron Job

### 進階項目 (建議執行)

- [ ] 9. HSTS 從 300 秒調整為 31536000 秒
- [ ] 10. PHP 設定調整 (memory_limit, upload_max_filesize)
- [ ] 11. 移除測試用 phpinfo.php
- [ ] 12. 設定錯誤日誌監控
- [ ] 13. 定期備份資料庫 (cPanel Backup)

### 需聯絡 GoDaddy 技術支援項目

- [ ] 14. 確認 OCSP Stapling 是否已啟用
- [ ] 15. 請求設定 `ServerTokens Prod`
- [ ] 16. 確認 HTTP/2 支援狀態
- [ ] 17. 詢問是否支援 TLS 1.3

---

## 🚀 部署流程 (針對 cPanel 環境)

### 步驟 1: 上傳專案

**方法 A - Git 部署** (推薦):

```bash
# 1. cPanel > Files > Git Version Control
# 2. Clone Repository
#    Repository URL: [您的 Git Repo URL]
#    Repository Path: public_html/line-post-card-app

# 3. SSH 連線後執行 (如有 SSH 權限)
cd ~/public_html/line-post-card-app
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**方法 B - FTP 上傳**:

1. cPanel > Files > File Manager
2. 上傳整個專案資料夾
3. 確保 `.env` 檔案已正確設定

---

### 步驟 2: 設定網站根目錄

**路徑**: cPanel > Domains > Domains

**設定**:
1. 選擇您的網域 `business.cheni.tw`
2. **Document Root** 改為: `/home/your_username/public_html/line-post-card-app/public`
3. 儲存

**重要**: Laravel 專案的入口點是 `public/` 目錄,不是專案根目錄!

---

### 步驟 3: 設定 .env 檔案

**cPanel File Manager** 編輯 `.env`:

```env
APP_NAME="LINE名片系統"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://business.cheni.tw

# 資料庫設定 (從 cPanel MySQL 資訊複製)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_strong_password

# Session 安全設定
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120

# CORS (如需自訂)
CORS_ALLOWED_ORIGINS=https://liff.line.me,https://access.line.me

# LINE Bot 設定
LINE_CHANNEL_ACCESS_TOKEN=your_token
LINE_CHANNEL_SECRET=your_secret

# OpenAI 設定
OPENAI_API_KEY=your_key
```

---

### 步驟 4: 執行 Laravel 初始化

**透過 cPanel Terminal** (如果可用):

```bash
cd ~/public_html/line-post-card-app

# 安裝依賴
composer install --no-dev

# 產生應用金鑰
php artisan key:generate

# 執行資料庫遷移
php artisan migrate --force

# 快取配置 (生產環境優化)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 建立 storage 符號連結
php artisan storage:link

# 設定權限
chmod -R 775 storage bootstrap/cache
```

**如果沒有 Terminal 權限**:
- 透過 cPanel File Manager 手動設定權限
- 或使用 FTP 軟體 (FileZilla) 設定

---

### 步驟 5: 前端資源編譯

**本地編譯後上傳** (推薦):

```bash
# 在本地開發環境執行
npm run build

# 上傳 public/build/ 整個資料夾到 cPanel
```

**cPanel 環境編譯** (如果支援 Node.js):

```bash
# cPanel Terminal (如可用)
cd ~/public_html/line-post-card-app
npm install
npm run build
```

---

## 🔍 驗收測試

### 1. SSL Labs 測試

訪問: https://www.ssllabs.com/ssltest/analyze.html?d=business.cheni.tw

**預期結果**:
- 整體評分: **A** 或 **A-** (cPanel 環境限制)
- 憑證: 有效且包含完整鏈
- 協定: 支援 TLS 1.2 & 1.3
- HSTS: 已啟用 (max-age=31536000)

**注意**: cPanel 環境可能無法達到 A+,因為缺少:
- OCSP Stapling (需主機商啟用)
- 進階 TLS 配置 (無系統權限)

---

### 2. 功能測試

- [ ] 網站正常訪問 (https://business.cheni.tw)
- [ ] HTTP 自動導向 HTTPS
- [ ] 使用者登入/登出
- [ ] LINE LIFF 功能
- [ ] 名片新增/編輯/刪除
- [ ] 圖片上傳
- [ ] OpenAI 功能

---

### 3. 安全標頭檢查

```bash
curl -Is https://business.cheni.tw | grep -E "(Strict-Transport-Security|X-Frame-Options|Content-Security-Policy|X-Content-Type-Options)"
```

**預期輸出**:
```
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Frame-Options: DENY
Content-Security-Policy-Report-Only: ...
X-Content-Type-Options: nosniff
```

---

## 🆘 常見問題與排除

### 問題 1: "500 Internal Server Error"

**可能原因**:
1. `.htaccess` 語法錯誤
2. PHP 版本不符 (需 ≥ 8.1)
3. 檔案權限錯誤
4. `.env` 設定錯誤

**排除步驟**:
```bash
# 1. 檢查 Laravel 錯誤日誌
# cPanel File Manager: storage/logs/laravel.log

# 2. 檢查 Apache 錯誤日誌
# cPanel > Metrics > Errors

# 3. 暫時啟用除錯模式 (注意:僅測試用)
# .env: APP_DEBUG=true

# 4. 檢查 .htaccess 語法
# 暫時移除 .htaccess 測試是否恢復正常
```

---

### 問題 2: "CSRF Token Mismatch"

**原因**: Session Cookie 設定問題

**解決**:
```env
# .env 確認設定
SESSION_SECURE_COOKIE=true  # HTTPS 環境必須為 true
SESSION_DOMAIN=.cheni.tw     # 如果有子網域問題
```

---

### 問題 3: 圖片無法上傳

**原因**: PHP 上傳大小限制

**解決**:
1. cPanel > Software > Select PHP Version > Options
2. 調整 `upload_max_filesize` 和 `post_max_size`

---

### 問題 4: Composer 無法執行

**原因**: cPanel 預設可能未安裝 Composer

**解決**:
```bash
# 方法 A: 使用 cPanel Terminal 安裝 Composer
curl -sS https://getcomposer.org/installer | php
alias composer='php ~/composer.phar'

# 方法 B: 本地執行 composer install,上傳 vendor 目錄
# (不推薦,檔案太多)
```

---

## 📞 GoDaddy 技術支援聯絡

**需要協助時,請準備以下資訊**:
- cPanel 使用者名稱
- 網域名稱: business.cheni.tw
- 問題描述與錯誤訊息截圖
- 希望設定的項目 (OCSP Stapling, ServerTokens 等)

**聯絡方式**:
- GoDaddy 客服電話
- cPanel 介面內的支援系統
- 線上即時聊天

---

## 📊 GoDaddy 環境預期評分

| 掃描工具 | 預期評分 | 說明 |
|---------|---------|------|
| SSL Labs | A 或 A- | 受限於共享主機環境 |
| ZAP (中風險) | 0 個 | 應用層已完整修正 |
| ZAP (低風險) | ≤ 2 個 | 部分項目需主機商配合 |

**限制項目** (無法在 cPanel 完成):
- ❌ OCSP Stapling (需主機商啟用)
- ❌ 完全隱藏 Server 版本 (需 ServerTokens Prod)
- ❌ 進階 TLS 配置 (需系統權限)

---

**結論**: 透過本指南的設定,您可在 GoDaddy cPanel 環境中達到 **80-90% 的安全性提升**,剩餘項目需透過技術支援或升級至 VPS 方案來實現。

**最後更新**: 2025-10-06
**文件版本**: 1.0
