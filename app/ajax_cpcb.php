<?php
      require_once('IConstants.inc');
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CPCBMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CameraPTZActionMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/StationReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/MultiStationReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/EffluentCumulativeFlowReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/WindRoseReportMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CommentsMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/ExemptionMgr.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/ExportUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/PrescribedLimitsUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelGroupDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDOpacityFileDataStore.php");  
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDAQMSPFileDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/CommentsDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ExemptionDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/UserDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/M2MSynchronizerDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Managers/CalibrationMgr.php");
      $REQUEST_ALL_CALIBRATION_LOCATIONS = "requestAllCalibrationLocationsAndStationsJSON";
      $REQUEST_DASHBOARD_STATION_REPORT = "requestDashboardStationReport";
      $REQUEST_ALL_LOCATIONS = "requestAllLocationsAndStationsJSON";
      $REQUEST_STATIONS_BY_LOCATION = "requestStationsByLocationJSON";
      $REQUEST_CHANNELS_BY_FOLDER = "requestChannelsByFolderJSON";
      $REQUEST_STATION_REPORT = "requestStationReport";
      $REQUEST_STATION_REPORT_ = "requestStationReport_";
      
      $REQUEST_STATION_REPORT_CALIBRATION = "requestStationReportCalibration";
      $REQUEST_STATION_CHART_JSON = "requestStationChartJSON";
      $REQUEST_WIND_ROSE_JSON = "requestWindRoseReport";
      $REQUEST_MAP_LOCATIONS_JSON = "requestMapLocationsJSON";
      $REQUEST_STATIONS_RECENT = "requestRecent";
      $SAVE_COMMENTS_MASTER = "saveCommentsMaster";
      $SAVE_COMMENTS_DETAILS = "saveCommentsDetails";
      $REQUEST_COMMENTS_MASTER = "requestCommentsMaster";
      $DELETE_COMMENTS_MASTER = "deleteCommentsMaster";
      $REQUEST_COMMENTS_DETAILS = "requestCommentsDetail";
      $DELETE_COMMENTS_DETAILS = "deleteCommentsDetails";
      $SAVE_EXEMPTION ="saveExemption";
      $REQUEST_EXEMPTIONS = "requestExemptions";
      $DELETE_EXEMPTION = "deleteExemption";
      $APPROVE_EXEMPTION = "approveExemption";
      $REQUEST_EXEMPTION_COMMENTS ="requestExemptionComments";
      $SAVE_EXEMPTION_COMMENT = "saveExemptionComment";
      //New API from industry interface with common request to validate/exempt/record
      $SAVE_VALIDATION_OR_EXEMPTION = "saveValidationExemptionRequest";
      $GENERATE_STATUS_REPORT = "exportStatusReport";
        
      //Security new code  
      session_start();
      if(!$_SESSION["userlogged"]){
      	echo "Invalid Execution";
      	return;
      	die;
      }
      //Security new code ends
      
      $method = $_GET['method'];
      //$locSeqsArr = CPCBMgr::getCPCBLocSeqsArray();
      if($method == "saveCameraPTZ"){
      		$cameraPTZMgr = CameraPTZActionMgr::getInstance();
      		$cameraPTZMgr->saveFromRequest();
      }
      $json = null;
      
      if($method == $GENERATE_STATUS_REPORT){
      	 	$stationRepMgr = MultiStationReportMgr::getInstance();
      	 	$reportType = $_GET["reportType"];
      	 	$loggedInManagerSeq = $_GET["userSeq"];
	      	$rep = $stationRepMgr->exportStatusReport($reportType,$loggedInManagerSeq);
	      	return null;
	  }
      if($method == $REQUEST_ALL_CALIBRATION_LOCATIONS){
	      	session_start();
	      	$userLogged = new User();
	      	$userLogged = $_SESSION["userlogged"];
	      	$userSeq = $userLogged->getSeq();
	      	$LDS = LocationDataStore::getInstance();
	       	$folderType = $_GET['folderType'];
	      	$locations = $LDS->FindCalibrationLocationArrByUser($userSeq);
	      	$locationsSeqsStr = implode(",",array_keys($locations));
	      	$locArr = array();
	      	$locArr['0'] = "All";
	      	$channelArr = array();
	      	$locationChCountArr = array();
	      	$channelGroupsDataStore = ChannelGroupDataStore::getInstance();
	      	foreach($locations as $location){
	      		$locArr[$location->getSeq()] = $location->getLocationDetails();
	      		$count = $channelGroupsDataStore->getCountByLocSeqs($location->getSeq(),strtolower($folderType));
	      		$locationChCountArr[$location->getSeq()] = $count;
	      
	      	}
	      
	      	$FDS = FolderDataStore::getInstance();
	      	$folders = $FDS->FindByLocationSeqs($locationsSeqsStr);
	      	$locArr1 = array();
	      	$locArr1['0'] = "All";
	      	$folArr = array();
	      	$locFolArr = array();
	      	$locationChCounarr = array();
	      	foreach($folders as $folder){
	      		if(empty($folder->getIsCalliberationEnabled())){
	      			continue;
	      		}
	      		$count = $channelGroupsDataStore->getCountByFolderSeqs($folder->getSeq());
	      		$stack = null;
	      		if(!empty($count)){
	      			$stack = "($count Stacks)";
	      		}
	      		if($folder->getStationType() == strtolower($folderType) || $folderType == "all"){
	      			$locName = $locArr[$folder->getLocationSeq()];
	      			$locChCount = $locationChCountArr[$folder->getLocationSeq()];
	      			$locStack = null;
	      			if(!empty($locChCount)){
	      				$locStack = "($locChCount Stacks)";
	      			}
	      			$locArr1[$folder->getLocationSeq()] = $locName ." " . $locStack;
	      			if($folder->getIsVisible() == 1){
	      				$status = getStationStatus($folder);
	      				$loc = $locations[$folder->getLocationSeq()];
	      				$fol  = array("folderName"=>$folder->getStationName(). " ".$status . " $stack " ,"folderLocation"=>$folder->getIndustryName());
	      				$folArr[$folder->getSeq()] = $fol;
	      			}
	      
	      		}
	      	}
	      	$json = new ArrayObject();
	      	$json['locations'] = $locArr1;
	      	$json['folders'] = $folArr;
	      	echo json_encode($json);
      }
      if($method == $REQUEST_STATION_REPORT_CALIBRATION){
        $stationRepMgr = MultiStationReportMgr::getInstance();
        $rep = $stationRepMgr->getDataForCalibration();
        echo json_encode($rep);
      }
      
       if($method == "exportCalibrationDetail"){
      	$stationRepMgr = MultiStationReportMgr::getInstance();
      	$stationRepMgr->exportCalibrationDetail();
      	return;
      }
      
//       if($method == $REQUEST_ALL_LOCATIONS){
//             session_start();
//             $userLogged = new User();
//             $userLogged = $_SESSION["userlogged"];
//             $userSeq = $userLogged->getSeq();
//             $userLocationSeq = $userLogged->getLocationSeq();
//             $LDS = LocationDataStore::getInstance();
//             $locationsArr = $LDS->FindLocationsByUser($userSeq);
// 			if($userLocationSeq){
//                 array_push($locationsArr,$userLocationSeq);
//             }
//             $locations = null;
//             $locationsSeqsStr = implode(",",$locationsArr);
//             $folderType = $_GET['folderType'];
//             $locations = $LDS->FindBySeqs($locationsSeqsStr);
//             $locArr = array();
//             $locArr['0'] = "All";
//             $channelArr = array();
//             $locationChCountArr = array();
//             $channelGroupsDataStore = ChannelGroupDataStore::getInstance();
//             foreach($locations as $location){
//                 $locArr[$location->getSeq()] = $location->getLocationDetails();
//                 $count = $channelGroupsDataStore->getCountByLocSeqs($location->getSeq(),strtolower($folderType));
//                 $locationChCountArr[$location->getSeq()] = $count;
                
//             }
            
//             $FDS = FolderDataStore::getInstance();
//             $folders = $FDS->FindByLocationSeqs($locationsSeqsStr);
//             $locArr1 = array();
//             $locArr1['0'] = "All";
//             $folArr = array();
//             $locFolArr = array();
//             $locationChCounarr = array();
//             foreach($folders as $folder){
//             	$count = $channelGroupsDataStore->getCountByFolderSeqs($folder->getSeq());
//             	$stack = null;
//             	if(!empty($count)){
//             		$stack = "($count Stacks)";
//             	}
//                 if($folder->getStationType() == strtolower($folderType) || $folderType == "all"){
//                         $locName = $locArr[$folder->getLocationSeq()];
//                         $locChCount = $locationChCountArr[$folder->getLocationSeq()];
//                         $locStack = null;
//                         if(!empty($locChCount)){
//                         	$locStack = "($locChCount Stacks)";
//                         }
//                         $locArr1[$folder->getLocationSeq()] = $locName ." " . $locStack;
//                         if($folder->getIsVisible() == 1){
//                             $status = getStationStatus($folder);
//                             $loc = $locations[$folder->getLocationSeq()];                            
//                             $fol  = array("folderName"=>$folder->getStationName(). " ".$status . " $stack " ,"folderLocation"=>$folder->getIndustryName());
//                             $folArr[$folder->getSeq()] = $fol;    
//                         }
                    
//                 }
//             }
//             $json = new ArrayObject();
//             $json['locations'] = $locArr1;
//             $json['folders'] = $folArr;
//             echo json_encode($json);
//       }
      
       if($method == $REQUEST_ALL_LOCATIONS){
            session_start();
            $userLogged = new User();
            $isShowHistory = false;
            $historicalLocSeq = 0;
            $historicalfolderSeq = 0;
            if(isset($_REQUEST["ishistory"])){
                $isShowHistory = !empty($_REQUEST["ishistory"]);
                $historicalLocSeq = $_REQUEST["locSeq"];
                $historicalfolderSeq = $_REQUEST["folderseq"];
            }
            $userLogged = $_SESSION["userlogged"];
            $userSeq = $userLogged->getSeq();
            $userLocationSeq = $userLogged->getLocationSeq();
            $LDS = LocationDataStore::getInstance();
            $locArr1 = array();
            if($isShowHistory){
                $locationsArr = array($historicalLocSeq);
            }else{
                $locationsArr = $LDS->FindLocationsByUser($userSeq);
                if($userLocationSeq){
                    array_push($locationsArr,$userLocationSeq);
                }
                $locArr1['0'] = "All";
            }
            $locations = null;
            $locationsSeqsStr = implode(",",$locationsArr);
            $folderType = $_GET['folderType'];
            $locations = $LDS->FindBySeqs($locationsSeqsStr);
            $locArr = array();
            $locArr['0'] = "All";
            $channelArr = array();
            $locationChCountArr = array();
            $channelGroupsDataStore = ChannelGroupDataStore::getInstance();
            foreach($locations as $location){
                $locArr[$location->getSeq()] = $location->getLocationDetails();
                $count = $channelGroupsDataStore->getCountByLocSeqs($location->getSeq(),strtolower($folderType));
                $locationChCountArr[$location->getSeq()] = $count;
                
            }
            $FDS = FolderDataStore::getInstance();
            $folders = $FDS->FindByLocationSeqs($locationsSeqsStr);
            
            $folArr = array();
            $locFolArr = array();
            $locationChCounarr = array();
            foreach($folders as $folder){
                if($isShowHistory && $historicalfolderSeq != $folder->getSeq()){
                    continue;
                }
                if($folder->getSeq() == 286 || $folder->getSeq() == 287){
            	    continue;
            	}
            	$count = $channelGroupsDataStore->getCountByFolderSeqs($folder->getSeq());
            	$stack = null;
            	if(!empty($count)){
            		$stack = "($count Stacks)";
            	}
                if($folder->getStationType() == strtolower($folderType) || $folderType == "all"){
                        $locName = $locArr[$folder->getLocationSeq()];
                        $locChCount = $locationChCountArr[$folder->getLocationSeq()];
                        $locStack = null;
                        if(!empty($locChCount)){
                        	$locStack = "($locChCount Stacks)";
                        }
                        $locArr1[$folder->getLocationSeq()] = $locName ." " . $locStack;
                        if($folder->getIsVisible() == 1 || $isShowHistory){
                            $status = getStationStatus($folder);
                            $loc = $locations[$folder->getLocationSeq()];                            
                            $fol  = array("folderName"=>$folder->getStationName(). " ".$status . " $stack " ,"folderLocation"=>$folder->getIndustryName());
                            $folArr[$folder->getSeq()] = $fol;    
                        }
                    
                }
            }
            $json = new ArrayObject();
            $json['locations'] = $locArr1;
            $json['folders'] = $folArr;
            echo json_encode($json);
      }
      
      if($method == $REQUEST_STATIONS_BY_LOCATION){
            $locSeq = $_GET['locSeq'];
            $stationType = $_GET['stationType'];
            $FDS = FolderDataStore::getInstance();
           
            $folders = $FDS->FindByLocation($locSeq);
            $folArr = new ArrayObject();
            $channelGroupDataStore = ChannelGroupDataStore::getInstance();
            foreach($folders as $folder){
                if(isset($_GET["isCalibration"])){
            		if(empty($folder->getIsCalliberationEnabled())){
            			continue;
            		}
            	}elseif($folder->getSeq() == 286 || $folder->getSeq() == 287){
            	    continue;
            	}
                $status = getStationStatus($folder);
                $folderSeq = $folder->getSeq();
                $chCount = $channelGroupDataStore->getCountByFolderSeqs($folderSeq);
                $stack = null;
                if(!empty($chCount)){
                	$stack = "($chCount Stacks)";
                }
                if($folder->getStationType() == strtolower($stationType) || $stationType == "ALL"){
                    if($folder->getIsVisible() == 1){
                    	//hide status for raymond 
                    	if($folder->getSeq() == 270){
                    		$status = "";
                    	}
                        $fol  = array("folderName"=>$folder->getStationName() . " " .$status . " $stack" ,"folderLocation"=>$folder->getIndustryName());
                        $folArr[$folder->getSeq()] = $fol;
                    }
                }
            }
            echo json_encode($folArr);
      }
      function getChannelsGroupBy($channelsInfo,$channelGroups){
          $others = array();
          $filterArr = $channelsInfo;
          $mainArr = array();
          foreach($channelGroups as $CG){
            $groupName = $CG->getName();
            $chSeqs =  $CG->getChannelSeqs();
            $channelsInfo = $filterArr;
            foreach($channelsInfo as $channel){
                if($channel->getIsVisible() == "1"){
                    $chName = $channel->getChannelName();
                    if(!empty($channel->getChannelRealName())){
                        $chName = $channel->getChannelRealName();
                    }
                    $chSeq =  $channel->getSeq();
                    $chNumber = $channel->getChannelNumber();
                    //if($channel->getChannelStation() != null){
                    //    $chName .= " -". $channel->getChannelStation();
                   // }
                    $chArr = array();
                    if(!in_array($chSeq,$chSeqs)){
                        $others[$chNumber] = $chName;
                    }else{
                        unset($others[$chNumber]);
                        if(array_key_exists($groupName,$mainArr)){
                            $chArr = $mainArr[$groupName];
                        }
                        $chArr[$channel->getChannelNumber()] = $chName;
                        $mainArr[$groupName]  = $chArr;
                        unset($filterArr[$chSeq]);
                    }
                }
            }
          }
          if(!empty($others)){
          	$mainArr["Other"]  = $others;
          }
          return $mainArr;
      }
      if($method == $REQUEST_CHANNELS_BY_FOLDER){
          $folSeq = $_GET['folSeq'];
          $FDS = FolderDataStore::getInstance();
          $folder = $FDS->FindBySeq($folSeq);
          $info = "";
          if($folder->getStationType() != "aqmsp"){
              $info = $folder->getIndustryName();
              $info .=  empty($info) ? "" : ", " . $folder->getCity();
              $info .=  empty($info) ? "" : ",". $folder->getState().". ";
              $category =  $folder->getCategory();
              $info .=  empty($category) ? "" : "<strong>Category</strong> : $category";
          }
          $CCDS = ChannelConfigurationDataStore::getInstance();
          $CGDS = ChannelGroupDataStore::getInstance();
          $channelsInfo = $CCDS->FindByFolder($folSeq);
          $channelGroups = $CGDS->FindByFolder($folSeq);
          $chArr = array();
          $mainArr = array();
          if(!empty($channelGroups)){
                $mainArr = getChannelsGroupBy($channelsInfo,$channelGroups);
          }else{
              foreach($channelsInfo as $channel){
                if($channel->getIsVisible()== "1"){
                    $chName = $channel->getChannelName();
                    if($channel->getChannelStation() != null){
                        $chName .= " -". $channel->getChannelStation();
                    }
                    $chArr[$channel->getChannelNumber()] = $chName;
                }
              }
              $mainArr["Channels"] = $chArr;
          }

			if($folSeq == "48"){
			  $comments = "<p><a target='new' href='http://123.63.167.133:8011'>Live Video For MCL</a></p><H3>Use the above link to view live video feed. <br>You can use username as MCL_KOTA and password as MCLKOTA</h3><p>Note: open this link on Internet Explorer and install plug-in first time.</p>";
			  $chArr['video'] = $comments;
		    }
		    $data["channels"] =  $mainArr;
            $data["folderInfo"] = $info;
            if($folder->getIsCalliberationEnabled()){
            	$data["calliberationURL"] = $folder->getCalliberationURL();
            }
            $cameraUrl = $folder->getCameraUrl();
            $data["cameraurl"] = $cameraUrl;
            echo json_encode($data);
      }
      if($method == $REQUEST_DASHBOARD_STATION_REPORT){
        $stationRepMgr = MultiStationReportMgr::getInstance();
        $folderSeq = $_GET["folderSeq"];
        $rep = $stationRepMgr->getDashboardStationReport($folderSeq);
        echo json_encode($rep);
      }
      if($method == $REQUEST_STATION_REPORT){
        $rep = null;
        if($_GET['isMultiStation'] != null){
            if($_GET['iscumulative']  != null){
                $stationRepMgr = EffluentCumulativeFlowReportMgr::getInstance();
                $rep = $stationRepMgr->getCumulativeReport($_GET);
            }else{
                $stationRepMgr = MultiStationReportMgr::getInstance();
                $rep = $stationRepMgr->getMultiStationReport($_GET);
            }
        }else{
            $stationRepMgr = StationReportMgr::getInstance();
            $rep = $stationRepMgr->getStationReport($_GET);
        }
        echo json_encode($rep);
      }
      if(isset($_POST["call"]) && $_POST["call"] == $REQUEST_STATION_REPORT_){
        $rep = null;
        $stationRepMgr = MultiStationReportMgr::getInstance();
        $rep = $stationRepMgr->getStationReport();
        echo json_encode($rep);
      }
      if($method == $REQUEST_WIND_ROSE_JSON){
         $windRoseReportMgr = WindRoseReportMgr::getInstance();
         $windRosePlot = $windRoseReportMgr->getWindRoseReport($_GET);
         echo json_encode($windRosePlot);
      }

      if($method == $REQUEST_MAP_LOCATIONS_JSON){
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $isLoggedIn = $_GET['isLoggedIn'];
         $cpcbMgr = CPCBMgr::getInstance();
         $UDS = UserDataStore::getInstance();
         //if($userLogged->getSeq() == 32){
         	$folders = $UDS->getAllFolder($userLogged->getSeq());
         	$mapJSON = $cpcbMgr->getMapData($folders);
         //}else{
         //	$folderSeqs = $UDS->getAllFolderSeqs($userLogged->getSeq());
         //	$mapJSON = $cpcbMgr->getCPCBMapsJson($isLoggedIn,$folderSeqs);
         //}
         echo json_encode($mapJSON);
      }

      if($method == $REQUEST_STATIONS_RECENT){
         $stationRepMgr = MultiStationReportMgr::getInstance();
         $recentJSON = $stationRepMgr->getRecentReport($_GET);
         echo json_encode($recentJSON);
      }

      if($method == $SAVE_COMMENTS_MASTER){
         $commentsMgr = CommentsMgr::getInstance();
         $res = $commentsMgr->saveCommentsMaster($_GET);
         echo json_encode($res);
      }

      if($method == $REQUEST_COMMENTS_MASTER){
         $locationSeq = $_GET['lsp'];
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $userSeq = $userLogged->getSeq();

         $LDS = LocationDataStore::getInstance();
         $locationsArr = $LDS->FindLocationsByUser($userSeq);
         if(!in_array($userLogged->getLocationSeq(),$locationsArr)){
            array_push($locationsArr, $userLogged->getLocationSeq());
         }
         if(count($locationsArr) == 0){
            echo json_encode("");
            return;
         }
         $locations = null;
         $locationsSeqsStr = implode(",",$locationsArr);
         $commentsMgr = CommentsMgr::getInstance();
         $comments = $commentsMgr->findAllCommentsMasterJSON($locationsSeqsStr);
         echo $comments;
      }
      if($method == $DELETE_COMMENTS_MASTER){
         $commentsMgr = CommentsMgr::getInstance();
         $bool = $commentsMgr->deleteCommentsMasterBySeq( $_GET['seq']);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }

      if($method == $SAVE_COMMENTS_DETAILS){
         $commentsMgr = CommentsMgr::getInstance();
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $userSeq = $userLogged->getSeq();
         $res = $commentsMgr->saveCommentsDetail($_GET,$userSeq);
         echo json_encode($res);
      }

      if($method == $REQUEST_COMMENTS_DETAILS){
         $commentsMgr = CommentsMgr::getInstance();
         $comments = $commentsMgr->findAllCommentsDetailJSON($_GET['seq']);
         echo $comments;
      }
      if($method == $DELETE_COMMENTS_DETAILS){
         $commentsMgr = CommentsMgr::getInstance();
         $bool = $commentsMgr->deleteCommentsDetailsBySeq($_GET['seq']);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $SAVE_EXEMPTION){
         $exmpMgr = ExemptionMgr::getInstance();
         $bool = $exmpMgr->saveExemptionMaster($_GET);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $REQUEST_EXEMPTIONS){
         $exemptionMgr = ExemptionMgr::getInstance();
         session_start();
         $userLogged = new User();
         $userLogged = $_SESSION["userlogged"];
         $isExemption = $_GET["isExemption"];
         if($userLogged->getUserName() == "cpcb"){
            $exmps = $exemptionMgr->findAllExemptionJSON($isExemption);
         }else{
            $exmps = $exemptionMgr->findByLocationSeqJSON($userLogged->getLocationSeq(),$isExemption);
         }
         echo $exmps;
      }
      if($method == $DELETE_EXEMPTION){
         $exemptionMgr = ExemptionMgr::getInstance();
         $bool = $exemptionMgr->deleteExemptionBySeq( $_GET['seq']);
         $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $APPROVE_EXEMPTION){
        $exemptionMgr = ExemptionMgr::getInstance();
        $bool = $exemptionMgr->approveExemption( $_GET['seq'],$_GET['flag']);
        $res = "SUCCESS";
         if($bool == false){
            $res = "FAILURE";
         }
         echo json_encode(array("RESPONSE"=>$res ));
      }
      if($method == $REQUEST_EXEMPTION_COMMENTS){
         $exemptionMgr = ExemptionMgr::getInstance();
         $comments = $exemptionMgr->findCommentsByExemptionSeqJSON($_GET['seq']);
         echo $comments;
      }
      if($method == $SAVE_EXEMPTION_COMMENT){
         $exemptionMgr = ExemptionMgr::getInstance();
         $res = $exemptionMgr->saveExemptionComment($_GET);
         echo json_encode($res);
      }

      if($method == $SAVE_VALIDATION_OR_EXEMPTION){
          $commentsMgr = CommentsMgr::getInstance();
          $res = $commentsMgr->saveValidationOrExeptionMaster($_GET);
          echo json_encode($res);
      }

      function getStationStatus($folder){
        $status = $folder->getIsOnline() == 1 ? "(Online)" : "(Offline)";
        return $status;
      }
?>

