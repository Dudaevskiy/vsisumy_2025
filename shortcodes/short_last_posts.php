<?php

/**
 * @param $atts
 * @return string
 * [categoryposts limit="10" thumb="false"]
 */
function rama_last_posts($atts)
{

    $atts = shortcode_atts(array(
        'name' => null,
        'class' => null,
        'limit' => null,
        'thumb' => null,
        'posts' => null,
        'exclude_cat_id' => null,
        'last' => null,
        'popular' => null,
        'comments' => null
    ), $atts);

    $name = $atts['name'];
    $class = $atts['class'];
    $limit = $atts['limit'];
    if (!$limit){
        $limit = 5;
    }
    $thumb = $atts['thumb'];
    if (!$thumb){
        $thumb = 'false';
    }
    $number = $atts['posts'];
    if (!$number){
        $number = 5;
    }

    $exclude_cat_id = $atts['exclude_cat_id'];
    if (!$exclude_cat_id){
        $exclude_cat_id = '';
    }

    $last = $atts['last'];
    if (!$last){
        $last = 'false';
    }

    $popular = $atts['popular'];
    if (!$popular){
        $popular = 'false';
    }

    $comments = $atts['comments'];
    if (!$comments){
        $comments = 'false';
    }

    /**
     * Shorten Counter
     */
    if (!function_exists('number_format_short')) {
        function number_format_short($n, $precision = 1)
        {
            if ($n < 900) {
                // 0 - 900
                $n_format = number_format($n, $precision);
                $suffix = '';
            } else if ($n < 900000) {
                // 0.9k-850k
                $n_format = number_format($n / 1000, $precision);
                $suffix = 'Т';
            } else if ($n < 900000000) {
                // 0.9m-850m
                $n_format = number_format($n / 1000000, $precision);
                $suffix = 'M';
            } else if ($n < 900000000000) {
                // 0.9b-850b
                $n_format = number_format($n / 1000000000, $precision);
                $suffix = 'B';
            } else {
                // 0.9t+
                $n_format = number_format($n / 1000000000000, $precision);
                $suffix = 'T';
            }

            // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
            // Intentionally does not affect partials, eg "1.50" -> "1.50"
            if ($precision > 0) {
                $dotzero = '.' . str_repeat('0', $precision);
                $n_format = str_replace($dotzero, '', $n_format);
            }

            return $n_format . $suffix;
        }
    }


    /**
     * LAST
     */
    if ($atts['last'] == "true") {
        // Кешируем данные https://bit.ly/2XOhoj1
        // Додаємо мову до ключа кешу для WPML
        $current_lang = apply_filters('wpml_current_language', 'uk');
        $cache_key = 'cache_query__last_posts_' . $current_lang;

        $cached_html = get_transient( $cache_key );
        // Перевіряємо чи кеш валідний (не порожній і містить пости)
        if ( false === $cached_html || empty($cached_html) || strpos($cached_html, '<div class="new">') === false ) {
            // =============================
            date_default_timezone_set('Europe/Kiev');

            /***
             * FIX sticky_posts START
             */
            $sticky_posts = get_option('sticky_posts');
            if (!is_array($sticky_posts)) {
                $sticky_posts = array();
            }

            // Отримуємо звичайні пости (не закріплені)
            $regular_posts = get_posts(array(
                'post_type' => array('post', 'audiopodkasty'),
                'post_status' => 'publish',
                'posts_per_page' => $number,
                'post__not_in' => $sticky_posts,
                'cat' => $exclude_cat_id,
                'fields' => 'ids',
                'orderby' => 'date',
                'order' => 'DESC',
            ));

            // Об'єднуємо закріплені та звичайні пости
            $all_post_ids = array_merge($sticky_posts, $regular_posts);

            // Якщо є пости для відображення
            if (!empty($all_post_ids)) {
                $args = array(
                    'post_type' => array('post', 'audiopodkasty'),
                    'post_status' => 'publish',
                    'posts_per_page' => $number,
                    'orderby' => 'post__in',
                    'post__in' => $all_post_ids,
                );
            } else {
                // Fallback - просто отримуємо останні пости
                $args = array(
                    'post_type' => array('post', 'audiopodkasty'),
                    'post_status' => 'publish',
                    'posts_per_page' => $number,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'cat' => $exclude_cat_id,
                );
            }
            $query = new WP_Query($args);
            /***
             * FIX sticky_posts END
             */


            ob_start();
            $html = '<div class="rama-last-posts">';

            while ($query->have_posts()) : $query->the_post();
                $html .= '<div class="new">';
                global $post;

                $full_date = $post->post_date;
                $full_date = explode(' ', $full_date)[0];
                $date_month = explode('-', $full_date)[1];
                $date_day = explode('-', $full_date)[2];
                $date_for_print = $date_day . '.' . $date_month;

                $post_format = get_post_format( $post->ID );
                if (!empty($post_format)){
                    if ($post_format == "gallery"){
                        $post_format = '<i class="fa fa-camera"></i>';
                    }
                    else if ($post_format == "image"){
                        $post_format = '<i class="fa fa-camera"></i>';
                    }
                    else if ($post_format == "video"){
                        $post_format = '<i class="fa fa-play"></i>';
                    }
                    else if ($post_format == "audio"){
                        $post_format = '<i class="fa fa-music"></i>';
                    }
                    else if ($post_format == "aside"){
                        $post_format = '<i class="fa fa-pencil"></i>';
                    }
                    else if ($post_format == "quote"){
                        $post_format = '<i class="fa fa-quote-left"></i>';
                    }
                    else if ($post_format == "status"){
                        $post_format = '<i class="fa fa-refresh"></i>';
                    }
                    else if ($post_format == "chat"){
                        $post_format = '<i class="fa fa-coffee"></i>';
                    }
                    else if ($post_format == "link"){
                        $post_format = '<i class="fa fa-link"></i>';
                    } else {
                        $post_format = '';
                    }
                } else {
                    $post_format = '';
                }

                // Отримання збереженого кольору
                $color_get = get_post_meta($post->ID, '_custom_color', true);

                // Використання кольору
                $color = '';
                if (!empty($color_get)) {
                    // echo 'Вибраний колір: ' . esc_attr($color_get);
                    $color = 'style="color:'.$color_get.';font-weight: 600;"';
                }

                $html .= '<div class="counter date"><i class="fa fa-calendar"></i>' . $date_for_print . "</div>";
                $html .= '<div class="title hyphenate"><a href="' . $post->guid . '" '.$color.'>'. $post_format .'  '. $post->post_title . '</a></div>';

                $html .= '<div class="clear"></div>';
                $html .= '</div>';
            endwhile;

            $html .= '</div>';
            wp_reset_postdata();

            // Кешуємо тільки якщо є пости (не порожній результат)
            if ( strpos($html, '<div class="new">') !== false ) {
                set_transient( $cache_key, $html, 24 * HOUR_IN_SECONDS );
            }

            return $html;
        } else {
            // Якщо кеш є, повертаємо HTML
            return $cached_html;
        }
    }


    /**
     * POPULAR
     */
    if ($popular == "true") {
        // Кешируем данные https://bit.ly/2XOhoj1
        // Додаємо мову до ключа кешу для WPML
        $current_lang = apply_filters('wpml_current_language', 'uk');
        $cache_key = 'cache_query__popular_' . $current_lang;

        if ( !$html = get_transient( $cache_key ) ) {
            // =============================
            // The Query - сортування по даті публікації (останні зверху)
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $number,
                'order' => 'DESC',
                'orderby' => 'date',
                'cat' => $exclude_cat_id,
                // cache
                'cache_wp_query' => true,
                'suppress_filters' => 0
            );

            $query = new WP_Query($args);
            ob_start();
            $html = '<div class="rama-last-posts">';

            while ($query->have_posts()) : $query->the_post();
                $html .= '<div class="new">';
                global $post;

                $full_date = $post->post_date;
                $full_date = explode(' ', $full_date)[0];
                $date_month = explode('-', $full_date)[1];
                $date_day = explode('-', $full_date)[2];
                $date_for_print = $date_day . '.' . $date_month;

                $html .= '<div class="counter date"><i class="fa fa-calendar"></i>' . $date_for_print . "</div>";
                $html .= '<div class="title hyphenate"><a href="' . $post->guid . '">' . $post->post_title . '</a></div>';

                $html .= '<div class="clear"></div>';
                $html .= '</div>';
            endwhile;

                $html .= '</div>';
                // wp_reset_postdata();
                // return $html;
                // =============================
                // $html = ob_get_clean();

                // Кешируем в временную опцию https://bit.ly/39CeoJd
                set_transient( $cache_key, $html, 24 * HOUR_IN_SECONDS );

                return $html;
            } else {
                // Если PHP кеш есть, просто вернем HTML
                $cache_key = 'cache_query__popular';
                $html = get_transient( $cache_key );
                return $html;
            }
        }


    /**
     * COMMENTS
     * Сортування за мета-полем comment_count (перенесені коментарі з rama.com.ua)
     * Включає пости з мета-полем і без нього (з fallback на 0)
     */
    if ($comments == "true") {
        // Кешируем данные https://bit.ly/2XOhoj1
        // Додаємо мову до ключа кешу для WPML
        $current_lang = apply_filters('wpml_current_language', 'uk');
        $cache_key = 'cache_query__comments_' . $current_lang;

        if ( !$html = get_transient( $cache_key ) ) {
            // =============================
            // Сортуємо за мета-полем comment_count (включаючи пости без мета-поля)
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $number,
                'cat' => $exclude_cat_id,
                'meta_query' => array(
                    'relation' => 'OR',
                    // Пости з мета-полем comment_count
                    'has_comments' => array(
                        'key' => 'comment_count',
                        'compare' => 'EXISTS',
                        'type' => 'NUMERIC',
                    ),
                    // Пости без мета-поля (будуть внизу)
                    'no_comments' => array(
                        'key' => 'comment_count',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
                'orderby' => array(
                    'has_comments' => 'DESC',
                ),
                // cache
                'cache_wp_query' => true,
                'suppress_filters' => 0
            );

            $query = new WP_Query($args);
            ob_start();
            $html = '<div class="rama-last-posts">';

            while ($query->have_posts()) : $query->the_post();
                $html .= '<div class="new">';
                global $post;

                $id = $post->ID;

                // Спочатку перевіряємо мета-поле comment_count
                $meta_comment_count = get_post_meta($id, 'comment_count', true);
                if (!empty($meta_comment_count) && (int)$meta_comment_count > 0) {
                    $comment_count_raw = (int)$meta_comment_count;
                } else {
                    // Якщо мета-поле порожнє, беремо стандартний comment_count
                    $comment_count_raw = (int)$post->comment_count;
                }

                $comment_count = number_format_short($comment_count_raw); // округляем

                $html .= '<div class="counter comments"><i class="fa fa-comments"></i>' . $comment_count . "</div>";
                $html .= '<div class="title hyphenate"><a href="' . $post->guid . '">' . $post->post_title . '</a></div>';

                $html .= '<div class="clear"></div>';
                $html .= '</div>';
            endwhile;

            $html .= '</div>';
            wp_reset_postdata();

            // Кешируем в временную опцию https://bit.ly/39CeoJd
            set_transient( $cache_key, $html, 24 * HOUR_IN_SECONDS );

            return $html;
        } else {
            // Если PHP кеш есть, просто вернем HTML
            $cache_key = 'cache_query__comments';
            $html = get_transient( $cache_key );
            return $html;
        }
    }

}

// Add a shortcode
add_shortcode('rama_last_posts', 'rama_last_posts');


// Enable shortcodes in text widgets
add_filter('rama_last_posts', 'do_shortcode');

/**
 * Очистити кеш rama_last_posts для всіх мов
 * Використання: додай ?clear_rama_cache=1 до URL (тільки для адмінів)
 */
add_action('init', 'rama_clear_posts_cache');
function rama_clear_posts_cache() {
    if (isset($_GET['clear_rama_cache']) && current_user_can('manage_options')) {
        $languages = array('uk', 'ru', 'en');
        $cache_types = array('last_posts', 'popular', 'comments');

        foreach ($languages as $lang) {
            foreach ($cache_types as $type) {
                delete_transient('cache_query__' . $type . '_' . $lang);
            }
        }

        // Також видаляємо старі ключі без мови (для сумісності)
        delete_transient('cache_query__last_posts');
        delete_transient('cache_query__popular');
        delete_transient('cache_query__comments');

        wp_die('Кеш rama_last_posts очищено для всіх мов!');
    }
}


/**
 * ====================================================
 */



