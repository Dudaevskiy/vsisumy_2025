<?php
function add_custom_meta_box() {
    add_meta_box(
        'custom_meta_box',       // ID метабокса
        'Вибір кольору назви',         // Заголовок метабокса
        'custom_meta_box_callback', // Функція зворотнього виклику
        'post'                   // Тип поста, до якого додати метабокс
    );
}
add_action('add_meta_boxes', 'add_custom_meta_box');


function custom_meta_box_callback($post) {
    // Додайте nonce для безпеки
    wp_nonce_field('custom_meta_box_nonce', 'meta_box_nonce');

    // Отримайте існуюче значення кольору з бази даних
    $color = get_post_meta($post->ID, '_custom_color', true);

    // Поле для вибору кольору
    echo '<input type="text" name="custom_color" value="' . esc_attr($color) . '" class="my-color-field" />';
}


function save_custom_meta_box_data($post_id) {
    // Перевірка nonce
    if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'custom_meta_box_nonce')) {
        return;
    }

    // Перевірка прав користувача
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Збереження кольору
    if (isset($_POST['custom_color'])) {
        update_post_meta($post_id, '_custom_color', sanitize_text_field($_POST['custom_color']));
    }
}
add_action('save_post', 'save_custom_meta_box_data');


function enqueue_color_picker_script($hook_suffix) {
    if ('post.php' != $hook_suffix && 'post-new.php' != $hook_suffix) {
        return;
    }

    // Підключення стандартного color picker WordPress
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    // Додавання інлайн-скрипта для ініціалізації color picker
    wp_add_inline_script('wp-color-picker', 'jQuery(document).ready(function($) { $(".my-color-field").wpColorPicker(); });');
}
add_action('admin_enqueue_scripts', 'enqueue_color_picker_script');

