<?php

/**
 * Настройки здесь:
 * https://rama.com.ua/wp-admin/admin.php?page=panorama-optsii&dialog-saved=1
 * [rama_last_history_posts_carusel limit="3"]
 * limit="3" - Количество постов в каруселм
 *
 * @param $atts
 * @return string
 *
 */
if (!function_exists('rama_last_history_posts_carusel')){
    function rama_last_history_posts_carusel($atts)
    {
        if (is_admin()) {
            return false;
        }

        // Количество слайдов в карусели
         $limit= $atts['limit'];

        // 📌📌📌📌 Здесь будет кеширование
        // Получаем последние посты
        global $post;
        $pid = 1; // post ID here
        $args = array(
            'numberposts' => $limit,
            'post_type' => 'kalendar_istor_podiy',
            'suppress_filters' => 0,
            'this_shortcodes_rama' => 'yes',
            'posts_per_page' => $limit,
            'date_query' => array(
                array(
                    'month' => date( 'n', current_time( 'timestamp' ) ),
                    'day' => date( 'j', current_time( 'timestamp' ) )
                )
            ),
        );
        // 📌📌📌📌 Здесь будет кеширование

        $posts = get_posts($args);
//        ddd($posts);

        $html = '';

        $html .= '<div class="sds_slideshow_posts_carusel">';

        $html .= '<ul class="sds_slideshow_posts_carusel_slides">';

//        $html .= '<li class="slideActive">1</li>';
//        $html .= '<li>2</li>';
//        $html .= '<li>3</li>';
//        $html .= '<li>4</li>';
//        $html .= '<li>5</li>';
//        $html .= '<li>6</li>';

        $i = 0;
        if (empty($posts)){
            //🔥🔥🔥🔥🔥🔥🔥🔥🔥
            $img_paused = '/wp-content/themes/vsisumy_2025/img/pause-button-320min.png';
//            $html .= '<li class="slideActive" style="background-image: url('.$img_paused .')">';
            $html .= '<li class="slideActive">';
            // =====================
            $html .= '<a class="rama_history_post__link" style="cursor:none;pointer-events: none;" href="#">';

            $html .= '<img src="'.$img_paused.'" class="rama_history_post__img" style="min-width: 200px;">';
            $html .= '<div class="rama_history_post__title" id="rama_empty_history_posts">На сьогодні не знайдено подій</div>';
            $html .= '<div class="rama_history_post__data"></div>';
            $html .= '<div class="rama_history_post__desc"></div>';

            $html .= '</a>';
            // =====================

            $html .= '</li>';
            //🔥🔥🔥🔥🔥🔥🔥🔥🔥
        } else {
            foreach ($posts as $post){
                $i++;
                $post_id = $post->ID;
                $post_title = $post->post_title;
                $post_data = $post->post_date;
//                setlocale(LC_ALL, 'uk');
                $post_data = date_i18n("j F", strtotime($post_data));
    //            $post_img_thumb = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
                $post_img_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );
                $post_desc = $post->post_excerpt;
                if (empty($post_desc)){
                    $post_desc = kama_excerpt( [ 'maxchar'=>90, 'text'=>$post->post_content ,'save_tags' => '']  );
                }
                $post_url = get_permalink($post_id);

                if ($i == 1){
//                    $html .= '<li class="slideActive" style="background-image: url('.$post_img_thumb[0] .')">';
                    $html .= '<li class="slideActive">';
                } else {
//                    $html .= '<li style="background-image: url('.$post_img_thumb[0] .')">';
                    $html .= '<li>';
                }
                // =====================
                $html .= '<a class="rama_history_post__link" href="'.$post_url.'">';

                $html .= '<img src="'.$post_img_thumb[0].'" class="rama_history_post__img">';
                $html .= '<div class="rama_history_post__title">'.$post_title.'</div>';
                $html .= '<div class="rama_history_post__data" style="color: #bc0505;">'.$post_data.'</div>';
//                $html .= '<div class="rama_history_post__desc">'.$post_desc.'</div>';

                $html .= '</a>';
                // =====================

                $html .= '</li>';
            }
        }

        if (count($posts) && count($posts) > 1){
            $html .= '<div class="control">';
            $html .= '<button id="previous"><i class="fa fa-angle-left" aria-hidden="true"></i></button>';
            $html .= '<button id="next"><i class="fa fa-angle-right" aria-hidden="true"></i></button>';
            $html .= '</div>';
        }
        $html .= '</ul>';
//        $html .= '<div class="pager">';
//        $html .= '<ul>';
//        $html .= '<li id="first">1</li>';
//        $html .= '<li id="second">2</li>';
//        $html .= '<li id="third">3</li>';
//        $html .= '<li id="fourth">4</li>';
//        $html .= '<li id="fifth">5</li>';
//        $html .= '<li id="sixth">6</li>';
//        $html .= '</ul>';
//        $html .= '</div>';
        if (count($posts) && count($posts) > 1){
            $html .= '<div class="progressbar">';
                $html .= '<div class="full">';
                    $html .= '<div class="progress"></div>';
                $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '<div class="all"><a href="/kalendar_istor_podiy/">Весь архів подій</a></div>';
        $html .= '</div>';

        return $html;
    }
}

// Add a shortcode
add_shortcode('rama_last_history_posts_carusel', 'rama_last_history_posts_carusel');
