@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>信用卡付款結果</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.businessCards.index') }}">首頁</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('renewal.index') }}">帳號續約</a></li>
                    <li class="breadcrumb-item active">付款結果</li>
                </ol>
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
                            <p>方案：{{ $order->plan?->name ?? '' }}</p>
                        @endif
                        <p class="mt-3">您的帳號已成功續約，感謝您的支持！</p>
                    @else
                        <i class="fas fa-times-circle fa-5x text-danger mb-3"></i>
                        <h3 class="text-danger">付款失敗或處理中</h3>
                        @if($order)
                            <p class="text-muted">訂單編號：{{ $order->order_no }}</p>
                        @endif
                        <p class="mt-2">付款未完成或尚在處理中，請稍後查看訂單狀態，或聯繫管理員。</p>
                    @endif
                    <div class="mt-4">
                        @if($order)
                            <a href="{{ route('renewal.order-detail', $order->id) }}" class="btn btn-outline-secondary mr-2">
                                <i class="fas fa-receipt"></i> 查看訂單詳情
                            </a>
                        @endif
                        <a href="{{ route('renewal.index') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> 返回續約頁面
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
