@extends('layouts_main.master')

@section('content')
    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden">

        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-center align-items-center px-lg-5 px-md-3 px-2">
                <div class="col-12">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">學習中心</h2>
                        <span>LEARNING CENTER</span>
                        <p class="text-58515D mt-4" style="font-size: 15px;">我們整理了數位名片的相關教學與應用技巧，幫助你快速上手、有效經營專業形象。</p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center mt-3 mb-3 learn-header">
                        @php
                            // 個位數補零
                            $lessonNum = str_pad($lessonInfo->num ?? '01', 2, '0', STR_PAD_LEFT);
                        @endphp
                        <span class="ml-0 mr-3 text-center text-7c6796 font-weight-light border-7c6796 rounded-10 p-1">LESSON<br>{{ $lessonNum }}</span>
                        <h5 class="text-7c6796 font-weight-bold">{{ $lessonInfo->title ?? '讓數位名片發揮最大效益的分享技巧' }}</h5>
                    </div>
                    <div class="line-d0c8d9 my-2"></div>
                    <div class="lesson-views text-right mb-4">
                        <span class="text-7c6796 font-weight-light">觀看人次：{{ $lessonInfo->views ?? 0 }}</span>
                    </div>
                </div>


                <div class="col-12">


                    <div class="lesson-content mb-5">

                        @if (($lessonInfo->content ?? null) !== null)
                            {!! $lessonInfo->content ?? '' !!}
                        @else
                            <img src="{{ asset('assets/images/04/inside_video.jpg') }}" class="img-fluid my-4" alt="">
                            <p class="text-58515D font-weight-normal">

                                數位名片的最大優勢，就是可以快速、靈活地分享給任何人。不過，怎麼分享最有效？關鍵在於  「場景對應」  與  「引導動作」  的搭配。<br><br>


                                以下是幾個實用分享方式與建議：<br><br>


                                1.面對面時｜使用 QR CODE 最直接<br>
                                參加展覽、市集、講座或會議時，將 QR CODE 印在立牌、識別證、貼紙或手機畫面上，讓對方一掃就能存取，迅速建立連結。<br><br>

                                2.線上聊天或社群｜傳送專屬連結<br>
                                將你的數位名片網址放在 LINE 聊天、FB/IG私訊中傳送，或設為 IG 個人簡介連結、一頁式網站入口，都能吸引更多人主動點擊。<br><br>

                                3.電子信件與簡報｜整合進個人資訊區塊<br>
                                將名片連結放入 EMAIL 簽名檔、履歷或簡報最後一頁，不僅提升專業度，也方便對方保存與回訪。<br><br>

                                4.加強行動引導｜搭配 CTA 語句效果更好<br>
                                例如： 「點這裡認識我更多」  、  「一鍵加我 LINE」  、  「看看我們的最新合作案例」  等語句，能提升點擊率與互動意願。<br><br>

                                讓數位名片不只是被動分享，而是主動創造互動與連結的機會。<br>
                            </p>
                        @endif


                    </div>

                    <div class="line-d0c8d9 my-4"></div>

                    <a href="{{ route('learning-center') }}">
                        <div class="btn-main animated-hover-4 font-weight-normal w-fit mx-auto mb-2 mt-3 px-3 py-1">
                            <i class="fas fa-chevron-left mr-2"></i> 返回列表
                        </div>
                    </a>

                </div>

            </div>
        </div>
    </div>
@endsection
