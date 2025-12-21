<?php
/**
 * Attach a class to linked images' parent anchors
 * Works for existing content
 * Requires: Attachments plugin
 */
add_filter('the_content', 'insert_flex_gallery');
function insert_flex_gallery($content) {

    // Перевірка: чи існує клас Attachments (плагін активний)
    if (!class_exists('Attachments')) {
        return $content;
    }

    global $post;
    if ($post->post_type == "post"){
        $id = $post->ID;
        $get_meta = get_post_meta($id);

        // Получим прикрепления к посту изображений
        $attachments = new Attachments( 'attachments' );

        if($attachments->exist() && count($attachments->get_attachments()) > 1) {
            global $post;
            $id_post = $post->ID;
            $getmeta = get_post_meta($id_post);
            // Images? for slides
            $html = '';
            $html .= '';
            $html .= '<!-- Place somewhere in the <body> of your page -->';
            $html .= '<div id="carousel" class="flexslider" style="margin-top: 0px;margin-bottom: 0px;">';
            $html .= '  <ul class="slides">';
            $i = 0;
            foreach ($attachments->get_attachments() as $img){
                //if ($i !== 0) {
                $html .= '    <li>';
                $html .= '      <img class="slidermainimg" src="' . wp_get_attachment_image_url($img->id, 'small-feature') . '" title="' . $img->fields->title . '"/>';
                $html .= '    </li>';
                //}
                $i++;
            }
            $html .= '  </ul>';
            $html .= '</div>';


            $html .= '<div id="slider" class="flexslider">';
            $html .= '  <ul class="slides" >';
            $i = 0;
            foreach ($attachments->get_attachments() as $img){
                //if ($i !== 0) {
                $counter_for_light_box = 100+$i;
                $html .= '    <li>';
                $html .= '        <a rel="lightbox" class="lightbox" data-rel="lightbox" data-lightbox-gallery="myGallery"  href="' . wp_get_attachment_image_url($img->id, 'full') . '">';
                $html .= '          <div class="overlay_flex_slider"></div>';
                $html .= '          <img class="attachment-thumbnail swiper-slide-image slidermainimg" src="' . wp_get_attachment_image_url($img->id, 'full') . '" title="' . $img->fields->title . '" />';
                $html .= '        </a>';
                $html .= '    </li>';
                //}
                $i++;
            }
            $html .= '  </ul>';
            $html .= '</div>';
            $html .= '<!-- items mirrored twice, total -->';



//

            return $html.$content;

        }
    }

    wp_reset_query();
    wp_reset_postdata();

    return $content;
}