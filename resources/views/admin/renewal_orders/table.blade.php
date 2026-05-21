<div class="table-responsive">
    <table class="table table-hover" id="renewal-orders-table">
        <thead>
        <tr>
            <th>訂單編號</th>
            <th>會員</th>
            <th>方案</th>
            <th>金額（NT$）</th>
            <th>付款方式</th>
            <th>狀態</th>
            <th>建立時間</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td>
                    <a href="{{ route('admin.renewalOrders.show', $order->id) }}">
                        {{ $order->order_no }}
                    </a>
                </td>
                <td>{{ $order->user->name ?? '-' }}</td>
                <td>{{ $order->plan->name ?? '-' }}</td>
                <td>NT$ {{ number_format($order->amount) }}</td>
                <td>
                    @php
                        $pmBadge = match ($order->payment_method) {
                            'ecpay_credit', 'newebpay_credit' => 'badge-info',
                            'bank_transfer' => 'badge-primary',
                            'cash' => 'badge-dark',
                            default => 'badge-secondary',
                        };
                    @endphp
                    <span class="badge {{ $pmBadge }}">{{ $order->getPaymentMethodLabel() }}</span>
                </td>
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
                        {{ $order->status }}
                    @endif
                </td>
                <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <div class="btn-group">
                        <a href="{{ route('admin.renewalOrders.show', $order->id) }}"
                           class="btn btn-default btn-sm" title="詳情">
                            <i class="fas fa-eye"></i>
                        </a>

                        @if($order->status === 'pending' && !$order->isRedirectPayment())
                            {!! Form::open(['route' => ['admin.renewalOrders.confirm', $order->id], 'method' => 'PATCH', 'style' => 'display:inline']) !!}
                            {!! Form::button('<i class="fas fa-check"></i>', [
                                'type' => 'submit',
                                'class' => 'btn btn-success btn-sm js-confirm-action',
                                'title' => '確認付款',
                                'data-confirm' => '確定要確認此訂單的付款嗎？'
                            ]) !!}
                            {!! Form::close() !!}
                        @endif

                        @if($order->status === 'pending')
                            {!! Form::open(['route' => ['admin.renewalOrders.cancel', $order->id], 'method' => 'PATCH', 'style' => 'display:inline']) !!}
                            {!! Form::button('<i class="fas fa-times"></i>', [
                                'type' => 'submit',
                                'class' => 'btn btn-danger btn-sm js-confirm-action',
                                'title' => '取消訂單',
                                'data-confirm' => '確定要取消此訂單嗎？'
                            ]) !!}
                            {!! Form::close() !!}
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">尚無訂單資料</td>
            </tr>
        @endforelse
        </tbody>
    </table>
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
