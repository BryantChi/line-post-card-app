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
            <div class="card-header">
                <form action="{{ route('admin.renewalOrders.index') }}" method="GET" class="form-inline flex-wrap" style="gap: 8px;">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control"
                               placeholder="搜尋訂單編號或會員名稱..."
                               value="{{ request('keyword') }}" style="min-width: 220px;">
                    </div>

                    <select name="status" class="form-control">
                        <option value="">全部狀態</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>待付款</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>已付款</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>已取消</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>已逾期</option>
                    </select>

                    @if(Auth::user()->isSuperAdmin())
                    <select name="user_id" class="form-control">
                        <option value="">全部會員</option>
                        @foreach($subUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> 搜尋
                    </button>
                    @if(request()->hasAny(['keyword', 'status', 'user_id']))
                        <a href="{{ route('admin.renewalOrders.index') }}" class="btn btn-secondary">
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
