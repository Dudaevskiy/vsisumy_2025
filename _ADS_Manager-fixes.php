<?php
if ( !function_exists( 'get_current_screen' ) ) {
    require_once ABSPATH . '/wp-admin/includes/screen.php';
}
global $pagenow; // "edit.php"
$post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : ''; // "better-banner"
$paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1; // "1"

if ($pagenow == "edit.php" && $post_type == "better-banner"){

    /**
     * https://wp-kama.ru/hook/manage_-post_type-_posts_columns
     */
    // Добавляем стили для зарегистрированных колонок. Необязательно.
    add_action( 'admin_print_footer_scripts-edit.php', function () {
        // Swithers https://webdesign.tutsplus.com/tutorials/toggle-switch-component-with-css-checkbox-hack--cms-35011
        ?>
        <style>
            body.post-type-better-banner ol {
                list-style: none;
            }

            body.post-type-better-banner label {
                cursor: pointer;
            }

            body.post-type-better-banner [type="checkbox"] {
                position: absolute;
                left: -9999px;
            }


            body.post-type-better-banner .switches {
                max-width: 500px;
                width: 95%;
                margin: 50px auto 0;
                border-radius: 5px;
                color: white;
                /*   background: var(--blue); */
            }

            body.post-type-better-banner .switches li {
                position: relative;
                counter-increment: switchCounter;
            }

            body.post-type-better-banner .switches li:not(:last-child) {
                border-bottom: 1px solid gray;
            }

            body.post-type-better-banner .switches li::before {
                content: counter(switchCounter);
                position: absolute;
                top: 50%;
                left: -30px;
                transform: translateY(-50%);
                font-size: 2rem;
                font-weight: bold;
                color: pink;
                display: none;
            }

            body.post-type-better-banner .switches label {
                display: flex;
                align-items: center;
                justify-content: initial;
                padding-bottom: 20px;
                margin-top: -35px;
            }

            body.post-type-better-banner .switches span:last-child {
                position: relative;
                width: 50px;
                height: 26px;
                border-radius: 15px;
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.4);
                background: #8080808c;
                transition: all 0.3s;
            }

            body.post-type-better-banner .switches span:last-child::before,
            body.post-type-better-banner .switches span:last-child::after {
                content: "";
                position: absolute;
            }

            body.post-type-better-banner .switches span:last-child::before {
                left: 1px;
                top: 1px;
                width: 24px;
                height: 24px;
                background: var(--white);
                border-radius: 50%;
                z-index: 1;
                transition: transform 0.3s;
            }

            body.post-type-better-banner .switches span:last-child::after {
                top: 50%;
                right: 8px;
                width: 12px;
                height: 12px;
                transform: translateY(-50%);
                background: url("data:image/svg+xml;base64,PHN2ZyBzdHlsZT0iZmlsbDpyZ2JhKDAsMCwwLC4zKTsiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiB2aWV3Qm94PSIwIDAgMjQgMjQiPjxwYXRoIGQ9Ik0yNCAyMC4xODhsLTguMzE1LTguMjA5IDguMi04LjI4Mi0zLjY5Ny0zLjY5Ny04LjIxMiA4LjMxOC04LjMxLTguMjAzLTMuNjY2IDMuNjY2IDguMzIxIDguMjQtOC4yMDYgOC4zMTMgMy42NjYgMy42NjYgOC4yMzctOC4zMTggOC4yODUgOC4yMDN6Ii8+PC9zdmc+");
                background-size: 12px 12px;
            }

            body.post-type-better-banner .switches [type="checkbox"]:checked + label span:last-child {
                background: green;
            }

            body.post-type-better-banner .switches [type="checkbox"]:checked + label span:last-child::before {
                transform: translateX(24px);
            }

            body.post-type-better-banner .switches [type="checkbox"]:checked + label span:last-child::after {
                width: 14px;
                height: 14px;
                /*right: auto;*/
                left: 8px;
                background-image: url("data:image/svg+xml;base64,PHN2ZyBmaWxsPSJ3aGl0ZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTkgMjEuMDM1bC05LTguNjM4IDIuNzkxLTIuODcgNi4xNTYgNS44NzQgMTIuMjEtMTIuNDM2IDIuODQzIDIuODE3eiIvPjwvc3ZnPg==");
                background-size: 14px 14px;
            }

            @media screen and (max-width: 600px) {
                .switches li::before {
                    display: none;
                }
            }
        </style>
        <script>
            /**
             * Вставка чекбоксов
             * @type {*|jQuery|HTMLElement}
             */
            let BetterIconsColumn = jQuery('.type-better-banner td.format.column-format');
            BetterIconsColumn.each(function($el){
                jQuery(this).find('ol.switches').remove();
                let PostID = jQuery(this).closest('tr.iedit').attr('id');
                let PostStatus = jQuery(this).closest('tr.iedit').attr('class');
                PostStatus = PostStatus.split('status-')[1];
                PostStatus = PostStatus.split(' ')[0];
                console.log(PostStatus);
                let html_switches = '<ol class="switches"><li><input type="checkbox" id="'+PostID+'-'+$el+'" data-status="'+PostStatus+'"><label for="'+PostID+'-'+$el+'"><span></span></label></li></ol>';

                if (PostStatus === 'publish'){
                    html_switches = '<ol class="switches"><li><input type="checkbox" id="'+PostID+'-'+$el+'" data-status="'+PostStatus+'" checked><label for="'+PostID+'-'+$el+'"><span></span></label></li></ol>';
                }

                jQuery(this).prepend(html_switches);

            });

            /**
             * События при отметке чекбокса
             */
            jQuery('body .type-better-banner td.format.column-format input[type="checkbox"]').change(function(){
                let StatusPost = jQuery(this).attr('data-status');
                let PostID = jQuery(this).attr('id');
                PostID = PostID.split('-');
                PostID = PostID[1].split('-')[0];

                // GET data
                var data_get = {
                    action: 'Better_Ads_Banners_status_changer',
                    PostID: PostID,
                    StatusPost:StatusPost,
                };

                if (StatusPost === 'publish'){
                    jQuery(this).attr('data-status','draft');
                    data_get.StatusPost = 'draft';
                }
                if (StatusPost === 'draft'){
                    jQuery(this).attr('data-status','publish');
                    data_get.StatusPost = 'publish';
                }
                jQuery.ajax({
                    type: "POST", // use $_POST method to submit data
                    url: '/wp-admin/admin-ajax.php?action=Better_Ads_Banners_status_changer', // where to submit the data
                    data: data_get,
                    success: function (response) {
                        console.log('Статус банера изменен на '+data_get.StatusPost);
                        console.log(response);
                    },
                    error: function(x, t, e,){
                        // error: function (errorThrown) {
                        if( t === 'timeout') {
                            // Произошел тайм-аут
                        } else {
                            //////////////////////////////
                            /// sweetalert2 START
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                type: 'error',
                                title: 'Ошибка получения уникальности записи',
    //                                 text: 'Во время поиска и замены, произошла не известная ошибка',
                                showConfirmButton: false,
                                scrollbarPadding: false,
                                // timer: 3000
                            });
                            /// sweetalert2 END
                            //////////////////////////////
                            console.log('Ошибка: ' + e);
                        }
                    },
                    timeout: 30000,
                })
            });
        </script>
        <?php
    } );


}


add_action('wp_ajax_Better_Ads_Banners_status_changer', 'Better_Ads_Banners_status_changer');
/**
 * Ajax Callback
 */
function Better_Ads_Banners_status_changer()
{
    $IDPost = isset($_POST['PostID']) ? absint($_POST['PostID']) : 0;
    $StatusPost = isset($_POST['StatusPost']) ? sanitize_text_field($_POST['StatusPost']) : '';

    //    vd($IDPost);
    //    vd($StatusPost);

    // Update post
    $my_post = array();
    $my_post['ID'] = $IDPost;
    $my_post['post_status'] = $StatusPost;

    // Update the post into the database
    wp_update_post( $my_post );
    echo "✅ - ".$StatusPost;
    wp_die();
}







/**
 * Publisher fix - Если After баннеры опубликованы добавим класс
 * Делаем для красоты отображения фиксированных  банеров "Небоскребов"
 * Так как в случае если банеров в After нет, будет лишний padding-top
 */
// Добавляем класс с типом профиля пользовател в html для скрытия элементов
// Add role class to html
add_filter( 'language_attributes', 'Publisher_fixes_ВetectAfterAllAfterBannersAndAddClass', 10, 2 );
function Publisher_fixes_ВetectAfterAllAfterBannersAndAddClass( $output, $doctype ) {
    if (is_admin()){
        return $output;
    }
    if ( 'html' !== $doctype ) {
        return $output;
    }

    // Узнаем статус постов
    // [better-ads type='banner' banner='242663' ] - 1 [728х90] Вверху страницы над «шапкой» (показывается на всех страницах сайта)
    // [better-ads type='banner' banner='242666' ] - 2 [220х90] Вверху страницы над «шапкой» (показывается на всех страницах сайта)
    $PublisherAfterHeaderBanners = array(242663,242666);
    $BannersCurrentStatus = 'draft';
    foreach ($PublisherAfterHeaderBanners as $AfterBanner){
        $ststusBanner = get_post_status($AfterBanner);
        if ($ststusBanner == 'publish'){
            $BannersCurrentStatus = 'publish';
        }
    }
    $add_detected_class = '';
    if ($BannersCurrentStatus == 'publish'){
        $add_detected_class = 'publisher_after_banners_publish';
    }
    $output .= ' class="'.$add_detected_class.'"';

    return $output;
}
/*
Publisher Sticky Banners FIX CSS
END
*/