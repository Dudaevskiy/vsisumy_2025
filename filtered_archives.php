<?php

if (is_admin()){
    return false;
}
/**
 * Кастомизируем архивы по годам, в связи с неизвестной ошибкой вывода архива стандартным методом WordPress
 * -- Не выводятся 2006,2007...
 */
add_filter('pre_get_posts', 'atom_include_cpt_in_archives');
function atom_include_cpt_in_archives($query){
//    s($query);
    /**
     * https://rama.com.ua/arhiv-novin/?rama_dates=2006
     */
    if ( ( isset($_GET['rama_dates']) && !empty($_GET['rama_dates']) ) || ( isset($_GET['rama_cat']) && !empty($_GET['rama_cat']) ) ){

        // Делим по - год-месяц-дата
        $rama_dates_spliter = isset($_GET['rama_dates']) ? explode("-", $_GET['rama_dates']) : array();

        // Исключаем другие кастомные типы записей из запроса
        // || $query->get('this_checking') == 'yes' - Исключаем проверку количества постов в месяцах
        if ($query->get('post_type') == 'nav_menu_item' ||
            $query->get('post_type') == 'better-banner'||
            $query->get('post_type') == 'page'||
            $query->get('this_checking') == 'yes'||
            $query->get('this_get_dates_for_picker') == 'yes' ||
            $query->get('this_shortcodes_rama') == 'yes') {
            return $query;
        }
        $query->set( 'post_type' , array(
            'post', 'announce'
        ));


        // Если только месяц
        // 2005-11
        if(count($rama_dates_spliter) == 2){
            $query->set( 'date_query', array(
                'year'=>(int)$rama_dates_spliter[0],
                'monthnum'=>(int)$rama_dates_spliter[1],
            ));

            // Если год, месяц и день
            // 2005-11-21
        } else if(count($rama_dates_spliter) == 3){
            $query->set( 'date_query', array(
                'year'=>(int)$rama_dates_spliter[0],
                'month'=>(int)$rama_dates_spliter[1],
                'day'=>(int)$rama_dates_spliter[2],
            ));

            // Если только год
            // 2005
        } else if(count($rama_dates_spliter) == 1 && isset($rama_dates_spliter[0])) {
            $query->set( 'year', (int)$rama_dates_spliter[0] );
        }


        // Если есть категория
//        s($_GET['rama_cat']);
        if ( isset($_GET['rama_cat']) && $_GET['rama_cat'] == 'announces'){
            $query->set( 'post_type' , array(
                'announce'
            ));
        }
        if ( isset($_GET['rama_cat']) && $_GET['rama_cat'] == 'projects'){
            $query->set( 'post_type' , array(
                'project'
            ));
        }
//        if ($_GET['rama_cat'] == 'kalendar_istor_podiy'){
//            $query->set( 'post_type' , array(
//                'kalendar_istor_podiy'
//            ));
//        }

        if (!empty($_GET['rama_cat']) && $_GET['rama_cat'] !== 'projects' && $_GET['rama_cat'] !== 'announces'){
            $query->set(
                'tax_query' , array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $_GET['rama_cat'],
                ),
            ));
        }

//        s($query);
        return $query;
    }

//    if (is_page('authors')){
//        // || $query->get('this_checking') == 'yes' - Исключаем проверку количества постов в месяцах
//        if ($query->get('post_type') == 'nav_menu_item' || $query->get('post_type') == 'better-banner'|| $query->get('post_type') == 'page'|| $query->get('this_checking') == 'yes'|| $query->get('this_get_dates_for_picker') == 'yes') {
//            return $query;
//        }
//        s($query);
//    }

    return $query;
}






function callback_archive_rama($buffer) {

    if (is_admin()){
        return false;
    }


    // Получаем первый опубликованный пост
    $first_post_date_year = '2005';
    // Текущая дата
    $today = date('Y-m-d', time());
    $current_year = explode("-",$today)[0];
    $current_month = explode("-",$today)[1];
    // Сколько лет прошло от первой публикации поста
    $off_years = (int)$current_year - (int)$first_post_date_year;

    // Генерируем HTML
    $get_year = isset($_GET['rama_dates']) ? $_GET['rama_dates'] : '';
    //if (explode($get_year)[1]){
//	if (isset($get_year) && explode('-', $get_year)[1]){
//        $get_year = explode($get_year)[0];
//    }

if (isset($get_year)) {
    $parts_dash = explode('-', $get_year);
    if (isset($parts_dash[1])) {
        $get_year = $parts_dash[0];
    }
}

//    print($current_year.' = '.$get_year);
    $styles_current_year = '';
    if ((int)$current_year == (int)$get_year){
        $styles_current_year = 'style="background: #bc0505 !important;color: white !important;"';
    }

    // Если есть категория
    $cat_slug = '';
    if ( isset($_GET['rama_cat']) && $_GET['rama_cat'] ){
        $cat_slug = '&rama_cat=' . sanitize_text_field($_GET['rama_cat']);
    }

    $html_years = '<div class="archive-badges term-badges .listing.columns-3">'."\n";
    $html_years .= '<p class="rama_filter_title"><b>Архіви по рокам:</b></p><br>'."\n";
    $html_years .= '<span class="archive-badge term-badge"><a '.$styles_current_year.' href="https://rama.com.ua/arhiv-novin/?rama_dates='.(int)$current_year.$cat_slug.'">'.(int)$current_year.'</a></span>'."\n";

    $i = 1;
    $styles_current_year = '';
    while ($i <= $off_years) {
        $this_year = (int)$current_year - $i;
        if ($this_year == $get_year){
            $styles_current_year = 'style="background: #bc0505 !important;color: white !important;"';
        }

        $html_years .= '<span class="archive-badge term-badge"><a '.$styles_current_year.' href="https://rama.com.ua/arhiv-novin/?rama_dates='.$this_year.$cat_slug.'">'.$this_year.'</a></span>'."\n";
        $styles_current_year = '';
        $i++;
    }
	
	// Corrected line 160
if (isset($_GET['rama_dates']) && !empty($_GET['rama_dates'])) {
    $parts = explode("-", $_GET['rama_dates']);
    $data_year = $parts[0];

    if (!isset($parts[1]) || empty($parts[1])){
        $data_year = $_GET['rama_dates'];
    } else {
        $data_year = $parts[0];
    }
}
    $html_years .= '</div>';






    /**
     * Укр. месяц
     */
    if (!function_exists('return_uk_month')) {
        function return_uk_month($num){
            $month_text = '';
            switch ($num) {
                case 1:
                    $month_text = 'Січень';
                    break;
                case 2:
                    $month_text = 'Лютий';
                    break;
                case 3:
                    $month_text = 'Березень';
                    break;
                case 4:
                    $month_text = 'Квітень';
                    break;
                case 5:
                    $month_text =  'Травень';
                    break;
                case 6:
                    $month_text = 'Червень';
                    break;
                case 7:
                    $month_text = 'Липень';
                    break;
                case 8:
                    $month_text = 'Серпень';
                    break;
                case 9:
                    $month_text = 'Вересень';
                    break;
                case 10:
                    $month_text = 'Жовтень';
                    break;
                case 11:
                    $month_text = 'Листопад';
                    break;
                case 12:
                    $month_text = 'Грудень';
                    break;
            }
            return $month_text;
        }
    }

    /**
     * Вывод по месяцам
     * @param $year
     * @param $month
     * @return string
     */
    function rama_monhs_return($year, $month){
        $year = (int)$year;
        $styles_active_month = '';

        $html = '<p class="rama_filter_title" ><b>Архіви по місяцям:</b></p><br>';
        $i = 1;

        // Текущая дата
        $today = date('Y-m-d', time());
        $current_year = explode("-",$today)[0];
        $current_month = explode("-",$today)[1];
        $max_count_months = 12;

        if ((int)$current_year == (int)$year || (int)$year == 0){
            $max_count_months = (int)$current_month;
        }

        while ($i <= 12) {
            $month_text = return_uk_month($i);
            if ($year == 0){
                $year = $current_year;
            }
            // Получаем посты за месяц
            $query =   '';

            $query = array(
                'date_query' => array(
                    'year' => (int)$year,
                    'monthnum' => $i,
                    'month' => $i
                ),
                // this_checking - Касттомный определеитель запроса для изключения в основном запросе постов
                'this_checking' => 'yes',
//                        'posts_per_page' => -1,
                'posts_per_page' => 1,
                // получаем только IDs записей
                'fields' => 'ids',
                'post_type' => array(
                    'post',
                    'announce'
                ),
                'post_status' => 'publish'
            );


            $query = new WP_Query($query);
            $posts_get_full = $query->post_count;

            // Cats
            $cat_slug = '';
            if ( isset($_GET['rama_cat']) && $_GET['rama_cat'] ){
                $cat_slug = '&rama_cat=' . sanitize_text_field($_GET['rama_cat']);
            }

            if ($posts_get_full > 0){
                if (!empty($month) && (int)$month == $i) {
                    $styles_active_month = 'style="background: #bc0505 !important;color: white !important;"';
                }
                $html .= '<span class="archive-badge term-badge"><a href="/arhiv-novin/?rama_dates=' . $year . '-' . $i .$cat_slug. '" ' . $styles_active_month . '>' . $month_text. '</a></span>' . "\n";
                $styles_active_month = '';
            }
            $posts_get_full = 0;
            $i++;

            // Делаем сброс запроса
            wp_reset_query();
            wp_reset_postdata();
        }

        return $html;
    }

    $rama_dates = isset($_GET['rama_dates']) ? $_GET['rama_dates'] : '';
    $get_year = $rama_dates;
    $get_month = '';
    $date_parts = explode("-", $rama_dates);
    if (isset($date_parts[1])){
        $get_month = $date_parts[1];
        $get_year = $date_parts[0];
    }

    $html_months = '<div class="archive-badges term-badges .listing.columns-3 months_archives">'."\n";
    $html_months .= rama_monhs_return($get_year, $get_month);
    $html_months .= '</div>'."\n";


    // Categories
    $html_cats = '<div class="archive-badges term-badges .listing.columns-3 cats_archives">'."\n";
    $html_cats .= '<p class="rama_filter_title"><b>Архіви за категоріями:</b></p><br>';
    $styles_active_cat = '';
    $cats = array(
        'news' => 'НОВИНИ',
        'articles' => 'СТАТТІ',
        'photos' => 'ФОТО',
        'videos' => 'ВІДЕО',
        'projects' => 'ПРОЕКТИ',
//        'announces' => 'АНОНСИ ГАЗЕТ',
    );
    $cats_get = isset($_GET['rama_cat']) ? sanitize_text_field($_GET['rama_cat']) : '';
    foreach ($cats as $key => $cat_title){
        if (!empty($cats_get) && $cats_get == $key) {
            $styles_active_cat = 'style="background: #bc0505 !important;color: white !important;"';
        }
        if (isset($_GET['rama_dates']) && !empty($_GET['rama_dates'])){
            $html_cats .= '<span class="archive-badge term-badge"><a href="/arhiv-novin/?rama_dates='.esc_attr($_GET['rama_dates']).'&rama_cat=' . $key . '" ' . $styles_active_cat . '>' . $cat_title . '</a></span>' . "\n";
        } else {
            $html_cats .= '<span class="archive-badge term-badge"><a href="/arhiv-novin/?rama_cat=' . $key . '" ' . $styles_active_cat . '>' . $cat_title . '</a></span>' . "\n";
//            $html_cats .= '<span class="archive-badge term-badge"><a href="/arhiv-novin/?rama_dates='.$current_year.'&rama_cat=' . $key . '" ' . $styles_active_cat . '>' . $cat_title . '</a></span>' . "\n";
        }
        $styles_active_cat = '';
    }
    $html_cats .= '</div>'."\n";
    // Выделяем пункт в меню
    $html_cats .= '<style>li.rama_archives_parent {background:#bc0505;}</style>';

    /**
     * flatpickr
     */

    $html_flatpickr = '<div id="flatpickr_cal"></div>';
    // Текущая дата
//    $today = date('Y-m-d', time());
//    $current_year = explode("-",$today)[0];
//    $current_month = explode("-",$today)[1];

if (!function_exists('ReternsDatesForDataPicker')){
    function ReternsDatesForDataPicker(){
        $today = date('Y-m-d', time());

        $current_year = explode("-",$today)[0];
        $current_month = explode("-",$today)[1];
        $cur_date_for_picker = $today;

        // Делим по - год-месяц-дата
        $rama_dates_value = isset($_GET['rama_dates']) ? $_GET['rama_dates'] : '';
        $rama_dates_spliter = explode("-", $rama_dates_value);

        $query_pick = [];
        // Для отмены в сете
        $query_pick['this_get_dates_for_picker'] = 'yes';

        $query_pick['post_type'] = array(
            'post', 'announce'
        );


        // Если только месяц
        // 2005-11
        if(count($rama_dates_spliter) == 2){
            $query_pick['date_query'] = array(
                'year'=>(int)$rama_dates_spliter[0],
                'monthnum'=>(int)$rama_dates_spliter[1],
            );

            // Если год, месяц и день
            // 2005-11-21
        } else if(count($rama_dates_spliter) == 3){
            $query_pick['date_query'] = array(
                'year'=>(int)$rama_dates_spliter[0],
                'monthnum'=>(int)$rama_dates_spliter[1],
//                'day'=>(int)$rama_dates_spliter[2],
            );

            // Если только год
            // 2005
        } else {
            $query_pick['year'] = (int)$rama_dates_spliter[0] ;

            if ($query_pick['year'] == $current_year){
                $query_pick['date_query'] = array(
                    'year'=>(int)$rama_dates_spliter[0],
                    'monthnum'=>$current_month,
                );
            } else if ($query_pick['year'] !== $current_year){
                $query_pick['date_query'] = array(
                    'year'=>(int)$rama_dates_spliter[0],
                    'monthnum'=>12,
                );
            }

            if ( !$query_pick['year']){
                $query_pick['date_query'] = array(
                    'year'=>(int)$current_year,
                    'month'=>(int)$current_month,
                );
            }
        }


        // Если есть категория
//        s($_GET['rama_cat']);
        if ( isset($_GET['rama_cat']) && $_GET['rama_cat'] == 'announces'){
            $query_pick['post_type'] = array(
                'announce'
            );
        }
        if ( isset($_GET['rama_cat']) && $_GET['rama_cat'] == 'projects'){
            $query_pick['post_type'] = array(
                'project'
            );
        }


        if (!empty($_GET['rama_cat']) && $_GET['rama_cat'] !== 'projects' && $_GET['rama_cat'] !== 'announces'){
            $query_pick['tax_query'] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $_GET['rama_cat'],
                ),
            );
        }

        // Сюда добавим даты месяца
        $get_dates = '[]';
        // Делаем проверку месяца
        if ( (isset($query_pick['date_query']['monthnum']) && $query_pick['date_query']['monthnum']) || (isset($query_pick['date_query']['month']) && $query_pick['date_query']['month']) ){

            $query_pick['posts_per_page'] = -1;
            $query_pick['fields'] = 'ids';
            $query_pick_get =  new WP_Query($query_pick);

            foreach ($query_pick_get->posts as $post){
                $date_ = get_the_time('Y-m-d', $post);
                $get_dates = str_replace(']','"'.$date_.'",]',$get_dates);
            }
            $get_dates = str_replace(",]","]",$get_dates);

        } else {
            // Если месяц не указан получаем все посты января
            $query_pick['date_query'] = array(
                'year'=>(int)$query_pick['year'],
                'month'=>1,
//            'day'=>(int)$rama_dates_spliter[2],
            );

            $query_pick['posts_per_page'] = 300;
            $query_pick['fields'] = 'ids';

            $query_pick_get =  new WP_Query($query_pick);
            foreach ($query_pick_get->posts as $post){
                $date_ = get_the_time('Y-m-d', $post);
                $get_dates = str_replace(']','"'.$date_.'",]',$get_dates);
//                $get_dates = str_replace(']','"'.explode(" ",$post->post_date)[0].'",]',$get_dates);
//            $return_data = '["' . $cur_date_for_picker . '"]';
            }
            $get_dates = str_replace(",]","]",$get_dates);
        }

        return $get_dates;
    }
}


    $html_flatpickr .= "<script>jQuery(\"#flatpickr_cal\").flatpickr({
                            inline: true,
                            dateFormat: 'Y-m-d',
                            defaultDate: ".ReternsDatesForDataPicker().",
                            locale: {
                                firstDayOfWeek: 1,
                                minDate: 'today',
                                weekdays: {
                                  shorthand: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                                  longhand: ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'],         
                                }, 
                                months: {
                                  shorthand: ['Січ', 'Лют', 'Бер', 'Кв', 'Тра', 'Чер', 'Лип', 'Серп', 'Вер', 'Жов', 'Лист', 'Груд'],
                                  longhand: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
                                }
                            },
                          onDayCreate: function(dateStr, instance,dObj,dayElem) {
                            //because you're working with an object and not an array, 
                            //we'll iterate using its keys
                            // Да, да... Все эти страдания что бы добавить Y-m-d в data-data_day атрибут
                            function convert_data(str) {
                              var date = new Date(str),
                                mnth = (\"0\" + (date.getMonth() + 1)).slice(-2),
                                day = (\"0\" + date.getDate()).slice(-2);
                              return [date.getFullYear(), mnth, day].join(\"-\");
                            }
                            
                            let data_tmp = convert_data(dObj.latestSelectedDateObj);
                            let split = data_tmp.split('-');
                            let data_cur_day = split[0]+'-'+split[1]+'-'+dayElem.innerText;
                           dayElem.dataset.data_day  = data_cur_day; // <span class='flatpickr-day selected' aria-label='Лютий 29, 2020' tabindex='-1' data-data_day='2020-02-29'>29</span>
                          },
                          onChange(selectedDates) {
                            window.flatpick_get = {}; 
                            window.flatpick_get.clickdate = selectedDates; 
                            window.flatpick_get.defaultDate = ".ReternsDatesForDataPicker()."; 
                            let split = window.flatpick_get.defaultDate[0].split('-');
                            let date_for_link = split[0]+'-'+split[1]+'-'+window.flatpick_get.clickdate[0].getDate();
                            
                            // Переход по ссылке
                            let old_get = window.location.search;
                            let cur_href = window.location.href.split('?')[0];
                            let new_link = '';
                            
                            if (old_get.includes( \"&\")){
                                let cat = old_get.split('&')[1];
                                let new_data = '?rama_dates='+date_for_link;
                                new_link = cur_href+new_data+'&'+cat;
                            } else {
                                let new_data = '?rama_dates='+date_for_link;
                                new_link = cur_href+new_data;
                            }
                            if (window.location.href.includes( \"page\")){
                                let old_pag = window.location.href.split('page');
//                            console.log(old_pag );
                                let end_link = old_pag[1].split('?')[1];
//                            console.log(end_link );
                                new_link = old_pag[0]+'?'+end_link;
                            }
                            window.location.href = new_link; 
//                            console.log(new_link);                            
//                            console.log(date_for_link);                            
                          },
                        });</script>";

    /**
     * Finish HTML
     */
    // Выделяем текущий день
    $cur_day_style = '';
    $rama_dates_get = isset($_GET['rama_dates']) ? $_GET['rama_dates'] : '';
    $date_parts_check = explode("-", $rama_dates_get);
    if (isset($date_parts_check[2])){
        $cur_day_style = '<style>[data-data_day="'.esc_attr($rama_dates_get).'"]{background:#bc0505 !important; color:white !important;}</style>';
    }
    // Обработка заголовка
    $title_head = $rama_dates_get;
    if (isset($date_parts_check[1])){
        $title_head = $date_parts_check[0];
        $title_head .= ' '.return_uk_month($date_parts_check[1]);
    } else {
        $today = date('Y-m-d', time());
        $current_year = explode("-",$today)[0];
        $current_month = explode("-",$today)[1];
        if (empty($rama_dates_get)){
            $title_head = $current_year;
            $title_head .= ' '.return_uk_month($current_month);
        } else {
            if ($rama_dates_get == $current_year){
                $title_head = $rama_dates_get;
                $title_head .= ' '.return_uk_month($current_month);
            } else {
                $title_head = $rama_dates_get;
                $title_head .= ' '.return_uk_month(12);
            }

        }

    }

    $funish_html = $cur_day_style.'
    <div class="container" id="rama_filters_wrap">
    <section class="archive-title daily-title" style="padding-left: 0px;">
        <h1 class="page-heading"><span class="h-title">'.$title_head.'</span></h1>
        <div id="filters_rama_calendar" class="col-md-4">'.$html_flatpickr.'</div>
        <div id="filters_rama" class="col-md-8"  style="padding-bottom: 20px;">'.$html_years.$html_months.$html_cats.'</div>
	</section>
	</div>';

    // Получаем количество запрошеных постов в wp query
    $args = array(
        'post_type' => 'post'
    );
    $the_query = new WP_Query( $args );
    $totalpost = $the_query->found_posts;

    // Получаем количество запрошеных постов в wp query
    $args = array(
        'check_get' => 'yes'
    );




    if ($totalpost > 0){
//        $buffer = str_replace('<div class="slider-container',$title_html.'<div class="slider-container',$buffer);
//        $buffer = str_replace('<div class="container layout-2-col layout-2-col-1 layout-right-sidebar">',$funish_html.'<div class="container layout-2-col layout-2-col-1 layout-right-sidebar">',$buffer);
        $buffer = str_replace('<div class="col-sm-8 content-column">','<div class="col-sm-8 content-column">'.$funish_html,$buffer);
    } else {
        $error = '<br><br><h2 style="text-align: center">Вибачте, але в даній категорії немає матеріалів.</h2><br><br><br><br><br>';
//        $buffer = str_replace('<div class="container layout-2-col layout-2-col-1 layout-right-sidebar">','<div class="container layout-2-col layout-2-col-1 layout-right-sidebar empty_content">'.$title_html.$error,$buffer);
        $buffer = str_replace('<div class="col-sm-8 content-column">','<div class="col-sm-8 content-column empty_content">'.$funish_html.$error,$buffer);
    }

    /**
     * Проекты - Заменяем оригинальные ссылки внешними
     */
    if ( !empty($the_query->posts) && isset($the_query->posts[0]) && isset($the_query->posts[0]->post_type) && $the_query->posts[0]->post_type == "project"){
        foreach ($the_query->posts as $key => $val){
            $url_orig_proj = get_permalink($val->ID);
            $url_custom_external = get_post_meta($val->ID, 'project_link');
            if ($url_custom_external){
                $buffer = str_replace($url_orig_proj.'"',$url_custom_external[0].'" target="_blank"',$buffer);
            }
        }
    }

    return $buffer;
}

// Страница блога выставленная в настройках WP
$get_blog_page = get_post_type_archive_link( 'post' );
// ID страницы для блога
$id_page_for_posts = get_option( 'page_for_posts' );
// Функция для захвата текущей ссылки страницы
if (!function_exists('page_current_url')){
    function page_current_url() {
        $current_url  = 'http';
        $server_https = $_SERVER["HTTPS"] ?? '';
        $server_name  = $_SERVER["SERVER_NAME"] ?? '';
        $server_port  = $_SERVER["SERVER_PORT"] ?? 80;
        $request_uri  = $_SERVER["REQUEST_URI"] ?? '';
        if ($server_https == "on") $current_url .= "s";
        $current_url .= "://";
        if ($server_port != "80") $current_url .= $server_name . $request_uri;
        else $current_url .= $server_name . $request_uri;
        return $current_url;
    }
}
$sdstudio_current_page = page_current_url();

$parts_question = explode("?", $sdstudio_current_page);
if (isset($parts_question[1])){
    $sdstudio_current_page = $parts_question[0];
}

$parts_page = explode("page", $sdstudio_current_page);
if (isset($parts_page[1])){
    $sdstudio_current_page = $parts_page[0];
}
//dd($sdstudio_current_page);
if ($get_blog_page == $sdstudio_current_page){
    if (is_admin()){
        return false;
    }
//if ($_GET['rama_dates']){
    function buffer_start_archive_rama() { ob_start("callback_archive_rama"); }
    function buffer_end_archive_rama() { ob_end_flush(); }

    add_action('after_setup_theme', 'buffer_start_archive_rama');
    add_action('shutdown', 'buffer_end_archive_rama');
}



