<?php
/**
 * VseSumy.com Drupal → WordPress Migration
 * WP All Import Custom Functions
 *
 * @package VseSumy_Migration
 * @version 1.0.25
 * @updated 2025-12-02 - Added 'Велика Економіка' (economics_company) support for sumy_company import
 * @updated 2025-12-01 - Fixed inline images in sdstudio_clean_html() to load from local files first
 * @updated 2025-12-01 - Added auto-assign "Аграрна Сумщина" tag + categories for company_articles
 * @updated 2025-11-30 - Added 'agro_sumy' post type import with auto-creation of sumy_company and company_articles
 * @updated 2025-11-29 - Added inline image processing for contest_entries body (gallery + media library)
 * @updated 2025-11-29 - Added 'views' meta field for contest_entries (in addition to views_count)
 * @updated 2025-11-29 - Fixed contest_entries XML iteration to use ->children() (not ->item) like poll_choices
 * @updated 2025-11-29 - Fixed contest_entries image import to use local files (image_filepath) instead of remote URLs
 * @updated 2025-11-29 - Added 'contest_entries' auto-creation from contests entries array (ONE-STEP import)
 * @updated 2025-11-28 - Changed poll_choices from pmxi_custom_field to pmxi_saved_post (XML-based processing)
 * @updated 2025-11-28 - Added 'poll' (опитування) post type support with JetEngine Repeater Field 'poll_choices'
 * @updated 2025-10-25 - Convert dates to ISO 8601 format without seconds (Y-m-d\TH:i) for JetEngine datetime fields
 * @updated 2025-10-25 - Replace "Guest" with "Гость" for Russian locale
 * @updated 2025-10-25 - Fixed XML children iteration for WP All Import dynamic tag names (item_0, item_1, item_2...)
 * @updated 2025-10-25 - Added pmxi_saved_post action to read questions from XML object directly
 * @updated 2025-10-25 - Changed to pmxi_custom_field filter for interview questions repeater (fixes empty field issue)
 * @updated 2025-10-24 - Added vsesumy_import_interview_questions() for JetEngine Repeater Field import
 * @updated 2025-10-24 - Added 'interview' post type support
 * @updated 2025-10-24 - Added 'sumy_company', 'company_articles', 'clubs' post type support
 * @updated 2025-10-18 - Added 'notes' post type support to all import functions
 * @updated 2025-10-18 - Added 'special_projects' post type support to all import functions
 * @updated 2025-10-18 - Added 'blogs' post type support to all import functions
 * @updated 2025-10-11 - Added default featured image fallback (ID: 1792)
 * @updated 2025-10-11 - Fixed priority and added constant for fallback image
 * @updated 2025-10-11 - Added "rn" artifacts cleanup from Drupal content
 */

// ============================================================================
// CONFIGURATION
// ============================================================================

/**
 * ID дефолтного зображення для постів без обкладинки
 * Змініть це значення якщо ID зображення інший 
 */
if (!defined('VSESUMY_DEFAULT_THUMBNAIL_ID')) {
    define('VSESUMY_DEFAULT_THUMBNAIL_ID', 1792);
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Очищає текст від емодзі маркерів 🪱 (замінює на переноси рядків)
 * Використовувати у WP All Import: [vsesumy_clean_body({body[1]})]
 */
if (!function_exists('vsesumy_clean_body')) {
    function vsesumy_clean_body($text) {
        if (empty($text)) {
            return '';
        }

        // Замінити емодзі маркер 🪱 на HTML переноси рядків
        $text = str_replace('🪱', '<br>', $text);
        $text = str_replace("\xF0\x9F\xAA\xB1", '<br>', $text); // UTF-8 hex
        $text = str_replace('&#129713;', '<br>', $text); // HTML entity
        $text = str_replace(html_entity_decode('&#129713;', ENT_QUOTES, 'UTF-8'), '<br>', $text);

        return $text;
    }
}

/**
 * Функція для отримання частини URL після домену
 * Перетворює: https://dom.biem.sumdu.edu.ua/uk/oholoshennya2/1047-...
 * На: uk/oholoshennya2/1047-...
 * Використання у WP All Import: [sdstudio_get_url_path({webscraperstarturl[1]})]
 */
if (!function_exists('sdstudio_get_url_path')) {
    function sdstudio_get_url_path($url) {
        $url = preg_replace('#^https?://#', '', $url);
        $parts = explode('/', $url, 2);

        if (isset($parts[1])) {
            $path = $parts[1];
            // Видаляємо 'en/' з початку шляху, якщо потрібно
            if (strpos($path, 'en/') === 0) {
                $path = substr($path, 3);
            }
            return $path;
        }

        return '';
    }
}

/**
 * Видаляє перше зображення з HTML контенту
 */
if (!function_exists('sdstudio_remove_first_image')) {
    function sdstudio_remove_first_image($html) {
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
        $cleanedHtml = '';
        foreach ($body->childNodes as $child) {
            $cleanedHtml .= $dom->saveHTML($child);
        }
        return $cleanedHtml;
    }
}

// ============================================================================
// MEDIA IMPORT FUNCTIONS
// ============================================================================

/**
 * Helper функція для завантаження ЛОКАЛЬНОГО файлу в Media Library
 *
 * @param string $local_file_path Абсолютний шлях до локального файлу
 * @param int $post_id ID поста до якого прикріплюється
 * @param string $media_type Тип: 'image', 'video', 'audio', 'file'
 * @return int|false Attachment ID або false при помилці
 */
function vsesumy_sideload_local_file($local_file_path, $post_id, $media_type = 'image') {
    if (!file_exists($local_file_path)) {
        return false;
    }

    // Дедуплікація по хешу
    $file_hash = md5_file($local_file_path);
    global $wpdb;
    $existing_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='drupal_file_hash' AND meta_value='%s' LIMIT 1",
        $file_hash
    ));

    if ($existing_id) {
        return intval($existing_id);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Визначити MIME тип
    $mime_type = mime_content_type($local_file_path);
    $filename = basename($local_file_path);

    // Fallback для MIME типу
    if (!$mime_type || $mime_type === 'application/octet-stream') {
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime_types_map = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf', 'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'mp4' => 'video/mp4', 'mp3' => 'audio/mpeg', 'wav' => 'audio/wav',
        ];
        if (isset($mime_types_map[$file_extension])) {
            $mime_type = $mime_types_map[$file_extension];
        }
    }

    // Організація по датах поста
    $post = get_post($post_id);
    $post_date = $post ? $post->post_date : current_time('mysql');

    $upload_dir_filter = function($dirs) use ($post_date) {
        $date = date('Y/m', strtotime($post_date));
        $dirs['path'] = $dirs['basedir'] . '/' . $date;
        $dirs['url'] = $dirs['baseurl'] . '/' . $date;
        $dirs['subdir'] = '/' . $date;
        return $dirs;
    };

    add_filter('upload_dir', $upload_dir_filter);
    $upload_dir = wp_upload_dir();
    remove_filter('upload_dir', $upload_dir_filter);

    if (!file_exists($upload_dir['path'])) {
        wp_mkdir_p($upload_dir['path']);
    }

    // Унікальне ім'я файлу
    $new_file_path = $upload_dir['path'] . '/' . $filename;
    $counter = 1;
    $file_info = pathinfo($filename);
    $base_name = $file_info['filename'];
    $extension = isset($file_info['extension']) ? '.' . $file_info['extension'] : '';

    while (file_exists($new_file_path)) {
        $new_filename = $base_name . '-' . $counter . $extension;
        $new_file_path = $upload_dir['path'] . '/' . $new_filename;
        $counter++;
    }

    // Копіювати файл
    if (!copy($local_file_path, $new_file_path)) {
        return false;
    }

    // Створити attachment
    $attachment = array(
        'guid' => $upload_dir['url'] . '/' . basename($new_file_path),
        'post_mime_type' => $mime_type,
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($new_file_path)),
        'post_content' => '',
        'post_status' => 'inherit',
        'post_parent' => $post_id
    );

    $attachment_id = wp_insert_attachment($attachment, $new_file_path, $post_id);

    if (is_wp_error($attachment_id)) {
        return false;
    }

    // Зберегти metadata
    update_post_meta($attachment_id, 'drupal_file_hash', $file_hash);
    update_post_meta($attachment_id, 'drupal_original_path', $local_file_path);

    // Згенерувати thumbnails для зображень
    if ($media_type === 'image' && strpos($mime_type, 'image/') === 0) {
        $metadata = wp_generate_attachment_metadata($attachment_id, $new_file_path);
        if ($metadata && !empty($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        } else {
            // Fallback metadata
            $image_info = getimagesize($new_file_path);
            if ($image_info) {
                $metadata = [
                    'width' => $image_info[0],
                    'height' => $image_info[1],
                    'file' => basename($new_file_path),
                ];
                wp_update_attachment_metadata($attachment_id, $metadata);
            }
        }
    }

    return $attachment_id;
}

/**
 * Основна функція імпорту медіафайлів з Drupal
 * Викликається через WP All Import hook: pmxi_saved_post
 *
 * Підтримувані типи контенту:
 * - 'post' (новини)
 * - 'blogs' (блоги)
 * - 'special_projects' (спецпроекти)
 * - 'notes' (нотатки)
 * - 'sumy_company' (компанії Sumy Market)
 * - 'company_articles' (статті компаній)
 * - 'clubs' (клуби)
 * - 'interview' (інтерв'ю)
 * - 'polls' (опитування)
 *
 * @param int $post_id ID поста
 * @param object $xml XML об'єкт з даними WP All Import
 * @param bool $update Чи це оновлення існуючого поста
 */
function vsesumy_add_media_from_csv($post_id, $xml, $update) {
    error_log("DEBUG MEDIA: vsesumy_add_media_from_csv called for post_id={$post_id}");

    // Перевірка типу поста - підтримка для всіх імпортованих типів
    $post_type = get_post_type($post_id);
    $supported_types = ['post', 'blogs', 'special_projects', 'notes', 'sumy_company', 'company_articles', 'clubs', 'interview', 'polls', 'velyka_ekonomika'];
    if (!in_array($post_type, $supported_types)) {
        return;
    }

    // Helper: парсинг URL з різних форматів (CSV pipe-separated, JSON array, string)
    $parse_urls = function($data) {
        if (empty($data)) return [];

        if (is_array($data)) {
            return array_filter(array_map('trim', $data));
        }

        // SimpleXMLElement → string
        if (is_object($data) && get_class($data) === 'SimpleXMLElement') {
            $data = (string) $data;
        }

        if (is_string($data)) {
            $data = trim($data);
            if ($data === '') return [];

            // Спробувати різні роздільники
            if (strpos($data, ', ') !== false) {
                return array_filter(array_map('trim', explode(', ', $data)));
            }
            if (strpos($data, ',') !== false) {
                return array_filter(array_map('trim', explode(',', $data)));
            }
            if (strpos($data, '|') !== false) {
                return array_filter(array_map('trim', explode('|', $data)));
            }

            return [$data];
        }

        return [];
    };

    // Helper: конвертація Drupal шляху в локальний абсолютний шлях
    $drupal_to_local_path = function($url) {
        $url = trim($url);
        if (empty($url)) return false;

        // Видалити домени
        $url = str_replace(['https://vsisumy.com/', 'http://vsisumy.com/',
                           'https://dev.rama.com.ua/', 'http://dev.rama.com.ua/'], '', $url);
        $url = ltrim($url, '/');

        // Перевірити чи це Drupal файл
        if (strpos($url, 'sites/default/files/') === 0) {
            $local_path = ABSPATH . $url;
            if (file_exists($local_path)) {
                return $local_path;
            }
        }

        return false;
    };

    // Змінні для збору медіа
    $content = get_post_field('post_content', $post_id);
    $media_content = '';
    $all_image_ids = [];
    $featured_image_id = null;

    // 1️⃣ FEATURED IMAGE
    if (!empty($xml->featured_image_url)) {
        $local_path = $drupal_to_local_path($xml->featured_image_url);

        if ($local_path && file_exists($local_path)) {
            $featured_image_id = vsesumy_sideload_local_file($local_path, $post_id, 'image');

            if ($featured_image_id) {
                wp_update_post(['ID' => $featured_image_id, 'post_parent' => $post_id]);
                $all_image_ids[] = $featured_image_id;
            }
        }
    }

    // 2️⃣ АУДІО (TODO: node references)
    if (!empty($xml->audio_urls)) {
        $audio_urls = $parse_urls($xml->audio_urls);
        foreach ($audio_urls as $audio_url_raw) {
            // TODO: Аудіо має формат "audio_nid_279" - потрібна окрема обробка
            continue;
        }
    }

    // 3️⃣ ВІДЕО
    if (!empty($xml->videos_urls)) {
        $video_urls = $parse_urls($xml->videos_urls);
        foreach ($video_urls as $video_url_raw) {
            $local_path = $drupal_to_local_path($video_url_raw);

            if ($local_path && file_exists($local_path)) {
                $attachment_id = vsesumy_sideload_local_file($local_path, $post_id, 'video');
                if ($attachment_id) {
                    $video_file_url = wp_get_attachment_url($attachment_id);
                    $media_content .= "\n[video src=\"{$video_file_url}\"]\n";
                }
            }
        }
    }

    // 4️⃣ ФАЙЛИ
    if (!empty($xml->files_urls)) {
        $file_urls = $parse_urls($xml->files_urls);
        if (count($file_urls) > 0) {
            $media_content .= "\n<h4>Прикріплені файли:</h4>\n<ul class=\"attached-files\">\n";

            foreach ($file_urls as $file_url_raw) {
                $local_path = $drupal_to_local_path($file_url_raw);

                if ($local_path && file_exists($local_path)) {
                    $attachment_id = vsesumy_sideload_local_file($local_path, $post_id, 'file');
                    if ($attachment_id) {
                        $file_url_wp = wp_get_attachment_url($attachment_id);
                        $file_name = basename($file_url_wp);
                        $media_content .= "<li><a href=\"{$file_url_wp}\" target=\"_blank\">{$file_name}</a></li>\n";
                    }
                }
            }

            $media_content .= "</ul>\n";
        }
    }

    // 5️⃣ ФОТО (photos_urls)
    if (!empty($xml->photos_urls)) {
        $photo_urls = $parse_urls($xml->photos_urls);

        foreach ($photo_urls as $photo_url_raw) {
            $local_path = $drupal_to_local_path($photo_url_raw);

            if ($local_path && file_exists($local_path)) {
                $attachment_id = vsesumy_sideload_local_file($local_path, $post_id, 'image');

                if ($attachment_id) {
                    wp_update_post(['ID' => $attachment_id, 'post_parent' => $post_id]);
                    $all_image_ids[] = $attachment_id;
                }
            }
        }
    }

    // 6️⃣ Видалити старий Drupal gallery shortcode
    if (preg_match('/\[gallery[^\]]*ids="([^"]*)"[^\]]*\]/', $content, $matches)) {
        $content = str_replace($matches[0], '', $content);
    }

    // 7️⃣ Встановити FEATURED IMAGE
    if ($featured_image_id) {
        set_post_thumbnail($post_id, $featured_image_id);
    } elseif (count($all_image_ids) > 0) {
        set_post_thumbnail($post_id, $all_image_ids[0]);
    } else {
        // Fallback: використати дефолтне зображення
        set_post_thumbnail($post_id, VSESUMY_DEFAULT_THUMBNAIL_ID);
    }

    // 8️⃣ Створити галерею (тільки якщо > 1 зображення)
    if (count($all_image_ids) > 1) {
        $media_content .= "\n" . '[gallery size="medium" ids="' . implode(',', $all_image_ids) . '" columns="4" link="file"]' . "\n";
    }

    // 9️⃣ Оновити контент
    $content = preg_replace('/<div id="old_post">(.*?)<\/div>/s', '$1', $content);

    if (!empty($media_content)) {
        $combined_content = $content . "\n<br>\n" . $media_content;
        $updated_content = '<div id="old_post">' . $combined_content . '</div>';
    } else {
        $updated_content = '<div id="old_post">' . $content . '</div>';
    }

    wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content
    ]);
}

add_action('pmxi_saved_post', 'vsesumy_add_media_from_csv', 10, 3);

/**
 * Видаляє емодзі маркер 🪱 та артефакти "rn" з контенту після імпорту
 *
 * Підтримувані типи контенту:
 * - 'post' (новини)
 * - 'blogs' (блоги)
 * - 'special_projects' (спецпроекти)
 * - 'notes' (нотатки)
 * - 'sumy_company' (компанії Sumy Market)
 * - 'company_articles' (статті компаній)
 * - 'clubs' (клуби)
 * - 'interview' (інтерв'ю)
 * - 'poll' (опитування)
 */
function vsesumy_remove_emoji_marker($post_id, $xml, $update) {
    $post_type = get_post_type($post_id);
    $supported_types = ['post', 'blogs', 'special_projects', 'notes', 'sumy_company', 'company_articles', 'clubs', 'interview', 'poll', 'velyka_ekonomika'];
    if (!in_array($post_type, $supported_types)) {
        return;
    }

    $content = get_post_field('post_content', $post_id);
    $excerpt = get_post_field('post_excerpt', $post_id);
    $title = get_post_field('post_title', $post_id);

    $clean_text = function($text, $is_html = false) {
        if (empty($text)) return $text;

        // Видалити емодзі маркер 🪱
        $text = str_replace('🪱', '', $text);
        $text = str_replace("\xF0\x9F\xAA\xB1", '', $text);
        $text = str_replace('&#129713;', '', $text);
        $text = str_replace(html_entity_decode('&#129713;', ENT_QUOTES, 'UTF-8'), '', $text);

        // Видалити артефакти "rn", "r", "n" з Drupal (залишки від \r\n)
        $text = str_replace('rnhttp', 'http', $text); // "rnhttp" → "http"
        $text = str_replace('rnhttps', 'https', $text); // "rnhttps" → "https"
        $text = preg_replace('/\brn\b/', '', $text); // "rn" як окреме слово
        $text = preg_replace('/^rn\s*/m', '', $text); // "rn" на початку рядка
        $text = preg_replace('/\s*rn$/m', '', $text); // "rn" в кінці рядка
        $text = preg_replace('/>\s*rn\s*</', '><', $text); // "rn" між HTML тегами

        // Видалити окремі "r" та "n" (залишки від \r\n)
        $text = preg_replace('/\br\b/', '', $text); // окрема "r"
        $text = preg_replace('/\bn\b/', '', $text); // окрема "n"
        $text = preg_replace('/^[rn]\s*/m', '', $text); // "r" або "n" на початку рядка
        $text = preg_replace('/\s*[rn]$/m', '', $text); // "r" або "n" в кінці рядка
        $text = preg_replace('/>\s*[rn]\s*</', '><', $text); // "r" або "n" між HTML тегами

        // Очистити зайві пробіли (тільки для не-HTML або між тегами)
        if (!$is_html) {
            $text = preg_replace('/\s+/', ' ', $text);
        } else {
            // Для HTML: очистити пробіли між тегами, але зберегти структуру
            $text = preg_replace('/>\s+</', '><', $text);
        }

        $text = trim($text);

        return $text;
    };

    wp_update_post([
        'ID' => $post_id,
        'post_title' => $clean_text($title, false),
        'post_content' => $clean_text($content, true),
        'post_excerpt' => $clean_text($excerpt, false)
    ]);
}

add_action('pmxi_saved_post', 'vsesumy_remove_emoji_marker', 30, 3);

// ============================================================================
// GALLERY POST-PROCESSING
// ============================================================================

/**
 * Обробка галереї після імпорту:
 * - Прикріплює зображення до поста
 * - Встановлює перше зображення як featured (якщо немає)
 * - Видаляє перше зображення з галереї
 * - Встановлює fallback обкладинку (ID: 1792) якщо немає зображень
 *
 * Підтримувані типи контенту:
 * - 'post' (новини)
 * - 'blogs' (блоги)
 * - 'special_projects' (спецпроекти)
 * - 'notes' (нотатки)
 * - 'sumy_company' (компанії Sumy Market)
 * - 'company_articles' (статті компаній)
 * - 'clubs' (клуби)
 * - 'interview' (інтерв'ю)
 * - 'poll' (опитування)
 */
function add_image_in_post_Import($post_id, $xml, $update) {
    // Перевірка типу поста - підтримка для всіх імпортованих типів
    $post_type = get_post_type($post_id);
    $supported_types = ['post', 'blogs', 'special_projects', 'notes', 'sumy_company', 'company_articles', 'clubs', 'interview', 'poll', 'velyka_ekonomika'];
    if (!in_array($post_type, $supported_types)) {
        return;
    }

    $content = get_post_field('post_content', $post_id);
    $updated_content = $content;
    $first_image_id = null;

    // Шукаємо шорткод галереї
    if (preg_match('/\[gallery[^\]]*ids="([^"]*)"[^\]]*\]/', $content, $matches)) {
        $gallery_shortcode = $matches[0];
        $ids_string = $matches[1];
        $image_ids = explode(',', $ids_string);

        // Прикріплюємо всі зображення до поста
        foreach ($image_ids as $image_id) {
            $image_id = trim($image_id);
            if (!empty($image_id)) {
                $image = get_post($image_id);
                if ($image && $image->post_type == 'attachment') {
                    wp_update_post([
                        'ID' => $image_id,
                        'post_parent' => $post_id
                    ]);
                }
            }
        }

        // Встановити featured image якщо немає
        $has_thumbnail = has_post_thumbnail($post_id);

        if (!$has_thumbnail && count($image_ids) > 0) {
            foreach ($image_ids as $key => $image_id) {
                $image_id = trim($image_id);
                if (!empty($image_id)) {
                    $image = get_post($image_id);
                    if ($image && $image->post_type == 'attachment') {
                        $first_image_id = $image_id;
                        unset($image_ids[$key]);
                        break;
                    }
                }
            }

            if ($first_image_id) {
                $new_ids_string = implode(',', $image_ids);

                if (!empty($new_ids_string)) {
                    $new_gallery_shortcode = str_replace('ids="' . $ids_string . '"', 'ids="' . $new_ids_string . '"', $gallery_shortcode);
                    $updated_content = str_replace($gallery_shortcode, $new_gallery_shortcode, $content);
                } else {
                    $updated_content = str_replace($gallery_shortcode, '', $content);
                }

                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $updated_content
                ]);

                set_post_thumbnail($post_id, $first_image_id);
            }
        }
    }

    // ВАЖЛИВО: Встановити fallback обкладинку якщо немає featured image
    // Виконується ПІСЛЯ обробки галереї і для постів БЕЗ галереї
    if (!has_post_thumbnail($post_id)) {
        set_post_thumbnail($post_id, VSESUMY_DEFAULT_THUMBNAIL_ID);
    }
}

// Пріоритет 99 - виконується останньою, після всіх інших функцій
// Гарантує що fallback встановиться якщо жодна інша функція не встановила featured image
add_action('pmxi_saved_post', 'add_image_in_post_Import', 99, 3);

// ============================================================================
// INTERVIEW REPEATER FIELD IMPORT
// ============================================================================

/**
 * PHP Function для WP All Import: конвертує XML questions у JSON string
 *
 * Використання у WP All Import Custom Field Value:
 * [vsesumy_serialize_questions({questions[1]})]
 *
 * @param string $xml_string XML з питаннями (передається через XPath)
 * @return string JSON string з масивом питань
 */
function vsesumy_serialize_questions($xml_string) {
    // Якщо передали порожнє значення
    if (empty($xml_string)) {
        return '';
    }

    // Якщо це вже JSON string - повернути як є
    if (is_string($xml_string) && (strpos($xml_string, '[') === 0 || strpos($xml_string, '{') === 0)) {
        return $xml_string;
    }

    // Спробувати парсити як XML
    $prev_errors = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml_string);
    libxml_use_internal_errors($prev_errors);

    if ($xml === false) {
        // Якщо не XML - можливо це вже JSON або масив елементів
        return '';
    }

    // Конвертувати XML у масив
    $questions = array();
    foreach ($xml as $item) {
        $question = array(
            'nid' => (string)$item->nid,
            'text' => (string)$item->text,
            'author_display_name' => (string)$item->author_display_name,
            'created' => (string)$item->created
        );

        // Додати відповідь якщо є
        if (isset($item->answer) && !empty($item->answer)) {
            $question['answer'] = array(
                'nid' => (string)$item->answer->nid,
                'text' => (string)$item->answer->text,
                'author_display_name' => (string)$item->answer->author_display_name,
                'created' => (string)$item->answer->created
            );
        }

        $questions[] = $question;
    }

    return json_encode($questions);
}

/**
 * Фільтр для Custom Field 'questions' - конвертує JSON у формат JetEngine Repeater
 *
 * ВАЖЛИВО: Використовує pmxi_custom_field фільтр замість pmxi_saved_post екшну!
 * Це дозволяє перехопити значення ДО того як воно буде збережене у базу.
 *
 * Обробляє JSON з питаннями та відповідями і конвертує у формат JetEngine:
 * - Масив з ключами item-0, item-1, item-2...
 * - Кожен item містить: question_text, question_author, question_date,
 *   answer_text, answer_author, answer_date
 *
 * Структура вхідного JSON:
 * [
 *   {
 *     "text": "питання",
 *     "author_display_name": "автор",
 *     "created": "2010-05-27T17:44:22",
 *     "answer": {
 *       "text": "відповідь",
 *       "author_display_name": "автор",
 *       "created": "2010-05-27T17:45:31"
 *     }
 *   }
 * ]
 *
 * @param mixed $value Значення Custom Field (JSON string)
 * @param int $post_id ID поста
 * @param string $field_name Назва поля ('questions')
 * @return mixed Масив для JetEngine repeater або оригінальне значення
 */
function vsesumy_import_interview_questions_filter($value, $post_id, $field_name) {
    // Тільки для поля 'questions'
    if ($field_name !== 'questions') {
        return $value;
    }

    // Тільки для типу 'interview'
    $post_type = get_post_type($post_id);
    if ($post_type !== 'interview') {
        return $value;
    }

    // Якщо пусте значення - повернути як є
    if (empty($value)) {
        return $value;
    }

    // Якщо це вже масив - повернути як є
    if (is_array($value)) {
        return $value;
    }

    // Декодувати JSON
    $questions_array = json_decode($value, true);

    if (!is_array($questions_array) || empty($questions_array)) {
        return $value;
    }

    // Конвертувати у формат JetEngine Repeater (item-0, item-1, ...)
    $repeater_data = array();
    $item_index = 0;

    foreach ($questions_array as $q) {
        // Конвертувати дату питання у Unix timestamp
        $question_timestamp = '';
        if (!empty($q['created'])) {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', (string)$q['created']);
            if ($dt) {
                $question_timestamp = $dt->getTimestamp();
            }
        }

        // Замінити "Guest" на "Гость"
        $question_author = $q['author_display_name'] ?? 'Guest';
        if ($question_author === 'Guest') {
            $question_author = 'Гость';
        }

        $row = array(
            'question_text' => $q['text'] ?? '',
            'question_author' => $question_author,
            'question_date' => $question_date,
            'answer_text' => '',
            'answer_author' => '',
            'answer_date' => ''
        );

        // Додати відповідь якщо є
        if (isset($q['answer']) && is_array($q['answer'])) {
            // Конвертувати дату відповіді у формат Y-m-d\TH:i (без секунд)
            $answer_date = '';
            if (!empty($q['answer']['created'])) {
                $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', (string)$q['answer']['created']);
                if ($dt) {
                    $answer_date = $dt->format('Y-m-d\TH:i');
                }
            }

            // Замінити "Guest" на "Гость"
            $answer_author = $q['answer']['author_display_name'] ?? 'Guest';
            if ($answer_author === 'Guest') {
                $answer_author = 'Гость';
            }

            $row['answer_text'] = $q['answer']['text'] ?? '';
            $row['answer_author'] = $answer_author;
            $row['answer_date'] = $answer_date;
        }

        $repeater_data["item-{$item_index}"] = $row;
        $item_index++;
    }

    if (!empty($repeater_data)) {
        return $repeater_data;
    }

    return $value;
}

// Фільтр pmxi_custom_field спрацьовує ПІД ЧАС обробки Custom Fields
// Дозволяє перехопити і модифікувати значення ДО збереження у базу
// Пріоритет 10 - стандартний
add_filter('pmxi_custom_field', 'vsesumy_import_interview_questions_filter', 10, 3);

/**
 * Екшн для обробки questions після імпорту - читає з XML і конвертує у repeater
 */
function vsesumy_import_interview_questions_from_xml($post_id, $xml, $update) {
    // Тільки для типу 'interview'
    $post_type = get_post_type($post_id);
    if ($post_type !== 'interview') {
        return;
    }

    // Перевірити чи є questions у XML
    if (!isset($xml->questions)) {
        return;
    }

    // Конвертувати XML questions у масив
    $questions_array = array();

    // WP All Import створює XML де кожен елемент масиву стає окремим тегом (item_0, item_1, item_2...)
    // Ітеруємось по всіх дочірніх елементах questions
    foreach ($xml->questions->children() as $tag_name => $items) {
        // $items може бути масивом (якщо є кілька елементів з однаковим тегом)
        // Обробляємо кожен елемент
        if (!is_array($items)) {
            $items = array($items);
        }

        foreach ($items as $item) {
            $question = array(
                'text' => (string)$item->text,
                'author_display_name' => (string)$item->author_display_name,
                'created' => (string)$item->created
            );

            // Додати відповідь якщо є
            if (isset($item->answer) && !empty($item->answer)) {
                $question['answer'] = array(
                    'text' => (string)$item->answer->text,
                    'author_display_name' => (string)$item->answer->author_display_name,
                    'created' => (string)$item->answer->created
                );
            }

            $questions_array[] = $question;
        }
    }

    if (empty($questions_array)) {
        return;
    }

    // Конвертувати у формат JetEngine Repeater (item-0, item-1, ...)
    $repeater_data = array();
    $item_index = 0;

    foreach ($questions_array as $q) {
        // Конвертувати дату питання у формат Y-m-d\TH:i (без секунд)
        $question_date = '';
        if (!empty($q['created'])) {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', (string)$q['created']);
            if ($dt) {
                $question_date = $dt->format('Y-m-d\TH:i');
            }
        }

        // Замінити "Guest" на "Гость"
        $question_author = $q['author_display_name'] ?? 'Guest';
        if ($question_author === 'Guest') {
            $question_author = 'Гость';
        }

        $row = array(
            'question_text' => $q['text'] ?? '',
            'question_author' => $question_author,
            'question_date' => $question_date,
            'answer_text' => '',
            'answer_author' => '',
            'answer_date' => ''
        );

        // Додати відповідь якщо є
        if (isset($q['answer']) && is_array($q['answer'])) {
            // Конвертувати дату відповіді у формат Y-m-d\TH:i (без секунд)
            $answer_date = '';
            if (!empty($q['answer']['created'])) {
                $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', (string)$q['answer']['created']);
                if ($dt) {
                    $answer_date = $dt->format('Y-m-d\TH:i');
                }
            }

            // Замінити "Guest" на "Гость"
            $answer_author = $q['answer']['author_display_name'] ?? 'Guest';
            if ($answer_author === 'Guest') {
                $answer_author = 'Гость';
            }

            $row['answer_text'] = $q['answer']['text'] ?? '';
            $row['answer_author'] = $answer_author;
            $row['answer_date'] = $answer_date;
        }

        $repeater_data["item-{$item_index}"] = $row;
        $item_index++;
    }

    // Зберегти repeater data
    if (!empty($repeater_data)) {
        update_post_meta($post_id, 'questions', $repeater_data);
    }
}

add_action('pmxi_saved_post', 'vsesumy_import_interview_questions_from_xml', 10, 3);

// ============================================================================
// POLL CHOICES REPEATER FIELD IMPORT
// ============================================================================

/**
 * Обробка JetEngine Repeater Field 'poll_choices' для імпорту опитувань
 *
 * ВАЖЛИВО: Використовує pmxi_saved_post action для читання XML об'єкту безпосередньо!
 * Це надійніший підхід ніж pmxi_custom_field фільтр.
 *
 * Читає XML структуру choices і конвертує у формат JetEngine Repeater:
 * - Масив з ключами item-0, item-1, item-2...
 * - Кожен item містить: choice_label, choice_votes, choice_percentage, choice_drupal_cid
 *
 * Структура XML (з JSON):
 * <choices>
 *   <item_0>
 *     <cid>12</cid>
 *     <label>Варіант 1</label>
 *     <votes_count>123</votes_count>
 *     <percentage>15.5</percentage>
 *   </item_0>
 * </choices>
 *
 * @param int $post_id ID створеного/оновленого поста
 * @param object $xml XML об'єкт з даними WP All Import
 * @param bool $update Чи це оновлення існуючого поста
 */
function vsesumy_import_poll_choices_from_xml($post_id, $xml, $update) {
    // DEBUG: Логування початку функції
    error_log("POLL DEBUG: Function called for post_id={$post_id}");

    // Тільки для типу 'polls' (JetEngine створює CPT з множиною)
    $post_type = get_post_type($post_id);
    error_log("POLL DEBUG: post_type={$post_type}");

    if ($post_type !== 'polls') {
        error_log("POLL DEBUG: Skipping - not a polls post type");
        return;
    }

    // Перевірити що XML об'єкт існує
    if (empty($xml) || !is_object($xml)) {
        error_log("POLL DEBUG: XML object is empty or not an object");
        return;
    }

    error_log("POLL DEBUG: XML object exists");

    // Отримати choices з XML
    if (!isset($xml->choices) || empty($xml->choices)) {
        error_log("POLL DEBUG: choices field is missing or empty in XML");
        error_log("POLL DEBUG: XML structure: " . print_r($xml, true));
        return;
    }

    error_log("POLL DEBUG: choices field found in XML");

    // Масив для збереження варіантів відповідей
    $choices_array = array();

    // Перебираємо всі дочірні елементи choices (item_0, item_1, item_2...)
    foreach ($xml->choices->children() as $item_name => $item) {
        // $item_name може бути: item_0, item_1, item_2...

        $choice = array(
            'cid' => (string)$item->cid,
            'label' => (string)$item->label,
            'weight' => (string)$item->weight,
            'writein' => (string)$item->writein,
            'votes_count' => (int)$item->votes_count,
            'percentage' => (float)$item->percentage
        );

        $choices_array[] = $choice;
    }

    if (empty($choices_array)) {
        return;
    }

    // Конвертувати у формат JetEngine Repeater (item-0, item-1, ...)
    $repeater_data = array();
    $item_index = 0;

    foreach ($choices_array as $choice) {
        $repeater_data["item-{$item_index}"] = array(
            'choice_label' => $choice['label'] ?? '',
            'choice_votes' => intval($choice['votes_count'] ?? 0),
            'choice_percentage' => floatval($choice['percentage'] ?? 0.0),
            'choice_drupal_cid' => $choice['cid'] ?? ''
        );
        $item_index++;
    }

    // Зберегти repeater data
    if (!empty($repeater_data)) {
        error_log("POLL DEBUG: Saving " . count($repeater_data) . " choices to poll_choices meta field");
        error_log("POLL DEBUG: Repeater data: " . print_r($repeater_data, true));
        update_post_meta($post_id, 'poll_choices', $repeater_data);
        error_log("POLL DEBUG: Successfully saved poll_choices");
    } else {
        error_log("POLL DEBUG: repeater_data is empty - nothing to save");
    }
}

add_action('pmxi_saved_post', 'vsesumy_import_poll_choices_from_xml', 10, 3);

// ============================================================================
// CONTEST ENTRIES - АВТОМАТИЧНЕ СТВОРЕННЯ З МАСИВУ ENTRIES
// ============================================================================

/**
 * Автоматично створює contest_entries з масиву entries при імпорті contests
 *
 * Працює при імпорті contests:
 * 1. WP All Import створює пост типу 'contests'
 * 2. Ця функція читає масив entries з XML
 * 3. Для кожного entry створює окремий пост типу 'contest_entries'
 * 4. Встановлює parent_contest для кожного entry
 * 5. Додає ID створених entries до масиву contest_entries батьківського конкурсу
 * 6. Завантажує Featured Image з локального файлу (використовує image_filepath)
 *
 * ВАЖЛИВО: Використовує локальні файли з кореня сайту (sites/default/files/...)
 * Файли копіюються в wp-content/uploads/ та додаються до медіабібліотеки.
 *
 * @param int $post_id ID створеного/оновленого contest
 * @param object $xml XML об'єкт з даними WP All Import
 * @param bool $update Чи це оновлення існуючого поста
 */
function vsesumy_create_contest_entries_from_xml($post_id, $xml, $update) {
    // Тільки для типу 'contests'
    $post_type = get_post_type($post_id);

    if ($post_type !== 'contests') {
        return;
    }

    error_log("CONTEST {$post_id}: Початок обробки entries");

    // Отримати масив entries з XML
    if (!isset($xml->entries) || empty($xml->entries)) {
        error_log("CONTEST {$post_id}: entries не знайдено у XML");
        error_log("CONTEST {$post_id}: XML structure: " . print_r($xml, true));
        return;
    }

    error_log("CONTEST {$post_id}: entries знайдено у XML");

    $created_entry_ids = array();
    $entries_count = 0;

    // Перебрати всі entries через children() (як у poll_choices)
    foreach ($xml->entries->children() as $entry_name => $entry) {
        $entries_count++;

        // Отримати дані entry
        $entry_nid = isset($entry->nid) ? (string)$entry->nid : '';
        $entry_title = isset($entry->title) ? (string)$entry->title : 'Без назви';
        $entry_body = isset($entry->body) ? (string)$entry->body : '';
        $entry_teaser = isset($entry->teaser) ? (string)$entry->teaser : '';
        $entry_created = isset($entry->created) ? (string)$entry->created : current_time('mysql');
        $entry_modified = isset($entry->modified) ? (string)$entry->modified : current_time('mysql');
        $entry_author = isset($entry->author_name) ? (string)$entry->author_name : 'admin';
        $entry_votes = isset($entry->votes_count) ? (int)$entry->votes_count : 0;
        $entry_views = isset($entry->views_count) ? (int)$entry->views_count : 0;
        $entry_weight = isset($entry->weight) ? (int)$entry->weight : 0;
        $entry_image_filepath = isset($entry->image_filepath) ? (string)$entry->image_filepath : '';
        $entry_image_fid = isset($entry->image_fid) ? (string)$entry->image_fid : '';
        $entry_drupal_url = isset($entry->drupal_url) ? (string)$entry->drupal_url : '';

        // Перевірити чи вже існує entry з таким drupal_nid
        $existing_entry = get_posts(array(
            'post_type' => 'contest_entries',
            'meta_key' => 'drupal_nid',
            'meta_value' => $entry_nid,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));

        if (!empty($existing_entry)) {
            $entry_id = $existing_entry[0]->ID;
            error_log("CONTEST {$post_id}: Entry {$entry_nid} вже існує (ID: {$entry_id})");
        } else {
            // Знайти автора по імені
            $author = get_user_by('login', $entry_author);
            $author_id = $author ? $author->ID : 1;

            // Створити новий пост contest_entry
            $entry_id = wp_insert_post(array(
                'post_type' => 'contest_entries',
                'post_title' => $entry_title,
                'post_content' => $entry_body,
                'post_excerpt' => $entry_teaser,
                'post_status' => 'publish',
                'post_author' => $author_id,
                'post_date' => $entry_created,
                'post_modified' => $entry_modified,
                'comment_status' => 'open'
            ));

            if (is_wp_error($entry_id)) {
                error_log("CONTEST {$post_id}: Помилка створення entry {$entry_nid}: " . $entry_id->get_error_message());
                continue;
            }

            error_log("CONTEST {$post_id}: Створено entry {$entry_nid} → ID {$entry_id}");
        }

        // Обробити зображення всередині body та створити галерею
        // Використовуємо sdstudio_clean_html() яка:
        // 1. Парсить HTML
        // 2. Знаходить зображення (в лайтбоксах та звичайні <img>)
        // 3. Завантажує їх у медіатеку
        // 4. Створює шорткод галереї [gallery ids="1,2,3"]
        // 5. Видаляє оригінальні зображення з контенту
        if (!empty($entry_body)) {
            $processed_body = sdstudio_clean_html($entry_body);

            // Оновити post_content з обробленим HTML та галереєю
            if ($processed_body !== $entry_body) {
                wp_update_post(array(
                    'ID' => $entry_id,
                    'post_content' => $processed_body
                ));
                error_log("CONTEST {$post_id}: Оброблено HTML та inline зображення для entry {$entry_nid}");
            }
        }

        // Встановити meta поля для entry
        update_post_meta($entry_id, 'parent_contest', $post_id);
        update_post_meta($entry_id, 'votes_count', $entry_votes);
        update_post_meta($entry_id, 'views_count', $entry_views);
        update_post_meta($entry_id, 'views', $entry_views);  // Додатково 'views' для сумісності
        update_post_meta($entry_id, 'weight', $entry_weight);
        update_post_meta($entry_id, 'image_fid', $entry_image_fid);
        update_post_meta($entry_id, 'drupal_nid', $entry_nid);
        update_post_meta($entry_id, 'drupal_url', $entry_drupal_url);

        // Завантажити Featured Image з локального файлу
        if (!empty($entry_image_filepath)) {
            // Перевірити чи вже є прикріплене зображення
            $existing_thumbnail = get_post_thumbnail_id($entry_id);

            if (empty($existing_thumbnail)) {
                // Повний шлях до локального файлу
                $local_file_path = ABSPATH . $entry_image_filepath;

                // Перевірити що файл існує
                if (file_exists($local_file_path)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');

                    // Отримати інформацію про файл
                    $filename = basename($local_file_path);
                    $filetype = wp_check_filetype($filename, null);

                    // Підготувати upload
                    $upload_dir = wp_upload_dir();
                    $new_filename = wp_unique_filename($upload_dir['path'], $filename);
                    $new_file_path = $upload_dir['path'] . '/' . $new_filename;

                    // Копіювати файл в wp-content/uploads
                    if (copy($local_file_path, $new_file_path)) {
                        // Підготувати attachment data
                        $attachment = array(
                            'guid' => $upload_dir['url'] . '/' . $new_filename,
                            'post_mime_type' => $filetype['type'],
                            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        // Вставити attachment в медіабібліотеку
                        $attach_id = wp_insert_attachment($attachment, $new_file_path, $entry_id);

                        // Згенерувати метадані для attachment
                        $attach_data = wp_generate_attachment_metadata($attach_id, $new_file_path);
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        // Встановити як Featured Image
                        set_post_thumbnail($entry_id, $attach_id);

                        error_log("CONTEST {$post_id}: Завантажено зображення для entry {$entry_nid} з {$entry_image_filepath}");
                    } else {
                        error_log("CONTEST {$post_id}: Помилка копіювання файлу {$local_file_path} для entry {$entry_nid}");
                    }
                } else {
                    error_log("CONTEST {$post_id}: Файл не знайдено {$local_file_path} для entry {$entry_nid}");
                }
            }
        }

        // Додати ID entry до масиву
        $created_entry_ids[] = $entry_id;
    }

    // Оновити масив contest_entries у батьківському contests
    if (!empty($created_entry_ids)) {
        update_post_meta($post_id, 'contest_entries', $created_entry_ids);
        error_log("CONTEST {$post_id}: Додано {$entries_count} entries до масиву contest_entries");
    }
}

add_action('pmxi_saved_post', 'vsesumy_create_contest_entries_from_xml', 10, 3);

// ============================================================================
// HTML CLEANUP (для інших імпортів)
// ============================================================================

require_once get_stylesheet_directory(__FILE__) . '/sds-child-theme-fixes/_Import_GOVNO_posts_ssu_old_sites/_class-download-remote-image.php';

/**
 * Функція sdstudio_clean_html для очищення HTML при імпорті з інших сайтів
 * Використання: [sdstudio_clean_html({content[1]})]
 */
function sdstudio_clean_html($html) {
    error_log("=== sdstudio_clean_html() ВИКЛИКАНО ===");
    error_log("HTML length: " . strlen($html));

    // Перевірка чи є зображення в HTML
    if (preg_match_all('/src=["\']([^"\']+)["\']/', $html, $matches)) {
        error_log("Знайдено зображень: " . count($matches[1]));
        foreach ($matches[1] as $src) {
            error_log("  - IMG SRC: " . $src);
        }
    } else {
        error_log("Зображень НЕ знайдено в HTML");
    }

    $DOMAIN_OLD = "http://dev.rama.com.ua";  // Drupal файли на dev сервері
    $remove_first_image = false;

    // Helper: завантажити зображення з локального файлу або URL
    if (!function_exists('sdstudio_load_image_local_or_remote')) {
        function sdstudio_load_image_local_or_remote($url) {
            // Витягти шлях з URL (sites/default/files/...)
            $local_path = '';
            if (preg_match('#(sites/default/files/.+)$#', $url, $matches)) {
                $local_path = $matches[1];
            } elseif (strpos($url, '/sites/default/files/') !== false) {
                $local_path = preg_replace('#^.*?(/sites/default/files/.+)$#', '$1', $url);
                $local_path = ltrim($local_path, '/');
            }

            // Спробувати локальний файл
            if (!empty($local_path)) {
                $full_local_path = ABSPATH . $local_path;
                if (file_exists($full_local_path)) {
                    // Використати vsesumy_sideload_local_file для завантаження
                    if (function_exists('vsesumy_sideload_local_file')) {
                        $attachment_id = vsesumy_sideload_local_file($full_local_path, 0, 'image');
                        if ($attachment_id && !is_wp_error($attachment_id)) {
                            error_log("sdstudio_clean_html: Завантажено локальний файл: {$local_path} -> attachment {$attachment_id}");
                            return $attachment_id;
                        }
                    }
                }
            }

            // Якщо локальний не знайдено - спробувати віддалено
            if (strpos($url, 'http') === 0 && class_exists('KM_Download_Remote_Image')) {
                $download_remote_image = new KM_Download_Remote_Image($url);
                $attachment_id = $download_remote_image->download();
                if ($attachment_id) {
                    error_log("sdstudio_clean_html: Завантажено віддалений файл: {$url} -> attachment {$attachment_id}");
                    return $attachment_id;
                }
            }

            error_log("sdstudio_clean_html: НЕ вдалося завантажити: {$url}");
            return 0;
        }
    }

    // Helper функції
    if (!function_exists('get_attachment_id_by_url')) {
        function get_attachment_id_by_url($url) {
            global $wpdb;
            $attachment = $wpdb->get_col($wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='old_img_url' AND meta_value='%s' LIMIT 1;",
                $url
            ));
            return $attachment ? $attachment[0] : 0;
        }
    }

    if (!function_exists('KM_Download_Remote_Image')) {
        function KM_Download_Remote_Image($url_external_image) {
            $download_remote_image = new KM_Download_Remote_Image($url_external_image);
            $attachment_id = $download_remote_image->download();
            return $attachment_id;
        }
    }

    if (!function_exists('remove_attributes')) {
        function remove_attributes($node) {
            if ($node->hasAttributes()) {
                $attributes_to_keep = ['href', 'src', 'alt', 'title', 'target', 'rel', 'type', 'name', 'value', 'placeholder', 'data-*'];

                if ($node->nodeName === 'img') {
                    $attributes_to_keep = ['src', 'alt'];
                }

                $attributes_to_remove = [];
                foreach ($node->attributes as $attr) {
                    if (!in_array($attr->nodeName, $attributes_to_keep) && strpos($attr->nodeName, 'data-') !== 0) {
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

    if (!function_exists('merge_nested_elements')) {
        function merge_nested_elements($node) {
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

    if (!function_exists('removeEmptyNodes')) {
        function removeEmptyNodes($node) {
            if ($node->hasChildNodes()) {
                for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
                    $child = $node->childNodes->item($i);
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        removeEmptyNodes($child);
                    }
                }
            }

            if (in_array(strtolower($node->nodeName), ['p', 'span', 'div', 'a'])) {
                $nodeContent = trim($node->textContent);
                $nodeContent = str_replace('&nbsp;', '', $nodeContent);
                $nodeContent = preg_replace('/\s+/', '', $nodeContent);

                if ($nodeContent === '' && !$node->hasAttributes() && (!$node->hasChildNodes() || $node->childNodes->length === 0)) {
                    $node->parentNode->removeChild($node);
                }
            }
        }
    }

    if (!function_exists('clean_url_params')) {
        function clean_url_params($url) {
            // Видалити escaped лапки з JSON (\" -> пусто)
            $url = str_replace('\"', '', $url);
            $url = str_replace("\'", '', $url);
            // Видалити query string
            return preg_replace('/\?.*/', '', $url);
        }
    }

    if (!function_exists('is_image_url')) {
        function is_image_url($url) {
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            foreach ($image_extensions as $ext) {
                if (preg_match('/\.' . $ext . '(?:\?|$)/i', $url)) {
                    return true;
                }
            }
            return false;
        }
    }

    // Основна логіка
    $html = htmlspecialchars_decode(htmlentities($html, ENT_QUOTES, 'UTF-8', false));
    $html_ = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html_, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    if ($remove_first_image) {
        $images = $xpath->query('//img');
        if ($images->length > 0) {
            $firstImage = $images->item(0);
            $firstImage->parentNode->removeChild($firstImage);
        }
    }

    $imgIdArray = [];
    $nodesToRemove = [];

    // Обробка лайтбоксів
    foreach ($xpath->query('//a') as $aNode) {
        $href = $aNode->getAttribute('href');
        $rel = $aNode->getAttribute('rel');
        $href_clean = clean_url_params($href);

        $is_lightbox = strpos($rel, 'lightbox') !== false;
        $href_is_image = is_image_url($href_clean);

        if ($is_lightbox || $href_is_image) {
            $contains_img = false;
            foreach ($aNode->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'img') {
                    $contains_img = true;
                    break;
                }
            }

            if ($contains_img) {
                if (strpos($href_clean, 'http') !== 0) {
                    $href_clean = $DOMAIN_OLD . $href_clean;
                }

                $existing_attachment_id = get_attachment_id_by_url($href_clean);

                if ($existing_attachment_id) {
                    $imgIdArray[] = $existing_attachment_id;
                } else {
                    // Використовуємо нову функцію для локальних файлів
                    $attachment_id = sdstudio_load_image_local_or_remote($href_clean);
                    if ($attachment_id) {
                        update_post_meta($attachment_id, 'old_img_url', $href_clean);
                        $imgIdArray[] = $attachment_id;
                    }
                }

                $nodesToRemove[] = $aNode;
            }
        }
    }

    // Загальна обробка
    foreach ($xpath->query('//*') as $node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            merge_nested_elements($node);
            remove_attributes($node);

            if ($node->nodeName === 'a' && !in_array($node, $nodesToRemove)) {
                $href = $node->getAttribute('href');
                $href = clean_url_params($href);
                if (empty($href) || $href === '#' || $node->textContent === '') {
                    $nodesToRemove[] = $node;
                } else {
                    if (strpos($href, 'http') !== 0) {
                        $href = $DOMAIN_OLD . $href;
                    }
                    $node->setAttribute('href', $href);
                }
            }

            if (in_array(strtolower($node->nodeName), ['p', 'span', 'div', 'a'])) {
                $isEmpty = true;
                $nodeContent = trim($node->textContent);
                $nodeContent = str_replace('&nbsp;', '', $nodeContent);
                $nodeContent = preg_replace('/\s+/', '', $nodeContent);

                if ($nodeContent !== '' || $node->hasChildNodes()) {
                    foreach ($node->childNodes as $child) {
                        if ($child->nodeType !== XML_TEXT_NODE || trim(preg_replace('/\s+/', '', $child->textContent)) !== '') {
                            $isEmpty = false;
                            break;
                        }
                    }
                }

                if ($isEmpty && !$node->hasAttributes()) {
                    $nodesToRemove[] = $node;
                    continue;
                }
            }

            if ($node->nodeName === 'img' && $node->parentNode->nodeName !== 'a') {
                $url = $node->getAttribute('src');
                $url = clean_url_params($url);
                if (strpos($url, 'http') !== 0) {
                    $url = $DOMAIN_OLD . $url;
                }

                $existing_attachment_id = get_attachment_id_by_url($url);

                if ($existing_attachment_id) {
                    $imgIdArray[] = $existing_attachment_id;
                } else {
                    // Використовуємо нову функцію для локальних файлів
                    $attachment_id = sdstudio_load_image_local_or_remote($url);
                    if ($attachment_id) {
                        update_post_meta($attachment_id, 'old_img_url', $url);
                        $imgIdArray[] = $attachment_id;
                    }
                }

                $nodesToRemove[] = $node;
            }
        }
    }

    removeEmptyNodes($dom->documentElement);

    foreach ($nodesToRemove as $node) {
        if ($node->parentNode !== null) {
            $node->parentNode->removeChild($node);
        }
    }

    $bodyNode = $dom->getElementsByTagName('body')->item(0);
    $cleanedHtml = '';
    foreach ($bodyNode->childNodes as $child) {
        $cleanedHtml .= $dom->saveHTML($child);
    }

    if (count($imgIdArray) > 0) {
        $gallery = '[gallery size="medium" ids="' . implode(',', $imgIdArray) . '" columns="4" link="file"]';
        $cleanedHtml .= "\n" . $gallery;
    }

    $cleanedHtml = '<div id="old_post">' . $cleanedHtml . '</div>';

    return $cleanedHtml;
}

// ============================================================================
// AGRO_SUMY IMPORT (Аграрна Сумщина)
// ============================================================================

/**
 * Імпорт Agro2012 контенту
 *
 * Логіка:
 * - about_agro2012 (21) → створює пост в agro_sumy (основний контент)
 * - predpriyatie_agro2012 (19) → створює sumy_company + зв'язує з agro_sumy
 * - article_firma_agro2012 (43) → створює company_articles + зв'язує з компанією
 * - razdel_predpriyatia_agro2012 (24) → створює company_articles + зв'язує з компанією
 *
 * ВАЖЛИВО:
 * - Featured image завантажується з локальної папки sites/default/files/
 * - Inline images обробляються через sdstudio_clean_html() для створення галерей
 * - views = перегляди (JetEngine мета поле)
 * - Зв'язки через JetEngine posts мета поля
 *
 * @param int $post_id ID створеного/оновленого agro_sumy
 * @param object $xml XML об'єкт з даними WP All Import
 * @param bool $update Чи це оновлення існуючого поста
 */
function vsesumy_process_agro2012_import($post_id, $xml, $update) {
    $post_type = get_post_type($post_id);

    // ============================================================
    // ІМПОРТ 1: about_agro2012.json → agro_sumy
    // ============================================================
    if ($post_type === 'agro_sumy') {
        $drupal_type = isset($xml->type) ? (string)$xml->type : '';
        $drupal_nid = isset($xml->nid) ? (string)$xml->nid : '';

        error_log("AGRO2012 agro_sumy {$post_id}: Обробка типу '{$drupal_type}', nid: {$drupal_nid}");

        update_post_meta($post_id, 'drupal_nid', $drupal_nid);
        update_post_meta($post_id, 'drupal_type', $drupal_type);

        // Обробити як статтю (featured image, inline images → gallery)
        vsesumy_process_agro_article($post_id, $xml);
        return;
    }

    // ============================================================
    // ІМПОРТ 2: companies_with_articles.json → sumy_company
    // Підтримує: predpriyatie_agro2012 (Агро Суми), economics_company (Велика Економіка)
    // ============================================================
    if ($post_type === 'sumy_company') {
        $drupal_type = isset($xml->type) ? (string)$xml->type : '';
        $drupal_nid = isset($xml->nid) ? (string)$xml->nid : '';

        // Підтримувані типи компаній
        $supported_company_types = array('predpriyatie_agro2012', 'economics_company');
        if (!in_array($drupal_type, $supported_company_types)) {
            return;
        }

        // Визначити категорію компанії та тег для записів
        $is_velyka_ekonomika = ($drupal_type === 'economics_company');
        $company_category = $is_velyka_ekonomika ? 'Велика Економіка' : 'Аграрна Сумщина';
        $log_prefix = $is_velyka_ekonomika ? 'VELYKA_EKONOMIKA' : 'AGRO2012';

        error_log("{$log_prefix} sumy_company {$post_id}: Обробка компанії nid: {$drupal_nid}");

        // Зберегти мета дані компанії
        update_post_meta($post_id, 'drupal_nid', $drupal_nid);
        update_post_meta($post_id, 'drupal_type', $drupal_type);
        update_post_meta($post_id, 'old_url', isset($xml->drupal_url) ? (string)$xml->drupal_url : '');
        update_post_meta($post_id, 'views', isset($xml->views_count) ? (int)$xml->views_count : 0);
        update_post_meta($post_id, 'author_name', isset($xml->author_name) ? (string)$xml->author_name : '');
        update_post_meta($post_id, 'author_display_name', isset($xml->author_display_name) ? (string)$xml->author_display_name : '');
        update_post_meta($post_id, 'author_email', isset($xml->author_email) ? (string)$xml->author_email : '');
        update_post_meta($post_id, 'comment_count', isset($xml->comment_count) ? (int)$xml->comment_count : 0);

        // Призначити категорію компанії (company_categories taxonomy)
        wp_set_object_terms($post_id, $company_category, 'company_categories', true);
        error_log("{$log_prefix} sumy_company {$post_id}: Призначено категорію '{$company_category}'");

        // Featured image
        $featured_image_url = isset($xml->featured_image_url) ? (string)$xml->featured_image_url : '';
        if (!empty($featured_image_url)) {
            vsesumy_set_agro_featured_image($post_id, $featured_image_url);
        }

        // Обробити body - inline зображення
        $body = get_post_field('post_content', $post_id);
        if (!empty($body)) {
            $processed_body = sdstudio_clean_html($body);
            if ($processed_body !== $body) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $processed_body
                ));
            }
        }

        // Створити company_articles з вкладених масивів
        $created_article_ids = array();

        // Обробити company_articles
        if (isset($xml->company_articles) && !empty($xml->company_articles)) {
            foreach ($xml->company_articles->children() as $article) {
                $article_id = vsesumy_create_company_article_from_xml($post_id, $article, $company_category);
                if ($article_id) {
                    $created_article_ids[] = $article_id;
                }
            }
        }

        // Обробити company_razdels (agro2012) або company_rubrics (velyka_ekonomika) як company_articles
        if (isset($xml->company_razdels) && !empty($xml->company_razdels)) {
            foreach ($xml->company_razdels->children() as $razdel) {
                $article_id = vsesumy_create_company_article_from_xml($post_id, $razdel, $company_category);
                if ($article_id) {
                    $created_article_ids[] = $article_id;
                }
            }
        }

        // company_rubrics для velyka_ekonomika
        if (isset($xml->company_rubrics) && !empty($xml->company_rubrics)) {
            foreach ($xml->company_rubrics->children() as $rubric) {
                $article_id = vsesumy_create_company_article_from_xml($post_id, $rubric, $company_category);
                if ($article_id) {
                    $created_article_ids[] = $article_id;
                }
            }
        }

        error_log("{$log_prefix} sumy_company {$post_id}: Створено " . count($created_article_ids) . " записів компанії");
        return;
    }
}

/**
 * Створення запису company_articles з XML елемента (вкладеного масиву)
 *
 * @param int $company_id ID батьківської компанії (sumy_company)
 * @param object $xml XML об'єкт з даними запису
 * @param string $company_category Категорія компанії для тегування ('Аграрна Сумщина' або 'Велика Економіка')
 */
function vsesumy_create_company_article_from_xml($company_id, $xml, $company_category = 'Аграрна Сумщина') {
    $drupal_nid = isset($xml->nid) ? (string)$xml->nid : '';
    $title = isset($xml->title) ? (string)$xml->title : 'Без назви';
    $log_prefix = ($company_category === 'Велика Економіка') ? 'VELYKA_EKONOMIKA' : 'AGRO2012';

    // Перевірити чи вже існує запис з таким drupal_nid
    $existing = get_posts(array(
        'post_type' => 'company_articles',
        'meta_key' => 'drupal_nid',
        'meta_value' => $drupal_nid,
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));

    if (!empty($existing)) {
        $article_id = $existing[0]->ID;
        error_log("{$log_prefix}: Запис компанії {$drupal_nid} вже існує (ID: {$article_id})");
        return $article_id;
    }

    // Створити новий запис компанії
    $body = isset($xml->body) ? (string)$xml->body : '';
    $teaser = isset($xml->teaser) ? (string)$xml->teaser : '';
    $created = isset($xml->created) ? (string)$xml->created : current_time('mysql');
    $modified = isset($xml->modified) ? (string)$xml->modified : current_time('mysql');

    $article_id = wp_insert_post(array(
        'post_type' => 'company_articles',
        'post_title' => $title,
        'post_content' => $body,
        'post_excerpt' => $teaser,
        'post_status' => 'publish',
        'post_date' => $created,
        'post_modified' => $modified
    ));

    if (is_wp_error($article_id)) {
        error_log("{$log_prefix}: Помилка створення запису компанії {$drupal_nid}: " . $article_id->get_error_message());
        return false;
    }

    // Обробити body - inline зображення
    if (!empty($body)) {
        $processed_body = sdstudio_clean_html($body);
        if ($processed_body !== $body) {
            wp_update_post(array(
                'ID' => $article_id,
                'post_content' => $processed_body
            ));
        }
    }

    // Featured image
    $featured_image_url = isset($xml->featured_image_url) ? (string)$xml->featured_image_url : '';
    if (!empty($featured_image_url)) {
        vsesumy_set_agro_featured_image($article_id, $featured_image_url);
    }

    // Зв'язок з компанією через JetEngine posts мета поле
    update_post_meta($article_id, 'company_post', array($company_id));

    // Мета поля
    $drupal_type = isset($xml->type) ? (string)$xml->type : '';
    update_post_meta($article_id, 'drupal_nid', $drupal_nid);
    update_post_meta($article_id, 'drupal_type', $drupal_type);
    update_post_meta($article_id, 'old_url', isset($xml->drupal_url) ? (string)$xml->drupal_url : '');
    update_post_meta($article_id, 'views', isset($xml->views_count) ? (int)$xml->views_count : 0);
    update_post_meta($article_id, 'author_name', isset($xml->author_name) ? (string)$xml->author_name : '');
    update_post_meta($article_id, 'author_display_name', isset($xml->author_display_name) ? (string)$xml->author_display_name : '');
    update_post_meta($article_id, 'author_email', isset($xml->author_email) ? (string)$xml->author_email : '');
    update_post_meta($article_id, 'comment_count', isset($xml->comment_count) ? (int)$xml->comment_count : 0);

    // Призначити тег категорії для company_articles
    // ('Аграрна Сумщина' для agro2012, 'Велика Економіка' для economics_company)
    wp_set_object_terms($article_id, $company_category, 'post_tag', true);

    // Також додати категорії з JSON якщо є
    $categories_str = isset($xml->categories) ? (string)$xml->categories : '';
    if (!empty($categories_str)) {
        $tags = array_map('trim', explode(',', $categories_str));
        $tags = array_filter($tags); // Видалити порожні
        if (!empty($tags)) {
            wp_set_object_terms($article_id, $tags, 'post_tag', true);
        }
    }

    error_log("{$log_prefix}: Створено запис компанії '{$title}' (ID: {$article_id}), категорія: {$company_category}");

    return $article_id;
}

/**
 * Обробка основної статті agro_sumy (about_agro2012)
 */
function vsesumy_process_agro_article($post_id, $xml) {
    // Featured image
    $featured_image_url = isset($xml->featured_image_url) ? (string)$xml->featured_image_url : '';
    if (!empty($featured_image_url)) {
        vsesumy_set_agro_featured_image($post_id, $featured_image_url);
    }

    // Обробити body - замінити inline зображення на галерею
    $body = get_post_field('post_content', $post_id);
    if (!empty($body)) {
        $processed_body = sdstudio_clean_html($body);
        if ($processed_body !== $body) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $processed_body
            ));
            error_log("AGRO2012 {$post_id}: Оброблено inline зображення, створено галерею");
        }
    }

    // Мета поля
    $views = isset($xml->views_count) ? (int)$xml->views_count : 0;
    $votes = isset($xml->votes_count) ? (int)$xml->votes_count : 0;
    $comments = isset($xml->comment_count) ? (int)$xml->comment_count : 0;
    $old_url = isset($xml->drupal_url) ? (string)$xml->drupal_url : '';

    update_post_meta($post_id, 'views', $views);
    update_post_meta($post_id, 'votes_count', $votes);
    update_post_meta($post_id, 'comment_count', $comments);
    update_post_meta($post_id, 'old_url', $old_url);

    // Автор
    update_post_meta($post_id, 'author_name', isset($xml->author_name) ? (string)$xml->author_name : '');
    update_post_meta($post_id, 'author_display_name', isset($xml->author_display_name) ? (string)$xml->author_display_name : '');
    update_post_meta($post_id, 'author_email', isset($xml->author_email) ? (string)$xml->author_email : '');

    error_log("AGRO2012 {$post_id}: Оброблено about_agro2012, views: {$views}");
}

/**
 * Встановлення Featured Image з локального файлу
 *
 * @param int $post_id ID поста
 * @param string $file_path Шлях до файлу (наприклад: sites/default/files/image.jpg)
 */
function vsesumy_set_agro_featured_image($post_id, $file_path) {
    // Перевірити чи вже є featured image
    if (has_post_thumbnail($post_id)) {
        return;
    }

    // Повний шлях до локального файлу
    $local_file_path = ABSPATH . $file_path;

    if (!file_exists($local_file_path)) {
        error_log("AGRO2012: Файл не знайдено: {$local_file_path}");
        return;
    }

    // Використовуємо існуючу функцію для завантаження локальних файлів
    $attachment_id = vsesumy_sideload_local_file($local_file_path, $post_id, 'image');

    if ($attachment_id) {
        set_post_thumbnail($post_id, $attachment_id);
        error_log("AGRO2012 {$post_id}: Встановлено featured image з {$file_path}");
    }
}

add_action('pmxi_saved_post', 'vsesumy_process_agro2012_import', 10, 3);
