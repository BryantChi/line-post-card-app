@extends('layouts_main.master')

@section('content')
    <!-- Ad Start -->
    <div class="container-xxl py-5 hp-ad-bg">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-lg-5 mb-3">
                        <h2 class="mb-2">優勢特點</h2>
                        <span>ADVANTAGES & FEATURES</span>
                    </div>
                </div>

                <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-items text-center">
                        <img src="{{ asset('assets/images/00-hp/ad_icon1.png') }}" class="img-fluid" alt="">
                        <h4 class="feature-title font-weight-bold">
                            一鍵分享
                            <br>
                            快速建立連結
                        </h4>
                    </div>
                </div>

                <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-items text-center">
                        <img src="{{ asset('assets/images/00-hp/ad_icon2.png') }}" class="img-fluid" alt="">
                        <h4 class="feature-title font-weight-bold">
                            整合LINE帳號
                            <br>
                            提升互動效率
                        </h4>
                    </div>
                </div>

                <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-items text-center">
                        <img src="{{ asset('assets/images/00-hp/ad_icon3.png') }}" class="img-fluid" alt="">
                        <h4 class="feature-title font-weight-bold">
                            形象完整呈現
                            <br>
                            專業加分
                        </h4>
                    </div>
                </div>

                <div class="clearfix w-100 d-none d-lg-block"></div>

                <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-items text-center">
                        <img src="{{ asset('assets/images/00-hp/ad_icon4.png') }}" class="img-fluid" alt="">
                        <h4 class="feature-title font-weight-bold">
                            數據追蹤
                            <br>
                            一目了然
                        </h4>
                    </div>
                </div>


                <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-items text-center">
                        <img src="{{ asset('assets/images/00-hp/ad_icon5.png') }}" class="img-fluid" alt="">
                        <h4 class="feature-title font-weight-bold">
                            即使更新
                            <br>
                            不怕資訊過時
                        </h4>
                    </div>
                </div>

                <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-items text-center">
                        <img src="{{ asset('assets/images/00-hp/ad_icon6.png') }}" class="img-fluid" alt="">
                        <h4 class="feature-title font-weight-bold">
                            AI輔助
                            <br>
                            生成簡介
                        </h4>
                    </div>
                </div>

                <div class="col-12 text-center mt-3">
                    <h5 class="text-7c6796 font-weight-bold mb-3" data-aos="fade-up" data-aos-delay="200">結合LINE優勢，打造高效AI數位名片</h5>
                    <p class="text-58515D font-weight-normal line-height-2" data-aos="fade-up" data-aos-delay="200">
                        在台灣，LINE擁有超過90%的使用率，已成為日常溝通的主流平台。<br>
                        因此，透過LINE製作數位名片，不僅更貼近現代人的使用習慣，更能有效提升曝光與互動效率。<br>
                        無論是個人品牌經營，還是企業行銷推廣，LINE數位名片都是引導潛在客群、開啟社群第二入口的強大工具。
                    </p>

                    <a href="{{ route('features') }}" class="text-decoration-none" data-aos="fade-up" data-aos-delay="200">
                        <div class="btn-main animated-hover-4 mt-4 w-fit mx-auto">了解更多產品特色</div>
                    </a>
                </div>

            </div>
        </div>
    </div>
    <!-- Ad End -->

    <!-- usemob start -->
    <div class="container-xxl py-lg-5 pt-5 pb-2 hp-use-mob-bg overflow-hidden">
        <div class="container-fluid px-lg-5 mx-lg-5 px-md-3 mx-md-3 px-2 mx-2">
            <div class="row align-items-md-center align-items-end py-lg-5">
                <div class="col-12 d-block d-md-none" style="height: 150px;"></div>
                <div class="col-lg-6 py-lg-5" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-white mb-4 usemob-title">
                        數位名片新時代
                        <br>
                        打造專屬於你的品牌入口
                    </h3>
                    <p class="text-white line-height-2 usemob-sub-title">
                        不只是一張名片，<br class="d-block d-md-none">也是你的行動品牌網站
                    </p>
                </div>

            </div>
        </div>
    </div>
    <!-- usemob end -->

    <!-- intro start -->
    <div class="container-xxl py-5 hp-intro-bg">
        <div class="container">
            <div class="row">
                <div class="col-12 mb-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-lg-5 mb-3">
                        <h2 class="mb-2">LINE數位名片是什麼？</h2>
                        <span class="text-uppercase">Line Digital Business Card</span>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-right" data-aos-delay="200">
                    <img src="{{ asset('assets/images/00-hp/intro_illu.png') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-7" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-58515D font-weight-normal line-height-2">
                        LINE 數位名片就是將你的個人資料、聯絡方式、社群連結、服務、商品等資訊，整合在3-10張「可以透過LINE分享的電子名片」中。<br>
                        不像傳統紙本名片容易弄丟、資訊一變就得重印，LINE 數位名片可以即時更新，直接傳送給對方，不管是聊天、加好友，還是引導對方點進你的網站或社群，都能一鍵完成。<br>
                        不管是業務人員、自由工作者、店家老闆，還是想經營個人品牌，有一張LINE數位名片，就能讓對方更快記住你、聯絡你，也讓你的專業形象大加分！
                    </p>

                    <a href="{{ route('application') }}" class="text-decoration-none">
                        <div class="btn-main animated-hover-4 mt-4 w-fit">了解應用場景</div>
                    </a>

                </div>


            </div>
        </div>
    </div>
    <!-- intro end -->

    <!-- intro2 start -->
    <div class="container-xxl py-5 hp-intro2-bg">
        <div class="container">
            <div class="row justify-content-center" id="intro2">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">新世代的數位連結利器</h2>
                        <p class="text-7c6796">
                            數位名片 VS. 傳統名片 |<br class="d-block d-md-none"> 9 大指標比較表
                        </p>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive intro2-table">

                        <table class="table table-borderless mb-0">
                            <thead class="text-center rounded-10" data-aos="fade-up" data-aos-delay="200">
                                <tr>
                                    <th class="align-middle bg-7c6796 text-nowrap">
                                        <h5 class="mb-0 text-white">項目</h5>
                                    </th>
                                    <th class="align-middle bg-8954cb-552f85 text-nowrap" style="min-width: 300px;">
                                        <h5 class="mb-0 text-white">
                                            <img src="{{ asset('assets/images/00-hp/crown.png') }}" class="img-fluid"
                                                width="40px" alt="">
                                            誠翊資訊 AI數位名片
                                        </h5>
                                    </th>
                                    <th class="align-middle bg-7c6796 text-nowrap">
                                        <h5 class="mb-0 text-white">傳統名片</h5>
                                    </th>
                                </tr>
                            </thead>
                            <tbody data-aos="fade-up" data-aos-delay="200">
                                <tr>
                                    <td>保存性</td>
                                    <td>雲端儲存、隨時回訪</td>
                                    <td>容易遺失、不易找回</td>
                                </tr>
                                <tr>
                                    <td>表達性</td>
                                    <td>可呈現圖片、影片、社群連結等豐富資訊</td>
                                    <td>限於紙面空間，內容受限</td>
                                </tr>
                                <tr>
                                    <td>時效性</td>
                                    <td>可即時更新資訊，永不過時</td>
                                    <td>一經印製無法修改</td>
                                </tr>
                                <tr>
                                    <td>吸睛力</td>
                                    <td>可設計專屬版型，提升第一眼吸引力</td>
                                    <td>視覺表現受限，較不易突出</td>
                                </tr>
                                <tr>
                                    <td>創意度</td>
                                    <td>可自由設計模組、互動連結</td>
                                    <td>版面格式固定，設計彈性小</td>
                                </tr>
                                <tr>
                                    <td>內容深度</td>
                                    <td>可延伸介紹、作品集、品牌故事</td>
                                    <td>僅限基本聯絡資訊</td>
                                </tr>
                                <tr>
                                    <td>即時性</td>
                                    <td>掃碼即看、一鍵分享</td>
                                    <td>需面對面遞交</td>
                                </tr>
                                <tr>
                                    <td>印象度</td>
                                    <td>可結合個人風格，加深記憶點</td>
                                    <td>易被遺忘或與他人混淆</td>
                                </tr>
                                <tr>
                                    <td>變化度</td>
                                    <td>彈性調整內容與樣式，隨時因應需求變化</td>
                                    <td>固定格式，一旦印製難以更動</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>


        </div>
    </div>
    <!-- intro2 end -->


    <!-- intro3 start -->
    <div class="container-xxl py-5 hp-intro3-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">誠翊資訊AI數位名片 | 專為中小企業與個人品牌打造</h2>
                        <p class="text-7c6796">
                            透過簡單操作，即可展現專業、連結人脈，創造更高的商機與信任感
                        </p>
                    </div>
                </div>

            </div>

            <div class="row justify-content-center align-items-center slick" data-aos="fade-up" data-aos-delay="200">
                @foreach ($cases ?? [] as $case)
                    <div class="col-lg-4 mb-3">
                        <a href="{{ $case->businessCard->getShareUrl() }}" target="_blank" class="text-decoration-none">
                            <img src="{{ asset('uploads/' . $case->businessCard->profile_image) }}" class="img-fluid rounded-10 case-item"
                            alt="">
                        </a>
                    </div>
                @endforeach

                @if (count($cases ?? []) == 0)
                    <div class="col-lg-4 mb-3">
                        <img src="{{ asset('assets/images/00-hp/case_01.jpg') }}" class="img-fluid rounded-10"
                            alt="">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <img src="{{ asset('assets/images/00-hp/case_02.jpg') }}" class="img-fluid rounded-10"
                            alt="">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <img src="{{ asset('assets/images/00-hp/case_03.jpg') }}" class="img-fluid rounded-10"
                            alt="">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <img src="{{ asset('assets/images/00-hp/case_03.jpg') }}" class="img-fluid rounded-10"
                            alt="">
                    </div>
                @endif

            </div>

            <div class="row justify-content-center mt-4">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-3">
                        <h2 class="mb-2 text-5f487c">善用科技的人走得更快</h2>
                        <span class="text-uppercase">SUCCESS CASES</span>
                    </div>
                </div>
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-center text-58515D font-weight-normal">
                        透過數位名片，找到更快被看見、更有效溝通的方式，一步步打開新的機會之門
                    </p>
                    <p class="text-center text-58515D" style="font-size: larger;">
                        成功不是巧合，而是選對工具與方法
                    </p>

                    <a href="{{ route('cases') }}" class="text-decoration-none">
                        <div class="btn-main animated-hover-4 w-fit mx-auto mt-3">觀看更多成功案例</div>
                    </a>
                </div>

            </div>


        </div>
    </div>
    <!-- intro3 end -->


    <!-- contact start -->
    <div class="container-xxl py-5 hp-cta-bg overflow-hidden">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row py-lg-5 py-3 px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-10 col-md-6">
                    <h4 class="text-5f487c font-weight-bold line-height-2-3">打造你的專屬數位名片，讓每一次的自我介紹更有價值</h4>
                    <p class="text-58515D font-weight-normal line-height-2">
                        介紹自己，不該只是名字與電話，數位名片為你說更多，讓每一次出場都更有影響力<br>
                        現在就打造專屬的LINE AI數位名片，一鍵分享、即時更新，無論在社群、會議、或陌生開場，都能留下深刻印象
                    </p>
                    <a href="https://lin.ee/HnB194r" target="_blank">
                        <img src="{{ asset('assets/images/00-hp/bu_line.svg') }}" class="img-fluid animated-hover-4" width="280px"
                            alt="">
                    </a>
                </div>
                <div class="col-12 d-md-none d-block" style="height: 250px;"></div>
            </div>
        </div>
    </div>
    <!-- contact end -->

    <!-- learn start -->
    <div class="container-xxl py-5 hp-learn-bg overflow-hidden">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row py-5 px-lg-5 px-md-3 px-2 align-items-center">
                <div class="col-auto mb-lg-0 mb-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-start pr-md-4 pb-md-0 pb-3 learn-border">
                        <h2 class="mb-2 text-white">學習中心</h2>
                        <span class="text-uppercase text-ede9f2" style="opacity: 0.5;">LEARNING<br>CENTER</span>
                    </div>
                </div>
                <div class="col-lg col-md-8" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-white font-weight-normal line-height-2">
                        這裡匯集常見問題、操作教學，幫助你快速上手、有效經營專業形象。<br>
                        不論你是第一次使用，還是想深入了解數位名片功能，這裡都是你最佳的學習起點。
                    </p>
                    <a href="{{ route('learning-center') }}" class="text-decoration-none">
                        <div class="btn-main animated-hover-4 w-fit mt-3 border border-white">前往學習中心</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- learn end -->
@endsection
