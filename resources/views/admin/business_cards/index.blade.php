@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>我的電子名片</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('admin.businessCards.create') }}"
                       data-step="1" data-intro="點擊這裡開始建立您的第一張電子名片。">
                        <i class="fa fa-plus"></i> 新增電子名片
                    </a>
                    <button class="btn btn-info float-right mr-2" onclick="startBusinessCardsTour()">
                        <i class="fa fa-question-circle"></i> 操作導覽
                    </button>

                    @if(Auth::user()->isMainUser() || Auth::user()->isSuperAdmin())
                    <a class="btn btn-info float-right mr-2"
                       href="{{ route('admin.all-cards') }}"
                       data-step="2" data-intro="如果您是管理員，可以點擊這裡查看所有使用者建立的電子名片。">
                        <i class="fa fa-users"></i> 查看所有電子名片
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        {{-- 子帳號只能有一組電子名片 --}}
        @if(Auth::user()->isSubUser())
            <div class="alert alert-warning">
                <strong>注意：</strong>每位會員只能擁有一組電子名片，請聯繫主帳號管理員進行調整。
            </div>
        @endif
        {{-- 超級管理員和主帳號可以擁有多組電子名片及子帳號 --}}
        @if(Auth::user()->isMainUser() || Auth::user()->isSuperAdmin())
            <div class="alert alert-info">
                <strong>提示：</strong>管理員帳號可以擁有多組電子名片，請根據需要進行管理。
            </div>
        @endif

        <div class="clearfix"></div>

        <div class="card" data-step="3" data-intro="這裡是您所有電子名片的列表。您可以從這裡管理、編輯、預覽或刪除您的名片。">
            <div class="card-body p-0">
                @include('admin.business_cards.table')

                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{-- @include('adminlte-templates::common.paginator', ['paginator' => $businessCards]) --}}
                        @include('adminlte-templates::common.paginate', ['records' => $businessCards])
                    </div>
                </div>
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
        function startBusinessCardsTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'shepherd-theme-arrows',
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true, label: '關閉導覽' }
                }
            });

            const stepData = [
                { selector: '[data-step="1"]', defaultIntro: "<strong>新增電子名片：</strong><br>點擊這裡開始建立您的第一張電子名片。您可以設定名片的標題、描述等基本資訊。" },
                @if(Auth::user()->isMainUser() || Auth::user()->isSuperAdmin())
                { selector: '[data-step="2"]', defaultIntro: "<strong>查看所有名片：</strong><br>如果您是管理員，可以點擊這裡查看系統中所有使用者建立的電子名片。", optional: true },
                @endif
                { selector: '[data-step="3"]', defaultIntro: "<strong>名片列表：</strong><br>這裡是您所有電子名片的列表。您可以從這裡管理每一張名片的卡片內容、編輯名片資訊、預覽名片效果或刪除不再需要的名片。" },
                { selector: '#businessCards-table tbody tr:first-child [data-step="4"]', defaultIntro: "<strong>查看名片：</strong><br>點擊此按鈕查看電子名片的詳細資訊及預覽。", optional: true },
                { selector: '#businessCards-table tbody tr:first-child [data-step="5"]', defaultIntro: "<strong>編輯名片：</strong><br>點擊此按鈕編輯電子名片的基本設定，例如標題、描述等。", optional: true },
                { selector: '#businessCards-table tbody tr:first-child [data-step="6"]', defaultIntro: "<strong>管理卡片：</strong><br>點擊此按鈕管理此電子名片包含的「卡片」。您可以在此新增、編輯、排序或刪除卡片。", optional: true },
                { selector: '#businessCards-table tbody tr:first-child [data-step="7"]', defaultIntro: "<strong>刪除名片：</strong><br>點擊此按鈕刪除此電子名片。請注意，此操作無法復原。", optional: true }
            ];

            let currentStepIndex = 0;
            const filteredStepData = stepData.filter(s => {
                const element = document.querySelector(s.selector);
                return element || !s.optional; // Include if element exists OR if it's not optional
            });
            const totalSteps = filteredStepData.length;

            filteredStepData.forEach((s) => {
                const element = document.querySelector(s.selector);
                if (element) { // Ensure element exists before adding step
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
