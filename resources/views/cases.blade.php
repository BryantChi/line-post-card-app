@extends('layouts_main.master')

@section('content')

    <div class="container-xxl py-5 overflow-hidden">


        <div class="container-fluid px-md-0 px-3">
            <div class="row">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">成功案例</h2>
                        <span>SUCCESS CASES</span>
                        <p class="text-58515D mt-4" style="font-size: 16.5px;">透過客製化設計，不論是色系搭配、字體風格、模組配置，甚至是品牌識別延伸，都能完美展現專業形象與個人特色。</p>
                    </div>
                </div>


                <div class="col-md-2 bg-case-left mb-md-0 mb-3" data-aos="zoom-in" data-aos-delay="200">
                    <picture>
                        <source srcset="{{ asset('assets/images/03/03left_325.jpg') }}" type="image/jpg" media="(min-width: 1440px)">
                        <source srcset="{{ asset('assets/images/03/03left_360.jpg') }}" type="image/jpg" media="(min-width: 1280px)">
                        <source srcset="{{ asset('assets/images/03/03left_624.jpg') }}" type="image/jpg" media="(min-width: 1024px)">
                        <img src="{{ asset('assets/images/03/03left_1080.jpg') }}" class="img-fluid" alt="">
                    </picture>
                </div>

                <div class="col-md-9">

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end" data-aos="fade-up" data-aos-delay="200">
                            <p class="text-7c6796 font-weight-light" style="font-size: 14px;">
                                共 {{ count($cases ?? []) }} 則案例
                            </p>
                        </div>
                    </div>

                    <div class="row justify-content-start align-items-center gap-3 mb-3">

                        @foreach ($cases ?? [] as $caseInfo)
                            <div class="col-lg-4 col-md-6">
                                <a href="{{ $caseInfo->businessCard->getShareUrl() }}" target="_blank">
                                    <div class="case-item text-center" data-aos="fade-up" data-aos-delay="200">
                                        <img src="{{ asset('uploads/' . $caseInfo->businessCard->profile_image) }}" class="img-fluid rounded-10 w-100" alt="">
                                        <h5 class="mt-3 text-58515D font-weight-bold">{{ $caseInfo->name }}</h5>
                                        <p class="text-7c6796 font-weight-light" style="font-size: 14px;">觀看人次：{{ $caseInfo->businessCard->views }}</p>
                                    </div>
                                </a>
                            </div>
                        @endforeach

                        @if (count($cases ?? []) == 0)

                            @for ($i = 1; $i <= 3; $i++)
                                <div class="col-lg-4 col-md-6">
                                    <div class="case-item text-center" data-aos="fade-up" data-aos-delay="200">
                                        <img src="{{ asset('assets/images/03/case1.svg') }}" class="img-fluid rounded-10 w-100" alt="">
                                        <h5 class="mt-3 text-58515D font-weight-bold">LING綾 • 靈感生活</h5>
                                        <p class="text-7c6796 font-weight-light" style="font-size: 14px;">觀看人次：123</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="case-item text-center" data-aos="fade-up" data-aos-delay="300">
                                        <img src="{{ asset('assets/images/03/case2.svg') }}" class="img-fluid rounded-10 w-100" alt="">
                                        <h5 class="mt-3 text-58515D font-weight-bold">Peipei紋繡整體造型美學館</h5>
                                        <p class="text-7c6796 font-weight-light" style="font-size: 14px;">觀看人次：123</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="case-item text-center" data-aos="fade-up" data-aos-delay="400">
                                        <img src="{{ asset('assets/images/03/case3.svg') }}" class="img-fluid rounded-10 w-100" alt="">
                                        <h5 class="mt-3 text-58515D font-weight-bold">川宏廚具</h5>
                                        <p class="text-7c6796 font-weight-light" style="font-size: 14px;">觀看人次：123</p>
                                    </div>
                                </div>
                            @endfor

                        @endif


                    </div>

                    <div class="overflow-auto mb-3">
                        {{ $cases->onEachSide(3)->links('layouts_main.custom-pagination') }}
                    </div>

                </div>

            </div>
        </div>

    </div>


@endsection
