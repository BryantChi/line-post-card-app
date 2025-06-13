
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        氣泡卡片詳情
                    </h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a class="btn btn-default my-1"
                           href="{{ route('admin.businessCards.bubbles.index', $card->id) }}">
                            <i class="fa fa-arrow-left"></i> 返回
                        </a>
                        <a class="btn btn-primary my-1"
                           href="{{ route('admin.businessCards.bubbles.edit', [$card->id, $bubble->id]) }}">
                            <i class="fa fa-edit"></i> 編輯
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>基本資訊</h5>
                    </div>
                    <div class="card-body">
                        <!-- ID Field -->
                        <div class="form-group">
                            {!! Form::label('id', '編號:') !!}
                            <p>{{ $bubble->id }}</p>
                        </div>

                        <!-- 模板 Field -->
                        <div class="form-group">
                            {!! Form::label('template_id', '使用模板:') !!}
                            <p>{{ optional($bubble->template)->name }} (ID: {{ $bubble->template_id }})</p>
                        </div>

                        <!-- 標題 Field -->
                        <div class="form-group">
                            {!! Form::label('title', '標題:') !!}
                            <p>{{ $bubble->title }}</p>
                        </div>

                        <!-- 副標題 Field -->
                        <div class="form-group">
                            {!! Form::label('subtitle', '副標題:') !!}
                            <p>{{ $bubble->subtitle ?: '無' }}</p>
                        </div>

                        <!-- 排序 Field -->
                        <div class="form-group">
                            {!! Form::label('order', '排序:') !!}
                            <p>{{ $bubble->order }}</p>
                        </div>

                        <!-- 狀態 Field -->
                        <div class="form-group">
                            {!! Form::label('active', '狀態:') !!}
                            <p>
                                <span class="badge badge-{{ $bubble->active ? 'success' : 'danger' }}">
                                    {{ $bubble->active ? '啟用' : '停用' }}
                                </span>
                            </p>
                        </div>

                        <!-- 建立時間 Field -->
                        <div class="form-group">
                            {!! Form::label('created_at', '建立時間:') !!}
                            <p>{{ $bubble->created_at }}</p>
                        </div>

                        <!-- 更新時間 Field -->
                        <div class="form-group">
                            {!! Form::label('updated_at', '更新時間:') !!}
                            <p>{{ $bubble->updated_at }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>內容與預覽</h5>
                    </div>
                    <div class="card-body">
                        <!-- 圖片 Field -->
                        <div class="form-group">
                            {!! Form::label('image', '圖片:') !!}
                            @if($bubble->image)
                                <div class="mt-2 mb-3">
                                    <img src="{{ asset('uploads/' . $bubble->image) }}"
                                         alt="Bubble Image" class="img-fluid rounded" style="max-height: 200px;">
                                </div>
                            @else
                                <p>無圖片</p>
                            @endif
                        </div>

                        <!-- 內容 Field -->
                        <div class="form-group">
                            {!! Form::label('content', '內容:') !!}
                            <div class="border p-3 rounded bg-light">
                                {!! nl2br(e($bubble->content)) ?: '無內容' !!}
                            </div>
                        </div>

                        <!-- 欄位資料 Field -->
                        @if(is_array($bubble->bubble_data) && count($bubble->bubble_data) > 0)
                            <div class="form-group">
                                {!! Form::label('bubble_data', '欄位資料:') !!}
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>欄位名稱</th>
                                                <th>值</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bubble->bubble_data as $key => $value)
                                                <tr>
                                                    <td>{{ $key }}</td>
                                                    <td>
                                                        @if(is_array($value))
                                                            <pre>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5>LINE Flex 訊息</h5>
                    </div>
                    <div class="card-body">
                        @if(is_array($bubble->json_content) || (is_string($bubble->json_content) && !empty($bubble->json_content)))
                            <div class="border p-3 rounded">
                                <pre class="bg-light p-3" style="min-width: 400px; max-height: 500px; overflow: auto;">{{ json_encode(
                                        is_array($bubble->json_content)
                                            ? $bubble->json_content
                                            : json_decode($bubble->json_content, true),
                                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                                    ) }}</pre>
                            </div>
                            <div class="mt-3">
                                <p class="text-muted">請使用 LINE 官方的 <a href="https://developers.line.biz/flex-simulator/" target="_blank">Flex Message Simulator</a> 查看實際效果。</p>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                無 Flex 訊息資料
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
