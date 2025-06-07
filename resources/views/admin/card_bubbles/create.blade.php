
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        新增電子名片-卡片 - {{ $card->title }}
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::open(['route' => ['admin.businessCards.bubbles.store', $card->id], 'files' => true, 'enctype' => 'multipart/form-data']) !!}

            <div class="card-body">
                <div class="row">
                    @include('admin.card_bubbles.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('admin.businessCards.bubbles.index', $card->id) }}" class="btn btn-default">取消</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
