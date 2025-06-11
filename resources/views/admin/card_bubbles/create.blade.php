@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>
                        新增電子名片-卡片 - {{ $card->title }}
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
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step' => 'save_bubble_create', 'data-intro' => '填寫完畢後，點擊這裡儲存新的電子名片-卡片。']) !!}
                <a href="{{ route('admin.businessCards.bubbles.index', $card->id) }}" class="btn btn-default" data-step="cancel_bubble_create" data-intro="點擊這裡取消建立，並返回卡片列表頁面。">取消</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection

@push('page_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('page_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function startCreateBubbleTour() {
            const steps = [];

            // Steps from included fields.blade.php
            if (document.querySelector('[data-step="1"]')) { // Template List
                steps.push({
                    element: document.querySelector('[data-step="1"]'),
                    intro: "<strong>選擇模板：</strong><br>在這裡選擇一個卡片模板。選擇後，右側會顯示即時預覽，下方會出現對應的欄位供您填寫。"
                });
            }
            if (document.querySelector('[data-step="2"]')) { // Live Preview
                steps.push({
                    element: document.querySelector('[data-step="2"]'),
                    intro: "<strong>卡片預覽：</strong><br>這裡是您選擇的卡片模板的即時預覽。"
                });
            }
            if (document.querySelector('[data-step="3"]')) { // Basic Settings
                steps.push({
                    element: document.querySelector('[data-step="3"]'),
                    intro: "<strong>基本設定：</strong><br>在這裡填寫卡片的基本資訊，如標題、副標題、主要圖片和內容。"
                });
            }
            if (document.querySelector('[data-step="4"]')) { // Dynamic Fields
                steps.push({
                    element: document.querySelector('[data-step="4"]'),
                    intro: "<strong>模板欄位：</strong><br>根據您選擇的模板，這裡會顯示需要填寫的額外欄位。"
                });
            }

            // Steps specific to create.blade.php
            if (document.querySelector('[data-step="save_bubble_create"]')) {
                steps.push({
                    element: document.querySelector('[data-step="save_bubble_create"]'),
                    intro: "<strong>儲存卡片：</strong><br>完成所有欄位填寫後，點擊這裡儲存您的電子名片-卡片。"
                });
            }
            if (document.querySelector('[data-step="cancel_bubble_create"]')) {
                steps.push({
                    element: document.querySelector('[data-step="cancel_bubble_create"]'),
                    intro: "<strong>取消：</strong><br>點擊這裡取消建立，並返回卡片列表頁面。"
                });
            }

            if (steps.length === 0 && $('.template-item').length === 0) {
                 alert("目前沒有可用的模板，無法開始導覽。請先至「模板管理」建立模板。");
                 return;
            }
            if (steps.length > 0) {
                introJs().setOptions({
                    steps: steps,
                    nextLabel: '下一步 &rarr;',
                    prevLabel: '&larr; 上一步',
                    doneLabel: '完成',
                    showBullets: false,
                    tooltipClass: 'customTooltip'
                }).start();
            } else if ($('.template-item').length > 0) {
                // If fields.blade.php steps are not found but templates exist,
                // it implies an issue with data-step attributes in fields.blade.php
                // or the tour in fields.blade.php itself should be called.
                // For now, we assume parent controls the full tour.
                 alert("導覽步驟未正確設定，請檢查頁面元素。");
            }
        }
    </script>
@endpush
