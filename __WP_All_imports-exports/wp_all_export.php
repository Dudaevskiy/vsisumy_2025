<?php

/***
 *  Функция для экспорта в WP All Export
 *  usage: Экспорт значения, возвращаемого PHP функцией -> rama_attachs($value)
 */

function rama_attachs($value)
{
    $get_meta = get_post_meta($value, "attachments", true);
    // {"attachments":[{"id":"233003","fields":{"title":"1","caption":""}},{"id":"233004","fields":{"title":"2","caption":""}},{"id":"233005","fields":{"title":"3","caption":""}},{"id":"233006","fields":{"title":"4","caption":""}},{"id":"233007","fields":{"title":"4_1","caption":""}}]}
    if ($get_meta) {

        $json_decode = json_decode($get_meta);

        foreach ($json_decode as $attach => $value) {
            foreach ($value as $value_attach => $val) {
                $img_id = $val->id;
                $img_url = wp_get_attachment_url($img_id);
                $val->{'url'} = $img_url; // public url -> string(60) "https://rama.com.ua/wp-content/uploads/2021/01/44.jpg"
            }
//            $this->{$name} = $value;
        }

        $json_encode_plus_img_url = json_encode($json_decode);
//        ddd($json_decode);
//        ddd($json_encode);

        return $json_encode_plus_img_url;


    }
}
