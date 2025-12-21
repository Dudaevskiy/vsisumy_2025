<?php

/**
 * [rama_last_news_friends rss_1="http://www.vsisumy.com/rss.xml" rss_2="http://www.ogo.vsisumy.com/ads/rss.xml" rss_3="http://www.dom.vsisumy.com/rss/realty" number="5"]
 * @param $atts
 * @return string
 * [categoryposts limit="10" thumb="false"]
 */
function rama_last_news_friends($atts)
{
    if (is_admin()) {
        return false;
    }


    // RSS 1
    $rss_1 = (int)$atts['rss_1'] ? $atts['rss_1'] : "http://www.vsisumy.com/rss.xml";
    // RSS 2
    $rss_2 = (int)$atts['rss_2'] ? $atts['rss_2'] : "http://www.ogo.vsisumy.com/ads/rss.xml";
    // RSS 3
    $rss_3 = (int)$atts['rss_3'] ? $atts['rss_3'] : "http://www.dom.vsisumy.com/rss/realty";
    // Количество постов для отображения
    $view = (int)$atts['view'] ? $atts['view'] : 5;

    // GEt Domain
    function GetRssDomain($rss_link)
    {
        $url = $rss_link;
        $parse = parse_url($url);
        $domain = str_replace("www.", "", $parse['host']); // prints 'google.com'
        return $domain;
    }


    // URL containing rss feed
    function parse_rss_feed($url, $max_count, $option)
    {
        //$url = "http://www.realbeta.net63.net/blog/rss?id=1";
        $max_count = (int)$max_count ? $max_count : 5;

        /**
         * Кешируем RSS
         */
        $name = str_replace(".", "_", GetRssDomain($url));
        $cache_key = 'rama_cache__rss' . $name;

        $xml = '';
        // Если нет кеша
        if (false === get_transient($cache_key)) {
            $xml = simplexml_load_file($url);
            if ($xml) {
                // Кешируем в временную опцию https://bit.ly/39CeoJd
                set_transient($cache_key, $xml->asXML(), 24 * HOUR_IN_SECONDS);
            } else {
                set_transient($cache_key, '', 24 * HOUR_IN_SECONDS);
            }

            // Если есть кеш
        } else {

            // Если PHP кеш есть, просто вернем HTML
            $cached = get_transient($cache_key);
            if (!empty($cached) || $cached !== "") {
                $xml = new SimpleXMLElement($cached);
            } else {
                $xml = false;
            }
        }

        if (!$xml) {
            $xml = false;
        }

        $html = '';

        // Только проверка
        if ($option == 'chechk') {

            if ($xml) {
                return true;
            } else {
                return false;
            }

            // Полное получение
        } else {

            if ($xml) {
                for ($i = 0; $i < $max_count; $i++) {
                    $title = $xml->channel->item[$i]->title;
                    $link = $xml->channel->item[$i]->link;
                    //            $description = $xml->channel->item[$i]->description;
                    $pubDate = $xml->channel->item[$i]->pubDate;
                    $now = new DateTime($pubDate);
                    $day = $now->format('d.m.Y');
                    $time = $now->format('H:i');
                    $data_join = $day . ', ' . $time;


                    $html .= "<a target='_blank' href='$link'>$title</a>"; // Title of post
                    //                $html .= "$description"; // Description
                    $html .= "$data_join<br /><br />"; // Date Published
                }
            } else {
                $html .= '';
            }


            if ($xml) {
                return "$html<br />";
            } else {
                return false;
            }
        }
    }


    $html = '<div class="block rama_widget_shortcode textwidget">';
    $html .= '<div class="bs-tab-shortcode">';
    $html .= '<ul class="nav nav-tabs" role="tablist">';

//dd(parse_rss_feed($rss_3, $view,'get'));

    if (parse_rss_feed($rss_1, 1, 'chechk')) {
        $html .= '<li class="active"><a href="#tab-rss1" data-toggle="tab">' . GetRssDomain($rss_1) . '</a></li>';
    }
//    if (parse_rss_feed($rss_2, 1, 'chechk')) {
//        $html .= '<li><a href="#tab-rss2" data-toggle="tab">' . GetRssDomain($rss_2) . '</a></li>';
//    }
    if (parse_rss_feed($rss_3, 1, 'chechk')) {
        $html .= '<li><a href="#tab-rss3" data-toggle="tab">' . GetRssDomain($rss_3) . '</a></li>';
    }
    $html .= '</ul>';


    $html .= '<div class="tab-content">';
    if (parse_rss_feed($rss_1, 1, 'chechk')) {
        $html .= '<div id="tab-rss1" class="active tab-pane">';
        $html .= '<div class="rama-last-posts">';
        $html .= parse_rss_feed($rss_1, $view, 'get');
        $html .= '<div class="all"><a href="http://'.GetRssDomain($rss_1).'" target="_blank">Усі новини</a></div>';
        $html .= '</div>';
        $html .= '</div>';
    }

//    if (parse_rss_feed($rss_2, 1, 'chechk')) {
//        $html .= '<div id="tab-rss2" class="tab-pane">';
//        $html .= '<div class="rama-last-posts">';
//        $html .= parse_rss_feed($rss_2, $view, 'get');
//        $html .= '<div class="all"><a href="http://'.GetRssDomain($rss_2).'" target="_blank">Усі новини</a></div>';
//        $html .= '</div>';
//        $html .= '</div>';
//    }
    if (parse_rss_feed($rss_3, 1, 'chechk')) {
        $html .= '<div id="tab-rss3" class="tab-pane">';
        $html .= '<div class="rama-last-posts">';
        $html .= parse_rss_feed($rss_3, 7, 'get');
        $html .= '<div class="all"><a href="http://'.GetRssDomain($rss_3).'" target="_blank">Усі новини</a></div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}


// Add a shortcode
add_shortcode('rama_last_news_friends', 'rama_last_news_friends');