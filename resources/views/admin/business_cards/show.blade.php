@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>AI數位名片詳情</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <div class="float-right">
                        {{-- 操作導覽按鈕 --}}
                        <button class="btn btn-info btn-sm mr-2 my-1" onclick="startShowCardTour()">
                            <i class="fa fa-question-circle"></i> 操作導覽
                        </button>
                        <a class="btn btn-default my-1"
                           href="{{ route('admin.businessCards.index') }}"
                           data-step="1" data-intro="點擊這裡返回AI數位名片列表頁面。">
                            <i class="fa fa-arrow-left"></i> 返回
                        </a>
                        <a class="btn btn-primary my-1"
                           href="{{ route('admin.businessCards.edit', $businessCard->id) }}"
                           data-step="2" data-intro="點擊這裡編輯此AI數位名片的基本資訊。">
                            <i class="fa fa-edit"></i> 編輯
                        </a>
                        <a class="btn btn-info my-1"
                           href="{{ route('admin.businessCards.bubbles.index', $businessCard->id) }}"
                           data-step="3" data-intro="點擊這裡管理此AI數位名片包含的所有「卡片」內容。">
                            <i class="fas fa-th-large"></i> 管理AI數位名片-卡片
                        </a>
                        <a class="btn btn-success my-1"
                           href="{{ $businessCard->getShareUrl() }}" target="_blank"
                           data-step="4" data-intro="點擊這裡在新分頁中預覽此AI數位名片的實際分享效果。">
                            <i class="fas fa-share-alt"></i> 預覽
                        </a>
                        <div class="btn-group my-1">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    data-step="5" data-intro="點擊這裡可以下載本週、本月或自訂區間的點閱/分享數據報表。">
                                <i class="fas fa-download"></i> 下載報表
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('admin.businessCards.report.weekly', $businessCard->id) }}">
                                    <i class="fas fa-calendar-week"></i> 本週報表
                                </a>
                                <a class="dropdown-item" href="{{ route('admin.businessCards.report.monthly', $businessCard->id) }}">
                                    <i class="fas fa-calendar-alt"></i> 本月報表
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#customReportModal">
                                    <i class="fas fa-calendar-plus"></i> 自訂區間報表
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-6">
                <div class="card" data-step="5" data-intro="這裡是AI數位名片的基本資訊，包括標題、狀態、分享連結等。">
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
                                        <th>AI數位名片-卡片數量</th>
                                        <td>{{ $bubbles->count() }}</td>
                                    </tr>
                                    <tr>
                                        <th>點閱率</th>
                                        <td>{{ $businessCard->views ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <th>分享數</th>
                                        <td>{{ $businessCard->shares ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <th>分享連結</th>
                                        <td data-step="6" data-intro="這是此AI數位名片的公開分享網址。您可以複製此連結並分享給他人。點擊右側的複製按鈕即可複製。">
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
                <div class="card" data-step="7" data-intro="這裡列出了此AI數位名片目前包含的所有「卡片」。您可以快速查看它們的標題和狀態。">
                    <div class="card-header">
                        <h3 class="card-title">AI數位名片-卡片列表</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.businessCards.bubbles.index', $businessCard->id) }}" class="btn btn-sm btn-primary"
                               data-step="8" data-intro="同樣可以點擊這裡進入「管理AI數位名片-卡片」頁面，進行新增、編輯或排序。">
                                <i class="fas fa-th-large"></i> 管理AI數位名片-卡片
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($bubbles->isEmpty())
                            <div class="alert alert-warning m-3">
                                尚未添加任何氣泡卡片。點擊上方「管理AI數位名片-卡片」按鈕開始創建。
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

                <div class="card mt-4" data-step="9" data-intro="這裡是此AI數位名片最終生成的 LINE Flex Message JSON 結構。您可以複製此結構到 LINE Flex Message Simulator 中進行更精確的預覽。">
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
                                尚未生成 Flex JSON。請先添加AI數位名片-卡片，然後點擊「重新生成 Flex JSON」按鈕。
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 自訂區間報表 Modal -->
    <div class="modal fade" id="customReportModal" tabindex="-1" role="dialog" aria-labelledby="customReportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.businessCards.report.custom', $businessCard->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="customReportModalLabel">下載自訂區間報表</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="start_date">起始日期</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required
                                   max="{{ date('Y-m-d') }}"
                                   value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="end_date">結束日期</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required
                                   max="{{ date('Y-m-d') }}"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            請選擇要下載報表的日期區間。報表將包含該區間內每日的點閱數和分享數統計。
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-download"></i> 下載報表
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>
    <style> .shepherd-text { max-width: 400px; } .shepherd-button { margin: 0 5px; } </style>
@endpush

@push('page_scripts')
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
<script>
    function copyShareUrl() {
        var copyText = document.getElementById("share-url");
        copyText.select();
        document.execCommand("copy");

        // 顯示提示
        swal.fire({
            title: '分享連結已複製',
            text: '您可以將此連結分享給他人。',
            icon: 'success',
            confirmButtonText: '確定'
        });
        // alert("分享連結已複製到剪貼簿！");
    }

    function startShowCardTour() {
        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shepherd-theme-arrows',
                scrollTo: { behavior: 'smooth', block: 'center' },
                cancelIcon: { enabled: true, label: '關閉導覽' }
            }
        });

        const stepData = [
            { selector: '[data-step="1"]', defaultIntro: "<strong>返回：</strong><br>點擊這裡返回AI數位名片列表頁面。" },
            { selector: '[data-step="2"]', defaultIntro: "<strong>編輯：</strong><br>點擊這裡編輯此AI數位名片的基本資訊，如名稱、描述等。" },
            { selector: '[data-step="3"]', defaultIntro: "<strong>管理卡片：</strong><br>點擊這裡管理此AI數位名片包含的所有「卡片」內容。您可以在該頁面新增、編輯、排序或刪除卡片。" },
            { selector: '[data-step="4"]', defaultIntro: "<strong>預覽：</strong><br>點擊這裡在新分頁中預覽此AI數位名片的實際分享效果。" },
            { selector: '[data-step="5"]', defaultIntro: "<strong>基本資訊區塊：</strong><br>這裡是AI數位名片的基本資訊，包括標題、狀態、分享連結等。" },
            { selector: '[data-step="6"]', defaultIntro: "<strong>分享連結：</strong><br>這是此AI數位名片的公開分享網址。您可以複製此連結並分享給他人。點擊右側的複製按鈕即可複製。" },
            { selector: '[data-step="7"]', defaultIntro: "<strong>卡片列表區塊：</strong><br>這裡列出了此AI數位名片目前包含的所有「卡片」。您可以快速查看它們的標題和狀態。" },
            { selector: '[data-step="8"]', defaultIntro: "<strong>管理卡片按鈕：</strong><br>同樣可以點擊這裡進入「管理AI數位名片-卡片」頁面，進行新增、編輯或排序。" },
            { selector: '[data-step="9"]', defaultIntro: "<strong>LINE Flex JSON 預覽：</strong><br>這裡是此AI數位名片最終生成的 LINE Flex Message JSON 結構。您可以複製此結構到 LINE Flex Message Simulator 中進行更精確的預覽。" },
            { selector: '[data-step="10"]', defaultIntro: "<strong>重新生成 Flex JSON：</strong><br>如果卡片內容有變更，或您覺得 JSON 結構有誤，可以點擊此按鈕強制重新生成。" }
        ];

        let currentStepIndex = 0;
        const totalSteps = stepData.filter(s => document.querySelector(s.selector)).length;

        stepData.forEach((s) => {
            const element = document.querySelector(s.selector);
            if (element) {
                currentStepIndex++;
                tour.addStep({
                    text: element.getAttribute('data-intro') || s.defaultIntro,
                    attachTo: { element: element, on: 'auto' },
                    buttons: [
                        {
                            action() { return this.back(); },
                            secondary: true,
                            text: '上一步',
                            classes: currentStepIndex === 1 ? 'shepherd-button-hidden' : ''
                        },
                        {
                            action() { return this.next(); },
                            text: '下一步',
                            classes: currentStepIndex === totalSteps ? 'shepherd-button-hidden' : ''
                        },
                        {
                            action() { return this.complete(); },
                            text: '完成',
                            classes: currentStepIndex === totalSteps ? '' : 'shepherd-button-hidden'
                        }
                    ]
                });
            }
        });

        if (tour.steps.length > 0) {
            tour.start();
        } else {
            alert("沒有可導覽的步驟。");
        }
    }
</script>
@endpush
