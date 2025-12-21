<?php


/// Announces ---------------------------------------------------------
add_filter('wp_dropdown_users', 'panorama_authors_list', 10 , 4);

function panorama_authors_list ($output, $args = array()) { //shows journalists only
    $output = "";

    $defaults = array(
        'show_option_all' => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
        'orderby' => 'display_name', 'order' => 'ASC',
        'include' => '', 'exclude' => '', 'multi' => 0,
        'show' => 'display_name', 'echo' => 1,
        'selected' => 0, 'name' => 'post_author_override', 'class' => '', 'id' => '',
        'blog_id' => $GLOBALS['blog_id'], 'who' => '', 'include_selected' => true
    );

    global $user_ID, $post;
    $defaults['selected'] = empty($post->ID) ? $user_ID : $post->post_author;

    $r = wp_parse_args( $args, $defaults );

    // Витягуємо змінні напряму замість використання extract()
    $show_option_all = $r['show_option_all'];
    $show_option_none = $r['show_option_none'];
    $hide_if_only_one_author = $r['hide_if_only_one_author'];
    $orderby = $r['orderby'];
    $order = $r['order'];
    $include = $r['include'];
    $exclude = $r['exclude'];
    $multi = $r['multi'];
    $show = $r['show'];
    $echo = $r['echo'];
    $selected = $r['selected'];
    $name = $r['name'];
    $class = $r['class'];
    $id = $r['id'];
    $blog_id = $r['blog_id'];
    $who = $r['who'];
    $include_selected = $r['include_selected'];

    $query_args = wp_array_slice_assoc( $r, array( 'blog_id', 'include', 'exclude', 'orderby', 'order', 'who' ) );
    $query_args['fields'] = array( 'ID', $show );

    $users1 = get_users( array( 'role' => 'administrator', 'orderby' => 'display_name' ) );//get_users( $query_args );
    $users2 = get_users( array( 'role' => 'editor', 'orderby' => 'display_name' ) );//get_users( $query_args );
    $users3 = get_users( array( 'role' => 'journalist', 'orderby' => 'display_name' ) );//get_users( $query_args );
    $users = array_merge($users1, $users2, $users3);
    $output = '';
    if ( !empty($users) && ( empty($hide_if_only_one_author) || count($users) > 1 ) ) {
        $name = esc_attr( $name );
        if ( $multi && ! $id )
            $id = '';
        else
            $id = $id ? " id='" . esc_attr( $id ) . "'" : " id='$name'";

        $output = "<select name='{$name}'{$id} class='$class'>\n";

        if ( $show_option_all )
            $output .= "\t<option value='0'>$show_option_all</option>\n";

        if ( $show_option_none ) {
            $_selected = selected( -1, $selected, false );
            $output .= "\t<option value='-1'$_selected>$show_option_none</option>\n";
        }

        $found_selected = false;
        foreach ( (array) $users as $user ) {
            $user->ID = (int) $user->ID;
            $_selected = selected( $user->ID, $selected, false );
            if ( $_selected )
                $found_selected = true;
            $display = !empty($user->$show) ? $user->$show : '('. $user->user_login . ')';
            $output .= "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
        }

        if ( $include_selected && ! $found_selected && ( $selected > 0 ) ) {
            $user = get_userdata( $selected );
            $_selected = selected( $user->ID, $selected, false );
            $display = !empty($user->$show) ? $user->$show : '('. $user->user_login . ')';
            $output .= "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
        }

        $output .= "</select>";
    }

    return $output;
}




function fields_box_announce(){
    global $post;
    $custom = get_post_custom($post->ID);
    ?>
    <p>
        <label><?php _e('Номер газеты:','panorama') ?></label>
        <input type="text" size="5" name="newspaper_nomer" value="<?php echo $custom["_newspaper_nomer"][0]; ?>" />
    </p>
    <p>
        <label><?php _e('Номер газеты (общий):','panorama') ?></label>
        <input type="text" size="5" name="newspaper_nomer_global" value="<?php echo $custom["_newspaper_nomer_global"][0]; ?>" />
    </p>
    <?php
}

add_action("admin_init", "add_articles_box", 1);
function add_articles_box() {
    add_meta_box( "add_articles", "Добавление статей к анонсу", "articles_box", "announce", "normal", "high" );
    add_meta_box( "extra_fields", "Дополнительные поля", "fields_box_announce", "announce", "normal", "high" );
}
function articles_box(){
    global $post;
    $custom = get_post_custom($post->ID);
    //print_r($custom);
    //$project_link = $custom["project_link"][0];
    //$art_items_count = $custom["art_items_count"][0]
    ?>
    <script type="text/javascript">
        var art_count = 0, art_sort_list;

        jQuery(document).ready(function(){
            art_count = jQuery('#add_articles_list').children().length | 0;
            art_sort_list = jQuery('#add_articles_list').sortable({
                placeholder: "ui-state-highlight",
                update: updateItems
            });
        });
        function addNewItem () {
            art_count++;
            jQuery('#add_articles_list').append(getNewItem());
            art_sort_list.sortable("refresh");
        }
        function getNewItem () {
            var art_template = jQuery('#art_template');
            var html = art_template.html();
            html = html.replace(/art_item_temp/ig, 'art_item_' + art_count);
            return html;
        }
        function deleteAnnArticle (btn) {
            if (!confirm('Удалить статью?')) return;
            jQuery(btn).closest('.articles_item').fadeOut(400,function(){
                jQuery(this).remove()
                updateItems();
                art_sort_list.sortable("refresh");
            });
        }
        function updateItems () {
            var art_list = jQuery('#add_articles_list');
            var output = '';
            art_list.children().each(function(ind){
                output += '<div id="art_item_' + (ind + 1) + '" class="articles_item">';
                output += jQuery(this).html().replace(/art_item_[0-9]+/ig, 'art_item_' + (ind + 1));
                output += '</div>';
            });
            art_list.html(output);
            art_count = art_list.children().length;
        }
    </script>
    <style>
        #add_articles_list .ui-state-highlight { height: 235px; background-color:#FFEEC6;}
        #add_articles_list .articles_item { background-color:#F7F7F7; border-bottom: 1px solid #DFDFDF; cursor: move; }
    </style>
    <div id="add_articles_cont">
        <div id="add_articles_list">
            <?php
            $i = 1;
            while (isset($custom["_art_item_".$i."_title"][0])) { ?>
                <div id="art_item_<?php echo $i ?>" class="articles_item">
                    <p style="float:left;padding-right: 10px;">
                        <label><?php _e('Рубрика','panorama') ?></label>
                        <?php wp_dropdown_categories(array('child_of' => 4, 'hide_empty' => 0, 'name' => 'art_item_'.$i.'_cat', 'selected' => $custom["_art_item_".$i."_cat"][0], 'hierarchical' => true, 'show_option_none' => __('Выберите категорию', 'panorama'))); ?>
                    </p>
                    <p style="float:left;padding-right: 10px;">
                        <label><?php _e('Префикс страницы','panorama') ?></label>
                        <select name="art_item_<?php echo $i ?>_group" style="width:50px">
                            <option value="A"<?php echo ($custom["_art_item_".$i."_group"][0] == 'A') ? ' selected="selected"' : '' ?>>A</option>
                            <option value="B"<?php echo ($custom["_art_item_".$i."_group"][0] == 'B') ? ' selected="selected"' : '' ?>>B</option>
                            <option value="C"<?php echo ($custom["_art_item_".$i."_group"][0] == 'C') ? ' selected="selected"' : '' ?>>C</option>
                            <option value="D"<?php echo ($custom["_art_item_".$i."_group"][0] == 'D') ? ' selected="selected"' : '' ?>>D</option>
                            <option value="E"<?php echo ($custom["_art_item_".$i."_group"][0] == 'E') ? ' selected="selected"' : '' ?>>E</option>
                            <option value="F"<?php echo ($custom["_art_item_".$i."_group"][0] == 'F') ? ' selected="selected"' : '' ?>>F</option>
                        </select>
                    </p>
                    <p style="float:left;padding-right: 10px;">
                        <label><?php _e('Номер страницы','panorama') ?></label>
                        <input type="text" size="5" name="art_item_<?php echo $i ?>_nomer" value="<?php echo $custom["_art_item_".$i."_nomer"][0] ?>" />
                    </p>
                    <div class="clear"></div>
                    <p>
                        <label><?php _e('Заголовок','panorama') ?></label>
                        <input type="text" size="60" name="art_item_<?php echo $i ?>_title" value="<?php echo $custom["_art_item_".$i."_title"][0] ?>" />
                    </p>
                    <p>
                        <label><?php _e('Описание','panorama') ?></label>
                        <textarea type="text" size="60" name="art_item_<?php echo $i ?>_descr" style="height: 60px;vertical-align: baseline;width: 329px;"><?php echo $custom["_art_item_".$i."_descr"][0] ?></textarea>
                    </p>
                    <p>
                        <label><?php _e('Автор','panorama'); ?></label>
                        <?php echo panorama_authors_list('', array( 'name' => 'art_item_'.$i.'_author', 'selected' => $custom["_art_item_".$i."_author"][0])); ?>
                    </p>
                    <p><a href="/" onclick="deleteAnnArticle(this); return false"><?php _e('Удалить статью','panorama')?></a></p>
                </div>
                <?php $i++; }?>
        </div>

        <div id="art_template" style="display:none">

            <div id="art_item_temp" class="articles_item">
                <p style="float:left;padding-right: 10px;">
                    <label><?php _e('Рубрика','panorama') ?></label>
                    <?php wp_dropdown_categories(array('child_of' => 4, 'hide_empty' => 0, 'name' => 'art_item_temp_cat', 'hierarchical' => true, 'show_option_none' => __('Выберите категорию', 'panorama'))); ?>
                </p>
                <p style="float:left;padding-right: 10px;">
                    <label><?php _e('Префикс страницы','panorama') ?></label>
                    <select name="art_item_temp_group" style="width:50px">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                    </select>
                </p>
                <p style="float:left;padding-right: 10px;">
                    <label><?php _e('Номер страницы','panorama') ?></label>
                    <input type="text" size="5" name="art_item_temp_nomer" value="" />
                </p>
                <div class="clear"></div>
                <p>
                    <label><?php _e('Заголовок','panorama') ?></label>
                    <input type="text" size="60" name="art_item_temp_title" value="" />
                </p>
                <p>
                    <label><?php _e('Описание','panorama') ?></label>
                    <textarea type="text" size="60" name="art_item_temp_descr" style="height: 60px;vertical-align: baseline;width: 329px;"></textarea>
                </p>
                <p>
                    <label><?php _e('Автор','panorama') ?></label>
                    <?php echo panorama_authors_list('', array( 'name' => 'art_item_temp_author')); ?>
                </p>
                <p><a href="/" onclick="deleteAnnArticle(this); return false"><?php _e('Удалить статью','panorama')?></a></p>
            </div>

        </div>
        <p><input type="button" value="Добавить статью" id="add_art_item_button" class="button" onclick="addNewItem()"></p>
    </div>

    <?php
}

add_action('save_post', 'save_added_articles', 0);
function save_added_articles( $post_id ){
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false; // выходим если это автосохранение
    if ( !current_user_can('edit_post', $post_id) ) return false; // выходим если юзер не имеет право редактировать запись

    // Nonce перевірка (потрібно додати wp_nonce_field до meta box форми)
    // if (!isset($_POST['announce_articles_nonce']) || !wp_verify_nonce($_POST['announce_articles_nonce'], 'save_announce_articles')) return false;

    if (!isset($_POST["newspaper_nomer"]) || !isset($_POST["newspaper_nomer_global"])) return false;
    global $post;
    update_post_meta($post_id, "_newspaper_nomer", sanitize_text_field($_POST["newspaper_nomer"]));
    update_post_meta($post_id, "_newspaper_nomer_global", sanitize_text_field($_POST["newspaper_nomer_global"]));
    $i = 1;
    while (isset($_POST["art_item_".$i."_title"])) {
        update_post_meta($post_id, "_art_item_".$i."_cat", sanitize_text_field($_POST["art_item_".$i."_cat"]));
        update_post_meta($post_id, "_art_item_".$i."_group", sanitize_text_field($_POST["art_item_".$i."_group"]));
        update_post_meta($post_id, "_art_item_".$i."_nomer", sanitize_text_field($_POST["art_item_".$i."_nomer"]));
        update_post_meta($post_id, "_art_item_".$i."_title", sanitize_text_field($_POST["art_item_".$i."_title"]));
        update_post_meta($post_id, "_art_item_".$i."_descr", wp_kses_post($_POST["art_item_".$i."_descr"]));
        update_post_meta($post_id, "_art_item_".$i."_author", sanitize_text_field($_POST["art_item_".$i."_author"]));
        $i++;
    }
    $custom = get_post_custom($post_id);
    while (isset($custom["_art_item_".$i."_title"])) {
        delete_post_meta($post_id, "_art_item_".$i."_cat");
        delete_post_meta($post_id, "_art_item_".$i."_group");
        delete_post_meta($post_id, "_art_item_".$i."_nomer");
        delete_post_meta($post_id, "_art_item_".$i."_title");
        delete_post_meta($post_id, "_art_item_".$i."_descr");
        delete_post_meta($post_id, "_art_item_".$i."_author");
        $i++;
    }
}

/// END Announces ---------------------------------------------------------

    /**
     * Nooppener - Отключение
     */
    //remove noreferrer on the frontend, but will still show in the editor
    function im_formatter($content) {
        if (is_admin()){
            return;
        }
        global $post;
        $meta = get_post_meta($post->ID);
        $vimknuti_relquotnooppenerquot = isset($meta['vimknuti-relquotnooppenerquot'][0]) ? $meta['vimknuti-relquotnooppenerquot'][0] : false;

    $content = str_replace('dofollow nofollow', 'dofollow', $content);
 
		
        if ($vimknuti_relquotnooppenerquot == true){
    //        $replace = array(" noreferrer" => "" ,"noreferrer " => "");
            $new_content = str_replace(" noopener", "",$content);
            $new_content = str_replace("noopener ", "",$new_content);
            $new_content = str_replace('rel="noopener"', "",$new_content);
            return $new_content;
        } else {
            return $content;
        }

    }
    add_filter('the_content', 'im_formatter', 999);