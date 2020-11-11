<?php
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");

    class ParserBhoomiFiles{
        public static function parse($filePath,$folderSeq){
                $content = file($filePath);
                $numLines = count($content);
                $lineRow = 0;
                for($i=0;$i<$numLines;$i++){
                    if(trim($content[$i]) == ""){

                    }else{
                        $lineRow = $i;
                        $i = $numLines;;
                    }
                }
                $arrayCount = 0;
                $WQDDataArray = new ArrayObject();
                $numLines = count($content);
                $logger = Logger::getLogger ( "eliveLogger" );
                for ($i = $lineRow; $i < $numLines; $i++) {
                        $WQDData = new WQDData();
                        $line = trim($content[$i]);
                        $lineVals = explode(',', $line);
                        $totalChannels = (count($lineVals) -2);

                        $dateStr = $lineVals[0];
                        $timeStr = $lineVals[1];
                        $dated = self::DateConvert($dateStr . $timeStr);
                        if (strpos($dated, '1999') !== false) {
                            $logger->info("Total Rows Found - " . $numLines . " for file - " . $filePath);
                        	$logger->info($dated . " found for folder seq - " . $folderSeq . " on line no - ".$lineRow);
                        	continue;
                        }
                        $channels = array();
                        $varLocation = 2;
                        for($channelNo=1;$channelNo<=$totalChannels;$channelNo++){
                            $channelValue = $lineVals[$varLocation++];
                            $channelInfo = array();
                            $channelInfo['value'] = $channelValue;
                            $channelInfo['status'] = 128;
                            $channels[$channelNo] = $channelInfo;
                        }
                        $WQDData->setReportNo(1);
                        $WQDData->setFolderSeq($folderSeq);
                        //$WQDData->setDataDate(DateUtils::getSQLDateFromDateObj($dated));
                        $WQDData->setDataDate($dated);
                        $WQDData->setChannels($channels);
                        $WQDData->setTotalChannels($totalChannels);
                        $WQDData->setChecksum(0);
                        $WQDDataArray[$arrayCount]=$WQDData;
                        $arrayCount = $arrayCount +1;
                    }

                    return $WQDDataArray;

        }

        public static function DateConvert($dateStr){
            $YY = substr($dateStr,0,4);
            $MM = substr($dateStr,4,2);
            $DD = substr($dateStr,6,2);
            $HH = substr($dateStr,8,2);
            $MN = substr($dateStr,10,2);
            $SS = substr($dateStr,12,2);
            //2015 08 29 14 37
            return date('Y-m-d H:i:s',mktime($HH, $MN, $SS, $MM, $DD, $YY));
        }

    }
?>
