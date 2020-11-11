<?php
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
    
    class ParserConfig{
        public static function parseConfig($filePath,$folderSeq){
                //$content = file("../files/config.cfg");
                $content = file($filePath);
                $configChannelRows = explode(';',$content[0]);
                $configChannelRowsCount = count($configChannelRows);
                
                $channelConfigArr = array();
                
                for($i=0;$i<$configChannelRowsCount;$i++){
                    $configChannel = $configChannel = explode(',',$configChannelRows[$i]);
                    if($i == 0){
                        $row = substr($configChannelRows[$i],3);
                        $configChannel = explode(',',$row);
                    } 
                    if(count($configChannel) == 4){
                    	echo "Folder : ".$folderSeq;
                        if($folderSeq == "20"){//to make channel numbers in order
                     
                            $configChannel[0] = $i+1;    
                        }
                        $channelConfigArr[$configChannel[0]] = $configChannel;
                    }
                }
                
                
                    return $channelConfigArr;

        }
    }
?>