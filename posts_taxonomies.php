<?php

add_action( 'init', 'create_post_taxonomies', 0 );

// функция, создающая 2 новые таксономии "genres" и "writers" для постов типа "book"
function create_post_taxonomies(){
// определяем заголовки для 'genre'
$labels = array(
'name' => __( 'Регионы', 'panorama' ),
'singular_name' => __( 'Регион', 'panorama', 'panorama' ),
'search_items' =>  __( 'Искать регионы', 'panorama' ),
'all_items' => __( 'Все регионы', 'panorama' ),
'parent_item' => __( 'Родительский регион', 'panorama' ),
'parent_item_colon' => __( 'Родительский регион:', 'panorama' ),
'edit_item' => __( 'Редактировать регион', 'panorama' ),
'update_item' => __( 'Обновить регион', 'panorama' ),
'add_new_item' => __( 'Добавить новый регион', 'panorama' ),
'new_item_name' => __( 'Имя нового региона', 'panorama' ),
'menu_name' => __( 'Регионы', 'panorama' ),
);

// Добавляем древовидную таксономию 'genre' (как категории)
register_taxonomy('region', array('post'), array(
'hierarchical' => true,
'labels' => $labels,
'show_ui' => true,
'query_var' => true,
'rewrite' => array( 'slug' => 'region' )
));


$labels1 = array(
'name' => __( 'Спецкатегории', 'panorama' ),
'singular_name' => __( 'Спецкатегория', 'panorama' ),
'search_items' =>  __( 'Искать спецкатегории', 'panorama' ),
'all_items' => __( 'Все спецкатегории', 'panorama' ),
'parent_item' => __( 'Родительская спецкатегория', 'panorama' ),
'parent_item_colon' => __( 'Родительская спецкатегория:', 'panorama' ),
'edit_item' => __( 'Редактировать спецкатегорию', 'panorama' ),
'update_item' => __( 'Обновить спецкатегорию', 'panorama' ),
'add_new_item' => __( 'Добавить новую спецкатегорию', 'panorama' ),
'new_item_name' => __( 'Имя новой спецкатегории', 'panorama' ),
'menu_name' => __( 'Спецкатегории', 'panorama' ),
);
// Добавляем древовидную таксономию 'genre' (как категории)
register_taxonomy('special-cat', array('post'), array(
'hierarchical' => true,
'labels' => $labels1,
'show_ui' => true,
'show_in_rest' => true,
'has_archive' => true,
'query_var' => true
));


}

//add_filter('admin_head', 'add_jquery_data');
function add_jquery_data() {
    global $parent_file;
    if ( !isset( $_GET['action'] ) && !isset( $_GET['post'] ) && $parent_file == 'edit.php') {
        echo '<script type="text/javascript" charset="utf-8">';
        echo 'jQuery(document).ready(function(){jQuery("#in-special-cat-85").attr("checked","checked")})';
        echo '</script>';
    }
}


//remove default category (uncategorized) when another category has been set
function remove_default_category($ID, $post) {

    //get all categories for the post
    $categories = wp_get_object_terms($ID, 'category');

    //if there is more than one category set, check to see if one of them is the default
    if (count($categories) > 1) {
        foreach ($categories as $key => $category) {
            //if category is the default, then remove it
            if ($category->name == "Uncategorized") {
                wp_remove_object_terms($ID, 'uncategorized', 'category');
            }
        }
    }
}
//hook in to the publsh_post action to run when a post is published
add_action('publish_post', 'remove_default_category', 10, 2);