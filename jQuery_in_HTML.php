<?php
//dd('wedfw');
/**
 * Полностью удаляем
 * jQuery из WordPress
 */
//function my_init() {
//    if (!is_admin()) {
//        wp_deregister_script('jquery');
//        wp_register_script('jquery', false);
//        wp_dequeue_script( 'jquery' );
//    }
//}
//add_action('init', 'my_init');
if (!is_admin()) {
    add_filter('wp_enqueue_scripts', 'change_default_jquery', PHP_INT_MAX);
}

function change_default_jquery( ){
    wp_dequeue_script( 'jquery');
    wp_deregister_script( 'jquery');
}

if (!is_admin()) {
    add_action('wp_head', 'check_jquery');
    add_action('admin_head', 'check_jquery');
}
function check_jquery() {

    global $wp_scripts;

    foreach ( $wp_scripts->registered as $wp_script ) {
        $handles[] = $wp_script->handle;
    }

//    /**
//     * Проверка отключения jQuery
//     */
//    if( in_array( 'jquery', $handles ) ) {
//        echo 'jquery has been loaded';
//    }else{
//        echo 'jquery has been removed';
//    }
}



/**
 * Встраиваем jQuery в тело страницы
 */
if (!is_admin()) {
    add_action('wp_head', 'print_jQuery', 1);
}
function print_jQuery(){
    // jQuery
    $jq = file_get_contents(ABSPATH . WPINC . '/js/jquery/jquery.min.js');
    // jQuery Migrate
    $mj =  "\n".'/* SDStudio Inline jQuery Migrate */' . "\n";
    $mj .= file_get_contents(ABSPATH . WPINC . '/js/jquery/jquery-migrate.min.js');

    /**
     * responsive-lightbox Слайдер
     * Проверка активности плагина
     */
    // Список всех активных плагинов
//    $pluginList = get_option( 'active_plugins' );
//    $plugin = "responsive-lightbox/responsive-lightbox.php";
    $rl = '';
    // Если плагин активен, добавим его JS скрипт + ручные настройки в inline
//    if ( in_array( $plugin , $pluginList ) ) {
        $rl =  "\n".'/* SDStudio Inline nivo-lightbox */' . "\n";
        // Получаем содержимое скрипта плагина
//        $rl .= file_get_contents(WP_PLUGIN_DIR.'/responsive-lightbox/assets/nivo/nivo-lightbox.min.js');
        $rl .= file_get_contents(get_stylesheet_directory_uri(). '/nivo-lightbox/nivo-lightbox.min.js');
//    $rl .= file_get_contents(WP_PLUGIN_DIR.'/responsive-lightbox/assets/infinitescroll/infinite-scroll.pkgd.min.js');
    $rl .= file_get_contents(get_stylesheet_directory_uri(). '/nivo-lightbox/infinite-scroll.pkgd.min.js');
//        $rl .= file_get_contents(WP_PLUGIN_DIR.'/responsive-lightbox/js/front.js');
        // Добавляем свои настройки
        $rl .="

        // Inicialize - Lightbox для галерей та зображень (працює на всіх сторінках)
        jQuery(document).ready(function($){
                    jQuery('a[rel=\"lightbox\"], a[rel^=\"attachment\"], a[data-rel^=\"attachment-\"], [data-rel^=\"lightbox\"], .nivo-lightbox-content, .gallery a[href*=\".jpg\"], .gallery a[href*=\".jpeg\"], .gallery a[href*=\".png\"], .gallery a[href*=\".gif\"], .gallery a[href*=\".webp\"], body.single-announce .single-featured > a, body.single-kalendar_istor_podiy .single-featured > a, body.single-project .single-featured > a').nivoLightbox({

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
                
            ";
//    }


    // Underscore
    $un =  "\n".'/* SDStudio Inline jQuery Underscore */' . "\n";
    $un .= file_get_contents(ABSPATH . WPINC . '/js/underscore.min.js');
    //<script type='text/javascript' src='https://rama.com.ua/wp-includes/js/underscore.min.js?ver=1.13.1' id='underscore-js'></script>


    $jq = $jq.$mj.$rl.$un;

//    dd($jq);


    // IF WPML ENABLE
    global $sitepress;
    if ($sitepress) {
        $wpml_jq_cokie = wp_remote_retrieve_body(wp_remote_get(plugins_url() . '/sitepress-multilingual-cms/res/js/jquery.cookie.js'));
    }

    if (!is_admin()) {
        echo '<script>';
        echo '/* SDStudio Inline jQuery */' . "\n";
        echo $jq;
        echo '$ = jQuery;';
        // IF WPML ENABLE
        if ($sitepress) {
            echo $wpml_jq_cokie;
        }
        echo '</script>';
    }
}