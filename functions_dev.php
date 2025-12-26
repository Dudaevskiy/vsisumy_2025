<?php
/**
 * DEV функції для розробки та тестування
 * Цей файл підключається з functions.php
 */

/**
 * Отримати ID постів з файлу посилань
 * Використання: додай ?get_ids_from_links=1 до URL сайту
 */
add_action('wp_loaded', 'dev_get_post_ids_from_links_file');
function dev_get_post_ids_from_links_file() {
    // Активується тільки з GET параметром
    if (!isset($_GET['get_ids_from_links']) || !current_user_can('manage_options')) {
        return;
    }

    $file_path = get_stylesheet_directory() . '/../DEV/Мови/UK_ALL_POSTS_MAIN.txt';

    if (!file_exists($file_path)) {
        dev_debug_output('Файл не знайдено: ' . $file_path);
        return;
    }

    $links = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (empty($links)) {
        dev_debug_output('Файл порожній або не вдалося прочитати');
        return;
    }

    $post_ids = [];
    $not_found = [];

    foreach ($links as $link) {
        $link = trim($link);
        if (empty($link)) continue;

        // Отримуємо ID поста за URL (slug)
        $post_id = url_to_postid($link);

        if ($post_id > 0) {
            $post_ids[] = $post_id;
        } else {
            // Спробуємо знайти по slug
            $slug = basename(untrailingslashit(parse_url($link, PHP_URL_PATH)));

            $post = get_page_by_path($slug, OBJECT, ['post', 'page', 'interview', 'announce', 'project']);

            if ($post) {
                $post_ids[] = $post->ID;
            } else {
                $not_found[] = $link;
            }
        }
    }

    // Результат
    $result = [
        'total_links' => count($links),
        'found_ids' => count($post_ids),
        'not_found_count' => count($not_found),
        'ids_string' => implode(', ', $post_ids),
        'ids_array' => $post_ids,
        'not_found_links' => $not_found,
    ];

    dev_debug_output($result);
}

/**
 * Універсальна функція для debug виводу
 * Використовує ddd() якщо Kint доступний, інакше var_dump + die
 */
function dev_debug_output($data) {
    if (function_exists('ddd')) {
        ddd($data);
    } else {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

/**
 * =====================================================
 * DEV CODE - Закоментований/тестовий код
 * =====================================================
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

/**
 * Исправление iframe при импорте, после бага с ними
 */
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
//}

// WPML Translation Connector перенесено до _WPML_FIXES.php
