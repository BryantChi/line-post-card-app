@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        {{ $card->title }} - 電子名片-卡片管理
                    </h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a class="btn btn-primary"
                           href="{{ route('admin.businessCards.bubbles.create', $card->id) }}">
                            <i class="fa fa-plus"></i> 新增電子名片-卡片
                        </a>
                        <a class="btn btn-default"
                           href="{{ route('admin.businessCards.index') }}">
                            <i class="fa fa-arrow-left"></i> 返回
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card">
            <div class="card-body">
                @if($bubbles->isEmpty())
                    <div class="alert alert-info">
                        尚未添加任何電子名片-卡片。點擊上方「新增電子名片-卡片」按鈕開始創建。
                    </div>
                @else
                    {{-- 提示最多10張卡片 --}}
                    @if($bubbles->count() >= 10)
                        <div class="alert alert-info">
                            注意：目前已達到電子名片-卡片的最大數量限制（10張）。請考慮刪除不需要的卡片。
                        </div>
                    @endif
                    {{-- 提示限制10張卡片 --}}
                    <div class="alert alert-warning">
                        注意：每個電子名片最多只能包含10張卡片。請確保您的卡片數量不超過此限制。
                    </div>
                    {{-- 提示拖曳排序 --}}
                    <div class="alert alert-info">
                        拖曳排序以調整電子名片-卡片的顯示順序。點擊操作按鈕進行編輯或刪除。
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="bubble-table">
                            <thead>
                            <tr>
                                <th style="width: 50px">排序</th>
                                <th>圖示</th>
                                <th>標題</th>
                                <th>模板</th>
                                <th>狀態</th>
                                <th>建立時間</th>
                                <th style="width: 120px">操作</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-bubbles">
                            @foreach($bubbles as $bubble)
                                <tr data-id="{{ $bubble->id }}">
                                    <td>
                                        <span class="handle btn btn-xs btn-default">
                                            <i class="fas fa-arrows-alt"></i>
                                        </span>
                                    </td>
                                    <td>
                                        @if($bubble->image)
                                            <img src="{{ asset('uploads/' . $bubble->image) }}"
                                                 alt="{{ $bubble->title }}"
                                                 class="img-thumbnail"
                                                 style="max-height: 60px">
                                        @else
                                            <span class="text-muted">無圖片</span>
                                        @endif
                                    </td>
                                    <td>{{ $bubble->title }}</td>
                                    <td>{{ optional($bubble->template)->name }}</td>
                                    <td>
                                        @if($bubble->active)
                                            <span class="badge badge-success">啟用</span>
                                        @else
                                            <span class="badge badge-secondary">停用</span>
                                        @endif
                                    </td>
                                    <td>{{ $bubble->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        {!! Form::open(['route' => ['admin.businessCards.bubbles.destroy', $card->id, $bubble->id], 'method' => 'delete']) !!}
                                        <div class='btn-group'>
                                            <a href="{{ route('admin.businessCards.bubbles.show', [$card->id, $bubble->id]) }}"
                                               class='btn btn-default btn-md' title="查看">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.businessCards.bubbles.edit', [$card->id, $bubble->id]) }}"
                                               class='btn btn-default btn-md' title="編輯">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                                'type' => 'button',
                                                'class' => 'btn btn-danger btn-md',
                                                'onclick' => "return check(this,'確定要刪除此電子名片-卡片嗎?')",
                                                'title' => '刪除'
                                            ]) !!}
                                        </div>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">LINE 電子名片預覽</h3>
            </div>
            <div class="card-body">
                @if($card->flex_json)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="border p-3 rounded">
                                <h6>JSON 結構:</h6>
                                <pre class="bg-light p-3" style="max-height: 500px; overflow-y: auto;">{{ json_encode($card->flex_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border p-3 rounded">
                                {{-- flex preview --}}
                                <h6>預覽:</h6>
                                <small class="text-muted">*此預覽僅供參考，請使用 LINE 官方的 Flex Message Simulator 查看實際效果</small>
                                <div id="livePreview" style="overflow-y: auto;">
                                    <div id="flex-root"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border p-3 rounded">
                                <h6>預覽說明:</h6>
                                <p>此為 LINE Flex Message 訊息格式，將顯示在 LINE 聊天介面中。</p>
                                <p class="text-muted">請使用 LINE 官方的 <a href="https://developers.line.biz/flex-simulator/" target="_blank">Flex Message Simulator</a> 查看實際效果。</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        無 Flex 訊息資料
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('page_css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('page_scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function() {
        // 拖曳排序功能
        $("#sortable-bubbles").sortable({
            handle: ".handle",
            update: function(event, ui) {
                const bubbleIds = [];
                $("#sortable-bubbles tr").each(function() {
                    bubbleIds.push($(this).data('id'));
                });

                // 發送 AJAX 請求更新排序
                $.ajax({
                    url: "{{ route('admin.businessCards.bubbles.reorder', $card->id) }}",
                    method: "POST",
                    data: {
                        bubbles: bubbleIds,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('排序更新成功');

                            // 更新 JSON 結構和預覽
                            if (response.flex_json) {
                                // 更新 JSON 顯示區域
                                const jsonDisplay = document.querySelector('pre.bg-light');
                                if (jsonDisplay) {
                                    jsonDisplay.textContent = JSON.stringify(response.flex_json, null, 2);
                                }

                                // 更新預覽區域
                                let flexJson = response.flex_json;

                                // 確認是否為 carousel 格式，如果不是則轉換為 carousel 格式
                                if (flexJson && flexJson.type !== 'carousel') {
                                    // 將單一 bubble 包裝成 carousel 格式
                                    flexJson = {
                                        type: "carousel",
                                        direction: "ltr",
                                        contents: [flexJson]
                                    };
                                }

                                const root = document.getElementById("flex-root");
                                if (root) {
                                    // 清空容器
                                    root.innerHTML = '';
                                    // 渲染 Flex 組件
                                    const rendered = renderFlexComponent(flexJson, "");
                                    if (rendered) {
                                        root.appendChild(rendered);
                                    } else {
                                        root.innerHTML = '<div class="alert alert-warning">無法渲染 Flex 訊息</div>';
                                    }
                                }
                            }
                        } else {
                            toastr.error(response.message || '排序更新失敗');
                        }
                    },
                    error: function() {
                        toastr.error('排序更新失敗');
                    }
                });
            }
        });

        // 載入渲染器樣式
        if (!document.getElementById('renderer-css')) {
            const rendererCss = document.createElement('link');
            rendererCss.id = 'renderer-css';
            rendererCss.rel = 'stylesheet';
            rendererCss.href = '{{ asset("assets/css/renderer.css") }}?v={{ time() }}';
            document.head.appendChild(rendererCss);
        }

        // 載入渲染器腳本
        $.getScript('{{ asset("js/renderer.js") }}', function() {
            // 檢查 JSON 數據並渲染
            @if($card->flex_json)
                let flexJson = @json($card->flex_json);

                // 確認是否為 carousel 格式，如果不是則轉換為 carousel 格式
                if (flexJson && flexJson.type !== 'carousel') {
                    // 將單一 bubble 包裝成 carousel 格式
                    flexJson = {
                        type: "carousel",
                        direction: "ltr",
                        contents: [flexJson]
                    };
                }

                const root = document.getElementById("flex-root");
                if (root) {
                    // 清空容器
                    root.innerHTML = '';
                    // 渲染 Flex 組件
                    const rendered = renderFlexComponent(flexJson, "");
                    if (rendered) {
                        root.appendChild(rendered);
                    } else {
                        root.innerHTML = '<div class="alert alert-warning">無法渲染 Flex 訊息</div>';
                    }
                }
            @endif
        });
    });
</script>
@endpush
