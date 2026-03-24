<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #28a745;">續約成功通知</h2>

        <p>{{ $order->user->name }} 您好，</p>
        <p>您的帳號已成功續約，詳細資訊如下：</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; width: 30%;">訂單編號</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->order_no }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">訂閱方案</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->plan->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">付款金額</td>
                <td style="padding: 8px; border: 1px solid #ddd;">NT$ {{ number_format($order->amount) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">新到期日</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    {{ $order->user->expires_at ? $order->user->expires_at->format('Y-m-d') : '無限期' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">付款時間</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->paid_at?->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>

        <p>感謝您的支持！如有任何問題，請聯繫管理員。</p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
        <p style="color: #999; font-size: 12px;">此信件由系統自動發送，請勿直接回覆。</p>
    </div>
</body>
</html>
