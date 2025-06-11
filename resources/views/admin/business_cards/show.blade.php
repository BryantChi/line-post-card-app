@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>電子名片詳情</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <button class="btn btn-info btn-sm mr-2" onclick="startShowCardTour()">
                            <i class="fa fa-question-circle"></i> 操作導覽
                        </button>
                        <a class="btn btn-default"
                           href="{{ route('admin.businessCards.index') }}"
                           data-step="1" data-intro="點擊這裡返回電子名片列表頁面。">
                            <i class="fa fa-arrow-left"></i> 返回
                        </a>
                        <a class="btn btn-primary"
                           href="{{ route('admin.businessCards.edit', $businessCard->id) }}"
                           data-step="2" data-intro="點擊這裡編輯此電子名片的基本資訊。">
                            <i class="fa fa-edit"></i> 編輯
                        </a>
                        <a class="btn btn-info"
                           href="{{ route('admin.businessCards.bubbles.index', $businessCard->id) }}"
                           data-step="3" data-intro="點擊這裡管理此電子名片包含的所有「卡片」內容。">
                            <i class="fas fa-th-large"></i> 管理電子名片-卡片
                        </a>
                        <a class="btn btn-success"
                           href="{{ $businessCard->getShareUrl() }}" target="_blank"
                           data-step="4" data-intro="點擊這裡在新分頁中預覽此電子名片的實際分享效果。">
                            <i class="fas fa-share-alt"></i> 預覽
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-6">
                <div class="card" data-step="5" data-intro="這裡是電子名片的基本資訊，包括標題、狀態、分享連結等。">
                    <div class="card-header">
                        <h3 class="card-title">基本資訊</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                @if($businessCard->profile_image)
                                    <div class="text-center mb-3">
                                        <img src="{{ asset('uploads/' . $businessCard->profile_image) }}"
                                             alt="{{ $businessCard->title }}"
                                             class="img-fluid rounded"
                                             style="max-height: 200px;">
                                    </div>
                                @endif

                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 30%">ID</th>
                                        <td>{{ $businessCard->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>標題</th>
                                        <td>{{ $businessCard->title }}</td>
                                    </tr>
                                    @if($businessCard->subtitle)
                                    <tr>
                                        <th>副標題</th>
                                        <td>{{ $businessCard->subtitle }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>所屬用戶</th>
                                        <td>{{ optional($businessCard->user)->name ?? '未知' }}</td>
                                    </tr>
                                    <tr>
                                        <th>狀態</th>
                                        <td>
                                            @if($businessCard->active)
                                                <span class="badge badge-success">啟用</span>
                                            @else
                                                <span class="badge badge-secondary">停用</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>氣泡卡片數量</th>
                                        <td>{{ $bubbles->count() }}</td>
                                    </tr>
                                    <tr>
                                        <th>分享連結</th>
                                        <td data-step="6" data-intro="這是此電子名片的公開分享網址。您可以複製此連結並分享給他人。點擊右側的複製按鈕即可複製。">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="{{ $businessCard->getShareUrl() }}" readonly id="share-url">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>創建時間</th>
                                        <td>{{ $businessCard->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>更新時間</th>
                                        <td>{{ $businessCard->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>

                                @if($businessCard->content)
                                <div class="mt-3">
                                    <h5>介紹內容</h5>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($businessCard->content)) !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card" data-step="7" data-intro="這裡列出了此電子名片目前包含的所有「卡片」。您可以快速查看它們的標題和狀態。">
                    <div class="card-header">
                        <h3 class="card-title">電子名片-卡片列表</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.businessCards.bubbles.index', $businessCard->id) }}" class="btn btn-sm btn-primary"
                               data-step="8" data-intro="同樣可以點擊這裡進入「管理電子名片-卡片」頁面，進行新增、編輯或排序。">
                                <i class="fas fa-th-large"></i> 管理電子名片-卡片
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($bubbles->isEmpty())
                            <div class="alert alert-warning m-3">
                                尚未添加任何氣泡卡片。點擊上方「管理電子名片-卡片」按鈕開始創建。
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">排序</th>
                                            <th>標題</th>
                                            <th>狀態</th>
                                            <th style="width: 100px">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bubbles as $bubble)
                                            <tr>
                                                <td>{{ $bubble->order }}</td>
                                                <td>
                                                    {{ $bubble->title }}
                                                    @if($bubble->image)
                                                        <img src="{{ asset('uploads/' . $bubble->image) }}"
                                                            alt="{{ $bubble->title }}"
                                                            class="img-thumbnail ml-2"
                                                            style="max-height: 30px">
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($bubble->active)
                                                        <span class="badge badge-success">啟用</span>
                                                    @else
                                                        <span class="badge badge-secondary">停用</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.businessCards.bubbles.edit', [$businessCard->id, $bubble->id]) }}"
                                                       class="btn btn-sm btn-default">
                                                       <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('admin.businessCards.bubbles.show', [$businessCard->id, $bubble->id]) }}"
                                                       class="btn btn-sm btn-info">
                                                       <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-4" data-step="9" data-intro="這裡是此電子名片最終生成的 LINE Flex Message JSON 結構。您可以複製此結構到 LINE Flex Message Simulator 中進行更精確的預覽。">
                    <div class="card-header">
                        <h3 class="card-title">LINE Flex 訊息預覽</h3>
                        <div class="card-tools">
                            {!! Form::open(['route' => ['admin.businessCards.regenerateFlexJson', $businessCard->id], 'method' => 'post']) !!}
                                <button type="submit" class="btn btn-sm btn-warning"
                                        data-step="10" data-intro="如果卡片內容有變更，或您覺得 JSON 結構有誤，可以點擊此按鈕強制重新生成。">
                                    <i class="fas fa-sync"></i> 重新生成 Flex JSON
                                </button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                    <div class="card-body">
                        @if($businessCard->flex_json)
                            <div class="border p-3 rounded">
                                <pre class="bg-light p-3" style="max-height: 400px; overflow-y: auto;">{{ json_encode($businessCard->flex_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div class="mt-3">
                                <p class="text-muted">請使用 LINE 官方的 <a href="https://developers.line.biz/flex-simulator/" target="_blank">Flex Message Simulator</a> 查看實際效果。</p>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                尚未生成 Flex JSON。請先添加電子名片-卡片，然後點擊「重新生成 Flex JSON」按鈕。
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('page_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function copyShareUrl() {
        var copyText = document.getElementById("share-url");
        copyText.select();
        document.execCommand("copy");

        // 顯示提示
        alert("分享連結已複製到剪貼簿！");
    }

    function startShowCardTour() {
        const steps = [
            {
                element: document.querySelector('[data-step="1"]'),
                intro: "<strong>返回：</strong><br>點擊這裡返回電子名片列表頁面。"
            },
            {
                element: document.querySelector('[data-step="2"]'),
                intro: "<strong>編輯：</strong><br>點擊這裡編輯此電子名片的基本資訊，如名稱、描述等。"
            },
            {
                element: document.querySelector('[data-step="3"]'),
                intro: "<strong>管理卡片：</strong><br>點擊這裡管理此電子名片包含的所有「卡片」內容。您可以在該頁面新增、編輯、排序或刪除卡片。"
            },
            {
                element: document.querySelector('[data-step="4"]'),
                intro: "<strong>預覽：</strong><br>點擊這裡在新分頁中預覽此電子名片的實際分享效果。"
            },
            {
                element: document.querySelector('[data-step="5"]'),
                intro: "<strong>基本資訊區塊：</strong><br>這裡是電子名片的基本資訊，包括標題、狀態、分享連結等。"
            },
            {
                element: document.querySelector('[data-step="6"]'),
                intro: "<strong>分享連結：</strong><br>這是此電子名片的公開分享網址。您可以複製此連結並分享給他人。點擊右側的複製按鈕即可複製。"
            },
            {
                element: document.querySelector('[data-step="7"]'),
                intro: "<strong>卡片列表區塊：</strong><br>這裡列出了此電子名片目前包含的所有「卡片」。您可以快速查看它們的標題和狀態。"
            },
            {
                element: document.querySelector('[data-step="8"]'),
                intro: "<strong>管理卡片按鈕：</strong><br>同樣可以點擊這裡進入「管理電子名片-卡片」頁面，進行新增、編輯或排序。"
            },
            {
                element: document.querySelector('[data-step="9"]'),
                intro: "<strong>LINE Flex JSON 預覽：</strong><br>這裡是此電子名片最終生成的 LINE Flex Message JSON 結構。您可以複製此結構到 LINE Flex Message Simulator 中進行更精確的預覽。"
            },
            {
                element: document.querySelector('[data-step="10"]'),
                intro: "<strong>重新生成 Flex JSON：</strong><br>如果卡片內容有變更，或您覺得 JSON 結構有誤，可以點擊此按鈕強制重新生成。"
            }
        ];

        introJs().setOptions({
            steps: steps.filter(step => step.element), // 修正篩選條件：確保 step.element 本身存在 (非 null)
            nextLabel: '下一步 &rarr;',
            prevLabel: '&larr; 上一步',
            doneLabel: '完成',
            showBullets: false,
            tooltipClass: 'customTooltip'
        }).start();
    }
</script>
@endpush
