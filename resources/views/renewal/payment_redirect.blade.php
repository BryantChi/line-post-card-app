@extends('layouts.app')
@section('content')
<div class="content px-3 text-center py-5">
    <p><i class="fas fa-spinner fa-spin fa-3x"></i></p>
    <p>正在跳轉至{{ $gatewayLabel ?? '金流' }}付款頁面,請稍候...</p>
    {!! $formHtml !!}
</div>
@endsection

@push('page_scripts')
<script @cspNonce>
    // Driver 產生的 <form id="payment-gateway-form"> 不含 inline auto-submit script
    // (避免 CSP 政策封鎖),此處以帶有 CSP nonce 的 script 手動觸發送出
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('payment-gateway-form');
        if (form) {
            form.submit();
        }
    });
</script>
@endpush
