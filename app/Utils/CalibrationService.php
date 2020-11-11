<?php
//require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/Managers/CalibrationMgr.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/Calibration.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/M2MUtil.php");
class CalibrationService{
	public static function makeCalibrationCall($loggerId,$calType,$isStart,$folderSeq,$userSeq){
		$data = array();
		$data["SerialNo"] = $loggerId;
		$data["RequestType"] = "ctl-tag";
		$requestArgs = array();
		//$requestArgs["CAL.CMD"] =  CalibrationService::getCalCMDValue($parameter, $isSpan);
		$requestArgs["CAL.CMD"] =  1;//CalibrationService::getCalCMDValueStartStop($isStart,$calType);
		$data["RequestArgs"] = json_encode($requestArgs);
		$dataJson = json_encode($data);
		//echo $dataJson;
		//die;
		$ch = curl_init();
		$CDS = ConfigurationDataStore::getInstance();
		//$url = "http://envirofront.m2mlogger.com/Services/EF/WS/Requests?uac=bhoomi";
		//$username =	"bhoomiltd@envirotechlive.com"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
		//$password = "password"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
		
		
		$url = "http://envirofront.m2mlogger.com/Services/EF/Api/Requests";
		$username =	"aman.dubey@envirotechlive.com"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
		$password = "Password@123"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
		
		$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		$response = array();
		$success = 0;
		$message = $result;
		if(!empty($result)){
			$jsonResult = json_decode($result);
			$id = $jsonResult->Id;
			if($id){
				$success = 1;
				if(!empty($isStart)){
					//self::saveCalibrationInfo($calType,$folderSeq,$userSeq);
				}else{
					M2MUtil::syncEliveStationsData();	
				}
				$message = "Action completed successfully Response : - " . $result; ;
			}
		}
		//echo "<br>" . $result . "<br><br>-----------------------------------------------------";
		curl_close($ch);
		$response["message"] = $message;// . $requestArgs["CAL.CMD"];
		$response["success"] = $success;
		return json_encode($response);
	}
	
	private static function saveCalibrationInfo($calType,$folderSeq,$userSeq){
		$calibration = new Calibration();
		$calibration->setStartedOn(new DateTime());
		$calibration->setCalType($calType);
		$calibration->setCalStartedBy($userSeq);
		$calibration->setFolderSeq($folderSeq);
		$calibrationMgr = CalibrationMgr::getInstance();
		$calibrationMgr->save($calibration);
	}
	
	private static function getAnalyzerStatus($statusCode){
		if($statusCode == 1){
			$statusCode = "Usable";
		}else if($statusCode == 2){
			$statusCode = "Calibration";
		}else if($statusCode == 3){
			$statusCode = "Maintenance";
		}else if($statusCode == 4){
			$statusCode = "Faulty";
		}else if($statusCode == 5){
			$statusCode = "Zero";
		}else if($statusCode == 6){
			$statusCode = "Calibration Drift";
		}
		return $statusCode;
	}
	private static function getCalCMDValueStartStop($isStart,$cmd){
		$calCmd = 0;
			if($cmd == "span"){
			    if($isStart == 1){
				    $calCmd = 4295034951;
			    }else{
			        $calCmd = 4294969415;
			    }
			}elseif($cmd == "purge"){
				if($isStart == 1){
				    $calCmd = 4295034950;
			    }else{
			        $calCmd = 4294969414;
			    }
			}elseif($cmd == "zero"){
				if($isStart == 1){
				    $calCmd = 4295034959;
			    }
			}
		
		return $calCmd;
	}
	private static function getCalCMDValue($parameter,$isSpan){
		if($parameter == "Sox"){
			if($isSpan){
				//$calCmd =  01;
				$calCmd = 4295034951;
			}else{
				//$calCmd = 02;
				$calCmd = 4295034959;
			}
		}else if($parameter == "Nox"){
			if($isSpan){
				//$calCmd =  03;
				$calCmd = 4295034951;
			}else{
				//$calCmd = 04;
				$calCmd = 4295034959;
			}
		}else if($parameter == "CO2"){
			if($isSpan){
				$calCmd =  05;
			}else{
				$calCmd = 06;
			}
		}else if($parameter == "CO"){
			if($isSpan){
				$calCmd =  07;
			}else{
				$calCmd = 08;
			}
		}else if($parameter == "O2"){
			if($isSpan){
				$calCmd =  09;
			}else{
				$calCmd = 10;
			}
		}
		return $calCmd;
	}
	
	public static function makeCalibrationDataCall($stationCode,$date){
		$data = array();
		$data["siteCode"] = array(0=>$stationCode);//array(0=>"DUMMY");
		$data["lastSyncDate"] = array(0=>$date);//array(0=>"25-09-2018 09:32:55");
		$dataJson = json_encode($data);
		//var_dump("<br>m2msites". $dataJson . "<br>");
		$ch = curl_init();
		$CDS = ConfigurationDataStore::getInstance();
		$url = "http://envirofront.m2mlogger.com/Services/EF/Api/SyncTypeA";//$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUrl);
		$username = "aman.dubey@envirotechlive.com"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
		$password = "Password@123"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
		$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	public static function getCallibrationData($stationCode,$channels){
			$date = new DateTime();
			$date = $date->modify("-1 hours");
			$date = $date->format("d-m-Y h:i:s");
			if($stationCode == "DUMMY"){
				//$date = "22-06-2018 09:10:01";
			}
			$dataJson = CalibrationService::makeCalibrationDataCall($stationCode,$date);
			$parameter = array();
			$units = array();
			$values = array();
			$lastSychDate = $date;
			if(!empty($dataJson)){
				$data = json_decode($dataJson)[0];
				$parameters = $data->parameters;
				$units = $data->units;
				$values = $data->data;
				
				if(!empty($values)){
					$lastIndex = count($values)-1;
					$keyValues = $values[$lastIndex];
					$lastSychDate = $keyValues->Key;
					$values = $keyValues->Value;
				}
			}
				$chCalibrationData = array();
				
				foreach ($channels as $ch){
					$calData = array();
					$chGasConcentrationVal = 0;
					$chGasConcentrationStatus = 0;
					$chCalibrationGas = 0;
					$analyzerRange = "--";
					$analyzerStatus = null;
					$chUnit = "NA";
					$chGasConcentration = "NA";
					$chGasConcentrationStatus = "NA";
					$chCalibrationGas = "NA";
					$analyzerStatus = "NA";
					$chNumber = $ch->getChannelNumber();
					$chName = $ch->getChannelName();
					$chCyCon = $chName.".CY.CON";
					$chCyGas = $chName.".S";
					$status = "STATUS"; 
					$CAL_CMD = "CAL.CMD";
					$CAL_FLAG = "CAL.FLAG";
					$calCmd = 0;
					$calFlag = 0;
					$index = -1;
					if(!empty($parameters)){
						foreach ($parameters as $key=>$parameter){
							if(strtolower($chName) == strtolower($parameter)){
								$index = $key;
								break;
							}
	// 						if(strtolower($CAL_CMD) == strtolower($parameter)){
	// 							$calCmd = $values[$key][0];
	// 						}
	// 						if(strtolower($CAL_FLAG) == strtolower($parameter)){
	// 							$calFlag = $values[$key][0];
	// 						}
	// 						if(strtolower($chName) == strtolower($parameter)){
	 							
	// 						}
	// 						if(strtolower($chCyCon) == strtolower($parameter)){
	// 							$chGasConcentration = $values[$key][0];
	// 							$chGasConcentrationStatus = $values[$key][1];
	// 						}
							//if(strtolower($chCyGas) == strtolower($parameter)){
							
							//}
	// 						if(strtolower($status) == strtolower($parameter)){
	// 							$analyzerStatus = $values[$key][0];
	// 							$analyzerStatus = CalibrationService::getAnalyzerStatus($analyzerStatus);
	// 						}
						}
						if($index != -1){
							$chUnit = $units[$index];
							$chCalibrationGas = $values[$key][0];
						}
					}
					$calibration = "--";
					if($calFlag != "0.00"){
						if($calCmd == 0){
							$calibration = "Zero cal is going";
						}else{
							$calibration = "Span cal is going";
						}
					}
					$calData["unit"] = $chUnit;
					$calData["gasconcentrationvalue"] = $chCalibrationGas;
					$calData["gasconcentrationstatus"] = $chGasConcentrationStatus;
					$calData["calgas"] = "NA";//$chCalibrationGas;
					$calData["anlyzerrange"] = "NA";
					$calData["anlyzerstatus"] = $analyzerStatus;
					$calData["calibrationstatus"] = $calibration;
					$parameterStr = "";
					if(!empty($parameters)){
						$parameterStr = implode(", ", $parameters); 
					}
					$calData["parameters"] = $parameterStr;
					$valuesStr = "";
					if(!empty($values)){
						$valuesStr = var_export($values, true);
					}
					$calData["values"] = $valuesStr;
					$chCalibrationData[$chNumber] = $calData;
				}
				$mainArr["lastSynchedDate"] = $lastSychDate;
				$mainArr["data"] = $chCalibrationData;
				return json_encode($mainArr);
			
		}
		
		public static function isCalbirationStart($val){
		    $binaryValue = decbin($val);
		    $flag = false;
		    if(strlen($binaryValue) > 8){
		        $char = substr($binaryValue, -9, 1);
		        if($char == 1){
		            $flag = true;
		        }
		    }
		    return $flag;
		}
}