<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $businessCard->title }} - 分享AI數位名片</title>

    <!-- Open Graph 標籤，用於社交媒體分享 -->
    <meta property="og:title" content="{{ $businessCard->title }} - AI數位名片">
    <meta property="og:description" content="{{ $businessCard->subtitle ?: '點擊查看AI數位名片詳情' }}">
    @if ($businessCard->profile_image)
        <meta property="og:image" content="{{ asset('uploads/' . $businessCard->profile_image) }}">
    @endif
    <meta property="og:url" content="{{ url('/share/' . $businessCard->uuid) }}">
    <meta property="og:type" content="website">

    {{-- font-awesome cdn --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/renderer.css') }}?v={{ config('app.version') }}">
    <style @cspNonce>
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .line-header {
            background-color: transparent;
            color: #775e9d;
            padding: 15px;
            text-align: center;
        }

        .share-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-liff {
            background-color: #06C755;
            color: white;
            border: none;
        }

        .btn-liff i.fab.fa-line {
            font-size: 23px !important;
        }

        .btn-liff:hover {
            background-color: #05a648;
            color: white;
        }

        .btn-line {
            background-color: #d9d9d9;
            color: #fff;
        }

        .btn-line:hover {
            background-color: #d9d9d9;
            color: #333;
        }

        .btn-copy {
            background-color: #b59ed8;
            color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .qr-code {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .flex-preview {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
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
        .max-h-200 {
            max-height: 200px;
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
            <h4 class="h2">AI數位名片</h4>
        </div>

        <div class="card-preview">
            <!-- 基本卡片資訊 -->
            <div
                class="text-center p-3 bg-white card-section d-flex flex-column justify-content-center align-items-center">
                <h3>{{ $businessCard->title }}</h3>
                @if ($businessCard->subtitle)
                    <p class="text-muted">{{ $businessCard->subtitle }}</p>
                @endif

                @if ($businessCard->profile_image)
                    <div class="mb-3 text-center">
                        <img src="{{ asset('uploads/' . $businessCard->profile_image) }}"
                            alt="{{ $businessCard->title }}" class="img-fluid rounded max-h-200">
                    </div>
                @endif

                @if ($businessCard->content)
                    <div class="mt-3 text-left">
                        {!! nl2br(e($businessCard->content)) !!}
                    </div>
                @endif
            </div>

            <!-- Flex Message 預覽區域 -->
            <div class="flex-preview p-3" id="flex-preview">
                <div class="flex-preview-title">LINE 卡片預覽：</div>
                <small class="text-muted">*此預覽僅供參考，以實際顯示效果為主</small>
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
                    {{-- <a href="{{ url('/liff?uuid=' . $businessCard->uuid) }}" id="open-liff-button" class="btn btn-primary btn-lg"> --}}
                    <button type="button" id="open-liff-button"
                        class="btn btn-liff btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fab fa-line" style="font-size: 23px !important;"></i>&nbsp;在 LINE 中開啟＆分享名片
                    </button>

                    <a href="{{ $lineShareUrl }}" target="_blank" id="line-share-btn" class="btn btn-line btn-lg">
                         備用LINE分享按鈕
                    </a>

                    <button id="copy-url-btn" class="btn btn-copy btn-lg">
                        <i class="fas fa-copy"></i> 複製分享連結
                    </button>
                </div>
            </div>
        </div>

        {{-- 點閱率、分享數 --}}
        <div class="text-center text-primary mb-3">
            點閱率：{{ $businessCard->views ?? 0 }} 次
            <span class="mx-2">|</span>
            分享數：{{ $businessCard->shares ?? 0 }} 次
            <br><br>
            <small class="text-muted">{{ $signature ?? 'Design by 誠翊資訊網路應用事業' }}</small>
        </div>

        <div class="qr-code d-flex flex-column justify-content-center align-items-center">
            <p>掃描 QR Code 在手機上查看</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($businessCard->getShareUrl()) }}&size=150x150"
                alt="QR Code" class="img-fluid">
        </div>

        <div class="text-center mt-4 mb-5">
            <p class="text-muted">使用上方按鈕將此AI數位名片分享給好友</p>
            <a href="{{ $businessCard->getShareUrl() }}" class="btn btn-secondary">查看原始卡片</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" nonce="{{ request()->attributes->get('csp_nonce', '') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" nonce="{{ request()->attributes->get('csp_nonce', '') }}"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" nonce="{{ request()->attributes->get('csp_nonce', '') }}"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous" nonce="{{ request()->attributes->get('csp_nonce', '') }}"></script>

    <!-- 加載 LINE LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/versions/2.22.0/sdk.js" nonce="{{ request()->attributes->get('csp_nonce', '') }}"></script>

    <!-- 加載 Flex 渲染器 -->
    <script src="{{ asset('js/renderer.js') }}?v={{ config('app.version') }}" nonce="{{ request()->attributes->get('csp_nonce', '') }}"></script>

    <script @cspNonce>
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
                    const rendered = renderFlexComponent(flexJson, "", {}, true);
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
                root.innerHTML = '<div class="alert alert-warning">此AI數位名片尚未設定 Flex Message</div>';
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

        function openInLine() {
            const liffId = '{{ env('LIFF_ID', '') }}';
            const uuid = '{{ $businessCard->uuid }}';
            if (!liffId || !uuid) {
                alert('無法取得 LIFF ID 或 UUID，請稍後再試。');
                return;
            }
            // 這行會讓 LINE App 切回 LIFF In-Client 模式
            window.open(`line://app/${liffId}?uuid=${uuid}`, '_blank');
        }

        // 頁面載入完成後執行
        document.addEventListener('DOMContentLoaded', function() {
            // 複製連結按鈕
            document.getElementById('copy-url-btn').addEventListener('click', copyShareLink);
            document.getElementById('open-liff-button').addEventListener('click', openInLine);

            // 檢查是否在 LINE 環境中
            const inLine = isInLineApp();

            // 如果在 LINE 環境中
            if (inLine) {
                // 隱藏Flex Message 預覽
                const flexPreview = document.getElementById('flex-preview');
                if (flexPreview) {
                    flexPreview.style.display = 'none';
                }

                const originalShareBtn = document.getElementById('line-share-btn');
                if (originalShareBtn) {
                    originalShareBtn.style.display = 'none';
                }

                // 在 LINE 環境中 '在 LINE App 中開啟' 改名爲 '在 LINE App 中查看＆分享'
                const openLiffButton = document.getElementById('open-liff-button');
                if (openLiffButton) {
                    openLiffButton.textContent = '在 LINE App 中查看＆分享';
                }

            } else {
                // 如果不在 LINE 環境中，顯示 Flex Message 預覽
                renderFlexPreview();
            }

        });
    </script>
</body>

</html>
