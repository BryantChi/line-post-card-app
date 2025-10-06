# 伺服器安全設定指南 (給主機商)

此文件提供主機商進行 Apache/Nginx 伺服器層級安全設定的完整指引,以修正 Qualys SSL Labs 掃描報告中的弱點。

---

## 📋 設定項目清單

### 1. SSL/TLS 憑證管理

#### 1.1 憑證續期與自動化
**問題**: 憑證將於 2025-10-30 到期

**解決方案**:
```bash
# 使用 Let's Encrypt + Certbot 自動續期
# 安裝 Certbot (如未安裝)
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache

# 自動取得並配置憑證
sudo certbot --apache -d business.cheni.tw

# 設定自動續期 (Cron Job)
# 每天凌晨 2:00 檢查並續期
0 2 * * * certbot renew --quiet --post-hook "systemctl reload apache2"
```

**到期告警設定**:
```bash
# 建立監控腳本 /usr/local/bin/ssl-expiry-check.sh
#!/bin/bash
DOMAIN="business.cheni.tw"
DAYS_THRESHOLD=30

EXPIRY_DATE=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | openssl x509 -noout -enddate | cut -d= -f2)
EXPIRY_EPOCH=$(date -d "$EXPIRY_DATE" +%s)
NOW_EPOCH=$(date +%s)
DAYS_LEFT=$(( ($EXPIRY_EPOCH - $NOW_EPOCH) / 86400 ))

if [ $DAYS_LEFT -lt $DAYS_THRESHOLD ]; then
    echo "警告: SSL 憑證將於 $DAYS_LEFT 天後到期!"
    # 發送告警 (可串接 Email/LINE Notify)
fi

# 加入 Cron (每天檢查一次)
# 0 8 * * * /usr/local/bin/ssl-expiry-check.sh
```

#### 1.2 部署完整憑證鏈 (Fullchain)
**問題**: 可能未部署中繼憑證,導致相容性問題

**Apache 設定** (`/etc/apache2/sites-available/business.cheni.tw-le-ssl.conf`):
```apache
<VirtualHost *:443>
    ServerName business.cheni.tw

    # 使用 fullchain.pem (包含 leaf + 中繼憑證)
    SSLCertificateFile /etc/letsencrypt/live/business.cheni.tw/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/business.cheni.tw/privkey.pem

    # 不需要 SSLCertificateChainFile (已包含在 fullchain 中)
</VirtualHost>
```

**驗收**:
```bash
openssl s_client -connect business.cheni.tw:443 -showcerts
# 應顯示完整憑證鏈 (Leaf + Intermediate CA)
```

---

### 2. HTTPS 強制轉向

**Apache 設定** (`/etc/apache2/sites-available/business.cheni.tw.conf`):
```apache
<VirtualHost *:80>
    ServerName business.cheni.tw

    # 強制 HTTP → HTTPS (301 永久轉向)
    RewriteEngine On
    RewriteCond %{HTTPS} !=on
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>
```

**驗收**:
```bash
curl -I http://business.cheni.tw
# 預期: HTTP/1.1 301 Moved Permanently
# Location: https://business.cheni.tw/
```

---

### 3. HSTS (HTTP Strict Transport Security)

**Apache 設定**:
```apache
<VirtualHost *:443>
    ServerName business.cheni.tw

    # 階段 1: 測試期 (300 秒 = 5 分鐘)
    # Header always set Strict-Transport-Security "max-age=300; includeSubDomains"

    # 階段 2: 正式上線 (1 年)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # 階段 3 (選做): 加入 HSTS Preload (需先提交至 https://hstspreload.org/)
    # Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
</VirtualHost>
```

**漸進式部署策略**:
1. **第 1 週**: `max-age=300` (測試)
2. **第 2-3 週**: `max-age=86400` (1 天)
3. **第 4 週後**: `max-age=31536000` (1 年)

**驗收**:
```bash
curl -Is https://business.cheni.tw | grep -i strict
# 預期: Strict-Transport-Security: max-age=31536000; includeSubDomains
```

---

### 4. OCSP Stapling

**問題**: 未啟用,影響憑證驗證效能

**Apache 啟用設定** (`/etc/apache2/mods-available/ssl.conf`):
```apache
# 啟用 OCSP Stapling
SSLUseStapling On

# OCSP Stapling Cache (記憶體快取)
SSLStaplingCache "shmcb:/var/run/ocsp(128000)"

# OCSP 回應逾時設定
SSLStaplingResponseMaxAge 900
SSLStaplingErrorCacheTimeout 600

# OCSP Resolver (使用 Google Public DNS)
SSLStaplingStandardCacheTimeout 3600
```

**啟用模組**:
```bash
sudo a2enmod ssl
sudo a2enmod socache_shmcb
sudo systemctl restart apache2
```

**驗收**:
```bash
# 方法 1: OpenSSL 測試
echo | openssl s_client -connect business.cheni.tw:443 -status 2>&1 | grep -A 17 "OCSP Response Status"

# 方法 2: SSL Labs 檢測
# https://www.ssllabs.com/ssltest/analyze.html?d=business.cheni.tw
# 應顯示 "OCSP stapling: Yes"
```

---

### 5. 隱藏伺服器版本資訊

**問題**: HTTP 回應顯示 `Server: Apache/2.4.x`

**Apache 設定** (`/etc/apache2/conf-available/security.conf`):
```apache
# 隱藏 Apache 版本與作業系統資訊
ServerTokens Prod
ServerSignature Off

# 移除 X-Powered-By 標頭
Header always unset X-Powered-By
Header unset X-Powered-By
```

**啟用設定**:
```bash
sudo a2enconf security
sudo a2enmod headers
sudo systemctl restart apache2
```

**驗收**:
```bash
curl -I https://business.cheni.tw
# 預期: Server: Apache (無版本號)
# 不應看到: X-Powered-By
```

---

### 6. 修正 No-SNI 預設 vhost

**問題**: 不支援 SNI 的舊客戶端連線時,回傳錯誤憑證

**解決方案**:
```apache
# 方法 1: 設定明確的預設站點
<VirtualHost *:443>
    ServerName _default_
    SSLEngine on

    # 使用與主站相同的憑證
    SSLCertificateFile /etc/letsencrypt/live/business.cheni.tw/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/business.cheni.tw/privkey.pem

    # 或直接回應錯誤
    <Location />
        Require all denied
    </Location>
</VirtualHost>

# 方法 2: 確保主站點是第一個定義的 VirtualHost
# (Apache 會將第一個 VirtualHost 作為預設站點)
```

**驗收**:
```bash
# 測試無 SNI 連線
openssl s_client -connect business.cheni.tw:443 -no_ssl3 -no_tls1 -no_tls1_1
# 檢查憑證是否正確
```

---

### 7. TLS 協定與加密套件優化

**已完成項目 (維持現狀)**:
- ✅ 僅啟用 TLS 1.2 & 1.3
- ✅ 禁用 SSL 2/3, TLS 1.0/1.1
- ✅ 支援前向保密 (Forward Secrecy)
- ✅ 啟用 ALPN/HTTP2

**建議設定** (`/etc/apache2/mods-available/ssl.conf`):
```apache
# 僅允許 TLS 1.2 & 1.3
SSLProtocol -all +TLSv1.2 +TLSv1.3

# 推薦加密套件 (優先使用前向保密)
SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
SSLHonorCipherOrder off

# 啟用 HTTP/2
Protocols h2 http/1.1
```

**驗收**:
```bash
# 測試 TLS 版本
nmap --script ssl-enum-ciphers -p 443 business.cheni.tw

# 測試 HTTP/2
curl -I --http2 https://business.cheni.tw
```

---

## 🔄 完整 Apache 配置範例

### `/etc/apache2/sites-available/business.cheni.tw-le-ssl.conf`
```apache
<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName business.cheni.tw
    ServerAdmin webmaster@cheni.tw

    DocumentRoot /var/www/html/line-post-card-app/public

    <Directory /var/www/html/line-post-card-app/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # SSL 憑證設定
    SSLCertificateFile /etc/letsencrypt/live/business.cheni.tw/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/business.cheni.tw/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

    # HSTS
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # 隱藏伺服器資訊
    Header always unset X-Powered-By
    Header unset X-Powered-By

    # OCSP Stapling (在 ssl.conf 中全域啟用)

    # HTTP/2
    Protocols h2 http/1.1

    ErrorLog ${APACHE_LOG_DIR}/business.cheni.tw-error.log
    CustomLog ${APACHE_LOG_DIR}/business.cheni.tw-access.log combined
</VirtualHost>
</IfModule>
```

### `/etc/apache2/sites-available/business.cheni.tw.conf` (HTTP)
```apache
<VirtualHost *:80>
    ServerName business.cheni.tw

    # 強制轉向 HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} !=on
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>
```

---

## ✅ 設定檢查清單

部署完成後,請逐一確認以下項目:

### SSL Labs 檢測 (https://www.ssllabs.com/ssltest/)
- [ ] 整體評分: **A 或 A+**
- [ ] 憑證: 有效且包含完整鏈
- [ ] 協定支援: 僅 TLS 1.2 & 1.3
- [ ] HSTS: 已啟用 (max-age=31536000)
- [ ] OCSP Stapling: Yes
- [ ] Forward Secrecy: Yes
- [ ] Server signature: 無版本資訊

### 功能驗證
```bash
# 1. 憑證鏈完整性
openssl s_client -connect business.cheni.tw:443 -showcerts

# 2. HTTP→HTTPS 轉向
curl -I http://business.cheni.tw

# 3. HSTS 標頭
curl -Is https://business.cheni.tw | grep -i strict

# 4. OCSP Stapling
echo | openssl s_client -connect business.cheni.tw:443 -status 2>&1 | grep "OCSP Response Status"

# 5. 伺服器資訊隱藏
curl -I https://business.cheni.tw | grep -i server

# 6. HTTP/2 支援
curl -I --http2 https://business.cheni.tw
```

---

## 📝 維運建議

### 監控項目
- **憑證到期監控**: ≤30 天告警
- **OCSP Stapling 狀態**: 每日檢查
- **TLS 錯誤率**: 監控連線失敗率
- **HTTP→HTTPS 轉向率**: 追蹤 301 回應比例

### 定期檢測
- **每月**: SSL Labs 完整掃描
- **每季**: TLS 協定與加密套件更新評估
- **每年**: 安全設定全面檢視

---

## 🚨 注意事項

1. **HSTS 測試**: 先以 `max-age=300` 測試,確認無誤後再延長
2. **備份配置**: 修改前備份所有 Apache 設定檔
3. **監控日誌**: 部署後 24-48 小時密切監控錯誤日誌
4. **回滾計畫**: 保留原配置檔案以便快速回滾

---

## 📞 聯絡資訊

如有疑問或需要協助,請聯絡:
- 開發團隊: [開發團隊聯絡方式]
- 主機商技術支援: [主機商聯絡方式]

---

**最後更新**: 2025-10-06
**文件版本**: 1.0
