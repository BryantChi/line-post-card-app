@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>我的電子名片</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('admin.businessCards.create') }}">
                        <i class="fa fa-plus"></i> 新增電子名片
                    </a>

                    @if(Auth::user()->isMainUser() || Auth::user()->isSuperAdmin())
                    <a class="btn btn-info float-right mr-2"
                       href="{{ route('admin.all-cards') }}">
                        <i class="fa fa-users"></i> 查看所有電子名片
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-0">
                @include('admin.business_cards.table')

                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{-- @include('adminlte-templates::common.paginator', ['paginator' => $businessCards]) --}}
                        @include('adminlte-templates::common.paginate', ['records' => $businessCards])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
