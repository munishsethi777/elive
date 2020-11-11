<?php
  require_once('IConstants.inc');  
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/SLM.php");
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/SLMDataStore.php");      
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/MainDB.php");      
  require_once($ConstantsArray['dbServerUrl'] ."log4php/Logger.php"); 

  
  class ParserSLM{
      
      public static function parseSLMFromFile($myFile){
          $fh = fopen($myFile, 'r');
          $theData = fread($fh,  filesize($myFile));
          ParserSLM::parseSLM($theData); 
      }
      
      public static function parseSLMFromString($theData,$loggerDB){
          $loggerDB->info("Now into parsing method");  
            $COLS = new ArrayObject();
            $COLS[0] = "DATE";
            $COLS[1] = "TIME";
            $COLS[2] = "LEQ";
            $COLS[3] = "MIN";
            $COLS[4] = "MAX";
            $COLS[5] = "L10";
            $COLS[6] = "L50";
            $COLS[7] = "L90";
            $COLS[8] = "SEL";
            $COLS[9] = "CRC";
        
            //$myFile = "../files/data.slm";
            
            
            $lineVals = explode(',', $theData);
            $count = count($lineVals) - 1;
            $totRows = $count/11;
            $arrayCount = 0;
            $SLMArray = new ArrayObject(); 
            
            //loop over each row of information in the SLM
            $varIndex = 0;
            for($a = 0;$a<=$totRows;$a++){
                
                $dated =  null;
                    if($lineVals[$varIndex]!= null && $lineVals[$varIndex+1]!= null){
                        $datedStr = $lineVals[$varIndex];
                        $timed = $lineVals[++$varIndex];
                        $crc = "";
                        if($a != 0){
                            $crc = substr($datedStr,0,1);
                            $datedStr = substr($datedStr,1,8);
                            
                        }
                        $dateWithoutSlashes = str_replace('/', '-', $datedStr); // dd-mm-yyyy
                        $date_parts = array_reverse(explode("-", $dateWithoutSlashes));
                        $dateReFormated = implode('-', $date_parts);
                        $timestamp = strtotime($dateReFormated . " " . $timed);
                        $dated = date('Y-m-d H:i:s', $timestamp);
                    
                        $SLM = new SLM();
                        $SLM->setDated($dated);
                        $SLM->setLEQ($lineVals[++$varIndex]);
                        $SLM->setMIN($lineVals[++$varIndex]);
                        $SLM->setMAX($lineVals[++$varIndex]);
                        $SLM->setL10($lineVals[++$varIndex]);
                        $SLM->setL50($lineVals[++$varIndex]);
                        $SLM->setL90($lineVals[++$varIndex]);
                        $SLM->setSEL($lineVals[++$varIndex]);
                        $SLM->setCRC($crc);
                    
                        $SLMDS = SLMDataStore::getInstance();
                        $res = $SLMDS->SaveSML($SLM);
                        $varIndex = $varIndex+3; //padding variable and another increased
                    }
                      
                 }//for loop
                 $loggerDB->info("Parser came out of loop now");
            
        }//end parser method
        
  }//end class
       
    
?>
