<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #007bff;">新增匯款訂單通知</h2>

        <p>您好，系統收到一筆新的匯款續約訂單，請確認後進行審核。</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; width: 30%;">訂單編號</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->order_no }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">會員名稱</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->user->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">會員 Email</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->user->email }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">訂閱方案</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->plan->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">金額</td>
                <td style="padding: 8px; border: 1px solid #ddd;">NT$ {{ number_format($order->amount) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5;">建立時間</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>

        <p>請前往後台審核此訂單：</p>
        <p>
            <a href="{{ url('/admin/renewal-orders/' . $order->id) }}"
               style="background: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
                前往審核
            </a>
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
        <p style="color: #999; font-size: 12px;">此信件由系統自動發送，請勿直接回覆。</p>
    </div>
</body>
</html>
