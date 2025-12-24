/**
 * Open OldImages in Light Box
 * https://rama.com.ua/skilki-koshtuye-novorichniy-stil-2020/
 */
// document.addEventListener("DOMContentLoaded", function(event) {
    document.querySelectorAll('a[rel^="attachment"]').forEach(
        el => {
            let ImgLink = el.querySelector('img').src;
            if (!ImgLink){
                ImgLink = el.querySelector('img').dataset.src;
            }
            el.setAttribute("href", ImgLink);
            console.log(el.querySelector('img').src);
        }
    )
// });

jQuery(document).ready(function($) {

    /**
     * CopyLink
     */
    // $('[href="#copylink"]').off('click');
    $('[href="#copylink"]').removeAttr("onclick").removeAttr("target");
    jQuery('body').on('click','[href="#copylink"]',function(event){

        function ClipBoard(){
            //     let ThisLink = window.location;
            event.preventDefault();
            let textToCopy = window.location;
            navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    // console.log(textToCopy);
                })
                .catch((error) => {
                    console.log(`❌ Copy failed! ${error}`);
                });
        }
        ClipBoard();
    });



});


/**
 * Novo Inicialize
 * @type {number}
 *
 * INLINE PHP START
 */
// Inicialize
jQuery(document).ready(function($){
    // Перевіряємо чи завантажений nivo-lightbox
    if (typeof jQuery.fn.nivoLightbox === 'undefined') {
        return;
    }
    // Lightbox для галерей та зображень (працює на всіх сторінках)
    jQuery('a[rel="lightbox"], a[rel^="attachment"], a[data-rel^="attachment-"], [data-rel^="lightbox"], .nivo-lightbox-content, .gallery a[href*=".jpg"], .gallery a[href*=".jpeg"], .gallery a[href*=".png"], .gallery a[href*=".gif"], .gallery a[href*=".webp"], body.single-kalendar_istor_podiy .single-featured > a, body.single-announce .single-featured > a, body.single-project .single-featured > a').nivoLightbox({
        // The effect to use when showing the lightbox
        // fade, fadeScale, slideLeft, slideRight, slideUp, slideDown, fall
        effect: 'fadeScale',

        // The lightbox theme to use
        theme: 'default',

        // Enable/Disable keyboard navigation
        keyboardNav: true,

        // Click image to close
        clickImgToClose: false,

        // Click overlay to close
        clickOverlayToClose: true,

        // Callback functions
        onInit: function(){},
        beforeShowLightbox: function(){},
        afterShowLightbox: function(lightbox){},
        beforeHideLightbox: function(){},
        afterHideLightbox: function(){},
        beforePrev: function(element){},
        onPrev: function(element){},
        beforeNext: function(element){},
        onNext: function(element){},

        // Error message
        errorMessage: 'The requested content cannot be loaded. Please try again later.'

    });
});
/**
 * INLINE PHP
 * END
 * @type {number}
 */




// Novo Lightbox
var CoutnImages = document.querySelectorAll('.single-container img').length;
jQuery(document).ready(function($){
    // Перевіряємо чи завантажений nivo-lightbox
    if (typeof jQuery.fn.nivoLightbox === 'undefined') {
        return;
    }

    // WordPress Gallery - додаємо атрибути для групування зображень
    jQuery('.gallery').each(function(galleryIndex) {
        var galleryId = 'wp-gallery-' + galleryIndex;
        jQuery(this).find('a').each(function() {
            jQuery(this).attr('data-lightbox-gallery', galleryId);
            jQuery(this).attr('data-rel', 'lightbox');
        });
    });

    if(CoutnImages > 1){
        // Old Attachemnt
        jQuery('#content a[rel*="attachment"], #content a[rel*="attachment"]').each( function() {
    //         var attr = jQuery( this ).attr( 'data-rel' );

            // check data-rel attribute first
    //         if ( typeof attr === 'undefined' || attr == false ) {
            // if not found then try to check rel attribute for backward compatibility
            jQuery( this ).attr( 'data-lightbox-gallery','myGallery' );
            jQuery(this).attr("data-rel","lightbox");
    //         }

        } );

        // Inicialize
        jQuery('a[rel="lightbox"], [data-rel^="lightbox"], a[data-rel^="attachment-"], a[rel^="attachment"], .lightbox, body.single-announce .single-featured > a,  body.single-project .single-featured > a, body.single-kalendar_istor_podiy .single-featured > a').each( function() {
            var attr = jQuery( this ).attr( 'data-rel' );

            // check data-rel attribute first
            if ( typeof attr === 'undefined' || attr == false ) {
                // if not found then try to check rel attribute for backward compatibility
                attr = jQuery( this ).attr( 'rel' );

            }

            // for some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
            if ( typeof attr !== 'undefined' && attr !== false ) {
                var match = attr.match( new RegExp( 'lightbox' + '\\-(gallery\\-(?:[\\da-z]{1,4}))', 'ig' ) );

                if ( match !== null ){
                    jQuery( this ).attr( 'data-lightbox-gallery', match[0] );
                }
            }
        } );
    }

    // Swipe
    // jQuery(function(){
        var addSwipeTo = function(selector) {
            jQuery(selector).swipe("destroy");
            jQuery(selector).swipe({
                swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                    if (direction == "left") {
                        jQuery(".nivo-lightbox-next").trigger("click");
                    }
                    if (direction == "right") {
                        jQuery(".nivo-lightbox-prev").trigger("click");
                    }
                }
            });
        };

        jQuery(document).on('click', ".lightbox, a[rel^=\"attachment\"], [data-rel=\"lightbox\"], .gallery a", function(){
            addSwipeTo(".nivo-lightbox-overlay");
        });
    // });

});

/**
 * TOP News - slider 📌📌📌
 */


/**
 * TOP News - slider OLD
 */
jQuery(document).ready(function ($) {
    if (jQuery('article.rama_main_slider_and_top_news').length === 0){
        // console.log('Это не главная страница, выходим');
        return false;
    }

    /**
     * 1️⃣ To Left
     **/
    function moveLeft($this_dom_selector, $option) {

        $this_dom_selector.closest('.rama_top_news_slider').find('ul').addClass('parrent_slider');

        var slideCount =  $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li').length;
        var slideWidth =  $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li').width();
        var slideHeight =  $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li').height();

        var sliderUlWidth = slideCount * slideWidth;
        //$('.rama_top_news_slider').css({ width: slideWidth, height: slideHeight });
        //$('.rama_top_news_slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });

        $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li:last-child').prependTo('.rama_top_news_slider ul.parrent_slider');

        $this_dom_selector.closest('.rama_top_news_slider ul.parrent_slider').animate({
            // left: + slideWidth
            opacity:0
        }, 300, function () {
            $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li:last-child').prependTo('.rama_top_news_slider ul.parrent_slider');
            // $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider').css('left', '').removeClass('parrent_slider');
            $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider').css('opacity', '1').removeClass('parrent_slider');

//             $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider').removeClass('parrent_slider');
        });
    };


    /**
     * 2️⃣ To Right
     **/
    function moveRight($this_dom_selector,$option) {
        /**
         * Отменяем клик при наведении на кнопки переключения слайдов
         */
        // if ($option === 'autoplay' && jQuery('.bs-slider-current div.rama_top_news_slider a.control_next:hover').length === 1){
        if ($option === 'autoplay' && jQuery('div.rama_top_news_slider a.control_next:hover').length === 1){
            // console.log('Курсор на кнопке навигации');
            return false;
        }
        // if ($option === 'autoplay' && jQuery('.bs-slider-current div.rama_top_news_slider a.control_prev:hover').length === 1){
        if ($option === 'autoplay' && jQuery('div.rama_top_news_slider a.control_prev:hover').length === 1){
            // console.log('Курсор на кнопке навигации');
            return false;
        }

        $this_dom_selector.closest('.rama_top_news_slider').find('ul').addClass('parrent_slider');

        var slideCount =  $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li').length;
        var slideWidth =  $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li').width();
        var slideHeight =  $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li').height();
        var sliderUlWidth = slideCount * slideWidth;

        $('.rama_top_news_slider ul.parrent_slider').animate({
            // left: - slideWidth
            opacity: 0
        }, 300, function () {

            $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider li:first-child').appendTo('.rama_top_news_slider ul.parrent_slider');
            $this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider').css('opacity', '1').removeClass('parrent_slider');

            //$this_dom_selector.closest('.rama_top_news_slider').find('ul.parrent_slider').removeClass('parrent_slider');
        });
    };



    // jQuery('body').on('click','.bs-slider-current article.rama_main_slider_and_top_news a.control_prev',function (event) {
    jQuery('body').on('click','article.rama_main_slider_and_top_news a.control_prev',function (event) {
        event.preventDefault();
        moveLeft(jQuery(this),'click_user');
    });

    // jQuery('body').on('click','.bs-slider-current article.rama_main_slider_and_top_news a.control_next',function (event) {
    jQuery('body').on('click','article.rama_main_slider_and_top_news a.control_next',function (event) {
        event.preventDefault();
        moveRight(jQuery(this),'click_user');
    });

    /*
    * Авто слайд
    */
    setInterval(function () {
        // moveRight(jQuery('.bs-slider-current article.rama_main_slider_and_top_news a.control_next'),'autoplay');
        moveRight(jQuery('article.rama_main_slider_and_top_news a.control_next'),'autoplay');
    }, 4000);

});



/*
Publisher Sticky Banners FIX JS
START
*/

jQuery( document ).ready(function( $ ){

	
	
// Sticky Plugin v1.0.4 for jQuery
// =============
// Author: Anthony Garand
// Improvements by German M. Bravo (Kronuz) and Ruud Kamphuis (ruudk)
// Improvements by Leonardo C. Daronco (daronco)
// Created: 02/14/2011
// Date: 07/20/2015
// Website: http://stickyjs.com/
// Description: Makes an element on the page stick on the screen as you scroll
//              It will only set the 'top' and 'position' of your element, you
//              might need to adjust the width in some cases.

    (function (factory) {
        if (typeof define === 'function' && define.amd) {
            // AMD. Register as an anonymous module.
            define(['jquery'], factory);
        } else if (typeof module === 'object' && module.exports) {
            // Node/CommonJS
            module.exports = factory(require('jquery'));
        } else {
            // Browser globals
            factory(jQuery);
        }
    }(function ($) {
        var slice = Array.prototype.slice; // save ref to original slice()
        var splice = Array.prototype.splice; // save ref to original slice()

        var defaults = {
                topSpacing: 0,
                bottomSpacing: 0,
                className: 'is-sticky',
                wrapperClassName: 'sticky-wrapper',
                center: false,
                getWidthFrom: '',
                widthFromWrapper: true, // works only when .getWidthFrom is empty
                responsiveWidth: false,
                zIndex: 'inherit'
            },
            $window = $(window),
            $document = $(document),
            sticked = [],
            windowHeight = $window.height(),
            scroller = function() {
                var scrollTop = $window.scrollTop(),
                    documentHeight = $document.height(),
                    dwh = documentHeight - windowHeight,
                    extra = (scrollTop > dwh) ? dwh - scrollTop : 0;

                for (var i = 0, l = sticked.length; i < l; i++) {
                    var s = sticked[i],
                        elementTop = s.stickyWrapper.offset().top,
                        etse = elementTop - s.topSpacing - extra;

                    //update height in case of dynamic content
                    s.stickyWrapper.css('height', s.stickyElement.outerHeight());

                    if (scrollTop <= etse) {
                        if (s.currentTop !== null) {
                            s.stickyElement
                                .css({
                                    'width': '',
                                    'position': '',
                                    'top': '',
                                    'z-index': ''
                                });
                            s.stickyElement.parent().removeClass(s.className);
                            s.stickyElement.trigger('sticky-end', [s]);
                            s.currentTop = null;
                        }
                    }
                    else {
                        var newTop = documentHeight - s.stickyElement.outerHeight()
                            - s.topSpacing - s.bottomSpacing - scrollTop - extra;
                        if (newTop < 0) {
                            newTop = newTop + s.topSpacing;
                        } else {
                            newTop = s.topSpacing;
                        }
                        if (s.currentTop !== newTop) {
                            var newWidth;
                            if (s.getWidthFrom) {
                                padding =  s.stickyElement.innerWidth() - s.stickyElement.width();
                                newWidth = $(s.getWidthFrom).width() - padding || null;
                            } else if (s.widthFromWrapper) {
                                newWidth = s.stickyWrapper.width();
                            }
                            if (newWidth == null) {
                                newWidth = s.stickyElement.width();
                            }
                            s.stickyElement
                                .css('width', newWidth)
                                .css('position', 'fixed')
                                .css('top', newTop)
                                .css('z-index', s.zIndex);

                            s.stickyElement.parent().addClass(s.className);

                            if (s.currentTop === null) {
                                s.stickyElement.trigger('sticky-start', [s]);
                            } else {
                                // sticky is started but it have to be repositioned
                                s.stickyElement.trigger('sticky-update', [s]);
                            }

                            if (s.currentTop === s.topSpacing && s.currentTop > newTop || s.currentTop === null && newTop < s.topSpacing) {
                                // just reached bottom || just started to stick but bottom is already reached
                                s.stickyElement.trigger('sticky-bottom-reached', [s]);
                            } else if(s.currentTop !== null && newTop === s.topSpacing && s.currentTop < newTop) {
                                // sticky is started && sticked at topSpacing && overflowing from top just finished
                                s.stickyElement.trigger('sticky-bottom-unreached', [s]);
                            }

                            s.currentTop = newTop;
                        }

                        // Check if sticky has reached end of container and stop sticking
                        var stickyWrapperContainer = s.stickyWrapper.parent();
                        var unstick = (s.stickyElement.offset().top + s.stickyElement.outerHeight() >= stickyWrapperContainer.offset().top + stickyWrapperContainer.outerHeight()) && (s.stickyElement.offset().top <= s.topSpacing);

                        if( unstick ) {
                            s.stickyElement
                                .css('position', 'absolute')
                                .css('top', '')
                                .css('bottom', 0)
                                .css('z-index', '');
                        } else {
                            s.stickyElement
                                .css('position', 'fixed')
                                .css('top', newTop)
                                .css('bottom', '')
                                .css('z-index', s.zIndex);
                        }
                    }
                }
            },
            resizer = function() {
                windowHeight = $window.height();

                for (var i = 0, l = sticked.length; i < l; i++) {
                    var s = sticked[i];
                    var newWidth = null;
                    if (s.getWidthFrom) {
                        if (s.responsiveWidth) {
                            newWidth = $(s.getWidthFrom).width();
                        }
                    } else if(s.widthFromWrapper) {
                        newWidth = s.stickyWrapper.width();
                    }
                    if (newWidth != null) {
                        s.stickyElement.css('width', newWidth);
                    }
                }
            },
            methods = {
                init: function(options) {
                    return this.each(function() {
                        var o = $.extend({}, defaults, options);
                        var stickyElement = $(this);

                        var stickyId = stickyElement.attr('id');
                        var wrapperId = stickyId ? stickyId + '-' + defaults.wrapperClassName : defaults.wrapperClassName;
                        var wrapper = $('<div></div>')
                            .attr('id', wrapperId)
                            .addClass(o.wrapperClassName);

                        stickyElement.wrapAll(function() {
                            if ($(this).parent("#" + wrapperId).length == 0) {
                                return wrapper;
                            }
                        });

                        var stickyWrapper = stickyElement.parent();

                        if (o.center) {
                            stickyWrapper.css({width:stickyElement.outerWidth(),marginLeft:"auto",marginRight:"auto"});
                        }

                        if (stickyElement.css("float") === "right") {
                            stickyElement.css({"float":"none"}).parent().css({"float":"right"});
                        }

                        o.stickyElement = stickyElement;
                        o.stickyWrapper = stickyWrapper;
                        o.currentTop    = null;

                        sticked.push(o);

                        methods.setWrapperHeight(this);
                        methods.setupChangeListeners(this);
                    });
                },

                setWrapperHeight: function(stickyElement) {
                    var element = $(stickyElement);
                    var stickyWrapper = element.parent();
                    if (stickyWrapper) {
                        stickyWrapper.css('height', element.outerHeight());
                    }
                },

                setupChangeListeners: function(stickyElement) {
                    if (window.MutationObserver) {
                        var mutationObserver = new window.MutationObserver(function(mutations) {
                            if (mutations[0].addedNodes.length || mutations[0].removedNodes.length) {
                                methods.setWrapperHeight(stickyElement);
                            }
                        });
                        mutationObserver.observe(stickyElement, {subtree: true, childList: true});
                    } else {
                        if (window.addEventListener) {
                            stickyElement.addEventListener('DOMNodeInserted', function() {
                                methods.setWrapperHeight(stickyElement);
                            }, false);
                            stickyElement.addEventListener('DOMNodeRemoved', function() {
                                methods.setWrapperHeight(stickyElement);
                            }, false);
                        } else if (window.attachEvent) {
                            stickyElement.attachEvent('onDOMNodeInserted', function() {
                                methods.setWrapperHeight(stickyElement);
                            });
                            stickyElement.attachEvent('onDOMNodeRemoved', function() {
                                methods.setWrapperHeight(stickyElement);
                            });
                        }
                    }
                },
                update: scroller,
                unstick: function(options) {
                    return this.each(function() {
                        var that = this;
                        var unstickyElement = $(that);

                        var removeIdx = -1;
                        var i = sticked.length;
                        while (i-- > 0) {
                            if (sticked[i].stickyElement.get(0) === that) {
                                splice.call(sticked,i,1);
                                removeIdx = i;
                            }
                        }
                        if(removeIdx !== -1) {
                            unstickyElement.unwrap();
                            unstickyElement
                                .css({
                                    'width': '',
                                    'position': '',
                                    'top': '',
                                    'float': '',
                                    'z-index': ''
                                })
                            ;
                        }
                    });
                }
            };

        // should be more efficient than using $window.scroll(scroller) and $window.resize(resizer):
        if (window.addEventListener) {
            window.addEventListener('scroll', scroller, false);
            window.addEventListener('resize', resizer, false);
        } else if (window.attachEvent) {
            window.attachEvent('onscroll', scroller);
            window.attachEvent('onresize', resizer);
        }

        $.fn.sticky = function(method) {
            if (methods[method]) {
                return methods[method].apply(this, slice.call(arguments, 1));
            } else if (typeof method === 'object' || !method ) {
                return methods.init.apply( this, arguments );
            } else {
                $.error('Method ' + method + ' does not exist on jQuery.sticky');
            }
        };

        $.fn.unstick = function(method) {
            if (methods[method]) {
                return methods[method].apply(this, slice.call(arguments, 1));
            } else if (typeof method === 'object' || !method ) {
                return methods.unstick.apply( this, arguments );
            } else {
                $.error('Method ' + method + ' does not exist on jQuery.sticky');
            }
        };
        $(function() {
            setTimeout(scroller, 0);
        });
    }));


    /*
    🔥🔥🔥🔥🔥🔥🔥
    https://github.com/garand/sticky
    */
    if ($(window).width() >= 1435) {

        let FooterHeight = jQuery("footer").height();

        // Right Banner
        $('.bs-sksitem.bs-sksitemr').sticky({
            //     topSpacing:-600,
            wrapperClassName:'Sticky_RightBanner',
            bottomSpacing:FooterHeight,
        });

        // Left Banner
        $('.bs-sksitem.bs-sksiteml').sticky({
            //     topSpacing:0,
            wrapperClassName:'Sticky_LeftBanner',
            bottomSpacing:FooterHeight,
        });
    }



});

/*
Publisher Sticky Banners FIX JS
END
*/





/**
 * History posts slider
 */

// Original code:
// https://codepen.io/viankakrisna/pen/JogZKO
function sds_slideshow_posts_carusel_slides($element){
    // console.log($element);
    $element = document.body.querySelector($element);

    var slide = 0,
        slides = $element.querySelectorAll('li'),
        numSlides = slides.length,

        //Functions!!
        currentSlide = function() {
            var itemToShow = Math.abs(slide % numSlides);
            [].forEach.call(slides, function(el) {
                el.classList.remove('slideActive')
            });
            slides[itemToShow].classList.add('slideActive');
            resetProgress();
            resetInterval();
        },

        next = function() {
            slide++;
            currentSlide();
        },

        prev = function() {
            slide--;
            currentSlide();
        },

        resetProgress = function() {
            var elm = $element.closest('.sds_slideshow_posts_carusel').querySelector('.progressbar'),
                newone = elm.cloneNode(true),
                x = elm.parentNode.replaceChild(newone, elm);
        },

        resetslide = function() {
            var elm = $element.querySelector('li'),
                newone = elm.cloneNode(true),
                x = elm.parentNode.replaceChild(newone, elm);
        },

        resetInterval = function() {
            clearInterval(autonext);
            autonext = setInterval(function() {
                slide++;
                currentSlide();
            }, 10000);
        },

        autonext = setInterval(function() {
            next();
        }, 10000);

// Navigation
    $element.querySelector('#next').addEventListener('click', function() {
        next();
    }, false);
    $element.querySelector('#previous').addEventListener('click', function() {
        prev();
    }, false);

// console.log('▶️');

// //Buttons
// document.querySelector('#first').addEventListener('click', function() {
//     slide = 0;
//     currentSlide();
// }, false);
// document.querySelector('#second').addEventListener('click', function() {
//     slide = 1;
//     currentSlide();
// }, false);
// document.querySelector('#third').addEventListener('click', function() {
//     slide = 2;
//     currentSlide();
// }, false);
// document.querySelector('#fourth').addEventListener('click', function() {
//     slide = 3;
//     currentSlide();
// }, false);
// document.querySelector('#fifth').addEventListener('click', function() {
//     slide = 4;
//     currentSlide();
// }, false);
// document.querySelector('#sixth').addEventListener('click', function() {
//     slide = 5;
//     currentSlide();
// }, false);
}


/**
 Функция получения селектораэлемента
 **/
function fullPath(el){
    var names = [];
    while (el.parentNode){
        if (el.id){
            names.unshift('#'+el.id);
            break;
        }else{
            if (el==el.ownerDocument.documentElement) names.unshift(el.tagName);
            else{
                for (var c=1,e=el;e.previousElementSibling;e=e.previousElementSibling,c++);
                names.unshift(el.tagName+":nth-child("+c+")");
            }
            el=el.parentNode;
        }
    }
    return names.join(" > ");
}

let sds_carusels = document.querySelectorAll('.sds_slideshow_posts_carusel_slides');
if (jQuery('.sds_slideshow_posts_carusel_slides #next').length > 0) {
    sds_carusels.forEach(el => {
        sds_slideshow_posts_carusel_slides(fullPath(el));
    });
}


/**
 * Клик для ссылок с иконками на ПК
 */
if (jQuery(window).width() >= 1000) {
    jQuery('.main-menu.menu>li.rama-photo-icon, .main-menu.menu>li.rama-video-icon, .main-menu.menu>li.rama-podcast-icon').click(function(e){
        if(e.target == this) {
            let Link = jQuery(this).children(":first").attr('href');
            window.location = Link;
        }
    });
}


jQuery( document ).ready(function( $ ){
	
	jQuery('body').on('click','.weather-tabs .weather-day h3',function() {
        // Переключення відображення вмісту при кліку на заголовок
        var content = jQuery(this).next(); // це <div> після <h3>
        content.toggle(); // Переключення видимості елемента
    });
	
});
