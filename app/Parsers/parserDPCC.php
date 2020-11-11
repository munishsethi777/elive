<?php
require_once("../IConstants.php");
                                                                         
require_once(IConstants::$ServerURL ."BusinessObjects/DPCC.php");
require_once(IConstants::$ServerURL ."DataStoreMgr/DPCCDataStore.php");
require_once("G:\\Webdocs\\EnvirontechLive\\httpdocs\\DataStoreMgr\\mainDB.php");

 $dd = new Database();
 
class ParserDPCC{
       
       public function dpccDataReportParser(){
            
           
            $COLS = new ArrayObject();
            $COLS[0] = "CO";
            $COLS[1] = "O3";
            $COLS[2] = "NO";
            $COLS[3] = "NO2";
            $COLS[4] = "NOx";
            $COLS[5] = "NH3";
            $COLS[6] = "SO2";
            $COLS[7] = "BEN";
            $COLS[8] = "TOL";
            $COLS[9] = "PXY";
            
            $content = file(IConstants::$ServerURL. 'files/datareport.txt');
            $max_line_length = 10000;
            $numLines = count($content);
            $array = new ArrayObject();
            
            for ($i = 2; $i < $numLines; $i++) {
                $line = trim($content[$i]);
                $lineVals = explode(' ', $line);
                if($lineVals[1] == "11:55"){
                    echo "hit";
                }
                $dated =  null;
                if($lineVals[0]!= null && $lineVals[1]!= null){
                    $datedString = $lineVals[0] ." ". $lineVals[1];
                    $dt = DateTime::createFromFormat( 'd/m/y H:i' ,$datedString);
                    if($dt != null){
                        $dated = date('Y-m-d H:i:s',$dt->getTimestamp());
                    }
                }
                
                $col1 = $lineVals[4]; 
                $col2 = $lineVals[6];
                $col3 = $lineVals[9];
                $col4 = $lineVals[11];
                $col5 = $lineVals[13];
                $col6 = $lineVals[16];
                $col7 = $lineVals[18];
                $col8 = $lineVals[21];
                $col9 = $lineVals[24];
                $col10 = $lineVals[27];
                
                $dpcc = new DPCC();
                $dpcc->setDated($dated);
                $dpcc->setCO($col1);
                $dpcc->setO3($col2);
                $dpcc->setNO($col3);
                $dpcc->setNO2($col4);
                $dpcc->setNOX($col5);
                $dpcc->setNH3($col6);
                $dpcc->setSO2($col7);
                $dpcc->setBEN($col8);
                $dpcc->setTOL($col9);
                $dpcc->setPXY($col10);
                $array[$i] = $dpcc;
                $dpccDS = DPCCDataStore::getInstance();
                $res = $dpccDS->SaveData($dpcc);
                if($res!= true && $res->getMessage()!= null){
                    echo " ".$res->getMessage()."</br>";   
                }
            }
       }
       
       public function dpccMetaDataReportParser(){
            $COLS = new ArrayObject();
            $COLS[0] = "PM25";
            $COLS[1] = "PM10";
            $COLS[2] = "AT";
            $COLS[3] = "RH";
            $COLS[4] = "WS";
            $COLS[5] = "WD";
            $COLS[6] = "VWS";
            $COLS[7] = "BP";
            $COLS[8] = "SR";
            $content = file(IConstants::$ServerURL. 'files/metadatareport.txt');  
            $max_line_length = 10000;
            $numLines = count($content);
            for ($i = 2; $i < $numLines; $i++) {
                $line = trim($content[$i]);
                $lineVals = explode(' ', $line);
                
                $dated =  null;
                if($lineVals[0]!= null && $lineVals[1]!= null){
                    $datedString = $lineVals[0] ." ". $lineVals[1];
                    $dt = DateTime::createFromFormat( 'd/m/Y H:i' ,$datedString);
                    if($dt != null){
                        $dated = date('Y-m-d H:i:s',$dt->getTimestamp());
                    }
                }
                $col1 = $lineVals[7]; 
                $col2 = $lineVals[10];
                $col3 = $lineVals[13];
                $col4 = $lineVals[17];
                $col5 = $lineVals[20];
                $col6 = $lineVals[24];
                $col7 = $lineVals[26];
                $col8 = $lineVals[29];
                $col9 = $lineVals[33];
                $dpcc = new DPCC();
                $dpcc->setDated($dated);
                $dpcc->setPM25($col1);
                $dpcc->setPM10($col2);
                $dpcc->setAT($col3);
                $dpcc->setRH($col4);
                $dpcc->setWS($col5);
                $dpcc->setWD($col6);
                $dpcc->setVWS($col7);
                $dpcc->setBP($col8);
                $dpcc->setSR($col9);
                $array[$i] = $dpcc;
                $dpccDS = DPCCDataStore::getInstance();
                $res = $dpccDS->SaveMetaData($dpcc);
                if($res!= true && $res->getMessage()!= null){
                    echo " ".$res->getMessage()."</br>";   
                }
            }
       }//function dpccMetaDataReportParser ends
       
   }//class ends here 
   $cls = new ParserDPCC();
   $cls->dpccDataReportParser();
   $cls->dpccMetaDataReportParser();
?>
