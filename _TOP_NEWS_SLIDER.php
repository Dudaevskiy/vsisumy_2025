<?php
/**
 * Отображаем слайдер топ новостей только на главной, в главном слайдере
 * $_SERVER['REQUEST_URI'] == '/'
 *
 * Слайдер находится здесь:
 * themes/vsisumy_2025/views/general/loop/listing-modern-grid-1.php
 */



function rama_top_news_slider() {

    // Получаем IDs постов в категории (далее будем их сравнивать с транзищен кешем)
    $term_id = 9783; // ТОП Тема -> ID
    $ids = get_posts( array(
        'cat' => $term_id,
        'post_type' => 'post',
        'pages_per_post' => -1,
    ) );
    $ids = wp_list_pluck( $ids, 'ID' );
    $ids = join(',',$ids);

    // Кешируем данные Query https://bit.ly/2XOhoj1
//        $cache_key = 'cache_template__rama_top_news'.$ids;
    $cache_key = 'cache_template__rama_top_news';



//                        if (!get_transient( $cache_key )){

    /**
     * 1️⃣ - Шаблон вывода слайдера топ новостей
     */

    //        $html_template = '<div class="mg-row mg-row-1">';
    $html_template = '<article class="post-{$post_id} type-post format-standard has-post-thumbnail listing-item-2 listing-item listing-mg-item listing-mg-type-1 listing-mg-1-item main-term-9">';
    $html_template .= '<div class="item-content">';
    $html_template .= '<a title="{$post_title}"';
    //        $html_template .= 'data-bs-srcset="{&quot;baseurl&quot;:&quot;https:\/\/rama.com.ua\/wp-content\/uploads\/2021\/09\/&quot;,&quot;sizes&quot;:{&quot;210&quot;:&quot;gaz-1-210x136.jpg&quot;,&quot;279&quot;:&quot;gaz-1-279x220.jpg&quot;,&quot;357&quot;:&quot;gaz-1-357x210.jpg&quot;,&quot;750&quot;:&quot;gaz-1-750x430.jpg&quot;,&quot;1200&quot;:&quot;gaz-1.jpg&quot;}}"';
    $html_template .= 'data-bs-srcset="{$post_image_urls}"';
    $html_template .= 'class="img-cont b-loaded"';
    //        $html_template .= '{$post_url}"';
    //        $html_template .= 'style="background-image: url(https://rama.com.ua/wp-content/uploads/2021/09/gaz-1-357x210.jpg);"></a>';
    $html_template .= 'style="background-image: url({$post_image_url_medium});"></a>';

    // foreach bredcreambs
    //$html_template .= '<div class="term-badges floated"><span class="term-badge term-9">';
    //$html_template .= '<a href="https://rama.com.ua/category/glavnoe-za-den/">';
    //$html_template .= '✅ Главное за день</a>';
    //$html_template .= '</a></span><span class="term-badge term-1">';
    //$html_template .= '<a href="https://rama.com.ua/category/news/">✅ Новости</a>';
    //$html_template .= '</span>';
    //$html_template .= '<span class="term-badge term-1214">';
    //$html_template .= '<a href="https://rama.com.ua/category/news/oblast-news/">✅ Область</a>';
    //$html_template .= '</span>';
    //$html_template .= '</div>';


    $html_template .= '<div class="content-container">';
    $html_template .= '<h2 class="title">';
    $html_template .= '<a href="{$post_url}" class="post-url post-title top_news">';
    $html_template .= '{$post_title}';
    $html_template .= '</a></h2>';
    $html_template .= '<div class="post-meta">';
    $html_template .= '<a href="{$post_author_url}" title="{$post_title}" class="post-author-a">';
    $html_template .= '<i class="post-author author">{$post_author_name}</i>';
    $html_template .= '</a>';
    $html_template .= '<span class="time">';
    $html_template .= '<time class="post-published updated" datetime="{$post_date_publish}">{$post_date}</time>';
    //        $html_template .= '<time class="post-published updated" datetime="2021-09-02T13:56:58+03:00">02.09.2021</time>';
    $html_template .= '</span>';
    $html_template .= '<span class="views post-meta-views rank-default" data-bpv-post="{$post_id}">{$post_views}</span></div>';
    $html_template .= '</div>';
    $html_template .= '</div>';
    $html_template .= '</article>';


    /**
     * 2️⃣ Получаем посты из категории "ТОП Новина"
     */
    $term_id = 9783; // ТОП Новина -> ID

    $the_query = new WP_Query( array(
        'cat' => $term_id,
        'posts_per_page' => 3,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $all_top_posts = '';

    if ( $the_query->have_posts() ){
        // Получаем первый пост
        $first_news = $the_query->posts[0];
        // Удаляем первый пост из массива
        unset($the_query->posts[0]);
        // И переместим первый элемент массива в конец для карусели
        array_push($the_query->posts, $first_news);

        foreach ($the_query->posts as $post){

            $tmp_timplate = $html_template;
            $post_id = $post->ID; //{$post_id}
            $post_title = $post->post_title; //{$post_title}
//                    s($post_title);
            $post_url = get_permalink($post_id); // {$post_url}
            $post_author_name = get_userdata( $post->post_author )->display_name; //{$post_author_name}
            $id_image_post = get_post_thumbnail_id($post_id);
            $post_image_urls = wp_get_attachment_image($id_image_post); //{$post_image_urls}
            $post_image_url_medium = wp_get_attachment_image_url($id_image_post,'medium_large'); //{$post_image_url_medium}
            $post_image_urls = preg_replace('/(.+?)srcset="(.+?)"(.+?)" \/>/','${2}',$post_image_urls);
            $post_views = get_post_meta($post_id,'views')[0]; //{$post_views}
            $post_date = get_the_date( '',$post_id ); //{$post_date}

            $tmp_timplate = preg_replace('/{\$post_id}/',$post_id,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_title}/',$post_title,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_url}/',$post_url,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_author_name}/',$post_author_name,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_views}/',$post_views,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_date}/',$post_date,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_image_urls}/',$post_image_urls,$tmp_timplate);
            $tmp_timplate = preg_replace('/{\$post_image_url_medium}/',$post_image_url_medium,$tmp_timplate);

            // https://bit.ly/3DOWBLq
            $all_top_posts .= '<li>'.$tmp_timplate.'</li>';
        }
    }

    /**
     * Создаем HTML для СубСлайдера
     */
//            $html_template = '<div class="mg-row mg-row-1" id="rama_main_slider_and_top_news">';
    $html_template = '<article class="rama_main_slider_and_top_news type-post format-standard has-post-thumbnail  listing-item-2 listing-item listing-mg-item listing-mg-type-1 listing-mg-1-item">';
    $html_template .= '<div class="rama_top_news_slider">';
    $html_template .= '<ul>'.$all_top_posts.'</ul>';
    if ($the_query->found_posts > 1){
        $html_template .= '<a rel="next" class="btn-bs-pagination control_next bs-slider-arrow" href="#">';
        $html_template .= '<i class="fa fa-angle-right" aria-hidden="true"></i>';
        $html_template .= '</a>';
        $html_template .= '<a rel="next" class="btn-bs-pagination control_prev bs-slider-arrow" href="#">';
        $html_template .= '<i class="fa fa-angle-left" aria-hidden="true"></i>';
        $html_template .= '</a>';
    }
    $html_template .= '</div>';
//            $html_template .= '</div>';
//            $html_template .= '<div class="mg-row mg-row-2">';
    $html_template .= '</article>';



//                            // Кешируем в временную опцию https://bit.ly/39CeoJd
//                            set_transient( $cache_key, $html_template, 24 * HOUR_IN_SECONDS );
//                            $posts_get_full = $html_template;
//                        } else {
//                            // Если PHP кеш есть, просто вернем HTML
//                            $html_template = get_transient( $cache_key );
//                        }



    /**
     *  - Замена в слайдере
     * https://regex101.com/r/QhAwd6/1
     */

    return $html_template;
}