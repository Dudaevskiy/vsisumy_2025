<?php

///***
// * WP All Import
// * ONLY_SDSTUDIO_WP
// */
//require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/SDStudio-WP_All_Import-Fixes_ONLY_SDSTUDIO_WP.php');

/***
 * Функція для отримання частини URL після домену. Ця функція повинна працювати з різними форматами URL та доменами.
 * Перетворює:
 * https://dom.biem.sumdu.edu.ua/uk/oholoshennya2/1047-osoblyvosti-promotsiyi-zdorovoho-rehionalnoho-rozvytku-dosvid-mista-sumy
 * На:
 * uk/oholoshennya2/1047-osoblyvosti-promotsiyi-zdorovoho-rehionalnoho-rozvytku-dosvid-mista-sumy
 *   У шаблоні імпорту WP All Import використовуємо:
 *   custom_permalink
 *   [sdstudio_get_url_path({webscraperstarturl[1]})]
 */
if (!function_exists('sdstudio_get_url_path')) {
    function sdstudio_get_url_path($url) {
        // Видаляємо протокол, якщо він є
        $url = preg_replace('#^https?://#', '', $url);

        // Розділяємо URL на частини
        $parts = explode('/', $url, 2);

        // Якщо є шлях після домену, обробляємо його
        if (isset($parts[1])) {
            $path = $parts[1];

            /***
             * Розкоментуй що б видалити дублюючий префікс мови з посилання
             */
            // Видаляємо 'en/' з початку шляху, якщо воно там є
            if (strpos($path, 'en/') === 0) {
                $path = substr($path, 3);
            }

            return $path;
        }

        // Якщо шляху немає, повертаємо порожній рядок
        return '';
    }
}

/***
 * Видалення першого зображення
 * Розкоментуй у sdstudio_remove_first_image:
 * $cleanedHtml = sdstudio_remove_first_image($cleanedHtml);
 */
if (!function_exists('sdstudio_remove_first_image')) {
    function sdstudio_remove_first_image($html) {
        // Основна логіка очищення HTML
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $html_ = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html_, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $images = $xpath->query('//img');
        if ($images->length > 0) {
            $firstImage = $images->item(0);
            $firstImage->parentNode->removeChild($firstImage);
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        // Отримання очищеного HTML
        $bodyNode = $dom->getElementsByTagName('body')->item(0);
        $cleanedHtml = '';
        foreach ($bodyNode->childNodes as $child) {
            $cleanedHtml .= $dom->saveHTML($child);
        }
        return $cleanedHtml;
    }
}

/***
 * Зміщюємо усі зображення поста, під контент у галерею
 * @param $post_id
 * @param $xml
 * @param $update
 * @return void
 */
function add_image_in_post_Import($post_id, $xml, $update) {
    // Отримуємо вміст поста
    $content = get_post_field('post_content', $post_id);

    // Шукаємо шорткод галереї
    if (preg_match('/\[gallery[^\]]*ids="([^"]*)"[^\]]*\]/', $content, $matches)) {
        $ids_string = $matches[1];
        $image_ids = explode(',', $ids_string);

        // Прикріплюємо кожне зображення до поста
        foreach ($image_ids as $image_id) {
            $image_id = trim($image_id);
            if (!empty($image_id)) {
                // Перевіряємо, чи існує зображення
                $image = get_post($image_id);
                if ($image && $image->post_type == 'attachment') {
                    // Прикріплюємо зображення до поста
                    wp_update_post(array(
                        'ID' => $image_id,
                        'post_parent' => $post_id
                    ));
                }
            }
        }
    }
}

require_once get_stylesheet_directory(__FILE__) . '/sds-child-theme-fixes/_Import_GOVNO_posts_ssu_old_sites/_class-download-remote-image_ONLY_SDSTUDIO_WP.php';

// Функція для отримання ID зображення за URL
if (!function_exists('get_attachment_id_by_url')) {
    function get_attachment_id_by_url($url)
    {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='old_img_url' AND meta_value='%s' LIMIT 1;", $url));
        return $attachment ? $attachment[0] : 0;
    }
}

/***
 * 📌📌📌 Переконуємось що імпорт у CSV ФАЙЛІ
 *
 * Функція sdstudio_clean_html призначена для очищення та оптимізації HTML-коду. Вона видаляє зайві атрибути,
 * об'єднує вкладені елементи та виконує загальне форматування HTML для покращення його структури та читабельності.
 * У шаблоні імпорту WP All Import використовуємо:
 * [sdstudio_clean_html({content[1]})]
 */
function sdstudio_clean_html($html) {
    // 📌 Вказуємо старий домен
    global $cleanedHtml;
    $DOMAIN_OLD = "https://inform.click";
    $remove_first_image = false;

    // 🔧 КРИТИЧНО ВАЖЛИВЕ ВИПРАВЛЕННЯ: Захищаємо зворотні слеші НА САМОМУ ПОЧАТКУ
    $uniqueMarker = "BACKSLASH_" . uniqid() . "_MARKER";

    // Захищаємо зворотні слеші в блоках коду перед будь-якою обробкою
    $html = preg_replace_callback(
        '/<(pre|code)([^>]*)>(.*?)<\/\1>/si',
        function($matches) use ($uniqueMarker) {
            $tag = $matches[1];
            $attributes = $matches[2];
            $content = $matches[3];

            // Замінюємо зворотні слеші на унікальний маркер
            $content = str_replace('\\', $uniqueMarker, $content);

            return "<{$tag}{$attributes}>{$content}</{$tag}>";
        },
        $html
    );

    // Завантаження у медіатеку, повертає ID зображення
    if (!function_exists('KM_Download_Remote_Image')) {
        function KM_Download_Remote_Image($url_external_image)
        {
            error_log('Trying to download image from URL: ' . $url_external_image);
            $headers = get_headers($url_external_image, 1);
            error_log('URL headers: ' . print_r($headers, true));

            $download_remote_image = new KM_Download_Remote_Image($url_external_image);
            $attachment_id = $download_remote_image->download();

            error_log('Attachment ID result: ' . ($attachment_id ? $attachment_id : 'false'));
            return $attachment_id;
        }
    }

    // 📌 НОВА ФУНКЦІЯ: Очищення проблемних CSS класів
    if (!function_exists('clean_problematic_css_classes')) {
        function clean_problematic_css_classes($node) {
            if ($node->hasAttributes()) {
                if ($node->hasAttribute('class')) {
                    $classes = $node->getAttribute('class');

                    // ❌ Видаляємо класи з "]:
                    if (strpos($classes, ']:') !== false) {
                        $node->removeAttribute('class');
                        error_log('Removed problematic class: ' . $classes);
                    } else {
                        // ✅ Очищаємо інші складні Tailwind класи
                        $cleaned_classes = clean_tailwind_classes($classes);
                        if (!empty($cleaned_classes)) {
                            $node->setAttribute('class', $cleaned_classes);
                        } else {
                            $node->removeAttribute('class');
                        }
                    }
                }
            }

            // Рекурсивно обробляємо дочірні елементи
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        clean_problematic_css_classes($child);
                    }
                }
            }
        }
    }

    // 📌 ФУНКЦІЯ: Очищення Tailwind класів
    if (!function_exists('clean_tailwind_classes')) {
        function clean_tailwind_classes($classes) {
            // Маппінг складних класів на прості WordPress класи
            $class_mapping = [
                // Текстові стилі
                'whitespace-normal break-words' => 'wp-block-paragraph',
                'text-xl font-bold text-text-100 mt-1 -mb-0.5' => 'wp-block-heading',
                'text-lg font-bold text-text-100 mt-1 -mb-1.5' => 'wp-block-subheading',

                // Таблиці
                'bg-bg-100 min-w-full border-separate border-spacing-0 text-sm leading-[1.88888] whitespace-normal' => 'wp-block-table',
                'border-b-border-100/50 border-b-[0.5px] text-left' => 'table-header',
                'border-t-border-100/50' => 'table-row',

                // Списки та блоки
                'list-disc space-y-1.5 pl-7' => 'wp-block-list',
                'border-border-200 border-l-4 pl-4' => 'wp-block-quote',

                // Кнопки і форми
                'font-styrene border-border-100/50 overflow-x-scroll w-full rounded border-[0.5px]' => 'code-block'
            ];

            // Шукаємо відповідність в маппінгу
            foreach ($class_mapping as $complex => $simple) {
                if (strpos($classes, $complex) !== false || $classes === $complex) {
                    return $simple;
                }
            }

            // Видаляємо всі Tailwind utility класи
            $classes_array = explode(' ', $classes);
            $cleaned_classes = [];

            foreach ($classes_array as $class) {
                $class = trim($class);

                // Пропускаємо порожні класи
                if (empty($class)) continue;

                // Зберігаємо WordPress класи
                if (strpos($class, 'wp-') === 0) {
                    $cleaned_classes[] = $class;
                    continue;
                }

                // Видаляємо Tailwind utility класи (містять дефіси, квадратні дужки, слеші)
                if (
                    strpos($class, '-') !== false ||
                    strpos($class, '[') !== false ||
                    strpos($class, ']') !== false ||
                    strpos($class, '/') !== false ||
                    strpos($class, ':') !== false ||
                    preg_match('/^(bg|text|border|p|m|w|h|flex|grid|space)-/', $class)
                ) {
                    continue; // Пропускаємо Tailwind класи
                }

                // Зберігаємо прості семантичні класи
                if (in_array($class, [
                    'content', 'header', 'footer', 'sidebar', 'main', 'article', 'section',
                    'table', 'list', 'quote', 'code', 'button', 'form', 'input'
                ])) {
                    $cleaned_classes[] = $class;
                }
            }

            return implode(' ', $cleaned_classes);
        }
    }

    // Функція для видалення атрибутів
    if (!function_exists('remove_attributes')) {
        function remove_attributes($node) {
            if ($node->hasAttributes()) {
                $attributes_to_keep = [
                    'href', 'src', 'alt', 'title', 'target', 'rel', 'type',
                    'name', 'value', 'placeholder', 'class' // Зберігаємо class для подальшої обробки
                ];

                // Спеціальна обробка для різних тегів
                if ($node->nodeName === 'img') {
                    $attributes_to_keep = ['src', 'alt', 'title', 'class'];
                } elseif (in_array($node->nodeName, ['pre', 'code'])) {
                    // Для блоків коду зберігаємо корисні атрибути
                    $attributes_to_keep = ['class', 'id', 'lang', 'language'];
                }

                $attributes_to_remove = [];
                foreach ($node->attributes as $attr) {
                    if (!in_array($attr->nodeName, $attributes_to_keep) &&
                        strpos($attr->nodeName, 'data-') !== 0) {
                        $attributes_to_remove[] = $attr->nodeName;
                    }
                }
                foreach ($attributes_to_remove as $attr) {
                    $node->removeAttribute($attr);
                }
            }

            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        remove_attributes($child);
                    }
                }
            }
        }
    }

    // Функція для об'єднання вкладених елементів
    if (!function_exists('merge_nested_elements')) {
        function merge_nested_elements($node) {
            // НЕ обробляємо блоки коду - залишаємо їх структуру недоторканою
            if (in_array(strtolower($node->nodeName), ['pre', 'code'])) {
                return;
            }

            $elements_to_merge = ['span', 'div', 'p'];
            if (in_array(strtolower($node->nodeName), $elements_to_merge)) {
                while ($node->firstChild && in_array(strtolower($node->firstChild->nodeName), $elements_to_merge)) {
                    $innerElement = $node->firstChild;
                    while ($innerElement->firstChild) {
                        $node->insertBefore($innerElement->firstChild, $innerElement);
                    }
                    $node->removeChild($innerElement);
                }
            }

            if ($node->hasChildNodes()) {
                $children = [];
                foreach ($node->childNodes as $child) {
                    $children[] = $child;
                }
                foreach ($children as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        merge_nested_elements($child);
                    }
                }
            }
        }
    }

    // 📌 ФУНКЦІЯ: Видалення порожніх елементів
    if (!function_exists('removeEmptyNodes')){
        function removeEmptyNodes($node) {
            // НЕ видаляємо блоки коду, навіть якщо вони здаються порожніми
            if (in_array(strtolower($node->nodeName), ['pre', 'code'])) {
                return;
            }

            if ($node->hasChildNodes()) {
                for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
                    $child = $node->childNodes->item($i);
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        removeEmptyNodes($child);
                    }
                }
            }

            // Розширений список елементів для перевірки на порожність
            if (in_array(strtolower($node->nodeName), ['p', 'span', 'div', 'a', 'section', 'article', 'header', 'footer', 'aside'])) {
                $nodeContent = trim($node->textContent);
                $nodeContent = str_replace(['&nbsp;', '&Acirc;&nbsp;'], '', $nodeContent);
                $nodeContent = preg_replace('/\s+/', '', $nodeContent);

                if ($nodeContent === '' && !$node->hasAttributes() && (!$node->hasChildNodes() || $node->childNodes->length === 0)) {
                    if ($node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }
    }

    // Основна логіка очищення HTML
    $html_ = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html_, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // 📌 ПОСЛІДОВНІСТЬ ОБРОБКИ:
    // 1. Спочатку видаляємо атрибути
    foreach ($xpath->query('//*') as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            remove_attributes($node);
        }
    }

    // 2. Потім очищаємо проблемні CSS класи
    foreach ($xpath->query('//*') as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            clean_problematic_css_classes($node);
        }
    }

    // 3. Об'єднуємо вкладені елементи
    foreach ($xpath->query('//*') as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            merge_nested_elements($node);
        }
    }

    // Масив для зберігання ID завантажених зображень
    $imgIdArray = [];
    $nodesToRemove = [];

    // 4. Обробляємо зображення та посилання
    foreach ($xpath->query('//*') as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            // НЕ ОБРОБЛЯЄМО блоки коду в DOM - залишаємо їх як є
            if (in_array(strtolower($node->nodeName), ['pre', 'code'])) {
                continue;
            }

            // Обробка посилань
            if ($node->nodeName === 'a') {
                $href = $node->getAttribute('href');
                $href = preg_replace('/\?.*/', '', $href);

                $hasImage = $node->getElementsByTagName('img')->length > 0;

                if (empty($href) || $href === '#') {
                    $nodesToRemove[] = $node;
                } else if ($hasImage) {
                    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $href)) {
                        if (strpos($href, 'http') !== 0) {
                            $href = $DOMAIN_OLD . $href;
                        }

                        $existing_attachment_id = get_attachment_id_by_url($href);

                        if ($existing_attachment_id) {
                            $image_url = wp_get_attachment_url($existing_attachment_id);
                            $node->setAttribute('href', $image_url);
                        } else {
                            $attachment_id = KM_Download_Remote_Image($href);
                            if ($attachment_id) {
                                update_post_meta($attachment_id, 'old_img_url', $href);
                                $imgIdArray[] = $attachment_id;
                                $image_url = wp_get_attachment_url($attachment_id);
                                $node->setAttribute('href', $image_url);
                            }
                        }

                        if (!$node->hasAttribute('data-rel')) {
                            $node->setAttribute('data-rel', 'lightbox');
                        }
                    }
                } else if ($node->textContent === '') {
                    $nodesToRemove[] = $node;
                } else {
                    if (strpos($href, 'http') !== 0) {
                        $href = $DOMAIN_OLD . $href;
                    }
                    $node->setAttribute('href', $href);
                }
            }

            // Завантаження зображень
            if ($node->nodeName === 'img') {
                $url = $node->getAttribute('src');
                $url = preg_replace('/\?.*/', '', $url);
                if (strpos($url, 'http') !== 0) {
                    $url = $DOMAIN_OLD . $url;
                }

                $existing_attachment_id = get_attachment_id_by_url($url);
                if ($existing_attachment_id) {
                    $image_url = wp_get_attachment_url($existing_attachment_id);
                    $node->setAttribute('src', $image_url);
                } else {
                    $attachment_id = KM_Download_Remote_Image($url);
                    if ($attachment_id) {
                        update_post_meta($attachment_id, 'old_img_url', $url);
                        $imgIdArray[] = $attachment_id;
                        $image_url = wp_get_attachment_url($attachment_id);
                        $node->setAttribute('src', $image_url);
                    }
                }
            }
        }
    }

    // 5. Видаляємо порожні елементи
    removeEmptyNodes($dom->documentElement);

    // 6. Видаляємо відмічені для видалення елементи
    foreach ($nodesToRemove as $node) {
        if ($node->parentNode !== null) {
            $node->parentNode->removeChild($node);
        }
    }

    // Отримання очищеного HTML
    $bodyNode = $dom->getElementsByTagName('body')->item(0);
    $cleanedHtml = '';
    if ($bodyNode) {
        foreach ($bodyNode->childNodes as $child) {
            $cleanedHtml .= $dom->saveHTML($child);
        }
    }

    // 📌 ДОДАТКОВА ОЧИСТКА РЕГУЛЯРНИМИ ВИРАЗАМИ
    // Видаляємо порожні теги
    $cleanedHtml = preg_replace('/<(pre|div|span|p|section|article)[^>]*>\s*<\/\1>/', '', $cleanedHtml);

    // Видаляємо залишки проблемних класів
    $cleanedHtml = preg_replace('/class="[^"]*\]:[^"]*"/', '', $cleanedHtml);

    // Очищення від зайвих тегів
    $cleanedHtml = str_replace(['<?xml encoding="UTF-8">', '</body></html>'], '', $cleanedHtml);

    // 🔧 КРИТИЧНО ВАЖЛИВЕ ВИПРАВЛЕННЯ: Відновлюємо зворотні слеші В КІНЦІ
    $cleanedHtml = preg_replace_callback(
        '/<(pre|code)([^>]*)>(.*?)<\/\1>/si',
        function($matches) use ($uniqueMarker) {
            $tag = $matches[1];
            $attributes = $matches[2];
            $content = $matches[3];

            // Відновлюємо зворотні слеші з унікального маркера
            $content = str_replace($uniqueMarker, '\\', $content);

            // 📌 ДОДАТКОВЕ ВИПРАВЛЕННЯ: Замінюємо подвійні повноширинні слеші на звичайні
            $content = str_replace(['＼&gt;', '＼＼', '＼'], ['&gt;', '\\', '\\'], $content);

            return "<{$tag}{$attributes}>{$content}</{$tag}>";
        },
        $cleanedHtml
    );

    // 📌 ДОДАТКОВЕ ВИПРАВЛЕННЯ: Використовуємо wp_slash() для захисту від stripslashes_deep()
    $cleanedHtml = wp_slash($cleanedHtml);

    // 🔧 ЗАГАЛЬНА ЗАМІНА: Замінюємо повноширинні слеші у всьому контенті
    $cleanedHtml = str_replace(['＼＼', '＼'], ['\\', '\\'], $cleanedHtml);

    // 📌 ЛОГУВАННЯ ДЛЯ ВІДЛАГОДЖЕННЯ
    error_log('Original HTML length: ' . strlen($html));
    error_log('Cleaned HTML length: ' . strlen($cleanedHtml));

    if (empty(trim(strip_tags($cleanedHtml)))) {
        error_log('WARNING: Cleaned HTML appears to be empty!');
        error_log('Original HTML preview: ' . substr($html, 0, 500));
    }

    return $cleanedHtml;
}

/**
 * Закріплюємо зображення за постом + встановлюємо обкладинку після імпорту
 * @param $post_id
 * @param $xml
 * @param $update
 * @return void
 */
function add_parrent_for_images_in_posts($post_id, $xml, $update) {
    if (isset($xml->imagefeatured)) {
        $featured_image_url = (string)$xml->imagefeatured;
        if (!empty($featured_image_url)) {
            // Видаляємо параметри з URL зображення
            $featured_image_url = preg_replace('/\?.*/', '', $featured_image_url);

            // Шукаємо зображення по збереженому оригінальному URL
            global $wpdb;
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'old_img_url' AND meta_value = %s LIMIT 1",
                $featured_image_url
            ));

            if ($existing_id) {
                // Якщо знайшли - використовуємо його
                set_post_thumbnail($post_id, $existing_id);
                $thumbnail_id = $existing_id;

                // Прикріплюємо тільки якщо зображення не має батьківського поста
                $current_parent = wp_get_post_parent_id($thumbnail_id);
                if (!$current_parent) {
                    wp_update_post(array(
                        'ID' => $thumbnail_id,
                        'post_parent' => $post_id
                    ));
                }
            } else {
                // Якщо не знайшли - завантажуємо
                $downloader = new KM_Download_Remote_Image($featured_image_url);
                $thumbnail_id = $downloader->download();
                if ($thumbnail_id) {
                    set_post_thumbnail($post_id, $thumbnail_id);
                }
                update_post_meta($thumbnail_id, 'old_img_url', $featured_image_url);
                // Прикріплюємо тільки якщо зображення не має батьківського поста
                $current_parent = wp_get_post_parent_id($thumbnail_id);
                if (!$current_parent) {
                    wp_update_post(array(
                        'ID' => $thumbnail_id,
                        'post_parent' => $post_id
                    ));
                }
            }
        }
    } else {
        $thumbnail_id = get_post_thumbnail_id($post_id);
    }

    // Отримуємо вміст поста
    $content = get_post_field('post_content', $post_id);

    $html = htmlspecialchars_decode(htmlentities($content, ENT_QUOTES, 'UTF-8', false));
    $html_ = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html_, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    foreach ($xpath->query('//*') as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            // Завантаження зображень та збереження їх ID
            if ($node->nodeName === 'img') {
                $url = $node->getAttribute('src');
                // Видаляємо параметри з URL зображення
                $url = preg_replace('/\?.*/', '', $url);

                // Функція для отримання ID зображення за URL
                if (!function_exists('get_attachment_id_by_url_in_post')) {
                    function get_attachment_id_by_url_in_post($url) {
                        // Очищаємо URL від параметрів запиту
                        $url = preg_replace('/\?.*/', '', $url);

                        // Отримуємо тільки шлях з URL
                        $upload_dir = wp_upload_dir();
                        $path = str_replace($upload_dir['baseurl'], '', $url);

                        global $wpdb;

                        // Шукаємо в базі даних
                        $attachment = $wpdb->get_col($wpdb->prepare(
                            "SELECT ID 
        FROM $wpdb->posts 
        WHERE guid LIKE %s 
        OR guid LIKE %s",
                            '%' . $wpdb->esc_like($path),
                            '%' . $wpdb->esc_like($url)
                        ));

                        // Повертаємо перший знайдений ID або null
                        return !empty($attachment[0]) ? $attachment[0] : null;
                    }
                }

                // Перевіряємо, чи вже існує зображення з таким URL
                $existing_attachment_id = get_attachment_id_by_url_in_post($url);
                if ($existing_attachment_id) {
                    $current_parent = wp_get_post_parent_id($existing_attachment_id);
                    // Прикріплюємо тільки якщо зображення не має батьківського поста
                    if (!$current_parent) {
                        wp_update_post(array(
                            'ID' => $existing_attachment_id,
                            'post_parent' => $post_id
                        ));
                    }
                }
            }
        }
    }

    // Ставимо категорію для обкладинки
    if ($thumbnail_id) {
        $category = get_post_meta($thumbnail_id, 'sdstudio_covers', true);
        if (!$category || $category !== 'IsPosterInPost') {

            // Перевіряємо чи успішно оновились мета-дані
            $check = update_post_meta($thumbnail_id, 'sdstudio_covers', 'IsPosterInPost');

            // Тільки потім оновлюємо пост якщо потрібно
            if ($check === 'IsPosterInPost') {
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'post_status' => 'publish'
                ));
            }
            $current_parent = wp_get_post_parent_id($thumbnail_id);
            // Прикріплюємо тільки якщо зображення не має батьківського поста
            if (!$current_parent) {
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'post_parent' => $post_id
                ));
            }
        }
    }

    /***
     * Пов'язуємо між собою переклади постів
     */
    if (!function_exists('sdstudio_import_translations_linker')){
        function sdstudio_import_translations_linker($post_id) {
            if (!$post_id || !get_post($post_id)) {
                return false;
            }

            $post_type = get_post_type($post_id);

            if ($post_type === 'post') {
                if (function_exists('icl_object_id')) {
                    global $sitepress;

                    $default_language = $sitepress->get_default_language();
                    $post_language = apply_filters('wpml_post_language_details', null, $post_id);

                    if ($post_language && $post_language['language_code'] !== $default_language) {
                        $current_post_meta = get_post_meta($post_id, 'sdstudio_get_original_post_link', true);

                        if ($current_post_meta) {
                            // Шукаємо пост на основній мові з таким самим значенням мета поля
                            $sitepress->switch_lang($default_language);

                            $args = array(
                                'post_type' => 'post',
                                'posts_per_page' => 1,
                                'meta_key' => 'sdstudio_get_original_post_link',
                                'meta_value' => $current_post_meta,
                                'post_status' => array('publish', 'future'), // додали future для запланованих постів
                                'suppress_filters' => false
                            );

                            $original_posts = get_posts($args);

                            if (!empty($original_posts)) {
                                $original_post_id = $original_posts[0]->ID;

                                // Отримуємо trid оригінального поста
                                global $wpdb;
                                $trid_query = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id={$original_post_id} AND element_type='post_post' LIMIT 1";
                                $trid = $wpdb->get_var($trid_query);

                                if ($trid) {
                                    // Пов'язуємо пости
                                    $sitepress->set_element_language_details(
                                        $post_id,
                                        'post_post',
                                        $trid,
                                        $post_language['language_code']
                                    );

                                    $sitepress->switch_lang($post_language['language_code']);
                                    return true;
                                }
                            }

                            $sitepress->switch_lang($post_language['language_code']);
                        }
                    }
                }
            }

            return false;
        }
    }
    sdstudio_import_translations_linker($post_id);
}

add_action('pmxi_saved_post', 'add_parrent_for_images_in_posts', 10, 3);