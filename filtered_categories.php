<?php
/**
 * require_once in header.php
 *
 * Вывод в категории постов
 * views/general/taxonomy/layout-2-col.php
 * views/general/archive/_title-category.php
 */
/**
 * START
 * https://wp-kama.ru/function/query_posts
 */
/**
 * REGIONS
 */
if ( isset($_GET['filter']) && $_GET['filter'] ){
    global $query_string;
    // добавляем базовые параметры в массив $args
    parse_str($query_string, $args);
    // добавляем/заменяем параметр post_type в массиве
//                $args['offset'] = 5;
    $args['post_type'] = 'post';
    $args['tax_query'] = array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $args['category_name']
        ),
        array(
            'taxonomy' => 'region',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_GET['filter'])
        )
    );
    query_posts( $args );
}
/**
 * Populars
 */
if ( isset($_GET['sort']) && $_GET['sort'] ){
    global $query_string;
    // добавляем базовые параметры в массив $args
    parse_str($query_string, $args);
    // добавляем/заменяем параметр post_type в массиве
//                $args['offset'] = 5;
    /**
     * Популярные
     */
    if ( isset($_GET['sort']) && $_GET['sort'] == 'popular' ){
        $args['post_type'] = 'post';
        $args['post_status'] = 'publish';
    //    $args['order'] = 'DESC';
        $args['meta_key'] = 'views';
        $args['orderby'] = 'meta_value_num';
        $args['cache_wp_query'] = true;
        $args['suppress_filters'] = 0;
    }

    /**
     * Обсуждаемые
     */
    if ( isset($_GET['sort']) && $_GET['sort'] == 'commented' ){
        $args['post_type'] = 'post';
        $args['post_status'] = 'publish';
        //    $args['order'] = 'DESC';
//        $args['meta_key'] = 'views';
        $args['orderby'] = 'comment_count';
        $args['cache_wp_query'] = true;
        $args['suppress_filters'] = 0;
    }

    query_posts( $args );
}
/**
 * END
 */
