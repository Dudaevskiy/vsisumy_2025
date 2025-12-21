<?php

/**
 * [rama_last_anonce]
 * @param $atts
 * @return string
 * [categoryposts limit="10" thumb="false"]
 */
function rama_last_anonce($atts)
{
    if (is_admin()){
        return false;
    }
    // Получаем последний анонс
    function GetLastPostId($post_type)
    {
        $db_serch = "post_type=" . $post_type . "&numberposts=1";
        $latest_cpt = get_posts($db_serch);
        return $latest_cpt[0]->ID;
    }

    $id = GetLastPostId('announce');// 241656
    $get_meta = get_post_meta($id);

    $post_id = $id;
    $html = '';

    $html .= '<div class="block rama_widget_shortcode last_annonces">';
//    $html .= '<div class="title">Анонс газети</div>';

    $html .= '<div class="paper">';
    $link_url = get_permalink($post_id);

    $post_title = esc_attr(get_the_title($post_id));
    $html .= '<div class="paper_title"><a target="_blank" href="' . $link_url . '" title="' . $post_title . '">Газета "Панорама" № '. get_post_meta($post_id, '_newspaper_nomer', true) . ' (' . get_post_meta($post_id, '_newspaper_nomer_global', true) . ') <span class="br">від ' . $post_title . '</span></a></div>';
    $html .= '<div class="img_wrap"><a target="_blank" href="' . $link_url . '" title="' . $post_title . '">';
    $img_poster = $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), "medium" )[0];

//    $html .= ' . get_the_post_thumbnail($post_id, 'announce-thumb', array('alt' => $post_title,'class' => 'img-holder', 'title' => '')) . '</a></div>';
    $html .= '<img data-src="'.$img_poster.'" class="img-holder" height="220" width="157" />';
    $html .= '</a></div>';

    $html .= '<div class="anounce">';

    function get_announces_desc($post_id){
        $i = 1;
        $html ='';
        $custom = get_post_custom($post_id);
        $query = new WP_Query(array('meta_key' => 'total_number_of_newspaper', 'meta_value' => $custom['_newspaper_nomer_global'][0], 'suppress_filters' => 0));
        wp_reset_postdata();

        if ($custom["_art_item_1_title"][0]) {
            $html .= '<div class="desc">';
        }
        while ($custom["_art_item_" . $i . "_title"][0]) {
            $title = $custom["_art_item_" . $i . "_title"][0];
            foreach ($query->posts as $post_item) {
                if ($post_item->post_title == $title) {
                    $post_link = get_permalink($post_item->ID);
                    break;
                }
            }
            $html .= '<div class="announces_article">';
            if (isset($post_link)) {
                $html .= '<a class="article_link hyphenate" href="' . $post_link . '">' . $title . '</a>';
            } else {
                $html .= '<span class="article_link hyphenate">' . $title . '</span>';
            }
            $html .= '</div>';
            $i++;
            unset($post_link);
        }

        return $html;
    }

    $html .= get_announces_desc($post_id);
    $html .= '<br><div class="all"><a href="' . esc_url(home_url('/')) . 'arhiv-novin/?rama_dates='. date('Y') .'&rama_cat=announces" target="_blank">Переглянути архів газети</a></div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
// Add a shortcode
add_shortcode('rama_last_anonce', 'rama_last_anonce');