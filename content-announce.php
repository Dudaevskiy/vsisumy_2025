<?php
function announce_content($content) {
if (is_singular('announce')) {

global $post;
$i = 1;
$custom = get_post_custom($post->ID);
$html = '';
$query = new WP_Query( array( 'meta_key' => 'total_number_of_newspaper', 'meta_value' => $custom['_newspaper_nomer_global'][0], 'suppress_filters' => 0) );
wp_reset_postdata();
while ($custom["_art_item_".$i."_title"][0]) {
$html .= '<div class="article">';
    $html .= '<div class="title">'.get_the_category_by_ID( $custom["_art_item_".$i."_cat"][0] ) .' ('.$custom["_art_item_".$i."_group"][0] . $custom["_art_item_".$i."_nomer"][0] .'):</div>';

    $title = $custom["_art_item_".$i."_title"][0];
    foreach ($query->posts as $post_item) {
    if ($post_item->post_title == $title) {
    $post_link = get_permalink($post_item->ID);
    break;
    }
    }
    if (isset($post_link)) {
    $html .= '<a class="article_link" href="'.$post_link.'">'. $title .'</a><br>';
    } else {
    $html .= '<span class="article_link">'.$title.'</span><br>';
    }
    $next_echo =  $custom["_art_item_".$i."_descr"][0] ? $custom["_art_item_".$i."_descr"][0].'<br>' : '';
    $html .= $next_echo;
    $html .= __('Автор: ','panorama') . get_the_author_meta('display_name', $custom["_art_item_".$i."_author"][0] ).'</a>';
    $html .='</div>';
$i++;
unset($post_link);
}

$pdf_announce = get_post_meta($post->ID, 'pdf_announce' );
$id = $pdf_announce[0]['id']; //  integer 22446
// $pdf_url = $pdf_announce[0]['url']; // https://rama.com.ua/wp-content/uploads/2012/10/Panorama-41.pdf
if ($id){
$shortcode = do_shortcode('[wp-embedder-pack width="85%" height="1000px" download="all" download-text="Завантажити" attachment_id="'.$id.'" /]');
$html .= "<br><br>\n".$shortcode;
}

return $html;
} return $content;
}
add_filter('the_content', 'announce_content');