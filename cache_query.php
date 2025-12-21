<?php
/**
 * Добавить поддержку моих типов сообщений для Cache WP Query
 */
function  my_cache_wp_query_post_types () {

    add_post_type_support ( 'post' , 'cache_wp_query' );
//    add_post_type_support ( 'my_cpt' , 'cache_wp_query' );

}
add_filter ( 'init' , 'my_cache_wp_query_post_types' );