@extends('layouts.app')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>系統設定</h1>
    </div>
</section>
<div class="content px-3">
    @include('flash::message')
    @include('adminlte-templates::common.errors')

    {!! Form::open(['route' => 'admin.systemSettings.update', 'method' => 'PATCH']) !!}

    {{-- 續約功能設定 --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i>續約功能</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="renewal_enabled">會員自助續約功能</label>
                <select name="renewal_enabled" id="renewal_enabled" class="form-control" style="max-width:200px">
                    <option value="true" {{ $renewalEnabled ? 'selected' : '' }}>啟用</option>
                    <option value="false" {{ !$renewalEnabled ? 'selected' : '' }}>停用</option>
                </select>
                <small class="form-text text-muted">停用後，子帳號無法自行續約，需由管理員代為處理</small>
            </div>

            <div class="form-group">
                <label for="renewal_test_user_ids">測試模式指定帳號</label>
                <select name="renewal_test_user_ids[]" id="renewal_test_user_ids"
                        class="form-control select2" multiple
                        style="width:100%; max-width:500px">
                    @foreach($subUsers as $user)
                        <option value="{{ $user->id }}"
                            {{ in_array($user->id, $testUserIds) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    測試模式下，只有被選取的帳號可以使用續約功能。正式模式下此設定不影響。
                </small>
            </div>
        </div>
    </div>

    {{-- 金流設定 --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>金流設定</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="ecpay_mode">金流模式</label>
                <select name="ecpay_mode" id="ecpay_mode" class="form-control" style="max-width:200px">
                    <option value="test" {{ $ecpayMode === 'test' ? 'selected' : '' }}>測試模式</option>
                    <option value="production" {{ $ecpayMode === 'production' ? 'selected' : '' }}>正式模式</option>
                </select>
                <small class="form-text text-muted">測試模式使用綠界沙箱環境，不會實際扣款</small>
            </div>

            <div class="alert {{ $ecpayMode === 'test' ? 'alert-warning' : 'alert-success' }}">
                <i class="fas {{ $ecpayMode === 'test' ? 'fa-flask' : 'fa-check-circle' }}"></i>
                目前金流環境：<strong>{{ $ecpayMode === 'test' ? '測試模式（沙箱）' : '正式模式' }}</strong>
            </div>

            @if($ecpayMode === 'test')
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                測試信用卡號：4311-9522-2222-2222（有效期限 12/26，安全碼 222，3D 驗證碼 1234）
            </div>
            @endif

            <hr>

            {{-- 測試環境憑證 --}}
            <h5 class="mb-3">
                <i class="fas fa-flask text-warning mr-1"></i>測試環境憑證
                @if($testCredsInDb)
                    <span class="badge badge-success ml-2">已設定</span>
                @else
                    <span class="badge badge-secondary ml-2">使用內建預設值</span>
                @endif
            </h5>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Merchant ID</label>
                    <input type="text" name="ecpay_test_merchant_id" class="form-control"
                           placeholder="{{ $testCredsInDb ? str_repeat('*', max(0, strlen($testCreds['merchant_id']) - 4)) . substr($testCreds['merchant_id'], -4) : '使用預設值' }}"
                           autocomplete="off">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash Key</label>
                    <input type="password" name="ecpay_test_hash_key" class="form-control"
                           placeholder="{{ $testCredsInDb ? '已設定（' . strlen($testCreds['hash_key']) . ' 字元）' : '使用預設值' }}"
                           autocomplete="new-password">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash IV</label>
                    <input type="password" name="ecpay_test_hash_iv" class="form-control"
                           placeholder="{{ $testCredsInDb ? '已設定（' . strlen($testCreds['hash_iv']) . ' 字元）' : '使用預設值' }}"
                           autocomplete="new-password">
                </div>
            </div>
            <div class="form-group">
                <label>Gateway URL</label>
                <input type="text" name="ecpay_test_gateway_url" class="form-control" style="max-width:600px"
                       placeholder="{{ $testCredsInDb ? $testCreds['gateway_url'] : '使用預設值' }}"
                       autocomplete="off">
            </div>
            <small class="form-text text-muted mb-3">留空表示不修改。測試環境有內建預設值，通常不需手動填寫。</small>

            <hr>

            {{-- 正式環境憑證 --}}
            <h5 class="mb-3">
                <i class="fas fa-shield-alt text-success mr-1"></i>正式環境憑證
                @if($prodCredsInDb)
                    <span class="badge badge-success ml-2">已設定</span>
                @else
                    <span class="badge badge-danger ml-2">未設定</span>
                @endif
            </h5>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Merchant ID</label>
                    <input type="text" name="ecpay_prod_merchant_id" class="form-control"
                           placeholder="{{ $prodCredsInDb ? str_repeat('*', max(0, strlen($prodCreds['merchant_id']) - 4)) . substr($prodCreds['merchant_id'], -4) : '未設定' }}"
                           autocomplete="off">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash Key</label>
                    <input type="password" name="ecpay_prod_hash_key" class="form-control"
                           placeholder="{{ $prodCredsInDb ? '已設定（' . strlen($prodCreds['hash_key']) . ' 字元）' : '未設定' }}"
                           autocomplete="new-password">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash IV</label>
                    <input type="password" name="ecpay_prod_hash_iv" class="form-control"
                           placeholder="{{ $prodCredsInDb ? '已設定（' . strlen($prodCreds['hash_iv']) . ' 字元）' : '未設定' }}"
                           autocomplete="new-password">
                </div>
            </div>
            <div class="form-group">
                <label>Gateway URL</label>
                <input type="text" name="ecpay_prod_gateway_url" class="form-control" style="max-width:600px"
                       placeholder="{{ $prodCredsInDb ? $prodCreds['gateway_url'] : '未設定（預設 https://payment.ecpay.com.tw/...）' }}"
                       autocomplete="off">
            </div>
            <small class="form-text text-muted">留空表示不修改。切換至正式模式前必須設定 Merchant ID、Hash Key、Hash IV。</small>
        </div>
    </div>

    {{-- 儲存按鈕 --}}
    <div class="card">
        <div class="card-body">
            {!! Form::submit('儲存設定', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>

    {!! Form::close() !!}
</div>
@endsection

@push('page_scripts')
<script @cspNonce>
$(function () {
    $('#renewal_test_user_ids').select2({
        language: 'zh-TW',
        placeholder: '請選擇測試帳號...',
        allowClear: true,
        width: 'style'
    });
});
</script>
@endpush
