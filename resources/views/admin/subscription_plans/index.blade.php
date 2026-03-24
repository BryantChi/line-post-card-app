@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>訂閱方案管理</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('admin.subscriptionPlans.create') }}">
                        <i class="fas fa-plus"></i> 新增方案
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="search-bar">
                <form action="{{ route('admin.subscriptionPlans.index') }}" method="GET" class="search-form">
                    <div class="search-input-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="keyword" class="form-control"
                               placeholder="搜尋方案名稱或說明..."
                               value="{{ request('keyword') }}">
                    </div>
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i> 搜尋
                    </button>
                    @if(request('keyword'))
                        <a href="{{ route('admin.subscriptionPlans.index') }}" class="btn btn-reset">
                            <i class="fas fa-redo"></i> 重置
                        </a>
                    @endif
                </form>
            </div>

            <div class="card-body p-0">
                @include('admin.subscription_plans.table')

                <div class="card-footer clearfix">
                    <div class="float-right">
                        @include('adminlte-templates::common.paginate', ['records' => $plans])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
