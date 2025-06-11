@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>
                        新增數位名片-卡片 - {{ $card->title }}
                    </h1>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info float-right" onclick="startCreateBubbleTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>
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
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step' => 'save_bubble_create', 'data-intro' => '填寫完畢後，點擊這裡儲存新的數位名片-卡片。']) !!}
                <a href="{{ route('admin.businessCards.bubbles.index', $card->id) }}" class="btn btn-default" data-step="cancel_bubble_create" data-intro="點擊這裡取消建立，並返回卡片列表頁面。">取消</a>
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
        function startCreateBubbleTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepData = [
                { selector: '[data-step="1"]', defaultIntro: "<strong>選擇模板：</strong><br>在這裡選擇一個卡片模板。選擇後，右側會顯示即時預覽，下方會出現對應的欄位供您填寫。" },
                { selector: '[data-step="2"]', defaultIntro: "<strong>卡片預覽：</strong><br>這裡是您選擇的卡片模板的即時預覽。" },
                { selector: '[data-step="3"]', defaultIntro: "<strong>基本設定：</strong><br>在這裡填寫卡片的基本資訊，如標題、副標題、主要圖片和內容。" },
                { selector: '[data-step="4"]', defaultIntro: "<strong>模板欄位：</strong><br>根據您選擇的模板，這裡會顯示需要填寫的額外欄位。" },
                { selector: '[data-step="save_bubble_create"]', defaultIntro: "<strong>儲存卡片：</strong><br>完成所有欄位填寫後，點擊這裡儲存您的數位名片-卡片。" },
                { selector: '[data-step="cancel_bubble_create"]', defaultIntro: "<strong>取消：</strong><br>點擊這裡取消建立，並返回卡片列表頁面。" }
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

            if (tour.steps.length === 0 && $('.template-item').length === 0) {
                 alert("目前沒有可用的模板，無法開始導覽。請先至「模板管理」建立模板。");
                 return;
            }
            if (tour.steps.length > 0) {
                tour.start();
            } else if ($('.template-item').length > 0) {
                 alert("導覽步驟未正確設定，請檢查頁面元素。");
            }
        }
    </script>
@endpush
