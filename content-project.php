<?php
/**
 * Просто редиректим из страницы на страницу проекта
 */
add_action('init','redirect_to_project');
function redirect_to_project(){
    global $post;
    function hook_inHeader() {
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
    }
}

//function project_redirect() {
//    if( is_singular('project') ){
//
//        if ( $post->post_type == 'project' ) {
//            $id = $post->ID;
//            $url_custom_external = get_post_meta($id, 'project_link');
//            dd($url_custom_external);
//            if ($url_custom_external[0]){
//                $html = '<script>';
//                $html .= 'window.location.href = "'.$url_custom_external[0].'";';
//                $html .= '</script>';
//                echo $html;
//            }
//        }
//        wp_enqueue_script('jobpostingschema', get_stylesheet_directory_uri() . '/js/jobpostingschema.js', array(), '', false);
//    }
//}
//add_action('wp_enqueue_scripts', 'my_custom_scripts');