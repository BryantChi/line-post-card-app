@extends('layouts_main.master')

@section('content')
    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden">

        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-start align-items-center px-lg-5 px-md-3 px-2">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">學習中心</h2>
                        <span>LEARNING CENTER</span>
                        <p class="text-58515D mt-4 fs-165">我們整理了數位名片的相關教學與應用技巧，幫助你快速上手、有效經營專業形象。</p>
                    </div>
                </div>


                <div class="col-12 d-flex justify-content-end" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-7c6796 font-weight-light fs-14">
                        共有 {{ count($lessons ?? []) }} 個教學
                    </p>
                </div>

                @foreach ($lessons ?? [] as $lessonInfo)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="learn-item animated-hover-10 pb-3 border-bottom" data-aos="fade-up" data-aos-delay="200">
                            <a href="{{ route('learning-center.details', ['id' => $lessonInfo->id]) }}">
                                <img src="{{ asset('uploads/' . $lessonInfo->image) }}" class="img-fluid rounded-10 w-100"
                                    alt="">
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3 learn-header">
                                    <h5 class="text-7c6796 font-weight-bold">{{ $lessonInfo->title }}</h5>
                                    <span
                                        class="ml-2 text-center text-7c6796 font-weight-light border-7c6796 rounded-10 p-1">LESSON<br>{{ str_pad($lessonInfo->num, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </a>
                            <p class="text-58515D font-weight-light multiline-ellipsis-2 fs-16">
                                @php
                                    $content = preg_replace('/<img[^>]*>/i', '', $lessonInfo->content);
                                    // 移除其他 HTML 標籤
                                    $cleanText = strip_tags($content);
                                    // // 截取前100字（處理UTF-8中文）
                                    // $preview = mb_substr($cleanText, 0, 100);
                                @endphp
                                {!! $cleanText ?? '' !!}
                            </p>
                            <a href="{{ route('learning-center.details', ['id' => $lessonInfo->id]) }}">
                                <div class="btn-main animated-hover-4 font-weight-normal w-fit mx-auto mb-2 mt-3 px-3 py-1">
                                    了解詳情 +
                                </div>
                            </a>
                                <p class="text-7c6796 text-center font-weight-light pb-0 fs-14">觀看人次:
                                {{ $lessonInfo->views }}</p>
                        </div>
                    </div>
                @endforeach

                @if (count($lessons ?? []) == 0)
                    @for ($i = 1; $i <= 2; $i++)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="learn-item animated-hover-10 pb-3 border-bottom" data-aos="fade-up" data-aos-delay="200">
                                <a href="{{ route('learning-center.details.mock') }}">
                                    <img src="{{ asset('assets/images/04/04pic1.jpg') }}" class="img-fluid rounded-10 w-100"
                                        alt="">
                                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3 learn-header">
                                        <h5 class="text-7c6796 font-weight-bold">讓數位名片發揮最大效益的分享技巧</h5>
                                        <span
                                            class="ml-2 text-center text-7c6796 font-weight-light border-7c6796 rounded-10 p-1">LESSON<br>01</span>
                                    </div>
                                </a>
                                <p class="text-58515D font-weight-light fs-16">
                                    數位名片的最大優勢，就是可以快速、靈活地分享給任何人。不過，怎麼分享最有效？關鍵在於...more
                                </p>
                                <a href="{{ route('learning-center.details.mock') }}">
                                    <div
                                        class="btn-main animated-hover-4 font-weight-normal w-fit mx-auto mb-2 mt-3 px-3 py-1">
                                        了解詳情 +
                                    </div>
                                </a>
                                <p class="text-7c6796 text-center font-weight-light pb-0 fs-14">觀看人次: 123
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="learn-item animated-hover-10 pb-3 border-bottom" data-aos="fade-up" data-aos-delay="200">
                                <img src="{{ asset('assets/images/04/04pic2.jpg') }}" class="img-fluid rounded-10 w-100"
                                    alt="">
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3 learn-header">
                                    <h5 class="text-7c6796 font-weight-bold">讓數位名片發揮最大效益的分享技巧</h5>
                                    <span
                                        class="ml-auto text-center text-7c6796 font-weight-light border-7c6796 rounded-10 p-1">LESSON<br>02</span>
                                </div>
                                <p class="text-58515D font-weight-light fs-16">
                                    數位名片的最大優勢，就是可以快速、靈活地分享給任何人。不過，怎麼分享最有效？關鍵在於...more
                                </p>
                                <a href="#" role="button">
                                    <div
                                        class="btn-main animated-hover-4 font-weight-normal w-fit mx-auto mb-2 mt-3 px-3 py-1">
                                        了解詳情 +
                                    </div>
                                </a>
                                <p class="text-7c6796 text-center font-weight-light pb-0 fs-14">觀看人次: 123
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="learn-item animated-hover-10 pb-3 border-bottom" data-aos="fade-up" data-aos-delay="200">
                                <img src="{{ asset('assets/images/04/04pic3.jpg') }}" class="img-fluid rounded-10 w-100"
                                    alt="">
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3 learn-header">
                                    <h5 class="text-7c6796 font-weight-bold">讓數位名片發揮最大效益的分享技巧</h5>
                                    <span
                                        class="ml-auto text-center text-7c6796 font-weight-light border-7c6796 rounded-10 p-1">LESSON<br>03</span>
                                </div>
                                <p class="text-58515D font-weight-light fs-16">
                                    數位名片的最大優勢，就是可以快速、靈活地分享給任何人。不過，怎麼分享最有效？關鍵在於...more
                                </p>
                                <a href="#" role="button">
                                    <div
                                        class="btn-main animated-hover-4 font-weight-normal w-fit mx-auto mb-2 mt-3 px-3 py-1">
                                        了解詳情 +
                                    </div>
                                </a>
                                <p class="text-7c6796 text-center font-weight-light pb-0 fs-14">觀看人次: 123
                                </p>
                            </div>
                        </div>
                    @endfor
                @endif

            </div>

            <div class="overflow-auto mb-3">
                {{ $lessons->onEachSide(3)->links('layouts_main.custom-pagination') }}
            </div>

        </div>


    </div>
@endsection
