<?php
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");

class ParserEnvidas{
    public static function parse($filePath,$folderSeq){
        echo("ENVIDAS");
        $content = file($filePath);
        $lineRow = 0;
        $numLines = count($content);
        for($i=0;$i<$numLines;$i++){
            if(trim($content[$i]) == ""){
            }else{
                $lineRow = $i;
                $i = $numLines;
            }
        }
        $arrayCount = 0;
        $WQDDataArray = new ArrayObject();
       
        $logger = Logger::getLogger ( "eliveLogger" );
        $logger->info($numLines . " rows found for folder seq - " . $folderSeq . " and filePath - ".$filePath);
        for ($i = $lineRow; $i < $numLines; $i++) {
            try{
            $WQDData = new WQDData();
            $line = trim($content[$i]);
            $lineVals = explode(',', $line);
            $totalChannels = (count($lineVals) -2);
            
            
            $dateStr = $lineVals[1];
            $dated = self::convertDate($dateStr);
            if(!$dated){
                $logger->info("Invalid date - $dateStr found in ParserEnvidas for folderSeq - ". $folderSeq);
                continue;
            }
            //if (strpos($dated, '1999') !== false) {
                //$logger->info("Total Rows Found - " . $numLines . " for file - " . $filePath);
            	//$logger->info($dated . " found for folder seq - " . $folderSeq . " on line no - ".$lineRow);
            	//continue;
            //}
            
            
            
            
            $channels = array();
            $varLocation = 2;
            $noOfChannels = 1;
            for($channelNo=1;$channelNo<=$totalChannels;$channelNo++){
                $channelValue = $lineVals[$varLocation++];
                if($channelValue < 0){
                    break;
                }
                $channelInfo = array();
                $channelInfo['value'] = $channelValue;
                $status = $lineVals[$varLocation];
                $channelInfo['status'] = $status;
                $channels[$channelNo] = $channelInfo;
                $varLocation++;
                $noOfChannels++;
            }
            $WQDData->setReportNo(1);
            $WQDData->setFolderSeq($folderSeq);
            $WQDData->setDataDate($dated);
            $WQDData->setChannels($channels);
            $WQDData->setTotalChannels($noOfChannels);
            $WQDData->setChecksum(0);
            $WQDDataArray[$arrayCount]=$WQDData;
            $arrayCount = $arrayCount +1;
            }catch (Exception $e){
                $logger->error("Error during parser envidas - " . $e->getMessage() );
            }
        }
        return $WQDDataArray;
    }
    
    public static function convertDate($dateStr){
       $date =  DateUtils::StringToDateByGivenFormat("m/d/Y h:i:s a", $dateStr);
       if($date){
           return $date->format("Y-m-d H:i:s");
       }
       return $date;
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
