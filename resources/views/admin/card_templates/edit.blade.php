@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>
                        名片模板
                        <small>編輯</small>
                    </h1>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info float-right" onclick="startEditTemplateTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::model($cardTemplate, ['route' => ['admin.cardTemplates.update', $cardTemplate->id], 'method' => 'patch', 'files' => true]) !!}

            <div class="card-body">
                <div class="row">
                    @include('admin.card_templates.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step' => 'save_template_edit', 'data-intro' => '修改完畢後，點擊這裡儲存變更。']) !!}
                <a href="{{ route('admin.cardTemplates.index') }}" class="btn btn-default" data-step="cancel_template_edit" data-intro="點擊這裡取消編輯，並返回模板列表頁面。"> 取消 </a>
            </div>

            {!! Form::close() !!}

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
        function startEditTemplateTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepData = [
                { selector: '[data-step="1"]', defaultIntro: "<strong>模板名稱：</strong><br>您可以修改模板的名稱。" },
                { selector: '[data-step="2"]', defaultIntro: "<strong>模板描述：</strong><br>您可以修改模板的描述。" },
                { selector: '[data-step="3"]', defaultIntro: "<strong>預覽圖片：</strong><br>您可以更換模板的預覽圖片。" },
                { selector: '[data-step="4"]', defaultIntro: "<strong>可編輯欄位設定：</strong><br>您可以修改、新增或刪除此模板的可編輯欄位。" },
                { selector: '[data-step="5"]', defaultIntro: "<strong>新增欄位按鈕：</strong><br>點擊此按鈕新增一個新的可編輯欄位。" },
                { selector: '[data-step="editable_field_example"]', defaultIntro: "<strong>可編輯欄位範例：</strong><br>這是一個已設定的可編輯欄位。您可以修改其設定或刪除它。", optional: true },
                { selector: '[data-step="6"]', defaultIntro: "<strong>模板基本結構：</strong><br>您可以修改模板的 LINE Flex Message JSON 結構。請確保 @{{欄位識別碼}} 與上方定義的欄位一致。" },
                { selector: '[data-step="7"]', defaultIntro: "<strong>啟用狀態：</strong><br>您可以修改此模板的啟用狀態。" },
                { selector: '[data-step="save_template_edit"]', defaultIntro: "<strong>儲存：</strong><br>修改完畢後，點擊這裡儲存變更。" },
                { selector: '[data-step="cancel_template_edit"]', defaultIntro: "<strong>取消：</strong><br>點擊這裡取消編輯，並返回模板列表頁面。" }
            ];

            let currentStepIndex = 0;
            const totalSteps = stepData.filter(s => !s.optional || (s.optional && document.querySelector(s.selector))).length;

            stepData.forEach((s) => {
                const element = document.querySelector(s.selector);
                 if (element || (!element && !s.optional)) {
                   if(element) {
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
