<?php
    //require_once('IConstants.inc');      
//  require_once($ConstantsArray['dbServerUrl'] ."log4php/Logger.php"); 
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
    
    
    class ParserAlternateFiles{
    
        public static function parse($filePath,$folderSeq){
                //$content = file("D:/alt.txt");
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
                
                for ($i = $lineRow; $i < $numLines; $i++) {
                        $WQDData = new WQDData();    
                        $line = trim($content[$i]);
                        $lineVals = explode(',', $line);
                        $totalChannels = (count($lineVals) -3)/3;
                        
                        $reportNo = $lineVals[1];
                        if($reportNo != "1"){
                            continue;
                        }
                        $dateStr = $lineVals[2];
                        $dated = new DateTime($dateStr);
                        $channels = array(); 
                        $varLocation = 4;
                        for($channelNo=1;$channelNo<=$totalChannels;$channelNo++){
                            $channelValue = $lineVals[$varLocation++];
                            $channelStatus = $lineVals[$varLocation++];
                            
                            $channelInfo = array();
                            $channelInfo['value'] = $channelValue;
                            $channelInfo['status'] = $channelStatus;
                            $channels[$channelNo] = $channelInfo;
                            $varLocation++;
                                
                        }
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
