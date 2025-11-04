@extends('layouts_main.master')


@section('content')


    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row mb-3 px-lg-5 px-md-3 px-2">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-4">
                        <h2 class="mb-2">應用場景</h2>
                        <span>APPLICATION</span>
                    </div>
                    <p class="text-center text-58515D font-weight-normal">無論是出席展覽、參加市集、進行業務拜訪，還是在社群平台上建立個人品牌，數位名片都能成為你溝通與曝光的利器。</p>
                </div>
            </div>

            <div class="px-application">

            <div class="row justify-content-center align-items-center mt-5 px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-5">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/02pic1_624.jpg') }}" media="(max-width: 768px)" type="image/jpg">
                        <img src="{{ asset('assets/images/02/02pic1_1080.jpg') }}" class="img-fluid rounded-10 pur-img" alt="">
                    </picture>
                </div>
                <div class="col-lg-7 px-lg-5 px-3 py-4">
                    <span class="text-7c6796 font-weight-bold application-number">01</span>
                    <h4 class="font-weight-bold text-58515D application-title">業務拜訪｜快速建立信任的第一步</h4>
                    <p class="text-58515D font-weight-normal application-description">
                        外出拜訪客戶時，只需掃描QR CODE，即可一鍵儲存聯絡資訊，還能即時瀏覽你的產品型錄、服務介紹與社群平台，大幅提升成交效率與專業印象。
                    </p>
                </div>
            </div>

            <div class="row justify-content-center align-items-center mt-5 purbg px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-5 order-lg-2 order-1 position-relative">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/02pic2_624.jpg') }}" media="(max-width: 768px)" type="image/jpg">
                        <img src="{{ asset('assets/images/02/02pic2_1080.jpg') }}" class="img-fluid rounded-10 pur-img2" alt="">
                    </picture>
                </div>
                <div class="col-lg-7 px-lg-5 px-3 py-4 order-lg-1 order-2">
                    <span class="text-7c6796 font-weight-bold application-number">02</span>
                    <h4 class="font-weight-bold text-58515D application-title">創業者介紹品牌｜不只是名片，更是你的品牌入口</h4>
                    <p class="text-58515D font-weight-normal application-description">
                        初創品牌資源有限，數位名片不但能整合社群、官網、影片、聯絡資訊於一頁，還能隨時更新最新活動與優惠內容，成為你低成本、高效益的行銷工具。
                    </p>
                </div>
            </div>

            <div class="row justify-content-center align-items-center mt-5 px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-5">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/02pic3_624.jpg') }}" media="(max-width: 768px)" type="image/jpg">
                        <img src="{{ asset('assets/images/02/02pic3_1080.jpg') }}" class="img-fluid rounded-10 pur-img" alt="">
                    </picture>
                </div>
                <div class="col-lg-7 px-lg-5 px-3 py-4">
                    <span class="text-7c6796 font-weight-bold application-number">03</span>
                    <h4 class="font-weight-bold text-58515D application-title">參展活動｜讓陌生人秒懂你是誰</h4>
                    <p class="text-58515D font-weight-normal application-description">
                        展覽或市集面對大量人流，紙本名片常被弄丟或忘了帶回家，數位名片掃碼即儲存、不佔空間，還可展示最新產品、合作案例與品牌故事，提升轉化與記憶點。
                    </p>
                </div>
            </div>

            <div class="row justify-content-center align-items-center mt-5 purbg px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-5 order-lg-2 order-1 position-relative">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/02pic4_624.jpg') }}" media="(max-width: 768px)" type="image/jpg">
                        <img src="{{ asset('assets/images/02/02pic4_1080.jpg') }}" class="img-fluid rounded-10 pur-img2" alt="">
                    </picture>
                </div>
                <div class="col-lg-7 px-lg-5 px-3 py-4 order-lg-1 order-2">
                    <span class="text-7c6796 font-weight-bold application-number">04</span>
                    <h4 class="font-weight-bold text-58515D application-title">品牌主與創作者｜整合內容與商業合作入口</h4>
                    <p class="text-58515D font-weight-normal application-description">
                        自媒體經營者可將所有平台（IG、YOUTUBE、PODCAST、LINE 官方帳號等）整合在一張數位名片中，還可加上「合作洽詢」連結，展現完整而清晰的專業形象。
                    </p>
                </div>
            </div>

            <div class="row justify-content-center align-items-center mt-5 px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-5">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/02pic5_624.jpg') }}" media="(max-width: 768px)" type="image/jpg">
                        <img src="{{ asset('assets/images/02/02pic5_1080.jpg') }}" class="img-fluid rounded-10 pur-img" alt="">
                    </picture>
                </div>
                <div class="col-lg-7 px-lg-5 px-3 py-4">
                    <span class="text-7c6796 font-weight-bold application-number">05</span>
                    <h4 class="font-weight-bold text-58515D application-title">地方商家與門市｜打造實體與線上無縫接軌</h4>
                    <p class="text-58515D font-weight-normal application-description">
                        實體店家如髮廊、咖啡店、選物店等，可將數位名片作為延伸宣傳工具，張貼於櫃台、桌面或商品包裝上，顧客掃碼即可連結 LINE 預約、GOOGLE 評價、社群追蹤與官網導購，實體互動延伸至線上經營，一次到位。
                    </p>
                </div>
            </div>

            <div class="row justify-content-center align-items-center mt-5 purbg px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-5 order-lg-2 order-1 position-relative">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/02pic6_624.jpg') }}" media="(max-width: 768px)" type="image/jpg">
                        <img src="{{ asset('assets/images/02/02pic6_1080.jpg') }}" class="img-fluid rounded-10 pur-img2" alt="">
                    </picture>
                </div>
                <div class="col-lg-7 px-lg-5 px-3 py-4 order-lg-1 order-2">
                    <span class="text-7c6796 font-weight-bold application-number">06</span>
                    <h4 class="font-weight-bold text-58515D application-title">自由接案者與顧問｜展現專業與作品集</h4>
                    <p class="text-58515D font-weight-normal application-description">
                        攝影師、設計師、講師、顧問等專業人士，可於名片中嵌入作品集連結、專案介紹與表單洽談通道，隨時隨地行動展示自我品牌價值。
                    </p>
                </div>
            </div>

            </div>


        </div>
    </div>


    <div class="container-xxl pt-lg-3 pb-lg-3 pt-3 pb-0 overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center">
                        <h2 class="mb-2">應用範例</h2>
                        <span>APPLICATION EXAMPLES</span>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden bg-examples">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-xl-start justify-content-center align-items-center px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-xl-8 col-lg text-center">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/sam_1_640.png') }}" media="(max-width: 768px)" type="image/png">
                        <img src="{{ asset('assets/images/02/sam_1_1080.png') }}" class="img-fluid" alt="">
                    </picture>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <p class="bg-7c6796 text-white text-center font-weight-bold rounded w-fit px-4 py-1">EXAMPLE 1</p>
                    <h4 class="text-7c6796 font-weight-bold examples-title">線上型錄｜不用印、不怕改</h4>
                    <p class="text-58515D font-weight-normal">
                        兼具效率與彈性的產品展示方式，不僅能隨時更新內容，讓資訊保持最新，也省去傳統印刷與配送的繁瑣與成本。<br><br>

                        顧客可以在手機、平板或電腦上隨時瀏覽，行動裝置也能完美顯示。更重要的是，線上型錄不只是「看得到」，還能整合社群、導購連結等互動功能，提升行銷效果與品牌專業形象。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden bg-examples">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-xl-start justify-content-center align-items-center px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-xl-8 col-lg text-center">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/sam_2_640.png') }}" media="(max-width: 768px)" type="image/png">
                        <img src="{{ asset('assets/images/02/sam_2_1080.png') }}" class="img-fluid" alt="">
                    </picture>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <p class="bg-7c6796 text-white text-center font-weight-bold rounded w-fit px-4 py-1">EXAMPLE 2</p>
                    <h4 class="text-7c6796 font-weight-bold examples-title">微官網｜打造您的公司形象</h4>
                    <p class="text-58515D font-weight-normal">
                        數位名片就是你的微官網，整合品牌介紹、服務內容、社群連結、合作案例與聯繫方式，透過一個網址、一個 QR CODE，就能讓對方全面了解你，並即刻產生信任與互動。<br><br>

                        無論是個人品牌、企業團隊，或是經常需要拓展人脈的行業工作者，這是一種能兼顧「介紹力」與「轉化力」的全新數位工具。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden bg-examples">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-xl-start justify-content-center align-items-center px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-xl-8 col-lg text-center">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/sam_3_640.png') }}" media="(max-width: 768px)" type="image/png">
                        <img src="{{ asset('assets/images/02/sam_3_1080.png') }}" class="img-fluid" alt="">
                    </picture>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <p class="bg-7c6796 text-white text-center font-weight-bold rounded w-fit px-4 py-1">EXAMPLE 3</p>
                    <h4 class="text-7c6796 font-weight-bold examples-title">店家介紹｜更有記憶點</h4>
                    <p class="text-58515D font-weight-normal">
                        對於在地商家，小型工作室、美業門市、餐飲品牌而言，「第一印象」往往決定顧客是否願意深入了解你，而數位名片，正是一個簡單又強大的工具，讓你的店家資訊在幾秒內被看見、被記住、被分享。<br><br>

                        無論是櫃台掃碼、展場互動、社群推廣，還是配合活動導流，數位名片都是現代店家不可或缺的溝通工具。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden bg-examples">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-xl-start justify-content-center align-items-center px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-xl-8 col-lg text-center">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/sam_4_640.png') }}" media="(max-width: 768px)" type="image/png">
                        <img src="{{ asset('assets/images/02/sam_4_1080.png') }}" class="img-fluid" alt="">
                    </picture>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <p class="bg-7c6796 text-white text-center font-weight-bold rounded w-fit px-4 py-1">EXAMPLE 4</p>
                    <h4 class="text-7c6796 font-weight-bold examples-title">組織、商會整合｜強化人脈連結</h4>
                    <p class="text-58515D font-weight-normal">
                        透過數位名片整合技術，可將組織內所有成員資訊集中在同一系列名片頁面中呈現，不但讓對外溝通更順暢，也大幅提升組織形象的專業度與效率。<br>
                        特別適合商會、協會、非營利組織或企業團隊，不僅方便成員內部彼此聯繫，更能在對外接觸、展覽交流、官方代表出訪時展現一致性與專業感。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5 px-lg-5 px-md-3 px-2 overflow-hidden bg-examples">
        <div class="container-fluid px-lg-5 px-md-3 px-2">
            <div class="row justify-content-xl-start justify-content-center align-items-center px-lg-5 px-md-3 px-2" data-aos="fade-up" data-aos-delay="200">
                <div class="col-xl-8 col-lg text-center">
                    <picture>
                        <source srcset="{{ asset('assets/images/02/sam_5_640.png') }}" media="(max-width: 768px)" type="image/png">
                        <img src="{{ asset('assets/images/02/sam_5_1080.png') }}" class="img-fluid" alt="">
                    </picture>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <p class="bg-7c6796 text-white text-center font-weight-bold rounded w-fit px-4 py-1">EXAMPLE 5</p>
                    <h4 class="text-7c6796 font-weight-bold examples-title">節慶賀卡｜祝福一發即到，品牌好感加倍</h4>
                    <p class="text-58515D font-weight-normal">
                        在農曆新年、中秋、聖誕或開工日等重要節慶，不只是傳遞祝福的好時機，更是經營人脈、加深印象的黃金時刻。<br>
                        透過結合節慶賀卡與數位名片，用一張有溫度的視覺祝福卡，搭配品牌形象的名片頁，讓收到的人「感受到心意，也認識你的專業」。<br>
                        祝福送達的同時順勢導流，從節慶問候自然銜接接到優惠活動，宣傳與成交一氣呵成。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5 overflow-hidden bg-ba box-shadow-section">

        <div class="container py-lg-5 py-md-3 py-2">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-10 text-center">
                    <h4 class="mb-4 text-5f487c font-weight-bold ba-title line-height-2-3" data-aos="fade-up" data-aos-delay="200">｜ 讓你的名片超越交換，<br class="d-md-none d-block">創造價值連結 ｜</h4>
                    <p class="text-58515D font-weight-normal mb-5 lh-17" data-aos="fade-up" data-aos-delay="200">
                        傳統名片只是一張聯絡資訊，但數位名片能做到的遠不只如此。<br>
                        它不只是「交換聯絡方式」，更是一個能延伸對話、傳遞專業與建立信任的入口。<br>
                        透過一張數位名片，你可以展示品牌故事、作品案例、社群連結，甚至引導對方直接加LINE、預約諮詢或瀏覽網站。
                    </p>
                    <p class="text-7c6796 font-weight-bold mb-4 lh-16 fs-12" data-aos="fade-up" data-aos-delay="200">
                        每一次分享，都是一次精準的曝光；每一次被點開，都是一個潛在的合作契機。<br>
                        讓名片不再只是形式，而是開啟人脈與機會的橋樑。
                    </p>
                    <a href="{{ route('cases') }}" class="text-decoration-none" data-aos="fade-up" data-aos-delay="200">
                        <div class="btn-main animated-hover-4 text-white w-fit mx-auto px-5 py-1">觀看成功案例</div>
                    </a>
                </div>
            </div>
        </div>


    </div>

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
                <div class="col-12 d-md-none d-block h-250px"></div>
            </div>
        </div>
    </div>
    <!-- contact end -->


@endsection
