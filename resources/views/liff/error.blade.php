<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>錯誤 - LINE AI數位名片</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f1f1f1;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .error-container {
            max-width: 100%;
            padding: 20px;
            margin-top: 50px;
            text-align: center;
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
            padding: 30px 15px;
            border-radius: 0 0 10px 10px;
        }
        .error-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="card-header">
                <h4><i class="fas fa-exclamation-triangle"></i> 錯誤</h4>
            </div>
            <div class="card-body">
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3>發生錯誤</h3>
                <p class="lead">{{ $message ?? '無法顯示指定的AI數位名片' }}</p>
                <div class="mt-4">
                    <a href="{{ url('/admin') }}" class="btn btn-primary">返回首頁</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
