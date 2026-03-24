@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>付款結果</h1>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    @if($success)
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h3 class="text-success">付款成功</h3>
                        @if($order)
                            <p class="text-muted">訂單編號：{{ $order->order_no }}</p>
                            <p>方案：{{ $order->plan->name ?? '' }}</p>
                        @endif
                        <p class="mt-3">您的帳號已成功續約，感謝您的支持！</p>
                    @else
                        <i class="fas fa-times-circle fa-5x text-danger mb-3"></i>
                        <h3 class="text-danger">付款失敗</h3>
                        @if($order)
                            <p class="text-muted">訂單編號：{{ $order->order_no }}</p>
                        @endif
                        <p>付款未完成（錯誤代碼：{{ $rtnCode }}），請重新嘗試或聯繫管理員。</p>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('admin.businessCards.index') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> 返回首頁
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
