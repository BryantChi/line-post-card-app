@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>訂單歷史</h1>
    </div>
</section>
<div class="content px-3">
    @include('flash::message')

    <div class="card">
        <div class="card-header">
            <h5>我的續約紀錄</h5>
            <div class="card-tools">
                <a href="{{ route('renewal.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-sync-alt"></i> 前往續約
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>訂單編號</th>
                        <th>方案</th>
                        <th>金額</th>
                        <th>付款方式</th>
                        <th>狀態</th>
                        <th>建立時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_no }}</td>
                        <td>{{ $order->plan->name ?? '-' }}</td>
                        <td>NT$ {{ number_format($order->amount) }}</td>
                        <td>{{ $order->getPaymentMethodLabel() }}</td>
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
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('renewal.order-detail', $order->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> 詳情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">尚無訂單紀錄</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
