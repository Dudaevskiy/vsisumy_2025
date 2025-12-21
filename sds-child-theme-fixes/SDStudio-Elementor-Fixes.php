<?php
/**
 *
 * require_once(get_stylesheet_directory() . '/sds-child-theme-fixes/SDStudio-Elementor-Fixes.php');
 */

///***
// * DEBUG - Дізнаємось ім'я віджету
// */
//add_action('elementor/frontend/widget/before_render', function($widget) {
//    // Логування або виведення імені віджета
//    vd($widget->get_name());
//});

/**
 * sdstudio_social_links SHORTCODE
 * У JetEngine створюємо мета поле репітер типу:
 * social_networks_links
 * у ньому:
 * Посилання на соціальну мережу
 * social_network_name
 * Далі використовуємо шорт код:
 * [sdstudio_social_links] - для оригінального функціоналу
 * [sdstudio_separate_social_links] - для варіанту з окремими мета полями
 */
//function sdstudio_social_links() {
//    if (!function_exists('DynamicFuncForSocialLink')) {
//        function DynamicFuncForSocialLink($link)
//        {
//            $social_icons = array(
//                'facebook.com' => 'fa-facebook',
//                'twitter.com' => 'fa-twitter',
//                'x.com' => 'fa-x-twitter',
//                'linkedin.com' => 'fa-linkedin',
//                'instagram.com' => 'fa-instagram',
//                'youtube.com' => 'fa-youtube',
//                'pinterest.com' => 'fa-pinterest',
//                'tiktok.com' => 'fa-tiktok',
//                'snapchat.com' => 'fa-snapchat',
//                'tumblr.com' => 'fa-tumblr',
//                'reddit.com' => 'fa-reddit',
//                'whatsapp.com' => 'fa-whatsapp',
//                'telegram.org' => 'fa-telegram',
//                'medium.com' => 'fa-medium',
//                'github.com' => 'fa-github',
//                'gitlab.com' => 'fa-gitlab',
//                'bitbucket.org' => 'fa-bitbucket',
//                'stackoverflow.com' => 'fa-stack-overflow',
//                'dribbble.com' => 'fa-dribbble',
//                'behance.net' => 'fa-behance',
//                'flickr.com' => 'fa-flickr',
//                'vimeo.com' => 'fa-vimeo',
//                'twitch.tv' => 'fa-twitch',
//                'discord.com' => 'fa-discord',
//                'slack.com' => 'fa-slack',
//                'spotify.com' => 'fa-spotify',
//                'soundcloud.com' => 'fa-soundcloud',
//                'last.fm' => 'fa-lastfm',
//                'vk.com' => 'fa-vk',
//                'weibo.com' => 'fa-weibo',
//                'ok.ru' => 'fa-odnoklassniki',
//                'xing.com' => 'fa-xing',
//                'meetup.com' => 'fa-meetup',
//                'quora.com' => 'fa-quora',
//                'goodreads.com' => 'fa-goodreads',
//                'yelp.com' => 'fa-yelp',
//                'tripadvisor.com' => 'fa-tripadvisor',
//                'producthunt.com' => 'fa-product-hunt',
//                'deviantart.com' => 'fa-deviantart',
//                'angel.co' => 'fa-angellist',
//                'slideshare.net' => 'fa-slideshare',
//                'foursquare.com' => 'fa-foursquare',
//                'skype.com' => 'fa-skype',
//                'researchgate.net' => 'fa-researchgate',
//                'academia.edu' => 'fa-academia',
//                'kaggle.com' => 'fa-kaggle',
//                'mastodon.social' => 'fa-mastodon',
//                'strava.com' => 'fa-strava',
//                'patreon.com' => 'fa-patreon',
//                'etsy.com' => 'fa-etsy',
//                'bandcamp.com' => 'fa-bandcamp',
//                'paypal.com' => 'fa-paypal',
//                'imdb.com' => 'fa-imdb',
//                'tiktok.com' => 'fa-tiktok'
//            );
//
//            $parsed_url = parse_url($link);
//            $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
//
//            $icon_class = 'fa-link'; // Стандартна іконка посилання
//            foreach ($social_icons as $domain => $icon) {
//                if (strpos($host, $domain) !== false) {
//                    $icon_class = $icon;
//                    break;
//                }
//            }
//
//            return sprintf(
//                '<a href="%s" target="_blank" rel="noopener noreferrer"><i class="fa %s" aria-hidden="true"></i><span class="sr-only">%s</span></a>',
//                esc_url($link),
//                esc_attr($icon_class),
//                esc_html($host)
//            );
//        }
//    }
//
//    global $post;
//    $social_networks = get_post_meta($post->ID, 'social_networks_links', true);
//
//    if (empty($social_networks) || !is_array($social_networks)) {
//        return '';
//    }
//
//    $links = array_map(function($network) {
//        if (isset($network['social_network_name']) && !empty($network['social_network_name'])) {
//            return DynamicFuncForSocialLink($network['social_network_name']);
//        }
//        return '';
//    }, $social_networks);
//
//    $links = array_filter($links);
//
//    return '<span class="sdstudio_social_links">'.implode(' ', $links).'</span>';
//}
//
//add_shortcode('sdstudio_social_links', 'sdstudio_social_links');


function sdstudio_social_links() {
    if (!function_exists('DynamicFuncForSocialLink')) {
        function DynamicFuncForSocialLink($link)
        {
            $social_icons = array(
                'facebook.com' => 'fa-facebook',
                'twitter.com' => 'fa-twitter',
                'x.com' => 'fa-x-twitter',
                'linkedin.com' => 'fa-linkedin',
                'instagram.com' => 'fa-instagram',
                'youtube.com' => 'fa-youtube',
                'pinterest.com' => 'fa-pinterest',
                'tiktok.com' => 'fa-tiktok',
                'snapchat.com' => 'fa-snapchat',
                'tumblr.com' => 'fa-tumblr',
                'reddit.com' => 'fa-reddit',
                'whatsapp.com' => 'fa-whatsapp',
                'telegram.org' => 'fa-telegram',
                'medium.com' => 'fa-medium',
                'github.com' => 'fa-github',
                'gitlab.com' => 'fa-gitlab',
                'bitbucket.org' => 'fa-bitbucket',
                'stackoverflow.com' => 'fa-stack-overflow',
                'dribbble.com' => 'fa-dribbble',
                'behance.net' => 'fa-behance',
                'flickr.com' => 'fa-flickr',
                'vimeo.com' => 'fa-vimeo',
                'twitch.tv' => 'fa-twitch',
                'discord.com' => 'fa-discord',
                'slack.com' => 'fa-slack',
                'spotify.com' => 'fa-spotify',
                'soundcloud.com' => 'fa-soundcloud',
                'last.fm' => 'fa-lastfm',
                'vk.com' => 'fa-vk',
                'weibo.com' => 'fa-weibo',
                'ok.ru' => 'fa-odnoklassniki',
                'xing.com' => 'fa-xing',
                'meetup.com' => 'fa-meetup',
                'quora.com' => 'fa-quora',
                'goodreads.com' => 'fa-goodreads',
                'yelp.com' => 'fa-yelp',
                'tripadvisor.com' => 'fa-tripadvisor',
                'producthunt.com' => 'fa-product-hunt',
                'deviantart.com' => 'fa-deviantart',
                'angel.co' => 'fa-angellist',
                'slideshare.net' => 'fa-slideshare',
                'foursquare.com' => 'fa-foursquare',
                'skype.com' => 'fa-skype',
                'researchgate.net' => 'fa-researchgate',
                'academia.edu' => 'fa-academia',
                'kaggle.com' => 'fa-kaggle',
                'mastodon.social' => 'fa-mastodon',
                'strava.com' => 'fa-strava',
                'patreon.com' => 'fa-patreon',
                'etsy.com' => 'fa-etsy',
                'bandcamp.com' => 'fa-bandcamp',
                'paypal.com' => 'fa-paypal',
                'imdb.com' => 'fa-imdb',
                'tiktok.com' => 'fa-tiktok'
            );

            $parsed_url = parse_url($link);
            $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

            $icon_class = 'fa-link';
            foreach ($social_icons as $domain => $icon) {
                if (strpos($host, $domain) !== false) {
                    $icon_class = $icon;
                    break;
                }
            }

            return sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer"><i class="fa %s" aria-hidden="true"></i><span class="sr-only">%s</span></a>',
                esc_url($link),
                esc_attr($icon_class),
                esc_html($host)
            );
        }
    }

    global $post;
    $social_networks = get_post_meta($post->ID, 'social_networks_links', true);

    if (empty($social_networks) || !is_array($social_networks)) {
        return '';
    }

    $links = array_map(function($network) {
        if (isset($network['social_network_name']) && !empty($network['social_network_name'])) {
            return DynamicFuncForSocialLink($network['social_network_name']);
        }
        return '';
    }, $social_networks);

    $links = array_filter($links);

    return '<span class="sdstudio_social_links">'.implode(' ', $links).'</span>';
}

function sdstudio_separate_social_links() {
    if (!function_exists('DynamicFuncForSocialLink')) {
        function DynamicFuncForSocialLink($link)
        {
            $social_icons = array(
                'facebook.com' => 'fa-facebook',
                't.me' => 'fa-telegram',
                'twitter.com' => 'fa-twitter',
                'x.com' => 'fa-x-twitter',
                'linkedin.com' => 'fa-linkedin',
                'instagram.com' => 'fa-instagram',
                'youtube.com' => 'fa-youtube',
                'pinterest.com' => 'fa-pinterest',
                'tiktok.com' => 'fa-tiktok',
                'snapchat.com' => 'fa-snapchat',
                'tumblr.com' => 'fa-tumblr',
                'reddit.com' => 'fa-reddit',
                'whatsapp.com' => 'fa-whatsapp',
                'telegram.org' => 'fa-telegram',
                'medium.com' => 'fa-medium',
                'github.com' => 'fa-github',
                'gitlab.com' => 'fa-gitlab',
                'bitbucket.org' => 'fa-bitbucket',
                'stackoverflow.com' => 'fa-stack-overflow',
                'dribbble.com' => 'fa-dribbble',
                'behance.net' => 'fa-behance',
                'flickr.com' => 'fa-flickr',
                'vimeo.com' => 'fa-vimeo',
                'twitch.tv' => 'fa-twitch',
                'discord.com' => 'fa-discord',
                'slack.com' => 'fa-slack',
                'spotify.com' => 'fa-spotify',
                'soundcloud.com' => 'fa-soundcloud',
                'last.fm' => 'fa-lastfm',
                'vk.com' => 'fa-vk',
                'weibo.com' => 'fa-weibo',
                'ok.ru' => 'fa-odnoklassniki',
                'xing.com' => 'fa-xing',
                'meetup.com' => 'fa-meetup',
                'quora.com' => 'fa-quora',
                'goodreads.com' => 'fa-goodreads',
                'yelp.com' => 'fa-yelp',
                'tripadvisor.com' => 'fa-tripadvisor',
                'producthunt.com' => 'fa-product-hunt',
                'deviantart.com' => 'fa-deviantart',
                'angel.co' => 'fa-angellist',
                'slideshare.net' => 'fa-slideshare',
                'foursquare.com' => 'fa-foursquare',
                'skype.com' => 'fa-skype',
                'researchgate.net' => 'fa-researchgate',
                'academia.edu' => 'fa-academia',
                'kaggle.com' => 'fa-kaggle',
                'mastodon.social' => 'fa-mastodon',
                'strava.com' => 'fa-strava',
                'patreon.com' => 'fa-patreon',
                'etsy.com' => 'fa-etsy',
                'bandcamp.com' => 'fa-bandcamp',
                'paypal.com' => 'fa-paypal',
                'imdb.com' => 'fa-imdb',
                'tiktok.com' => 'fa-tiktok'
            );

            $parsed_url = parse_url($link);
            $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

            $icon_class = 'fa-link';
            foreach ($social_icons as $domain => $icon) {
                if (strpos($host, $domain) !== false) {
                    $icon_class = $icon;
                    break;
                }
            }

            return sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer"><i class="fa %s" aria-hidden="true"></i><span class="sr-only">%s</span></a>',
                esc_url($link),
                esc_attr($icon_class),
                esc_html($host)
            );
        }
    }

    global $post;
    $social_networks = array();
    $meta_keys = array(
        'soc_facebook' => 'facebook',
        'soc_instagram' => 'instagram',
        'soc_twitter' => 'twitter',
        'soc_linkedin' => 'linkedin',
        'soc_telegram' => 'telegram',
        'soc_sciprofiles' => 'sciprofiles'
    );

    foreach ($meta_keys as $meta_key => $network_name) {
        $value = get_post_meta($post->ID, $meta_key, true);
        if (!empty($value)) {
            $social_networks[] = array(
                'social_network_name' => $value
            );
        }
    }

    if (empty($social_networks) || !is_array($social_networks)) {
        return '';
    }

    $links = array_map(function($network) {
        if (isset($network['social_network_name']) && !empty($network['social_network_name'])) {
            return DynamicFuncForSocialLink($network['social_network_name']);
        }
        return '';
    }, $social_networks);

    $links = array_filter($links);

    return '<span class="sdstudio_social_links">'.implode(' ', $links).'</span>';
}

add_shortcode('sdstudio_social_links', 'sdstudio_social_links');
add_shortcode('sdstudio_separate_social_links', 'sdstudio_separate_social_links');
/****
 * Тайм лайн для постів
 */

function timeline_shortcode($atts) {

    $atts = shortcode_atts(array(
        'posts_per_page' => 4,
        'post_type' => 'advertisement',
        'category' => '',
    ), $atts);

    $current_date = current_time('Y-m-d H:i');

    $args = array(
        'posts_per_page' => $atts['posts_per_page'],
        'post_type' => $atts['post_type'],
        'meta_key' => 'data_podii',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_type' => 'DATETIME',
        'meta_query' => array(
            array(
                'key' => 'data_podii',
                'value' => $current_date,
                'compare' => '>=',
                'type' => 'DATETIME'
            ),
        ),
    );


    // Додаємо фільтр по кастомній таксономії, якщо вказана категорія
    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'categories_of_events',
                'field'    => 'name',
                'terms'    => $atts['category'],
            ),
        );

    }

    $query = new WP_Query($args);

    // Початок виводу HTML
    $output = '<div class="jet-hor-timeline-track">';
    $output .= '<div class="jet-hor-timeline-list jet-hor-timeline-list--top jet-hor-timeline--scroll-bar">';

    // Верхня частина таймлайну
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();
            $post_url = get_the_permalink(get_the_ID());
            $title = get_the_title();
            $content = wp_trim_words(get_the_content(), 30);
            $title_speaker = 'Cпікер: ';
            $val_speaker = get_post_meta($post_id, 'spiker', true); // Отримуємо дату з мета поля
            $title_organizer = 'Організатор: ';
            $val_organizer = get_post_meta($post_id, 'organizator', true);
            $title_to_register = 'Для реєстрації тисни тут...';
            $val_to_register = get_post_meta($post_id, 'posilannia_na_reiestratsiiu', true);
            $title_to_post = 'Детальніше';

            global $sitepress;
            if ($sitepress){
                if ( function_exists('icl_object_id') ) {
                    if (ICL_LANGUAGE_CODE=='en'){
                        $title_speaker = 'Speaker: ';
                        $title_organizer = 'Organizer: ';
                        $title_to_register = 'To register, click here...';
                        $title_to_post = 'More details';
                    }
                }
            }


            // Вивід картки
            $output .= '
            <div class="jet-hor-timeline-item elementor-repeater-item-' . esc_attr($post_id) . '">
                <div class="jet-hor-timeline-item__card">
                    <div class="jet-hor-timeline-item__card-inner">
                        <h5 class="jet-hor-timeline-item__card-title">' . esc_html($title) . '</h5>
                        <div class="jet-hor-timeline-item__card-desc">' . esc_html($content) . '</div>';
if (isset($val_speaker) && !empty($val_speaker)){
    $output .= '        <div class="jet-hor-timeline-item__card-speaker"><strong>' . esc_html($title_speaker) .'</strong><br>'.esc_html($val_speaker)  .'</div>';
}
if (isset($val_organizer) && !empty($val_organizer)){
    $output .= '        <div class="jet-hor-timeline-item__card-speaker"><strong>' . esc_html($title_organizer) .'</strong><br>'.esc_html($val_organizer)  .'</div>';
}
if (isset($val_to_register) && !empty($val_to_register)){
    $output .= '        <br><div class="jet-hor-timeline-item__card-speaker"><strong><a href="'.esc_html($val_to_register).'"><i class="fas fa-check-square"></i> ' . esc_html($title_to_register) .'</a></strong></div>';
}
            $output .= '        <br><div class="jet-hor-timeline-item__card-speaker"><strong><a href="'.esc_html($post_url).'" target="_blank"><i class="fa fa-solid fa-eye"></i> ' . esc_html($title_to_post) .'</a></strong></div>';
            $output .= '        </div>
                    <div class="jet-hor-timeline-item__card-arrow" style="margin-left: 10px;"></div>
                </div>
            </div>';
        endwhile;
        wp_reset_postdata();
    else :
        $output .= '<p>Публікацій не знайдено</p>';
    endif;

    $output .= '</div>'; // Закриття верхнього списку таймлайну

    // Середня частина таймлайну з іконками
    $output .= '<div class="jet-hor-timeline-list jet-hor-timeline-list--middle"><div class="jet-hor-timeline__line" style="left: 45px; width: 100%;"></div>';
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();

            // Вивід іконки таймлайну
            $output .= '
            <div class="jet-hor-timeline-item elementor-repeater-item-' . esc_attr($post_id) . '">
                <div class="jet-hor-timeline-item__point">
                    <div class="jet-hor-timeline-item__point-content">
                        <span class="jet-elements-icon">
                            <i aria-hidden="true" class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                </div>
            </div>';
        endwhile;
        wp_reset_postdata();
    endif;
    $output .= '</div>'; // Закриття середнього списку таймлайну

// Нижня частина таймлайну з датами
    $output .= '<div class="jet-hor-timeline-list jet-hor-timeline-list--bottom">';
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();
            $meta_date = get_post_meta($post_id, 'data_podii', true); // Отримуємо дату з мета поля

            // Перетворення формату дати
            $date_object = DateTime::createFromFormat('Y-m-d\TH:i', $meta_date);
            if ($date_object) {
                // Використання wp_date() для локалізованого форматування дати
                $formatted_date = wp_date('l, F j, Y H:i', $date_object->getTimestamp());
            } else {
                $formatted_date = $meta_date; // Якщо перетворення не вдалось, вивести оригінал
            }

            // Вивід дати з мета поля data_podii
            $output .= '
        <div class="jet-hor-timeline-item elementor-repeater-item-' . esc_attr($post_id) . '">
            <div class="jet-hor-timeline-item__meta">' . esc_html($formatted_date) . '</div>
        </div>';
        endwhile;
        wp_reset_postdata();
    endif;

    $output .= '</div>'; // Закриття нижнього списку таймлайну

    $output .= '</div>'; // Закриття основного контейнера

    return $output;
}
add_shortcode('timeline', 'timeline_shortcode');


/****
 * [sds_projects_announcements]
 * spiker
 * organizator
 * language
 * posilannia_na_reiestratsiiu
 * posilannia_na_checklist
 * posilannia_na_presentation
 * data_podii
 */


function sds_projects_announcements() {
    // Get current post ID
    $post_id = get_the_ID();

    // Add custom CSS
    add_action('wp_head', function() {
        echo '<style>
            .sds-shirt-ev-button {
                display: inline-flex;
                align-items: center;
                padding: 8px 16px;
                background-color: #F26B3C;
                color: white !important;
                text-decoration: none;
                border-radius: 4px;
                margin: 5px 0;
                font-weight: 500;
                transition: background-color 0.3s ease;
            }
            .sds-shirt-ev-button:hover {
                background-color: #d85a2f;
                text-decoration: none;
            }
            .sds-shirt-ev-button i {
                margin-right: 8px;
            }
            .sds-shirt-ev-metadata-item {
                margin: 10px 0;
            }
            a.sds-shirt-ev-button.external.external_icon {
                border-bottom-color: #de6906;
                background-color: #F07939;
                color: #ffffff;
                padding: 8px 22px 10px 22px;
                font-size: 14px;
                margin-top:15px !important;
                display:block;
                border-radius: 100px;
            }
            a.sds-shirt-ev-button.external.external_icon:hower{
                background-color: #ffffff;
                color: #F07939;
            } 
        </style>';
    });

    // Set default Ukrainian labels
    $title_speaker = 'Спікер:';
    $title_organizer = 'Організатор:';
    $title_language = 'Мова:';
    $title_event_date = 'Дата події:';
    $title_event_completed = 'Завершено:';
    $button_register = 'Зареєструватися';
    $button_checklist = 'Завантажити чекліст';
    $button_presentation = 'Переглянути презентацію';
    $button_accession = 'Приєднатися';

    // Check for WPML and set English labels if needed
    global $sitepress;
    if ($sitepress) {
        if (function_exists('icl_object_id')) {
            if (ICL_LANGUAGE_CODE == 'en') {
                $title_speaker = 'Speaker:';
                $title_organizer = 'Organizer:';
                $title_language = 'Language:';
                $title_event_date = 'Event date:';
                $title_event_completed = 'Completed:';
                $button_register = 'Register';
                $button_checklist = 'Download checklist';
                $button_presentation = 'View presentation';
                $button_accession = 'Join';
            }
        }
    }

    // Get all metadata fields
    $spiker = get_post_meta($post_id, 'spiker', true);
    $organizator = get_post_meta($post_id, 'organizator', true);
    $language = get_post_meta($post_id, 'language', true);
    $registration_link = get_post_meta($post_id, 'posilannia_na_reiestratsiiu', true);
    $checklist_link = get_post_meta($post_id, 'posilannia_na_checklist', true);
    $presentation_link = get_post_meta($post_id, 'posilannia_na_presentation', true);
    $accession_link = get_post_meta($post_id, 'posilannia_na_accession', true);
    $event_date = get_post_meta($post_id, 'data_podii', true);

    // Format the date and check if event is past
    $formatted_date = '';
    $is_past_event = false;

    if (!empty($event_date)) {
        try {
            $date = new DateTime($event_date);
            $now = new DateTime();
            $formatted_date = $date->format('d.m.Y / H:i');
            $is_past_event = $date < $now;
        } catch (Exception $e) {
            // If date parsing fails, keep the original format
            $formatted_date = $event_date;
        }
    }

    // Start building output
    $output = '<div class="sds-shirt-ev-metadata">';

    // Add each field if it exists
    if (!empty($spiker)) {
        $output .= '<div class="sds-shirt-ev-metadata-item"><strong>' . esc_html($title_speaker) . '</strong> ' . esc_html($spiker) . '</div>';
    }

    if (!empty($organizator)) {
        $output .= '<div class="sds-shirt-ev-metadata-item"><strong>' . esc_html($title_organizer) . '</strong> ' . esc_html($organizator) . '</div>';
    }

    if (!empty($language)) {
        $output .= '<div class="sds-shirt-ev-metadata-item"><strong>' . esc_html($title_language) . '</strong> ' . esc_html($language) . '</div>';
    }

    if (!empty($formatted_date)) {
        $date_label = $is_past_event ? $title_event_completed : $title_event_date;
        $output .= '<div class="sds-shirt-ev-metadata-item"><strong>' . esc_html($date_label) . '</strong> ' . esc_html($formatted_date) . '</div>';
    }

    // Add links with proper formatting, but only if event is not past
    if (!$is_past_event) {
        if (!empty($registration_link)) {
            $output .= '<div class="sds-shirt-ev-metadata-item">
                <a href="' . esc_url($registration_link) . '" target="_blank" class="sds-shirt-ev-button">
                    <i class="fa fa-user-plus"></i> ' . esc_html($button_register) . '
                </a></div>';
        }

        if (!empty($accession_link)) {
            $output .= '<div class="sds-shirt-ev-metadata-item">
                <a href="' . esc_url($accession_link) . '" target="_blank" class="sds-shirt-ev-button">
                    <i class="fa fa-sign-in"></i> ' . esc_html($button_accession) . '
                </a></div>';
        }
    }

    if (!empty($checklist_link)) {
        $output .= '<div class="sds-shirt-ev-metadata-item">
            <a href="' . esc_url($checklist_link) . '" target="_blank" class="sds-shirt-ev-button">
                <i class="fa fa-list-check"></i> ' . esc_html($button_checklist) . '
            </a></div>';
    }

    if (!empty($presentation_link)) {
        $output .= '<div class="sds-shirt-ev-metadata-item">
            <a href="' . esc_url($presentation_link) . '" target="_blank" class="sds-shirt-ev-button">
                <i class="fa fa-file-powerpoint"></i> ' . esc_html($button_presentation) . '
            </a></div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('sds_projects_announcements', 'sds_projects_announcements');


/***
 * Просто відображаємо контент
 * [sdstudio_current_post_content_shortcode]
 */

function sdstudio_current_post_content_shortcode($atts)
{
    // Перевіряємо чи знаходимось в циклі WordPress
    global $post;
    $post_content = $post->post_content;

    // Застосовуємо фільтри WordPress для правильного форматування контенту
    $content = apply_filters('the_content', $post_content);

    // Повертаємо відформатований контент
    return $content;
}

add_shortcode('sdstudio_current_post_content_shortcode', 'sdstudio_current_post_content_shortcode');



