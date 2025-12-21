<?php

/**
 * Замена плагина
 * https://www.clickonf5.org/iframe-embed-for-youtube
 * 
 */

if (!shortcode_exists( 'yframe' )){
    add_shortcode( 'yframe', 'yframe_iframe_shortcode' );

    function yframe_iframe_shortcode( $iframe ) {
        $tags = array( // Default values for some tags
            'width' => '100%',
            'height' => '100%',
            'frameborder' => '0'
        );

        foreach ( $tags as $default => $value ) { // Add new tags in array if they don't exist
            if ( !@array_key_exists( $default, $iframe ) ) {
                $iframe[$default] = $value;
            }
        }

        $html = '<iframe width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"';
        foreach( $iframe as $attr => $value ) { // Insert tags and default values if none were set

        // Заменяем http: на https:
        if (strpos($value, 'http') !== false){
            $value = str_replace('http:/','https:/',$value);
        }

        // Удаляем полный фрем
        // https://www.youtube.com/watch?v=j_siUiNAleg&feature=c4-overview-vl&list=PL1neMztLSbMO5Ff6AY3crYegyXxrkBJUJ -> https://youtu.be/j_siUiNAleg
        // https://www.youtube.com/watch?v=j_siUiNAleg -> https://youtu.be/j_siUiNAleg
        if (strpos($value, 'youtube.com') !== false && strpos($value, '&amp;feature') !== false){
            $value = explode("&amp;feature", $value)[0];
            $value = explode("watch?v=", $value)[1];
            $value = 'https://www.youtube.com/embed/'.$value;
        }

            if ( $value != '' ) {
                $html .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
            }
        }
        $html .= '></iframe>';
        $html = str_replace('url=','src=',$html);

        return $html;
    }

}