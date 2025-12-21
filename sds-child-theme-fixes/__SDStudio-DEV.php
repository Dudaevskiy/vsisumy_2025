<?php
/***
 * DEV code
 * require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/__SDStudio-DEV.php');
 */

//$_POST['OriginalPostLinkMD'] = 'https://noomarketing.net/organizatsia-marketinga-v-malom-biznese';
//////$_POST['OriginalPostLinkMD'] = 175053;
////global $query;
////$args = array(
////    'meta_query' => array(
////        array(
////            'key' => 'sdstudio_get_original_post_link',
////            'value' => $_POST['OriginalPostLinkMD']
////        )
////    ),
////    'post_type' => 'orig_post',
////    'posts_per_page' => -1
////);
////$query = new WP_Query($args);
////
////
////$orig_post_detected_id = $query->posts[0]->ID;
////ddd($orig_post_detected_id);
///
///
///


// Додайте цей код у файл functions.php вашої теми або у власний плагін WordPress.

// 1. Реєстрація кастомного типу запису 'gpt_regenerate'
function register_gpt_regenerate_post_type()
{
    $args = array(
        'label' => 'GPT Regenerate',
        'public' => true,
        'show_in_menu' => true,
        'supports' => array('title', 'editor'),
    );

    register_post_type('gpt_regenerate', $args);
}

add_action('init', 'register_gpt_regenerate_post_type');

// 2. Функція для виклику OpenAI API
function call_chatgpt_api($prompt)
{
    $api_key = 'YOUR_OPENAI_API_KEY';

    $url = 'https://api.openai.com/v1/chat/completions';

    $data = array(
        'model' => 'gpt-4o-2024-08-06',
        'messages' => array(
            array('role' => 'user', 'content' => $prompt)
        ),
        'max_tokens' => 16400, // Налаштуйте відповідно до ваших потреб
        'temperature' => 0.7,
    );

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body' => json_encode($data),
        'timeout' => 120, // Збільште таймаут при необхідності
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        error_log('Помилка запиту до OpenAI API: ' . $response->get_error_message());
        return null;
    }

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    if (isset($result['error'])) {
        error_log('Помилка OpenAI API: ' . $result['error']['message']);
        return null;
    }

    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    }

    return null;
}

// 3. Функція для обробки та збереження поста
function process_and_save_post($post_id)
{
    // Отримуємо оригінальний пост
    $original_post = get_post($post_id);

    if (!$original_post) {
        return;
    }

    // Отримуємо контент поста
    $original_content = $original_post->post_content;

    // Готуємо промпт (вставте ваш промпт замість $prompt_content)
    $prompt_content = <<<EOT
У мене є сайт WordPress на якому 10 000 постів, у кожному з яких мінімум 5000 символів контенту. Мені потрібно переписати кожен пост згідно цьому промпту:
Создай SEO-оптимизированный информационный пост в формате Markdown. Пост должен состоять из 4 частей (твоих ответов), символов без пробелов. Все четыре части вместе должны содержать от 5000 до 7000 символов, исключая пробелы. Если пост состоит из 1 или 2 или 3 ответов это грубая ошибка! Пост должен состоять только из 4 ответов!

[Вставте тут інші деталі вашого промпту, включаючи константи, вимоги та інструкції]

Оригинальный пост:
$original_content
EOT;

    // Викликаємо API
    $processed_content = call_chatgpt_api($prompt_content);

    if (!$processed_content) {
        return;
    }

    // Створюємо новий пост типу 'gpt_regenerate'
    $new_post = array(
        'post_title' => $original_post->post_title,
        'post_content' => $processed_content,
        'post_status' => 'publish',
        'post_type' => 'gpt_regenerate',
    );

    // Вставляємо пост у базу даних
    wp_insert_post($new_post);
}

// 4. Приклад використання: обробка одного поста з ID 123
// process_and_save_post(123);

// 5. Функція для пакетної обробки всіх постів
function process_multiple_posts()
{
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Або встановіть конкретну кількість
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        process_and_save_post($post->ID);
        // Додайте затримку, щоб уникнути перевищення лімітів API
        sleep(2); // Пауза на 2 секунди між запитами
    }
}

// 6. Запуск пакетної обробки (розкоментуйте, щоб запустити)
// process_multiple_posts();
