@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>會員續約</h1>
    </div>
</section>
<div class="content px-3">
    @include('flash::message')

    {{-- 到期資訊卡片 --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5>帳號到期資訊</h5>
            @if($user->expires_at)
                @if($daysUntilExpiry < 0)
                    <div class="alert alert-danger">您的帳號已於 {{ $user->expires_at->format('Y-m-d') }} 過期</div>
                @elseif($daysUntilExpiry <= 7)
                    <div class="alert alert-danger">帳號將於 {{ $user->expires_at->format('Y-m-d') }} 到期（剩餘 {{ $daysUntilExpiry }} 天）</div>
                @elseif($daysUntilExpiry <= 30)
                    <div class="alert alert-warning">帳號將於 {{ $user->expires_at->format('Y-m-d') }} 到期（剩餘 {{ $daysUntilExpiry }} 天）</div>
                @else
                    <div class="alert alert-info">帳號到期日：{{ $user->expires_at->format('Y-m-d') }}（剩餘 {{ $daysUntilExpiry }} 天）</div>
                @endif
            @else
                <div class="alert alert-info">您的帳號無到期日限制</div>
            @endif
        </div>
    </div>

    {{-- 有 pending 訂單時顯示提醒 --}}
    @if($pendingOrder)
        <div class="alert alert-info">
            您有一筆待付款訂單（{{ $pendingOrder->order_no }}，方案：{{ $pendingOrder->plan->name }}），
            請先完成付款或取消後再建立新訂單。
            @if($pendingOrder->payment_method === 'bank_transfer')
                <a href="{{ route('renewal.bank-transfer', $pendingOrder->id) }}" class="btn btn-sm btn-primary ml-2">查看匯款資訊</a>
            @elseif($pendingOrder->payment_method === 'ecpay_credit')
                <a href="{{ route('renewal.ecpay-redirect', $pendingOrder->id) }}" class="btn btn-sm btn-primary ml-2">繼續付款</a>
            @endif
        </div>
    @endif

    {{-- 方案選擇（有 pending 訂單時停用） --}}
    @if(!$pendingOrder)
    <div class="card">
        <div class="card-header"><h5>選擇續約方案</h5></div>
        <div class="card-body">
            {!! Form::open(['route' => 'renewal.create-order', 'method' => 'POST']) !!}

            <div class="row mb-4">
                @foreach($plans as $plan)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="plan_{{ $plan->id }}" name="plan_id"
                                       value="{{ $plan->id }}" class="custom-control-input" required>
                                <label class="custom-control-label h5" for="plan_{{ $plan->id }}">
                                    {{ $plan->name }}
                                </label>
                            </div>
                            <p class="h3 text-primary">NT$ {{ number_format($plan->price) }}</p>
                            <p class="text-muted">有效期 {{ $plan->duration_days }} 天</p>
                            @if($plan->description)
                                <p class="small">{{ $plan->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="form-group">
                <label>付款方式</label>
                <select name="payment_method" class="form-control" required>
                    <option value="">請選擇付款方式</option>
                    <option value="ecpay_credit">信用卡（綠界金流）</option>
                    <option value="bank_transfer">匯款</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-cart"></i> 立即續約
            </button>
            {!! Form::close() !!}
        </div>
    </div>
    @endif

    {{-- 訂單歷史連結 --}}
    <div class="mt-3">
        <a href="{{ route('renewal.history') }}" class="btn btn-secondary">
            <i class="fas fa-history"></i> 查看訂單歷史
        </a>
    </div>
</div>
@endsection
