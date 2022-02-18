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
            $page = explode('?', $_SERVER['REQUEST_URI'])[0];

            $this->controls = 
                '<div class="calendarControls">
                    <a href="' . $page . '?date=' . $prevMonth . $gets . '" class="btn btn-primary text-light">Previous Month</a>
                    <a href="' . $page . '?date=' . $nextMonth . $gets . '" class="btn btn-primary text-light">Next Month</a>
                </div>';
        }

        public function display() {
            $offset = $this->firstDayOffset;
            $newRow = true;

            $calendar = 
                '<div class="table-responsive">
                    <h3 class="calendarHeading">' . date('F Y', strtotime($this->calendarDate)) .  '</h3>

                    <table class="calendar table table-light">
                        <thead>
                            <tr>
                                <th>Sunday</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>

                        <tbody>';

            //Loop days of month and display table rows
            for($d = 1 + $offset; $d <= intval(date('d', strtotime($this->lastOfMonth)) + $offset); $d++) {
                $actualDay = $d - $offset;
                $actualDayPre = ($actualDay < 10 ? '0' . $actualDay : $actualDay);
                $today = date($this->currentYear . '-' . $this->currentMonth . '-' . $actualDayPre);
                
                if($newRow == true) {
                    $calendar .= 
                        '<tr>';

                    $newRow = false;
                }

                if($actualDay == 1) {
                    for($b = 0; $b < $offset; $b++) {
                        $calendar .=
                            '<td></td>';
                    }
                }
                
                $calendar .=
                    '<td ' . ($today < date('Y-m-d') ? 'class="calendarPast"' : '') . '>
                        <div class="calendarInner">
                            <span class="calendarDay' . ($today == date('Y-m-d') ? ' current' : '') . '">' . $actualDay . '</span>' .
                            $this->displayevents($today) . 
                        '</div>
                    </td>';

                if($d == intval(date('d', strtotime($this->lastOfMonth)) + $offset)) {
                    for($l = 0; $l < $this->lastDayOffset; $l++) {
                        $calendar .=
                            '<td></td>';
                    }
                }

                if($d % 7 == 0) {
                    $calendar .= 
                        '</tr>';

                    $newRow = true;
                }
            }

            $calendar .=    
                        '</tbody>
                    </table>' . 
                    $this->controls .
                '</div>';

            echo $calendar;
        }

        public function displaysmall() {
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
                        '</div>
                    </div>';

                unset($nextEvent);
            }

            $calendar .=
                '</div>';

            echo $calendar;
        }

        private function sorteventtimes($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        }

        private function displayevents($date) {
            $events = '';

            if(!empty($this->events[$date])) {
                usort($this->events[$date], [$this, 'sorteventtimes']);

                foreach($this->events[$date] as $event) {
                    $styles = '';

                    if(!empty($event['background_colour'])) {
                        $styles .= 'background-color: ' . $event['background_colour'];
                    }
                    
                    if(!empty($event['text_colour'])) {
                        $styles .= 'color: ' . $event['text_colour'];
                    }

                    $events .=
                        '<div class="calendarEvent" style="' . $styles . '">' .
                            (strlen($event['name']) > $this->nameLength ? rtrim(substr($event['name'], 0, $this->nameLength)) . '...' : $event['name']) .
                        '</div>';
                }
            }

            return $events;
        }

        public function loadevents($array = []) {
            global $mysqli;

            if(is_array($array) && !empty($array)) {
                $this->events = $array;
                return;
            }

            $eventArray = [];
            $i = 0;

            $getEvents = $mysqli->query("SELECT * FROM `posts` WHERE post_type_id = 1 AND state = 2");
            
            if($getEvents->num_rows > 0) {
                while($event = $getEvents->fetch_assoc()) {
                    $date = date('Y-m-d', strtotime($event['date_created']));
                    $time = date('H:i', strtotime($event['date_created']));

                    $eventArray[$date][$i] = [
                        'name' => $event['name'],
                        'link' => $event['url'],
                        'excerpt' => $event['excerpt'],
                        'background_colour' => $event['background'],
                        'text_colour' => $event['text'],
                        'time' => $time
                    ];

                    $i++;
                }
            }

            $this->events = $eventArray;
            return;
        }
    }

?>