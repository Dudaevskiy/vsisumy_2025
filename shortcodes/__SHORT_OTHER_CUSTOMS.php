<?php
if (!function_exists('SHORT_OTHER_CUSTOM_custom_shortcode')) {
function SHORT_OTHER_CUSTOM_custom_shortcode() {
    // HTML код вашої сторінки
    $html = '
    <div>
        <h2 class="sdstudio-subtitle">Оберіть свою чергу</h2>
        <div class="sdstudio-button-group">
            <button onclick="showSchedule(1)">1</button>
            <button onclick="showSchedule(2)">2</button>
            <button onclick="showSchedule(3)">3</button>
            <button onclick="showSchedule(4)">4</button>
            <button onclick="showSchedule(5)">5</button>
            <button onclick="showSchedule(6)">6</button>
        </div>
        <div id="sdstudio-schedule">
            <h3>Стан відключень у Вашій черзі <span id="sdstudio-queue-number">❓</span> :</h3>
            <div id="sdstudio-times" style="min-height:90px">Оберіть чергу для результату</div>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">№ з/п</th>
                    <th colspan="3">Числа місяця</th>
                    <th rowspan="2">Черга ГПВ</th>
                </tr>
                <tr id="date-headers">
                    <th>
                        <span>1</span>, <span>4</span>, <span>7</span>, <span>10</span>, <span>13</span>, <span>16</span>, <span>19</span>, <span>22</span>, <span>25</span>, <span>28</span>, <span>31</span>
                    </th>
                    <th>
                        <span>2</span>, <span>5</span>, <span>8</span>, <span>11</span>, <span>14</span>, <span>17</span>, <span>20</span>, <span>23</span>, <span>26</span>, <span>29</span>
                    </th>
                    <th>
                        <span>3</span>, <span>6</span>, <span>9</span>, <span>12</span>, <span>15</span>, <span>18</span>, <span>21</span>, <span>24</span>, <span>27</span>, <span>30</span>
                    </th>
                </tr>
            </thead>
            <tbody id="sdstudio-schedule-table">
                <tr>
                    <td>1</td>
                    <td data-time="00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00">00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00</td>
                    <td data-time="04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00">04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00</td>
                    <td data-time="02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td data-time="02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00</td>
                    <td data-time="00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00">00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00</td>
                    <td data-time="04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00">04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00</td>
                    <td>2</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td data-time="04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00">04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00</td>
                    <td data-time="02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00</td>
                    <td data-time="00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00">00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00</td>
                    <td>3</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td data-time="00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00">00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00</td>
                    <td data-time="04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00">04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00</td>
                    <td data-time="02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00</td>
                    <td>4</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td data-time="02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00</td>
                    <td data-time="00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00">00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00</td>
                    <td data-time="04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00">04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00</td>
                    <td>5</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td data-time="04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00">04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00</td>
                    <td data-time="02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00</td>
                    <td data-time="00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00">00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00</td>
                    <td>6</td>
                </tr>
            </tbody>
        </table>
    </div>';

    // CSS код вашої сторінки
    $css = '
 <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .sdstudio-button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .sdstudio-button-group button {
            padding: 10px;
            border: none;
            background-color: #ffffff;
            color: black;
            font-size: 18px;
            border-radius: 0;
            cursor: pointer;
            border: 2px solid red;
            transition: all 0.3s;
        }
        .sdstudio-button-group button.active {
            background-color: red;
            color: white;
        }
        #sdstudio-schedule {
            margin-top: 20px;
            font-size: 18px;
        }
        .sdstudio-highlight {
            background-color: red;
            color: white;
            padding: 5px;
            margin-bottom: 10px;
        }
        .sdstudio-no-off {
            background-color: green;
            color: white;
            padding: 5px;
            margin-bottom: 10px;
        }
        .sdstudio-past-time {
            color: grey;
        }
        .sdstudio-current-time {
            font-weight: bold;
        }
        #sdstudio-times {
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            th, td {
                font-size: 14px;
            }
            .sdstudio-button-group button {
                font-size: 16px;
                padding: 8px;
            }
        }
        .table-container {
            overflow-x: auto;
        }
        .current-day {
            border: 2px solid green;
        }
        .current-day-parent {
            background-color: lightgreen;
        }
    </style>';

    // JS код вашої сторінки
    $js = '
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const schedules = {
            1: {
                "1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31": "00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00",
                "2, 5, 8, 11, 14, 17, 20, 23, 26, 29": "04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00",
                "3, 6, 9, 12, 15, 18, 21, 24, 27, 30": "02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00"
            },
            2: {
                "1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31": "02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00",
                "2, 5, 8, 11, 14, 17, 20, 23, 26, 29": "00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00",
                "3, 6, 9, 12, 15, 18, 21, 24, 27, 30": "04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00"
            },
            3: {
                "1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31": "04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00",
                "2, 5, 8, 11, 14, 17, 20, 23, 26, 29": "02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00",
                "3, 6, 9, 12, 15, 18, 21, 24, 27, 30": "00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00"
            },
            4: {
                "1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31": "00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00",
                "2, 5, 8, 11, 14, 17, 20, 23, 26, 29": "04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00",
                "3, 6, 9, 12, 15, 18, 21, 24, 27, 30": "02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00"
            },
            5: {
                "1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31": "02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00",
                "2, 5, 8, 11, 14, 17, 20, 23, 26, 29": "00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00",
                "3, 6, 9, 12, 15, 18, 21, 24, 27, 30": "04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00"
            },
            6: {
                "1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31": "04:00-06:00; 10:00-12:00; 16:00-18:00; 22:00-24:00",
                "2, 5, 8, 11, 14, 17, 20, 23, 26, 29": "02:00-04:00; 08:00-10:00; 14:00-16:00; 20:00-22:00",
                "3, 6, 9, 12, 15, 18, 21, 24, 27, 30": "00:00-02:00; 06:00-08:00; 12:00-14:00; 18:00-20:00"
            }
        };

        function getCurrentTime() {
            const now = new Date();
            return now.getHours() * 60 + now.getMinutes();
        }

        function timeToMinutes(time) {
            const [hours, minutes] = time.split(":").map(Number);
            return hours * 60 + minutes;
        }

        function getTodaysSchedule(queue) {
            const today = new Date().getDate();
            let schedule = "";
            for (let dates in schedules[queue]) {
                if (dates.split(", ").map(Number).includes(today)) {
                    schedule = schedules[queue][dates];
                    break;
                }
            }
            return schedule;
        }

        function formatSchedule(schedule) {
            const currentTime = getCurrentTime();
            let formattedSchedule = "";
            let currentOff = false;
            let nextOffTimes = [];
            const scheduleTimeRanges = schedule.split("; ");

            scheduleTimeRanges.forEach((timeRange) => {
                const [start, end] = timeRange.split("-").map(timeToMinutes);
                if (start <= currentTime && currentTime < end) {
                    currentOff = true;
                } else if (currentTime < start) {
                    nextOffTimes.push(timeRange);
                }
            });

            if (currentOff) {
                formattedSchedule = `<div class="sdstudio-highlight">Cвітло вимкнено згідно з чергою</div>`;
            } else {
                formattedSchedule = `<div class="sdstudio-no-off">Зараз немає відключень</div>`;
            }

            if (nextOffTimes.length > 0) {
                formattedSchedule += `<strong>Наступні вимкнення сьогодні:</strong>`;
                nextOffTimes.forEach((timeRange) => {
                    formattedSchedule += `<div>${timeRange}</div>`;
                });
            }

            return formattedSchedule;
        }

        function showSchedule(queue) {
            const schedule = getTodaysSchedule(queue);
            document.getElementById("sdstudio-times").innerHTML = formatSchedule(schedule);
            document.querySelectorAll(".sdstudio-button-group button").forEach((button) => button.classList.remove("active"));
            document.querySelector(`.sdstudio-button-group button:nth-child(${queue})`).classList.add("active");
            document.querySelector("#sdstudio-queue-number").innerHTML = `<span style="font-weight:bold; color:red">${queue}</span>`;
            setCookie("queue", queue, 7);
        }

        function updateTable() {
            const currentTime = getCurrentTime();
            const cells = document.querySelectorAll("#sdstudio-schedule-table td[data-time]");

            cells.forEach((cell) => {
                const schedule = cell.getAttribute("data-time");
                const updatedSchedule = schedule
                    .split("; ")
                    .map((timeRange) => {
                        const [start, end] = timeRange.split("-").map(timeToMinutes);
                        if (end <= currentTime) {
                            return `<span class="sdstudio-past-time">${timeRange}</span>`;
                        }
                        return `<span class="sdstudio-current-time">${timeRange}</span>`;
                    })
                    .join("; ");
                cell.innerHTML = updatedSchedule;
            });

            const today = new Date().getDate();
            const headers = document.querySelectorAll("#date-headers th span");
            headers.forEach((span) => {
                if (span.textContent == today.toString()) {
                    span.classList.add("current-day");
                    span.parentNode.classList.add("current-day-parent");
                }
            });
        }

        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
            const expires = "expires=" + d.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(";");
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === " ") c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function addEventListeners() {
            const buttons = document.querySelectorAll(".sdstudio-button-group button");
            buttons.forEach((button, index) => {
                button.addEventListener("click", function() {
                    showSchedule(index + 1);
                });
            });
        }

        window.onload = function () {
            updateTable();
            const savedQueue = getCookie("queue");
            if (savedQueue) {
                showSchedule(savedQueue);
            }
            addEventListeners();
        };
    });
</script>';



    // Повертаємо весь контент
    return $css . $html . $js;
}
}

// Реєструємо шорткод [SHORT_OTHER_CUSTOM]
add_shortcode('SHORT_OTHER_CUSTOM', 'SHORT_OTHER_CUSTOM_custom_shortcode');



/****
** 2024-06-08
** 10+2-
*****/

if (!function_exists('SHORT_2MINUS_10PLUS_table_function')) {
function SHORT_2MINUS_10PLUS_table_function() {
    // HTML код вашої сторінки
    $html = '
   <div>
    <h2 class="sdstudio-subtitle">Оберіть свою чергу (Оновлено від 2024-06-08 / 10+ 2-)</h2>
    <div class="sdstudio-button-group">
        <button onclick="showSchedule(1)">1</button>
        <button onclick="showSchedule(2)">2</button>
        <button onclick="showSchedule(3)">3</button>
        <button onclick="showSchedule(4)">4</button>
        <button onclick="showSchedule(5)">5</button>
        <button onclick="showSchedule(6)">6</button>
    </div>
    <div id="sdstudio-schedule">
        <h3>Стан відключень у Вашій черзі <span id="sdstudio-queue-number">❓</span> на <span id="current-date"></span> :</h3>
        <div id="sdstudio-times" style="min-height:90px">Оберіть чергу для результату</div>
    </div>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th rowspan="2">№ з/п</th>
                <th colspan="6">Числа місяця</th>
                <th rowspan="2">Черга ГПВ</th>
            </tr>
            <tr id="date-headers">
                <th><span>1</span>, <span>7</span>, <span>13</span>, <span>19</span>, <span>25</span>, <span>31</span></th>
                <th><span>2</span>, <span>8</span>, <span>14</span>, <span>20</span>, <span>26</span></th>
                <th><span>3</span>, <span>9</span>, <span>15</span>, <span>21</span>, <span>27</span></th>
                <th><span>4</span>, <span>10</span>, <span>16</span>, <span>22</span>, <span>28</span></th>
                <th><span>5</span>, <span>11</span>, <span>17</span>, <span>23</span>, <span>29</span></th>
                <th><span>6</span>, <span>12</span>, <span>18</span>, <span>24</span>, <span>30</span></th>
            </tr>
        </thead>
        <tbody id="sdstudio-schedule-table">
            <tr>
                <td>1</td>
                <td data-time="00:00-02:00; 12:00-14:00">00:00-02:00; 12:00-14:00</td>
                <td data-time="04:00-06:00; 16:00-18:00">04:00-06:00; 16:00-18:00</td>
                <td data-time="02:00-04:00; 14:00-16:00">02:00-04:00; 14:00-16:00</td>
                <td data-time="06:00-08:00; 18:00-20:00">06:00-08:00; 18:00-20:00</td>
                <td data-time="10:00-12:00; 22:00-24:00">10:00-12:00; 22:00-24:00</td>
                <td data-time="08:00-10:00; 20:00-22:00">08:00-10:00; 20:00-22:00</td>
                <td>1</td>
            </tr>
            <tr>
                <td>2</td>
                <td data-time="02:00-04:00; 14:00-16:00">02:00-04:00; 14:00-16:00</td>
                <td data-time="00:00-02:00; 12:00-14:00">00:00-02:00; 12:00-14:00</td>
                <td data-time="04:00-06:00; 16:00-18:00">04:00-06:00; 16:00-18:00</td>
                <td data-time="08:00-10:00; 20:00-22:00">08:00-10:00; 20:00-22:00</td>
                <td data-time="06:00-08:00; 18:00-20:00">06:00-08:00; 18:00-20:00</td>
                <td data-time="10:00-12:00; 22:00-24:00">10:00-12:00; 22:00-24:00</td>
                <td>2</td>
            </tr>
            <tr>
                <td>3</td>
                <td data-time="04:00-06:00; 16:00-18:00">04:00-06:00; 16:00-18:00</td>
                <td data-time="02:00-04:00; 14:00-16:00">02:00-04:00; 14:00-16:00</td>
                <td data-time="00:00-02:00; 12:00-14:00">00:00-02:00; 12:00-14:00</td>
                <td data-time="10:00-12:00; 22:00-24:00">10:00-12:00; 22:00-24:00</td>
                <td data-time="08:00-10:00; 20:00-22:00">08:00-10:00; 20:00-22:00</td>
                <td data-time="06:00-08:00; 18:00-20:00">06:00-08:00; 18:00-20:00</td>
                <td>3</td>
            </tr>
            <tr>
                <td>4</td>
                <td data-time="06:00-08:00; 18:00-20:00">06:00-08:00; 18:00-20:00</td>
                <td data-time="10:00-12:00; 22:00-24:00">10:00-12:00; 22:00-24:00</td>
                <td data-time="08:00-10:00; 20:00-22:00">08:00-10:00; 20:00-22:00</td>
                <td data-time="00:00-02:00; 12:00-14:00">00:00-02:00; 12:00-14:00</td>
                <td data-time="04:00-06:00; 16:00-18:00">04:00-06:00; 16:00-18:00</td>
                <td data-time="02:00-04:00; 14:00-16:00">02:00-04:00; 14:00-16:00</td>
                <td>4</td>
            </tr>
            <tr>
                <td>5</td>
                <td data-time="08:00-10:00; 20:00-22:00">08:00-10:00; 20:00-22:00</td>
                <td data-time="06:00-08:00; 18:00-20:00">06:00-08:00; 18:00-20:00</td>
                <td data-time="10:00-12:00; 22:00-24:00">10:00-12:00; 22:00-24:00</td>
                <td data-time="02:00-04:00; 14:00-16:00">02:00-04:00; 14:00-16:00</td>
                <td data-time="00:00-02:00; 12:00-14:00">00:00-02:00; 12:00-14:00</td>
                <td data-time="04:00-06:00; 16:00-18:00">04:00-06:00; 16:00-18:00</td>
                <td>5</td>
            </tr>
            <tr>
                <td>6</td>
                <td data-time="10:00-12:00; 22:00-24:00">10:00-12:00; 22:00-24:00</td>
                <td data-time="08:00-10:00; 20:00-22:00">08:00-10:00; 20:00-22:00</td>
                <td data-time="06:00-08:00; 18:00-20:00">06:00-08:00; 18:00-20:00</td>
                <td data-time="04:00-06:00; 16:00-18:00">04:00-06:00; 16:00-18:00</td>
                <td data-time="02:00-04:00; 14:00-16:00">02:00-04:00; 14:00-16:00</td>
                <td data-time="00:00-02:00; 12:00-14:00">00:00-02:00; 12:00-14:00</td>
                <td>6</td>
            </tr>
        </tbody>
    </table>
</div>
';

    // CSS код вашої сторінки
    $css = '
 <style>
             table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .sdstudio-button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .sdstudio-button-group button {
            padding: 10px;
            border: none;
            background-color: #ffffff;
            color: black;
            font-size: 18px;
            border-radius: 0;
            cursor: pointer;
            border: 2px solid red;
            transition: all 0.3s;
        }
        .sdstudio-button-group button.active {
            background-color: red;
            color: white;
        }
        #sdstudio-schedule {
            margin-top: 20px;
            font-size: 18px;
        }
        .sdstudio-highlight {
            background-color: red;
            color: white;
            padding: 5px;
            margin-bottom: 10px;
        }
        .sdstudio-no-off {
            background-color: green;
            color: white;
            padding: 5px;
            margin-bottom: 10px;
        }
        .sdstudio-past-time {
            color: grey;
        }
        .sdstudio-current-time {
            font-weight: bold;
        }
        #sdstudio-times {
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            th, td {
                font-size: 14px;
            }
            .sdstudio-button-group button {
                font-size: 16px;
                padding: 8px;
            }
        }
        .table-container {
            overflow-x: auto;
        }
        .current-day {
            border: 2px solid green;
        }
        .current-day-parent {
            background-color: lightgreen;
        }
    </style>';

    // JS код вашої сторінки
    $js = '
<script>
   document.addEventListener("DOMContentLoaded", function() {
    const schedules = {
        1: {
            "1, 7, 13, 19, 25, 31": "00:00-02:00; 12:00-14:00",
            "2, 8, 14, 20, 26": "04:00-06:00; 16:00-18:00",
            "3, 9, 15, 21, 27": "02:00-04:00; 14:00-16:00",
            "4, 10, 16, 22, 28": "06:00-08:00; 18:00-20:00",
            "5, 11, 17, 23, 29": "10:00-12:00; 22:00-24:00",
            "6, 12, 18, 24, 30": "08:00-10:00; 20:00-22:00"
        },
        2: {
            "1, 7, 13, 19, 25, 31": "02:00-04:00; 14:00-16:00",
            "2, 8, 14, 20, 26": "00:00-02:00; 12:00-14:00",
            "3, 9, 15, 21, 27": "04:00-06:00; 16:00-18:00",
            "4, 10, 16, 22, 28": "08:00-10:00; 20:00-22:00",
            "5, 11, 17, 23, 29": "06:00-08:00; 18:00-20:00",
            "6, 12, 18, 24, 30": "10:00-12:00; 22:00-24:00"
        },
        3: {
            "1, 7, 13, 19, 25, 31": "04:00-06:00; 16:00-18:00",
            "2, 8, 14, 20, 26": "02:00-04:00; 14:00-16:00",
            "3, 9, 15, 21, 27": "00:00-02:00; 12:00-14:00",
            "4, 10, 16, 22, 28": "10:00-12:00; 22:00-24:00",
            "5, 11, 17, 23, 29": "08:00-10:00; 20:00-22:00",
            "6, 12, 18, 24, 30": "06:00-08:00; 18:00-20:00"
        },
        4: {
            "1, 7, 13, 19, 25, 31": "06:00-08:00; 18:00-20:00",
            "2, 8, 14, 20, 26": "10:00-12:00; 22:00-24:00",
            "3, 9, 15, 21, 27": "08:00-10:00; 20:00-22:00",
            "4, 10, 16, 22, 28": "00:00-02:00; 12:00-14:00",
            "5, 11, 17, 23, 29": "04:00-06:00; 16:00-18:00",
            "6, 12, 18, 24, 30": "02:00-04:00; 14:00-16:00"
        },
        5: {
            "1, 7, 13, 19, 25, 31": "08:00-10:00; 20:00-22:00",
            "2, 8, 14, 20, 26": "06:00-08:00; 18:00-20:00",
            "3, 9, 15, 21, 27": "10:00-12:00; 22:00-24:00",
            "4, 10, 16, 22, 28": "02:00-04:00; 14:00-16:00",
            "5, 11, 17, 23, 29": "00:00-02:00; 12:00-14:00",
            "6, 12, 18, 24, 30": "04:00-06:00; 16:00-18:00"
        },
        6: {
            "1, 7, 13, 19, 25, 31": "10:00-12:00; 22:00-24:00",
            "2, 8, 14, 20, 26": "08:00-10:00; 20:00-22:00",
            "3, 9, 15, 21, 27": "06:00-08:00; 18:00-20:00",
            "4, 10, 16, 22, 28": "04:00-06:00; 16:00-18:00",
            "5, 11, 17, 23, 29": "02:00-04:00; 14:00-16:00",
            "6, 12, 18, 24, 30": "00:00-02:00; 12:00-14:00"
        }
    };

    function getCurrentTime() {
        const now = new Date();
        return now.getHours() * 60 + now.getMinutes();
    }

    function timeToMinutes(time) {
        const [hours, minutes] = time.split(":").map(Number);
        return hours * 60 + minutes;
    }

    function getTodaysSchedule(queue) {
        const today = new Date().getDate();
        let schedule = "";
        for (let dates in schedules[queue]) {
            if (dates.split(", ").map(Number).includes(today)) {
                schedule = schedules[queue][dates];
                break;
            }
        }
        return schedule;
    }

    function getTomorrowsSchedule(queue) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const day = tomorrow.getDate();
        const month = tomorrow.getMonth() + 1;
        let schedule = "";
        for (let dates in schedules[queue]) {
            if (dates.split(", ").map(Number).includes(day)) {
                schedule = schedules[queue][dates];
                break;
            }
        }
        return schedule;
    }

    function formatSchedule(schedule) {
        const currentTime = getCurrentTime();
        let formattedSchedule = "";
        let currentOff = false;
        let nextOffTimes = [];
        const scheduleTimeRanges = schedule.split("; ");

        scheduleTimeRanges.forEach((timeRange) => {
            const [start, end] = timeRange.split("-").map(timeToMinutes);
            if (start <= currentTime && currentTime < end) {
                currentOff = true;
            } else if (currentTime < start) {
                nextOffTimes.push(timeRange);
            }
        });

        if (currentOff) {
            formattedSchedule = `<div class="sdstudio-highlight">Cвітло вимкнено згідно з чергою</div>`;
        } else {
            formattedSchedule = `<div class="sdstudio-no-off">Зараз немає відключень</div>`;
        }

        if (nextOffTimes.length > 0) {
            formattedSchedule += `<strong>Наступні вимкнення сьогодні:</strong>`;
            nextOffTimes.forEach((timeRange) => {
                formattedSchedule += `<div>${timeRange}</div>`;
            });
        }

        return formattedSchedule;
    }

    function formatTomorrowsSchedule(schedule) {
        let formattedSchedule = "";
        let nextOffTimes = [];
        const scheduleTimeRanges = schedule.split("; ");

        scheduleTimeRanges.forEach((timeRange) => {
            nextOffTimes.push(timeRange);
        });

        if (nextOffTimes.length > 0) {
            formattedSchedule += `<strong>Наступні вимкнення на завтра:</strong>`;
            nextOffTimes.forEach((timeRange) => {
                formattedSchedule += `<div>${timeRange}</div>`;
            });
        }

        return formattedSchedule;
    }

    function showSchedule(queue) {
        const schedule = getTodaysSchedule(queue);
        const tomorrowSchedule = getTomorrowsSchedule(queue);
        const todayScheduleHTML = formatSchedule(schedule);
        const tomorrowScheduleHTML = formatTomorrowsSchedule(tomorrowSchedule);
        document.getElementById("sdstudio-times").innerHTML = todayScheduleHTML + tomorrowScheduleHTML;
        document.querySelectorAll(".sdstudio-button-group button").forEach((button) => button.classList.remove("active"));
        document.querySelector(`.sdstudio-button-group button:nth-child(${queue})`).classList.add("active");
        document.querySelector("#sdstudio-queue-number").innerHTML = `<span style="font-weight:bold; color:red">${queue}</span>`;
        setCookie("queue", queue, 7);
    }

    function updateTable() {
        const currentTime = getCurrentTime();
        const cells = document.querySelectorAll("#sdstudio-schedule-table td[data-time]");

        cells.forEach((cell) => {
            const schedule = cell.getAttribute("data-time");
            const updatedSchedule = schedule
                .split("; ")
                .map((timeRange) => {
                    const [start, end] = timeRange.split("-").map(timeToMinutes);
                    if (end <= currentTime) {
                        return `<span class="sdstudio-past-time">${timeRange}</span>`;
                    }
                    return `<span class="sdstudio-current-time">${timeRange}</span>`;
                })
                .join("; ");
            cell.innerHTML = updatedSchedule;
        });

        const today = new Date().getDate();
        const headers = document.querySelectorAll("#date-headers th span");
        headers.forEach((span) => {
            if (span.textContent == today.toString()) {
                span.classList.add("current-day");
                span.parentNode.classList.add("current-day-parent");
            }
        });
    }

    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(";");
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === " ") c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function addEventListeners() {
        const buttons = document.querySelectorAll(".sdstudio-button-group button");
        buttons.forEach((button, index) => {
            button.addEventListener("click", function() {
                showSchedule(index + 1);
            });
        });
    }

    window.onload = function () {
        const currentDate = new Date().toISOString().split("T")[0];
        document.getElementById("current-date").textContent = currentDate;
        updateTable();
        const savedQueue = getCookie("queue");
        if (savedQueue) {
            showSchedule(savedQueue);
        }
        addEventListeners();
    };
});
</script>';



    // Повертаємо весь контент
    return $css . $html . $js;
}
}

// Реєструємо шорткод [SHORT_OTHER_CUSTOM]
add_shortcode('SHORT_2MINUS_10PLUS_table', 'SHORT_2MINUS_10PLUS_table_function');


/****
 ** 2024-06-24
 ** Оберіть свою чергу (Оновлено від 2024-06-24 / 4+ 2- та 2+ 4-)
 *****/

if (!function_exists('SHORT_4PLUS_2MINUS_ta_4MINUS_2PLUS__table_function')) {
    function SHORT_4PLUS_2MINUS_ta_4MINUS_2PLUS__table_function() {
        // HTML код вашої сторінки
        $html = '<div>
    <h2 class="sdstudio-subtitle">Оберіть свою чергу (Оновлено від 2024-06-24 / 4+ 2- та 2+ 4-)</h2>
    <div class="sdstudio-button-group">
        <button onclick="showSchedule(1)">1</button>
        <button onclick="showSchedule(2)">2</button>
        <button onclick="showSchedule(3)">3</button>
        <button onclick="showSchedule(4)">4</button>
        <button onclick="showSchedule(5)">5</button>
        <button onclick="showSchedule(6)">6</button>
    </div>
    <div id="sdstudio-schedule">
        <h3>Стан відключень у Вашій черзі <span id="sdstudio-queue-number">❓</span> на <span id="current-date"></span> :</h3>
        <div id="sdstudio-times" style="min-height:90px">Оберіть чергу для результату</div>
    </div>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th rowspan="2">№ з/п</th>
                <th colspan="6">Числа місяця</th>
                <th rowspan="2">Черга ГПВ</th>
            </tr>
            <tr id="date-headers">
                <th><span>1</span>, <span>7</span>, <span>13</span>, <span>19</span>, <span>25</span>, <span>31</span></th>
                <th><span>2</span>, <span>8</span>, <span>14</span>, <span>20</span>, <span>26</span></th>
                <th><span>3</span>, <span>9</span>, <span>15</span>, <span>21</span>, <span>27</span></th>
                <th><span>4</span>, <span>10</span>, <span>16</span>, <span>22</span>, <span>28</span></th>
                <th><span>5</span>, <span>11</span>, <span>17</span>, <span>23</span>, <span>29</span></th>
                <th><span>6</span>, <span>12</span>, <span>18</span>, <span>24</span>, <span>30</span></th>
            </tr>
        </thead>
        <tbody id="sdstudio-schedule-table">
            <tr>
                <td>1</td>
                <td data-time="00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00">00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00</td>
                <td data-time="02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00">02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00</td>
                <td data-time="02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00</td>
                <td data-time="00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00">00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00</td>
                <td data-time="04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00">04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00</td>
                <td data-time="02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00">02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00</td>
                <td>1</td>
            </tr>
            <tr>
                <td>2</td>
                <td data-time="02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00</td>
                <td data-time="00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00">00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00</td>
                <td data-time="02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00">02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00</td>
                <td data-time="02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00">02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00</td>
                <td data-time="00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00">00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00</td>
                <td data-time="04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00">04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00</td>
                <td>2</td>
            </tr>
            <tr>
                <td>3</td>
                <td data-time="02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00">02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00</td>
                <td data-time="02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00</td>
                <td data-time="00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00">00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00</td>
                <td data-time="04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00">04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00</td>
                <td data-time="02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00">02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00</td>
                <td data-time="00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00">00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00</td>
                <td>3</td>
            </tr>
            <tr>
                <td>4</td>
                <td data-time="00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00">00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00</td>
                <td data-time="04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00">04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00</td>
                <td data-time="02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00">02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00</td>
                <td data-time="00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00">00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00</td>
                <td data-time="02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00">02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00</td>
                <td data-time="02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00</td>
                <td>4</td>
            </tr>
            <tr>
                <td>5</td>
                <td data-time="02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00">02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00</td>
                <td data-time="00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00">00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00</td>
                <td data-time="04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00">04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00</td>
                <td data-time="02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00</td>
                <td data-time="00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00">00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00</td>
                <td data-time="02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00">02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00</td>
                <td>5</td>
            </tr>
            <tr>
                <td>6</td>
                <td data-time="04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00">04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00</td>
                <td data-time="02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00">02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00</td>
                <td data-time="00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00">00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00</td>
                <td data-time="02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00">02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00</td>
                <td data-time="02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00">02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00</td>
                <td data-time="00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00">00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00</td>
                <td>6</td>
            </tr>
        </tbody>
    </table>
</div>
';

        // CSS код вашої сторінки
        $css = '
 <style>
          table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid black;
    padding: 8px;
    text-align: center;
}
th {
    background-color: #f2f2f2;
}
.sdstudio-button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}
.sdstudio-button-group button {
    padding: 10px;
    border: none;
    background-color: #ffffff;
    color: black;
    font-size: 18px;
    border-radius: 0;
    cursor: pointer;
    border: 2px solid red;
    transition: all 0.3s;
}
.sdstudio-button-group button.active {
    background-color: red;
    color: white;
}
#sdstudio-schedule {
    margin-top: 20px;
    font-size: 18px;
}
.sdstudio-highlight {
    background-color: red;
    color: white;
    padding: 5px;
    margin-bottom: 10px;
}
.sdstudio-no-off {
    background-color: green;
    color: white;
    padding: 5px;
    margin-bottom: 10px;
}
.sdstudio-past-time {
    color: grey;
}
.sdstudio-current-time {
    font-weight: bold;
}
#sdstudio-times {
    margin-bottom: 10px;
}
@media (max-width: 600px) {
    th, td {
        font-size: 14px;
    }
    .sdstudio-button-group button {
        font-size: 16px;
        padding: 8px;
    }
}
.table-container {
    overflow-x: auto;
}
.current-day {
    border: 2px solid green;
}
.current-day-parent {
    background-color: lightgreen;
}
    </style>';

        // JS код вашої сторінки
        $js = '
<script>
   document.addEventListener("DOMContentLoaded", function() {
    const schedules = {
        1: {
            "1, 7, 13, 19, 25, 31": "00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00",
            "2, 8, 14, 20, 26": "02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00",
            "3, 9, 15, 21, 27": "02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00",
            "4, 10, 16, 22, 28": "00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00",
            "5, 11, 17, 23, 29": "04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00",
            "6, 12, 18, 24, 30": "02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00"
        },
        2: {
            "1, 7, 13, 19, 25, 31": "02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00",
            "2, 8, 14, 20, 26": "00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00",
            "3, 9, 15, 21, 27": "02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00",
            "4, 10, 16, 22, 28": "02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00",
            "5, 11, 17, 23, 29": "00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00",
            "6, 12, 18, 24, 30": "04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00"
        },
        3: {
            "1, 7, 13, 19, 25, 31": "02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00",
            "2, 8, 14, 20, 26": "02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00",
            "3, 9, 15, 21, 27": "00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00",
            "4, 10, 16, 22, 28": "04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00",
            "5, 11, 17, 23, 29": "02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00",
            "6, 12, 18, 24, 30": "00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00"
        },
        4: {
            "1, 7, 13, 19, 25, 31": "00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00",
            "2, 8, 14, 20, 26": "04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00",
            "3, 9, 15, 21, 27": "02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00",
            "4, 10, 16, 22, 28": "00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00",
            "5, 11, 17, 23, 29": "02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00",
            "6, 12, 18, 24, 30": "02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00"
        },
        5: {
            "1, 7, 13, 19, 25, 31": "02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00",
            "2, 8, 14, 20, 26": "00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00",
            "3, 9, 15, 21, 27": "04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00",
            "4, 10, 16, 22, 28": "02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00",
            "5, 11, 17, 23, 29": "00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00",
            "6, 12, 18, 24, 30": "02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00"
        },
        6: {
            "1, 7, 13, 19, 25, 31": "04:00-06:00; 08:00-12:00; 16:00-18:00; 20:00-24:00",
            "2, 8, 14, 20, 26": "02:00-04:00; 06:00-10:00; 14:00-16:00; 18:00-22:00",
            "3, 9, 15, 21, 27": "00:00-02:00; 04:00-08:00; 12:00-14:00; 16:00-20:00",
            "4, 10, 16, 22, 28": "02:00-06:00; 10:00-12:00; 14:00-18:00; 22:00-24:00",
            "5, 11, 17, 23, 29": "02:00-04:00; 08:00-10:00; 12:00-16:00; 20:00-22:00",
            "6, 12, 18, 24, 30": "00:00-02:00; 06:00-08:00; 10:00-14:00; 18:00-20:00; 22:00-24:00"
        }
    };

    function getCurrentTime() {
        const now = new Date();
        return now.getHours() * 60 + now.getMinutes();
    }

    function timeToMinutes(time) {
        const [hours, minutes] = time.split(":").map(Number);
        return hours * 60 + minutes;
    }

    function getTodaysSchedule(queue) {
        const today = new Date().getDate();
        let schedule = "";
        for (let dates in schedules[queue]) {
            if (dates.split(", ").map(Number).includes(today)) {
                schedule = schedules[queue][dates];
                break;
            }
        }
        return schedule;
    }

    function getTomorrowsSchedule(queue) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const day = tomorrow.getDate();
        let schedule = "";
        for (let dates in schedules[queue]) {
            if (dates.split(", ").map(Number).includes(day)) {
                schedule = schedules[queue][dates];
                break;
            }
        }
        return schedule;
    }

    function formatSchedule(schedule) {
        const currentTime = getCurrentTime();
        let formattedSchedule = "";
        let currentOff = false;
        let currentOffTime = "";
        let nextOffTimes = [];
        const scheduleTimeRanges = schedule.split("; ");

        scheduleTimeRanges.forEach((timeRange) => {
            const [start, end] = timeRange.split("-").map(timeToMinutes);
            if (start <= currentTime && currentTime < end) {
                currentOff = true;
                currentOffTime = timeRange;
            } else if (currentTime < start) {
                nextOffTimes.push(timeRange);
            }
        });

        if (currentOff) {
            formattedSchedule = `<div class="sdstudio-highlight">Cвітло вимкнено за чергою з ${currentOffTime}</div>`;
        } else {
            formattedSchedule = `<div class="sdstudio-no-off">Зараз немає відключень</div>`;
        }

        if (nextOffTimes.length > 0) {
            formattedSchedule += `<strong>Наступні вимкнення сьогодні:</strong>`;
            nextOffTimes.forEach((timeRange) => {
                formattedSchedule += `<div>${timeRange}</div>`;
            });
        }

        return formattedSchedule;
    }

    function formatTomorrowsSchedule(schedule) {
        let formattedSchedule = "";
        let nextOffTimes = [];
        const scheduleTimeRanges = schedule.split("; ");

        scheduleTimeRanges.forEach((timeRange) => {
            nextOffTimes.push(timeRange);
        });

        if (nextOffTimes.length > 0) {
            formattedSchedule += `<strong>Наступні вимкнення на завтра:</strong>`;
            nextOffTimes.forEach((timeRange) => {
                formattedSchedule += `<div>${timeRange}</div>`;
            });
        }

        return formattedSchedule;
    }

    function showSchedule(queue) {
        const schedule = getTodaysSchedule(queue);
        const tomorrowSchedule = getTomorrowsSchedule(queue);
        const todayScheduleHTML = formatSchedule(schedule);
        const tomorrowScheduleHTML = formatTomorrowsSchedule(tomorrowSchedule);
        document.getElementById("sdstudio-times").innerHTML = todayScheduleHTML + tomorrowScheduleHTML;
        document.querySelectorAll(".sdstudio-button-group button").forEach((button) => button.classList.remove("active"));
        document.querySelector(`.sdstudio-button-group button:nth-child(${queue})`).classList.add("active");
        document.querySelector("#sdstudio-queue-number").innerHTML = `<span style="font-weight:bold; color:red">${queue}</span>`;
        setCookie("queue", queue, 7);
    }

    function updateTable() {
        const currentTime = getCurrentTime();
        const cells = document.querySelectorAll("#sdstudio-schedule-table td[data-time]");

        cells.forEach((cell) => {
            const schedule = cell.getAttribute("data-time");
            const updatedSchedule = schedule
                .split("; ")
                .map((timeRange) => {
                    const [start, end] = timeRange.split("-").map(timeToMinutes);
                    if (end <= currentTime) {
                        return `<span class="sdstudio-past-time">${timeRange}</span>`;
                    }
                    return `<span class="sdstudio-current-time">${timeRange}</span>`;
                })
                .join("; ");
            cell.innerHTML = updatedSchedule;
        });

        const today = new Date().getDate();
        const headers = document.querySelectorAll("#date-headers th span");
        headers.forEach((span) => {
            if (span.textContent == today.toString()) {
                span.classList.add("current-day");
                span.parentNode.classList.add("current-day-parent");
            }
        });
    }

    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(";");
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === " ") c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function addEventListeners() {
        const buttons = document.querySelectorAll(".sdstudio-button-group button");
        buttons.forEach((button, index) => {
            button.addEventListener("click", function() {
                showSchedule(index + 1);
            });
        });
    }

    window.onload = function () {
        const currentDate = new Date().toISOString().split("T")[0];
        document.getElementById("current-date").textContent = currentDate;
        updateTable();
        const savedQueue = getCookie("queue");
        if (savedQueue) {
            showSchedule(savedQueue);
        }
        addEventListeners();
    };
});
</script>';



        // Повертаємо весь контент
        return $css . $html . $js;
    }
}

// Реєструємо шорткод [SHORT_4PLUS_2MINUS_ta_4MINUS_2PLUS]
add_shortcode('SHORT_4PLUS_2MINUS_ta_4MINUS_2PLUS', 'SHORT_4PLUS_2MINUS_ta_4MINUS_2PLUS__table_function');