<?php
/***
 * https://www.weatherapi.com/docs/
 * Use [rama_weather_tabs]
 * [rama_weather_tabs widget]
 * @return false|string
 */

function weather_tabs_shortcode($atts) {
    // Ваш API ключ
    $api_key = '1a41c9ce51a743e5bec15757232611';
    $api_url = 'http://api.weatherapi.com/v1/forecast.json?key=' . $api_key . '&q=Sumy&days=7&lang=uk';

    // Перевірка, чи вже існують збережені дані погоди
    $weather_data = get_transient('weather_data');
    if (false === $weather_data) {
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            return 'Не вдалося отримати дані про погоду.';
        }
        $weather_data = json_decode(wp_remote_retrieve_body($response), true);
        set_transient('weather_data', $weather_data, 4 * HOUR_IN_SECONDS);
    }

    $html = '';

    if (isset($atts) && $atts[0] == 'widget') {
        // Відображення віджета погоди для сьогодні
        setlocale(LC_TIME, 'uk_UA.utf8'); // Встановлення української локалі для часу
        $today_weather = $weather_data['forecast']['forecastday'][0]; // Погода на сьогодні

        $html .= '<div class="weather-widget" style="text-align:center">';
        $html .= '<img src="' . $today_weather['day']['condition']['icon'] . '" alt="Weather icon" style="vertical-align: middle;">';
        $html .= '<p style="display: inline-block; margin: 0 10px;">' . $today_weather['day']['avgtemp_c'] . '°C</p>';
        $html .= '<p>' . $today_weather['day']['condition']['text'] . '</p>';
        $html .= '<a href="/pogoda-v-sumah/" style="color: #da1013;">Дізнатись погоду на 7 днів</a>';
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
            $title = ($count == 0) ? 'Сьогодні' : (($count == 1) ? 'Завтра' : $day['date']);
            $html .= '<div class="weather-day">';
            $html .= '<h3>' . $title . '</h3>';
            $html .= '<div' . ($count == 0 ? ' style="display: block;"' : '') . '>';
            
            $parts_of_day = ['00:00' => 'Вночі', '06:00' => 'Вранці', '12:00' => 'Вдень', '18:00' => 'Увечері'];
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
                    $html .= '<b>Вітер:</b> ' . $hour_data['wind_kph'] . ' км/год, ';
                    $html .= '<b>Вологість:</b> ' . $hour_data['humidity'] . '%, ';
                    $html .= '<b>Тиск:</b> ' . $hour_data['pressure_mb'] . ' мбар';
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