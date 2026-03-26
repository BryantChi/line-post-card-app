@extends('layouts.app')
@section('content')
<div class="content px-3 text-center py-5">
    <p><i class="fas fa-spinner fa-spin fa-3x"></i></p>
    <p>正在跳轉至綠界金流付款頁面，請稍候...</p>
    {!! $formHtml !!}
</div>
@endsection

@push('page_scripts')
<script @cspNonce>
    // ECPay SDK (FormWithCmvService) 只產生 <form>，不含 inline auto-submit script
    // 此處使用帶有 CSP nonce 的 script 手動觸發送出，避免 CSP 政策封鎖
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('ecpay-form');
        if (form) {
            form.submit();
        }
    });
</script>
@endpush
