<?php

/**
 * [rama_last_news_partners cat_id="10" view="5"]
 * @param $atts
 * @return string
 * [categoryposts limit="10" thumb="false"]
 */
if (!function_exists('rama_last_news_partners')){
    function rama_last_news_partners($atts)
    {
        if (is_admin()) {
            return false;
        }

        $ID_CAT = (int)(isset($atts['cat_id']) ? $atts['cat_id'] : 10);
        $view = (int)(isset($atts['view']) ? $atts['view'] : 5);

        // Отримання закріплених постів з категорії
        $sticky_posts = get_option('sticky_posts');
        $args_sticky = array(
            'posts_per_page' => -1,
            'post__in'       => $sticky_posts,
            'category'       => $ID_CAT,
            'post_type'      => 'post',
            'suppress_filters' => false
        );
        $sticky_posts_list = get_posts($args_sticky);

        // Фільтрація, щоб забезпечити, що ми маємо лише закріплені пости з вказаної категорії
        $filtered_sticky_posts = array_filter($sticky_posts_list, function($post) use ($ID_CAT) {
            return has_category($ID_CAT, $post->ID);
        });

        // Витягуємо ID закріплених постів для виключення
        $sticky_post_ids = array_map(function($post) {
            return $post->ID;
        }, $filtered_sticky_posts);

        // Обмежуємо кількість закріплених постів, якщо вони перевищують ліміт
        $filtered_sticky_posts = array_slice($filtered_sticky_posts, 0, $view);

        // Кількість інших постів, яку потрібно отримати
        $num_other_posts = $view - count($filtered_sticky_posts);

        // Отримуємо інші пости, виключаючи закріплені
        $args = array(
            'posts_per_page' => $num_other_posts,
            'category'       => $ID_CAT,
            'post_type'      => 'post',
            'suppress_filters' => false,
            'post__not_in'   => array_merge($sticky_posts, $sticky_post_ids) // Виключіть закріплені пости
        );
        $other_posts = get_posts($args);

        // Об'єднайте закріплені пости та інші пости
        $myposts = array_merge($filtered_sticky_posts, $other_posts);

        $html = '<div class="block rama_widget_shortcode last_partners_news">';
        foreach ($myposts as $post) {
            setup_postdata($post);

            $title = $post->post_title;
            $id = $post->ID;
            $color_get = get_post_meta($id, '_custom_color', true);
            $color = !empty($color_get) ? 'style="color:'.$color_get.';font-weight: 600;"' : '';

            $html .= '<div class="new">';
            $html .= '<div class="title hyphenate"><a href="' . get_the_permalink($id) . '" title="' . esc_attr($title) . '" '.$color.'>' . $title . '</a></div>';
            $html .= '<span class="date">' . get_the_date('', $id) . ', ' . get_the_time('', $id) . '</span>';
            $html .= '</div>';
        }

        $html .= '<div class="all"><a href="' . get_category_link($ID_CAT) . '">Усі новини</a></div>';
        $html .= '</div>';
        wp_reset_postdata();
        return $html;
    }
}

add_shortcode('rama_last_news_partners', 'rama_last_news_partners');





//if (!function_exists('rama_last_news_partner')){
//    function rama_last_news_partners($atts)
//    {
//        if (is_admin()) {
//            return false;
//        }
////        ob_start();
//
//        // Для всех страниц кроме главной
//        $ID_CAT = (int)$atts['cat_id'] ? $atts['cat_id'] : 10;
//        // Для главной страницы
//    //    $for_main = $atts['for_main'] ? $atts['for_main'] : 2013;
//        // Количество постов для отображения
//        $view = (int)$atts['view'] ? $atts['view'] : 5;
//
//
//        // Получаем последний анонс
//        if (!function_exists('GetLastPostPartners')){
//            function GetLastPostPartners($id_cat, $num_views, $post_type)
//            {
//                $id_cat = (int)$id_cat;
//                $num_views = (int)$num_views;
//
//                $db_serch = "post_type=" . $post_type . "&numberposts=" . $num_views . "&category=" . $id_cat;
//                $latest_cpt = get_posts($db_serch);
//                return $latest_cpt;
//            }
//        }
//
//        $get_posts = GetLastPostPartners($ID_CAT, $view, 'post');
//
//
//        $html = '';
//        $html .= '<div class="block rama_widget_shortcode last_partners_news">';
//    //    s($html);
////        global $post;
////        $args = array(
////            'numberposts' => $view,
////            'category' => $ID_CAT,
////            'post_type' => 'post',
////            'suppress_filters' => 0,
////            'this_shortcodes_rama' => 'yes'
////        );
////
////        $myposts = get_posts($args);
//        /***
//         * FIX sticky_posts START
//         */
//        global $post;
//
//// Отримайте закріплені пости
//        $sticky_posts = get_option('sticky_posts');
//        $args_sticky = array(
//            'posts_per_page' => $view,
//            'post__in'       => $sticky_posts,
//            'post_type'      => 'post',
//            'suppress_filters' => 0,
//            'this_shortcodes_rama' => 'yes'
//        );
//        $sticky_posts_list = get_posts($args_sticky);
//
//// Отримайте інші пости, виключаючи закріплені
//        $args = array(
//            'numberposts'       => $view,
//            'category'          => $ID_CAT,
//            'post_type'         => 'post',
//            'suppress_filters'  => 0,
//            'this_shortcodes_rama' => 'yes',
//            'post__not_in'      => $sticky_posts // Виключіть закріплені пости
//        );
//        $other_posts = get_posts($args);
//
//// Об'єднайте закріплені пости та інші пости
//        $myposts = array_merge($sticky_posts_list, $other_posts);
///**
//END
// */
//
//
//        foreach ($myposts as $post) {
//            //    dd($post);
//            setup_postdata($post);
//
//            $title = $post->post_title;
//            $id = $post->ID;
//            // Отримання збереженого кольору
//            $color_get = get_post_meta($post->ID, '_custom_color', true);
//
//            // Використання кольору
//            $color = '';
//            if (!empty($color_get)) {
//                // echo 'Вибраний колір: ' . esc_attr($color_get);
//                $color = 'style="color:'.$color_get.';font-weight: 600;"';
//            }
//
//            $html .= '<div class="new">';
//            $html .= '<div class="title hyphenate"><a href="' . get_the_permalink($id) . '" title="' . esc_attr($title) . '" '.$color.'>' . $title . '</a></div>';
//            $html .= '<span class="date">' . get_the_date('', $id) . ', ' . get_the_time('', $id) . '</span>';
//            $html .= '</span>';
//            $html .= '<div class="clear"></div>';
//            $html .= '</div>';
//        }
//
//        $html .= '<div class="all"><a href="' . get_category_link($ID_CAT) . '">Усі новини</a></div>';
//        $html .= '</div>';
//        wp_reset_postdata();
////        ob_get_clean();
//        return $html;
//    }
//}
//
//// Add a shortcode
//add_shortcode('rama_last_news_partners', 'rama_last_news_partners');