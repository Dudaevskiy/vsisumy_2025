<?php

/**
 * Очистка transient
 */
function empty_all_transient_rama($post_ID)  {
    //do my stuff here;
    /**
     * Шорт код с блогерами
     *  [short_last_posts_authors_main_block]
     */
    delete_transient( 'rama_top_authors____shortcode_authors' );
    delete_transient( 'rama_top_authors____shortcode_authors_emails' );

    /**
     * Список последни постов
     * [rama_last_posts popular="true" exclude_cat_id="*****"]
     */
    delete_transient( 'cache_query__last_posts' );
    delete_transient( 'cache_query__popular' );
    delete_transient( 'cache_query__comments' );

    /**
     * TOP Slider
     */
    delete_transient( 'cache_template__rama_top_news' );

    return $post_ID;


}

add_action('save_post', 'empty_all_transient_rama');

//delete_transient( 'cache_query__last_posts' );
//delete_transient( 'empty_all_transient_if_change_posts__rama_top_authors____shortcode_authors' );
//delete_transient( 'cache_template__rama_top_news' );
//delete_transient( 'cache_query__comments' );