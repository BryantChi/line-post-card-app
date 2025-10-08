# CSP Nonce 使用指南

## 什麼是 CSP Nonce?

CSP (Content Security Policy) Nonce 是一種安全機制,用於替代 `unsafe-inline`,提升網站對 XSS 攻擊的防護能力。

## 如何使用

### 在 Blade 視圖中使用 Nonce

#### 1. 內聯 JavaScript

**不安全的寫法** (會被 CSP 阻擋):
```html
<script>
    console.log('Hello World');
</script>
```

**安全的寫法** (使用 nonce):
```html
<script nonce="{{ csp_nonce() }}">
    console.log('Hello World');
</script>
```

#### 2. 內聯 CSS

**不安全的寫法**:
```html
<style>
    .my-class { color: red; }
</style>
```

**安全的寫法**:
```html
<style nonce="{{ csp_nonce() }}">
    .my-class { color: red; }
</style>
```

#### 3. 事件處理器

**不安全的寫法**:
```html
<button onclick="alert('Hello')">Click me</button>
```

**安全的寫法**:
```html
<button id="myButton">Click me</button>
<script nonce="{{ csp_nonce() }}">
    document.getElementById('myButton').addEventListener('click', function() {
        alert('Hello');
    });
</script>
```

## 當前狀態

- ✅ Nonce 機制已實作
- ⚠️ `unsafe-inline` 目前仍保留以確保相容性
- ⚠️ `unsafe-eval` 保留是因為 DataTables 和 Select2 需求

## 未來改善建議

1. **逐步移除 unsafe-inline**: 將所有內聯腳本/樣式添加 nonce 屬性後,可移除 CSP 中的 `unsafe-inline`
2. **評估 unsafe-eval**: 考慮升級或替換 DataTables/Select2,以移除 `unsafe-eval` 需求
3. **實作 SRI**: 為第三方 CDN 資源添加 Subresource Integrity 檢查

## 相關檔案

- `app/Http/Middleware/SecurityHeaders.php` - CSP 設定與 nonce 生成
- `app/Providers/AppServiceProvider.php` - csp_nonce() helper 函數註冊

## 測試

使用瀏覽器開發者工具的 Console,檢查是否有 CSP 違規錯誤訊息。

## 參考資料

- [MDN: Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [OWASP: Content Security Policy Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html)
