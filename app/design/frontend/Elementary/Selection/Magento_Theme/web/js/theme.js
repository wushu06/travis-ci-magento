define([
    'jquery',
    'viewportChecker',
    'slick',
    'selectric',
    "mmenu",
    "mmenuWrapper",
    'jquery/ui'

], function ($, viewportChecker, slick, selectric) {
    'use strict';
    $(document).ready(function(){

        var cloned = $("#menu").clone();
        $('.mobile-menu').html(cloned)
        $('.mobile-menu #menu').attr('id', 'mm-menu')

        /* $(".mobile-menu #mm-menu").mmenu({
            wrappers: ["magento"]
            }, {
            // configuration
           // clone: true
        });*/
        if($('#mm-menu .menu-children').length > 0) {
            $('#mm-menu .red-line').remove()
            $('#mm-menu .menu-children').unwrap()
            $('#mm-menu .level1').unwrap();
        }
        setTimeout(function () {
            $("#mm-menu").mmenu({
                slidingSubmenus: false
            });
            var API = $("#mm-menu").data( "mmenu" );

            $("#nav-icon").click(function(e) {
                e.preventDefault();
                API.open();
            });
        }, 200);

        let hoverImg = '';
        $('.level1 a').on('mouseover', function () {
            hoverImg = $(this).data('img');
            $('.parent-img .menu-img').attr('src', hoverImg);

        })
        $('.red-border li').on('mouseover', function () {
            hoverImg = $(this).data('img');
            $('.parent-img .menu-img').attr('src', hoverImg);
        })


        // $('input[type=radio]').each(function (el) {
        //     $(this).after('<div class="check"></div>').parent().addClass('custom-radio');
        // })

        // $('select').each(function (el) {
        //     $(this).after('<i class="fas fa-chevron-down"></i>');
        // })
        $('h1').viewportChecker({
            classToAdd: 'animated fadeInUp',
            offset: 0,
            removeClassAfterAnimation: false,
            repeat: false,
        });
        $('.nav.itemsX').on('init', function () {
            $('.nav.items').addClass('show-nav')
        })
            .slick({
            dots: false,
            arrows: false,

            slidesToShow: 8,
            slidesToScroll: 1,
            responsive: [

                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                }
            ]});
    });
});
