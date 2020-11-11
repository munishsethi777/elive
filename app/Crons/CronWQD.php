<?php
    
  ini_set('max_execution_time', 600);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/MailerUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserAppcbHyd.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDOpacityFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserConfig.php");
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."Managers/ReminderMgr.php");
  require_once($ConstantsArray['dbServerUrl'] ."admin/configuration.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserAlternateFiles.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserStackLsi.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserBoomiM2MCSV.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWADAPPCB.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserDuplicateAQMS.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserBhoomiFiles.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserRaveAQMS.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserScaledM2MCSV.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserEnvirotechAQMSMini.php");
  require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserEnvidas.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDAQMSPFileDataStore.php");  
  require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
  //Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');

  Logger::configure ( $ConstantsArray ['dbServerUrl'] . "log4php/log4php.xml" );
  $logger = Logger::getLogger ( "eliveLogger" );
  $rave_aqms_filename = "data.csv";
  $display_txt = "Display.txt";
  //$parserWqd = ParserWQD::parseWQD($ConstantsArray['baseUrl'].'/app/Crons/tag01102015_000108.wqd',0);
 
  //$repositoryPath =  $ConstantsArray['baseUrl'].'/Repository/';
  try{
  $FDS = FolderDataStore::getInstance();
  $folders = $FDS->FindAll();
  echo ("\n <br> Cron started here");
  $fileUtils = FileSystemUtils::getInstance();
  //$backupPath =  $ConstantsArray['dbServerUrl'].'Backup/';
  foreach($folders as $k=>$folder){
       echo " <br>start cron for folder - " . $folder->getIndustryName() . " seq - " . $folder->getSeq() . "<br>";
        $repositoryPath =  "/home/envirote/public_html/Repository/";
	  if(!empty($folder->getParentSeq())){
	  	//echo ("\n\n <br> Skipping the parser as ". $folder->getActualName() ." is Sub Station ");
	  	continue;
	  }	
	  if(!$folder->getIsEnable()){
          echo ("\n\n <br> Skipping the parser as ". $folder->getActualName() ." is disabled ");
          continue;          
      }
      //$FDS->updateLastSynchDate($folder->getSeq());//UPDATE LAST SYNCH DATE
      echo ("\n\n Into Folder ". $folder->getActualName() ."(". $folder->getSeq() .")");
      $filespath = null;
	  $backupPath =  $ConstantsArray['dbServerUrl'].'Backup/';
      if(!empty($folder->getRepositoryFolder())){
      	$repositoryPath .= $folder->getRepositoryFolder() ."/";
      	$backupPath .= $folder->getRepositoryFolder() ."/";
      }
	  
      $filespath =  $repositoryPath .  $folder->getLocationFolder() . "/" . $folder->getActualName();
      $files= null;
      $files= $fileUtils->Read_Directory($filespath);
      if($files == null){
           //ReminderMgr::reminderInvoker($folder);
           echo ("\n <br> No Files found in ". $filespath);
           $lastSynchedOn = $folder->getLastSynchedOn();
           $lastSynchedOn = new DateTime($lastSynchedOn);
           $logger->info("Folder: ".$folder->getSeq() . " lastSynchedOn Date after conversion - " . $lastSynchedOn->format("d-m-Y H:i"));
           $FDS->notifyUploadFailure($folder, $lastSynchedOn);
           $FDS->markAsOffline($folder->getSeq(),$lastSynchedOn);
           continue;
      }
      
      $isNotifyFailure = $folder->getHasNotifyUploadFailure();
      if(!empty($isNotifyFailure)){
      	 $FDS->notifyUploadStarted($folder);
      }
      
      $FDS->updateIsOnline($folder->getSeq(),1);  
      
      //$FDS->updateLastParseDate($folder->getSeq());//UPDATE LAST PARSED ON DATE
      //latest file throw starts here
    $latestFile = $fileUtils->getLatestFileName($filespath);
    $latestFileType = $fileUtils->getFileType($latestFile);  
	if(strtoupper($latestFileType) == "WQD" && ($folder->getSeq() == 44 || $folder->getSeq() == 46)){
		
		echo ("\nSaving latest file");
      	//copy($filespath. "/" . $latestFile ,$filespath. "/latest/latest.wqd");
		
		echo("\nlatest file name : ". $latestFile);
		$content = file($filespath."/".$latestFile);
		$numLines = count($content);
		if($numLines > 3){
			for($i=2;$i<$numLines-1;$i++){
				unset($content[$i]);
			}
		}
		$fileParams = $content[1];
		$fileParamsArray = array("Unit1","Unit2");
		$newParamsArray = array("Unit1-","Unit2-");
		$fileParams = str_replace($fileParamsArray,$newParamsArray,$fileParams);
		$content[1] = $fileParams;
		file_put_contents($filespath. "/latest/latestData.wqd",implode('', $content));
		echo("\n <br> Copied file to the latest folder");
	}
      //latest file throw ends here

      //backup Folder Creation starts
       try{
          $backupFolName =  date('MY');
          $backupFolder =  $backupPath . $folder->getLocationFolder() . "/" . $folder->getActualName() ."/". $backupFolName;
          if(!is_dir($backupFolder)){
                mkdir($backupFolder,0777,true);
          }
       }catch(Exception $e){
         echo("\n <br> Exception occured backfolder creation -".$e->getMessage());
       }
      //backup folder creation ends..

      echo ("\n\n <br><br> Files found in folder ". $filespath ."(".$folder->getSeq().") ->> Parsing now");
      $message = "";
      $txt = "";
      $from = "noreply@elive.com";
      $to = "munishsethi777@gmail.com";
      $subject = "Exception in Elive Parsers";
      foreach($files as $value){
            //backup file in a separate location
              try{
                  $fName = 	FileSystemUtils::getFileName($value);
              	  if($fName == $display_txt){
              	  	 continue;
              	  }
              	  if($fName == $rave_aqms_filename){
				  	$pathInfo = pathinfo($value, PATHINFO_FILENAME);
				  	$date = new DateTime();
				  	$dataStr = $date->format("dmYHis");
				  	$fName = $pathInfo . "_".$dataStr . ".csv";
              	  }
                  $bkfilespath =  $backupFolder . "/". $fName;
                  copy($value ,$bkfilespath);
              }catch(Exception $e){
                 echo("\n <br> Exception occured backingup file -".$e->getMessage());
              }
            //backup file ends here


            echo("\n <br> Now Processing: ". $value);
            $fileNameTotalCount = strlen($value);
            $fileType = substr($value,$fileNameTotalCount-3,3);

            if(strtoupper($fileType)== "CFG"){
                ConfigurationParsing($value,$folder);
                continue;
            }
            if(strtoupper($fileType) == "WQD" || strtoupper($fileType) == "TXT" || strtoupper($fileType) == "WAD"
                || strtoupper($fileType) == "LSI" || strtoupper($fileType) == "CSV" || strtoupper($fileType) == "ATA"){
                echo ("\n <br>". date("Y-m-d  H:i:s") ." Parsing ".$fileType." file ". $value);
                $WQDDataArray = null;
                try{
                    $parser = $folder->getParserType();
                    if(empty($parser)){
                        echo("No Parser Found");
                        continue;
                    }
                    $parserInstance = new $parser();
                    $WQDDataArray =  $parserInstance::parse($value,$folder->getSeq());
					echo("\n<br>FolderSeq:".$folder->getSeq() ." ParserType:". $parser);
					if($WQDDataArray != null){
					    if($folder->getSeq() == "317"){//Shakun sponge troubleshoot
    					    //echo("reached");
    					}
                        if($folder->getStationType() == "stack" || $folder->getStationType() == "effluent"){
                        	$isOpacity = $folder->getIsOpacity();
                        	if(!empty($isOpacity)){
                        		$WOFDS = WQDOpacityFileDataStore::getInstance();
                        		$WOFDS->saveSyncedData($WQDDataArray);
                        	}
                        	else{
                        		$WDSD = WQDStackDataStore::getInstance();
                        		$WDSD->saveSyncedData($WQDDataArray);
                        	}
                            echo ("\n<br>". date("Y-m-d  H:i:s") ." Stack File Saved Successfully");
                            if($folder->getSeq() == "314"){
                                echo("<br>Entering iit latest file zone");
                                $fileiit = file($value);
                                $file_contents = explode(",",$fileiit[count($fileiit) - $i]);
                                $latest_value = $file_contents[6];
                                $latest_time = $file_contents[0];
                                while($latest_value == "" && count($fileiit) > $i){
                                    $i++;
                                    $file_contents = explode(",",$fileiit[count($fileiit) - $i]);
                                    $latest_value = $file_contents[6];
                                    $latest_time = $file_contents[0];
                                }
                                file_put_contents("/home/envirote/public_html/Repository/elive/iitdelhi/latest/latest.csv",strtotime($latest_time) . "," . $latest_value);
                                echo("<br>iit building backup file". $filespath . "/latest/latest.csv");
                            }

                        }else if($folder->getStationType() == "aqmsp"){
                        		$WAQSD = WQDAQMSPFileDataStore::getInstance();
                        		$WAQSD->saveSyncedData($WQDDataArray);
                        }else{
                            $WDD = WQDDataDataStore::getInstance();
                            $WDD->SaveList($WQDDataArray);
                            echo ("\n<br>". date("Y-m-d  H:i:s") ." AQMS File Saved Successfully");
                            //if($status != null){
                                //echo ("\n<br> Status from save call". $status);
                           // }
                        }
                        unlink($value);
                        unset($WQDDataArray);
                        unset($wqdFile);
                    }else{
                        echo("<br> No Data found from parsed file<br>");
                    }
              }catch(Exception $e){
                    echo '<br>Exception caught while Parsing: '.  $e . "\n";
	                $txt = 'Exception caught while Parsing: '.  $e->getMessage(). "\n";
	                $txt .= ' in folder '. $folder->getActualName();      
	                $message .= $txt . "<br>";
	                $logger = Logger::getLogger("myDBLogger");
	                $logger->error($txt);
              }
            }
            unset($value);

      }
      
      try{
        unset($folder);
        unset($files);
        unset($filespath);
      }catch (Exception $e){
        echo 'Exception caught while unset: '.  $e->getMessage(). "\n";
      }
  }
  //end of loop on files found
   if(!empty($message)){
  	  MailerUtils::sendMandrillEmailNotification($message,$subject,$from,$to);
   }
   function uploadFile($data){
        $date = $data->Key; //20-11-2019 01:25:50
        $values = $data->Value;
        $dateObj = DateUtils::StringToDateByGivenFormat("d-m-Y H:i:s", $date);
        $dateStr = $dateObj->format("dmYHis");
        $dataFile = fopen("/home/envirote/public_html/Repository/elive/iitdelhi/latest/latest.csv", "w") or die("Unable to open file!");
        $dataRowArr = array();
        foreach ($values as $value){
           array_push($dataRowArr,$value[0]);
        }
        $dataRow = "";
        if(!empty($dataRowArr)){
            $dataRow = implode(",", $dataRowArr);
        }
        $txt = $dateStr . ",". $dataRow;
        fwrite($dataFile, $txt);
        fclose($dataFile);
    }
    function getFullFolderPath(){
          return $ConstantsArray['applicationURL']. "Repository/" . self::getLocationFolder() . "/" . self::getFolderName();
    }

    function ConfigurationParsing($value,$folder){
        echo("\n\n Starting with Configuration file:  ". $value);
        $configs = ParserConfig::parseConfig($value,$folder->getSeq());

        $CCDS = ChannelConfigurationDataStore::getInstance();
        $CCDS->Delete($folder->getSeq());

        foreach($configs as $config){
            $channelConfig = new ChannelConfiguration();
            $channelConfig->setFolderSeq($folder->getSeq());
            $channelConfig->setChannelNumber(trim($config[0]));
            $channelConfig->setChannelStatusFlag(trim($config[1]));
            $channelConfig->setChannelName(trim($config[2]));
            $channelConfig->setChannelUnit(trim($config[3]));
            $CCDS->Save($channelConfig);

        }
        echo ("\n Unlinking configuration file". $value);
        unlink($value);
    }    
  }catch(Exception $e){
      $logger = Logger::getLogger($ConstantsArray["logger"]);
      $logger->error("Error in CronWQD : - " . $e->getMessage());
	  echo("Error occured - ". $e->getMessage());
  }
  
?>
