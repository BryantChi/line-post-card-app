@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>卡片模板</h1>
                </div>
                <div class="col-sm-6">
                    <button class="btn btn-info float-right ml-2" onclick="startTemplatesTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>
                    <a class="btn btn-primary float-right"
                       href="{{ route('admin.cardTemplates.create') }}"
                       data-step="1" data-intro="點擊這裡新增一個新的卡片模板。您可以定義模板的結構、樣式和可編輯欄位。">
                        <i class="fas fa-plus"></i>
                        新增卡片模板
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card" data-step="table_intro" data-intro="這裡是您所有卡片模板的列表。您可以查看每個模板的詳細資訊、預覽效果，並進行管理。">
            @include('admin.card_templates.table')
        </div>
    </div>

@endsection

@push('page_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('page_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function startTemplatesTour() {
            const steps = [
                {
                    element: document.querySelector('[data-step="1"]'),
                    intro: "<strong>新增卡片模板：</strong><br>點擊這裡新增一個新的卡片模板。您可以定義模板的結構、樣式和可編輯欄位。"
                },
                {
                    element: document.querySelector('[data-step="table_intro"]'),
                    intro: "<strong>模板列表：</strong><br>這裡是您所有卡片模板的列表。您可以查看每個模板的詳細資訊、預覽效果，並進行管理。"
                }
            ];

            // Dynamically add steps for the first row of the table if it exists
            if (document.querySelector('#card-templates-table tbody tr:first-child')) {
                steps.push(
                    {
                        element: document.querySelector('#card-templates-table tbody tr:first-child [data-step="2"]'),
                        intro: "<strong>模板名稱：</strong><br>這是模板的名稱，方便您在建立卡片時辨識。"
                    },
                    {
                        element: document.querySelector('#card-templates-table tbody tr:first-child [data-step="3"]'),
                        intro: "<strong>模板描述：</strong><br>模板的簡短描述。"
                    },
                    {
                        element: document.querySelector('#card-templates-table tbody tr:first-child [data-step="4"]'),
                        intro: "<strong>預覽圖片：</strong><br>模板的預覽圖片，讓您快速了解模板的樣式。"
                    },
                    {
                        element: document.querySelector('#card-templates-table tbody tr:first-child [data-step="5"]'),
                        intro: "<strong>查看/隱藏 JSON：</strong><br>點擊這裡可以查看或隱藏此模板的原始 LINE Flex Message JSON 結構。"
                    },
                    {
                        element: document.querySelector('#card-templates-table tbody tr:first-child [data-step="6"]'),
                        intro: "<strong>Flex 預覽：</strong><br>這裡是模板在 LINE 中的大致預覽效果。請注意，此預覽僅供參考，實際效果請以 LINE Flex Message Simulator 為準。"
                    },
                    {
                        element: document.querySelector('#card-templates-table tbody tr:first-child [data-step="7"]'),
                        intro: "<strong>操作：</strong><br>您可以在這裡編輯或刪除此模板。"
                    }
                );
            }

            introJs().setOptions({
                steps: steps.filter(step => step.element), // Ensure element exists
                nextLabel: '下一步 &rarr;',
                prevLabel: '&larr; 上一步',
                doneLabel: '完成',
                showBullets: false,
                tooltipClass: 'customTooltip',
                scrollParent: document.body,
                scrollToElement: true,
            }).start();
        }
    </script>
@endpush
