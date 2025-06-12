<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>誠翊資訊數位名片專區 | 結合雲端科技與數位設計，開啟您的智慧名片新時代!</title>

    <!-- Favicon -->
    <link href="{{ asset('assets/img/fimgs/favicon.ico') }}" rel="icon">

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
        integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
        crossorigin="anonymous" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />

    @vite('resources/sass/app.scss')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}?v={{ time() }}"/>
    @stack('third_party_stylesheets')
    @stack('page_css')

</head>

{{ $slot }}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"
        integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg=="
        crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js "></script>

<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

<script>
    $(function() {
        bsCustomFileInput.init();
    });

    // $("input[data-bootstrap-switch]").each(function() {
    //     $(this).bootstrapSwitch('state', $(this).prop('checked'));
    // });

    $('select').select2({
        language: 'zh-TW',
        width: '100%',
        maximumInputLength: 100,
        minimumInputLength: 0,
        tags: false,
        placeholder: '請選擇',
        allowClear: true
    });
</script>
@stack('third_party_scripts')
@stack('page_scripts')

<script>
    function check(e, msg = '是否刪除？') {
        Swal.fire({
            title: msg,
            text: "刪除後您將無法恢復！",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '刪除',
            cancelButtonText: '取消'
        }).then((result) => {
            if (result.isConfirmed) {
                e.parentElement.parentElement.submit();
            }
        })
    }

    $(function() {
        var idleTime = 0;

        // 增加每分鐘的閒置時間
        var idleInterval = setInterval(timerIncrement, 60000); // 1 分鐘

        // 重置閒置計時器當有活動發生時
        $(this).mousemove(function(e) {
            idleTime = 0;
        });
        $(this).keypress(function(e) {
            idleTime = 0;
        });

        function timerIncrement() {
            if ('{{ !Auth::check() }}') {
                return;
            }
            idleTime = idleTime + 1;
            if (idleTime > 30) { // 30 分鐘
                clearInterval(idleInterval);
                Swal.fire({
                    title: '注意！',
                    text: "閒置太久<已自動登出>！",
                    icon: 'warning',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '確認',
                    cancelButtonText: '取消',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // $.ajax({
                        //     url: '/logout', // Laravel 登出路由
                        //     type: 'POST',
                        //     // 需要添加 CSRF Token
                        //     success: function(result) {
                        //         window.location.href = '/login'; // 重定向到登入頁面
                        //     }
                        // });
                        // $('form#logout-form').submit();
                        window.location.reload();
                    }
                })

            }
        }

        // 監聽 .shepherd-element 是否被加入畫面，如果在加入css
        const observer = new MutationObserver(function(mutationsList, observer) {
            for(const mutation of mutationsList) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1 && node.classList.contains('shepherd-element')) {
                            // 在這裡加入你想要應用的 CSS
                            // 例如：改變背景顏色
                            // node.style.backgroundColor = '#333333';
                            // console.log('.shepherd-element has been added to the DOM.');

                            // 針對 .shepherd-element 添加樣式 max-width
                            node.style.maxWidth = '350px';

                            // 如果 shepherd-element 內部有 .shepherd-content, .shepherd-header, .shepherd-text, .shepherd-footer
                            // 可以針對這些元素添加樣式
                            const shepherdContent = node.querySelector('.shepherd-content');
                            if (shepherdContent) {
                                // shepherdContent.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                                // shepherdContent.style.borderRadius = '8px';
                            }
                            const shepherdHeader = node.querySelector('.shepherd-header');
                            if (shepherdHeader) {
                                // close button
                                const closeButton = shepherdHeader.querySelector('.shepherd-cancel-icon');
                                if (closeButton) {
                                    // closeButton.style.color = 'white';
                                    // closeButton.style.cursor = 'pointer';
                                }
                                // shepherdHeader.style.backgroundColor = 'transparent';
                                // shepherdHeader.style.padding = '0.5em 0.75em';
                            }
                            const shepherdText = node.querySelector('.shepherd-text');
                            if (shepherdText) {
                                shepherdText.style.color = '#333';
                                // shepherdText.style.padding = '0.5em 0.75em';
                            }
                            const shepherdFooter = node.querySelector('.shepherd-footer');
                            if (shepherdFooter) {
                                // shepherdFooter.style.padding = '0 0.75em 0.75em';
                                const buttons = shepherdFooter.querySelectorAll('.shepherd-button');
                                buttons.forEach(button => {
                                    // 第一個按鈕的樣式
                                    if (button === buttons[0]) {
                                        button.style.backgroundColor = '#fff';
                                        button.style.color = '#333';
                                        button.style.border = '1px solid #333';
                                        button.style.borderRadius = '4px';
                                        button.style.padding = '0.5em 1em';
                                    } else {
                                        // 其他按鈕的樣式
                                        button.style.backgroundColor = '#6c757d';
                                        button.style.color = 'white';
                                        button.style.border = 'none';
                                        button.style.borderRadius = '4px';
                                        button.style.padding = '0.5em 1em';
                                    }
                                    // button.style.backgroundColor = '#007bff';
                                    // button.style.color = 'white';
                                    // button.style.border = 'none';
                                    // button.style.borderRadius = '4px';
                                    // button.style.padding = '0.5em 1em';
                                    // button.style.marginRight = '0.5em';

                                    // 移除最後一個按鈕的 margin-right
                                    if (button === buttons[buttons.length - 1]) {
                                        // button.style.marginRight = '0';
                                    }
                                });
                            }
                        }
                    });
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });

    })
</script>
@vite('resources/js/app.js')

</html>
