<?php

    //V1.0.5
    define('EVENTS_PREFIX', 'events/');
    
    class calendar {
        public $nameLength = 20;
        public $modalLink = 'MORE';
        
        private $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        private $shortDaysOfWeek = []; //Populated in constructor

        private $firstOfMonth;
        private $lastOfMonth;
        private $firstDayOffset;
        private $lastDayOffset;

        private $currentDay;
        private $currentMonth;
        private $currentYear;
        private $currentDaysInMonth;

        private $calendarDate;
        private $calendarRows;
        private $events;
        private $controls;
        private $smallControls;

        public function __construct($date = '') {
            if(!empty($date) && date_parse($date)['year'] == true) {
                //Get the set day, month, year
                $this->currentDay = date('d', strtotime($date));
                $this->currentMonth = date('m', strtotime($date));
                $this->currentYear = date('Y', strtotime($date));

                $this->calendarDate = date('Y-m-d', strtotime($date));
            }
            else {
                //Get current day, month, year
                $this->currentDay = date('d');
                $this->currentMonth = date('m');
                $this->currentYear = date('Y');

                $this->calendarDate = date('Y-m-d');
            }

            //Populate the short days of the week
            foreach($this->daysOfWeek as $dow) {
                array_push($this->shortDaysOfWeek, substr($dow, 0, 3));
            }

            //Get the first and last day offset
            $this->firstOfMonth = date('Y-m-01', strtotime($this->calendarDate));
            $this->lastOfMonth = date('Y-m-t', strtotime($this->calendarDate));
            
            $this->firstDayOffset = array_search(date('l', strtotime($this->firstOfMonth)), $this->daysOfWeek);
            $this->lastDayOffset = count(array_slice($this->daysOfWeek, array_search(date('l', strtotime($this->lastOfMonth)), $this->daysOfWeek) + 1));

            //Get total days in month
            $this->currentDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->currentMonth, $this->currentYear);
            $this->calendarRows = ceil($this->currentDaysInMonth / 7);

            //Set the controls
            $gets = '';
            
            $getDate = $_GET['date'];
            $getUrl = $_GET['date'];

            unset($_GET['date']);
            unset($_GET['url']);

            if(!empty($_GET)) {
                foreach($_GET as $index => $value) {
                    $gets .= '&' . $index . '=' . $value;
                }
            }

            $_GET['date'] = $getDate;
            $_GET['url'] = $getUrl;
            
            $prevMonth = date('Y-m-d', strtotime($this->calendarDate . ' -1 month'));
            $nextMonth = date('Y-m-d', strtotime($this->calendarDate . ' +1 month'));
            $prevWeek = date('Y-m-d', strtotime($this->calendarDate . ' -1 week'));
            $nextWeek = date('Y-m-d', strtotime($this->calendarDate . ' +1 week'));
            $page = explode('?', $_SERVER['REQUEST_URI'])[0];

            $this->controls = 
                '<div class="calendarControls">
                    <a href="' . $page . '?date=' . $prevMonth . $gets . '" class="btn btn-primary text-light">Previous Month</a>
                    <a href="' . $page . '?date=' . $nextMonth . $gets . '" class="btn btn-primary text-light">Next Month</a>
                </div>';

            $this->smallControls = 
            '<div class="calendarControls">
                <a href="' . $page . '?date=' . $prevWeek . $gets . '" class="btn btn-primary text-light">Previous Week</a>
                <a href="' . $page . '?date=' . $nextWeek . $gets . '" class="btn btn-primary text-light">Next Week</a>
            </div>';
        }

        public function display($showControls = true) {
            //Display headings based on order of days of the week
            $headings = '';

            foreach($this->daysOfWeek as $dow) {
                $headings .= '<th>' . substr($dow, 0, 3) . '<span class="headingSuffix">' . substr($dow, 3, strlen($dow)) . '</span></th>';
            }

            $calendar = 
                '<div class="calendar">
                    <h3 class="calendarHeading">' . date('F Y', strtotime($this->calendarDate)) .  '</h3>

                    <div class="table-responsive-lg">
                        <table class="calendarTable table table-light">
                            <thead>
                                <tr>' .
                                    $headings .
                                '</tr>
                            </thead>

                            <tbody>';

            //Loop days of month and display table rows
            $firstWeekStart = date('w', strtotime($this->firstOfMonth));
            $lastWeekEnd = date('w', strtotime($this->lastOfMonth));
            $i = 1;
            $newRow = true;

            for($d = 0 - $firstWeekStart; $d < intval(date('d', strtotime($this->lastOfMonth)) + (6 - $lastWeekEnd)); $d++) {
                $sign = ($d <= 0 ? '-' : '+');
                $days = abs($d);
                $today = date('Y-m-d', strtotime($this->firstOfMonth . $sign . $days . 'days'));

                if($newRow == true) {
                    $calendar .= 
                        '<tr>';
                    $newRow = false;
                }

                if(!empty($this->displayevents($today))) {
                    $id = 'events' . date('Ymd', strtotime($today));
                    $calendarDay = 
                        '<a href="#" class="moreEventsLink" data-bs-toggle="modal" data-bs-target="#' . $id . '"><span class="calendarDay' . ($today == date('Y-m-d') ? ' current' : '') . '">' . date('d', strtotime($today)) . '</span></a>';
                }
                else {
                    $calendarDay = 
                        '<span class="calendarDay' . ($today == date('Y-m-d') ? ' current' : '') . '">' . date('d', strtotime($today)) . '</span>';
                }

                $calendar .=
                    '<td ' . ($today < date('Y-m-d') ? 'class="calendarPast"' : '') . '>
                        <div class="calendarInner">' .
                            $calendarDay .
                            $this->displayevents($today) . $this->moreeventsmodal($today) .
                        '</div>
                    </td>';

                if($i % 7 == 0) {
                    $calendar .= 
                        '</tr>';
                    $newRow = true;
                }

                $i++;
            }

            $calendar .=    
                            '</tbody>
                        </table>
                    </div>' . 
                    ($showControls === true ? $this->controls : '') .
                '</div>';

            echo $calendar;
        }

        public function displaysmall($showControls = true) {
            $todaysDate = date('Y-m-d');

            //Get the first day of the week
            if(date('w', strtotime($this->calendarDate)) > 0) {
                $firstOfWeek = date('Y-m-d', strtotime('last sunday', strtotime($this->calendarDate)));
            }
            else {
                $firstOfWeek = $this->calendarDate;
            }

            $calendar =
                '<div class="calendarSmall">
                    <h3 class="calendarHeading">' . date('d M y', strtotime($firstOfWeek)) . ' to ' . date('d M y', strtotime($firstOfWeek . ' +6 days')) .  '</h3>';

            for($d = 0; $d < 7; $d++) {
                $today = date('Y-m-d', strtotime($firstOfWeek . ' +' . $d . ' days'));
                $actualDay = date('d', strtotime($today));

                //Find first event for today with a time after current
                $currentTime = date('H:i');
                $todaysEvents = $this->events[$today];
                $nextEvents = null;

                switch($todaysDate) {
                    case $todaysDate >= $today:
                        $nextEventName = 'No past events';
                        break;
                    default:
                        $nextEventName = 'No upcoming events';
                        break;
                }                

                if(!empty($todaysEvents)) {
                    usort($todaysEvents, [$this, 'sorteventtimes']);
                    
                    //Get next upcoming event for current day
                    if($today === $todaysDate) {
                        $nextEvents = 
                            array_values(
                                array_filter($todaysEvents, function($value) use ($currentTime) {
                                    if(($value['time'] < $currentTime && $value['end_time'] >= $currentTime && $value['time'] <= $value['end_time']) || $value['time'] >= $currentTime) {
                                        return true;
                                    }
                                    elseif(!empty($value['time']) && empty($value['end_time']) && $value['time'] >= $currentTime) {
                                        return true;
                                    }
                                    
                                    return false;
                                })
                            );

                            $nextEvent = (is_array($nextEvents) && !empty($nextEvents) ? $nextEvents[0] : null);
                    
                        if(!empty($nextEvent['name'])) {
                            $nextEventName = (strlen($nextEvent['name']) > ($this->nameLength * 2) ? rtrim(substr($nextEvent['name'], 0, $this->nameLength)) . '...' : $nextEvent['name']);
                        }
                    }
                    //Get last event for previous days
                    elseif($today < $todaysDate) {
                        $nextEvent = array_reverse($todaysEvents)[0];
                        $nextEventName = $nextEvent['name'];
                    }
                    //Get first event for next days
                    else {
                        $nextEvent = $todaysEvents[0];
                        $nextEventName = $nextEvent['name'];
                    }
                }

                $calendar .=
                    '<div class="row">
                        <div class="col-auto calendarDow">' .
                            $this->shortDaysOfWeek[$d] .
                        '</div>

                        <div class="col-auto calendarDate">
                            <span class="calendarDay' . ($today === $todaysDate ? ' current' : '') . '">' . $actualDay . '</span>
                        </div>

                        <div class="col col-sm-auto calendarTime">' .
                            (!empty($nextEvent['time']) ? $nextEvent['time'] : '') . (!empty($nextEvent['end_time']) ? ' - ' . $nextEvent['end_time'] : '') .
                        '</div>

                        <div class="col-sm calendarEvent">' .
                            (!empty($nextEvent['link']) ? '<a href="' . $nextEvent['link'] . '">' . $nextEventName . '</a>' : $nextEventName) .
                            (!empty($todaysEvents) && count($todaysEvents) > 1 ? $this->moreeventsmodal($today, $this->modalLink) : '') .
                        '</div>
                    </div>';

                unset($nextEvent);
            }

            $calendar .=
                ($showControls === true ? $this->smallControls : '') . 
                '</div>';

            echo $calendar;
        }

        private function sorteventtimes($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        }

        private function displayevents($date, $charLimit = 0) {
            $events = '';

            if(!is_numeric($charLimit) || $charLimit <= 0) {
                $charLimit = $this->nameLength;
            }

            if(!empty($this->events[$date])) {
                usort($this->events[$date], [$this, 'sorteventtimes']);

                foreach($this->events[$date] as $event) {
                    $styles = '';

                    if(!empty($event['time']) && !empty($event['end_time'])) {
                        $eventTime = $event['time'] . ' to ' . $event['end_time'];
                    }
                    elseif(!empty($event['time'])) {
                        $eventTime = $event['time'];
                    }

                    if(!empty($event['background_colour'])) {
                        $styles .= 'background-color: ' . $event['background_colour'] . ';';
                    }
                    
                    if(!empty($event['text_colour'])) {
                        $styles .= 'color: ' . $event['text_colour'] . ';';
                    }

                    $events .=
                        '<a href="' . $event['link'] . '" class="eventLink">
                            <div class="calendarEvent" style="' . $styles . '">' .
                                (!empty($eventTime) ? $eventTime . ': ' : '') .
                                (strlen($event['name']) > $charLimit ? rtrim(substr($event['name'], 0, $charLimit)) . '...' : $event['name']) .
                            '</div>
                        </a>';
                }
            }

            return $events;
        }

        private function moreeventsmodal($date, $linkText = '') {
            $id = 'events' . date('Ymd', strtotime($date));

            $modal = 
                '<div class="modal fade calendarModal" id="' . $id . '" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Events for ' . date('jS F Y', strtotime($date)) . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">' .
                                $this->displayevents($date, 200) . 
                            '</div>
                        </div>
                    </div>
                </div>';

            if(!empty($linkText)) {
                $output = 
                    '<button type="button" class="btn btn-primary moreEvents" data-bs-toggle="modal" data-bs-target="#' . $id . '"><small>' . $linkText . '</small></button>' . $modal;

                return $output . $modal;
            }
            
            return $modal;
        }

        public function loadevents($array = []) {
            global $mysqli, $state;

            if(is_array($array) && !empty($array)) {
                $this->events = $array;
                return;
            }

            $eventArray = [];
            $i = 0;
            
            $calStart = date('Y-m-d 00:00:00', strtotime($this->firstOfMonth . ' -' . $this->firstDayOffset . 'days'));
            $calEnd = date('Y-m-d 00:00:00', strtotime($this->lastOfMonth . ' +' . $this->lastDayOffset . 'days'));
            
            $getEvents = $mysqli->prepare("SELECT * FROM `events` WHERE state >= ? AND start_date >= ? AND end_date <= ?");
            $getEvents->bind_param('iss', $state, $calStart, $calEnd);
            $getEvents->execute();
            $eventResult = $getEvents->get_result();
            
            if($eventResult->num_rows > 0) {
                while($event = $eventResult->fetch_assoc()) {
                    $styles = json_decode($event['styles'], true);
                    $styles = (!empty($styles) ? $styles : null);

                    $date = date('Y-m-d', strtotime($event['start_date']));
                    $time = date('H:i', strtotime($event['start_date']));
                    $endDate = date('Y-m-d', strtotime($event['end_date']));
                    $endTime = date('H:i', strtotime($event['end_date']));
                    $endTime = ($endTime === $time ? null : $endTime);

                    $start = new DateTime($date);
                    $end = new DateTime(date('Y-m-d', strtotime($endDate . '+1 day')));
                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($start, $interval, $end);

                    foreach($period as $day) {
                        $eventArray[$day->format('Y-m-d')][$i] = [
                            'name' => $event['name'],
                            'link' => (substr($event['url'], 0, 4) === 'http' ? $event['url'] : EVENT_PREFIX . $event['url']),
                            'excerpt' => $event['excerpt'],
                            'background_colour' => $styles['background'],
                            'text_colour' => $styles['text'],
                            'time' => $time,
                            'end_time' => $endTime
                        ];
                    }

                    $i++;
                }
            }

            $this->events = $eventArray;
            return;
        }
    }

    //Create database tables
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `events` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            excerpt VARCHAR(500),
            content TEXT,
            url VARCHAR(191) UNIQUE,
            author VARCHAR(255),
            featured_image VARCHAR(500),
            carousel LONGTEXT,
            template VARCHAR(255),
            start_date DATETIME DEFAULT CURRENT_TIMESTAMP(),
            end_date DATETIME DEFAULT CURRENT_TIMESTAMP(),
            last_edited DATETIME DEFAULT CURRENT_TIMESTAMP(),
            last_edited_by INT,
            state INT DEFAULT 0,
            meta_title VARCHAR(255),
            meta_description VARCHAR(500),
            meta_author VARCHAR(255),
            meta_keywords VARCHAR(255),
            styles TEXT DEFAULT NULL
        )"
    );

    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `event_styles` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(10) DEFAULT 'background',
            selector VARCHAR(191) UNIQUE DEFAULT NULL,
            value VARCHAR(50) DEFAULT NULL
        )"
    );

    $mysqli->query(
        "INSERT IGNORE INTO `event_styles` (type, selector, value) VALUES
        ('text', 'Dark', '#000'),
        ('text', 'Light', '#fff'),
        ('background', 'Black', '#212529'),
        ('background', 'White', 'white'),
        ('background', 'Grey', 'grey'),
        ('background', 'Red', 'crimson'),
        ('background', 'Orange', 'coral'),
        ('background', 'Yellow', 'goldenrod'),
        ('background', 'Green', 'mediumseagreen'),
        ('background', 'Blue', 'cornflowerblue'),
        ('background', 'Purple', 'blueviolet'),
        ('background', 'Pink', 'magenta')"
    );

    //Shortcode
    function calendar($params) {
        ob_start();

        $calendar = new calendar($_GET['date']);
        $calendar->loadevents();

        if(isset($params['controls']) && $params['controls'] === 'false') {
            $controls = false;
        }
        else {
            $controls = true;
        }

        if(isset($params['modallink'])) {
            $calendar->modalLink = $params['modallink'];
        }

        if(isset($params['namelength']) && intval($params['namelength']) > 0) {
            $calendar->nameLength = intval($params['namelength']);
        }

        switch($params['type']) {
            case 'small':
                $calendar->displaysmall($controls);
                break;
            default:
                $calendar->display($controls);
                break;
        }

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }

    //Insert menu item for admin
    add_admin_navigation([[
        'name' => 'Events',
        'link' => 'admin/plugin/calendar/manage-events',
        'icon' => 'fa fa-calendar-alt',
        'filename' => 'manage-events.php'
    ]], 1);

    if(isset($_GET['url']) && substr($_GET['url'], 0, strlen(EVENTS_PREFIX)) === EVENTS_PREFIX) {
        $url = explode(EVENTS_PREFIX, $_GET['url'])[1];

        $checkEvent = $mysqli->prepare("SELECT * FROM `events` AS posts WHERE url = ? AND posts.state >= ? LIMIT 1");
        $checkEvent->bind_param('si', $url, $state);
        $checkEvent->execute();
        $checkEventResult = $checkEvent->get_result();

        if($checkEventResult->num_rows > 0) {
            $event = $checkEventResult->fetch_assoc();
            $notFound = false;
            $contentId = $event['id'];
            
            if(!empty($event['template'])) {
                if(file_exists(dirname(__FILE__, 3) . '/templates/' . $event['template'] . '.php')) {
                    $templatePath .= 'includes/templates/';
                    $template = $event['template'] . '.php';
                }
            }
            else {
                $templatePath = dirname(__FILE__) . '/';
                $template = 'event_template.php';
            }
        }
    }

    //Load CSS
    $__pluginManager->storecss(dirname(__FILE__) . '/css/calendar.min.css');

    //Load JS
    $__pluginManager->storejs(dirname(__FILE__) . '/js/calendar.min.js');

?>