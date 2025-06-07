@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>建立電子名片</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::open(['route' => 'admin.businessCards.store', 'files' => true]) !!}

            <div class="card-body">
                <div class="row">
                    @include('admin.business_cards.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('admin.businessCards.index') }}" class="btn btn-default">取消</a>
                <div class="float-right text-muted">建立後，您可以繼續添加電子名片-卡片</div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
