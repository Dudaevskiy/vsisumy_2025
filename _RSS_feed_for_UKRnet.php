<?php



/**
 * Правила ниже срабатывают только для фида:
 * https://rama.com.ua/special-cat/rss-feed/feed/
 */
if ( isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == "/special-cat/rss-feed/feed/" ){

    /**
     * Удаляем iframe
     * @param $content
     * @return array|string|string[]|null
     */
    function edit_your_feed_content($content) {
        $content = preg_replace("/(<iframe.*?[^>]*>)(.*?)(<\/iframe>)/i", "", $content);
        return $content;
    }
    add_filter('the_content', 'edit_your_feed_content');


    /**
     * Обработка категорий
     * https://bit.ly/3HsiPp1
     */
    function dcg_tidy_rss_categories( $the_list ) {
        $categories = get_the_category();

        if ( ! empty( $categories ) ) {
            foreach ($categories as $category) {
// remove the following line if you don't want Categories to appear
//$the_list = str_replace("<category><![CDATA[{$category->name}]]></category>", "$category->name, ", $the_list);
                $the_list = 'Новини';
            }
        }

        return "<category><![CDATA[" . preg_replace('/\s+/S', " ", trim( rtrim( $the_list, ", \t\n" ) ) ) . "]]></category>\n";
    }
    add_filter( 'the_category_rss', 'dcg_tidy_rss_categories' );

    /**
     * RSS Title замена
     * @param $title
     * @return string
     */
    function dcg_tidy_rss_title( $title ) {
        return "<![CDATA[" . $title . "]]>";
    }
    add_filter( 'the_title_rss', 'dcg_tidy_rss_title' );


    /**
     * Content
     * Удаляем все лишнее
     */
    add_filter( 'the_content_feed', 'flipboard_slideshow' );

    function flipboard_slideshow( $content ) {
        global $post;
        $content = kama_excerpt( [ 'maxchar'=>1500, 'text'=>$post->post_content ,'save_tags' => '']  );
        return $content;
    }


    /**
     * Заменяем время и двту поста публикации
     * на дату и время согласно WP
     *
     * Другие полезности:
     * https://themes.artbees.net/blog/customizing-wordpress-rss-feed-with-code-the-easy-way/
     */
    add_filter( 'get_post_time', 'return_event_date_rss_2_feed_func', 10, 3 );

    function return_event_date_rss_2_feed_func( $time, $d, $gmt )
    {
        if (did_action('rss2_head')) {
            global $post;
            if (get_post_type() == 'post') {
                $time = $post->post_date;
            }
        }
        return $time;
    }

////    /**
////     * Вставляем все что угодно в rss поста
////     * @return void
////     */
////    function add_custom_fields_to_rss() {
////        if(get_post_type() == 'post') {
////            ? >
////<!--            <event-date>-->< ?php //echo 'ewwedwe'; ? ><!--</event-date>-->
////<!--            -->< ?php
////        }
////    }
//
//    add_action('rss2_item', 'add_custom_fields_to_rss');





    /** Заменяем +0000 на +0200
     * + title
     * @param $buffer
     * @return array|string|string[]|null
     */

    function callback_rss_fide_rama($buffer)
    {

        $buffer = preg_replace('/\+0000/','+0200',$buffer);
        $buffer = preg_replace('/&#8211;/m','-',$buffer);
        $buffer = preg_replace('/content:encoded/m','fulltext',$buffer);

        $buffer = preg_replace('/<div(.+?)>[\s\S]+?\/div>/','',$buffer);
        $buffer = preg_replace('/&#8220;/','“',$buffer);
        $buffer = preg_replace('/&#8221;/','”',$buffer);
        $buffer = preg_replace('/&#8230;/','...',$buffer);
        $buffer = preg_replace('/&#8217;/','`',$buffer);
        $buffer = preg_replace('/&#8243;/','″',$buffer);

//        $buffer = preg_replace("/<p[^>]*>/", "", $buffer);
//        $buffer = str_replace("</p>", "", $buffer);


        return $buffer;

    }
    function buffer_start_rss_fide_rama() { ob_start("callback_rss_fide_rama"); }
    function buffer_end_rss_fide_rama() { ob_end_flush(); }

    add_action('after_setup_theme', 'buffer_start_rss_fide_rama');
    add_action('shutdown', 'buffer_end_rss_fide_rama');


}

