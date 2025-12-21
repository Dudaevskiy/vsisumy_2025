<?php
/**
 * Обработка всех ссылок из  постов категорий
 * то еесть добавляем каждой ссылке rel="follow" если его нет
 * и вставляем метаполе для отключения nooppener
 */


/**
 * Внимание данная функция заточена под обработку значения
 * @param $value
 * @return mixed
 */
function DEVupdate_all_posts_follow_links_for_partners_posts_wp_all_import($value){

    // Обязательно делаем замену перед обработкой
    $value = preg_replace('/alt=" width/','alt="" width',$value);

    /**
     * Идеальный вариант для тестирования HTML
     * контента перед импортом в
     * WP All Import
     *
     * Проверка HTML в SimpleXMLElement:
     * //ddd($xml->asXML());
     *
     * START
     */
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->strictErrorChecking = FALSE;
//    $doc->preserveWhitespace = FALSE;
    //$doc->loadHTML(mb_convert_encoding($xml, 'HTML-ENTITIES', 'UTF-8'));
    $dom->loadHTML(mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    $xml = simplexml_import_dom($dom);

//    s($xml);

    /**
     * Идеальный вариант для тестирования HTML
     * контента перед импортом в
     * WP All Import
     *
     * END
     */
    // ================================================================
    // ================================================================

    // Функция для получения текущего домена сайта
    if (!function_exists('siteURL')){
        function siteURL(){
            $https = $_SERVER['HTTPS'] ?? '';
            $port = $_SERVER['SERVER_PORT'] ?? 80;
            $protocol = ((!empty($https) && $https != 'off') || $port == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'] ?? '';
            return $protocol.$domainName;
        }
    }

    $blog_url = siteURL();

    $parse = parse_url($blog_url);  //$parse['host']
    $current_site_domain =  $parse['host'];


    // grab all <a> nodes with an href attribute
    foreach ($xml->xpath('//a[@href]') as $a)
    {
        // Парсим ссылку
        $parse = parse_url($a['href']);  //$parse['host']
        // Получаем домен
        $current_link_domain =  $parse['host'];

        // Если домен ссылки равен текущему домену пропускаем и переходим к следующему перебору ссылок
        // + Если у ссылки уже есть  follow - делаем то же самое что и выше
//        s('На входе - '.$a['rel']);
//        s(strpos($a['rel'], 'follow') !== false && strpos($a['rel'], 'nofollow') == false);
        if ($current_link_domain == $current_site_domain || strpos($a['rel'], 'nofollow') !== false)
        {
            $a['rel'] = str_replace('nofollow','follow',$a['rel']);
//            $a['rel'] = 'nofollow';
        }

        if ($current_link_domain == $current_site_domain || strpos($a['rel'], 'follow') !== false)
        {
            // skip all links that start with the URL in $blog_url, as long as they
            // don't start with the URL from $my_folder;
            continue;
        }

        if (empty($a['rel']))
        {
            $a['rel'] = 'follow';
//            $a['rel'] = 'nofollow';
        }
        else
        {
            $a['rel'] .= ' follow';
//            $a['rel'] .= ' nofollow';
        }

//        s($a['href']);
//        s('На ВЫХОДЕ - '.$a['rel']);
    }


//    ddd($xml->asXML());
//    $doc->loadHTML(mb_convert_encoding($xml, 'HTML-ENTITIES', 'UTF-8'));
//    ddd($dom>saveHTML());
//    $new_html = $dom->saveHTML();


    // ================================================================
    // ================================================================


    return $xml->asXML();

}