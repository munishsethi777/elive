<?php 
   //require_once('IConstants.inc');
//  require_once($ConstantsArray['dbServerUrl'] ."log4php/Logger.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");

    //gagal stacks dates are dd.mm.yyyy
    //core functionality supports basic datetime api with mm.dd.yyyy
    class ParserStackLsi{

        public static function parse($filePath,$folderSeq){
                $gagalFolderSeq = array(33,40,35);
                $nabhaFolderSeqs = array(38,39);
                //$content = file("D:/dat.lsi");
                //$folderSeq = 33;
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
                        $line = rtrim($line, ',');
                        $lineVals = explode(',', $line);
                        $totalChannels = (count($lineVals) -2);
                        $totalChannels = $totalChannels / 2;

                        $reportNo = $lineVals[0];
                        if($reportNo != "1"){
                            continue;
                        }
                        $dateStr = $lineVals[1];
						$dateStr = preg_replace('~\x{00a0}~u', ' ', $dateStr);
						$dateStr = trim(html_entity_decode($dateStr));
                        if(in_array($folderSeq,$gagalFolderSeq)){
                            $dateStr = ParserStackLsi::convertDate($dateStr);
                        }
						//$dateStr = ParserStackLsi::convertDate($dateStr);
                        $dated = new DateTime($dateStr);

                        $channels = array();
                        $varLocation = 2;
                        for($channelNo=1;$channelNo<=$totalChannels;$channelNo++){
                            $channelValue = $lineVals[$varLocation];
                            $channelStatus = $lineVals[$varLocation + 1];
                            if($channelStatus == "1" || $channelStatus == "Ok"){
                                $channelStatus = "128";
                            }							
                            if($channelStatus == "Abnormal"){                                
                                $channelStatus = "0";
                            }
                            if(in_array($folderSeq,$nabhaFolderSeqs) && $channelNo == 4){
                            	 $channelValue = $channelValue / 10;
                            } 
                            $channelInfo = array();
                            $channelInfo['value'] = round($channelValue, 2);
                            $channelInfo['status'] = $channelStatus;
                            $channels[$channelNo] = $channelInfo;
                            $varLocation++;
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

        private static  function convertDate($actualDate) {
                $dateTimeSplits = explode(" ",$actualDate);
                $date = $dateTimeSplits[0];

               // EN-Date to GE-Date
               if (strstr($date, "-") || strstr($date, "/"))   {
                       $date = preg_split("/[\/]|[-]+/", $date);
                       $date = $date[1]."/".$date[0]."/".$date[2];
                       return $date . " " .$dateTimeSplits[1];
               }
               // GE-Date to EN-Date
               else if (strstr($date, ".")) {
                       $date = preg_split("[.]", $date);
                       $date = $date[1]."-".$date[0]."-".$date[2];
                       return $date . " " .$dateTimeSplits[1];
               }
               return false;
        }

    }
?>
