<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $businessCard->title }} - 分享電子名片</title>

    <!-- Open Graph 標籤，用於社交媒體分享 -->
    <meta property="og:title" content="{{ $businessCard->title }} - 電子名片">
    <meta property="og:description" content="{{ $businessCard->subtitle ?: '點擊查看電子名片詳情' }}">
    @if($businessCard->profile_image)
    <meta property="og:image" content="{{ asset('uploads/' . $businessCard->profile_image) }}">
    @endif
    <meta property="og:url" content="{{ url('/share/' . $businessCard->uuid) }}">
    <meta property="og:type" content="website">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/renderer.css') }}">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .share-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .card-preview {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .line-header {
            background-color: #06C755;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .share-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-line {
            background-color: #06C755;
            color: white;
        }
        .btn-line:hover {
            background-color: #05a648;
            color: white;
        }
        .btn-copy {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        .qr-code {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .flex-preview {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .flex-preview-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            font-size: 16px;
        }
        .flex-root {
            overflow-x: auto;
            padding: 10px 0;
            min-height: 50px;
        }
        .card-section {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        #status-message {
            margin-top: 10px;
            padding: 5px 10px;
            display: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="share-container mt-4">
        <div class="line-header">
            <h4><i class="fas fa-share-alt"></i> 分享電子名片</h4>
        </div>

        <div class="card-preview">
            <!-- 基本卡片資訊 -->
            <div class="text-center p-3 bg-white card-section">
                <h2>{{ $businessCard->title }}</h2>
                @if($businessCard->subtitle)
                    <p class="text-muted">{{ $businessCard->subtitle }}</p>
                @endif

                @if($businessCard->profile_image)
                    <div class="mb-3">
                        <img src="{{ asset('uploads/' . $businessCard->profile_image) }}"
                             alt="{{ $businessCard->title }}"
                             class="img-fluid rounded"
                             style="max-height: 200px;">
                    </div>
                @endif

                @if($businessCard->content)
                    <div class="mt-3 text-left">
                        {!! nl2br(e($businessCard->content)) !!}
                    </div>
                @endif
            </div>

            <!-- Flex Message 預覽區域 -->
            <div class="flex-preview p-3">
                <div class="flex-preview-title">LINE 卡片預覽：</div>
                <div id="flex-root" class="flex-root">
                    <div class="text-center text-muted py-2">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="sr-only">載入中...</span>
                        </div>
                        <span class="ml-2">載入卡片內容...</span>
                    </div>
                </div>
            </div>

            <!-- 狀態訊息區 -->
            <div id="status-message"></div>

            <!-- 分享按鈕區域 -->
            <div class="p-3 bg-light">
                <div class="share-options">
                    <a href="{{ $lineShareUrl }}" target="_blank" id="line-share-btn" class="btn btn-line btn-lg">
                        <i class="fab fa-line"></i> 分享到 LINE
                    </a>

                    <a href="{{ url('/liff?uuid=' . $businessCard->uuid) }}" id="open-liff-button" class="btn btn-primary btn-lg">
                        <i class="fas fa-external-link-alt"></i> 在 LINE App 中開啟
                    </a>

                    <button id="copy-url-btn" class="btn btn-copy btn-lg">
                        <i class="fas fa-copy"></i> 複製分享連結
                    </button>
                </div>
            </div>
        </div>

        <div class="qr-code">
            <p>掃描 QR Code 在手機上查看</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($businessCard->getShareUrl()) }}&size=150x150"
                 alt="QR Code" class="img-fluid">
        </div>

        <div class="text-center mt-4 mb-5">
            <p class="text-muted">使用上方按鈕將此電子名片分享給好友</p>
            <a href="{{ $businessCard->getShareUrl() }}" class="btn btn-secondary">查看原始卡片</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <!-- 加載 LINE LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/versions/2.22.0/sdk.js"></script>

    <!-- 加載 Flex 渲染器 -->
    <script src="{{ asset('js/renderer.js') }}"></script>

    <script>
        // 卡片 JSON 資料
        const flexJson = @json($businessCard->flex_json);
        let isLiffInitialized = false;

        // 檢測是否在 LINE 環境中
        function isInLineApp() {
            const userAgent = navigator.userAgent || '';
            return userAgent.includes('Line') || userAgent.includes('LIFF');
        }

        // 渲染 Flex Message 預覽
        function renderFlexPreview() {
            const root = document.getElementById('flex-root');
            if (flexJson) {
                try {
                    const rendered = renderFlexComponent(flexJson, "");
                    if (rendered) {
                        // 清空載入中訊息
                        root.innerHTML = '';
                        root.appendChild(rendered);
                    } else {
                        root.innerHTML = '<div class="alert alert-warning">無法渲染 Flex Message</div>';
                    }
                } catch (error) {
                    console.error('渲染錯誤:', error);
                    root.innerHTML = '<div class="alert alert-danger">渲染過程發生錯誤</div>';
                }
            } else {
                root.innerHTML = '<div class="alert alert-warning">此電子名片尚未設定 Flex Message</div>';
            }
        }

        // 複製分享連結
        function copyShareLink() {
            const shareUrl = '{{ url("/share/{$businessCard->uuid}") }}';

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareUrl)
                    .then(() => {
                        alert('連結已複製到剪貼簿！');
                    })
                    .catch(() => {
                        // 降級方法 - 使用提示框讓用戶手動複製
                        prompt('請手動複製此連結:', shareUrl);
                    });
            } else {
                // 不支援 Clipboard API
                prompt('請手動複製此連結:', shareUrl);
            }
        }

        // 初始化 LIFF SDK (無需登入)
        function initLiffForShare() {
            const liffId = '{{ env("LIFF_ID", "") }}';

            if (!liffId) {
                console.warn('未設置 LIFF ID');
                return Promise.reject(new Error('未設置 LIFF ID'));
            }

            if (typeof liff === 'undefined') {
                console.warn('LIFF SDK 未載入');
                return Promise.reject(new Error('LIFF SDK 未載入'));
            }

            // 初始化 LIFF 但不要求登入
            return liff.init({
                liffId: liffId,
                // 不需要登入
                withLoginOnExternalBrowser: false
            })
            .then(() => {
                console.log('LIFF 初始化成功');
                isLiffInitialized = true;
                return {
                    isInClient: liff.isInClient(),
                    isLoggedIn: liff.isLoggedIn(),
                    hasShareApi: liff.isApiAvailable('shareTargetPicker')
                };
            })
            .catch(err => {
                console.error('LIFF 初始化失敗:', err);
                return Promise.reject(err);
            });
        }

        // 使用 LIFF 分享 Flex Message
        function shareFlexMessage() {
            if (!isLiffInitialized || !liff.isApiAvailable('shareTargetPicker')) {
                alert('無法使用 LINE 原生分享功能，請使用其他分享方式');
                return;
            }

            const flexMessage = {
                type: "flex",
                altText: "{{ $businessCard->title }} - 電子名片",
                contents: flexJson
            };

            // 顯示分享中狀態
            const statusDiv = document.getElementById('status-message');
            statusDiv.innerHTML = '<div class="alert alert-info">正在分享...</div>';
            statusDiv.style.display = 'block';

            liff.shareTargetPicker([flexMessage])
                .then(res => {
                    if (res) {
                        statusDiv.innerHTML = '<div class="alert alert-success">分享成功！</div>';
                    } else {
                        // 用戶取消
                        statusDiv.innerHTML = '<div class="alert alert-warning">已取消分享</div>';
                    }

                    // 3秒後隱藏狀態
                    setTimeout(() => {
                        statusDiv.style.display = 'none';
                    }, 3000);
                })
                .catch(err => {
                    console.error('分享失敗:', err);
                    statusDiv.innerHTML = '<div class="alert alert-danger">分享失敗，請嘗試其他方式</div>';
                });
        }

        // 頁面載入完成後執行
        document.addEventListener('DOMContentLoaded', function() {
            // 渲染 Flex Message 預覽
            renderFlexPreview();

            // 複製連結按鈕
            document.getElementById('copy-url-btn').addEventListener('click', copyShareLink);

            // 檢查是否在 LINE 環境中
            const inLine = isInLineApp();

            // 如果在 LINE 環境中，嘗試添加 Flex 分享功能
            if (inLine) {
                // 載入 LIFF SDK
                if (typeof liff === 'undefined') {
                    // 動態載入 LIFF SDK
                    const liffScript = document.createElement('script');
                    liffScript.src = 'https://static.line-scdn.net/liff/edge/versions/2.22.0/sdk.js';
                    liffScript.charset = 'utf-8';
                    document.head.appendChild(liffScript);

                    liffScript.onload = function() {
                        setupLineShare();
                    };
                } else {
                    setupLineShare();
                }
            }

            // 設置 LINE 分享功能
            function setupLineShare() {
                // 初始化 LIFF (無需登入)
                initLiffForShare()
                    .then(liffInfo => {
                        // 如果是在 LINE 中，添加直接分享 Flex 的按鈕
                        if (liffInfo.isInClient || liffInfo.isLoggedIn) {
                            // 如果原始分享按鈕存在，先隱藏它
                            const originalShareBtn = document.getElementById('line-share-btn');
                            if (originalShareBtn) {
                                originalShareBtn.style.display = 'none';
                            }

                            // 創建新的 Flex 分享按鈕
                            const flexShareBtn = document.createElement('a');
                            flexShareBtn.id = 'flex-share-btn';
                            flexShareBtn.className = 'btn btn-line btn-lg';
                            flexShareBtn.innerHTML = '<i class="fab fa-line"></i> 分享名片至聊天室';
                            flexShareBtn.href = 'javascript:void(0)';
                            flexShareBtn.addEventListener('click', shareFlexMessage);

                            // 添加到分享選項區域
                            const shareOptions = document.querySelector('.share-options');
                            if (shareOptions) {
                                // 插入到第一個位置
                                if (shareOptions.firstChild) {
                                    shareOptions.insertBefore(flexShareBtn, shareOptions.firstChild);
                                } else {
                                    shareOptions.appendChild(flexShareBtn);
                                }
                            }
                        }
                    })
                    .catch(err => {
                        console.warn('無法初始化 LIFF:', err);
                        // 保留原始分享方式，不顯示錯誤
                    });
            }
        });
    </script>
</body>
</html>
