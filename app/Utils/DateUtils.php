<?php
    class DateUtils{

        //This method gives slices by 24hrs/1day and left over hrs in last slice
        public static function getDateSlicesForCumulativeFlow($fromDate, $toDate){
            $datesFinalArray = array();
            $incrementalDate = clone $fromDate;
            while($toDate >= $incrementalDate){
                $incrementalDate->add(date_interval_create_from_date_string("1day"));
                if($incrementalDate > $toDate){//if its last date and you need to keep last few hrs also
                    array_push($datesFinalArray,$toDate->format("Y-m-d  H:i:s"));
                }else{
                    array_push($datesFinalArray,$incrementalDate->format("Y-m-d  H:i:s"));
                }

            }
            return $datesFinalArray;
        }
        //Process DateIntervalSlices as per fromTo dates and interval type
        //intervaltype can be 5mins,10mins,30mins,1hrs
        public static function getDateSlicesByInterval($fromDate,$toDate, $interval){
            $datesFinalArray = array();
            $incrementalDate = clone $fromDate;
            while($toDate >= $incrementalDate){
                $incrementalDateStr = $incrementalDate->format("Y-m-d  H:i:s");
                array_push($datesFinalArray,$incrementalDateStr);
                $incrementalDate->add(date_interval_create_from_date_string($interval));
            }
            return $datesFinalArray;
        }

        public static function getDateSlicesByComments($commentsArr, $interval,$chArrayAll){
            //here commentsArr are the valid channelscomments, $chArrayAll is all the channels selcted in ui
            $dateSlicesArr = array();
            if($commentsArr == null){
                return $dateSlicesArr;
            }
            foreach($chArrayAll as $ch){
                $dateSlicesArr[(int)$ch] = array();
            }

            foreach($commentsArr as $comment){
                $chNo = $comment->getChannelNumber();
                $fromDate = new DateTime($comment->getFromDateRange());
                $toDate = new DateTime($comment->getToDateRange());
                $dateSlices = self::getDateSlicesByInterval($fromDate, $toDate, $interval);
                $dateSlicesArr[$chNo] = array_merge($dateSlicesArr[$chNo],$dateSlices);
            }
            return $dateSlicesArr;
        }
        //Works For Stations Reports as per selected PeriodType and FromDate
        public static function getDatesArrayForStationReports($quickReportType, $fromDateStr, $toDateStr){
            $dateArr = null;
            $fromDate = new DateTime($toDateStr);
            $toDate = new DateTime($toDateStr);


            if($quickReportType != "null"){
                $fromDate = new DateTime();
                $toDate = new DateTime();
            }
            //$fromDate->setTimezone($timezone);
            //$toDate ->setTimezone($timezone);

            $fromDate->setTime(0,0,0);
            if($quickReportType == "today"){
                //$toDate = $toDate->setTime(23,59,59);
            }else if($quickReportType == "last7days"){
                $fromDate->add(DateInterval::createFromDateString('-7 days'));
                $fromDate->setTime(0,0,0);
            }else if($quickReportType == "last30days"){
                $fromDate->add(DateInterval::createFromDateString('-1 month'));
                $fromDate = $fromDate->setTime(0,0,0);
            }else if($quickReportType == "last6months"){
                $fromDate->add(DateInterval::createFromDateString('-6 months'));
                $fromDate = $fromDate->setTime(0,0,0);
            }else if($quickReportType == "thisyear"){
                $d = $fromDate->format('d');
                $m = $fromDate->format('m');
                $Y = $fromDate->format('Y');
                $fromDate = $fromDate->setDate($Y , 1 , 1); // set the wanted day for the month
                $fromDate = $fromDate->setTime(0,0,0);
            }else if($quickReportType == "null"){
                $fromDate = new DateTime($fromDateStr);
                $toDate = new DateTime($toDateStr);
            }

            $fromDateAStr = $fromDate->format("Y/m/d  H:i:s");
            $toDateAStr = $toDate->format("Y/m/d  H:i:s");
            $dateArr = array('fromDate'=> $fromDateAStr, 'toDate'=> $toDateAStr);
            return $dateArr;

        }

        public static function getIncrementedDateStr($dated, $timeBase){
            $timezone = new DateTimeZone("Asia/Kolkata");
            $dated = new DateTime($dated);
            $dated->setTimezone($timezone);
            $dated = $dated->add(DateInterval::createFromDateString($timeBase));
            return $dated->format("Y-m-d  H:i:s");;
        }

        public static function getSQLDateFromDateObj($date){
            return $date->format("Y/m/d  H:i:s");
        }

        public static function getDateFromStrDDMMYYYY($date){
            new DateTime($date);
        }
        public static function isDateInBetween($fromDateStr, $toDateStr, $currDateStr){
            $fromDate = new DateTime($fromDateStr);
            $toDate = new DateTime($toDateStr);
            $currDate = new DateTime($currDateStr);
            if($currDate >= $fromDate and $currDate <=  $toDate){
                return true;
            }else{
                return false;
            }
        }


        public static function getDateSlicesForData($dataArr,$isAverage,$timeBase){
            $datesFinalArray = array();
            $datesTSArray = array_keys($dataArr);
            for($i=0;$i<count($datesTSArray);$i++){
                $date = date("Y-m-d H:i:s", $datesTSArray[$i]);
                array_push($datesFinalArray,$date);
            }
            return $datesFinalArray;
        }
        public static function getTimeDiffTillNow($date){
            $diff = $date->diff(new DateTime());
            $years = $diff->y;
            $months = $diff->m;
            $days = $diff->d;
            $hours = $diff->h;
            $minuts = $diff->i;
            $str= null;
            if(!empty($years)){
                if($years > 1){
                    $years .= " Years ";
                }else{
                    $years .= " Year ";
                }
                $str = $years;    
            }
            else if(!empty($months)){
                if($months > 1){
                    $months .= " Months ";
                }else{
                    $months .= " Month ";
                }
                $str .= $months;
            }
            else if(!empty($days)){
                if($days > 1){
                    $days .= " Days ";
                }else{
                    $days .= " Day ";
                }
                $str .= $days; 
            }
            else if(!empty($hours)){
                if($hours > 1){
                    $hours .= " Hours ";
                }else{
                    $hours .= " Hour ";
                }
                $str .=  $hours;
            }
            else if(!empty($minuts)){
                if($minuts > 1){
                    $minuts .= " Minutes ";
                }else{
                    $minuts .= " Minute ";
                }
                $str .= $minuts;
            }
            else {
                $str = "Few Seconds";    
            }
            return $str;
        }
        public static function getDaysDiffTillNow($date){
        	$diff = $date->diff(new DateTime());
        	$days = $diff->format('%a');        	
            if(!empty($days)){
                if($days > 1){
                    $days .= " Days ";
                }else{
                    $days .= " Day ";
                }
                $str = $days; 
            }
        	return $str;
        }
        public static function getHourlySegment($from,$to){
            $t1 = strtotime($from);
            $t2 = strtotime($to);
            $timeslots[] = date('Y-m-d H:i:s', $t1);
            while ($t1 < $t2) {
              $t1 = strtotime('+1 hour', $t1);
              $timeslots[] = date('Y-m-d H:i:s', $t1);
            }
            return $timeslots;
        }
        
        public static function StringToDateByGivenFormat($format,$dateStr){
        	return DateTime::createFromFormat($format, $dateStr);
        }
    }
?>
