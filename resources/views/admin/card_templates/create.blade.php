@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>
                    建立卡片模板
                    </h1>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info float-right" onclick="startCreateTemplateTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::open(['route' => 'admin.cardTemplates.store', 'files' => true]) !!}

            <div class="card-body">

                <div class="row">
                    @include('admin.card_templates.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step="save_template_create" data-intro="填寫完畢後，點擊這裡儲存新的卡片模板。"']) !!}
                <a href="{{ route('admin.cardTemplates.index') }}" class="btn btn-default" data-step="cancel_template_create" data-intro="點擊這裡取消建立，並返回模板列表頁面。"> 取消 </a>
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
        function startCreateTemplateTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepData = [
                { selector: '[data-step="1"]', defaultIntro: "<strong>模板名稱：</strong><br>請為您的模板設定一個獨特且易於辨識的名稱。" },
                { selector: '[data-step="2"]', defaultIntro: "<strong>模板描述：</strong><br>可選填。為您的模板添加簡短的描述，說明其用途或特色。" },
                { selector: '[data-step="3"]', defaultIntro: "<strong>預覽圖片：</strong><br>上傳一張預覽圖片，讓使用者在選擇模板時能快速了解模板的樣式。" },
                { selector: '[data-step="4"]', defaultIntro: "<strong>可編輯欄位設定：</strong><br>定義此模板中哪些欄位是使用者可以編輯的。點擊「新增欄位」來添加可編輯的項目。" },
                { selector: '[data-step="5"]', defaultIntro: "<strong>新增欄位按鈕：</strong><br>點擊此按鈕新增一個可編輯欄位。您可以設定欄位的識別碼、標籤、類型、是否必填及預設值。" },
                // Note: editable_field_example might not exist on initial load, handle dynamically or omit if too complex for this pass
                { selector: '[data-step="editable_field_example"]', defaultIntro: "<strong>可編輯欄位範例：</strong><br>這是一個可編輯欄位的設定範例。您需要設定欄位識別碼（用於在JSON結構中引用）、標籤（顯示給使用者）、類型等。", optional: true },
                { selector: '[data-step="6"]', defaultIntro: "<strong>模板基本結構：</strong><br>在這裡輸入模板的 LINE Flex Message JSON 結構。您可以使用 @{{欄位識別碼}} 的格式來引用上面定義的可編輯欄位，例如 @{{field}}。" },
                { selector: '[data-step="7"]', defaultIntro: "<strong>啟用狀態：</strong><br>勾選此項以啟用此模板，使其在建立電子名片-卡片時可供選擇。" },
                { selector: '[data-step="save_template_create"]', defaultIntro: "<strong>儲存：</strong><br>填寫完畢後，點擊這裡儲存新的卡片模板。" },
                { selector: '[data-step="cancel_template_create"]', defaultIntro: "<strong>取消：</strong><br>點擊這裡取消建立，並返回模板列表頁面。" }
            ];

            let currentStepIndex = 0;
            const totalSteps = stepData.filter(s => !s.optional || (s.optional && document.querySelector(s.selector))).length;

            stepData.forEach((s) => {
                const element = document.querySelector(s.selector);
                if (element || (!element && !s.optional)) { // Add step if element exists or if it's not optional
                   if(element) { // only add if element truly exists
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
