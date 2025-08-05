<div class="site-mobile-menu">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3">
            <span class="icon-close2 js-menu-toggle"></span>
        </div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<header class="site-navbar py-2" role="banner" data-aos="fade-down" data-aos-delay="200">

    <div class="container-fluid">
        <div class="row justify-content-around align-items-center">

            <div class="col-8 col-xl-3" data-aos="fade-down">
                <a href="{{ route('index') }}" class="text-black h2 mb-0">
                    <img src="{{ asset('assets/images/00-hp/top_logo.svg') }}" class="img-fluid logo-img" alt="">
                </a>
                <!-- <h1 class="mb-0"></h1> -->
            </div>
            <div class="col-10 col-md-8 d-none d-xl-block" data-aos="fade-down">
                <nav class="site-navigation position-relative text-right text-lg-center2" role="navigation">

                    <ul class="site-menu js-clone-nav mx-auto w-fit2 d-none d-lg-block">
                        <li class="d-block d-lg-none text-lg-start text-center"><a href="{{ route('index') }}" class="text-decoration-none">
                                <img src="{{ asset('assets/images/mobile/logo.png') }}" class="img-fluid" width="70" alt="">
                            </a></li>
                        <li class="text-lg-start text-center {{ Request::is('features') ? 'active' : '' }}"><a href="{{ route('features') }}">產品特色</a></li>
                        <li class="text-lg-start text-center {{ Request::is('application') ? 'active' : '' }}"><a href="{{ route('application') }}">應用場景</a></li>
                        <li class="text-lg-start text-center {{ Request::is('cases') ? 'active' : '' }}"><a href="{{ route('cases') }}">成功案例</a></li>
                        <li class="text-lg-start text-center {{ Request::is('learning-center') ? 'active' : '' }}"><a href="{{ route('learning-center') }}">學習中心</a></li>
                        <li class="text-lg-start text-center"><a href="https://cheni.com.tw/" target="_blank">誠翊資訊</a></li>
                        <li class="d-block d-lg-none text-lg-start text-center"><a href="{{ route('index') }}" class="text-decoration-none">回首頁</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="col col-xl-2 text-right d-inline-block d-xl-none" data-aos="fade-down">
                <div class="d-inline-block d-xl-none ml-md-0 mr-auto py-3" style="position: relative; top: 3px;"><a
                        href="javascript:void(0);" class="site-menu-toggle js-menu-toggle text-black">
                        <img src="{{ asset('assets/images/fimgs/iconmenu.png') }}" class="img-fluid" width="30" alt="">
                    </a></div>
            </div>

        </div>
    </div>

</header>
