(function ($) {
    "use strict";

    $(window).on("load", function () {
        // $('.site-wrap').addClass('fixed-top');
        // 隱藏 Loading 效果
        // setTimeout(function () {
        //     $("#loading").fadeOut("slow");
        // }, 350);

    });

    $(function () {
        // Custom JavaScript code can be added here
        let $scrollVal = 200;
        if ($(window).width() < 768) {
            $scrollVal = 100;
        }

        $(window).on("scroll", function () {
            if ($(this).scrollTop() > $scrollVal) {
                $('.back-to-top').fadeIn("slow");

                $('.site-navbar').css({
                    backgroundColor: '#603ab1',
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    zIndex: 1000,
                    transition: 'background-color 0.5s ease-in-out',
                })
            } else {
                $('.back-to-top').fadeOut("slow");

                $('.site-navbar').css({
                    backgroundColor: 'transparent',
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    zIndex: 1000,
                    transition: 'background-color 0.5s ease-in-out',
                })
            }
        }).trigger("scroll");
        $(".back-to-top").on("click", function () {
            $("html, body").animate({ scrollTop: 0 }, 1500);
            return false;
        });


    });



})(jQuery);
