@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>匯款資訊</h1>
    </div>
</section>
<div class="content px-3">
    @include('flash::message')

    <div class="card mb-4">
        <div class="card-header">
            <h5>訂單資訊</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">訂單編號</th>
                    <td>{{ $order->order_no }}</td>
                </tr>
                <tr>
                    <th>訂閱方案</th>
                    <td>{{ $order->plan->name }}</td>
                </tr>
                <tr>
                    <th>應付金額</th>
                    <td class="h5 text-primary">NT$ {{ number_format($order->amount) }}</td>
                </tr>
                <tr>
                    <th>付款方式</th>
                    <td>匯款</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-university"></i> 銀行匯款資訊</h5>
        </div>
        <div class="card-body">
            <p>{{ config('app.bank_transfer_info', '請聯繫管理員取得匯款資訊') }}</p>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                匯款時請務必填寫備註：訂單編號 <strong>{{ $order->order_no }}</strong>，以便核對款項。
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-upload"></i> 上傳匯款收據</h5>
        </div>
        <div class="card-body">
            @if($order->receipt_image)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 已上傳收據，等待管理員確認
                </div>
            @else
                <p class="text-muted">請上傳匯款收據截圖或照片（支援 JPG、PNG、PDF，最大 2MB）</p>
                {!! Form::open(['route' => ['renewal.upload-receipt', $order->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="form-group">
                    <label for="receipt_image">選擇收據檔案</label>
                    <input type="file" name="receipt_image" id="receipt_image"
                           class="form-control-file" accept=".jpg,.jpeg,.png,.pdf" required>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload"></i> 上傳收據
                </button>
                {!! Form::close() !!}
            @endif
        </div>
    </div>

    <a href="{{ route('renewal.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> 返回續約頁面
    </a>
</div>
@endsection
