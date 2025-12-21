<?php

/**
 * Настройки здесь:
 * /wp-admin/admin.php?page=panorama-optsii&dialog-saved=1
 * В поле настроек указывается 4 топ автора, которые далее генерируются при помощи данного шорткода
 * [short_last_posts_authors_main_block]
 *
 *
 *
 * @param $atts
 * @return string
 *
 */
if (!function_exists('short_last_posts_authors_main_block')) {

    function short_last_posts_authors_main_block($atts)
    {

        if (is_admin()) {
            return false;
        }

//        ddd($atts['first_bloger']);

        /**
         * Если в шорт коде не указана почта для первого блогера
         * получим блогеров из настроек сайта
         */
        if (empty($atts['first_bloger'])) {
            $option_page = jet_engine()->options_pages->registered_pages['panorama-optsii'];
        }

        /**
         * Если в шорт коде указана почта для первого блогера
         * укажем данного блогера первым, далее отобразим 3 блогеров из
         * трех последних опубликованных постов
         */
        if (!empty($atts['first_bloger'])) {
            // Получаем последних 150 постов из категории "Блоги" (указан ее ID 'cat' => 5)
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 100,
                'order' => 'DESC',
                'orderby' => 'ID',
                // https://rama.com.ua/wp-admin/term.php?taxonomy=category&tag_ID=5&post_type=post
                'cat' => 5,
                // cache
                'cache_wp_query' => true,
                'suppress_filters' => 0
            );

            /**
             * Место для транзишн кеша
             */
            // Кешируем данные Query https://bit.ly/2XOhoj1
            $cache_key = 'rama_top_authors____shortcode_authors_emails';
            if (!get_transient( $cache_key )){
                $query = new WP_Query($args);
            // Кешируем в временную опцию https://bit.ly/39CeoJd
                set_transient( $cache_key, $query, 6 * HOUR_IN_SECONDS );
            } else {
                // Если PHP кеш есть, просто вернем HTML
                $query = get_transient( $cache_key );
            }
//
//            $query = new WP_Query($args);
//            vd($query);
            wp_reset_query();

            $authors = array();
            foreach ($query->posts as $post){
//                ddd($post->post_author);
                $user = get_user_by( 'id', $post->post_author );
                $user_email = $user->user_email;
                // Делаем проверку на топ блоггера
                if (in_array("author", $user->roles)){
                    if (!in_array($user_email,$authors) && $user_email !== $atts['first_bloger']){
                        array_push($authors, $user_email);
                    }

                }
            }
            // Удаляем дубликаты
//            $authors = array_unique($authors);
//            s($authors);
//            vd($atts['first_bloger']);
            // Удаляем блогера из атрибута шорткода
//            unset($authors[array_search($atts['first_bloger'], $authors)]);
//            vd( $authors);
            // Срезаем до 3 авторов
            array_splice($authors, 2);
            // Ставим первым автора почта которого указана в шорт коде
            array_unshift($authors, $atts['first_bloger']);
            // Переводим почты в строку
            $authors = implode(",", $authors);
            // И отдаем результат
            $option_page = $authors;
        }

        if (!empty($option_page)) {

            if (empty($atts['first_bloger'])){
                $rama_top_autors_emails = $option_page->get('rama_top_autors'); // string
            }
            if (!empty($atts['first_bloger'])){
                $rama_top_autors_emails = $option_page; // string
            }

            // Удаляем пробелы
            $rama_top_autors_emails = preg_replace('/\s+/', '', $rama_top_autors_emails);
            $rama_top_autors_emails = explode(",", $rama_top_autors_emails);

            if (!function_exists('get_attachment_url_by_slug')) {
                function get_attachment_url_by_slug($slug)
                {
                    $args = array(
                        'post_type' => 'attachment',
                        'image_size' => 'thumbnail',
//                        'name' => sanitize_title($slug),
                        'name' => $slug,
                        'posts_per_page' => 1,
                        'post_status' => 'inherit',
                    );
                    $_header = get_posts($args);
//                    s($args);
                    $header = $_header ? array_pop($_header) : null;
                    return $header ? wp_get_attachment_url($header->ID) : '';
                }
            }

            // Получаем 1) Фото автора 2) ФИО 3) ССылка на записи автора

            if (!function_exists('GetListAuthors')) {
                // GetListAuthors($rama_top_autors_emails, 'TOP_Authors')
                function GetListAuthors($rama_top_autors_emails, $option)
                {

                    // Если это топ авторов
                    if ($option == 'TOP_Authors') {

                    }

                    // Если это журналисты
                    if ($option == 'TOP_Jurnalists') {

                        $args = array(
                            'orderby' => 'post_count',
                            'order' => 'DESC',
                            'number' => 4,
                            'role' => 'journalist'
                        );
                        $user_query = new WP_User_Query($args);
                        $authors = $user_query->get_results();

                        $rama_top_autors_emails = $authors;

                    }


                    $html = '';
                    $i = 0;

                    foreach ($rama_top_autors_emails as $email_user) {

                        if ($option == 'TOP_Authors') {
                            $user_all_onfo = get_user_by_email($email_user);
                        }
                        if ($option == 'TOP_Jurnalists') {
                            $user_all_onfo = get_user_by_email($email_user->user_email);
                        }

                        $display_Name = $user_all_onfo->display_name; // "Сергей Алещенко"
                        $login = $user_all_onfo->user_login; // string (5) "evgen"
//                        $login = get_author_posts_url( $last_post_id );
                        $login = str_replace(' ','-',$login);
                        $user_id = $user_all_onfo->ID; // integer 311
                        $user_user_email = $user_all_onfo->user_email; // str email
                        $user_posts_count = count_user_posts($user_id, 'post');


                        // UserPhoto
                        $photo_emptu = "/wp-content/uploads/2021/11/empty_user_photo.jpg";
                        $user_get_meta = get_user_meta($user_id); // array
//                        s($user_get_meta);

//                        $user_photo = $user_get_meta['userphoto_image_file']; // string (7) "311.jpg"
                        $user_photo = $user_get_meta['avatar']; // string (7) "311.jpg"


                        if (!empty($user_photo) ) {

                            // Если нет изображения
                            $img_id = (int)$user_get_meta['avatar'][0]; // string (6) "242873"

//                            $user_photo = wp_get_attachment_image_url($img_id, 'thumbnail');
                            $user_photo = wp_get_attachment_image_url($img_id, array(50,50));

                            if (!$user_photo) {
                                $user_photo = $photo_emptu;
                            }


                        }

//                        else if (emty($user_photo)) {
//                            if (is_array($user_photo)){
//                                $user_photo = $user_photo[0];
//                            }
//                            $user_photo = get_attachment_url_by_slug(explode(".", $user_photo)[0]);
//                        }

                            $html .= '<div class="listing-item listing-item-user type-2 style-1 clearfix">';
                        $html .= '<div class="bs-user-item">';

                        // Если аватара все равно нет
                        if ($user_photo == ""){
                            $user_photo = $photo_emptu;
                        }

                        $html .= '<a class="user_photo" href="/author/' . $login . '/?user_cat=publication" target="_blank"><img data-src="' . $user_photo . '" class="photo img-holder" width="50" height="50"></a>';
                        //data-src="'.$img_poster.'" class="img-holder" height="220" width="157"
                        $html .= '<div class="details">';
                        // С каунтером постов
//                        $html .= '<div class="name"><a href="/author/' . $login . '/?user_cat=publication" target="_blank">' . $display_Name . ' (' . $user_posts_count . ')</a></div>';
                        // Без каунтера
                        $html .= '<div class="name"><a href="/author/' . $login . '/?user_cat=publication" target="_blank" style="font-size:14px">' . $display_Name . '</a></div>';
                        $html .= '</div>';

                        /**
                         * Получаем последний пост юзера
                         */
                        global $current_user;
                        get_currentuserinfo();

                        $args = array(
                            'author'        =>  $user_id, // I could also use $user_ID, right?
                            'posts_per_page' => 1,
                            'orderby'       =>  'post_date',
                            'order'         =>  'DESC'
                        );
                        /**
                         * Место для транзишн кеша
                         */
                        // Кешируем данные Query https://bit.ly/2XOhoj1
//                        $cache_key = 'rama_top_authors____shortcode_'.$user_id;
//                        $cache_key = 'rama_top_authors____shortcode_authors';

//                        if (!get_transient( $cache_key )){
                            $query = get_posts( $args );
                            // Кешируем в временную опцию https://bit.ly/39CeoJd
//                            set_transient( $cache_key, $query, 6 * HOUR_IN_SECONDS );
                            $current_user_last_post =  $query[0];
//                        } else {
//                            // Если PHP кеш есть, просто вернем HTML
//                            $posts_get_full = get_transient( $cache_key );
//                            $current_user_last_post = $posts_get_full[0];
//                        }


                        $last_post_id = $current_user_last_post->ID;
                        $last_post_url = get_post_permalink( $last_post_id );
                        $last_post_title = $current_user_last_post->post_title;
                        $last_post_content = $current_user_last_post->post_content;
                        $last_post_post_excerpt = $current_user_last_post->post_excerpt;
                        // Если есть excrept
                        if ($last_post_post_excerpt){
                            // https://wp-kama.ru/id_31/obrezka-teksta-zamenyaem-the-excerpt.html
//                            $last_post_post_excerpt = kama_excerpt( [ 'maxchar'=>60, 'text'=>$last_post_post_excerpt ,'save_tags' => '']  );

                            $last_post_post_excerpt =  htmlspecialchars(strip_tags($last_post_post_excerpt));
                            $last_post_post_excerpt =  mb_strimwidth($last_post_post_excerpt, 0, 60, '...');

                            // Если excrept нет берем из контента
                        } else {
//                            $last_post_post_excerpt = kama_excerpt( [ 'maxchar'=>60, 'text'=>$last_post_content, 'save_tags' => '' ]  );

                            $last_post_post_excerpt =  htmlspecialchars(strip_tags($last_post_content));
                            $last_post_post_excerpt =  mb_strimwidth($last_post_content, 0, 60, '...');
                        }

                        $last_post_post_excerpt =  preg_replace('/&nbsp;/','',$last_post_post_excerpt);
                        $last_post_post_excerpt =  htmlspecialchars(strip_tags($last_post_post_excerpt));


                        $last_post_post_date = get_the_date('', $last_post_id) . ', ' . get_the_time('', $last_post_id);

                        $html .= '<div class="last_post_wrap">';
                        $html .= '<a href="'.$last_post_url.'" target="_blank" class="last_author_post__title">'.$last_post_title.'</a>';
                        $html .= '<span class="last_author_post__desc">'.$last_post_post_excerpt.'</span>';
                        $html .= '<sub class="last_author_post__data_time"><b>'.$last_post_post_date.'</b></sub>';
                        $html .= '</div>';


                        $html .= '</div>';
                        $html .= '</div>';
                        //////////////
//                    $html .= "<a target='_blank' href='$link'>$title</a>"; // Title of post
//                        $html .= "<br />"; // Date Published

                        $i++;
                    }

                    return $html;
                }
            }

            /**
             * HTML Вывод
             */
//            $html = '<div class="listing listing-user type-2 style-1 columns-4 clearfix">';
            $html = '<div class="listing listing-user type-2 style-1 columns-3 clearfix">';

            $html .= '<div class="block rama_widget_shortcode textwidget">';
            $html .= '<div class="bs-tab-shortcode">';
//            $html .= '<ul class="nav nav-tabs" role="tablist">';
//            $html .= '<li class="active"><a href="#tab-rss1" data-toggle="tab"> ТОП Автори</a></li>';
//            $html .= '<li><a href="#tab-rss2" data-toggle="tab"> Журналісти </a></li>';
//            $html .= '</ul>';


            $html .= '<div class="tab-content">';

            if ($rama_top_autors_emails) {
//                $html .= '<div id="tab-rss1" class="active tab-pane">';
                $html .= '<div class="rama-last-posts_for_main">';
                $html .= GetListAuthors($rama_top_autors_emails, 'TOP_Authors');
//                $html .= '<div class="all"><a href="/authors/" target="_blank">Усі автори</a></div>';
                $html .= '</div>';
//                $html .= '</div>';
            }

//            // Вывод топа журналистов
//            $html .= '<div id="tab-rss2" class="tab-pane">';
//            $html .= '<div class="rama-last-posts">';
//            $html .= GetListAuthors(array(), 'TOP_Jurnalists');
//            $html .= '<div class="all"><a href="/authors/?cat=journalists" target="_blank">Усі журналісти</a></div>';
//            $html .= '</div>';
//            $html .= '</div>';


            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="top_blogers_link first" style="float: left;"><a href="/authors/" target="_blank">Переглянути всіх блогерів</a></div>';
            $html .= '<div class="top_blogers_link" style="float: right;"><a href="/category/blogs/" target="_blank">Переглянути блоги</a></div>';

            $html .= '</div>';

            return $html;
        }
    }
}

// Add a shortcode
add_shortcode('short_last_posts_authors_main_block', 'short_last_posts_authors_main_block');






/**
 * ====================================================
 */



