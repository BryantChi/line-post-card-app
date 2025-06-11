@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>編輯電子名片</h1>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info float-right" onclick="startEditCardTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::model($businessCard, ['route' => ['admin.businessCards.update', $businessCard->id], 'method' => 'patch', 'files' => true]) !!}

            <div class="card-body" data-step="fields_edit" data-intro="您可以修改以下電子名片的資訊。">
                <div class="row">
                    @include('admin.business_cards.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step="save_edit" data-intro="修改完畢後，點擊這裡儲存變更。"']) !!}
                <a href="{{ route('admin.businessCards.index') }}" class="btn btn-default" data-step="cancel_edit" data-intro="點擊這裡取消編輯，並返回列表頁面。">取消</a>
                <a href="{{ route('admin.businessCards.bubbles.index', $businessCard->id) }}" class="btn btn-info" data-step="manage_bubbles_edit" data-intro="點擊這裡進入此電子名片的「卡片管理」頁面，您可以新增、編輯或排序卡片。">
                    <i class="fas fa-th-large"></i> 管理電子名片-卡片
                </a>
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
        function startEditCardTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepData = [
                { selector: '[data-step="1"]', defaultIntro: "<strong>卡片名稱：</strong><br>您可以修改電子名片的名稱。" },
                { selector: '[data-step="2"]', defaultIntro: "<strong>副標題：</strong><br>您可以修改或新增電子名片的副標題。" },
                { selector: '[data-step="3"]', defaultIntro: "<strong>頭像/Logo：</strong><br>您可以更換代表此電子名片的頭像或Logo圖片。" },
                { selector: '[data-step="4"]', defaultIntro: "<strong>啟用狀態：</strong><br>您可以修改此電子名片的啟用狀態。" },
                { selector: '[data-step="5"]', defaultIntro: "<strong>簡介內容：</strong><br>您可以修改此電子名片的簡短介紹或說明。" },
                { selector: '[data-step="6"]', defaultIntro: "<strong>重新生成 Flex JSON：</strong><br>如果您對內含的卡片進行了修改，並希望更新整個電子名片的LINE Flex Message JSON結構，請勾選此項。", optional: true },
                { selector: '[data-step="save_edit"]', defaultIntro: "<strong>儲存：</strong><br>修改完畢後，點擊這裡儲存變更。" },
                { selector: '[data-step="cancel_edit"]', defaultIntro: "<strong>取消：</strong><br>點擊這裡取消編輯，並返回列表頁面。" },
                { selector: '[data-step="manage_bubbles_edit"]', defaultIntro: "<strong>管理電子名片-卡片：</strong><br>點擊這裡進入此電子名片的「卡片管理」頁面，您可以新增、編輯或排序卡片。" }
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
