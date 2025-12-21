<?php

/***
 * Данная функция отвечает за импорт изображений из другого сайта
 * в кастомные поля записей, плагина attachs
 *
 * Создана 2022-01-16 при переносе не достающих записей сайта rama.com.ua
 * Скорость:
 * Последний раз 1100 постов импортировалось 4 часа (с учетом того что в базе более 52000 записей)
 */

// Загрузчик изображений
require_once get_stylesheet_directory( ) . '/__WP_All_imports-exports/_class-download-remote-image.php';

function DEV_GetAttachmentsID_to_URL_IMPORT($json_data){

    if ($json_data){


        if (!function_exists('km_set_remote_image_as_featured_image')){
            function km_set_remote_image_as_featured_image( $post_id, $url, $attachment_data = array() ) {

                $download_remote_image = new KM_Download_Remote_Image( $url, $attachment_data );
                $attachment_id         = $download_remote_image->download();

                if ( ! $attachment_id ) {
                    return false;
                }

//                return set_post_thumbnail( $post_id, $attachment_id );
                return $attachment_id;
            }
        }

//        s($json_data);
//        $json_data = str_replace("\r\n", '', $json_data); // single quotes do the trick
        $json_data = preg_replace('/,"caption":"}/m', ',"caption":""}', $json_data); // single quotes do the trick
        $json_decode = json_decode($json_data);

//        ddd($json_decode);

        foreach ($json_decode as $attach => $value) {
            foreach ($value as $value_attach => $val) {

                $img_url = $val->url;// get_image_attachs
                $post_id = 245330;


                $val->{'id'} = km_set_remote_image_as_featured_image( $post_id, $img_url );
                sleep(5);
            }
        }


//        $json_encode_plus_img_url = json_encode($json_decode);

        // Здесь указываем  JSON_UNESCAPED_UNICODE иначе title + caption будут иметь не разобранный вид
        $json_encode_plus_img_url = json_encode($json_decode, JSON_UNESCAPED_UNICODE);
//        ddd($json_encode_plus_img_url);
        return $json_encode_plus_img_url;

        //{"attachments":[{"id":"233003","fields":{"title":"1","caption":""},"url":"https:\/\/rama.com.ua\/wp-content\/uploads\/2021\/01\/120.jpg"},{"id":"233004","fields":{"title":"2","caption":""},"url":"https:\/\/rama.com.ua\/wp-content\/uploads\/2021\/01\/213.jpg"},{"id":"233005","fields":{"title":"3","caption":""},"url":"https:\/\/rama.com.ua\/wp-content\/uploads\/2021\/01\/39.jpg"},{"id":"233006","fields":{"title":"4","caption":""},"url":"https:\/\/rama.com.ua\/wp-content\/uploads\/2021\/01\/44.jpg"},{"id":"233007","fields":{"title":"4_1","caption":""},"url":"https:\/\/rama.com.ua\/wp-content\/uploads\/2021\/01\/4_1.jpg"}]}

    }
}


/**
 * Импорт тегов как с разделителем "|", так и без него  (в случае если только один тег у записи)
 */

$tested_tags = 'OLX Работа|работа|сумы';
function insert_tags_wp_all_imp($tag_string){
    // Если строка содержит разделитель вернем строку, если же нет то добавим тег
    if (strpos($tag_string, '|') !== false){
        return $tag_string;
    } else {

    }
}


/**
 * Обновляем каждый пост во время импорта
 */
add_action( 'pmxi_saved_post', 'update_all_posts__wp_all_import', 10, 3 );

function update_all_posts__wp_all_import( $id, $xml, $update ) {


    /**
     * Теги
     */
    // Поле (столбик) с тегами
    $tag_str = $xml->undefined25;
    // Удаляем теги перед примменением
    wp_set_post_tags( $id, '', true );

    // Если строка содержит разделитель вернем строку, если же нет то добавим тег
    if (strpos($tag_str, '|') !== false){
        $tag_str = str_replace("|",",",$tag_str);
        wp_set_post_tags( $id, $tag_str, false );
    } else {
        wp_set_post_tags( $id, $tag_str, true );
    }

    /**
     * Загрузка и замена изображений в теле поста
     */
    if (!function_exists('km_set_remote_image_download_and_replace_in_content')){
        function km_set_remote_image_download_and_replace_in_content( $post_id, $url, $attachment_data = array() ) {

            $download_remote_image = new KM_Download_Remote_Image( $url, $attachment_data );
            $attachment_id         = $download_remote_image->download();

            if ( !$attachment_id ) {
                return false;
            }

            // Прикрепляем изображение за записью
            wp_update_post( array(
                'ID'            => $attachment_id,
                'post_parent'   => $post_id,
            ), true );

            return $attachment_id;
        }
    }

    // Поле (столбик) с контентом
    $content_import = (string)$xml->content;

    // Обновляем пост
    // Создаем массив данных
    $my_post = array();
    $my_post['ID'] = $id;
    $my_post['post_content'] = $content_import;

    // Обновляем данные в БД
    wp_update_post( wp_slash($my_post) );

    $regex = '/src="([^"]*)"/';
    if (preg_match_all( $regex, $content_import, $matches )){
        foreach($matches as $image){

            foreach($image as $img_url){

                $img_url = str_replace('src="','',$img_url);
                $img_url = str_replace('"','',$img_url);
                $attachment_id = km_set_remote_image_download_and_replace_in_content( $id, $img_url);

                /**
                 * Заменяем изображение
                 */
                $new_download_image_url = wp_get_attachment_url($attachment_id);
            sleep(5);
            $content_post = get_post($id);
            $content = $content_post->post_content;

                $search = '/'.str_replace("/","\/",$img_url).'/m';
                $content = preg_replace($search, $new_download_image_url, $content);

                // Обновляем пост
                // Создаем массив данных
                $my_post = array();
                $my_post['ID'] = $id;
                $my_post['post_content'] = $content;

                // Обновляем данные в БД
                wp_update_post( wp_slash($my_post) );
            }
            break;
        }
    }

    /**
     * И заменяем старый домен
     */
    $content_post = get_post($id);
    $content = $content_post->post_content;

    $search = '/impoldrama.com.ua/m';
    $replace = 'rama.com.ua';
    $content = preg_replace($search, $replace, $content);

    // Обновляем пост
    // Создаем массив данных
    $my_post = array();
    $my_post['ID'] = $id;
    $my_post['post_content'] = '<div class="old_post">'.$content.'</div>';

    // Обновляем данные в БД
    wp_update_post( wp_slash($my_post) );




/*

SimpleXMLElement (35) (
    public id -> string (6) "243112"
    public title -> string (13) "DEV_TAG_multi"
    public slug -> string (28) "yak-vidklasti-splatu-kreditu"
    public content -> string UTF-8 (2440) "“Зі мною таке не трапиться”, зізнава."
    public excerpt -> SimpleXMLElement (0)
    public date -> string (19) "2021-09-24 09:10:58"
    public permalink -> string (62) "https://impoldrama.com.ua/yak-vidklasti-splatu-kreditu/"
    public imageurl -> string (93) "https://impoldrama.com.ua/wp-content/uploads/2021/09/prolongatsiya_kredita_460x342.jpg"
    public imagefilename -> string (33) "prolongatsiya_kredita_460x342.jpg"
    public imagepath -> string (110) "/var/www/vhosts/impoldrama.com.ua/httpdocs/wp-content/uploads/2021/09/prolongatsiya_kredita_460x342.jpg"
    public imageid -> string (6) "243113"
    public imagetitle -> string (64) "Savings, close up of male hand stacking golden coins over sky ba"
    public imagecaption -> SimpleXMLElement (0)
    public imagedescription -> SimpleXMLElement (0)
    public imagealttext -> SimpleXMLElement (0)
    public imagefeatured -> string (93) "https://impoldrama.com.ua/wp-content/uploads/2021/09/prolongatsiya_kredita_460x342.jpg"
    public attachmenturl -> SimpleXMLElement (0)
    public attachmentfilename -> SimpleXMLElement (0)
    public attachmentpath -> SimpleXMLElement (0)
    public attachmentid -> SimpleXMLElement (0)
    public attachmenttitle -> SimpleXMLElement (0)
    public attachmentcaption -> SimpleXMLElement (0)
    public attachmentdescription -> SimpleXMLElement (0)
    public attachmentalttext -> SimpleXMLElement (0)
    public undefined24 -> string UTF-8 (17) "Новости партнеров"
    public undefined25 -> string UTF-8 (77) "дтп сумы|новини суми|новости Сумы|новости сумы дтп|суми новости|сумы|сумы дтп"
    public undefined26 -> SimpleXMLElement (0)
    public undefined27 -> SimpleXMLElement (0)
    public views -> string (2) "41"
    public _attachments -> SimpleXMLElement (0)
    public rama_attachs -> SimpleXMLElement (0)
    public status -> string (7) "publish"
    public authorid -> string (1) "1"
    public authorusername -> string (5) "admin"
    public authoremail -> string (19) "ramacomua@gmail.com"
)

 */


}


//$dev_attach_id = 248083;
//ddd(wp_get_attachment_url($dev_attach_id));
