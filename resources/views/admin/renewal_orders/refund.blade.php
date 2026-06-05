@extends('layouts.app')
@section('content')
<div class="content px-3 py-3">
    <h1 class="mb-3">訂單退款 — {{ $order->order_no }}</h1>

    <div class="card mb-3">
        <div class="card-body">
            <p>用戶:{{ $order->user->name ?? $order->user->email }}</p>
            <p>方案:{{ $order->plan->name }}（{{ $order->plan->duration_days }} 天）</p>
            <p>原始金額:NT$ {{ number_format($order->amount) }}</p>
            <p>已退金額:NT$ {{ number_format($order->refunded_amount) }}</p>
            <p><strong>可退餘額:NT$ {{ number_format($refundableAmount) }}</strong></p>
            <p>付款方式:{{ $order->getPaymentMethodLabel() }}</p>
        </div>
    </div>

    {!! Form::open(['route' => ['admin.renewalOrders.refund', $order->id], 'method' => 'POST']) !!}
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>退款金額（最多 NT$ {{ number_format($refundableAmount) }}）</label>
                <input type="number" name="amount" class="form-control" style="max-width:240px"
                       min="1" max="{{ $refundableAmount }}" value="{{ $refundableAmount }}" required>
            </div>

            @if($order->payment_method !== 'bank_transfer')
            <div class="form-group">
                <label>退款動作（系統建議:<strong>{{ $suggestedAction }}</strong>，可手動調整）</label>
                <select name="action" class="form-control" style="max-width:280px">
                    <option value="refund" {{ $suggestedAction === 'refund' ? 'selected' : '' }}>退款（已請款/關帳）</option>
                    <option value="void" {{ $suggestedAction === 'void' ? 'selected' : '' }}>取消授權/作廢（未請款/關帳）</option>
                </select>
            </div>
            @else
            <input type="hidden" name="action" value="manual">
            <div class="alert alert-info">銀行轉帳為人工退款,送出後僅記錄退款,請另行線下匯款。</div>
            @endif

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="rollback_expiration" value="1" class="form-check-input" id="rollback" checked>
                    <label class="form-check-label" for="rollback">扣回服務期（該方案 {{ $order->plan->duration_days }} 天）</label>
                </div>
            </div>

            <div class="form-group">
                <label>退款原因（必填）</label>
                <input type="text" name="reason" class="form-control" maxlength="255" required>
            </div>

            <button type="submit" class="btn btn-danger" onclick="return confirm('確定要退款嗎?此動作會實際向金流商發出退款。')">
                <i class="fas fa-undo"></i> 確認退款
            </button>
            <a href="{{ route('admin.renewalOrders.show', $order->id) }}" class="btn btn-secondary">取消</a>
        </div>
    </div>
    {!! Form::close() !!}
</div>
@endsection
