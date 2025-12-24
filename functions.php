<?php

//require_once get_stylesheet_directory( ) . '/jQuery_in_HTML.php';
require_once get_stylesheet_directory( ) . '/content-project.php';

/***
 * SDStudio-WP_All_Import-Fixes.php
 */
//require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/SDStudio-WP_All_Import-Fixes.php');





add_filter('wp_all_import_is_enabled_stream_filter', 'wpai_wp_all_import_is_enabled_stream_filter', 10, 1);
function wpai_wp_all_import_is_enabled_stream_filter($enable_strem_filter) {
    return FALSE;
}

add_filter('wp_all_import_csv_to_xml_remove_non_ascii_characters', 'wpai_wp_all_import_csv_to_xml_remove_non_ascii_characters', 10, 1);
function wpai_wp_all_import_csv_to_xml_remove_non_ascii_characters($remove_non_ascii_characters) {
    return FALSE;
}

function allow_txt_uploads_for_admin($mimes) {
    // Перевіряємо права користувача
    if (current_user_can('manage_options') || current_user_can('administrator')) {
        $mimes['txt'] = 'text/plain';
    }
    return $mimes;
}
add_filter('upload_mimes', 'allow_txt_uploads_for_admin');

/**
 * Збільшити memory limit та CSV field size limit для WP All Import
 * Деякі новини мають body > 400KB, що перевищує стандартний ліміт 128KB
 */
add_action('init', 'vsesumy_increase_csv_limits', 1);
function vsesumy_increase_csv_limits() {
    // Збільшити memory limit до 512MB
    @ini_set('memory_limit', '512M');

    // Включити auto detect line endings для кращої сумісності CSV
    @ini_set('auto_detect_line_endings', '1');

    // Збільшити max execution time до 10 хвилин
    @ini_set('max_execution_time', '600');

    // Збільшити post_max_size та upload_max_filesize для великих CSV
    @ini_set('post_max_size', '256M');
    @ini_set('upload_max_filesize', '256M');
}

/**
 * Хук для WP All Import перед парсингом CSV - збільшуємо field size limit
 */
add_action('pmxi_before_xml_import', 'vsesumy_increase_csv_field_size_before_import', 1);
function vsesumy_increase_csv_field_size_before_import($import_id) {
    // PHP CSV parser за замовчуванням має ліміт 128KB на поле
    // Збільшуємо до 10MB для підтримки великих body полів
    @ini_set('auto_detect_line_endings', '1');

    // Ця функція працює на рівні PHP і дозволяє читати великі CSV поля
    if (function_exists('ini_set')) {
        @ini_set('memory_limit', '1024M'); // 1GB під час імпорту
    }
}





/**
 * Відключити WPML cron jobs та API виклики
 */
add_filter('wpml_cron_jobs', '__return_empty_array'); // Вимкнути всі WPML cron задачі
add_filter('wpml_enable_language_meta_box', '__return_false'); // Спростити мета-бокс мови
add_filter('wpml_enable_translate_link', '__return_false'); // Вимкнути кнопку "Translate" в admin bar

function sdstudio_enqueue_styles_PANORAMA(){
//    $parent_style = 'parent-style';
//    wp_enqueue_style('___SDStudio__CSS', get_stylesheet_directory_uri() . '/___SDStudio__CSS.css');
//    wp_enqueue_style('rama_styles', get_stylesheet_directory_uri() . '/style.css');

    wp_enqueue_style('banners', get_stylesheet_directory_uri() . '/banners.css');
    // -----------------------------------------------------------------------
    // ------- CSS START
    // -----------------------------------------------------------------------
    if (is_singular('post') || is_singular('announce') ){
        //FlexSlider
        wp_enqueue_style( 'rama-flexslider', get_stylesheet_directory_uri() .'/flex_slider/flexslider.css');
        wp_enqueue_script( 'rama-flexslider', get_stylesheet_directory_uri(). '/flex_slider/jquery.flexslider.js');
//        wp_enqueue_script( 'rama-touchSwipe', get_stylesheet_directory_uri(). '/jquery.touchSwipe.min.js');

        //Nivo Lightbox
        wp_enqueue_style( 'nivo-lightbox', get_stylesheet_directory_uri() .'/nivo-lightbox/nivo-lightbox.min.css');
        wp_enqueue_style( 'nivo-lightbox_def', get_stylesheet_directory_uri() .'/nivo-lightbox/themes/default/default.css');
        wp_enqueue_script( 'rama-touchSwipe',get_stylesheet_directory_uri(). '/nivo-lightbox/jquery.touchSwipe.min.js');
        wp_register_script( 'nivo-lightbox', get_stylesheet_directory_uri(). '/nivo-lightbox/nivo-lightbox.min.js', array('jquery'), '', true);
        wp_enqueue_script('nivo-lightbox');
    }

    wp_enqueue_script( 'functions.js', get_stylesheet_directory_uri() . '/functions.js','','',true);

//    wp_enqueue_style( '013_PLUGIN_Before After Image_Comparison_Slider_for_Elementor', get_stylesheet_directory_uri() .'/__SDStudio_CSS_INCLUDED_ON_PART/013_PLUGIN_Before After Image_Comparison_Slider_for_Elementor.css' );

    /**
     * Подключение календаря
     */
    // Страница блога выставленная в настройках WP
    $get_blog_page = get_post_type_archive_link( 'post' );
    // ID страницы для блога
    $id_page_for_posts = get_option( 'page_for_posts' );
    // Функция для захвата текущей ссылки страницы
    if (!function_exists('page_current_url')){
        function page_current_url() {
            $current_url  = 'http';
            $server_https = $_SERVER["HTTPS"] ?? '';
            $server_name  = $_SERVER["SERVER_NAME"] ?? '';
            $server_port  = $_SERVER["SERVER_PORT"] ?? 80;
            $request_uri  = $_SERVER["REQUEST_URI"] ?? '';
            if ($server_https == "on") $current_url .= "s";
            $current_url .= "://";
            if ($server_port != "80") $current_url .= $server_name . $request_uri;
            else $current_url .= $server_name . $request_uri;
            return $current_url;
        }
    }
    $sdstudio_current_page = page_current_url();

    $parts_question = explode("?", $sdstudio_current_page);
    if (isset($parts_question[1])){
        $sdstudio_current_page = $parts_question[0];
    }

    $parts_page = explode("page", $sdstudio_current_page);
    if (isset($parts_page[1])){
        $sdstudio_current_page = $parts_page[0];
    }

    if ($get_blog_page == $sdstudio_current_page){
//        s('Да, на странице блога');
        wp_enqueue_style( 'rama-flatpickr_css', get_stylesheet_directory_uri() .'/flatpickr/flatpickr.min.css');
        wp_enqueue_script( 'rama-flatpickr_js', get_stylesheet_directory_uri(). '/flatpickr/flatpickr.js');
    }


    // -----------------------------------------------------------------------
    // ------- CSS END
    // -----------------------------------------------------------------------

}
add_action('wp_enqueue_scripts', 'sdstudio_enqueue_styles_PANORAMA');


/**
 * Admin CSS + JS
 */
add_action('admin_enqueue_scripts', '__admin_css_and_js');
function __admin_css_and_js()
{
    // CSS - ADMIN
    wp_register_style('rama_admin_css', get_stylesheet_directory_uri() . '/style_admin.css');
    wp_enqueue_style('rama_admin_css');
    // JS - ADMIN
//    wp_enqueue_script('admin_js_styles_ssu_domains', get_stylesheet_directory_uri() . '/admin/admin_js_ssu_domains.js');
}

/**
 * Запрещаем доступ к админ панели всем кроме администраторов
 */
function wpse_11244_restrict_admin() {

    if ( wp_doing_ajax() ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Вам заборонено доступ до цієї частини сайту. Доступ до адмін панелі можливий лише адміністраторам сайту!' ) );
    }
}

add_action( 'admin_init', 'wpse_11244_restrict_admin', 1 );




// Add Twenty Eleven's custom image sizes.
//add_image_size( 'small-thumb-author', 30, 30, true);
add_image_size( 'list-thumb', 100, 75, true );
add_image_size( 'announce-thumb', 90, 120, true );
add_image_size( 'gallery-thumb', 110, 82, true );
add_image_size( 'small-feature', 129, 95, true );


/**
 * Обрезка текста и/или замена стандартной функции the_excerpt()
 * https://wp-kama.ru/id_31/obrezka-teksta-zamenyaem-the-excerpt.html
 */
if (!function_exists('kama_excerpt')){
    function kama_excerpt( $args = '' ){
        global $post;

        if( is_string( $args ) ){
            parse_str( $args, $args );
        }

        $rg = (object) array_merge( [
            'maxchar'           => 350,
            'text'              => '',
            'autop'             => true,
            'more_text'         => 'Читать дальше...',
            'ignore_more'       => false,
            'save_tags'         => '<strong><b><a><em><i><var><code><span>',
            'sanitize_callback' => static function( string $text, object $rg ){
                return strip_tags( $text, $rg->save_tags );
            },
        ], $args );

        $rg = apply_filters( 'kama_excerpt_args', $rg );

        if( ! $rg->text ){
            $rg->text = $post->post_excerpt ?: $post->post_content;
        }

        $text = $rg->text;
        // strip content shortcodes: [foo]some data[/foo]. Consider markdown
        $text = preg_replace( '~\[([a-z0-9_-]+)[^\]]*\](?!\().*?\[/\1\]~is', '', $text );
        // strip others shortcodes: [singlepic id=3]. Consider markdown
        $text = preg_replace( '~\[/?[^\]]*\](?!\()~', '', $text );
        // strip direct URLs
        $text = preg_replace( '~(?<=\s)https?://.+\s~', '', $text );
        $text = trim( $text );

        // <!--more-->
        if( ! $rg->ignore_more && strpos( $text, '<!--more-->' ) ){

            preg_match( '/(.*)<!--more-->/s', $text, $mm );

            $text = trim( $mm[1] );

            $text_append = sprintf( ' <a href="%s#more-%d">%s</a>', get_permalink( $post ), $post->ID, $rg->more_text );
        }
        // text, excerpt, content
        else {

            $text = call_user_func( $rg->sanitize_callback, $text, $rg );
            $has_tags = false !== strpos( $text, '<' );

            // collect html tags
            if( $has_tags ){
                $tags_collection = [];
                $nn = 0;

                $text = preg_replace_callback( '/<[^>]+>/', static function( $match ) use ( & $tags_collection, & $nn ){
                    $nn++;
                    $holder = "~$nn";
                    $tags_collection[ $holder ] = $match[0];

                    return $holder;
                }, $text );
            }

            // cut text
            $cuted_text = mb_substr( $text, 0, $rg->maxchar );
            if( $text !== $cuted_text ){

                // del last word, it not complate in 99%
                $text = preg_replace( '/(.*)\s\S*$/s', '\\1...', trim( $cuted_text ) );
            }

            // bring html tags back
            if( $has_tags ){
                $text = strtr( $text, $tags_collection );
                $text = force_balance_tags( $text );
            }
        }

        // add <p> tags. Simple analog of wpautop()
        if( $rg->autop ){

            $text = preg_replace(
                [ "/\r/", "/\n{2,}/", "/\n/" ],
                [ '', '</p><p>', '<br />' ],
                "<p>$text</p>"
            );
        }

        $text = apply_filters( 'kama_excerpt', $text, $rg );

        if( isset( $text_append ) ){
            $text .= $text_append;
        }

        return $text;
    }
}



require_once get_stylesheet_directory( ) . '/posts_types.php';
require_once get_stylesheet_directory( ) . '/posts_taxonomies.php';
require_once get_stylesheet_directory( ) . '/cache_query.php';
/**
 * Custom Shortcodes
 */
require_once get_stylesheet_directory( ) . '/shortcodes/short_last_posts.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_last_news_partners.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_last_news_friends.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_authors_list.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_last_posts_authors_main_block.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_last_history_posts_carusel.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_yframe_custom_iframe.php';
require_once get_stylesheet_directory( ) . '/shortcodes/short_weatherapicom.php';
require_once get_stylesheet_directory( ) . '/shortcodes/__SHORT_OTHER_CUSTOMS.php';


require_once get_stylesheet_directory( ) . '/content_flex_gallery.php';
require_once get_stylesheet_directory( ) . '/content-announce.php';
require_once get_stylesheet_directory( ) . '/_ADS_Manager-fixes.php';
//require_once get_stylesheet_directory( ) . '/_TOP_NEWS_SLIDER.php'; -> themes/vsisumy_2025/views/general/loop/listing-modern-grid-1.php
require_once get_stylesheet_directory( ) . '/_TRANSITIONS_CLEANER.php';

require_once get_stylesheet_directory( ) . '/_COLOR_PICKER_FOR_POSTS.php';

/**
 * RSS фид спец. кат. специально для Укр.net
 */
require_once get_stylesheet_directory( ) . '/_RSS_feed_for_UKRnet.php';

/**
 * DEACTIVE
 */
//require_once get_stylesheet_directory( ) . '/shortcodes/short_last_announce.php';
//require_once get_stylesheet_directory( ) . '/widgets/widget-posts-feed.php';

/**
 * WP All Import - Export
 * Функции для обработки постов при импорте и экспорте
 * через плагины WP All Import + Export
 */

//require_once get_stylesheet_directory( ) . '/__WP_All_imports-exports/wp_all_export.php';
//require_once get_stylesheet_directory( ) . '/__WP_All_imports-exports/wp_all_import.php';
require_once get_stylesheet_directory( ) . '/__WP_All_imports-exports/wp_all_import__update_rel_follow_links.php';

//require_once get_stylesheet_directory( ) . '/shortcodes/listing-shortcodes/bs-modern-grid-listings.php';
if (!is_admin()) {
    require_once get_stylesheet_directory() . '/filtered_archives.php';
}



/**
 * Авторы в выпадающем списке
 *
 */
function manager_author_editor () {
    $users = get_users([ 'role__in' => [ 'author' ], 'role__not_in' => [ 'editor' ], 'blog_id' => get_current_blog_id() ]);
    foreach ($users as $user) {
        $user->add_role('editor');
    }
}
add_action('admin_init','manager_author_editor');


/**/

function true_apply_taxonomy_for_post_type(){
	// add_meta_box() в данном случае не нужен
	register_taxonomy_for_object_type('category', 'interview');
}
 
add_action('admin_init','true_apply_taxonomy_for_post_type');  
 
function true_expanded_request_custom($q) {
	if (isset($q['category_name'])) // для произвольных таксономий нужно использовать их название, например $q['product_category']
		$q['post_type'] = array('post', 'interview');
	return $q;
}
 
add_filter('request', 'true_expanded_request_custom');


function add_category_to_interview_post_type($args, $post_type) {
    if ('interview' === $post_type) {
        $args['taxonomies'] = array('category');
    }
    return $args;
}
add_filter('register_post_type_args', 'add_category_to_interview_post_type', 10, 2);





/****
* Інтерв'ю кількість переглядів
******/
add_action( 'add_meta_boxes', 'add_views_meta_box' );
function add_views_meta_box() {
    add_meta_box(
        'post_views',
        'Просмотры',
        'show_views_meta_box',
        'interview',
        'side',
        'high'
    );
}

function show_views_meta_box( $post ) {
    // Добавляем nonce для безопасности
    wp_nonce_field( 'save_post_views', 'post_views_nonce' );

    // Получить количество просмотров из метаданных записи
    $views = get_post_meta( $post->ID, 'views', true );

    // Вывести поле для ввода количества просмотров с экранированием
    echo '<input type="number" name="post_views" value="' . esc_attr( $views ) . '" />';
}

add_action( 'save_post', 'save_views_meta_data' );
function save_views_meta_data( $post_id ) {
    // Nonce перевірка
    if ( ! isset( $_POST['post_views_nonce'] ) || ! wp_verify_nonce( $_POST['post_views_nonce'], 'save_post_views' ) ) {
        return;
    }

    // Autosave перевірка
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Перевірка прав
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Проверить, установлено ли поле post_views
    if ( isset( $_POST['post_views'] ) ) {
        // Обновить метаданные записи с использованием absint для безопасности
        update_post_meta( $post_id, 'views', absint( $_POST['post_views'] ) );
    }
}
function add_categories_to_interviews() {
    register_taxonomy_for_object_type( 'category', 'interview' );
}
//add_action( 'init', 'add_categories_to_interviews' );

/****
 * НОВИН ПАРТНЕРІВ вибірка для віджету на головній
 */
add_action('pre_get_posts', function ($query) {

    // Тут вказуємо ID категорії "Новини партнерів (Головна віджет)"
    $term_partners_posts_filter = "15108";

    // Перевірте, чи запит відповідає потрібній категорії
    if (is_front_page() &&
        !empty($query->query['tax_query']) &&
        isset($query->query['tax_query'][0]['terms']) &&
        is_array($query->query['tax_query'][0]['terms']) &&
        isset($query->query['tax_query'][0]['terms'][0]) &&
        $query->query['tax_query'][0]['terms'][0] == $term_partners_posts_filter) {
        // Отримайте ID закріплених постів
        $sticky_posts = get_option('sticky_posts');

        // Отримайте усі пости з категорії
        $args = array(
            'posts_per_page' => 80,
            'post_status'    => 'publish',
            'cat'            => $term_partners_posts_filter, // Використання ID категорії
            'fields'         => 'ids',
            'post__not_in'   => $sticky_posts, // Виключіть закріплені пости з цього запиту
        );
        $all_posts = get_posts($args);

        // Об'єднайте ID закріплених постів та інших постів
        $post_ids = array_merge($sticky_posts, $all_posts);

        // Встановіть параметри запиту
        $query->set('post__in', $post_ids);
        $query->set('orderby', 'post__in');
        $query->set('ignore_sticky_posts', 1);
    }

});


// Підтримка тегів для CPT blogs, special_projects, та notes
add_action('init', function () {
    // Увімкнути REST для CPT `blogs` (якщо JetEngine його не вмикав)
    $pt_obj = get_post_type_object('blogs');
    if ( $pt_obj && empty($pt_obj->show_in_rest) ) {
        // Додатково ввімкнемо REST через фільтр на аргументи (див. Варіант C) — тут просто нагадаємо
    }

    // Прив'язати стандартні теги до CPT `blogs`
    register_taxonomy_for_object_type('post_tag', 'blogs');

    // Прив'язати стандартні теги до CPT `special_projects`
    register_taxonomy_for_object_type('post_tag', 'special_projects');

    // Прив'язати стандартні теги до CPT `notes`
    register_taxonomy_for_object_type('post_tag', 'notes');

    // Прив'язати стандартні теги до CPT `sumy_company` (компанії Sumy Market)
    register_taxonomy_for_object_type('post_tag', 'sumy_company');

    // Прив'язати стандартні теги до CPT `company_articles` (статті компаній)
    register_taxonomy_for_object_type('post_tag', 'company_articles');

    // Прив'язати стандартні теги до CPT `clubs` (клуби)
    register_taxonomy_for_object_type('post_tag', 'clubs');

    // Прив'язати стандартні теги до CPT `velyka_ekonomika` (Велика Економіка)
    register_taxonomy_for_object_type('post_tag', 'velyka_ekonomika');

    // Прив'язати стандартні теги до CPT `agro_sumy` (Аграрна Сумщина)
    register_taxonomy_for_object_type('post_tag', 'agro_sumy');
}, 100); // <- важливо: пізній пріоритет




/**
 * =====================================================
 * ПРИМУСОВЕ ВІДКЛЮЧЕННЯ GUTENBERG (БЛОЧНОГО РЕДАКТОРА)
 * =====================================================
 * 
 * Цей код повністю відключає редактор Gutenberg та примусово
 * використовує класичний редактор для всіх типів постів.
 * 
 * ВАЖЛИВО: Додавайте цей код в кінець файлу functions.php
 * вашої дочірньої теми (child theme). Якщо додасте в батьківську
 * тему - код зникне після оновлення теми.
 */

// =====================================================
// 1. ВІДКЛЮЧЕННЯ GUTENBERG ДЛЯ ОКРЕМИХ ПОСТІВ
// =====================================================
/**
 * Відключає блоковий редактор (Gutenberg) для всіх постів
 * 
 * Цей фільтр спрацьовує кожен раз, коли WordPress вирішує,
 * який редактор використовувати для конкретного посту.
 * 
 * @param bool $use_block_editor Чи використовувати блоковий редактор
 * @return bool Завжди повертає false (не використовувати)
 */
add_filter('use_block_editor_for_post', '__return_false', 10);


// =====================================================
// 2. ВІДКЛЮЧЕННЯ GUTENBERG ДЛЯ ТИПІВ ПОСТІВ
// =====================================================
/**
 * Відключає блоковий редактор для всіх типів контенту
 * (пости, сторінки, custom post types)
 * 
 * Цей фільтр контролює доступність Gutenberg на рівні
 * типів постів, а не окремих записів.
 * 
 * Пріоритет 10 - стандартний, але можна збільшити до 99,
 * якщо інші плагіни конфліктують
 * 
 * @param bool $use_block_editor Чи використовувати блоковий редактор
 * @return bool Завжди повертає false
 */
add_filter('use_block_editor_for_post_type', '__return_false', 10);


// =====================================================
// 3. ДОДАТКОВЕ ВІДКЛЮЧЕННЯ (якщо перші два не спрацювали)
// =====================================================
/**
 * Альтернативний метод відключення Gutenberg
 * 
 * Використовується як резервний варіант, якщо перші
 * два фільтри з якоїсь причини не спрацювали
 */
add_filter('gutenberg_can_edit_post_type', '__return_false', 10);


// =====================================================
// 4. ВИДАЛЕННЯ GUTENBERG CSS/JS З ФРОНТЕНДУ
// =====================================================
/**
 * Видаляє CSS стилі Gutenberg з frontend частини сайту
 * 
 * Це зменшує розмір сторінки та час завантаження,
 * оскільки стилі блокового редактора не потрібні,
 * якщо ви його не використовуєте
 */
function remove_gutenberg_styles() {
    // wp-block-library - основна бібліотека стилів блоків
    wp_dequeue_style('wp-block-library');
    
    // wp-block-library-theme - тематичні стилі блоків
    wp_dequeue_style('wp-block-library-theme');
    
    // global-styles - глобальні стилі WordPress 5.9+
    wp_dequeue_style('global-styles');
}
// Спрацьовує на frontend під час завантаження стилів
add_action('wp_enqueue_scripts', 'remove_gutenberg_styles', 100);


// =====================================================
// 5. ВІДКЛЮЧЕННЯ GUTENBERG ВІДЖЕТІВ (WordPress 5.8+)
// =====================================================
/**
 * Повертає класичний редактор віджетів
 * 
 * Починаючи з WordPress 5.8, віджети також використовують
 * блоковий редактор. Це відключає його для віджетів.
 */
add_filter('use_widgets_block_editor', '__return_false');


// =====================================================
// 6. ПОВЕРНЕННЯ КЛАСИЧНОГО РЕДАКТОРА ДЛЯ ВСІХ КОРИСТУВАЧІВ
// =====================================================
/**
 * Примусово встановлює класичний редактор для всіх користувачів
 * 
 * Ця функція перевіряє і встановлює мета-дані користувача,
 * які контролюють, який редактор відображається за замовчуванням
 */
function force_classic_editor_for_users() {
    // Отримуємо ID поточного користувача
    $user_id = get_current_user_id();
    
    // Якщо користувач не авторизований - виходимо
    if (!$user_id) {
        return;
    }
    
    // Перевіряємо поточне налаштування користувача
    $classic_editor_preference = get_user_meta($user_id, 'classic-editor-settings', true);
    
    // Якщо налаштування ще не встановлено або встановлено неправильно
    if ($classic_editor_preference !== 'classic-editor') {
        // Встановлюємо класичний редактор як налаштування за замовчуванням
        update_user_meta($user_id, 'classic-editor-settings', 'classic-editor');
    }
}
// Запускаємо при завантаженні адмінки
add_action('admin_init', 'force_classic_editor_for_users');

/**
 * Зміна aria-label для російської мови в WPML
 * Використовуємо output buffering для заміни в фінальному HTML
 */
add_action('wp_loaded', function() {
    ob_start(function($html) {
        // Заміна aria-label
        $html = str_replace(
            'aria-label="Перемкнути на Russian"',
            'aria-label="Перемкнути на мову окупантів"',
            $html
        );
        // Заміна title
        $html = str_replace(
            'title="Перемкнути на Russian"',
            'title="Перемкнути на мову окупантів"',
            $html
        );
        return $html;
    });
});

add_action('shutdown', function() {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}, 0);



// =====================================================
// 7. ВИДАЛЕННЯ GUTENBERG З АДМІНКИ (ОПЦІОНАЛЬНО)
// =====================================================
/**
 * Повністю видаляє Gutenberg скрипти з адмінпанелі
 * 
 * УВАГА: Розкоментуйте цей блок тільки якщо перші методи
 * не допомогли. Це агресивний метод, який може конфліктувати
 * з деякими плагінами.
 */
/*
function remove_gutenberg_admin_scripts() {
    wp_dequeue_script('wp-block-editor');
    wp_dequeue_script('wp-block-library');
    wp_dequeue_script('wp-blocks');
    wp_dequeue_script('wp-edit-post');
}
add_action('admin_enqueue_scripts', 'remove_gutenberg_admin_scripts', 100);
*/


// =====================================================
// 8. ЛОГУВАННЯ ДЛЯ ВІДЛАГОДЖЕННЯ (ОПЦІОНАЛЬНО)
// =====================================================
/**
 * Записує в лог, який редактор використовується
 * 
 * Розкоментуйте цей блок для діагностики проблеми.
 * Лог буде записаний у файл /wp-content/debug.log
 * (переконайтесь що WP_DEBUG_LOG увімкнено в wp-config.php)
 */
/*
function log_editor_type($use_block_editor) {
    error_log('Editor type check: ' . ($use_block_editor ? 'Gutenberg' : 'Classic'));
    return $use_block_editor;
}
add_filter('use_block_editor_for_post', 'log_editor_type', 1);
*/


// =====================================================
// 4. ПРИМУСОВА УСТАНОВКА КЛАСИЧНОГО РЕДАКТОРА ДЛЯ ПОСТІВ
// =====================================================
/**
 * При відкритті поста примусово встановлює класичний редактор
 */
function force_classic_editor_for_posts_only($post) {
    if ($post && $post->post_type === 'post') {
        // Видаляємо прапорці WPBakery
        delete_post_meta($post->ID, '_wpb_vc_js_status');
        delete_post_meta($post->ID, 'vcv-be-editor');
    }
}
add_action('edit_form_top', 'force_classic_editor_for_posts_only');

// Вимкнути WPML Translation Editor - відкривати переклади у WordPress редакторі
add_filter('wpml_use_tm_editor', '__return_false');










/***
 * FIX sticky_posts END
 */
/**
 * DEV
 */
/*
array (10) [
    'thumbnail' => array (3) [
        'width' => integer 284
        'height' => integer 211
        'crop' => string (1) "1"
    ]
    'medium' => array (3) [
        'width' => integer 284
        'height' => integer 500
        'crop' => bool FALSE
    ]
    'medium_large' => array (3) [
        'width' => integer 768
        'height' => integer 0
        'crop' => bool FALSE
    ]
    'large' => array (3) [
        'width' => integer 1300
        'height' => integer 1300
        'crop' => bool FALSE
    ]
    '1536x1536' => array (3) [
        'width' => integer 1536
        'height' => integer 1536
        'crop' => bool FALSE
    ]
    '2048x2048' => array (3) [
        'width' => integer 2048
        'height' => integer 2048
        'crop' => bool FALSE
    ]
    'list-thumb' => array (3) [
        'width' => integer 100
        'height' => integer 75
        'crop' => bool TRUE
    ]
    'announce-thumb' => array (3) [
        'width' => integer 90
        'height' => integer 120
        'crop' => bool TRUE
    ]
    'gallery-thumb' => array (3) [
        'width' => integer 110
        'height' => integer 82
        'crop' => bool TRUE
    ]
    'small-feature' => array (3) [
        'width' => integer 129
        'height' => integer 95
        'crop' => bool TRUE
    ]
]
*/

///**
// * Исправление iframe при импорте, после бага с ними
// */
//if ($_SERVER['REQUEST_URI'] == "/dev-ne-udaljat-dlja-testirovanija-baga-s-iframe-src/"){
//
//    $original_post = 'http://rama.com.ua/sumah-patrulnyie-sostavili-protokol-na-voditelya-sovershivshego-letom-dtp/';
//    $import_post_content = '<div class="old_post">В июне водитель авто ВАЗ-2107 въехал в летнюю площадку на ул. Набережная р. Стрелки. Троих человек, получивших травмы, госпитализировали. 55-летний мужчина сдал тест на употребление алкоголя - результат оказался негативным. Однако тогда же он сдал и анализы биосреды; и вот он как раз показал, что водитель всё-таки был нетрезвым. Таким образом, через три месяца патрульные всё же составили на водителя админпротокол по 130-й статье.
//
//Пресс-служба патрульной полиции Сумской области
//<iframe title="YouTube video player" src="" frameborder="0" width="560" height="315"></iframe></div>';
//
//    $html_in_original_site = new DOMDocument();
//    libxml_use_internal_errors( 1 );
//    $html_in_original_site->loadHTMLfile( $original_post );
//    $html_in_original_site->formatOutput = True;
//
//    ddd($html_in_original_site);
//
//
//    $import_post_content = new SimpleXMLElement($import_post_content);
//
//
//    foreach ($import_post_content->iframe as $iframe) {
//
//        if ($iframe['src'] == ""){
//            // iframe src пуст
//        }
//
//    }
//    ddd($import_post_content);
//
//
////    /**
////     * И возвращаем HTML в кодировке
////     */
////    $import_post_content = html_entity_decode((string)$import_post_content->asXML(), ENT_NOQUOTES, 'UTF-8');
//
//
//
//}



