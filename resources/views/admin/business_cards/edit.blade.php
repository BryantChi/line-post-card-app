@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>編輯電子名片</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::model($businessCard, ['route' => ['admin.businessCards.update', $businessCard->id], 'method' => 'patch', 'files' => true]) !!}

            <div class="card-body">
                <div class="row">
                    @include('admin.business_cards.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('admin.businessCards.index') }}" class="btn btn-default">取消</a>
                <a href="{{ route('admin.businessCards.bubbles.index', $businessCard->id) }}" class="btn btn-info">
                    <i class="fas fa-th-large"></i> 管理電子名片-卡片
                </a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
