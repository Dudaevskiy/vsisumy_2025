<?php
/**
 *
 * require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/SDStudio-Custom_Permalink_Fixes.php');
 */


/**
 * Handle custom permalink and language conversion on post save
 *
 * Ця функція створена що б виправити баг з отримання постів у сітці постів (враховуючи і переклади):
 *
 * Спрацьовує при збереженні будь-якого типу посту завдяки хуку save_post
 * Перевіряє чи не є це автозбереженням або ревізією
 * Отримує значення метаполів custom_permalink та custom_permalink_language
 * Якщо обидва поля існують і не пусті:
 *
 * Видаляє метаполе custom_permalink_language
 * Конвертує значення з custom_permalink_language у слаг за допомогою sanitize_title()
 * Оновлює слаг посту
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an existing post being updated
 */
//function handle_custom_permalink_on_save($post_id, $post, $update)
//{
//    // Skip autosaves
//    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
//        return;
//    }
//
//    // Skip revisions
//    if (wp_is_post_revision($post_id)) {
//        return;
//    }
//
//    // Get custom permalink and language values with fallbacks
//    $custom_permalink = get_post_meta($post_id, 'custom_permalink', true);
//    $custom_permalink = !empty($custom_permalink) ? $custom_permalink : '';
//
//    $custom_permalink_language = get_post_meta($post_id, 'custom_permalink_language', true);
//    $custom_permalink_language = !empty($custom_permalink_language) ? $custom_permalink_language : '';
//
//// Додаткова перевірка і встановлення значень за замовчуванням
//    if (!empty($custom_permalink)) {
//        // Для URL
//        $slug = sanitize_title_for_query($custom_permalink);
//
//        // Convert language value to slug
//        $new_slug = sanitize_title($slug);
//
//        global $wpdb;
//
//// Підготовка даних для безпечного оновлення
//        $table = $wpdb->posts;
//        $data = array('post_name' => $new_slug);
//        $where = array('ID' => $post_id);
//
//// Безпечне оновлення через prepare
//        $wpdb->update(
//            $table,
//            $data,
//            $where,
//            array('%s'),  // формат для data
//            array('%d')   // формат для where
//        );
//
//// Очищення кешу для цього поста
//        clean_post_cache($post_id);
//    }
//
//    if (!empty($custom_permalink_language)) {
//        // Delete the language meta field
//        $inf = delete_post_meta($post_id, 'custom_permalink_language');
//    }
//}
//
//// Add the function to handle all post types
//add_action('save_post', 'handle_custom_permalink_on_save', 10, 3);


/**
 * Handle custom permalink and language conversion on post save
 *
 * Ця функція створена що б виправити баг з отримання постів у сітці постів (враховуючи і переклади):
 *
 * Спрацьовує при збереженні будь-якого типу посту завдяки хуку save_post
 * Перевіряє чи не є це автозбереженням або ревізією
 * Отримує значення метаполів custom_permalink та custom_permalink_language
 * Якщо обидва поля існують і не пусті:
 *
 * Видаляє метаполе custom_permalink_language
 * Конвертує значення з custom_permalink_language у слаг за допомогою sanitize_title()
 * Оновлює слаг посту
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an existing post being updated
 */
function handle_custom_permalink_on_save($post_id, $post, $update)
{
    // Skip autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Skip revisions
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Додаємо перевірку, щоб уникнути повторного спрацювання
    if (defined('DOING_CUSTOM_PERMALINK_UPDATE') && DOING_CUSTOM_PERMALINK_UPDATE) {
        return;
    }

    // Get custom permalink and language values with fallbacks
    $custom_permalink = get_post_meta($post_id, 'custom_permalink', true);
    $custom_permalink = !empty($custom_permalink) ? $custom_permalink : '';

    $custom_permalink_language = get_post_meta($post_id, 'custom_permalink_language', true);
    $custom_permalink_language = !empty($custom_permalink_language) ? $custom_permalink_language : '';

    // Додаткова перевірка і встановлення значень за замовчуванням
    if (!empty($custom_permalink)) {
        // Встановлюємо прапорець для уникнення рекурсії
        define('DOING_CUSTOM_PERMALINK_UPDATE', true);

        // Для URL
        $slug = sanitize_title_for_query($custom_permalink);
        $new_slug = sanitize_title($slug);

        global $wpdb;

        // Підготовка даних для безпечного оновлення
        $table = $wpdb->posts;
        $data = array('post_name' => $new_slug);
        $where = array('ID' => $post_id);

        // Безпечне оновлення через prepare
        $wpdb->update(
            $table,
            $data,
            $where,
            array('%s'),  // формат для data
            array('%d')   // формат для where
        );

        // Очищення кешу для цього поста
        clean_post_cache($post_id);
    }

    if (!empty($custom_permalink_language)) {
        // Delete the language meta field
        delete_post_meta($post_id, 'custom_permalink_language');
    }
}

// Add the function to handle all post types with lower priority
//add_action('save_post', 'handle_custom_permalink_on_save', 999, 3);