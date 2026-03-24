@extends('layouts.app')
@section('content')
<div class="content px-3 text-center py-5">
    <p><i class="fas fa-spinner fa-spin fa-3x"></i></p>
    <p>正在跳轉至綠界金流付款頁面，請稍候...</p>
    {!! $formHtml !!}
</div>
@endsection
