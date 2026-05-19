@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>編輯訂閱方案</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')
        @include('flash::message')

        <div class="card">
            {!! Form::model($plan, ['route' => ['admin.subscriptionPlans.update', $plan->id], 'method' => 'patch', 'files' => true, 'enctype' => 'multipart/form-data']) !!}

            <div class="card-body">
                <div class="row">
                    @include('admin.subscription_plans.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('更新', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('admin.subscriptionPlans.index') }}" class="btn btn-default">取消</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
