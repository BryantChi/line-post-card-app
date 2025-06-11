@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>建立數位名片</h1>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info float-right" onclick="startCreateCardTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::open(['route' => 'admin.businessCards.store', 'files' => true]) !!}

            <div class="card-body" data-step="fields" data-intro="請填寫以下數位名片的基本資訊。">
                <div class="row">
                    @include('admin.business_cards.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step="save_create" data-intro="填寫完畢後，點擊這裡儲存新的數位名片。"']) !!}
                <a href="{{ route('admin.businessCards.index') }}" class="btn btn-default" data-step="cancel_create" data-intro="點擊這裡取消建立，並返回列表頁面。">取消</a>
                <div class="float-right text-muted" data-step="info_create" data-intro="建立名片後，您就可以開始為這張名片新增和管理多個「卡片」內容了。">建立後，您可以繼續添加數位名片-卡片</div>
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
        function startCreateCardTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepData = [
                { selector: '[data-step="1"]', defaultIntro: "<strong>卡片名稱：</strong><br>請為您的數位名片設定一個明確的名稱，方便您辨識。" },
                { selector: '[data-step="2"]', defaultIntro: "<strong>副標題：</strong><br>可選填。如果您的名片需要一個副標題，請在此輸入。" },
                { selector: '[data-step="3"]', defaultIntro: "<strong>頭像/Logo：</strong><br>上傳代表此數位名片的頭像或Logo圖片。" },
                { selector: '[data-step="4"]', defaultIntro: "<strong>啟用狀態：</strong><br>勾選此項以啟用此數位名片，使其可被分享和查看。" },
                { selector: '[data-step="5"]', defaultIntro: "<strong>簡介內容：</strong><br>在此輸入關於此數位名片的簡短介紹或說明。" },
                { selector: '[data-step="save_create"]', defaultIntro: "<strong>儲存：</strong><br>填寫完畢後，點擊這裡儲存新的數位名片。" },
                { selector: '[data-step="cancel_create"]', defaultIntro: "<strong>取消：</strong><br>點擊這裡取消建立，並返回列表頁面。" },
                { selector: '[data-step="info_create"]', defaultIntro: "<strong>提示：</strong><br>建立名片後，您就可以開始為這張名片新增和管理多個「卡片」內容了。" }
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
