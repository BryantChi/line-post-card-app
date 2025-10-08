<!-- filepath: /Users/bryantchi/Documents/MWStudio Code/line-post-card/line-post-card-app/resources/views/card_preview/show.blade.php -->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $businessCard->title }} - AI數位名片預覽</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/renderer.css') }}?v={{ config('app.version') }}">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .preview-container {
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
        .share-button {
            background-color: #06C755;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
        }
        .share-button:hover {
            background-color: #05a648;
        }
        .qr-code {
            text-align: center;
            margin-top: 30px;
        }
        .flex-preview {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .action-buttons .btn {
            flex: 1;
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
    <div class="preview-container mt-4">
        <div class="line-header">
            <h4><i class="fas fa-comment"></i> LINE AI數位名片預覽</h4>
        </div>

        <div class="card-preview">
            <div class="text-center p-3 bg-white">
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

            <!-- 添加 Flex Message 預覽區域 -->
            <div class="flex-preview p-3">
                <div class="flex-preview-title">LINE Flex Message 預覽：</div>
                <div id="flex-root" class="flex-root"></div>
            </div>

            <!-- 狀態訊息區 -->
            <div id="status-message"></div>

            <div id="share-buttons" class="p-3 bg-light">
                <button id="share-line-btn" class="share-button">
                    <i class="fab fa-line"></i> 分享到 LINE
                </button>

                <div class="action-buttons">
                    <button id="copy-url-btn" class="btn btn-outline-secondary">
                        <i class="fas fa-copy"></i> 複製連結
                    </button>
                    <button id="open-liff-btn" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> 在 LINE 中開啟
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
            <p class="text-muted">此預覽頁面僅供參考，實際在 LINE 上的顯示效果可能有所不同</p>
            <a href="javascript:history.back()" class="btn btn-secondary">返回</a>
            <a href="{{ url('/share/' . $businessCard->uuid) }}" class="btn btn-primary ml-2">開啟分享頁面</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <!-- 加載 LINE LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/versions/2.22.0/sdk.js"></script>

    <!-- 加載 Flex 渲染器 -->
    <script src="{{ asset('js/renderer.js') }}?v={{ config('app.version') }}"></script>

    <script>
        // 卡片 JSON 資料
        const flexJson = @json($businessCard->flex_json);
        let isLiffInitialized = false;

        // 顯示狀態訊息的函數
        function showStatus(message, isError = false) {
            const statusElement = document.getElementById('status-message');
            statusElement.style.display = 'block';
            statusElement.className = isError ? 'alert alert-danger' : 'alert alert-success';
            statusElement.textContent = message;

            // 3秒後自動隱藏
            setTimeout(() => {
                statusElement.style.display = 'none';
            }, 3000);
        }

        // 複製文字到剪貼簿
        function copyToClipboard(text) {
            return new Promise((resolve, reject) => {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text)
                        .then(() => resolve(true))
                        .catch(() => {
                            // 降級到舊方法
                            const textArea = document.createElement('textarea');
                            textArea.value = text;
                            textArea.style.position = 'fixed';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();

                            try {
                                const successful = document.execCommand('copy');
                                document.body.removeChild(textArea);
                                successful ? resolve(true) : reject(new Error('無法複製'));
                            } catch (err) {
                                document.body.removeChild(textArea);
                                reject(err);
                            }
                        });
                } else {
                    // 降級到舊方法
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();

                    try {
                        const successful = document.execCommand('copy');
                        document.body.removeChild(textArea);
                        successful ? resolve(true) : reject(new Error('無法複製'));
                    } catch (err) {
                        document.body.removeChild(textArea);
                        reject(err);
                    }
                }
            });
        }

        // 檢測當前環境
        function detectEnvironment() {
            const userAgent = navigator.userAgent || '';
            return {
                isLineApp: userAgent.includes('Line') || userAgent.includes('LIFF'),
                isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent)
            };
        }

        // 初始化 LIFF
        function initializeLiff() {
            const liffId = '{{ env("LIFF_ID", "") }}';

            if (!liffId) {
                console.warn('未設置 LIFF ID，無法初始化 LIFF');
                return Promise.reject(new Error('未設置 LIFF ID'));
            }

            if (typeof liff === 'undefined') {
                console.warn('LIFF SDK 未載入');
                return Promise.reject(new Error('LIFF SDK 未載入'));
            }

            return liff.init({
                liffId: liffId
            })
            .then(() => {
                isLiffInitialized = true;
                console.log('LIFF 初始化成功');
                console.log('是否在 LINE 內:', liff.isInClient());
                console.log('LINE 登入狀態:', liff.isLoggedIn());
                return true;
            })
            .catch(error => {
                console.error('LIFF 初始化失敗:', error);
                return Promise.reject(error);
            });
        }

        // 分享到 LINE
        function shareLine() {
            // 優先使用 LINE LIFF 分享
            if (isLiffInitialized && liff.isInClient() && liff.isApiAvailable('shareTargetPicker')) {
                const flexMessage = {
                    type: "flex",
                    altText: "{{ $businessCard->title }} - AI數位名片",
                    contents: flexJson
                };

                liff.shareTargetPicker([flexMessage])
                    .then(res => {
                        if (res) {
                            showStatus('分享成功！');
                        } else {
                            console.log('用戶取消分享');
                        }
                    })
                    .catch(error => {
                        console.error('LIFF 分享失敗:', error);
                        // 降級到普通分享
                        useRegularLineShare();
                    });
            } else {
                // 使用普通 LINE 分享
                useRegularLineShare();
            }
        }

        // 使用普通 LINE 分享機制
        function useRegularLineShare() {
            const shareUrl = encodeURIComponent('{{ $businessCard->getShareUrl() }}');
            const shareTitle = encodeURIComponent('{{ $businessCard->title }} - AI數位名片');
            window.open(`https://social-plugins.line.me/lineit/share?url=${shareUrl}&text=${shareTitle}`, '_blank');
        }

        // 在頁面加載完成後初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 環境檢測
            const env = detectEnvironment();
            console.log('環境檢測:', env);

            // 渲染 Flex Message
            const root = document.getElementById('flex-root');
            if (flexJson) {
                try {
                    const rendered = renderFlexComponent(flexJson, "", {}, true);
                    if (rendered) {
                        root.appendChild(rendered);
                    } else {
                        root.innerHTML = '<div class="alert alert-warning">無法渲染 Flex Message</div>';
                    }
                } catch (error) {
                    console.error('渲染錯誤:', error);
                    root.innerHTML = '<div class="alert alert-danger">渲染過程發生錯誤</div>';
                }
            } else {
                root.innerHTML = '<div class="alert alert-warning">尚未生成 Flex Message</div>';
            }

            // 複製連結按鈕
            document.getElementById('copy-url-btn').addEventListener('click', function() {
                const shareUrl = '{{ $businessCard->getShareUrl() }}';
                copyToClipboard(shareUrl)
                    .then(() => {
                        showStatus('連結已複製到剪貼簿!');
                    })
                    .catch(err => {
                        console.error('無法複製連結:', err);
                        const result = prompt('請手動複製此連結:', shareUrl);
                        if (result !== null) {
                            showStatus('請手動複製連結');
                        }
                    });
            });

            // 在 LINE 中開啟按鈕
            document.getElementById('open-liff-btn').addEventListener('click', function() {
                const liffUrl = "{{ url('/liff') }}?uuid={{ $businessCard->uuid }}";
                window.open(liffUrl, '_blank');
            });

            // 分享到 LINE 按鈕
            document.getElementById('share-line-btn').addEventListener('click', shareLine);

            // 如果已經在 LINE App 中，可以調整一些 UI
            if (env.isLineApp) {
                // 隱藏「在 LINE 中開啟」按鈕
                document.getElementById('open-liff-btn').style.display = 'none';
            }

            // 初始化 LIFF (不影響頁面正常功能)
            initializeLiff()
                .then(() => {
                    // 如果在 LINE 客戶端內，且支援分享功能，可以增強分享體驗
                    if (liff.isInClient() && liff.isApiAvailable('shareTargetPicker')) {
                        console.log('在 LINE 內，啟用增強分享功能');
                    }
                })
                .catch(error => {
                    console.warn('LIFF 功能不可用:', error.message);
                    // 不顯示錯誤，因為我們有降級方案
                });
        });
    </script>
</body>
</html>
