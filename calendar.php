<?php
    $title = 'Calendar Test';
    require_once(dirname(__FILE__) . '/includes/header.php');
?>

<div class="uc content">
    <?php 
        $testevents = [
            '2022-01-31' => [
                0 => [
                    'name' => 'jan test 1',
                    'link' => '#',
                    'excerpt' => '',
                    'background_colour' => '#ff0000',
                    'text_colour' => '',
                    'time' => '10:00',
                    'end_time' => '17:00'
                ],
                1 => [
                    'name' => 'event 2',
                    'link' => '#',
                    'excerpt' => '',
                    'background_colour' => '',
                    'text_colour' => '',
                    'time' => '12:00',
                    'end_time' => '14:00'
                ]
            ],
            '2022-02-27' => [
                0 => [
                    'name' => 'first of the day',
                    'link' => '#',
                    'excerpt' => '',
                    'background_colour' => '#ff0000',
                    'text_colour' => '',
                    'time' => '10:00',
                    'end_time' => '17:00'
                ],
                1 => [
                    'name' => 'last of the day',
                    'link' => '#',
                    'excerpt' => '',
                    'background_colour' => 'green',
                    'text_colour' => '',
                    'time' => '12:00',
                    'end_time' => '14:00'
                ]
            ],
            '2022-03-03' => [
                0 => [
                    'name' => 'march test 1',
                    'link' => '#',
                    'excerpt' => '',
                    'background_colour' => 'cobalt',
                    'text_colour' => '',
                    'time' => '10:00',
                    'end_time' => '17:00'
                ],
                1 => [
                    'name' => 'march test 2',
                    'link' => '#',
                    'excerpt' => '',
                    'background_colour' => 'orange',
                    'text_colour' => '',
                    'time' => '12:00',
                    'end_time' => '14:00'
                ]
            ]
        ];

        $calendar = new calendar((isset($_GET['date']) ? $_GET['date'] : ''));
        $calendar->loadevents();
        $calendar->display();

        echo '<br><br>';
        $calendar->displaysmall();
    ?>
</div>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>