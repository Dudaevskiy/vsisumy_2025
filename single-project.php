<?php
/**
 * single.php
 *
 * The template for displaying posts
 *
 *
 * @author    BetterStudio
 * @package   Publisher
 * @version   1.8.4
 */
///**
// * Просто редиректим из страницы на страницу проекта
// */
//global $post;
//
//$id = $post->ID;
//$url_custom_external = get_post_meta($id, 'project_link');
//if ($url_custom_external[0]){
//    $html = '<script>';
//    $html .= 'window.location.href = "'.$url_custom_external[0].'";';
//    $html .= '</script>';
//} else {
//    $html = '<script>';
//    $html .= 'window.location.href = "/';
//    $html .= '</script>';
//}
//echo $html;
global $post;
//        dd($post);
if ( $post->post_type == 'project' ) {
    $id = $post->ID;
    $url_custom_external = get_post_meta($id, 'project_link');
//            dd($url_custom_external);
    if ($url_custom_external[0]){
        $html = '<script>';
        $html .= 'window.location.href = "'.$url_custom_external[0].'";';
        $html .= '</script>';
        echo $html;
    }
}


//get_header();



publisher_the_post();

// Prints content with layout that is selected in panels.
// Location: "views/general/post/style-*.php"
publisher_get_view( 'post', publisher_get_single_template() );

get_footer();
