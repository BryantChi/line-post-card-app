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

    {{-- 啟用金流 --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-toggle-on mr-2"></i>啟用的金流</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>啟用的付款方式(前台可選擇)</label>
                <div>
                    @foreach($allGatewayCodes as $code)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="active_payment_gateways[]" value="{{ $code }}"
                                   id="active_gateway_{{ $code }}"
                                   class="form-check-input"
                                   {{ in_array($code, $activeGateways) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active_gateway_{{ $code }}">
                                {{ $gatewayLabels[$code] ?? $code }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">至少需勾選一個。未勾選的金流前台不會顯示為付款選項。</small>
            </div>

            <div class="form-group">
                <label for="default_payment_gateway">預設金流(前台預選)</label>
                <select name="default_payment_gateway" id="default_payment_gateway" class="form-control" style="max-width:300px">
                    @foreach($allGatewayCodes as $code)
                        <option value="{{ $code }}" {{ $defaultGateway === $code ? 'selected' : '' }}>
                            {{ $gatewayLabels[$code] ?? $code }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">必須是啟用中的金流之一。</small>
            </div>
        </div>
    </div>

    {{-- 綠界金流 (ECPay) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>綠界金流 (ECPay) 設定</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="ecpay_mode">綠界金流模式</label>
                <select name="ecpay_mode" id="ecpay_mode" class="form-control" style="max-width:200px">
                    <option value="test" {{ $ecpayMode === 'test' ? 'selected' : '' }}>測試模式</option>
                    <option value="production" {{ $ecpayMode === 'production' ? 'selected' : '' }}>正式模式</option>
                </select>
                <small class="form-text text-muted">測試模式使用綠界沙箱環境，不會實際扣款</small>
            </div>

            <div class="alert {{ $ecpayMode === 'test' ? 'alert-warning' : 'alert-success' }}">
                <i class="fas {{ $ecpayMode === 'test' ? 'fa-flask' : 'fa-check-circle' }}"></i>
                目前綠界環境：<strong>{{ $ecpayMode === 'test' ? '測試模式（沙箱）' : '正式模式' }}</strong>
            </div>

            @if($ecpayMode === 'test')
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                綠界測試信用卡號：4311-9522-2222-2222（有效期限 12/26，安全碼 222，3D 驗證碼 1234）
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

    {{-- 藍新金流 (NewebPay) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>藍新金流 (NewebPay) 設定</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="newebpay_mode">藍新金流模式</label>
                <select name="newebpay_mode" id="newebpay_mode" class="form-control" style="max-width:200px">
                    <option value="test" {{ $newebpayMode === 'test' ? 'selected' : '' }}>測試模式</option>
                    <option value="production" {{ $newebpayMode === 'production' ? 'selected' : '' }}>正式模式</option>
                </select>
                <small class="form-text text-muted">測試模式使用藍新沙箱環境(ccore.newebpay.com),不會實際扣款</small>
            </div>

            <div class="alert {{ $newebpayMode === 'test' ? 'alert-warning' : 'alert-success' }}">
                <i class="fas {{ $newebpayMode === 'test' ? 'fa-flask' : 'fa-check-circle' }}"></i>
                目前藍新環境：<strong>{{ $newebpayMode === 'test' ? '測試模式(沙箱)' : '正式模式' }}</strong>
            </div>

            @if($newebpayMode === 'test')
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                藍新測試信用卡號：4000-2211-1111-1111(任意有效期 + 任意安全碼即可通過授權)
            </div>
            @endif

            <hr>

            {{-- 測試環境憑證 --}}
            <h5 class="mb-3">
                <i class="fas fa-flask text-warning mr-1"></i>測試環境憑證
                @if($newebpayTestCredsInDb)
                    <span class="badge badge-success ml-2">已設定</span>
                @else
                    <span class="badge badge-secondary ml-2">未設定</span>
                @endif
            </h5>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Merchant ID</label>
                    <input type="text" name="newebpay_test_merchant_id" class="form-control"
                           placeholder="{{ $newebpayTestCredsInDb ? str_repeat('*', max(0, strlen($newebpayTestCreds['merchant_id']) - 4)) . substr($newebpayTestCreds['merchant_id'], -4) : '請輸入藍新測試 MerchantID' }}"
                           autocomplete="off">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash Key</label>
                    <input type="password" name="newebpay_test_hash_key" class="form-control"
                           placeholder="{{ $newebpayTestCredsInDb ? '已設定(' . strlen($newebpayTestCreds['hash_key']) . ' 字元)' : '請輸入 Hash Key' }}"
                           autocomplete="new-password">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash IV</label>
                    <input type="password" name="newebpay_test_hash_iv" class="form-control"
                           placeholder="{{ $newebpayTestCredsInDb ? '已設定(' . strlen($newebpayTestCreds['hash_iv']) . ' 字元)' : '請輸入 Hash IV' }}"
                           autocomplete="new-password">
                </div>
            </div>
            <small class="form-text text-muted mb-3">留空表示不修改。需於藍新測試後台 (https://cwww.newebpay.com) 申請測試帳號取得。</small>

            <hr>

            {{-- 正式環境憑證 --}}
            <h5 class="mb-3">
                <i class="fas fa-shield-alt text-success mr-1"></i>正式環境憑證
                @if($newebpayProdCredsInDb)
                    <span class="badge badge-success ml-2">已設定</span>
                @else
                    <span class="badge badge-danger ml-2">未設定</span>
                @endif
            </h5>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Merchant ID</label>
                    <input type="text" name="newebpay_prod_merchant_id" class="form-control"
                           placeholder="{{ $newebpayProdCredsInDb ? str_repeat('*', max(0, strlen($newebpayProdCreds['merchant_id']) - 4)) . substr($newebpayProdCreds['merchant_id'], -4) : '未設定' }}"
                           autocomplete="off">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash Key</label>
                    <input type="password" name="newebpay_prod_hash_key" class="form-control"
                           placeholder="{{ $newebpayProdCredsInDb ? '已設定(' . strlen($newebpayProdCreds['hash_key']) . ' 字元)' : '未設定' }}"
                           autocomplete="new-password">
                </div>
                <div class="form-group col-md-4">
                    <label>Hash IV</label>
                    <input type="password" name="newebpay_prod_hash_iv" class="form-control"
                           placeholder="{{ $newebpayProdCredsInDb ? '已設定(' . strlen($newebpayProdCreds['hash_iv']) . ' 字元)' : '未設定' }}"
                           autocomplete="new-password">
                </div>
            </div>
            <small class="form-text text-muted">留空表示不修改。切換至正式模式前必須設定 Merchant ID、Hash Key、Hash IV,並至藍新後台設定我方 server IP 白名單。</small>
        </div>
    </div>

    {{-- 訂閱方案費用設定 --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-coins mr-2"></i>訂閱方案費用設定</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="first_time_design_fee">第一次設計費（NT$）</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">NT$</span>
                        </div>
                        <input type="number" name="first_time_design_fee" id="first_time_design_fee"
                               class="form-control" min="0" max="999999"
                               value="{{ old('first_time_design_fee', $firstTimeDesignFee) }}" required>
                    </div>
                    <small class="form-text text-muted">第一次設計名片所收取的設計費（顯示於訂閱頁備註）</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="reactivation_setup_fee">重新開通設定費（NT$）</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">NT$</span>
                        </div>
                        <input type="number" name="reactivation_setup_fee" id="reactivation_setup_fee"
                               class="form-control" min="0" max="999999"
                               value="{{ old('reactivation_setup_fee', $reactivationSetupFee) }}" required>
                    </div>
                    <small class="form-text text-muted">會員到期未續約後再次開通所收取的設定費</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="card_retention_days">名片保留天數</label>
                    <div class="input-group">
                        <input type="number" name="card_retention_days" id="card_retention_days"
                               class="form-control" min="0" max="3650"
                               value="{{ old('card_retention_days', $cardRetentionDays) }}" required>
                        <div class="input-group-append">
                            <span class="input-group-text">天</span>
                        </div>
                    </div>
                    <small class="form-text text-muted">到期未續約後資料保留可瀏覽的天數</small>
                </div>
            </div>
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
