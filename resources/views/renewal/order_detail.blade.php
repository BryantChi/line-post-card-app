@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>訂單詳情</h1>
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
                    <th>狀態</th>
                    <td>
                        @if($order->status === 'pending')
                            <span class="badge badge-warning">待付款</span>
                        @elseif($order->status === 'paid')
                            <span class="badge badge-success">已付款</span>
                        @elseif($order->status === 'cancelled')
                            <span class="badge badge-secondary">已取消</span>
                        @elseif($order->status === 'expired')
                            <span class="badge badge-danger">已逾期</span>
                        @else
                            <span class="badge badge-light">{{ $order->status }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>訂閱方案</th>
                    <td>{{ $order->plan->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>金額</th>
                    <td class="h5 text-primary">NT$ {{ number_format($order->amount) }}</td>
                </tr>
                <tr>
                    <th>付款方式</th>
                    <td>
                        @if($order->payment_method === 'ecpay_credit')
                            信用卡（綠界）
                        @elseif($order->payment_method === 'bank_transfer')
                            匯款
                        @elseif($order->payment_method === 'cash')
                            現金
                        @else
                            {{ $order->payment_method }}
                        @endif
                    </td>
                </tr>
                @if($order->paid_at)
                <tr>
                    <th>付款時間</th>
                    <td>{{ $order->paid_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                @endif
                <tr>
                    <th>建立時間</th>
                    <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                <tr>
                    <th>匯款收據</th>
                    <td>
                        @if($order->receipt_image)
                            <span class="text-success"><i class="fas fa-check-circle"></i> 已上傳</span>
                        @else
                            <span class="text-muted">尚未上傳</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- 交易記錄 --}}
    @if($order->transactions->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header">
            <h5>交易記錄</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>交易編號</th>
                        <th>金額</th>
                        <th>狀態</th>
                        <th>備註</th>
                        <th>時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_no }}</td>
                        <td>NT$ {{ number_format($transaction->amount) }}</td>
                        <td>
                            @if($transaction->status === 'success')
                                <span class="badge badge-success">成功</span>
                            @elseif($transaction->status === 'failed')
                                <span class="badge badge-danger">失敗</span>
                            @else
                                <span class="badge badge-secondary">{{ $transaction->status }}</span>
                            @endif
                        </td>
                        <td>{{ $transaction->note ?? '-' }}</td>
                        <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="d-flex gap-2">
        <a href="{{ route('renewal.history') }}" class="btn btn-secondary mr-2">
            <i class="fas fa-arrow-left"></i> 返回訂單歷史
        </a>

        {{-- 取消按鈕（僅 pending 狀態） --}}
        @if($order->status === 'pending')
        {!! Form::open(['route' => ['renewal.cancel-order', $order->id], 'method' => 'POST', 'style' => 'display:inline']) !!}
        <button type="submit" class="btn btn-danger"
                onclick="return confirm('確定要取消此訂單嗎？')">
            <i class="fas fa-times"></i> 取消訂單
        </button>
        {!! Form::close() !!}
        @endif
    </div>
</div>
@endsection
