@extends('layouts_main.master')


@section('content')
    <div class="container-xxl py-5 overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">產品特色</h2>
                        <span>FEATURES</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="container ">
            {{-- px-lg-5 mx-lg-5 px-md-3 mx-md-3 px-2 mx-2 --}}
            <div class="row justify-content-center ">
                <div class="col-lg">
                    <div class="features-item d-flex flex-row mb-5">
                        <div class="features-icon mr-2 mb-lg-0 mb-3">
                            <img src="{{ asset('assets/images/01/01icon_1.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="features-number position-relative">
                            <span class="text-7c6796 font-weight-bold mr-2">01</span>
                            <div class="feature-number-line mx-auto mt-3"></div>
                        </div>
                        <div class="features-content">
                            <div class="features-content-title mb-3 d-flex">
                                <span class="features-title text-58515D font-weight-bold text-box">一掃即存，免安裝、零阻力分享</span>
                            </div>
                            <p class="features-text text-58515D font-weight-normal mb-0">
                                只需要LINE，無需下載其他APP，即可快速儲存聯絡資訊、開啟網站與社群連結，讓每一次介紹都順暢無礙。
                            </p>
                        </div>
                    </div>
                    <div class="features-item d-flex flex-row mb-5">
                        <div class="features-icon mr-2 mb-lg-0 mb-3">
                            <img src="{{ asset('assets/images/01/01icon_2.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="features-number position-relative">
                            <span class="text-7c6796 font-weight-bold mr-2">02</span>
                            <div class="feature-number-line mx-auto mt-3"></div>
                        </div>
                        <div class="features-content">
                            <div class="features-content-title mb-3 d-flex">
                                <span class="features-title text-58515D font-weight-bold text-box">多平台整合，打造你的品牌入口匯流點</span>
                            </div>
                            <p class="features-text text-58515D font-weight-normal mb-0">
                                結合官網、FB、LINE、IG、YOUTUBE、商品頁與預約資訊，一張數位名片即是你的行動微型官網。
                            </p>
                        </div>
                    </div>
                    <div class="features-item d-flex flex-row mb-5">
                        <div class="features-icon mr-2 mb-lg-0 mb-3">
                            <img src="{{ asset('assets/images/01/01icon_3.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="features-number position-relative">
                            <span class="text-7c6796 font-weight-bold mr-2">03</span>
                            <div class="feature-number-line mx-auto mt-3"></div>
                        </div>
                        <div class="features-content">
                            <div class="features-content-title mb-3 d-flex">
                                <span class="features-title text-58515D font-weight-bold text-box">雲端即時更新，永不過期的名片</span>
                            </div>
                            <p class="features-text text-58515D font-weight-normal mb-0">
                                可隨時修改聯絡方式、職稱、作品連結或優惠訊息，確保對外資訊即時、正確，減少紙本名片重印成本。
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 text-center mb-lg-0 mb-4">
                    <img src="{{ asset('assets/images/01/fea_mobile.svg') }}" class="img-fluid features-img" alt="">
                </div>

                <div class="col-lg">
                    <div class="features-item d-flex flex-row flex-lg-row-reverse mb-5">
                        <div class="features-icon mr-2 mb-lg-0 mb-3">
                            <img src="{{ asset('assets/images/01/01icon_4.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="features-number position-relative">
                            <span class="text-7c6796 font-weight-bold mr-2">04</span>
                            <div class="feature-number-line mx-auto mt-3"></div>
                        </div>
                        <div class="features-content">
                            <div class="features-content-title mb-3 d-flex">
                                <span class="features-title text-58515D font-weight-bold text-box">專屬設計版型，展現你的專業品味</span>
                            </div>
                            <p class="features-text text-58515D font-weight-normal mb-0">
                                客製模板，符合品牌色系與風格定位，打造令人印象深刻的品牌識別。
                            </p>
                        </div>
                    </div>
                    <div class="features-item d-flex flex-row flex-lg-row-reverse mb-5">
                        <div class="features-icon mr-2 mb-lg-0 mb-3">
                            <img src="{{ asset('assets/images/01/01icon_5.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="features-number position-relative">
                            <span class="text-7c6796 font-weight-bold mr-2">05</span>
                            <div class="feature-number-line mx-auto mt-3"></div>
                        </div>
                        <div class="features-content">
                            <div class="features-content-title mb-3 d-flex">
                                <span class="features-title text-58515D font-weight-bold text-box">互動追蹤數據，掌握名片觸及效益</span>
                            </div>
                            <p class="features-text text-58515D font-weight-normal mb-0">
                                提供點擊次數、分享次數等基本分析，幫助你了解名片成效，優化行銷策略。
                            </p>
                        </div>
                    </div>
                    <div class="features-item d-flex flex-row flex-lg-row-reverse mb-5">
                        <div class="features-icon mr-2 mb-lg-0 mb-3">
                            <img src="{{ asset('assets/images/01/01icon_6.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="features-number position-relative">
                            <span class="text-7c6796 font-weight-bold mr-2">06</span>
                            <div class="feature-number-line mx-auto mt-3"></div>
                        </div>
                        <div class="features-content">
                            <div class="features-content-title mb-3 d-flex">
                                <span class="features-title text-58515D font-weight-bold text-box">AI輔助，生成簡介</span>
                            </div>
                            <p class="features-text text-58515D font-weight-normal mb-0">
                                AI輔助生成技術，打造更符合需求的專業文案；只需提供方向，其餘交給智慧演算完成，簡單、快速、可靠。
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="container-xxl py-5 overflow-hidden pg-features-bg">
        <div class="container">
            <div class="row" id="features2">
                <div class="col-lg-auto">
                    <div class="section-title mb-5">
                        <h2 class="mb-2 text-white">技術優勢</h2>
                        <span class="text-uppercase text-white-50">
                            TECHNICAL<br>ADVANTAGES
                        </span>
                    </div>
                </div>

                <div class="col features2-content">
                    <div class="row justify-content-center align-items-center mb-5">
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <img src="{{ asset('assets/images/01/tech_pic1.jpg') }}" class="img-fluid rounded-10" alt="">
                        </div>
                        <div class="col-lg-7">
                            <h5 class="text-white font-weight-bold mb-3">模組化架構，彈性擴充整合應用</h5>
                            <p class="text-ede9f2 font-weight-normal mb-0">
                                採用模組化設計，除了基本名片資訊呈現，還可整合商品展示、預約表單、社群連結、影片播放等功能，依需求彈性擴充，快速打造專屬微型網站。
                            </p>
                        </div>
                    </div>

                    <div class="row justify-content-center align-items-center mb-5">
                        <div class="col-lg-5 mb-4 mb-lg-0 order-lg-2 order-1">
                            <img src="{{ asset('assets/images/01/tech_pic2.jpg') }}" class="img-fluid rounded-10" alt="">
                        </div>
                        <div class="col-lg-7 order-lg-1 order-2">
                            <h5 class="text-white font-weight-bold mb-3">全平台相容與響應式設計</h5>
                            <p class="text-ede9f2 font-weight-normal mb-0">
                                全面支援手機、平板與電腦，並針對不同裝置進行介面優化，確保使用者在各平台皆有一致且流暢的瀏覽體驗。
                            </p>
                        </div>
                    </div>

                    <div class="row justify-content-center align-items-center mb-5">
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <img src="{{ asset('assets/images/01/tech_pic3.jpg') }}" class="img-fluid rounded-10" alt="">
                        </div>
                        <div class="col-lg-7">
                            <h5 class="text-white font-weight-bold mb-3">雲端後台管理，支援多名片管理與即時更新</h5>
                            <p class="text-ede9f2 font-weight-normal mb-0">
                                提供專屬後台系統，可快速建立與管理多張數位名片、即時更新內容，適用於個人接案者與企業團隊集中管理需求。
                            </p>
                        </div>
                    </div>

                    <div class="row justify-content-center align-items-center mb-5">
                        <div class="col-lg-5 mb-4 mb-lg-0 order-lg-2 order-1">
                            <img src="{{ asset('assets/images/01/tech_pic4.jpg') }}" class="img-fluid rounded-10" alt="">
                        </div>
                        <div class="col-lg-7 order-lg-1 order-2">
                            <h5 class="text-white font-weight-bold mb-3">AI 智慧文案引擎，首頁品牌敘事即刻就緒</h5>
                            <p class="text-ede9f2 font-weight-normal mb-0">
                                依品牌資料，自動生成精煉介紹文字並同步發布於數位名片首頁，節省撰寫品牌簡介的時間成本；保留人工編修控管，快速上線又維持一致專業形象。
                            </p>
                        </div>
                    </div>


                </div>

            </div>



        </div>
    </div>

    <div class="container-xxl py-5 pg-intro-table-bg">
        <div class="container">
            <div class="row justify-content-center" id="intro2">
                <div class="col-12">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">新世代的數位連結利器</h2>
                        <p class="text-7c6796" style="font-size: smaller;">
                            數位名片 VS. 傳統名片 | 9 大指標比較表
                        </p>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive intro2-table">

                        <table class="table table-borderless mb-0">
                            <thead class="text-center rounded-10">
                                <tr>
                                    <th class="align-middle bg-7c6796 text-nowrap">
                                        <h5 class="mb-0 text-white">項目</h5>
                                    </th>
                                    <th class="align-middle bg-8954cb-552f85 text-nowrap" style="min-width: 300px;">
                                        <h5 class="mb-0 text-white">
                                            <img src="{{ asset('assets/images/00-hp/crown.png') }}" class="img-fluid" width="40px"
                                                alt="">
                                            誠翊資訊 AI數位名片
                                        </h5>
                                    </th>
                                    <th class="align-middle bg-7c6796 text-nowrap">
                                        <h5 class="mb-0 text-white">傳統名片</h5>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
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

    <!-- contact start -->
    <div class="container-xxl py-5 hp-cta-bg overflow-hidden">
        <div class="container-fluid px-lg-5 mx-lg-5 px-md-3 mx-md-3 px-2 mx-2">
            <div class="row py-lg-5 py-3">
                <div class="col-lg-10 col-md-6">
                    <h4 class="text-5f487c font-weight-bold">打造你的專屬數位名片，讓每一次的自我介紹更有價值</h4>
                    <p class="text-58515D font-weight-normal line-height-2" style="font-size: smaller;">
                        介紹自己，不該只是名字與電話，數位名片為你說更多，讓每一次出場都更有影響力<br>
                        現在就打造專屬的LINE AI數位名片，一鍵分享、即時更新，無論在社群、會議、或陌生開場，都能留下深刻印象
                    </p>
                    <a href="https://lin.ee/HnB194r" target="_blank">
                        <img src="{{ asset('assets/images/00-hp/bu_line.svg') }}" class="img-fluid" width="230px" alt="">
                    </a>
                </div>
                <div class="col-12 d-md-none d-block" style="height: 230px;"></div>
            </div>
        </div>
    </div>
    <!-- contact end -->
@endsection
