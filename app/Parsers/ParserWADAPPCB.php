<?php

    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
    
    
    class ParserWADAPPCB{
        
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
                echo ("\n Total Rows Found:". ($numLines - 2));
                $lineRow = $lineRow +1;
                for ($i = $lineRow; $i < $numLines; $i++) {
                        $WQDData = new WQDData();    
                        $line = trim($content[$i]);
                        $lineVals = explode(',', $line);
                        $totalChannels = (count($lineVals) -3)/2;
                        
                        $reportNo = substr($lineVals[1],0,3);
                        if($reportNo != "001"){
                            continue;
                        }
                        $dateNumeric = $lineVals[2];
                        $dated = $dateNumeric;//self::DateConvert($dateNumeric);

                        $channels = array(); 
                        $varLocation = 3;
                        for($channelNo=1;$channelNo<=$totalChannels;$channelNo++){  
                            $channelValue = $lineVals[$varLocation];
                            $channelValue = 0 + $channelValue;
                            $channelValue = round($channelValue,2);
                            $channelStatus = $lineVals[$varLocation+1];
                            $varLocation = $varLocation +2;
                            
                            $channelInfo = array();
                            $channelInfo['value'] = $channelValue;
                            $channelInfo['status'] = $channelStatus;
                            $channels[$channelNo] = $channelInfo;
                        }
                        $checkSum = $lineVals[0];
                        $WQDData->setReportNo($reportNo);
                        $WQDData->setFolderSeq($folderSeq);
                        $WQDData->setDataDate($dated);
                        $WQDData->setChannels($channels);
                        $WQDData->setTotalChannels($totalChannels);
                        $WQDData->setChecksum($checkSum);
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
