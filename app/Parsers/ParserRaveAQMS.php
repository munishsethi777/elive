<?php 
    //require_once('IConstants.inc');
	//require_once($ConstantsArray['dbServerUrl'] ."log4php/Logger.php");
	require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");

    class ParserRaveAQMS{
        public static function parse($filePath,$folderSeq){
                $fileName = substr($filePath, strrpos($filePath, '/') + 1);
                if($fileName != "data.csv"){
                	return null;
                }
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
                $lineRow = 0;
                for ($i = $lineRow; $i < $numLines; $i++) {
                        $WQDData = new WQDData();
                        $line = trim($content[$i]);
                        $lineVals = explode(',', $line);
                        $reportNo = $lineVals[2];
                        if($reportNo == "1" || $reportNo == "11"){
                        }else{
                        	continue;
                        }
                        $dateStr = $lineVals[0] . " " . $lineVals[1];
                        $dated = new DateTime($dateStr);
                        $channelDataArr = array_slice($lineVals, 3, count($lineVals), true);
                        $channels = array();
                        $channelNo = 1;
                        foreach ($channelDataArr as $channelValue ){
                        	if(empty($channelValue)){
                        		continue;
                        	}
                        	$channelStatus = 128;
                        	$channelInfo = array();
                        	$channelInfo['value'] = $channelValue;
                        	$channelInfo['status'] = $channelStatus;
                        	$channels[$channelNo] = $channelInfo;
                        	$channelNo++;
                        }
                        $totalChannels = $channelNo - 1;
                        $WQDData->setReportNo($reportNo);
                        $WQDData->setFolderSeq($folderSeq);
                        $WQDData->setDataDate(DateUtils::getSQLDateFromDateObj($dated));
                        $WQDData->setChannels($channels);
                        $WQDData->setTotalChannels($totalChannels);
                        $WQDData->setChecksum(0);
                        $WQDDataArray[$arrayCount]=$WQDData;
                        $arrayCount = $arrayCount +1;
                    }
                    return $WQDDataArray;
        }

        public static function DateConvert($dateStr){
            $YY = substr($dateStr,0,2);
            $MM = substr($dateStr,2,2);
            $DD = substr($dateStr,4,2);
            $HH = substr($dateStr,6,2);
            $MN = substr($dateStr,8,2);
            $SS = substr($dateStr,10,2);
            //11 02 19 13 50 00
            return date('Y-m-d H:i:s',mktime($HH, $MN, $SS, $MM, $DD, $YY));
        }

    }
?>

