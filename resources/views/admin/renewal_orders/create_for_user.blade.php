@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>為會員建立續約訂單</h1>
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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    會員：<strong>{{ $subUser->name }}</strong>
                    <small class="text-muted ml-2">{{ $subUser->email }}</small>
                </h3>
                <div class="card-tools">
                    @if($subUser->expires_at)
                        <span class="badge badge-{{ $subUser->expires_at->isPast() ? 'danger' : 'info' }}">
                            目前到期日：{{ $subUser->expires_at->format('Y-m-d') }}
                        </span>
                    @else
                        <span class="badge badge-secondary">尚無到期日</span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                {!! Form::open(['route' => ['admin.renewalOrders.storeForUser', $subUser->id], 'method' => 'POST']) !!}

                    <div class="form-group">
                        <label class="font-weight-bold">選擇訂閱方案 <span class="text-danger">*</span></label>
                        <div class="row">
                            @forelse($plans as $plan)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 plan-card" style="cursor:pointer;">
                                        <div class="card-body text-center">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="plan_{{ $plan->id }}" name="plan_id"
                                                       value="{{ $plan->id }}"
                                                       class="custom-control-input"
                                                       {{ old('plan_id') == $plan->id ? 'checked' : '' }}>
                                                <label class="custom-control-label w-100" for="plan_{{ $plan->id }}">
                                                    <h5 class="mb-1">{{ $plan->name }}</h5>
                                                    <div class="h3 text-primary mb-1">NT$ {{ number_format($plan->price) }}</div>
                                                    <div class="text-muted">有效 {{ $plan->duration_days }} 天</div>
                                                    @if($plan->description)
                                                        <small class="text-muted d-block mt-2">{{ $plan->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted">目前沒有啟用中的訂閱方案</p>
                                </div>
                            @endforelse
                        </div>
                        @error('plan_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="payment_method" class="font-weight-bold">付款方式 <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" style="max-width: 300px;">
                            <option value="">請選擇付款方式</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>匯款</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>現金</option>
                            <option value="ecpay_credit" {{ old('payment_method') === 'ecpay_credit' ? 'selected' : '' }}>信用卡（綠界）</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="admin_note">管理員備註</label>
                        <textarea name="admin_note" id="admin_note" rows="3"
                                  class="form-control @error('admin_note') is-invalid @enderror"
                                  placeholder="選填，用於記錄付款相關說明..."
                                  style="max-width: 600px;">{{ old('admin_note') }}</textarea>
                        @error('admin_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-4">
                        {!! Form::submit('建立訂單', ['class' => 'btn btn-primary']) !!}
                        <a href="{{ route('admin.renewalOrders.index') }}" class="btn btn-default ml-2">取消</a>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>

@push('styles')
<style>
.plan-card:hover {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}
.custom-control-input:checked ~ .custom-control-label .card {
    border-color: #007bff;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // 點擊整個方案卡片時選中對應 radio
    $('.plan-card').on('click', function() {
        var radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
    });
});
</script>
@endpush
@endsection
