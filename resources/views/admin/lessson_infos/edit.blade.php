@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        學習中心
                        <small>編輯</small>
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::model($lesssonInfo, ['route' => ['admin.lessonInfos.update', $lesssonInfo->id], 'method' => 'patch', 'files' => true]) !!}

            <div class="card-body">
                <div class="row">
                    @include('admin.lessson_infos.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('admin.lessonInfos.index') }}" class="btn btn-default"> 取消 </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
