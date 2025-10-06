# GoDaddy cPanel 安全設定快速檢查清單

**目標**: 修正 Qualys SSL Labs 與 ZAP Checkmarx 掃描的弱點

---

## ✅ 第一階段:立即可完成 (30 分鐘)

### 1. SSL 憑證設定

**cPanel > Security > SSL/TLS Status**

- [ ] 點選 **Run AutoSSL** 安裝 Let's Encrypt 憑證
- [ ] 確認憑證狀態為 **Valid**
- [ ] 檢查到期日為 3 個月後

---

### 2. 強制 HTTPS

**cPanel > Domains > Domains**

- [ ] 找到 `business.cheni.tw`
- [ ] 開啟 **Force HTTPS Redirect**
- [ ] 儲存

**測試**:
```bash
curl -I http://business.cheni.tw
# 預期: 301 → https://business.cheni.tw/
```

---

### 3. PHP 版本與擴展

**cPanel > Software > Select PHP Version**

- [ ] 選擇 **PHP 8.1** 或 **8.2**
- [ ] 點選 **Extensions**,確認以下已勾選:
  - [x] curl
  - [x] gd
  - [x] intl
  - [x] json
  - [x] mbstring
  - [x] mysqli
  - [x] openssl
  - [x] pdo
  - [x] pdo_mysql
  - [x] zip

---

### 4. PHP 參數調整

**cPanel > Software > Select PHP Version > Options**

設定以下參數:

| 參數 | 設定值 |
|------|--------|
| `memory_limit` | `256M` |
| `max_execution_time` | `300` |
| `upload_max_filesize` | `20M` |
| `post_max_size` | `25M` |
| `display_errors` | `Off` |
| `log_errors` | `On` |

---

### 5. 檔案權限設定

**cPanel > Files > File Manager**

導航至專案目錄,設定權限:

```
storage/              → 775
storage/logs/         → 775
storage/framework/    → 775
bootstrap/cache/      → 775
.env                  → 600 (重要!)
```

**設定方式**:
1. 選取目錄/檔案 → 右鍵 → Change Permissions
2. 輸入數字權限值

---

### 6. 網站根目錄設定

**cPanel > Domains > Domains**

- [ ] 點選 `business.cheni.tw` 旁的 **Manage**
- [ ] **Document Root** 改為:
  ```
  /home/your_username/public_html/line-post-card-app/public
  ```
  (替換 `your_username` 為您的 cPanel 使用者名稱)
- [ ] 儲存

---

### 7. 環境變數設定

**cPanel > File Manager > 編輯 `.env`**

確認以下設定:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

**重要**: 檢查資料庫設定是否正確!

---

### 8. Laravel 排程設定

**cPanel > Advanced > Cron Jobs**

**設定**:
- 時間: `* * * * *` (每分鐘)
- 指令:
  ```bash
  cd /home/your_username/public_html/line-post-card-app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
  ```
  (替換 `your_username`)

---

## ⚙️ 第二階段:HSTS 漸進式啟用 (1-2 週)

### 週 1: 測試模式 (已在 .htaccess 設定)

**檔案**: `public/.htaccess` 第 30 行

```apache
Header always set Strict-Transport-Security "max-age=300; includeSubDomains" env=HTTPS
```

**監控**: 檢查是否有使用者回報問題

---

### 週 2+: 正式啟用

**修改 .htaccess** 第 30 行為:

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains" env=HTTPS
```

**驗收**:
```bash
curl -Is https://business.cheni.tw | grep Strict
# 預期: Strict-Transport-Security: max-age=31536000; includeSubDomains
```

---

## 🔍 第三階段:驗收測試

### SSL Labs 掃描

**網址**: https://www.ssllabs.com/ssltest/analyze.html?d=business.cheni.tw

**預期結果**:
- 評分: **A** 或 **A-**
- 憑證: ✅ Valid
- 協定: ✅ TLS 1.2 & 1.3
- HSTS: ✅ Yes

---

### 安全標頭檢查

```bash
curl -Is https://business.cheni.tw
```

**應包含以下標頭**:
```
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Content-Security-Policy-Report-Only: ...
Referrer-Policy: strict-origin-when-cross-origin
```

---

### 功能測試

- [ ] 網站可正常訪問
- [ ] HTTP → HTTPS 自動導向
- [ ] 使用者登入/登出
- [ ] LINE LIFF 功能
- [ ] 名片編輯功能
- [ ] 圖片上傳

---

## 🚨 第四階段:需聯絡 GoDaddy 技術支援

### 檢查 OCSP Stapling

```bash
echo | openssl s_client -connect business.cheni.tw:443 -status 2>&1 | grep "OCSP"
```

**如果顯示 "No OCSP response"**:
- 聯絡 GoDaddy 技術支援
- 詢問: "請協助啟用 OCSP Stapling"

---

### 請求隱藏伺服器版本

**聯絡 GoDaddy 技術支援**:

請求在 Apache 配置中加入:
```apache
ServerTokens Prod
ServerSignature Off
```

**說明**: 這可隱藏 Apache 版本資訊,提升安全性評分

---

## 📋 完整檢查清單總覽

### 必要項目 (影響掃描評分)

- [ ] ✅ SSL 憑證已安裝
- [ ] ✅ 強制 HTTPS 導向
- [ ] ✅ HSTS 已啟用 (max-age=31536000)
- [ ] ✅ PHP 版本 ≥ 8.1
- [ ] ✅ 安全標頭已設定 (透過 Laravel Middleware)
- [ ] ✅ Cookie 安全屬性 (Secure, HttpOnly, SameSite)
- [ ] ✅ 檔案權限正確
- [ ] ✅ .env 權限為 600

### 進階項目 (提升評分)

- [ ] ⚙️ OCSP Stapling (需 GoDaddy 協助)
- [ ] ⚙️ 隱藏伺服器版本 (需 GoDaddy 協助)
- [ ] ⚙️ HTTP/2 支援 (通常已預設啟用)

---

## ⏱️ 預估時間

| 階段 | 時間 | 負責方 |
|------|------|--------|
| 第一階段 | 30 分鐘 | 您 (cPanel 操作) |
| 第二階段 | 1-2 週 | 監控測試 |
| 第三階段 | 30 分鐘 | 驗收測試 |
| 第四階段 | 1-3 天 | GoDaddy 技術支援 |

---

## 📞 需要協助時

### GoDaddy 技術支援

**準備資訊**:
- cPanel 使用者名稱: `_______`
- 網域: `business.cheni.tw`
- 問題: 請求啟用 OCSP Stapling 與 ServerTokens Prod

**聯絡方式**:
- 電話客服
- cPanel 支援系統
- 線上即時聊天

---

## 🎯 最終目標

| 項目 | 目標 | 現況 |
|------|------|------|
| SSL Labs 評分 | A 或 A- | 待測試 |
| ZAP 中風險 | 0 個 | 待測試 |
| ZAP 低風險 | ≤ 2 個 | 待測試 |

---

**下一步**: 按照第一階段清單逐項完成,完成後回報結果! 📋✅
