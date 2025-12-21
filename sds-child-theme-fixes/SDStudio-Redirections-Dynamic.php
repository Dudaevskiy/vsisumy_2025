<?php
/**
 *
 * __SDStudio-Redirections-Dynamic
 * Тут знаходяться редиректи у тому ж числі для orig_post постів
 * + Уся основна робота з 404 помилками у Google Search Console
 * require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/SDStudio-Redirections-Dynamic.php');
 */


/***
 * Якщо ні чого не увімкнено відобразимо редіректи
 * Для зручності роботи експортуємо 404 лінки з помилками з Rank Match, далі опрацьовуємо у таблиці
 * Google Sheet формула:
 * ="'/"&B2&"/' => '"&C2&"',"
 */
/***
 * FOR ALL
 */
//// Масив редіректів
//global $redirects;
//$redirects = [
//    '/uk/component/sppagebuilder/?view=page&id=209' => 'https://dev2.biem.sumdu.edu.ua/uk/monography_homenko',
////        '/orig_post/tury-v-chehiju-chto-posmotret/' => 'https://tourism.com.de/touren-nach-tschechien-was-zu-sehen/',
////            '/old-page-2' => '/new-page-2',
////            '/old-category/post' => '/new-category/post',
////            '/legacy-product' => 'https://newsite.com/product',
//    // Додавайте нові редіректи сюди
//];
//
//
//
//
//function custom_early_redirects() {
//    global $redirects;
//
//    // Отримуємо поточний URL
//    $current_url = $_SERVER['REQUEST_URI'];
//
//    // Видаляємо query string, якщо вона є
////        $current_path = strtok($current_url, '?');
//
////        // Логування для відлагодження
////        if (defined('WP_DEBUG') && WP_DEBUG) {
////            error_log('Current Path: ' . $current_path);
////        }
//
//    foreach ($redirects as $old_url => $new_url) {
//        // Перевіряємо, чи поточний шлях відповідає шаблону редіректу
//        if (rtrim($current_url, '/') === rtrim($old_url, '/')) {
//            if (defined('WP_DEBUG') && WP_DEBUG) {
//                error_log('Redirect matched: ' . $old_url . ' -> ' . $new_url);
//            }
//
//            // Виконуємо редірект
//            header("Location: " . $new_url, true, 301);
//            exit;
//        }
//    }
//}
//
//// Використовуємо ранній хук з високим пріоритетом
//add_action('init', 'custom_early_redirects', 1);
//
//// Додатково можемо використовувати фільтр для URL
//add_filter('template_redirect', 'custom_template_redirect', 1);
//
//function custom_template_redirect() {
//    global $redirects;
//
//    $current_url = $_SERVER['REQUEST_URI'];
//    $current_path = strtok($current_url, '?');
//
//    foreach ($redirects as $old_url => $new_url) {
//        if (rtrim($current_path, '/') === rtrim($old_url, '/')) {
//            wp_redirect($new_url, 301);
//            exit;
//        }
//    }
//}
/***
 * END
 */

//// Не виконуємо на сайті якщо увійшов адміністратор або редактор
include_once(ABSPATH . 'wp-includes/pluggable.php');
if ( current_user_can( 'administrator' ) && current_user_can( 'editor' )) {
    return false;
}

global $redux;
$redux = get_option('redux_sds_editor_tools');
$google_translator_enable_orig_post_sdstudio_markdown_clipper_addons_sds_editor_tools = isset($redux['google_translator_enable_orig_post_sdstudio_markdown_clipper_addons_sds-editor-tools']) ? $redux['google_translator_enable_orig_post_sdstudio_markdown_clipper_addons_sds-editor-tools'] : null;
$enable_orig_post_publih_for_all_sdstudio_markdown_clipper_addons_sds_editor_tools = isset($redux['enable_orig_post_publih_for_all_sdstudio_markdown_clipper_addons_sds-editor-tools']) ? $redux['enable_orig_post_publih_for_all_sdstudio_markdown_clipper_addons_sds-editor-tools'] : null;
$is_google_translate = null;

/***
 * Тут вказуємо тільки orig_post
 */

if (
    isset($google_translator_enable_orig_post_sdstudio_markdown_clipper_addons_sds_editor_tools) && isset($enable_orig_post_publih_for_all_sdstudio_markdown_clipper_addons_sds_editor_tools)
) {
    /***
     * Якщо увімкнена будь яка опція по парсінгу далі ні чого не виконуємо
     */
    if (
        !empty($google_translator_enable_orig_post_sdstudio_markdown_clipper_addons_sds_editor_tools) ||
        !empty($enable_orig_post_publih_for_all_sdstudio_markdown_clipper_addons_sds_editor_tools)
    ) {
        // Умови
        return false;
    }

    /***
     * Якщо ні чого не увімкнено відобразимо редіректи
     * Для зручності роботи експортуємо 404 лінки з помилками з Rank Match, далі опрацьовуємо у таблиці
     * Google Sheet формула:
     * ="'/"&B2&"/' => '"&C2&"',"
     */

    // Масив редіректів
    global $redirects;
    $redirects = [
        '/orig_post/tury-v-chehiju-chto-posmotret/' => 'https://tourism.com.de/touren-nach-tschechien-was-zu-sehen/',
        '/orig_post/chto-posmotret-v-gamburge-za-1-den-samostojatelno-marshrut-foto-opisanie-karta/' => 'https://tourism.com.de/was-sie-in-hamburg-an-einem-tag-auf-eigene-faust-sehen-koennen-route-foto-beschreibung/',
        '/orig_post/dostoprimechatelnosti-pekina-25-samyh-interesnyh-mest/' => 'https://tourism.com.de/sightseeing-in-peking-25-interessanteste-orte/',
        '/orig_post/30-luchshih-muzeev-kazani-spisok-foto-opisanie-ceny-2021-karta/' => 'https://tourism.com.de/die-30-besten-museen-in-kasan-liste-foto-beschreibung-preise-2021-karte/',
        '/orig_post/kogda-zhenu-luchshe-ostavit-doma-vse-o-seks-turizme-v-tailande/' => 'https://tourism.com.de/sextourismus-in-thailand-alles-was-sie-wissen-wollten',
        '/orig_post/13-samyh-neobychnyh-otelej-v-mire-foto-opisanie/' => 'https://tourism.com.de/13-ungewoehnlichste-hotels-der-welt-foto-beschreibung/',
        '/orig_post/chto-posmotret-v-stambule-za-2-dnja-samostojatelno-marshrut-foto-opisanie-karta/' => 'https://tourism.com.de/was-sie-in-istanbul-in-2-tagen-auf-eigene-faust-sehen-koennen-route-foto-beschreibung-karte/',
        '/orig_post/chto-proishodit-na-rozhdestvenskih-jarmarkah/' => 'https://tourism.com.de/weihnachtsmaerkte-in-europa-ein-leitfaden-zu-den-besten-maerkten',
        '/orig_post/jekoturizm-chto-jeto-takoe-i-gde-iskat/' => 'https://tourism.com.de/oekotourismus-ein-leitfaden-fuer-verantwortungsvolles-reisen',
        '/orig_post/chto-posmotret-v-stambule-za-4-dnja-samostojatelno-marshrut-opisanie-foto-karta/' => 'https://tourism.com.de/was-sie-in-istanbul-in-4-tagen-auf-eigene-faust-sehen-koennen-route-beschreibung-foto-karte/',
        '/orig_post/kuda-poehat-v-marte/' => 'https://tourism.com.de/top-10-reiseziele-im-maerz/',
        '/orig_post/gajd-po-budapeshtu-chto-posmotret-i-top-dostoprimechatelnostej-peshta/' => 'https://tourism.com.de/pest-top-15-sehenswuerdigkeiten-im-osten-budapests',
        '/orig_post/15-luchshih-dostoprimechatelnostej-korfu-foto-opisanie/' => 'https://tourism.com.de/15-besten-sehenswuerdigkeiten-von-korfu-foto-beschreibung/',
        '/orig_post/gajd-po-kritu-kak-provesti-otpusk-na-rodine-zevsa/' => 'https://tourism.com.de/kreta-ein-vollstaendiger-reisefuehrer-zur-insel-zeus',
        '/orig_post/kuda-sezdit-iz-veny-na-1-den-samostojatelno-marshrut-foto-opisanie-karta/' => 'https://tourism.com.de/wohin-sie-von-wien-aus-fuer-1-tag-auf-eigene-faust-gehen-koennen-route-foto-beschreibung-karte/',
        '/orig_post/15-luchshih-kapsulnyh-otelej-moskvy/' => 'https://tourism.com.de/die-15-besten-kapselhotels-in-moskau/',
        '/orig_post/chto-posmotret-v-tbilisi-za-3-dnja-samostojatelno-marshrut-foto-opisanie-karta/' => 'https://tourism.com.de/was-sie-in-tiflis-in-3-tagen-auf-eigene-faust-sehen-koennen-route-foto-beschreibung-karte/',
        '/orig_post/chto-posmotret-po-doroge-iz-lissabona-v-portu/' => 'https://tourism.com.de/lissabon-porto-10-orte-die-man-unterwegs-unbedingt-besuchen-muss/',
        '/orig_post/8-samyh-neobychnyh-pamjatnikov-mira/' => 'https://tourism.com.de/die-8-ungewoehnlichsten-denkmaeler-der-welt',
        '/orig_post/kak-dobratsja-i-chto-posmotret-v-kasablanke/' => 'https://tourism.com.de/casablanca-fuehrer-zur-perle-marokkos/',
        '/orig_post/19-luchshih-kurortov-italii-na-more-kakoj-vybrat-dlja-otdyha-foto-opisanie-ceny-2021-karta/' => 'https://tourism.com.de/19-beste-badeorte-in-italien-welchen-sie-fuer-ihren-urlaub-waehlen-sollten-foto-beschreibung-preise-2021-karte/',
        '/orig_post/gajd-po-dalatu-chem-zanjatsja-i-chto-posmotret/' => 'https://tourism.com.de/dalat-die-perle-der-vietnamesischen-berge',
        '/orig_post/chto-posmotret-v-mjunhene-neochevidnyj-spisok/' => 'https://tourism.com.de/muenchen-jenseits-des-oktoberfests-15-nicht-offensichtliche-orte',
        '/orig_post/mariinskij-dvorec-v-sankt-peterburge-foto-i-opisanie-interesnye-fakty-karta-kak-dobratsja-samostojatelno/' => 'https://tourism.com.de/mariinsky-palast-in-st-petersburg-foto-und-beschreibung-interessante-fakten-karte-anfahrt-auf-eigene-faust/',
        '/orig_post/chto-posmotret-v-vologde-za-1-den-samostojatelno-marshrut-foto-opisanie-karta/' => 'https://tourism.com.de/was-sie-in-wologda-an-einem-tag-auf-eigene-faust-sehen-koennen-route-foto-beschreibung-karte/',
        '/orig_post/15-luchshih-otelej-horvatii-s-akvaparkom-i-vodnymi-gorkami/' => 'https://tourism.com.de/die-15-besten-hotels-in-kroatien-mit-wasserpark-und-wasserrutschen/',
        '/old-page-1' => '/new-page-1',
//            '/old-page-2' => '/new-page-2',
//            '/old-category/post' => '/new-category/post',
//            '/legacy-product' => 'https://newsite.com/product',
        // Додавайте нові редіректи сюди
    ];




    function custom_early_redirects() {
        global $redirects;

        // Отримуємо поточний URL
        $current_url = $_SERVER['REQUEST_URI'];

        // Видаляємо query string, якщо вона є
        $current_path = strtok($current_url, '?');

        // Логування для відлагодження
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Current Path: ' . $current_path);
        }

        foreach ($redirects as $old_url => $new_url) {
            // Перевіряємо, чи поточний шлях відповідає шаблону редіректу
            if (rtrim($current_path, '/') === rtrim($old_url, '/')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Redirect matched: ' . $old_url . ' -> ' . $new_url);
                }

                // Виконуємо редірект
                header("Location: " . $new_url, true, 301);
                exit;
            }
        }
    }

// Використовуємо ранній хук з високим пріоритетом
    add_action('init', 'custom_early_redirects', 1);

// Додатково можемо використовувати фільтр для URL
    add_filter('template_redirect', 'custom_template_redirect', 1);

    function custom_template_redirect() {
        global $redirects;

        $current_url = $_SERVER['REQUEST_URI'];
        $current_path = strtok($current_url, '?');

        foreach ($redirects as $old_url => $new_url) {
            if (rtrim($current_path, '/') === rtrim($old_url, '/')) {
                wp_redirect($new_url, 301);
                exit;
            }
        }
    }

}