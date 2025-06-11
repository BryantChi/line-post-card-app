@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h1>建立電子名片</h1>
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

            <div class="card-body" data-step="fields" data-intro="請填寫以下電子名片的基本資訊。">
                <div class="row">
                    @include('admin.business_cards.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('儲存', ['class' => 'btn btn-primary', 'data-step="save_create" data-intro="填寫完畢後，點擊這裡儲存新的電子名片。"']) !!}
                <a href="{{ route('admin.businessCards.index') }}" class="btn btn-default" data-step="cancel_create" data-intro="點擊這裡取消建立，並返回列表頁面。">取消</a>
                <div class="float-right text-muted" data-step="info_create" data-intro="建立名片後，您就可以開始為這張名片新增和管理多個「卡片」內容了。">建立後，您可以繼續添加電子名片-卡片</div>
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
        function startCreateCardTour() {
            const steps = [
                {
                    element: document.querySelector('[data-step="1"]'),
                    intro: "<strong>卡片名稱：</strong><br>請為您的電子名片設定一個明確的名稱，方便您辨識。"
                },
                {
                    element: document.querySelector('[data-step="2"]'),
                    intro: "<strong>副標題：</strong><br>可選填。如果您的名片需要一個副標題，請在此輸入。"
                },
                {
                    element: document.querySelector('[data-step="3"]'),
                    intro: "<strong>頭像/Logo：</strong><br>上傳代表此電子名片的頭像或Logo圖片。"
                },
                {
                    element: document.querySelector('[data-step="4"]'),
                    intro: "<strong>啟用狀態：</strong><br>勾選此項以啟用此電子名片，使其可被分享和查看。"
                },
                {
                    element: document.querySelector('[data-step="5"]'),
                    intro: "<strong>簡介內容：</strong><br>在此輸入關於此電子名片的簡短介紹或說明。"
                },
                {
                    element: document.querySelector('[data-step="save_create"]'),
                    intro: "<strong>儲存：</strong><br>填寫完畢後，點擊這裡儲存新的電子名片。"
                },
                {
                    element: document.querySelector('[data-step="cancel_create"]'),
                    intro: "<strong>取消：</strong><br>點擊這裡取消建立，並返回列表頁面。"
                },
                {
                    element: document.querySelector('[data-step="info_create"]'),
                    intro: "<strong>提示：</strong><br>建立名片後，您就可以開始為這張名片新增和管理多個「卡片」內容了。"
                }
            ];

            introJs().setOptions({
                steps: steps,
                nextLabel: '下一步 &rarr;',
                prevLabel: '&larr; 上一步',
                doneLabel: '完成',
                showBullets: false,
                tooltipClass: 'customTooltip'
            }).start();
        }
    </script>
@endpush
