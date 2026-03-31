<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>付款結果 - LINE AI 數位名片</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .icon { font-size: 72px; margin-bottom: 20px; }
        .icon-success { color: #28a745; }
        .icon-fail    { color: #dc3545; }
        h2 { font-size: 1.8rem; margin-bottom: 12px; }
        h2.success { color: #28a745; }
        h2.fail    { color: #dc3545; }
        .info { color: #6c757d; margin: 6px 0; font-size: 0.95rem; }
        .msg  { margin: 16px 0; font-size: 1rem; color: #333; }
        .btn {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 32px;
            background: #007bff;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn:hover { background: #0056b3; color: #fff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        @if($success)
            <div class="icon"><i class="fas fa-check-circle icon-success"></i></div>
            <h2 class="success">付款成功</h2>
            @if($order)
                <p class="info">訂單編號：{{ $order->order_no }}</p>
                <p class="info">方案：{{ $order->plan?->name ?? '' }}</p>
            @endif
            <p class="msg">您的帳號已成功續約，感謝您的支持！</p>
        @else
            <div class="icon"><i class="fas fa-times-circle icon-fail"></i></div>
            <h2 class="fail">付款失敗</h2>
            @if($order)
                <p class="info">訂單編號：{{ $order->order_no }}</p>
            @endif
            <p class="msg">付款未完成（錯誤代碼：{{ $rtnCode }}），請重新嘗試或聯繫管理員。</p>
        @endif
        <a href="{{ route('home') }}" class="btn">
            <i class="fas fa-home"></i> 返回管理後台
        </a>
    </div>
</body>
</html>
