<?php 
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderUserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/HighValueRuleDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");
                                                                               
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
  require_once($ConstantsArray['dbServerUrl'] . "SecurityUtil/SecurityUtil.php");
  
                                                            
                                            
                                                              
  $ACTION_WQDFILES = "wqdfiles";
  $ACTION_WQDFILESDATA = "wqdfilesData";
  $ACTION_FOLDERS = "folders";
  $ACTION_FOLDERUSERS = "folderusers";
  $ACTION_USERS = "users";
  $ACTION_LOCATIONS = "locations";
  $ACTION_HIGHVALUERULES = "highvaluerules";
  $ACTION_HIGHVALUERULEREMINDERS = "highvaluerulereminders";
  $ACTION_CONFIGURATIONS = "configurations";
  $ACTION_CHANNELCONFIGURATIONS = "channelconfigurations";
  
  $limit = 300;
  
  $action = $_GET["feed"];
  $locationSeqs = $_GET["locs"];
  $lastSeq = $_GET['lastSeq'];
  $actionArr = explode(",",$action);
  if($actionArr != null && count($actionArr)>0){
      $XML = "<?xml version='1.0' encoding='UTF-8'?>";
      $parentTag = "ELiveDifferentialCall";
      if(in_array($ACTION_WQDFILES,$actionArr) || in_array($ACTION_WQDFILESDATA,$actionArr)){
          $parentTag = "ELiveFullDataCall";
      }
      $XML .= '<'. $parentTag .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';   
      foreach($actionArr as $action){
          if($action == $ACTION_WQDFILES){
              if($lastSeq != ""){
                    $WQDFS = WQDFileDataStore::getInstance();
                    $files = $WQDFS->findByLocationSeqLastSeq($locationSeqs,$lastSeq,$limit);
                    $XML .= "<WQDFiles>";
                    foreach($files as $file){
                        $XML .= getWQDFileXML($file);
                    }
                    $XML .= "</WQDFiles>"; 
              }  
               
          }elseif($action == $ACTION_WQDFILESDATA){
              if($lastSeq != ""){
                    $WQDDDS = WQDDataDataStore::getInstance();
                    $files = $WQDDDS->getWQDDataByLocationSeqsAndLastSeq($locationSeqs,$lastSeq,$limit);
                    $XML .= "<WQDFilesData>";
                    foreach($files as $file){
                        $XML .= getWQDDataXML($file);
                    }
                    $XML .= "</WQDFilesData>"; 
              }
          }elseif($action == $ACTION_FOLDERS){
                $FDS = FolderDataStore::getInstance();
                $folders = $FDS->FindByLocationSeqs($locationSeqs);
                $XML .= "<Folders>";
                foreach($folders as $folder){
                    $XML .= getFolderXML($folder);
                }
                $XML .= "</Folders>";
          }elseif($action == $ACTION_FOLDERUSERS){
                $FUDS = FolderUserDataStore::getInstance();
               
                $folderUsers = $FUDS->FindByLocations($locationSeqs);
                $XML .= "<FolderUsers>";
                foreach($folderUsers as $folderUser){
                    $XML .= getFolderUserXML($folderUser);
                }
                $XML .= "</FolderUsers>";
          }elseif($action == $ACTION_USERS){
                $UDS = UserDataStore::getInstance();
                $users = $UDS->FindUsersByLocSeqs($locationSeqs);
                $XML .= "<Users>";
                foreach($users as $user){
                    $XML .= getUserXML($user);
                }
                $XML .= "</Users>";
          }elseif($action == $ACTION_LOCATIONS){
                $LDS = LocationDataStore::getInstance();
                $locations = $LDS->FindBySeqs($locationSeqs);
                $XML .= "<Locations>";
                foreach($locations as $location){
                    $XML .= getLocationXML($location);
                }
                $XML .= "</Locations>";
          }elseif($action == $ACTION_HIGHVALUERULES){
                $HVRDS = HighValueRuleDataStore::getInstance();
                $rules = $HVRDS->FindByLocationSeqs($locationSeqs);
                $XML .= "<HighValueRules>";
                foreach($rules as $rule){
                    $XML .= getHighValueRuleXML($rule);
                }
                $XML .= "</HighValueRules>";
          }elseif($action == $ACTION_CONFIGURATIONS){
                $CDS = ConfigurationDataStore::getInstance();
                $configs = $CDS->FindAll();  
                $XML .= "<Configurations>";
                foreach($configs as $config){
                    $XML .= getConfigurationXML($config);
                }
                $XML .= "</Configurations>";
          }elseif($action == $ACTION_CHANNELCONFIGURATIONS){
                $CCDS = ChannelConfigurationDataStore::getInstance();
                $configs = $CCDS->FindByLocSeqs($locationSeqs);
                $XML .= "<ChannelConfigurations>";
                foreach($configs as $config){
                    if($config->getChannelNumber()!=""){
                        $XML .= getChannelConfigurationXML($config);
                    }
                }
                $XML .= "</ChannelConfigurations>";
          }
          
      }
      $XML .= "</". $parentTag .">";
      header('Content-Type: text/xml');
      //header("HTTP/1.0 200 OK");
      echo $XML;
      die;
  }
  
  
  
  
  
  function getWQDFileXML($row){
        $XML .= "<wqdfile>";
            $XML .= "<wqdfileseq>". $row["wqdfileseq"] ."</wqdfileseq>";
            $XML .= "<wqdfiledate>". $row["wqdfiledate"] ."</wqdfiledate>";
            $XML .= "<wqdfilename>". $row["wqdfilename"] ."</wqdfilename>";
            $XML .= "<wqdfolderseq>". $row["wqdfolderseq"] ."</wqdfolderseq>";
            $XML .= "<wqdlocationseq>". $row["wqdlocationseq"] ."</wqdlocationseq>";
        $XML .= "</wqdfile>";
        return $XML;
  }
  
  function getWQDDataXML($row){
        $XML .= "<wqdfiledata>";
            $XML .= "<wqdfiledataseq>". $row["wqdfiledataseq"] ."</wqdfiledataseq>";
            $XML .= "<wqdfileseq>". $row["wqdfileseq"] ."</wqdfileseq>";
            $XML .= "<wqdfolderseq>". $row["wqdfolderseq"] ."</wqdfolderseq>";
            $XML .= "<wqdfiledatadated>". $row["wqdfiledatadated"] ."</wqdfiledatadated>";
            $XML .= "<wqdfiledatareportno>". $row["wqdfiledatareportno"] ."</wqdfiledatareportno>";
            $XML .= "<wqdfiledatatotalchannels>". $row["wqdfiledatatotalchannels"] ."</wqdfiledatatotalchannels>";
            $XML .= "<wqdfiledatachecksum>". $row["wqdfiledatachecksum"] ."</wqdfiledatachecksum>";
            
            for($i=1;$i<=30;$i++){
                $val =  $row["ch".$i."value"];
                $status = $row["ch".$i."status"];
                $xsi = getXSI($val);
                $XML .= "<ch".$i."value ". $xsi .">". $val ."</ch".$i."value>";
                $XML .= "<ch".$i."status ". $xsi .">". $status ."</ch".$i."status>";
            }               
        $XML .= "</wqdfiledata>";
        return $XML;
  }
  
  function getFolderXML($folder){ 
      $XML .= "<folder>";
        $XML .= "<seq>". $folder->getSeq() ."</seq>";
        $XML .= "<foldername>". $folder->getFolderName() ."</foldername>";
        $XML .= "<details ". getXSI($folder->getDetails()) .">". $folder->getDetails() ."</details>";
        $XML .= "<locationseq>". $folder->getLocationSeq() ."</locationseq>";
        $XML .= "<lastsynchedon ". getXSI($folder->getLastSynchedOn()) .">". $folder->getLastSynchedOn() ."</lastsynchedon>";
        $XML .= "<lastparsedon ". getXSI($folder->getLastParsedOn()) .">". $folder->getLastParsedOn() ."</lastparsedon>";
        $XML .= "<lastremindedon ". getXSI($folder->getLastRemindedOn()) .">". $folder->getLastRemindedOn() ."</lastremindedon>";
      $XML .= "</folder>";
      return $XML;
  }
  function getFolderUserXML($fu){ 
      $XML .= "<folderuser>";
        $XML .= "<folderseq>". $fu->getFolderSeq()."</folderseq>";
        $XML .= "<userseq>". $fu->getUserSeq() ."</userseq>";
        $XML .= "<permission>". $fu->getPermission() ."</permission>";
      $XML .= "</folderuser>";
      return $XML;
  }
  function getUserXML( $user){ 
      $XML .= "<user>";
        $XML .= "<seq>". $user->getSeq() ."</seq>";
        $XML .= "<username>". $user->getUserName() ."</username>";
        $XML .= "<password>". $user->getPassword() ."</password>";
        $XML .= "<emailid>". $user->getEmailId() ."</emailid>";
        $XML .= "<dateofregistration>". $user->getDateOfRegistration() ."</dateofregistration>";
        $XML .= "<isactive>". $user->getIsActive() ."</isactive   >";
        $XML .= "<fullname ". getXSI($user->getFullName()) .">". $user->getFullName() ."</fullname>";
        $XML .= "<locationseq ". getXSI($user->getLocationSeq()) .">". $user->getLocationSeq() ."</locationseq>";
        $XML .= "<folderseq ". getXSI($user->getFolderSeq()) .">". $user->getFolderSeq() ."</folderseq>";
        $XML .= "<ismanager>". $user->getIsManager() ."</ismanager>";
      $XML .= "</user>";
      return $XML;
  }
  
  function getLocationXML($location){
      $XML .= "<location>";
        $XML .= "<seq>". $location->getSeq() ."</seq>";
        $XML .= "<name>". $location->getLocationName() ."</name>";
        $XML .= "<details ". getXSI($location->getLocationDetails()) .">". $location->getLocationDetails() ."</details>";
        $XML .= "<locationfolder>". $location->getLocationFolder() ."</locationfolder>";
        $XML .= "<isprivate>". $location->getIsPrivate() ."</isprivate>";
      $XML .= "</location>";
      return $XML;
  }
  
  function getHighValueRuleXML($rule){
      $XML .= "<highvaluerule>";
        $XML .= "<seq>". $rule->getSeq() ."</seq>";
        $XML .= "<folderseq>". $rule->getFolderSeq() ."</folderseq>";
        $XML .= "<email ".getXSI($rule->getEmail()).">". $rule->getEmail() ."</email>";
        $XML .= "<mobile ".getXSI($rule->getMobile()).">". $rule->getMobile() ."</mobile>";
        $XML .= "<parameterchannelno>". $rule->getParameter() ."</parameterchannelno>";
        $XML .= "<highvalue>". $rule->getHighValue() ."</highvalue>";
        $XML .= "<frequency ".getXSI($rule->getFrequency()).">". $rule->getFrequency() ."</frequency>";
        $XML .= "<isactive ".getXSI($rule->getIsActive()).">". $rule->getIsActive() ."</isactive>";
        $XML .= "<rulehits ".getXSI($rule->getRuleHits()).">". $rule->getRuleHits() ."</rulehits>";
        $XML .= "<lastrulehitwqdfiledataseq ".getXSI($rule->getLastRuleHitFileDataSeq()).">". $rule->getLastRuleHitFileDataSeq() ."</lastrulehitwqdfiledataseq>";
        $XML .= "<rulestartwqdfiledataseq ".getXSI($rule->getRuleStartFileDataSeq()).">". $rule->getRuleStartFileDataSeq() ."</rulestartwqdfiledataseq>";
      $XML .= "</highvaluerule>";
      return $XML;
  }

  function getChannelConfigurationXML($cc){
      $XML .= "<channelconfiguration>";
        $XML .= "<configseq>". $cc->getSeq() ."</configseq>";
        $XML .= "<folderseq>". $cc->getFolderSeq() ."</folderseq>";
        $XML .= "<channelnumber>". $cc->getChannelNumber() ."</channelnumber>";
        $XML .= "<channelname>". $cc->getChannelName() ."</channelname>";
        $XML .= "<channelstatusflag ".getXSI($cc->getChannelStatusFlag()).">". $cc->getChannelStatusFlag() ."</channelstatusflag>";
        $XML .= "<channelunit>". utf8_encode($cc->getChannelUnit()) ."</channelunit>";
      $XML .= "</channelconfiguration>";
      return $XML;
  }
  
  function getConfigurationXML($cc){
      $XML .= "<configuration>";
        $XML .= "<configkey>". $cc['configkey'] ."</configkey>";
        $XML .= "<configvalue>". $cc['configvalue'] ."</configvalue>";
      $XML .= "</configuration>";
      return $XML;
  }
  
  function getXSI($val){
    $xsi = "";
    if($val == ""){
        $xsi = "xsi:null=\"true\"";
    }
    return $xsi;  
  }
?>