@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            @php
                $currentCount = $card->bubbles()->count();
                $maxCount = $card->user->getMaxCardBubbles();
                $canAdd = $currentCount < $maxCount;
            @endphp
            <div class="row mb-2">
                <div class="col-sm-6 d-flex align-items-center">
                    <h1>
                        {{ $card->title }} - AI數位名片-卡片管理
                    </h1>
                    @if (!$canAdd)
                        <span class="badge badge-warning ml-2">
                            已達上限 ({{ $currentCount }}/{{ $maxCount }})
                        </span>
                    @else
                        <span class="badge badge-info ml-2">
                            ({{ $currentCount }}/{{ $maxCount }})
                        </span>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="float-right text-right">
                        <a class="btn btn-primary my-1 {{ !$canAdd ? 'disabled' : '' }}"
                            href="{{ $canAdd ? route('admin.businessCards.bubbles.create', $card->id) : '#' }}"
                            @if (!$canAdd) onclick="return false;"
                               title="已達卡片數量上限({{ $maxCount }}張)" @endif
                            data-intro="點擊這裡新增您的第一張AI數位名片-卡片。" data-step="1">
                            <i class="fa fa-plus"></i> 新增AI數位名片-卡片
                        </a>

                        <button class="btn btn-info my-1" type="button" id="start-bubbles-tour">
                            <i class="fa fa-question-circle"></i> 開始導覽
                        </button>
                        <a class="btn btn-default my-1" href="{{ route('admin.businessCards.index') }}">
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
                @if ($bubbles->isEmpty())
                    <div class="alert alert-info">
                        尚未添加任何AI數位名片-卡片。點擊上方「新增AI數位名片-卡片」按鈕開始創建。
                    </div>
                @else
                    {{-- 動態提示卡片數量限制 --}}
                    @php
                        $bubbleCount = $bubbles->count();
                        $maxBubbles = $card->user->getMaxCardBubbles();
                    @endphp
                    @if ($bubbleCount >= $maxBubbles)
                        <div class="alert alert-warning">
                            注意：目前已達到AI數位名片-卡片的最大數量限制（{{ $maxBubbles }}張）。請考慮刪除不需要的卡片。
                        </div>
                    @else
                        <div class="alert alert-info">
                            注意：每個AI數位名片最多只能包含{{ $maxBubbles }}張卡片。目前已使用{{ $bubbleCount }}張。
                        </div>
                    @endif
                    {{-- 提示拖曳排序 --}}
                    <div class="alert alert-info">
                        拖曳排序以調整AI數位名片-卡片的顯示順序。點擊操作按鈕進行編輯或刪除。
                    </div>
                    <div class="table-responsive" data-intro="這裡是您所有AI數位名片-卡片的列表。您可以拖曳「排序」欄位來調整它們的順序。" data-step="2">
                        <table class="table" id="bubble-table">
                            <thead>
                                <tr>
                                    <th class="w-50px">排序</th>
                                    <th>圖示</th>
                                    <th>標題</th>
                                    <th>模板</th>
                                    <th>狀態</th>
                                    <th>建立時間</th>
                                    <th class="w-120px">操作</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-bubbles">
                                @foreach ($bubbles as $bubble)
                                    <tr data-id="{{ $bubble->id }}">
                                        <td>
                                            <span class="handle btn btn-xs btn-default">
                                                <i class="fas fa-arrows-alt"></i>
                                            </span>
                                        </td>
                                        <td>
                                            @if ($bubble->image)
                                                <img src="{{ asset('uploads/' . $bubble->image) }}"
                                                alt="{{ $bubble->title }}" class="img-thumbnail max-h-60" @else <span
                                                    class="text-muted">無圖片</span>
                                            @endif
                                        </td>
                                        <td>{{ $bubble->title }}</td>
                                        <td>{{ optional($bubble->template)->name }}</td>
                                        <td>
                                            @if ($bubble->active)
                                                <span class="badge badge-success">啟用</span>
                                            @else
                                                <span class="badge badge-secondary">停用</span>
                                            @endif
                                        </td>
                                        <td>{{ $bubble->created_at->format('Y-m-d H:i') }}</td>
                                        <td data-intro="您可以在這裡查看、編輯或刪除每一張卡片。" data-step="3">
                                            {!! Form::open([
                                                'route' => ['admin.businessCards.bubbles.destroy', $card->id, $bubble->id],
                                                'method' => 'delete',
                                            ]) !!}
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
                                                    'class' => 'btn btn-danger btn-md js-confirm-delete',
                                                    'data-confirm' => '確定要刪除此AI數位名片-卡片嗎?',
                                                    'title' => '刪除',
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
                <h3 class="card-title">LINE AI數位名片預覽</h3>
            </div>
            <div class="card-body">
                @if ($card->flex_json)
                    <div class="row">
                        @if (Auth::user()->isSuperAdmin() || Auth::user()->isMainUser())
                            <div class="col-md-4" data-intro="這裡是AI數位名片的 JSON 結構。當您新增、編輯或排序卡片時，這裡會即時更新。" data-step="4">
                                <div class="border p-3 rounded">
                                    <h6>JSON 結構:</h6>
                                    <pre class="bg-light p-3 max-h-500 overflow-y-auto">{{ json_encode($card->flex_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-{{ Auth::user()->isSuperAdmin() || Auth::user()->isMainUser() ? '4' : '6' }}"
                            data-intro="這裡是AI數位名片的即時預覽。它會根據 JSON 結構的變化而更新。" data-step="5">
                            <div class="border p-3 rounded">
                                {{-- flex preview --}}
                                <h6>預覽:</h6>
                                <small class="text-muted">*此預覽僅供參考，請使用 LINE 官方的 Flex Message Simulator 查看實際效果</small>
                                <div id="livePreview" class="overflow-y-auto">
                                    <div id="flex-root"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-{{ Auth::user()->isSuperAdmin() || Auth::user()->isMainUser() ? '4' : '6' }}">
                            <div class="border p-3 rounded">
                                <h6>預覽說明:</h6>
                                <p>此為 LINE Flex Message 訊息格式，將顯示在 LINE 聊天介面中。</p>
                                <p class="text-muted">請使用 LINE 官方的 <a href="https://developers.line.biz/flex-simulator/"
                                        target="_blank">Flex Message Simulator</a> 查看實際效果。</p>
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
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css" />
    <style @cspNonce>
        .shepherd-text {
            max-width: 400px;
            /* Adjust as needed */
        }

        .shepherd-text p {
            margin-bottom: 0.5em;
        }

        .shepherd-button {
            margin: 0 5px;
        }
    </style>
@endpush

@push('page_scripts')
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
    <script src="{{ asset('js/renderer.js') }}?v={{ config('app.version') }}"></script>
    <script @cspNonce>
        function startTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows shepherd-transparent-text',
                    scrollTo: {
                        behavior: 'smooth',
                        block: 'center'
                    },
                    cancelIcon: {
                        enabled: true,
                        label: '關閉導覽'
                    }
                }
            });

            const stepsData = [{
                    selector: '[data-step="1"]',
                    defaultText: "點擊這裡新增您的第一張AI數位名片-卡片。每個AI數位名片最多可以包含10張卡片。"
                },
                {
                    selector: '[data-step="2"]',
                    defaultText: "這裡是您所有AI數位名片-卡片的列表。您可以拖曳最左側的圖示來調整它們的顯示順序。"
                },
                {
                    selector: '#sortable-bubbles tr:first-child [data-step="3"]',
                    defaultText: "您可以在這裡查看、編輯或刪除每一張卡片。"
                },
                {
                    selector: '[data-step="4"]',
                    defaultText: "這裡是AI數位名片的 JSON 結構。當您新增、編輯或排序卡片時，這裡會即時更新。"
                },
                {
                    selector: '[data-step="5"]',
                    defaultText: "這裡是AI數位名片的即時預覽。它會根據 JSON 結構的變化而更新。請注意，此預覽僅供參考，實際效果請以 LINE Flex Message Simulator 為準。"
                }
            ];

            const availableSteps = stepsData.filter(step => document.querySelector(step.selector));

            availableSteps.forEach((stepInfo, index) => {
                const target = document.querySelector(stepInfo.selector);
                tour.addStep({
                    id: `step-${index + 1}`,
                    text: target.getAttribute('data-intro') || stepInfo.defaultText,
                    attachTo: {
                        element: target,
                        on: 'bottom'
                    },
                    buttons: [{
                            action() {
                                return this.back();
                            },
                            secondary: true,
                            text: '上一步',
                            classes: index === 0 ? 'shepherd-button-hidden' : ''
                        },
                        {
                            action() {
                                return this.next();
                            },
                            text: '下一步',
                            classes: index === availableSteps.length - 1 ? 'shepherd-button-hidden' : ''
                        },
                        {
                            action() {
                                return this.complete();
                            },
                            text: '完成',
                            classes: index === availableSteps.length - 1 ? '' : 'shepherd-button-hidden'
                        }
                    ]
                });
            });

            if (tour.steps.length > 0) {
                tour.start();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: '提示',
                    text: '沒有可導覽的步驟。'
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const tourButton = document.getElementById('start-bubbles-tour');
            if (tourButton) {
                tourButton.addEventListener('click', startTour);
            }
        });

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
                                Swal.fire({
                                    icon: 'success',
                                    title: '排序更新成功',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // 更新 JSON 結構和預覽
                                if (response.flex_json) {
                                    // 更新 JSON 顯示區域
                                    const jsonDisplay = document.querySelector(
                                        'pre.bg-light');
                                    if (jsonDisplay) {
                                        jsonDisplay.textContent = JSON.stringify(response
                                            .flex_json, null, 2);
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
                                        const rendered = renderFlexComponent(flexJson,
                                            "", {}, true);
                                        if (rendered) {
                                            root.appendChild(rendered);
                                        } else {
                                            root.innerHTML =
                                                '<div class="alert alert-warning">無法渲染 Flex 訊息</div>';
                                        }
                                    }
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: '排序更新失敗',
                                    text: response.message || '請稍後再試'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: '排序更新失敗',
                                text: '請稍後再試'
                            });
                        }
                    });
                }
            });

            // 載入渲染器樣式
            if (!document.getElementById('renderer-css')) {
                const rendererCss = document.createElement('link');
                rendererCss.id = 'renderer-css';
                rendererCss.rel = 'stylesheet';
                rendererCss.href = '{{ asset('assets/css/renderer.css') }}?v={{ config('app.version') }}';
                document.head.appendChild(rendererCss);
            }

            // 檢查 JSON 數據並渲染
            @if ($card->flex_json)
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
                    const rendered = renderFlexComponent(flexJson, "", {}, true);
                    if (rendered) {
                        root.appendChild(rendered);
                    } else {
                        root.innerHTML = '<div class="alert alert-warning">無法渲染 Flex 訊息</div>';
                    }
                }
            @endif
        });
    </script>
@endpush
