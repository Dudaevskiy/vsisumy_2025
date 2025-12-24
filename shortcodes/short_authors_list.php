<?php

/**
 * Настройки здесь:
 * https://rama.com.ua/wp-admin/admin.php?page=panorama-optsii&dialog-saved=1
 * [rama_authors_list limit="10"]
 *
 * @param $atts
 * @return string
 *
 */
if (!function_exists('rama_authors_list')){
    function rama_authors_list($atts)
    {
        if (is_admin()) {
            return false;
        }

        $option_page  = jet_engine()->options_pages->registered_pages['panorama-optsii'];
        if (!empty( $option_page)){
            $rama_top_autors_emails = $option_page->get( 'rama_top_autors' ); // true/false
            // Удаляем пробелы
            $rama_top_autors_emails = preg_replace('/\s+/', '', $rama_top_autors_emails);
            $rama_top_autors_emails = explode(",",$rama_top_autors_emails);

            if (!function_exists('get_attachment_url_by_slug')){
                function get_attachment_url_by_slug( $slug ) {
                    $args = array(
                        'post_type' => 'attachment',
                        'image_size' =>'thumbnail',
                        'name' => sanitize_title($slug),
                        'posts_per_page' => 1,
                        'post_status' => 'inherit',
                    );
                    $_header = get_posts( $args );
                    $header = $_header ? array_pop($_header) : null;
                    return $header ? wp_get_attachment_url($header->ID) : '';
                }
            }

            // Получаем 1) Фото автора 2) ФИО 3) ССылка на записи автора

            if (!function_exists('GetListAuthors')){
                // GetListAuthors($rama_top_autors_emails, 'TOP_Authors')
                function GetListAuthors($rama_top_autors_emails, $option){

                    // Если это топ авторов
                    if ($option == 'TOP_Authors'){


                    }

                    // Если это журналисты
                    if ($option == 'TOP_Jurnalists'){

                        $args = array(
                            'orderby'    => 'post_count',
                            'order'      => 'DESC',
                            'number' => 5,
                            'role'       => 'journalist'
                        );
                        $user_query = new WP_User_Query( $args );
                        $authors = $user_query->get_results();

                        $rama_top_autors_emails = $authors;

                    }


                $html = '';

                foreach ($rama_top_autors_emails as $email_user){

                    if ($option == 'TOP_Authors'){
                        $user_all_onfo = get_user_by_email( $email_user );
                    }
                    if ($option == 'TOP_Jurnalists'){
                        $user_all_onfo = get_user_by_email( $email_user->user_email );
                    }

                    $display_Name = $user_all_onfo->display_name; // "Сергей Алещенко"
                    $login = $user_all_onfo->nickname; // "Сергей Алещенко"
                    $user_id  = $user_all_onfo->ID; // integer 311
                    $user_posts_count = count_user_posts($user_id,'post');
                    // UserPhoto
                    $photo_emptu = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect fill='%23ddd' width='100' height='100'/%3E%3Ccircle cx='50' cy='35' r='20' fill='%23999'/%3E%3Cellipse cx='50' cy='85' rx='35' ry='25' fill='%23999'/%3E%3C/svg%3E";
                    $user_get_meta = get_user_meta( $user_id); // array

                    $user_photo  = $user_get_meta['userphoto_image_file']; // string (7) "311.jpg"
                    if ($user_photo) {
                        $user_photo = get_attachment_url_by_slug(explode(".", $user_photo[0])[0]);
                    } else if (!$user_photo){
                        // Если нет изображения
                        $img_id = (int)$user_get_meta['avatar'][0]; // string (6) "242873"

                        $user_photo = wp_get_attachment_image_url( $img_id, 'thumbnail' );

                        if (!$user_photo){
                            $user_photo = $photo_emptu;
                        }
                    }

                    $html .= '<div class="author">';
                    $html .= '<a href="/author/'.$login.'/?user_cat=publication" target="_blank"><img src="'.$user_photo.'" alt="'.$display_Name.'" class="photo" width="50" height="50"></a>';
                    $html .= '<div class="details">';
                    $html .= '<div class="name"><a href="/author/'.$login.'/?user_cat=publication" target="_blank">'.$display_Name.' ('.$user_posts_count.')</a></div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    //////////////
//                    $html .= "<a target='_blank' href='$link'>$title</a>"; // Title of post
                    $html .= "<br />"; // Date Published

                    $i++;
                }

                return $html;
                }
            }

            /**
             * HTML Вывод
             */
            $html = '<div class="block rama_widget_shortcode textwidget">';
            $html .= '<div class="bs-tab-shortcode">';
            $html .= '<ul class="nav nav-tabs" role="tablist">';
                $html .= '<li class="active"><a href="#tab-rss1" data-toggle="tab"> ТОП Автори</a></li>';
                $html .= '<li><a href="#tab-rss2" data-toggle="tab"> Журналісти </a></li>';
            $html .= '</ul>';


            $html .= '<div class="tab-content">';

                if ($rama_top_autors_emails) {
                    $html .= '<div id="tab-rss1" class="active tab-pane">';
                    $html .= '<div class="rama-last-posts">';
                        $html .= GetListAuthors($rama_top_autors_emails, 'TOP_Authors');
                    $html .= '<div class="all"><a href="/authors/" target="_blank">Усі автори</a></div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }

                // Вывод топа журналистов
                    $html .= '<div id="tab-rss2" class="tab-pane">';
                    $html .= '<div class="rama-last-posts">';
                        $html .= GetListAuthors(array(), 'TOP_Jurnalists');
                    $html .= '<div class="all"><a href="/authors/?cat=journalists" target="_blank">Усі журналісти</a></div>';
                    $html .= '</div>';
                    $html .= '</div>';


            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            return $html;
        }
    }
}

// Add a shortcode
add_shortcode('rama_authors_list', 'rama_authors_list');