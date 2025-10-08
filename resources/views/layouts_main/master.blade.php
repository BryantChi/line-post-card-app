<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="{{ $seoInfo->description ?? '' }}" />
    <meta name="keywords" content="{{ $seoInfo->keywords ?? '' }}" />
    <meta property="og:locale" content="zh_TW" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $seoInfo->og_title ?? '' }}" />
    <meta property="og:description" content="{{ $seoInfo->og_discription ?? '' }}" />
    <meta property="og:image" content="{{ asset('assets/images/fimgs/fbimg.jpg') }}" />
    <meta property="og:url" content="{{ url()->full() }}" />
    <meta property="og:site_name" content="{{ $seoInfo->og_site_name ?? '' }}" />
    <title>{{ $seoInfo->title ?? '' }}</title>
    <meta name="title" content="{{ $seoInfo->title ?? '' }}" />
    <link rel="canonical" href="{{ url()->full() }}" />

    <!-- Favicon -->
    <link href="{{ asset('assets/images/fimgs/favicon.ico') }}" rel="icon">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,700,900|Display+Playfair:200,300,400,700">
    <link rel="stylesheet" href="{{ asset('assets/fonts/icomoon/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/fonts/flaticon/font/flaticon.css') }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- slick carousel --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">


    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ config('app.version', md5_file(public_path('assets/css/style.css'))) }}">

    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ config('app.version') }}">

    @stack('third_party_stylesheets')
    @stack('page_css')

    {!! $seoInfo->ga_header ?? '' !!}

</head>

<body>
    {!! $seoInfo->ga_body ?? '' !!}

    <div class="site-wrap" style="overflow-x: hidden;">

        @include('layouts_main.header')
        @include('layouts_main.hero')

        <div class="main-section" id="{{ Request::routeIs('index') ? 'main-section' : 'main' }}">
            @yield('content')
        </div>
        <!-- main-section end -->

        @include('layouts_main.footer')

    </div>
    <!-- site-wrap end -->


    <a href="#" class="rounded-circle back-to-top">
        <img src="{{ asset('assets/images/00-hp/top.png') }}" class="img-fluid" style="width: 50px;" alt="">
    </a>

    <div class="d-flex d-md-none position-fixed bottom-0 left-0 social-links-btn-mobile">
        <div class="row justify-content-center align-content-center text-center p-0 m-0 w-100">
            <div class="col-12 s-line-btn align-self-center">
                <a href="https://lin.ee/HnB194r" target="_blank">
                    <span><i class="bi bi-line"></i></span> 加入官方LINE諮詢
                </a>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script src="{{ asset('assets/js/main.js') }}?v={{ config('app.version') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}?v={{ config('app.version') }}"></script>


    <script>
        var swiper = new Swiper(".heroSwiper", {
            spaceBetween: 30,
            effect: "fade",
            loop: true,
            centeredSlides: true,
            speed: 1000,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
        });

        var slick = $('.slick').slick({
            arrows: true,
            dots: false,
            speed: 300,
            autoplay: true,
            draggable: true,
            centerPadding: '100px',
            autoplaySpeed: 2000,
            slidesToShow: 3,
            slidesToScroll: 2,
            responsive: [{
                breakpoint: 992,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 2
                }
            }, {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            }, {
                breakpoint: 576,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }]
        });
    </script>

    @stack('third_party_scripts')
    @stack('page_scripts')

</body>

</html>
