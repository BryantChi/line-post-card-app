@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>訂單詳情</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('admin.renewalOrders.index') }}" class="btn btn-default float-right">
                        <i class="fas fa-arrow-left"></i> 返回訂單列表
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="row">
            <div class="col-md-8">
                {{-- 訂單基本資訊 --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            訂單 {{ $order->order_no }}
                        </h3>
                        <div class="card-tools">
                            @if($order->status === 'pending')
                                <span class="badge badge-warning badge-lg">待付款</span>
                            @elseif($order->status === 'paid')
                                <span class="badge badge-success badge-lg">已付款</span>
                            @elseif($order->status === 'cancelled')
                                <span class="badge badge-secondary badge-lg">已取消</span>
                            @elseif($order->status === 'expired')
                                <span class="badge badge-danger badge-lg">已逾期</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th width="30%">訂單編號</th>
                                <td>{{ $order->order_no }}</td>
                            </tr>
                            <tr>
                                <th>會員</th>
                                <td>
                                    {{ $order->user->name ?? '-' }}
                                    <small class="text-muted ml-1">{{ $order->user->email ?? '' }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>訂閱方案</th>
                                <td>
                                    @if($order->plan)
                                        {{ $order->plan->name }}
                                        <small class="text-muted">（{{ $order->plan->duration_days }} 天）</small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>訂單金額</th>
                                <td><strong>NT$ {{ number_format($order->amount) }}</strong></td>
                            </tr>
                            <tr>
                                <th>付款方式</th>
                                <td>{{ $order->getPaymentMethodLabel() }}</td>
                            </tr>
                            <tr>
                                <th>建立時間</th>
                                <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>訂單有效期限</th>
                                <td>
                                    @if($order->expires_at)
                                        {{ $order->expires_at->format('Y-m-d H:i:s') }}
                                        @if($order->status === 'pending' && $order->expires_at->isPast())
                                            <span class="badge badge-danger ml-1">已逾期</span>
                                        @endif
                                    @else
                                        -
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
                                <th>建立者</th>
                                <td>
                                    @if($order->createdBy)
                                        {{ $order->createdBy->name }}（管理員代辦）
                                    @else
                                        會員自助
                                    @endif
                                </td>
                            </tr>
                            @if($order->admin_note)
                            <tr>
                                <th>管理員備註</th>
                                <td>{{ $order->admin_note }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- 收據圖片 --}}
                @if($order->receipt_image)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">付款收據</h3>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ Storage::url($order->receipt_image) }}" alt="付款收據"
                             class="img-fluid" style="max-height: 400px;">
                        <div class="mt-2">
                            <a href="{{ Storage::url($order->receipt_image) }}" class="btn btn-sm btn-default" target="_blank">
                                <i class="fas fa-download"></i> 下載原檔
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 交易紀錄 --}}
                @if($order->transactions->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">交易紀錄</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>交易編號</th>
                                <th>付款方式</th>
                                <th>金額</th>
                                <th>狀態</th>
                                <th>備註</th>
                                <th>時間</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->transactions as $tx)
                                <tr>
                                    <td><small>{{ $tx->transaction_no }}</small></td>
                                    <td>{{ $tx->payment_method }}</td>
                                    <td>NT$ {{ number_format($tx->amount) }}</td>
                                    <td>
                                        @if($tx->status === 'success')
                                            <span class="badge badge-success">成功</span>
                                        @elseif($tx->status === 'failed')
                                            <span class="badge badge-danger">失敗</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $tx->status }}</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $tx->note ?? '-' }}</small></td>
                                    <td><small>{{ $tx->created_at->format('Y-m-d H:i') }}</small></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                {{-- 操作區 --}}
                @if($order->status === 'pending')
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">確認付款</h3>
                    </div>
                    <div class="card-body">
                        @if(!$order->isRedirectPayment())
                        <p class="text-sm">確認後將自動更新會員到期日並建立交易紀錄。</p>
                        {!! Form::open(['route' => ['admin.renewalOrders.confirm', $order->id], 'method' => 'PATCH']) !!}
                            <div class="form-group">
                                <label for="confirm_admin_note">管理員備註（選填）</label>
                                <textarea name="admin_note" id="confirm_admin_note" rows="3"
                                          class="form-control"
                                          placeholder="例如：已確認匯款到帳..."></textarea>
                            </div>
                            {!! Form::button('<i class="fas fa-check"></i> 確認付款', [
                                'type' => 'submit',
                                'class' => 'btn btn-success btn-block js-confirm-action',
                                'data-confirm' => '確定要確認此訂單的付款並延長會員到期日嗎？'
                            ]) !!}
                        {!! Form::close() !!}
                        @else
                        <p class="text-muted text-sm">信用卡訂單將透過綠界金流自動確認，無需手動操作。</p>
                        @endif
                    </div>
                </div>

                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">取消訂單</h3>
                    </div>
                    <div class="card-body">
                        {!! Form::open(['route' => ['admin.renewalOrders.cancel', $order->id], 'method' => 'PATCH']) !!}
                            {!! Form::button('<i class="fas fa-times"></i> 取消此訂單', [
                                'type' => 'submit',
                                'class' => 'btn btn-danger btn-block js-confirm-action',
                                'data-confirm' => '確定要取消此訂單嗎？此操作不可復原。'
                            ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
                @endif

                {{-- 會員資訊 --}}
                @if($order->user)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">會員資訊</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">姓名</td>
                                <td>{{ $order->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td><small>{{ $order->user->email }}</small></td>
                            </tr>
                            <tr>
                                <td class="text-muted">目前到期日</td>
                                <td>
                                    @if($order->user->expires_at)
                                        <span class="{{ $order->user->expires_at->isPast() ? 'text-danger' : 'text-success' }}">
                                            {{ $order->user->expires_at->format('Y-m-d') }}
                                        </span>
                                    @else
                                        <span class="text-muted">無</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

@push('scripts')
<script>
$(document).on('submit', 'form:has(.js-confirm-action)', function(e) {
    var btn = $(this).find('.js-confirm-action');
    var msg = btn.data('confirm') || '確定要執行此操作嗎？';
    if (!confirm(msg)) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection
