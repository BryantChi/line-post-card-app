<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- 新增 CSRF token -->
    <title>{{ $businessCard->title }} - LINE 數位名片</title>

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Flex Renderer CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/renderer.css') }}?v={{ time() }}">

    <style>
        body {
            background-color: #f1f1f1;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .liff-container {
            max-width: 100%;
            padding: 10px;
        }
        .card-header {
            background-color: #06C755;
            color: white;
            padding: 12px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .card-body {
            background-color: white;
            padding: 15px;
            border-radius: 0 0 10px 10px;
        }
        .share-button {
            background-color: #06C755;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 20px;
            width: 100%;
            margin-top: 15px;
            font-weight: bold;
        }
        .share-button:hover {
            background-color: #05a648;
        }
        .flex-root {
            overflow-x: auto;
            padding: 10px 0;
        }
        .card-info {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .status-bar {
            padding: 10px;
            margin: 10px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
        }
        .debug-info {
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .open-in-line-btn {
            background-color: #00C300;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }
        .open-in-line-btn:hover {
            background-color: #00a000;
        }
    </style>
</head>
<body>
    <div class="liff-container">
        <div class="card-header">
            <h4><i class="fas fa-id-card"></i> LINE 數位名片</h4>
        </div>

        <div class="card-body">
            <!-- 狀態顯示區域 -->
            <div id="status-bar" class="status-bar" style="display: none;">
                <div id="status-message">初始化中...</div>
                <div id="debug-info" class="debug-info" style="display: none;"></div>
            </div>

            <!-- 名片標題、子標題、圖片、內容 -->
            <div class="card-info text-center d-flex flex-column justify-content-center align-items-center">
                <h3>{{ $businessCard->title }}</h3>
                @if($businessCard->subtitle)
                    <p class="text-muted">{{ $businessCard->subtitle }}</p>
                @endif

                @if($businessCard->profile_image)
                    <div class="text-center my-3">
                        <img src="{{ asset('uploads/' . $businessCard->profile_image) }}"
                             alt="{{ $businessCard->title }}"
                             class="img-fluid rounded"
                             style="max-height: 150px;">
                    </div>
                @endif

                @if($businessCard->content)
                    <div class="mt-3 text-left">
                        {!! nl2br(e($businessCard->content)) !!}
                    </div>
                @endif
            </div>

            <!-- 載入 Flex Message 的旋轉圈 -->
            <div id="loading" class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">載入中...</span>
                </div>
                <p class="mt-2">正在載入卡片內容...</p>
            </div>

            <!-- 渲染之後的 Flex Message 內容 -->
            <div id="flex-root" class="flex-root" style="display: none;">
                <h5 class="text-center">數位名片預覽</h5>
                <p class="text-center text-muted">請在 LINE App 中查看最佳效果</p>
                <small class="text-muted">*此預覽僅供參考，以實際顯示效果為主</small>
            </div>

            <!-- 分享按鈕 -->
            <button id="share-btn" class="share-button">
                <i class="fas fa-share-alt"></i> 分享此數位名片
            </button>

            <!-- 動態插入的額外按鈕容器（登入、複製連結、在 LINE 中開啟等） -->
            <div id="action-container" class="action-buttons" style="display: none;"></div>

            <!-- 調試按鈕與直接查看分享頁面按鈕 -->
            <div class="mt-3 text-center">
                <button id="debug-btn" class="btn btn-sm btn-secondary">檢測LIFF功能</button>
                <a href="{{ url('/share/' . $businessCard->uuid) }}" class="btn btn-sm btn-outline-primary ml-2">查看分享頁面</a>
            </div>
        </div>
    </div>

    <!-- jQuery、Popper.js、Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <!-- LINE LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>
    <!-- Flex Renderer JS（請確保 public/js/renderer.js 已正確載入） -->
    <script src="{{ asset('js/renderer.js') }}?v={{ time() }}"></script>

    <script>
        // 後端傳來的 Flex JSON
        const flexJson = @json($businessCard->flex_json);
        let isLiffInitialized = false;

        // UI 元素集合
        const UI = {
            statusBar: document.getElementById('status-bar'),
            statusMessage: document.getElementById('status-message'),
            debugInfo: document.getElementById('debug-info'),
            loading: document.getElementById('loading'),
            flexRoot: document.getElementById('flex-root'),
            shareBtn: document.getElementById('share-btn'),
            actionContainer: document.getElementById('action-container'),
            debugBtn: document.getElementById('debug-btn'),

            showStatus(message, isError = false) {
                this.statusBar.style.display = 'block';
                this.statusMessage.textContent = message;
                this.statusMessage.style.color = isError ? 'red' : 'green';
                if (!isError) {
                    setTimeout(() => { this.statusBar.style.display = 'none'; }, 10000);
                }
            },

            toggleDebugInfo(show, info = '') {
                this.debugInfo.style.display = show ? 'block' : 'none';
                this.debugInfo.innerHTML = info;
            },

            renderFlexMessage() {
                if (!flexJson) {
                    this.loading.innerHTML = '<div class="alert alert-warning">無法渲染 Flex Message 或尚未設定</div>';
                    return false;
                }
                try {
                    const rendered = renderFlexComponent(flexJson, '');
                    this.loading.style.display = 'none';
                    this.flexRoot.style.display = 'block';
                    this.flexRoot.appendChild(rendered);
                    return true;
                } catch (error) {
                    this.loading.innerHTML = '<div class="alert alert-danger">渲染過程發生錯誤</div>';
                    console.error('渲染錯誤:', error);
                    return false;
                }
            },

            addActionButton(id, text, className, icon, handler) {
                if (document.getElementById(id)) return;
                const btn = document.createElement('button');
                btn.id = id;
                btn.className = className;
                btn.innerHTML = `<i class="fas fa-${icon}"></i> ${text}`;
                btn.addEventListener('click', handler);
                this.actionContainer.appendChild(btn);
                this.actionContainer.style.display = 'flex';
                return btn;
            },

            clearActionButtons() {
                this.actionContainer.innerHTML = '';
                this.actionContainer.style.display = 'none';
            }
        };

        // 取得網址中的 uuid 參數
        function getUuidFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('uuid');
        }

        // 將 uuid 存到 localStorage（供跳回後續重導使用）
        function saveUuidToStorage() {
            const uuid = getUuidFromUrl();
            if (uuid) {
                localStorage.setItem('line_card_uuid', uuid);
            }
        }

        // 從 localStorage 拿回 uuid
        function restoreUuidFromStorage() {
            return localStorage.getItem('line_card_uuid');
        }

        // 清除 localStorage 裡的 uuid
        function clearStoredUuid() {
            localStorage.removeItem('line_card_uuid');
        }

        // 初始化 LIFF，並回傳 { isInClient, isLoggedIn, hasShareApi } 的 Promise
        function initializeLiff() {
            const liffId = '{{ env("LIFF_ID", "") }}';
            UI.showStatus('正在初始化 LIFF...');
            if (!liffId) {
                UI.showStatus('未設置 LIFF ID - 請檢查環境設定', true);
                return Promise.reject(new Error('未設置 LIFF ID'));
            }
            if (typeof liff === 'undefined') {
                UI.showStatus('LIFF SDK 未載入 - 請確認網路連線', true);
                return Promise.reject(new Error('LIFF SDK 未載入'));
            }

            // 將目前 URL 的 uuid 先存起來，以便登入後可還原
            saveUuidToStorage();

            return liff.init({ liffId, withLoginOnExternalBrowser: false })
                .then(() => {
                    isLiffInitialized = true;
                    UI.showStatus('LIFF 初始化成功');

                    // 如果網址上沒有 uuid，就嘗試從 localStorage 拿回並重寫回 URL
                    if (!getUuidFromUrl()) {
                        const storedUuid = restoreUuidFromStorage();
                        if (storedUuid) {
                            window.history.replaceState({}, document.title, `${window.location.pathname}?uuid=${storedUuid}`);
                            clearStoredUuid();
                        }
                    } else {
                        // 有帶 uuid，就清除掉暫存的 localStorage
                        clearStoredUuid();
                    }

                    // 如果剛才是因為登入而重導回來，並要自動分享
                    const action = localStorage.getItem('line_login_action');
                    if (action === 'share' && liff.isLoggedIn()) {
                        localStorage.removeItem('line_login_action');
                        setTimeout(() => {
                            UI.showStatus('登入成功，準備分享...');
                            executeShareAction();
                        }, 800);
                    }

                    return {
                        isInClient: liff.isInClient(),
                        isLoggedIn: liff.isLoggedIn(),
                        hasShareApi: liff.isApiAvailable('shareTargetPicker')
                    };
                })
                .catch(error => {
                    UI.showStatus(`LIFF 初始化失敗: ${error.message}`, true);
                    return Promise.reject(error);
                });
        }

        // 當使用者需要登入 LINE 才能分享時，顯示登入按鈕和「複製連結」備用
        function requestLineLogin() {
            UI.clearActionButtons();
            UI.addActionButton(
                'login-btn',
                '登入 LINE 以分享',
                'btn btn-primary btn-md',
                'sign-in-alt',
                () => {
                    // 儲存 uuid，標記要自動分享，然後呼叫 liff.login()
                    saveUuidToStorage();
                    localStorage.setItem('line_login_action', 'share');
                    liff.login({ redirectUri: window.location.href });
                }
            );
            UI.addActionButton(
                'copy-link-btn',
                '複製分享連結',
                'btn btn-outline-secondary btn-md',
                'copy',
                offerCopyShareLink
            );
        }

        function validateFlexJson(json) {
            // 基本類型檢查
            if (!json || typeof json !== 'object') {
                return { valid: false, error: 'Flex 訊息格式無效' };
            }

            // 檢查必要的 type 屬性 (必須是 bubble 或 carousel)
            if (!json.type || (json.type !== 'bubble' && json.type !== 'carousel')) {
                return { valid: false, error: 'Flex 訊息需要有效的 type 屬性 (bubble 或 carousel)' };
            }

            // 如果是 carousel 類型，檢查 contents 陣列
            if (json.type === 'carousel') {
                if (!json.contents || !Array.isArray(json.contents) || json.contents.length === 0) {
                    return { valid: false, error: 'Carousel 需要非空的 contents 陣列' };
                }

                // 檢查 contents 中的每個項目
                for (let i = 0; i < json.contents.length; i++) {
                    if (!json.contents[i] || typeof json.contents[i] !== 'object' || json.contents[i].type !== 'bubble') {
                        return { valid: false, error: `Carousel 中的第 ${i+1} 個項目必須是有效的 bubble` };
                    }
                }
            }

            // 檢查 bubble 必須有基本元素 (body, header, footer 至少一個)
            if (json.type === 'bubble' &&
                !json.body && !json.header && !json.footer && !json.hero) {
                return { valid: false, error: 'Bubble 必須至少包含 body, header, footer 或 hero 其中之一' };
            }

            return { valid: true, success: `Flex 訊息格式有效 ${json.type}` };
        }

        // 真正呼叫 shareTargetPicker 的邏輯
        function executeShareAction() {
            const currentFlex = @json($businessCard->flex_json);
            const validation = validateFlexJson(currentFlex);
            if (!validation.valid) {
                UI.showStatus(`Flex 訊息格式無效: ${validation.error}`, true);
                console.error('無效的 flex:', currentFlex);
                offerCopyShareLink();
                return;
            }

            const flexMessage = {
                type: 'flex',
                altText: '{{ $businessCard->title }} - 數位名片',
                contents: currentFlex
            };
            UI.showStatus('正在分享...');
            liff.shareTargetPicker([flexMessage])
                .then(res => {
                    console.log('shareTargetPicker 回傳：', res);
                    // UI.showStatus(`${JSON.stringify(res)}`, false);
                    if (res.status === 'success') {
                        UI.showStatus('分享成功！');
                        recordShare('{{ $businessCard->uuid }}'); // 新增：記錄分享
                    } else {
                        UI.showStatus('已取消分享');
                    }
                })
                .catch(error => {
                    console.error('分享失敗', error);
                    const msg = error.message || '';
                    if (msg.includes('not logged in')) {
                        requestLineLogin();
                        return;
                    }
                    UI.showStatus(`分享失敗: ${msg}`, true);
                    offerCopyShareLink();
                });
        }

        // 退而求其次－複製分享連結到剪貼簿
        function offerCopyShareLink() {
            UI.clearActionButtons();
            const shareUrl = '{{ url("/share/" . $businessCard->uuid) }}';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareUrl).then(() => {
                    UI.showStatus('連結已複製，請貼到 LINE 中分享');
                }).catch(() => {
                    prompt('請手動複製此連結：', shareUrl);
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = shareUrl;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                UI.showStatus('連結已複製，請貼到 LINE 中分享');
            }
            // 顯示一顆「查看分享頁面」按鈕
            // UI.addActionButton(
            //     'view-share-page',
            //     '查看分享頁面',
            //     'btn btn-outline-primary btn-md',
            //     'external-link-alt',
            //     () => window.open(shareUrl, '_blank')
            // );
        }

        // 如果偵測到是「外部瀏覽器」模式（In-Client = false），就顯示「在 LINE 中開啟」的公告和按鈕
        function handleExternalBrowserPrompt(isLoggedIn) {
            if (document.querySelector('.external-browser-notice')) return;
            const notice = document.createElement('div');
            notice.className = 'alert alert-info mb-3 external-browser-notice';

            if (isLoggedIn) {
                // 已在 LINE WebView 中登入帳號，但仍然沒有以 LIFF In-Client 方式啟動
                notice.innerHTML = `
                    <strong>提示：</strong> 您已登入 LINE，可使用分享功能。<br>
                    <button class="open-in-line-btn" onclick="openInLine()">
                        在 LINE 中開啟
                    </button>`;
            } else {
                // 未登入 LINE，或尚未到 LIFF In-Client
                notice.innerHTML = `
                    <strong>提示：</strong> 請在 LINE App 內開啟此頁，以獲得完整分享功能。<br>
                    <button class="open-in-line-btn" onclick="openInLine()">
                        在 LINE 中開啟
                    </button>`;
            }

            // 插到 card-info 之上
            document.querySelector('.card-body').insertBefore(notice, document.querySelector('.card-info'));

            // 隱藏 分享按鈕，因為外部瀏覽器無法直接分享
            UI.shareBtn.style.display = 'none';
        }

        // 按下「在 LINE 中開啟」時，使用 LINE URI Scheme 跳轉
        function openInLine() {
            const liffId = '{{ env("LIFF_ID", "") }}';
            const uuid = getUuidFromUrl();
            if (!liffId || !uuid) {
                alert('無法取得 LIFF ID 或 UUID，請稍後再試。');
                return;
            }
            // 這行會讓 LINE App 切回 LIFF In-Client 模式
            window.open(`line://app/${liffId}?uuid=${uuid}`, '_blank');
        }

        // 顯示 LIFF 診斷資訊，並可複製
        function showDebugInfo() {
            const userAgent = navigator.userAgent || '';
            const isLineApp = userAgent.includes('Line') || userAgent.includes('LIFF');
            const debugInfoData = {
                '時間': new Date().toLocaleString(),
                'LIFF初始化': isLiffInitialized,
                'LIFF ID': '{{ env("LIFF_ID", "未設置") }}',
                'URL': window.location.href,
                'UUID': '{{ $businessCard->uuid }}',
                'URL參數UUID': getUuidFromUrl() || '無',
                '儲存的UUID': restoreUuidFromStorage() || '無',
                'User Agent': userAgent,
                'UA判斷為LINE': isLineApp,
                'SDK已載入': typeof liff !== 'undefined',
                'API判斷為LINE': typeof liff !== 'undefined' ? liff.isInClient() : '無法檢測',
                'LINE登入': typeof liff !== 'undefined' ? liff.isLoggedIn() : '無法檢測',
                'LIFF版本': typeof liff !== 'undefined' ? liff.getVersion() : '無法檢測',
                '分享功能': typeof liff !== 'undefined' ? liff.isApiAvailable('shareTargetPicker') : '無法檢測',
                '螢幕寬度': window.innerWidth,
                '螢幕高度': window.innerHeight
            };
            UI.toggleDebugInfo(true, `<pre style="white-space: pre-wrap; word-break: break-all;">${JSON.stringify(debugInfoData, null, 2)}</pre>`);
            UI.addActionButton(
                'close-debug-btn',
                '關閉診斷資訊',
                'btn btn-secondary btn-md',
                'times',
                () => {
                    UI.toggleDebugInfo(false);
                    UI.clearActionButtons();
                }
            );
            UI.addActionButton(
                'copy-debug-btn',
                '複製診斷資訊',
                'btn btn-info btn-md',
                'copy',
                () => {
                    const dbgText = JSON.stringify(debugInfoData, null, 2);
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(dbgText).then(() => {
                            UI.showStatus('診斷資訊已複製');
                        }).catch(() => {
                            alert('無法複製診斷資訊');
                        });
                    } else {
                        prompt('請手動複製診斷資訊:', dbgText);
                    }
                }
            );
            console.table(debugInfoData);
        }

        // 頁面載入後啟動
        document.addEventListener('DOMContentLoaded', () => {
            // 先渲染 Flex Message
            UI.renderFlexMessage();

            // 預設先禁用分享按鈕，等 LIFF 初始化完成後再啟用
            UI.shareBtn.disabled = true;
            UI.shareBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 準備中...';

            // 如果 LIFF 長時間無反應（8秒），就把分享按鈕改成複製連結
            const initTimeout = setTimeout(() => {
                if (!isLiffInitialized) {
                    UI.showStatus('LINE 連接超時，切換到備用分享模式', true);
                    UI.shareBtn.disabled = false;
                    UI.shareBtn.innerHTML = '<i class="fas fa-copy"></i> 複製分享連結';
                    UI.shareBtn.removeEventListener('click', shareCard);
                    UI.shareBtn.addEventListener('click', offerCopyShareLink);
                    UI.addActionButton(
                        'retry-btn',
                        '重試連接 LINE',
                        'btn btn-warning btn-md',
                        'sync',
                        () => location.reload()
                    );
                }
            }, 8000);

            // 綁定調試按鈕
            UI.debugBtn.addEventListener('click', showDebugInfo);

            // 如果網址沒有 uuid，就先嘗試從 localStorage 拿回，然後重載
            if (!getUuidFromUrl()) {
                const storedUuid = restoreUuidFromStorage();
                if (storedUuid) {
                    window.location.href = `${window.location.pathname}?uuid=${storedUuid}`;
                    return;
                }
            }

            UI.showStatus('正在連接 LINE 平台...');
            initializeLiff()
                .then(info => {
                    clearTimeout(initTimeout);

                    // LIFF 初始化成功，啟用分享按鈕
                    UI.shareBtn.disabled = false;
                    UI.shareBtn.innerHTML = '<i class="fas fa-share-alt"></i> 分享此數位名片';
                    UI.shareBtn.removeEventListener('click', shareCard);
                    UI.shareBtn.addEventListener('click', shareCard);

                    // 如果不是 In-Client，就顯示「在 LINE 中開啟」
                    if (!info.isInClient) {
                        handleExternalBrowserPrompt(info.isLoggedIn);
                    }
                })
                .catch(error => {
                    clearTimeout(initTimeout);
                    UI.showStatus(`無法連接 LINE 平台: ${error.message}`, true);
                    // 失敗後把分享按鈕改成複製連結
                    UI.shareBtn.disabled = false;
                    UI.shareBtn.innerHTML = '<i class="fas fa-copy"></i> 複製分享連結';
                    UI.shareBtn.removeEventListener('click', shareCard);
                    UI.shareBtn.addEventListener('click', offerCopyShareLink);
                    UI.addActionButton(
                        'retry-btn',
                        '重試連接 LINE',
                        'btn btn-warning btn-md',
                        'sync',
                        () => location.reload()
                    );
                });
        });

        // shareBtn 的綁定函式（為了好拆分）
        function shareCard() {
            // 如果 LIFF 還沒初始化，就提示錯誤並提供重試
            if (!isLiffInitialized) {
                UI.showStatus('LINE 尚未初始化', true);
                UI.addActionButton('retry-init', '重試初始化', 'btn btn-warning btn-md', 'sync', () => location.reload());
                return;
            }

            // In-Client 的話，直接呼叫分享
            if (liff.isInClient()) {
                executeShareAction();
                return;
            }

            // 外部瀏覽器模式，先判斷是否登入
            if (!liff.isLoggedIn()) {
                UI.showStatus('分享需要先登入 LINE', true);
                requestLineLogin();
                return;
            }

            // 已登入但仍非 In-Client，就改用「在 LINE 中開啟」
            if (!liff.isApiAvailable('shareTargetPicker')) {
                UI.showStatus('LINE 版本不支援分享 API，請在 LINE 中開啟', true);
                handleExternalBrowserPrompt(true);
                return;
            }

            // 正常情況下，執行分享
            executeShareAction();
        }

        // 新增：記錄分享次數的函數
        function recordShare(cardUuid) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
            fetch(`/api/cards/${cardUuid}/increment-share`, { // 請確保此路由已在您的路由文件中定義
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken, // 發送 CSRF Token
                    'Accept': 'application/json',
                },
                // body: JSON.stringify({}) // 如果 API 需要 body 的話
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Share recorded successfully.');
                } else {
                    console.error('Failed to record share:', data.message);
                }
            })
            .catch(error => {
                console.error('Error recording share:', error);
            });
        }
    </script>
</body>
</html>
