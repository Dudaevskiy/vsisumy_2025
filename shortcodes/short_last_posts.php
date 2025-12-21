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
        $cache_key = 'cache_query__last_posts';
//ddd(!get_transient( $cache_key ));
//        if ( !$html = get_transient( $cache_key ) ) {
        if ( !get_transient( $cache_key ) ) {
            // =============================
            date_default_timezone_set('Europe/Kiev');
            $args = array('posts_per_page' => -1, 'cat' => $exclude_cat_id, 'suppress_filters' => 0);

//            // The Query
//            $args = array(
////                'post_type' => 'post',
//                'post_type' => array('post','audiopodkasty'),
//                'post_status' => 'publish',
//                'posts_per_page' => $number,
////                'orderby' => 'ID',
//                'orderby' => 'publish_date',
//                'order' => 'DESC',
//                'cat' => $exclude_cat_id,
//                // cache
//                'cache_wp_query' => true,
//                'suppress_filters' => 0
//            );
//
//            $query = new WP_Query($args);
            /***
             * FIX sticky_posts START
             */
            $sticky_posts = get_option('sticky_posts');
            $number = 20;
            $args = array(
                'post_type' => array('post', 'audiopodkasty'),
                'post_status' => 'publish',
                'posts_per_page' => $number,
                'orderby' => 'post__in',
                'post__in' => array_merge($sticky_posts, get_posts(array(
                    'post_type' => array('post', 'audiopodkasty'),
                    'post_status' => 'publish',
                    'posts_per_page' => $number,
                    'post__not_in' => $sticky_posts, // Виключити закріплені пости
                    'fields' => 'ids', // Отримати лише ID
                ))),
                'cat' => $exclude_cat_id,
            );
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
            // wp_reset_postdata();
            // return $html;
            // =============================
            // $html = ob_get_clean();

            // Кешируем в временную опцию https://bit.ly/39CeoJd
            set_transient( $cache_key, $html, 24 * HOUR_IN_SECONDS );

            return $html;
        } else {
            // Если PHP кеш есть, просто вернем HTML
            $cache_key = 'cache_query__last_posts';
            $html = get_transient( $cache_key );
            return $html;
        }
    }


    /**
     * POPULAR
     */
    if ($popular == "true") {
        // Кешируем данные https://bit.ly/2XOhoj1
        $cache_key = 'cache_query__popular';

        if ( !$html = get_transient( $cache_key ) ) {
            // =============================
            // The Query
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $number,
                'order' => 'DESC',
                'cat' => $exclude_cat_id,
                'meta_key' => 'views',
                'orderby' => 'meta_value_num',
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
                $get_meta = get_post_meta($id);
                $vievs_count = $get_meta['views'][0];

                $vievs_count = number_format_short((int)$vievs_count); // округляем

                $html .= '<div class="counter eye"><i class="fa fa-eye"></i>' . $vievs_count . "</div>";
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
     */
    if ($comments == "true") {
        // Кешируем данные https://bit.ly/2XOhoj1
        $cache_key = 'cache_query__comments';

        if ( !$html = get_transient( $cache_key ) ) {
            // =============================
            // The Query
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $number,
                'order' => 'DESC',
                'cat' => $exclude_cat_id,
    //            'meta_key' => 'views',
                'orderby' => 'comment_count',
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
                $get_meta = get_post_meta($id);
                $comment_count = number_format_short((int)$post->comment_count); // округляем

                $html .= '<div class="counter comments"><i class="fa fa-comments"></i>' . $comment_count . "</div>";
                $html .= '<div class="title hyphenate"><a href="' . $post->guid . '">' . $post->post_title . '</a></div>';

                $html .= '<div class="clear"></div>';
                $html .= '</div>';
            endwhile;

            $html .= '</div>';
            wp_reset_postdata();
            return $html;
            // =============================
            // $html = ob_get_clean();

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
 * ====================================================
 */



