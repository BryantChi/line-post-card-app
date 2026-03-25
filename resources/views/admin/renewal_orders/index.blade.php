@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>續約訂單</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="search-bar">
                <form action="{{ route('admin.renewalOrders.index') }}" method="GET" class="search-form">
                    <div class="search-input-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="keyword" class="form-control"
                               placeholder="搜尋訂單編號或會員名稱..."
                               value="{{ request('keyword') }}">
                    </div>
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i> 搜尋
                    </button>
                    <select name="status" class="search-select" data-placeholder="全部狀態" style="min-width: 130px;">
                        <option value="">全部狀態</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>待付款</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>已付款</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>已取消</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>已逾期</option>
                    </select>
                    @if(Auth::user()->isSuperAdmin())
                    <select name="user_id" class="search-select" data-placeholder="全部會員" style="min-width: 160px;">
                        <option value="">全部會員</option>
                        @foreach($subUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    @endif
                    @if(request()->hasAny(['keyword', 'status', 'user_id']))
                        <a href="{{ route('admin.renewalOrders.index') }}" class="btn btn-reset">
                            <i class="fas fa-redo"></i> 重置
                        </a>
                    @endif
                </form>
            </div>

            <div class="card-body p-0">
                @include('admin.renewal_orders.table')

                <div class="card-footer clearfix">
                    <div class="float-right">
                        @include('adminlte-templates::common.paginate', ['records' => $orders])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
<script @cspNonce>
$(function () {
    // 狀態下拉（選項少，不需搜尋框）
    $('.search-bar select[name="status"]').select2({
        language: 'zh-TW',
        width: 'resolve',
        minimumResultsForSearch: Infinity,
        allowClear: true,
        placeholder: function () { return $(this).data('placeholder'); }
    }).on('change', function () {
        $(this).closest('form').submit();
    });

    // 會員下拉（選項多，保留搜尋框）
    $('.search-bar select[name="user_id"]').select2({
        language: 'zh-TW',
        width: 'resolve',
        allowClear: true,
        placeholder: function () { return $(this).data('placeholder'); }
    }).on('change', function () {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
