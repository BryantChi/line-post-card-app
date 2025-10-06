# 安全性弱點修正文件索引

本目錄包含所有與安全性弱點修正相關的文件。

---

## 📚 文件清單

### 🚀 快速開始 (推薦先閱讀)

#### 1. [cPanel 快速檢查清單](./CPANEL_QUICK_CHECKLIST.md)
**適用對象**: GoDaddy cPanel 用戶
**用途**: 30 分鐘快速完成必要安全設定
**內容**:
- ✅ SSL 憑證安裝
- ✅ 強制 HTTPS
- ✅ PHP 設定
- ✅ 檔案權限
- ✅ HSTS 啟用

**開始使用**:
```bash
cd docs/
cat CPANEL_QUICK_CHECKLIST.md
```

---

### 🔧 完整實作指南

#### 2. [GoDaddy cPanel 環境安全優化指南](./SECURITY_GODADDY_CPANEL_GUIDE.md)
**適用對象**: GoDaddy cPanel 用戶
**用途**: 完整的 cPanel 環境安全設定教學
**內容**:
- 📋 cPanel 環境特性與限制
- 🔐 SSL/TLS 憑證管理
- 🛡️ 安全標頭設定
- 📁 檔案權限配置
- 🗄️ 資料庫安全
- 🚀 部署流程
- 🆘 常見問題排除

**關鍵章節**:
- 第 1-7 節: cPanel 可直接完成的設定
- 第 8 節: 需主機商協助的項目
- 第 9 節: 部署流程
- 第 10 節: 驗收測試

---

#### 3. [應用層安全實作指南](./SECURITY_IMPLEMENTATION_GUIDE.md)
**適用對象**: 開發人員
**用途**: Laravel 應用層安全修正說明
**內容**:
- ✅ 已完成的修正項目
  - SecurityHeaders Middleware
  - Session Cookie 安全強化
  - CORS 精準白名單
  - HTTPS 強制導轉
  - Vite SRI 支援
- 🔧 後續需執行的步驟
  - CSP 測試與啟用
  - jQuery UI 升級
  - 環境變數設定
- ✅ 驗收標準

**重要檔案**:
- `app/Http/Middleware/SecurityHeaders.php`
- `config/session.php`
- `config/cors.php`
- `public/.htaccess`
- `vite.config.js`

---

#### 4. [伺服器設定指南 (給主機商)](./SECURITY_SERVER_CONFIG.md)
**適用對象**: 主機商技術人員 / VPS 用戶
**用途**: Apache/Nginx 伺服器層級安全設定
**內容**:
- 🔐 SSL/TLS 憑證管理
- 🔄 HTTPS 強制轉向
- 🛡️ HSTS 設定
- 📡 OCSP Stapling
- 🔒 隱藏伺服器版本
- 🔧 TLS 協定優化

**注意**:
- ⚠️ GoDaddy cPanel 用戶**無法**直接使用此文件的大部分內容
- ✅ 可將此文件提供給 GoDaddy 技術支援參考
- ✅ VPS/Dedicated Server 用戶可完整使用

---

## 🎯 使用情境指引

### 情境 1: 我是 GoDaddy cPanel 用戶,想快速修正弱點

**建議閱讀順序**:
1. [cPanel 快速檢查清單](./CPANEL_QUICK_CHECKLIST.md) ← **從這裡開始**
2. [GoDaddy cPanel 環境安全優化指南](./SECURITY_GODADDY_CPANEL_GUIDE.md) (詳細說明)
3. [應用層安全實作指南](./SECURITY_IMPLEMENTATION_GUIDE.md) (了解已修正項目)

---

### 情境 2: 我是開發人員,想了解程式碼修改內容

**建議閱讀順序**:
1. [應用層安全實作指南](./SECURITY_IMPLEMENTATION_GUIDE.md) ← **從這裡開始**
2. 檢視已修改的檔案:
   - `app/Http/Middleware/SecurityHeaders.php`
   - `config/session.php`
   - `config/cors.php`

---

### 情境 3: 我有 VPS/Dedicated Server 完整權限

**建議閱讀順序**:
1. [應用層安全實作指南](./SECURITY_IMPLEMENTATION_GUIDE.md)
2. [伺服器設定指南](./SECURITY_SERVER_CONFIG.md) ← **完整設定**
3. [cPanel 快速檢查清單](./CPANEL_QUICK_CHECKLIST.md) (參考應用層設定)

---

### 情境 4: 我要與 GoDaddy 技術支援溝通

**準備資料**:
1. 提供 [伺服器設定指南](./SECURITY_SERVER_CONFIG.md) 中的:
   - 第 4 節: OCSP Stapling 設定
   - 第 5 節: 隱藏伺服器版本資訊
2. 說明需求:
   - "請協助啟用 OCSP Stapling"
   - "請設定 ServerTokens Prod 以隱藏 Apache 版本"

---

## 📊 修正對照表

### Qualys SSL Labs 掃描問題

| 問題 | 解決方案 | 文件章節 | 負責方 |
|------|---------|---------|--------|
| 憑證即將到期 | Let's Encrypt 自動續期 | cPanel Guide §1.1 | GoDaddy/自動 |
| 未啟用 HSTS | 透過 .htaccess 設定 | Quick Checklist §階段2 | 您 |
| 未啟用 OCSP Stapling | 需主機商啟用 | Server Config §4 | GoDaddy |
| 伺服器資訊洩漏 | 需主機商設定 | Server Config §5 | GoDaddy |

---

### ZAP by Checkmarx 掃描問題

| 問題 | 解決方案 | 文件章節 | 狀態 |
|------|---------|---------|------|
| 未設定 CSP | SecurityHeaders Middleware | Implementation §1 | ✅ 已完成 |
| 缺少點擊劫持防護 | X-Frame-Options: DENY | Implementation §1 | ✅ 已完成 |
| jQuery UI 有漏洞 | 需升級或移除 | Implementation §步驟2 | ⚠️ 待處理 |
| Cookie 未設定 Secure | session.php 設定 | Implementation §2 | ✅ 已完成 |
| 缺少 MIME 防護 | X-Content-Type-Options | Implementation §1 | ✅ 已完成 |
| 跨網域設定錯誤 | CORS 精準白名單 | Implementation §3 | ✅ 已完成 |

---

## ⚡ 快速指令參考

### 測試 SSL 設定
```bash
# SSL Labs 線上測試
https://www.ssllabs.com/ssltest/analyze.html?d=business.cheni.tw

# 本地測試 HTTPS 導向
curl -I http://business.cheni.tw

# 檢查安全標頭
curl -Is https://business.cheni.tw | grep -E "(Strict|X-Frame|Content-Security)"
```

---

### 檢查檔案權限
```bash
# SSH 環境
ls -la storage/
ls -la bootstrap/cache/
ls -la .env

# 預期結果:
# storage/          → 775
# bootstrap/cache/  → 775
# .env              → 600
```

---

### Laravel 快取清除
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔄 更新歷程

| 日期 | 版本 | 修改內容 |
|------|------|---------|
| 2025-10-06 | 1.0 | 初始版本,完成所有安全修正文件 |

---

## 📞 技術支援

### 應用層問題 (Laravel/程式碼)
- 參考: [應用層安全實作指南](./SECURITY_IMPLEMENTATION_GUIDE.md)
- 檢查: `storage/logs/laravel.log`

### 伺服器問題 (cPanel/Apache)
- 參考: [GoDaddy cPanel 環境安全優化指南](./SECURITY_GODADDY_CPANEL_GUIDE.md)
- 聯絡: GoDaddy 技術支援

### 掃描結果解讀
- Qualys SSL Labs: [伺服器設定指南](./SECURITY_SERVER_CONFIG.md)
- ZAP Checkmarx: [應用層安全實作指南](./SECURITY_IMPLEMENTATION_GUIDE.md)

---

## ✅ 下一步行動

1. **立即執行** (30 分鐘):
   - 閱讀 [cPanel 快速檢查清單](./CPANEL_QUICK_CHECKLIST.md)
   - 完成第一階段設定

2. **1-2 週內**:
   - 啟用正式 HSTS (max-age=31536000)
   - 測試 CSP 並正式啟用
   - 升級 jQuery UI

3. **持續監控**:
   - 每月執行 SSL Labs 掃描
   - 每季執行 ZAP 掃描
   - 監控 SSL 憑證到期日

---

**祝您順利完成安全性修正! 🎉🔒**

如有任何問題,請參考對應文件或聯絡技術支援。
