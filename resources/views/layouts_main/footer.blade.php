<footer class="site-footer overflow-hidden">
    <div class="container-fluid px-lg-5 px-md-3 px-2">
        <div class="row justify-content-between align-items-center px-lg-5 px-md-3 px-2 mb-5">
            <div class="col-auto mb-lg-0 mb-3">
                <a href="{{ route('index') }}" class="text-decoration-none">
                    <img src="{{ asset('assets/images/00-hp/top_logo.svg') }}" class="img-fluid w-75" alt="">
                </a>
            </div>

            <div class="col-auto">
                <div class="d-flex flex-lg-row flex-column align-items-center">
                    <a href="{{ route('features') }}" class="pl-0 pr-3 text-white animated-hover-4">產品特色</a>
                    <a href="{{ route('application') }}" class="pl-lg-3 pr-3 text-white animated-hover-4">應用場景</a>
                    <a href="{{ route('cases') }}" class="pl-lg-3 pr-3 text-white animated-hover-4">成功案例</a>
                    <a href="{{ route('learning-center') }}" class="pl-lg-3 pr-3 text-white animated-hover-4">學習中心</a>
                    <a href="https://cheni.com.tw/" target="_blank" class="pl-lg-3 pr-3 text-white animated-hover-4">誠翊資訊</a>
                </div>
            </div>
        </div>

        <div class="row px-lg-5 px-md-3 px-2 mt-5">
            <div class="col-12">
                <div class="d-flex flex-lg-row flex-column text-white">
                    <p class="text-white font-weight-normal font-14 mr-3"><i class="fas fa-map-marker-alt mr-1"></i>
                        公司地址:
                        花蓮縣吉安鄉中原路一段89號</p>
                    <p class="text-white font-weight-normal font-14 mr-3"><i class="fas fa-phone-alt mr-1"></i>
                        電話: 03-8511-126
                    </p>
                    <p class="text-white font-weight-normal font-14 mr-3"><i class="fas fa-fax mr-1"></i> 傳真:
                        03-8526-126</p>
                    <p class="text-white font-weight-normal font-14 mr-3"><i class="fas fa-envelope mr-1"></i>
                        Email: <a href="mailto:yen@cheni.com.tw" class="text-white">yen@cheni.com.tw</a></p>
                </div>
            </div>
            <div style="background-color: #bab4c6; height: 1px;width: 98%;" class="mx-auto"></div>
            <div class="col-12 mt-3 d-flex flex-lg-row flex-column justify-content-between align-items-lg-center">
                <div class="order-lg-1 order-2">
                    <div class="d-flex flex-lg-row flex-column">
                        {{-- <p class="text-white font-weight-normal font-14 mb-0 mr-3">今日人數: 361</p>
                        <p class="text-white font-weight-normal font-14 mb-0 mr-3">總進站人數: 361825</p> --}}

                        <div class="d-flex mb-0 mr-3">
                            <p class="text-white font-weight-normal font-14 mb-0">今日人數：</p>
                            <div class="d-flex align-items-center" id="counter-today">
                                @if(isset($visitorCountToday))
                                    @foreach (str_split(($visitorCountToday + 361)) as $digit)
                                        <img src="{{ asset('assets/images/00-hp/' . $digit . '.svg') }}" class="img-fluid" style="width: auto; height: 16px;" alt="{{ $digit }}">
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="d-flex mb-0 mr-3">
                            <p class="text-white font-weight-normal font-14 mb-0">總進站人數：</p>
                            <div class="d-flex align-items-center" id="counter">
                                @if(isset($visitorCount))
                                    @foreach (str_split(($visitorCount + 361825)) as $digit)
                                        <img src="{{ asset('assets/images/00-hp/' . $digit . '.svg') }}" class="img-fluid" style="width: auto; height: 16px;" alt="{{ $digit }}">
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <p class="text-white font-weight-normal font-14 mb-0 mt-2">
                        © 2025 ALL RIGHTS RESERVED AND WEB DESIGN MAINTAINED POWER BY <a href="https://cheni.com.tw/" target="_blank"
                            class="text-white">誠翊資訊</a>
                    </p>
                </div>

                <div class="d-flex order-lg-2 order-1 mb-lg-0 mb-3">
                    <a href="https://www.facebook.com/chenitw" target="_blank" class="mr-3">
                        <img src="{{ asset('assets/images/00-hp/footer_fb.png') }}" class="img-fluid" width="36" alt="">
                    </a>
                    <a href="https://lin.ee/HnB194r" target="_blank" class="mr-3">
                        <img src="{{ asset('assets/images/00-hp/footer_line.png') }}" class="img-fluid" width="36" alt="">
                    </a>
                </div>

            </div>

        </div>

    </div>
</footer>
