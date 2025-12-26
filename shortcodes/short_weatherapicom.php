<?php
/***
 * https://www.weatherapi.com/docs/
 * Use [rama_weather_tabs]
 * [rama_weather_tabs widget]
 * @return false|string
 */

function weather_tabs_shortcode($atts) {
    // Отримуємо поточну мову WPML
    $current_lang = apply_filters('wpml_current_language', 'uk');

    // Маппінг мов WPML -> WeatherAPI
    $weather_lang_map = array(
        'uk' => 'uk',
        'ru' => 'ru',
        'en' => 'en'
    );
    $weather_lang = isset($weather_lang_map[$current_lang]) ? $weather_lang_map[$current_lang] : 'uk';

    // Переклади для інтерфейсу
    $translations = array(
        'uk' => array(
            'error' => 'Не вдалося отримати дані про погоду.',
            'more_link' => 'Дізнатись погоду на 3 дні',
            'today' => 'Сьогодні',
            'tomorrow' => 'Завтра',
            'night' => 'Вночі',
            'morning' => 'Вранці',
            'day' => 'Вдень',
            'evening' => 'Увечері',
            'wind' => 'Вітер',
            'humidity' => 'Вологість',
            'pressure' => 'Тиск',
        ),
        'ru' => array(
            'error' => 'Не удалось получить данные о погоде.',
            'more_link' => 'Узнать погоду на 3 дня',
            'today' => 'Сегодня',
            'tomorrow' => 'Завтра',
            'night' => 'Ночью',
            'morning' => 'Утром',
            'day' => 'Днём',
            'evening' => 'Вечером',
            'wind' => 'Ветер',
            'humidity' => 'Влажность',
            'pressure' => 'Давление',
        ),
        'en' => array(
            'error' => 'Failed to get weather data.',
            'more_link' => 'See 3-day forecast',
            'today' => 'Today',
            'tomorrow' => 'Tomorrow',
            'night' => 'Night',
            'morning' => 'Morning',
            'day' => 'Afternoon',
            'evening' => 'Evening',
            'wind' => 'Wind',
            'humidity' => 'Humidity',
            'pressure' => 'Pressure',
        ),
    );
    $t = isset($translations[$current_lang]) ? $translations[$current_lang] : $translations['uk'];

    // Ваш API ключ
    $api_key = '1a41c9ce51a743e5bec15757232611';
    $api_url = 'http://api.weatherapi.com/v1/forecast.json?key=' . $api_key . '&q=Sumy&days=7&lang=' . $weather_lang;

    // Перевірка, чи вже існують збережені дані погоди (з урахуванням мови)
    $cache_key = 'weather_data_' . $current_lang;
    $weather_data = get_transient($cache_key);
    if (false === $weather_data) {
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            return $t['error'];
        }
        $weather_data = json_decode(wp_remote_retrieve_body($response), true);
        set_transient($cache_key, $weather_data, 4 * HOUR_IN_SECONDS);
    }

    $html = '';

    if (isset($atts) && $atts[0] == 'widget') {
        // Відображення віджета погоди для сьогодні
        $today_weather = $weather_data['forecast']['forecastday'][0]; // Погода на сьогодні

        // URL сторінки погоди з урахуванням мови
        $weather_page_url = '/pogoda-v-sumah/';
        if ($current_lang === 'ru') {
            $weather_page_url = '/ru/pogoda-v-sumah/';
        } elseif ($current_lang === 'en') {
            $weather_page_url = '/en/pogoda-v-sumah/';
        }

        $html .= '<div class="weather-widget" style="text-align:center">';
        $html .= '<img src="' . $today_weather['day']['condition']['icon'] . '" alt="Weather icon" style="vertical-align: middle;">';
        $html .= '<p style="display: inline-block; margin: 0 10px;">' . $today_weather['day']['avgtemp_c'] . '°C</p>';
        $html .= '<p>' . $today_weather['day']['condition']['text'] . '</p>';
        $html .= '<a href="' . $weather_page_url . '" style="color: #bc0505;">' . $t['more_link'] . '</a>';
        $html .= '</div>';
    } else {
		 // Додаємо CSS стилі
    $html .= '<style>
                .weather-tabs {
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }

                .weather-day {
                    border-top: 1px solid #ddd;
                }

                .weather-day h3 {
                    margin: 0;
                    padding: 10px;
                    background-color: #f8f8f8;
                    cursor: pointer;
                }

                .weather-day div {
                    padding: 10px;
                    display: none; /* Спочатку приховано */
                }

                .weather-day img {
                    vertical-align: middle;
                    margin-right: 5px;
                }

                .weather-day [style="display: block;"] .weather_content {
                    display: block;
                }

                @media screen and (max-width: 600px) {
                    .weather-day h3 {
                        /* Стилі для заголовків акардеону на мобільних */
                    }

                    .weather-day div {
                        /* Стилі для змісту акардеону */
                    }

                    span.display-mobal {
                        min-width: 100%;
                        display: block;
                    }
                }
            </style>';
        // Стандартне відображення шорткоду
        $html .= '<div class="weather-tabs">';
        foreach ($weather_data['forecast']['forecastday'] as $count => $day) {
            $title = ($count == 0) ? $t['today'] : (($count == 1) ? $t['tomorrow'] : $day['date']);
            $html .= '<div class="weather-day">';
            $html .= '<h3>' . $title . '</h3>';
            $html .= '<div' . ($count == 0 ? ' style="display: block;"' : '') . '>';

            $parts_of_day = [
                '00:00' => $t['night'],
                '06:00' => $t['morning'],
                '12:00' => $t['day'],
                '18:00' => $t['evening']
            ];

            // Одиниці виміру з урахуванням мови
            $wind_unit = ($current_lang === 'en') ? ' km/h' : ' км/год';
            $pressure_unit = ($current_lang === 'en') ? ' mbar' : ' мбар';

            foreach ($parts_of_day as $time => $part_name) {
                $hour_data = array_filter($day['hour'], function ($hour) use ($time) {
                    return date('H:i', strtotime($hour['time'])) == $time;
                });
                $hour_data = reset($hour_data); // Перша година, що відповідає вказаному часу

                if ($hour_data) {
                    $html .= '<h4>' . $part_name . '</h4>';
                    $html .= '<div class="weather_content">';
                    $html .= '<img src="' . $hour_data['condition']['icon'] . '" alt="' . $hour_data['condition']['text'] . '">';
                    $html .= '<span style="font-size: 22px;font-weight: 600;">' . $hour_data['temp_c'] . ' °C</span>, ';
                    $html .= $hour_data['condition']['text'] . ', ';
                    $html .= '<b>' . $t['wind'] . ':</b> ' . $hour_data['wind_kph'] . $wind_unit . ', ';
                    $html .= '<b>' . $t['humidity'] . ':</b> ' . $hour_data['humidity'] . '%, ';
                    $html .= '<b>' . $t['pressure'] . ':</b> ' . $hour_data['pressure_mb'] . $pressure_unit;
                    $html .= '</div>';
                }
            }

            $html .= '</div>'; // Кінець div.weather-day
            $html .= '</div>'; // Кінець div.weather-day
        }
        $html .= '</div>'; // Кінець div.weather-tabs
    }

    return $html;
}
add_shortcode('rama_weather_tabs', 'weather_tabs_shortcode');