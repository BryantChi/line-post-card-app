<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>子帳號即將到期通知</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .warning {
            color: #ff6600;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>子帳號即將到期通知</h2>
        <p>以下子帳號將在一週內到期，請確認是否需要續期：</p>

        <table>
            <thead>
                <tr>
                    <th>帳號ID</th>
                    <th>姓名</th>
                    <th>郵箱</th>
                    <th>到期日期</th>
                    <th>剩餘天數</th>
                    <th>所屬主帳號</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expiringUsers as $user)
                <tr>
                    <td>{{ $user['id'] }}</td>
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['expires_at'] }}</td>
                    <td class="{{ $user['days_left'] <= 3 ? 'warning' : '' }}">
                        {{ $user['days_left'] }} 天
                    </td>
                    <td>{{ $user['parent_name'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>請登入管理後台進行帳號續期操作。若不續期，系統將在到期日自動停用該帳號。</p>
    </div>
</body>
</html>
