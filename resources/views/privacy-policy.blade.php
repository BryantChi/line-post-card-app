@extends('layouts_main.master')


@section('content')
    <div class="container-xxl py-5 overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-title text-center mb-5">
                        <h2 class="mb-2">網站政策</h2>
                        <span>WEBSITE POLICY</span>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10" data-aos="fade-up" data-aos-delay="200">
                    <div class="privacy-content">

                        {{-- 一、隱私權保護政策 --}}
                        <h4 class="text-58515D font-weight-bold mb-3 mt-2">一、隱私權保護政策 (Privacy Policy)</h4>
                        <p class="text-58515D font-weight-normal mb-4">
                            誠翊資訊科技有限公司（以下簡稱「本公司」）非常重視您的隱私權。依據中華民國《個人資料保護法》相關規定，為了讓您安心使用本公司的各項服務，特此說明本公司的隱私權保護政策，以保障您的權益：
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">1. 個人資料之收集目的</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司於您使用本公司服務、加入會員時，將收集您的個人資料，目的在於進行客戶管理、會員服務、行銷推廣、內部統計分析及提升服務品質。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">2. 個人資料蒐集項目</h5>
                        <p class="text-58515D font-weight-normal mb-3">當您使用本公司之服務時，可能蒐集以下資訊：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">姓名、聯絡電話、電子郵件</li>
                            <li class="mb-2">公司名稱、職稱</li>
                            <li class="mb-2">使用行為紀錄（如瀏覽紀錄、點擊紀錄）</li>
                            <li class="mb-2">裝置資訊（IP位址、瀏覽器、作業系統）</li>
                            <li class="mb-2">其他您主動提供之資訊</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">3. 個人資料利用之期間與對象</h5>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2"><strong>期間：</strong>本公司營運期間或法令規範之保存期限。</li>
                            <li class="mb-2"><strong>地區：</strong>本公司營運之地區。</li>
                            <li class="mb-2"><strong>對象：</strong>本公司、委託處理事務之合作夥伴（如物流、金流服務商）、政府機關或司法單位。</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">3-1. 個人資料跨境傳輸</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司使用之第三方服務（包括 AI 運算平台及雲端儲存服務）可能將您的個人資料傳輸至中華民國境外進行處理。本公司將確保接受資料之第三方具備與本公司相當之個人資料保護措施，並依個人資料保護法相關規定辦理。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">4. 資料安全保護</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司採用適當的安全防護技術與管理措施，以防止您的個人資料被洩漏、篡改或毀損。僅有經過授權的人員能接觸您的個人資料。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">5. 使用者權利</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            您可以隨時透過電子郵件 <a href="mailto:cheni.com.tw@gmail.com">cheni.com.tw@gmail.com</a> 聯繫我們，針對您的個人資料行使查詢、閱覽、補充、更正、停止收集處理或刪除之權利。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">6. Cookie 使用</h5>
                        <p class="text-58515D font-weight-normal mb-3">本公司之服務使用以下類型之 Cookie：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">（1）<strong>必要性 Cookie：</strong>維持服務正常運作，無法關閉；</li>
                            <li class="mb-2">（2）<strong>分析性 Cookie：</strong>如 Google Analytics，用於統計使用行為，以改善服務品質；</li>
                            <li class="mb-2">（3）<strong>功能性 Cookie：</strong>記憶您的偏好設定。</li>
                        </ul>
                        <p class="text-58515D font-weight-normal mb-4">
                            您可透過瀏覽器設定拒絕或限制非必要 Cookie，惟部分功能可能受影響。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">7. 政策修訂</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司有權於任何時間修改本隱私權保護政策，建議您定期查看（<a href="https://business.cheni.tw/" target="_blank">https://business.cheni.tw/</a>），修訂後若您繼續使用本服務，視為同意修改內容。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">8. 聯絡方式</h5>
                        <p class="text-58515D font-weight-normal mb-3">如有相關問題，請聯繫：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">公司名稱：誠翊資訊科技有限公司</li>
                            <li class="mb-2">聯絡信箱：<a href="mailto:cheni.com.tw@gmail.com">cheni.com.tw@gmail.com</a></li>
                            <li class="mb-2">聯絡電話：03-8511-126</li>
                        </ul>

                        <hr class="my-5">

                        {{-- 二、使用者條款 --}}
                        <h4 class="text-58515D font-weight-bold mb-3 mt-2">二、使用者條款</h4>
                        <p class="text-58515D font-weight-normal mb-4">
                            歡迎您使用誠翊資訊科技有限公司（以下簡稱「本公司」）提供之服務。當您使用本服務時，即表示您已閱讀、瞭解並同意本條款。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">1. 服務內容</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司提供 AI 數位名片、網站建置及相關數位服務（以下簡稱「本服務」），實際內容以網站公告或合約為準。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">2. 帳號與使用</h5>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">使用者須提供正確資訊</li>
                            <li class="mb-2">帳號應妥善保管，不得轉讓或借用</li>
                            <li class="mb-2">因帳號使用所產生之行為，使用者需自行負責</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">3. 使用規範</h5>
                        <p class="text-58515D font-weight-normal mb-3">使用者不得：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">從事違法行為</li>
                            <li class="mb-2">上傳不實或侵權內容</li>
                            <li class="mb-2">干擾或破壞系統運作</li>
                            <li class="mb-2">未經授權使用他人資料</li>
                        </ul>
                        <p class="text-58515D font-weight-normal mb-4">
                            若您從事禁止行為，本公司可暫停或終止您的帳戶。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">4. 服務費用與付款</h5>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">本服務依方案收費，價格以公告或報價單為準。</li>
                            <li class="mb-2">第一次製作名片，需含視覺頁面設計費用 NT$1,500 元。</li>
                            <li class="mb-2">本服務於到期未續約時，系統將自動備份您的名片資料。若日後需重新開通服務，將酌收設定費用 NT$2,000 元。</li>
                            <li class="mb-2">未續約期間，名片仍可保留瀏覽功能 90 天（期間內僅供查看，無法進行編輯或更新）。若超過 90 天仍未續約，系統將停止名片服務、關閉顯示並刪除雲端上之相關資料。</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">5. 服務中斷與終止</h5>
                        <p class="text-58515D font-weight-normal mb-3">本公司得於以下情況暫停或終止服務：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">系統維護或升級</li>
                            <li class="mb-2">不可抗力因素</li>
                            <li class="mb-2">使用者違反條款</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">6. 智慧財產權</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司服務所使用之軟體或程式、網站上所有內容（包括但不限於文字、圖片、標誌），其著作權、專利權、商標權及其他智慧財產權均屬本公司或其權利人所有，未經書面授權，不得逕自使用。使用者上傳至名片之圖片、文字、商標，須保證具備合法使用權，若涉及侵權，由使用者自行承擔法律責任。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">7. 條款修改</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司有權於任何時間修改本服務條款，建議您定期查看。修改後的內容將公佈於網站上，不另行個別通知。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">8. 爭議解決與管轄法院</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本條款之解釋及效力依中華民國法律為準。如因本條款或本服務所生之爭議，雙方同意以花蓮地方法院為第一審管轄法院。
                        </p>

                        <hr class="my-5">

                        {{-- 三、免責聲明 --}}
                        <h4 class="text-58515D font-weight-bold mb-3 mt-2">三、免責聲明</h4>
                        <p class="text-58515D font-weight-normal mb-4">
                            歡迎您使用誠翊資訊科技有限公司（以下簡稱「本公司」）之 AI 數位名片（以下簡稱「本服務」）服務，使用前請詳閱以下免責聲明。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">1. 使用限制</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本服務嚴禁用於任何非法或未經授權的用途，包括但不限於利用 AI 技術進行欺詐、誤導、侵犯隱私、騷擾或其他不道德行為。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">2. 資訊正確性</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本服務所呈現之內容，無法保證 AI 生成內容的準確性或適用性，使用者應自行核對內容是否正確無誤。請理解，儘管 AI 演算法技術先進，其生成的內容仍受限於現有數據和演算法的限制。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">3. 第三方服務</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本服務可能串接第三方平台（如 LINE、社群媒體等），該等網站之內容與隱私政策與本公司無涉，使用者應自行評估風險。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">4. 責任限制</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            在任何情況下，本服務及其開發者均不對因使用其 AI 工具可能導致的任何形式的間接、偶然、特殊、懲罰性或後果性損害負責。因本服務瑕疵所導致之損害，本公司之賠償責任以該名使用者當年度所支付之服務費用為最高上限。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">5. 使用風險</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司將盡力維持服務之穩定，但對於因電信服務中斷、硬體故障、不可抗力（如地震、天災）或第三方攻擊所導致之服務中斷、延遲、資料遺失或損害，本公司不負損害賠償責任。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">6. 聲明修訂</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司保留修改、暫停或終止服務之權利。如涉及重大變更，本公司將於網站公告或以電子郵件通知使用者，並給予合理因應期間。服務可能因技術、法律或商業決策而發生調整，使用者繼續使用服務即視為同意相關變更。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">7. 遵守法律規定</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            使用本服務時，您有責任遵守所在地之所有相關法律規定，包括但不限於個人資料保護法及有關人工智慧技術之適用規範。
                        </p>

                        <p class="text-58515D font-weight-normal mb-4">
                            本服務可能不時更新本免責聲明。使用本服務即表示您同意這些條款及其任何更新。建議您定期查看免責聲明，以了解最新變更。
                        </p>
                        <p class="text-58515D font-weight-normal mb-4">
                            透過使用本服務，您確認已閱讀、理解並同意遵守本免責聲明的所有條款。
                        </p>

                        <hr class="my-5">

                        {{-- 四、退款政策 --}}
                        <h4 class="text-58515D font-weight-bold mb-3 mt-2">四、退款政策</h4>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司提供之 AI 數位名片設計、網站建置及相關設計勞務，屬客製化服務。依消費者保護法第 19 條第 2 項及《通訊交易解除權合理例外情事適用準則》，本類服務不適用 7 天猶豫期（鑑賞期），請委託前審慎評估。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">1. 退款適用條件</h5>
                        <p class="text-58515D font-weight-normal mb-3">退款申請須同時符合以下全部條件，方予受理：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">付款完成後 3 個日曆天內提出書面申請</li>
                            <li class="mb-2">設計作業尚未開始（本公司尚未產出任何初稿、草圖或提案）</li>
                            <li class="mb-2">以電子郵件向本公司提出正式退款申請</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">2. 退款金額計算</h5>
                        <p class="text-58515D font-weight-normal mb-3">符合退款條件者，退還金額計算如下：</p>
                        <p class="text-58515D font-weight-normal mb-3">
                            <strong>退還金額 ＝ 已繳費用 − 手續費（已繳費用總額之 5%）</strong>
                        </p>
                        <p class="text-58515D font-weight-normal mb-4">
                            範例：若已繳費用為 NT$10,000，手續費為 NT$500，實際退還 NT$9,500。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">3. 不予退款之情形</h5>
                        <p class="text-58515D font-weight-normal mb-3">有下列任一情形，本公司不受理退款申請：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">付款完成後逾 3 個日曆天始提出申請</li>
                            <li class="mb-2">本公司已開始設計作業（包含但不限於提供初稿、草圖、色稿或任何提案）</li>
                            <li class="mb-2">訂閱制服務（AI 數位名片年費）一經開通，恕不退費</li>
                            <li class="mb-2">因使用者違反本服務條款遭終止帳戶者</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">4. 申請方式與退款流程</h5>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2"><strong>申請：</strong>請以電子郵件寄至 <a href="mailto:cheni.com.tw@gmail.com">cheni.com.tw@gmail.com</a>，信件主旨請註明「退款申請」，並說明付款日期及申請原因。</li>
                            <li class="mb-2"><strong>審核：</strong>本公司將於收到申請後 3 個工作天內回覆審核結果。</li>
                            <li class="mb-2"><strong>退款：</strong>審核通過後，退款將於 14 個工作天內匯回原付款帳戶或方式。</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">5. 其他說明</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            本退款政策如有未盡事宜，依本公司與委託方簽訂之合約約定為準。如有爭議，依本網站政策「二、使用者條款 第 8 條」所定管轄法院處理。
                        </p>

                        <hr class="my-5">

                        {{-- 五、消費者權益說明 --}}
                        <h4 class="text-58515D font-weight-bold mb-3 mt-2">五、消費者權益說明</h4>
                        <p class="text-58515D font-weight-normal mb-4">
                            誠翊資訊科技有限公司（以下簡稱「本公司」）依據消費者保護法及相關法令，說明消費者於使用本公司服務時所享有之各項權益，請於委託前詳閱。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">1. 服務資訊充分揭露</h5>
                        <p class="text-58515D font-weight-normal mb-3">本公司於消費者訂購服務前，將提供以下資訊，確保您在充分了解的情況下做出決策：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2"><strong>服務項目說明：</strong>包含 AI 數位名片、網站建置、相關設計勞務之服務範疇、功能及限制。</li>
                            <li class="mb-2"><strong>費用明細：</strong>各方案費用、一次性設計費、年費訂閱、重新開通費等，均以官網公告或書面報價單為準。</li>
                            <li class="mb-2"><strong>服務期限：</strong>訂閱制服務之起訖日期、到期後資料保留期間（90 天）及後續處理方式。</li>
                            <li class="mb-2"><strong>服務範圍限制：</strong>AI 生成內容之準確性限制、第三方平台（如 LINE）整合之相依性等。</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">2. 猶豫期（鑑賞期）適用說明</h5>
                        <p class="text-58515D font-weight-normal mb-3">
                            依消費者保護法第 19 條規定，通訊交易消費者享有收受商品或服務後 7 日之猶豫期。惟本公司所提供之服務涉及以下情形，依法不適用猶豫期，請委託前審慎確認：
                        </p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2"><strong>客製化設計勞務</strong>（如 AI 數位名片視覺設計、網站客製開發）：屬依消費者要求所為之客製化給付，一經開始執行即不適用猶豫期。</li>
                            <li class="mb-2"><strong>已開通之訂閱制數位服務：</strong>服務一經啟用，依數位內容及服務之性質，不適用猶豫期。</li>
                            <li class="mb-2">適用範圍若有疑義，請於付款前聯繫本公司確認。</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">3. 契約審閱權</h5>
                        <p class="text-58515D font-weight-normal mb-4">
                            依消費者保護法第 11-1 條規定，消費者對定型化契約有合理審閱期間之權利。本公司提供之服務條款、報價單及相關合約文件，消費者得於簽約或付款前要求提供紙本或電子檔，充分審閱後再行決定是否委託。如有任何條款疑義，請於付款前向本公司提出說明。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">4. 服務品質保障</h5>
                        <p class="text-58515D font-weight-normal mb-3">本公司承諾依約定規格與期程提供服務，並保障以下消費者權益：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2"><strong>服務瑕疵：</strong>若本公司所交付之成果有明顯瑕疵（非因消費者提供素材錯誤所致），本公司將免費進行修正，修正次數以合約約定為準。</li>
                            <li class="mb-2"><strong>資料安全：</strong>本公司依個人資料保護法妥善保管您提供之個人資料及商業資訊，不得擅自對外揭露或另作他用。</li>
                            <li class="mb-2"><strong>服務連續性：</strong>訂閱制服務如遭本公司主動終止（非因使用者違約），本公司應提前 30 天書面通知，並按比例退還剩餘費用。</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">5. 消費者申訴管道</h5>
                        <p class="text-58515D font-weight-normal mb-3">若您對本公司服務有任何疑慮或申訴，請依下列管道反映，本公司將於 5 個工作天內回覆處理結果：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2"><strong>電子郵件申訴：</strong><a href="mailto:cheni.com.tw@gmail.com">cheni.com.tw@gmail.com</a>（主旨請註明「客戶申訴」）</li>
                            <li class="mb-2"><strong>電話申訴：</strong>03-8511-126（服務時間:週一至週五 09:00–18:00）</li>
                        </ul>
                        <p class="text-58515D font-weight-normal mb-3">若與本公司協商未果，消費者尚可透過以下外部管道尋求協助：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">行政院消費者保護會申訴專線:1950</li>
                            <li class="mb-2">花蓮縣政府消費者服務中心:03-8227171</li>
                            <li class="mb-2">線上申訴:行政院消費者保護會網站（<a href="https://www.cpc.ey.gov.tw" target="_blank">https://www.cpc.ey.gov.tw</a>）</li>
                        </ul>

                        <h5 class="text-58515D font-weight-bold mb-3">6. 個人資料權利行使</h5>
                        <p class="text-58515D font-weight-normal mb-3">
                            依個人資料保護法，您對本公司持有之個人資料享有以下權利，可隨時透過 <a href="mailto:cheni.com.tw@gmail.com">cheni.com.tw@gmail.com</a> 提出申請：
                        </p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">查詢或閱覽本公司持有之您的個人資料</li>
                            <li class="mb-2">補充或更正不正確之個人資料</li>
                            <li class="mb-2">停止蒐集、處理或利用您的個人資料</li>
                            <li class="mb-2">請求刪除您的個人資料（惟依法令須保存者除外）</li>
                        </ul>
                        <p class="text-58515D font-weight-normal mb-4">
                            本公司將於收到申請後 15 個工作天內回覆處理結果。
                        </p>

                        <h5 class="text-58515D font-weight-bold mb-3">7. 法規依據</h5>
                        <p class="text-58515D font-weight-normal mb-3">本消費者權益說明依據以下法規訂定，如本說明與相關法規抵觸，以法規規定為準：</p>
                        <ul class="text-58515D font-weight-normal mb-4 pl-4">
                            <li class="mb-2">消費者保護法及消費者保護法施行細則</li>
                            <li class="mb-2">個人資料保護法及個人資料保護法施行細則</li>
                            <li class="mb-2">電子商務消費者保護綱領</li>
                            <li class="mb-2">行政院消費者保護委員會相關函示</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
