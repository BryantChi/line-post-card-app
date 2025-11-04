@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>卡片模板</h1>
                </div>
                <div class="col-sm-6">
                    <button type="button" class="btn btn-info float-right ml-2" id="start-templates-tour">
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
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>
    <style @cspNonce>
        /* Optional: Custom Shepherd styles if needed */
        .shepherd-text { max-width: 400px; }
        .shepherd-button { margin: 0 5px; }
    </style>
@endpush

@push('page_scripts')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
    <script @cspNonce>
        function startTemplatesTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepDefinitions = [
                { selector: '[data-step="1"]', defaultIntro: "點擊這裡新增一個新的卡片模板。您可以定義模板的結構、樣式和可編輯欄位。" },
                { selector: '[data-step="table_intro"]', defaultIntro: "這裡是您所有卡片模板的列表。您可以查看每個模板的詳細資訊、預覽效果，並進行管理。" }
            ];

            // Steps for the first row of the table
            const firstRowSelectors = [
                { selector: '#card-templates-table tbody tr:first-child [data-step="2"]', defaultIntro: "這是模板的名稱，方便您在建立卡片時辨識。" },
                { selector: '#card-templates-table tbody tr:first-child [data-step="3"]', defaultIntro: "模板的簡短描述。" },
                { selector: '#card-templates-table tbody tr:first-child [data-step="4"]', defaultIntro: "模板的預覽圖片，讓您快速了解模板的樣式。" },
                { selector: '#card-templates-table tbody tr:first-child [data-step="5"]', defaultIntro: "點擊這裡可以查看或隱藏此模板的原始 LINE Flex Message JSON 結構。" },
                { selector: '#card-templates-table tbody tr:first-child [data-step="6"]', defaultIntro: "這裡是模板在 LINE 中的大致預覽效果。請注意，此預覽僅供參考，實際效果請以 LINE Flex Message Simulator 為準。" },
                { selector: '#card-templates-table tbody tr:first-child [data-step="7"]', defaultIntro: "您可以在這裡編輯、複製或刪除此模板。" }
            ];

            if (document.querySelector('#card-templates-table tbody tr:first-child')) {
                stepDefinitions.push(...firstRowSelectors);
            }

            let currentStep = 0;
            const totalSteps = stepDefinitions.filter(s => document.querySelector(s.selector)).length;

            stepDefinitions.forEach((stepDef) => {
                const element = document.querySelector(stepDef.selector);
                if (element) {
                    currentStep++;
                    tour.addStep({
                        text: element.getAttribute('data-intro') || stepDef.defaultIntro,
                        attachTo: { element: element, on: 'auto' },
                        buttons: [
                            {
                                action() { return this.back(); },
                                secondary: true,
                                text: '上一步',
                                classes: currentStep === 1 ? 'shepherd-button-hidden' : ''
                            },
                            {
                                action() { return this.next(); },
                                text: '下一步',
                                classes: currentStep === totalSteps ? 'shepherd-button-hidden' : ''
                            },
                            {
                                action() { return this.complete(); },
                                text: '完成',
                                classes: currentStep === totalSteps ? '' : 'shepherd-button-hidden'
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

        document.addEventListener('DOMContentLoaded', () => {
            const tourButton = document.getElementById('start-templates-tour');
            if (tourButton) {
                tourButton.addEventListener('click', startTemplatesTour);
            }
        });
    </script>
@endpush
