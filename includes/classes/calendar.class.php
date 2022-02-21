<?php

    class calendar {
        public $nameLength = 20;
        
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
            unset($_GET['date']);

            if(!empty($_GET)) {
                foreach($_GET as $index => $value) {
                    $gets .= '&' . $index . '=' . $value;
                }
            }
            
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
                $headings .= '<th>' . $dow . '</th>';
            }

            $calendar = 
                '<div class="calendar">
                    <h3 class="calendarHeading">' . date('F Y', strtotime($this->calendarDate)) .  '</h3>

                    <div class="table-responsive">
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

            for($d = 1 - $firstWeekStart; $d <= intval(date('d', strtotime($this->lastOfMonth)) + (6 - $lastWeekEnd)); $d++) {
                $sign = ($d < 0 ? '-' : '+');
                $days = abs($d);
                $today = date('Y-m-d', strtotime($this->firstOfMonth . $sign . $days . 'days'));

                if($newRow == true) {
                    $calendar .= 
                        '<tr>';
                    $newRow = false;
                }

                $calendar .=
                    '<td ' . ($today < date('Y-m-d') ? 'class="calendarPast"' : '') . '>
                        <div class="calendarInner">
                            <span class="calendarDay' . ($today == date('Y-m-d') ? ' current' : '') . '">' . date('d', strtotime($today)) . '</span>' .
                            $this->displayevents($today) . 
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
                $firstOfWeek = $this->calendarDay;
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
                $nextEventName = 'No upcoming events';

                if(!empty($todaysEvents)) {
                    usort($todaysEvents, [$this, 'sorteventtimes']);
                    
                    //Get next upcoming event for current day
                    if($today === $todaysDate) {
                        $nextEvents = 
                            array_values(
                                array_filter($todaysEvents, function($value) use ($currentTime) {
                                    if($value['time'] < $currentTime && $value['end_time'] >= $currentTime && $value['time'] <= $value['end_time']) {
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
                            (!empty($todaysEvents) && count($todaysEvents) > 1 ? $this->moreeventsmodal($today, 'MORE') : '') .
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
            global $mysqli;

            if(is_array($array) && !empty($array)) {
                $this->events = $array;
                return;
            }

            $state = (!empty($_SESSION['adminid']) ? 1 : 2);
            $eventArray = [];
            $i = 0;
            
            $getEvents = $mysqli->prepare("SELECT * FROM `events` WHERE state >= ?");
            $getEvents->bind_param('i', $state);
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

                    $start = new DateTime($date);
                    $end = new DateTime(date('Y-m-d', strtotime($endDate . '+1 day')));
                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($start, $interval, $end);

                    foreach($period as $day) {
                        $eventArray[$day->format('Y-m-d')][$i] = [
                            'name' => $event['name'],
                            'link' => (substr($event['url'], 0, 4) === 'http' ? $event['url'] : 'events/' . $event['url']),
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

?>