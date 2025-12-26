<?php
/**
 * WPML Fixes - Фікси для перекладів та мультимовності
 *
 * Цей файл містить різні фікси, хуки та допоміжні функції
 * для роботи з WPML плагіном мультимовності
 *
 * @package vsisumy_2025
 * @since 2024-12
 */

// Захист від прямого доступу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =====================================================
 * Фікс помилки open_basedir для WPML String Translation
 * =====================================================
 *
 * Вирішує проблему: file_exists(): open_basedir restriction in effect
 * для файлів перекладу .mo та .l10n.php
 */
add_filter('load_translation_file', 'wpml_fix_open_basedir_translation', 1, 2);

function wpml_fix_open_basedir_translation($file, $domain) {
    // Перевіряємо чи файл знаходиться в дозволеній директорії
    if ($file && !@file_exists($file)) {
        // Якщо файл не існує або недоступний - повертаємо false
        // щоб уникнути помилки open_basedir
        $allowed_paths = [
            ABSPATH,
            WP_CONTENT_DIR,
            '/tmp/'
        ];

        $file_allowed = false;
        foreach ($allowed_paths as $path) {
            if (strpos($file, $path) === 0) {
                $file_allowed = true;
                break;
            }
        }

        if (!$file_allowed) {
            return false;
        }
    }
    return $file;
}

/**
 * =====================================================
 * Шорткод [wpml_translate] для мультимовного контенту
 * =====================================================
 *
 * Використання:
 * [wpml_translate lang="uk"]Український текст[/wpml_translate]
 * [wpml_translate lang="ru"]Русский текст[/wpml_translate]
 * [wpml_translate lang="en"]English text[/wpml_translate]
 */
add_shortcode('wpml_translate', 'wpml_translate_shortcode');

function wpml_translate_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'lang' => '',
    ), $atts);

    // Отримуємо поточну мову
    $current_lang = apply_filters('wpml_current_language', null);

    // Показуємо контент тільки якщо мова співпадає
    if ($atts['lang'] === $current_lang) {
        return do_shortcode($content);
    }

    return '';
}

/**
 * Дозволити шорткоди у віджетах
 */
add_filter('widget_text', 'do_shortcode');
add_filter('widget_text', 'shortcode_unautop');

// Для кастомних полів віджетів (наприклад Better Studio)
add_filter('widget_custom_html_content', 'do_shortcode');

// Універсальний фільтр для всіх текстових полів віджетів
add_filter('dynamic_sidebar_params', 'wpml_process_widget_shortcodes');

function wpml_process_widget_shortcodes($params) {
    return $params;
}

// Фільтр для обробки шорткодів у будь-якому виведенні віджета
add_filter('widget_display_callback', 'wpml_widget_display_shortcodes', 10, 3);

function wpml_widget_display_shortcodes($instance, $widget, $args) {
    if (is_array($instance)) {
        array_walk_recursive($instance, function(&$value) {
            if (is_string($value) && strpos($value, '[wpml_translate') !== false) {
                $value = do_shortcode($value);
            }
        });
    }
    return $instance;
}

/**
 * =====================================================
 * WPML Translation Connector for WP All Import
 * =====================================================
 *
 * Автоматично прив'язує імпортований пост як переклад до батьківського поста
 * на основі мета-поля 'wpml_parent_id'
 *
 * Використання:
 * 1. При імпорті через WP All Import додайте мета-поле 'wpml_parent_id'
 *    зі значенням ID українського (основного) поста
 * 2. Функція автоматично прив'яже імпортований пост як переклад
 *
 * Приклад CSV:
 * | post_title | post_content | wpml_parent_id |
 * | Заголовок RU | Контент RU | 137901 |
 */
add_action('pmxi_saved_post', 'wpml_connect_translations_on_import', 10, 1);

function wpml_connect_translations_on_import($post_id) {
    global $sitepress, $wpdb;

    // Check if WPML is installed
    if (!function_exists('icl_object_id') || !isset($sitepress)) return;

    // Get parent post ID from meta
    $parent_post_id = get_post_meta($post_id, 'wpml_parent_id', true);

    // If there's no parent ID, exit the function
    if (empty($parent_post_id)) return;

    // Make sure parent post exists
    $parent_post = get_post($parent_post_id);
    if (!$parent_post) return;

    // Get the imported post
    $post = get_post($post_id);

    // Get the trid of the parent post
    $trid = apply_filters('wpml_element_trid', null, $parent_post_id, 'post_' . $parent_post->post_type);

    // If the trid is not found, exit the function
    if (empty($trid)) return;

    // Get the language of the current post
    $post_language_details = apply_filters('wpml_post_language_details', null, $post_id);
    $language_code = isset($post_language_details['language_code']) ? $post_language_details['language_code'] : null;

    // If no language set, try to detect from WPML current language or default to 'ru'
    if (empty($language_code)) {
        $language_code = $sitepress->get_current_language();
        if ($language_code === 'all' || empty($language_code)) {
            $language_code = 'ru'; // Default fallback
        }
    }

    // Get the source language (parent post language)
    $parent_language_details = apply_filters('wpml_post_language_details', null, $parent_post_id);
    $source_language_code = isset($parent_language_details['language_code']) ? $parent_language_details['language_code'] : apply_filters('wpml_default_language', null);

    // Link the post as translation
    $sitepress->set_element_language_details($post_id, 'post_' . $post->post_type, $trid, $language_code, $source_language_code);

    // Copy featured image from parent if current post doesn't have one
    $current_thumbnail_id = get_post_thumbnail_id($post_id);
    if (empty($current_thumbnail_id)) {
        $parent_thumbnail_id = get_post_thumbnail_id($parent_post_id);
        if (!empty($parent_thumbnail_id)) {
            set_post_thumbnail($post_id, $parent_thumbnail_id);
        }
    }

    // Copy translated categories from parent post
    $parent_categories = wp_get_post_categories($parent_post_id, array('fields' => 'ids'));
    if (!empty($parent_categories)) {
        $translated_categories = array();
        foreach ($parent_categories as $parent_cat_id) {
            // Get translated category for current language
            $translated_cat_id = apply_filters('wpml_object_id', $parent_cat_id, 'category', false, $language_code);
            if ($translated_cat_id) {
                $translated_categories[] = (int) $translated_cat_id;
            }
        }
        if (!empty($translated_categories)) {
            wp_set_post_categories($post_id, $translated_categories);
        }
    }

    // Optional: Remove the meta after successful connection
    // delete_post_meta($post_id, 'wpml_parent_id');
}

/**
 * =====================================================
 * Універсальний фікс для archives у меню
 * =====================================================
 *
 * Якщо архів CPT вказано у пунктах меню, замість "Архів клубів"
 * буде відображатись основна назва типу "Клуби"
 */
add_filter('wp_setup_nav_menu_item', 'wpml_fix_archive_menu_title');

function wpml_fix_archive_menu_title($menu_item) {
    // Перевіряємо чи це посилання на архів CPT
    if ($menu_item->type === 'post_type_archive') {
        $post_type = $menu_item->object;
        $post_type_obj = get_post_type_object($post_type);

        if ($post_type_obj && isset($post_type_obj->labels->name)) {
            // Використовуємо основну назву (name) замість archives
            $menu_item->title = $post_type_obj->labels->name;
        }
    }

    return $menu_item;
}

/**
 * =====================================================
 * Переклади назв Custom Post Types
 * =====================================================
 */

/**
 * Переклад назви типу постів "clubs" (Клуби)
 */
add_filter('post_type_labels_clubs', 'wpml_translate_clubs_labels');

function wpml_translate_clubs_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Клубы';
            $labels->singular_name = 'Клуб';
            $labels->menu_name = 'Клубы';
            $labels->all_items = 'Все клубы';
            $labels->add_new = 'Добавить новый';
            $labels->add_new_item = 'Добавить новый клуб';
            $labels->edit_item = 'Редактировать клуб';
            $labels->new_item = 'Новый клуб';
            $labels->view_item = 'Просмотреть клуб';
            $labels->view_items = 'Просмотреть клубы';
            $labels->search_items = 'Искать клубы';
            $labels->not_found = 'Клубы не найдены';
            $labels->not_found_in_trash = 'В корзине клубов не найдено';
            $labels->archives = 'Архив клубов';
            break;

        case 'en':
            $labels->name = 'Clubs';
            $labels->singular_name = 'Club';
            $labels->menu_name = 'Clubs';
            $labels->all_items = 'All Clubs';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Club';
            $labels->edit_item = 'Edit Club';
            $labels->new_item = 'New Club';
            $labels->view_item = 'View Club';
            $labels->view_items = 'View Clubs';
            $labels->search_items = 'Search Clubs';
            $labels->not_found = 'No clubs found';
            $labels->not_found_in_trash = 'No clubs found in Trash';
            $labels->archives = 'Club Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Клуби';
            $labels->singular_name = 'Клуб';
            $labels->menu_name = 'Клуби';
            $labels->all_items = 'Усі клуби';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий клуб';
            $labels->edit_item = 'Редагувати клуб';
            $labels->new_item = 'Новий клуб';
            $labels->view_item = 'Переглянути клуб';
            $labels->view_items = 'Переглянути клуби';
            $labels->search_items = 'Шукати клуби';
            $labels->not_found = 'Клуби не знайдено';
            $labels->not_found_in_trash = 'У кошику клубів не знайдено';
            $labels->archives = 'Архів клубів';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "velyka_ekonomika" (Велика економіка)
 */
add_filter('post_type_labels_velyka_ekonomika', 'wpml_translate_velyka_ekonomika_labels');

function wpml_translate_velyka_ekonomika_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Большая экономика';
            $labels->singular_name = 'Большая экономика';
            $labels->menu_name = 'Большая экономика';
            $labels->all_items = 'Все записи';
            $labels->add_new = 'Добавить новую';
            $labels->add_new_item = 'Добавить новую запись';
            $labels->edit_item = 'Редактировать запись';
            $labels->new_item = 'Новая запись';
            $labels->view_item = 'Просмотреть запись';
            $labels->view_items = 'Просмотреть записи';
            $labels->search_items = 'Искать записи';
            $labels->not_found = 'Записи не найдены';
            $labels->not_found_in_trash = 'В корзине записей не найдено';
            $labels->archives = 'Архив Большой экономики';
            break;

        case 'en':
            $labels->name = 'Big Economy';
            $labels->singular_name = 'Big Economy';
            $labels->menu_name = 'Big Economy';
            $labels->all_items = 'All Entries';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Entry';
            $labels->edit_item = 'Edit Entry';
            $labels->new_item = 'New Entry';
            $labels->view_item = 'View Entry';
            $labels->view_items = 'View Entries';
            $labels->search_items = 'Search Entries';
            $labels->not_found = 'No entries found';
            $labels->not_found_in_trash = 'No entries found in Trash';
            $labels->archives = 'Big Economy Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Велика економіка';
            $labels->singular_name = 'Велика економіка';
            $labels->menu_name = 'Велика економіка';
            $labels->all_items = 'Усі записи';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий запис';
            $labels->edit_item = 'Редагувати запис';
            $labels->new_item = 'Новий запис';
            $labels->view_item = 'Переглянути запис';
            $labels->view_items = 'Переглянути записи';
            $labels->search_items = 'Шукати записи';
            $labels->not_found = 'Записи не знайдено';
            $labels->not_found_in_trash = 'У кошику записів не знайдено';
            $labels->archives = 'Архів Великої економіки';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "agro_sumy" (Аграрна Сумщина)
 */
add_filter('post_type_labels_agro_sumy', 'wpml_translate_agro_sumy_labels');

function wpml_translate_agro_sumy_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Аграрная Сумщина';
            $labels->singular_name = 'Аграрная Сумщина';
            $labels->menu_name = 'Аграрная Сумщина';
            $labels->all_items = 'Все записи';
            $labels->add_new = 'Добавить новую';
            $labels->add_new_item = 'Добавить новую запись';
            $labels->edit_item = 'Редактировать запись';
            $labels->new_item = 'Новая запись';
            $labels->view_item = 'Просмотреть запись';
            $labels->view_items = 'Просмотреть записи';
            $labels->search_items = 'Искать записи';
            $labels->not_found = 'Записи не найдены';
            $labels->not_found_in_trash = 'В корзине записей не найдено';
            $labels->archives = 'Архив Аграрной Сумщины';
            break;

        case 'en':
            $labels->name = 'Agrarian Sumy';
            $labels->singular_name = 'Agrarian Sumy';
            $labels->menu_name = 'Agrarian Sumy';
            $labels->all_items = 'All Entries';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Entry';
            $labels->edit_item = 'Edit Entry';
            $labels->new_item = 'New Entry';
            $labels->view_item = 'View Entry';
            $labels->view_items = 'View Entries';
            $labels->search_items = 'Search Entries';
            $labels->not_found = 'No entries found';
            $labels->not_found_in_trash = 'No entries found in Trash';
            $labels->archives = 'Agrarian Sumy Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Аграрна Сумщина';
            $labels->singular_name = 'Аграрна Сумщина';
            $labels->menu_name = 'Аграрна Сумщина';
            $labels->all_items = 'Усі записи';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий запис';
            $labels->edit_item = 'Редагувати запис';
            $labels->new_item = 'Новий запис';
            $labels->view_item = 'Переглянути запис';
            $labels->view_items = 'Переглянути записи';
            $labels->search_items = 'Шукати записи';
            $labels->not_found = 'Записи не знайдено';
            $labels->not_found_in_trash = 'У кошику записів не знайдено';
            $labels->archives = 'Архів Аграрної Сумщини';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "contest_entries" (Заявки на конкурси)
 */
add_filter('post_type_labels_contest_entries', 'wpml_translate_contest_entries_labels');

function wpml_translate_contest_entries_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Заявки на конкурсы';
            $labels->singular_name = 'Заявка на конкурс';
            $labels->menu_name = 'Заявки на конкурсы';
            $labels->all_items = 'Все заявки';
            $labels->add_new = 'Добавить новую';
            $labels->add_new_item = 'Добавить новую заявку';
            $labels->edit_item = 'Редактировать заявку';
            $labels->new_item = 'Новая заявка';
            $labels->view_item = 'Просмотреть заявку';
            $labels->view_items = 'Просмотреть заявки';
            $labels->search_items = 'Искать заявки';
            $labels->not_found = 'Заявки не найдены';
            $labels->not_found_in_trash = 'В корзине заявок не найдено';
            $labels->archives = 'Архив заявок';
            break;

        case 'en':
            $labels->name = 'Contest Entries';
            $labels->singular_name = 'Contest Entry';
            $labels->menu_name = 'Contest Entries';
            $labels->all_items = 'All Entries';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Entry';
            $labels->edit_item = 'Edit Entry';
            $labels->new_item = 'New Entry';
            $labels->view_item = 'View Entry';
            $labels->view_items = 'View Entries';
            $labels->search_items = 'Search Entries';
            $labels->not_found = 'No entries found';
            $labels->not_found_in_trash = 'No entries found in Trash';
            $labels->archives = 'Contest Entry Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Заявки на конкурси';
            $labels->singular_name = 'Заявка на конкурс';
            $labels->menu_name = 'Заявки на конкурси';
            $labels->all_items = 'Усі заявки';
            $labels->add_new = 'Додати нову';
            $labels->add_new_item = 'Додати нову заявку';
            $labels->edit_item = 'Редагувати заявку';
            $labels->new_item = 'Нова заявка';
            $labels->view_item = 'Переглянути заявку';
            $labels->view_items = 'Переглянути заявки';
            $labels->search_items = 'Шукати заявки';
            $labels->not_found = 'Заявки не знайдено';
            $labels->not_found_in_trash = 'У кошику заявок не знайдено';
            $labels->archives = 'Архів заявок';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "contests" (Конкурси)
 */
add_filter('post_type_labels_contests', 'wpml_translate_contests_labels');

function wpml_translate_contests_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Конкурсы';
            $labels->singular_name = 'Конкурс';
            $labels->menu_name = 'Конкурсы';
            $labels->all_items = 'Все конкурсы';
            $labels->add_new = 'Добавить новый';
            $labels->add_new_item = 'Добавить новый конкурс';
            $labels->edit_item = 'Редактировать конкурс';
            $labels->new_item = 'Новый конкурс';
            $labels->view_item = 'Просмотреть конкурс';
            $labels->view_items = 'Просмотреть конкурсы';
            $labels->search_items = 'Искать конкурсы';
            $labels->not_found = 'Конкурсы не найдены';
            $labels->not_found_in_trash = 'В корзине конкурсов не найдено';
            $labels->archives = 'Архив конкурсов';
            break;

        case 'en':
            $labels->name = 'Contests';
            $labels->singular_name = 'Contest';
            $labels->menu_name = 'Contests';
            $labels->all_items = 'All Contests';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Contest';
            $labels->edit_item = 'Edit Contest';
            $labels->new_item = 'New Contest';
            $labels->view_item = 'View Contest';
            $labels->view_items = 'View Contests';
            $labels->search_items = 'Search Contests';
            $labels->not_found = 'No contests found';
            $labels->not_found_in_trash = 'No contests found in Trash';
            $labels->archives = 'Contest Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Конкурси';
            $labels->singular_name = 'Конкурс';
            $labels->menu_name = 'Конкурси';
            $labels->all_items = 'Усі конкурси';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий конкурс';
            $labels->edit_item = 'Редагувати конкурс';
            $labels->new_item = 'Новий конкурс';
            $labels->view_item = 'Переглянути конкурс';
            $labels->view_items = 'Переглянути конкурси';
            $labels->search_items = 'Шукати конкурси';
            $labels->not_found = 'Конкурси не знайдено';
            $labels->not_found_in_trash = 'У кошику конкурсів не знайдено';
            $labels->archives = 'Архів конкурсів';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "polls" (Опитування)
 */
add_filter('post_type_labels_polls', 'wpml_translate_polls_labels');

function wpml_translate_polls_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Опросы';
            $labels->singular_name = 'Опрос';
            $labels->menu_name = 'Опросы';
            $labels->all_items = 'Все опросы';
            $labels->add_new = 'Добавить новый';
            $labels->add_new_item = 'Добавить новый опрос';
            $labels->edit_item = 'Редактировать опрос';
            $labels->new_item = 'Новый опрос';
            $labels->view_item = 'Просмотреть опрос';
            $labels->view_items = 'Просмотреть опросы';
            $labels->search_items = 'Искать опросы';
            $labels->not_found = 'Опросы не найдены';
            $labels->not_found_in_trash = 'В корзине опросов не найдено';
            $labels->archives = 'Архив опросов';
            break;

        case 'en':
            $labels->name = 'Polls';
            $labels->singular_name = 'Poll';
            $labels->menu_name = 'Polls';
            $labels->all_items = 'All Polls';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Poll';
            $labels->edit_item = 'Edit Poll';
            $labels->new_item = 'New Poll';
            $labels->view_item = 'View Poll';
            $labels->view_items = 'View Polls';
            $labels->search_items = 'Search Polls';
            $labels->not_found = 'No polls found';
            $labels->not_found_in_trash = 'No polls found in Trash';
            $labels->archives = 'Poll Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Опитування';
            $labels->singular_name = 'Опитування';
            $labels->menu_name = 'Опитування';
            $labels->all_items = 'Усі опитування';
            $labels->add_new = 'Додати нове';
            $labels->add_new_item = 'Додати нове опитування';
            $labels->edit_item = 'Редагувати опитування';
            $labels->new_item = 'Нове опитування';
            $labels->view_item = 'Переглянути опитування';
            $labels->view_items = 'Переглянути опитування';
            $labels->search_items = 'Шукати опитування';
            $labels->not_found = 'Опитування не знайдено';
            $labels->not_found_in_trash = 'У кошику опитувань не знайдено';
            $labels->archives = 'Архів опитувань';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "interview" (Інтерв'ю)
 */
add_filter('post_type_labels_interview', 'wpml_translate_interview_labels');

function wpml_translate_interview_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Интервью';
            $labels->singular_name = 'Интервью';
            $labels->menu_name = 'Интервью';
            $labels->all_items = 'Все интервью';
            $labels->add_new = 'Добавить новое';
            $labels->add_new_item = 'Добавить новое интервью';
            $labels->edit_item = 'Редактировать интервью';
            $labels->new_item = 'Новое интервью';
            $labels->view_item = 'Просмотреть интервью';
            $labels->view_items = 'Просмотреть интервью';
            $labels->search_items = 'Искать интервью';
            $labels->not_found = 'Интервью не найдены';
            $labels->not_found_in_trash = 'В корзине интервью не найдено';
            $labels->archives = 'Архив интервью';
            break;

        case 'en':
            $labels->name = 'Interviews';
            $labels->singular_name = 'Interview';
            $labels->menu_name = 'Interviews';
            $labels->all_items = 'All Interviews';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Interview';
            $labels->edit_item = 'Edit Interview';
            $labels->new_item = 'New Interview';
            $labels->view_item = 'View Interview';
            $labels->view_items = 'View Interviews';
            $labels->search_items = 'Search Interviews';
            $labels->not_found = 'No interviews found';
            $labels->not_found_in_trash = 'No interviews found in Trash';
            $labels->archives = 'Interview Archives';
            break;

        case 'uk':
        default:
            $labels->name = "Інтерв'ю";
            $labels->singular_name = "Інтерв'ю";
            $labels->menu_name = "Інтерв'ю";
            $labels->all_items = "Усі інтерв'ю";
            $labels->add_new = 'Додати нове';
            $labels->add_new_item = "Додати нове інтерв'ю";
            $labels->edit_item = "Редагувати інтерв'ю";
            $labels->new_item = "Нове інтерв'ю";
            $labels->view_item = "Переглянути інтерв'ю";
            $labels->view_items = "Переглянути інтерв'ю";
            $labels->search_items = "Шукати інтерв'ю";
            $labels->not_found = "Інтерв'ю не знайдено";
            $labels->not_found_in_trash = "У кошику інтерв'ю не знайдено";
            $labels->archives = "Архів інтерв'ю";
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "company_articles" (Записи компаній)
 */
add_filter('post_type_labels_company_articles', 'wpml_translate_company_articles_labels');

function wpml_translate_company_articles_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Записи компаний';
            $labels->singular_name = 'Запись компании';
            $labels->menu_name = 'Записи компаний';
            $labels->all_items = 'Все записи';
            $labels->add_new = 'Добавить новую';
            $labels->add_new_item = 'Добавить новую запись';
            $labels->edit_item = 'Редактировать запись';
            $labels->new_item = 'Новая запись';
            $labels->view_item = 'Просмотреть запись';
            $labels->view_items = 'Просмотреть записи';
            $labels->search_items = 'Искать записи';
            $labels->not_found = 'Записи не найдены';
            $labels->not_found_in_trash = 'В корзине записей не найдено';
            $labels->archives = 'Архив записей компаний';
            break;

        case 'en':
            $labels->name = 'Company Articles';
            $labels->singular_name = 'Company Article';
            $labels->menu_name = 'Company Articles';
            $labels->all_items = 'All Articles';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Article';
            $labels->edit_item = 'Edit Article';
            $labels->new_item = 'New Article';
            $labels->view_item = 'View Article';
            $labels->view_items = 'View Articles';
            $labels->search_items = 'Search Articles';
            $labels->not_found = 'No articles found';
            $labels->not_found_in_trash = 'No articles found in Trash';
            $labels->archives = 'Company Article Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Записи компаній';
            $labels->singular_name = 'Запис компанії';
            $labels->menu_name = 'Записи компаній';
            $labels->all_items = 'Усі записи';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий запис';
            $labels->edit_item = 'Редагувати запис';
            $labels->new_item = 'Новий запис';
            $labels->view_item = 'Переглянути запис';
            $labels->view_items = 'Переглянути записи';
            $labels->search_items = 'Шукати записи';
            $labels->not_found = 'Записи не знайдено';
            $labels->not_found_in_trash = 'У кошику записів не знайдено';
            $labels->archives = 'Архів записів компаній';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "sumy_company" (Компанії)
 */
add_filter('post_type_labels_sumy_company', 'wpml_translate_sumy_company_labels');

function wpml_translate_sumy_company_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Компании';
            $labels->singular_name = 'Компания';
            $labels->menu_name = 'Компании';
            $labels->all_items = 'Все компании';
            $labels->add_new = 'Добавить новую';
            $labels->add_new_item = 'Добавить новую компанию';
            $labels->edit_item = 'Редактировать компанию';
            $labels->new_item = 'Новая компания';
            $labels->view_item = 'Просмотреть компанию';
            $labels->view_items = 'Просмотреть компании';
            $labels->search_items = 'Искать компании';
            $labels->not_found = 'Компании не найдены';
            $labels->not_found_in_trash = 'В корзине компаний не найдено';
            $labels->archives = 'Архив компаний';
            break;

        case 'en':
            $labels->name = 'Companies';
            $labels->singular_name = 'Company';
            $labels->menu_name = 'Companies';
            $labels->all_items = 'All Companies';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Company';
            $labels->edit_item = 'Edit Company';
            $labels->new_item = 'New Company';
            $labels->view_item = 'View Company';
            $labels->view_items = 'View Companies';
            $labels->search_items = 'Search Companies';
            $labels->not_found = 'No companies found';
            $labels->not_found_in_trash = 'No companies found in Trash';
            $labels->archives = 'Company Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Компанії';
            $labels->singular_name = 'Компанія';
            $labels->menu_name = 'Компанії';
            $labels->all_items = 'Усі компанії';
            $labels->add_new = 'Додати нову';
            $labels->add_new_item = 'Додати нову компанію';
            $labels->edit_item = 'Редагувати компанію';
            $labels->new_item = 'Нова компанія';
            $labels->view_item = 'Переглянути компанію';
            $labels->view_items = 'Переглянути компанії';
            $labels->search_items = 'Шукати компанії';
            $labels->not_found = 'Компанії не знайдено';
            $labels->not_found_in_trash = 'У кошику компаній не знайдено';
            $labels->archives = 'Архів компаній';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "notes" (Нотатки)
 */
add_filter('post_type_labels_notes', 'wpml_translate_notes_labels');

function wpml_translate_notes_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Заметки';
            $labels->singular_name = 'Заметка';
            $labels->menu_name = 'Заметки';
            $labels->all_items = 'Все заметки';
            $labels->add_new = 'Добавить новую';
            $labels->add_new_item = 'Добавить новую заметку';
            $labels->edit_item = 'Редактировать заметку';
            $labels->new_item = 'Новая заметка';
            $labels->view_item = 'Просмотреть заметку';
            $labels->view_items = 'Просмотреть заметки';
            $labels->search_items = 'Искать заметки';
            $labels->not_found = 'Заметки не найдены';
            $labels->not_found_in_trash = 'В корзине заметок не найдено';
            $labels->archives = 'Архив заметок';
            break;

        case 'en':
            $labels->name = 'Notes';
            $labels->singular_name = 'Note';
            $labels->menu_name = 'Notes';
            $labels->all_items = 'All Notes';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Note';
            $labels->edit_item = 'Edit Note';
            $labels->new_item = 'New Note';
            $labels->view_item = 'View Note';
            $labels->view_items = 'View Notes';
            $labels->search_items = 'Search Notes';
            $labels->not_found = 'No notes found';
            $labels->not_found_in_trash = 'No notes found in Trash';
            $labels->archives = 'Note Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Нотатки';
            $labels->singular_name = 'Нотатка';
            $labels->menu_name = 'Нотатки';
            $labels->all_items = 'Усі нотатки';
            $labels->add_new = 'Додати нову';
            $labels->add_new_item = 'Додати нову нотатку';
            $labels->edit_item = 'Редагувати нотатку';
            $labels->new_item = 'Нова нотатка';
            $labels->view_item = 'Переглянути нотатку';
            $labels->view_items = 'Переглянути нотатки';
            $labels->search_items = 'Шукати нотатки';
            $labels->not_found = 'Нотатки не знайдено';
            $labels->not_found_in_trash = 'У кошику нотаток не знайдено';
            $labels->archives = 'Архів нотаток';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "special_projects" (Спецпроєкти)
 */
add_filter('post_type_labels_special_projects', 'wpml_translate_special_projects_labels');

function wpml_translate_special_projects_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Спецпроекты';
            $labels->singular_name = 'Спецпроект';
            $labels->menu_name = 'Спецпроекты';
            $labels->all_items = 'Все спецпроекты';
            $labels->add_new = 'Добавить новый';
            $labels->add_new_item = 'Добавить новый спецпроект';
            $labels->edit_item = 'Редактировать спецпроект';
            $labels->new_item = 'Новый спецпроект';
            $labels->view_item = 'Просмотреть спецпроект';
            $labels->view_items = 'Просмотреть спецпроекты';
            $labels->search_items = 'Искать спецпроекты';
            $labels->not_found = 'Спецпроекты не найдены';
            $labels->not_found_in_trash = 'В корзине спецпроектов не найдено';
            $labels->archives = 'Архив спецпроектов';
            break;

        case 'en':
            $labels->name = 'Special Projects';
            $labels->singular_name = 'Special Project';
            $labels->menu_name = 'Special Projects';
            $labels->all_items = 'All Projects';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Project';
            $labels->edit_item = 'Edit Project';
            $labels->new_item = 'New Project';
            $labels->view_item = 'View Project';
            $labels->view_items = 'View Projects';
            $labels->search_items = 'Search Projects';
            $labels->not_found = 'No projects found';
            $labels->not_found_in_trash = 'No projects found in Trash';
            $labels->archives = 'Special Project Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Спецпроєкти';
            $labels->singular_name = 'Спецпроєкт';
            $labels->menu_name = 'Спецпроєкти';
            $labels->all_items = 'Усі спецпроєкти';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий спецпроєкт';
            $labels->edit_item = 'Редагувати спецпроєкт';
            $labels->new_item = 'Новий спецпроєкт';
            $labels->view_item = 'Переглянути спецпроєкт';
            $labels->view_items = 'Переглянути спецпроєкти';
            $labels->search_items = 'Шукати спецпроєкти';
            $labels->not_found = 'Спецпроєкти не знайдено';
            $labels->not_found_in_trash = 'У кошику спецпроєктів не знайдено';
            $labels->archives = 'Архів спецпроєктів';
            break;
    }

    return $labels;
}

/**
 * Переклад назви типу постів "blogs" (Блоги)
 */
add_filter('post_type_labels_blogs', 'wpml_translate_blogs_labels');

function wpml_translate_blogs_labels($labels) {
    $current_lang = apply_filters('wpml_current_language', null);

    switch ($current_lang) {
        case 'ru':
            $labels->name = 'Блоги';
            $labels->singular_name = 'Блог';
            $labels->menu_name = 'Блоги';
            $labels->all_items = 'Все блоги';
            $labels->add_new = 'Добавить новый';
            $labels->add_new_item = 'Добавить новый блог';
            $labels->edit_item = 'Редактировать блог';
            $labels->new_item = 'Новый блог';
            $labels->view_item = 'Просмотреть блог';
            $labels->view_items = 'Просмотреть блоги';
            $labels->search_items = 'Искать блоги';
            $labels->not_found = 'Блоги не найдены';
            $labels->not_found_in_trash = 'В корзине блогов не найдено';
            $labels->archives = 'Архив блогов';
            break;

        case 'en':
            $labels->name = 'Blogs';
            $labels->singular_name = 'Blog';
            $labels->menu_name = 'Blogs';
            $labels->all_items = 'All Blogs';
            $labels->add_new = 'Add New';
            $labels->add_new_item = 'Add New Blog';
            $labels->edit_item = 'Edit Blog';
            $labels->new_item = 'New Blog';
            $labels->view_item = 'View Blog';
            $labels->view_items = 'View Blogs';
            $labels->search_items = 'Search Blogs';
            $labels->not_found = 'No blogs found';
            $labels->not_found_in_trash = 'No blogs found in Trash';
            $labels->archives = 'Blog Archives';
            break;

        case 'uk':
        default:
            $labels->name = 'Блоги';
            $labels->singular_name = 'Блог';
            $labels->menu_name = 'Блоги';
            $labels->all_items = 'Усі блоги';
            $labels->add_new = 'Додати новий';
            $labels->add_new_item = 'Додати новий блог';
            $labels->edit_item = 'Редагувати блог';
            $labels->new_item = 'Новий блог';
            $labels->view_item = 'Переглянути блог';
            $labels->view_items = 'Переглянути блоги';
            $labels->search_items = 'Шукати блоги';
            $labels->not_found = 'Блоги не знайдено';
            $labels->not_found_in_trash = 'У кошику блогів не знайдено';
            $labels->archives = 'Архів блогів';
            break;
    }

    return $labels;
}
