<?php
/**
 *
 * require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/SDStudio-WPML-Fixes.php');
 */


/**
 * Виправлення бага з /uk/uk/ при створенні переклада і застосуванні кастомного лінка що містить '/uk/' -
 * баг поляга у тому що у перемикачі мов на англ. мові посилання на укр. містить дубль '/uk/' - '/uk/uk/'
 * FIX START
 * Fix double /uk/uk/ in WPML language switcher URLs
 */
add_filter('wpml_ls_language_url', function ($url, $data) {
    // Перевіряємо чи це посилання на українську версію
    if (isset($data['code']) && $data['code'] === 'uk') {
        // Перевіряємо наявність подвійного /uk/uk/
        if (strpos($url, '/uk/uk/') !== false) {
            // Замінюємо подвійний /uk/uk/ на одинарний /uk/
            $url = str_replace('/uk/uk/', '/uk/', $url);
        }
    }
    return $url;
}, 10, 2);

/**
 * Fix double /uk/uk/ in WPML menu language switcher
 */
add_filter('wpml_ls_languages', function ($languages) {
    if (!empty($languages['uk'])) {
        if (strpos($languages['uk']['url'], '/uk/uk/') !== false) {
            $languages['uk']['url'] = str_replace('/uk/uk/', '/uk/', $languages['uk']['url']);
        }
    }
    return $languages;
}, 10, 1);
/**
 *  FIX END
 */


/**
 *  У шаблоні імпорту тільки прописуємо мета поле "wpml_id_parrent" та вказуємо у ньому ID поста на основній мові сайту.
 */


add_action('pmxi_saved_post', 'connect_wpml_translations_by_post_ID_PARRENT', 10, 1);

function connect_wpml_translations_by_post_ID_PARRENT($post_id) {
    global $sitepress, $wpdb;

    // Перевіряємо, чи встановлено WPML
    if (!function_exists('icl_object_id')) return;

    // Отримуємо імпортований пост
    $post = get_post($post_id);

    // Отримуємо ID батьківського поста на основній мові
    $parent_post_id = get_post_meta($post_id, 'wpml_id_parrent', true);

    // Якщо немає ID батьківського поста, виходимо з функції
    if (empty($parent_post_id)) return;

    // Перевіряємо, чи є обкладинка у поточного поста
    $current_thumbnail_id = get_post_thumbnail_id($post_id);

    // Якщо обкладинки немає, копіюємо її з батьківського поста
    if (!$current_thumbnail_id) {
        $parent_thumbnail_id = get_post_thumbnail_id($parent_post_id);
        if ($parent_thumbnail_id) {
            // Встановлюємо обкладинку для поточного поста
            set_post_thumbnail($post_id, $parent_thumbnail_id);
        }
    }

    // Отримуємо мову поточного поста
    $post_language_details = apply_filters('wpml_post_language_details', NULL, $post_id);
    $current_language_code = $post_language_details['language_code'];

    // Отримуємо основну мову сайту
    $default_language = $sitepress->get_default_language();

    // Якщо поточний пост на основній мові, виходимо з функції
    if ($current_language_code == $default_language) return;

    // Отримуємо trid батьківського поста
    $trid_query = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id={$parent_post_id} AND element_type='post_{$post->post_type}' LIMIT 1";
    $trid = $wpdb->get_var($trid_query);

    // Якщо trid не знайдено, виходимо з функції
    if (empty($trid)) return;

    // Оновлюємо таблицю перекладів
    $sitepress->set_element_language_details(
        $post_id,
        'post_' . $post->post_type,
        $trid,
        $current_language_code
    );
}