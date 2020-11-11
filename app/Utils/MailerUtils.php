<?php
require_once($ConstantsArray['dbServerUrl'] . "Mandrill/Mandrill.php");
require_once($ConstantsArray['dbServerUrl'] . "Managers/ReminderMgr.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/ses/class.phpmailer.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/ses/class.smtp.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/UserDataStore.php");
class MailerUtils{


        
        public static function sendError($message,$subject,$isSentSms = true){
            $from = "noreply@envirotechlive.com";
            $to = "baljeetgaheer@gmail.com";
            $cc = "munishsethi777@gmail.com";
            
            self::sendMandrillEmailNotification($message,$subject,$from,$to,$cc);			
            if($isSentSms){
	            $mobileNumber = "9814600356";
	            $smsMessage = $subject . " \n " . $message ;
	            ReminderMgr::sendSMSNew($mobileNumber,$smsMessage);			
            }
        }
        public static function sendDelayNotification($delayHours,$stationSeq,$stationName,$email){
            $to = "baljeetgaheer@gmail.com";
            if (! empty ( $email )) {
                $to = $email;
            }
            $from = "noreply@envirotechlive.com";
            $cc = "munishsethi777@gmail.com";
            $subject = "Delay happened ($delayHours Hrs) with station ".$stationName." (". $stationSeq .")";
            $message = "Hi. A delay happened ($delayHours Hrs) with station ". $stationName ." (Seq:".$stationSeq.") just happened";
            self::sendMandrillEmailNotification ( $message, $subject, $from, $to, $cc );
        }
        public static function sendCronApiError($message,$subject,$email){
        	$to = "baljeetgaheer@gmail.com";
        	if(!empty($email)){
        		$to = $email;
        	}
        	$from = "noreply@envirotechlive.com";        	
        	$cc = "munishsethi777@gmail.com";        
        	self::sendMandrillEmailNotification($message,$subject,$from,$to,$cc);
        }
        
        public static function sendMandrillEmailNotification($message,$subject,$from,$to,$cc=null,$bcc=null){
        	return self::sendEmail($to, $from, $subject, $message);
          // return self::sendSESEmailNotification($message,$subject,$from,$to,$cc,$bcc);
        }

        public static function sendEmailNotificationWithAttachment($message,$subject,$from,$to,$attachments,$ccAddress=null,$bcc=null){
        	 
        	try{
	        	$mail = new PHPMailer();
        		$mail->SetFrom($from, 'EnvirotechLive');
        		$mail->AddReplyTo($from,'EnvirotechLive');
        		$mail->Subject = $subject;
        		$mail->MsgHTML($message);
        		foreach($attachments as $name=>$attachment){
        			$name .= ".xls"; 
        			$mail->addStringAttachment($attachment, $name);
        		}
        		$toAddresses = explode(",",$to);
        		foreach($toAddresses as $toAdd){
        			$mail->AddAddress($toAdd, $toAdd);
        		}
        		if(!empty($ccAddress)){
	        		foreach($ccAddress as $cc){
		        		$mail->addBCC($cc,$cc);
		        	}
        		}
        		//var_dump($mail);
        		if(!$mail->Send()){
        			$errorInfo = $mail->getSMTPInstance()->getError();
        			echo "<br>" . $subject ." Email Sending failed to address :- ". $to ."<br>".$errorInfo["error"] ."<br>".$errorInfo["detail"]."<br>";
        			return false;        			
        		}else{
        			echo "success";
        			return true;
        		}
        	} catch (phpmailerException $e) {
        		echo $e->errorMessage(); //Pretty error messages from PHPMailer
        	} catch (Exception $e) {
        	    echo $e->getMessage(); //Boring error messages from anything else!
        	}
        }
        
        public static function sendChannelMismatchNotification($response,$oldChannels,$newChannels,$folder){
        	$message = "Channels are mismatch for Station - " . $folder->getStationName() . ", Industry - " . $folder->getIndustryName();
        	$message .= "<br/>Response : - " .$response;
        	$message .= "<br/>Existing channels are : - " .$oldChannels;
        	$message .= "<br/>New channels are : - " .$newChannels;
        	$subject  = "Channels Mismatch Notification";
        	$from = "noreply@envirotechlive.com";
        	$vendor = $folder->getVendor();
        	$emailTo = null;
        	if(!empty($vendor) && (strpos(strtolower($vendor), 'bhoomi') !== false)){
        		$uds = UserDataStore::getInstance();
        		$bhoomiManager = $uds->FindBySeq(83);//83 is bhoomimanager seq
        		$emailTo = $bhoomiManager->getEmailId();
        	}
            if(!empty($vendor) && (strpos(strtolower($vendor), 'heat') !== false)){
                $emailTo = 'saleshet@gmail.com';
            }

        	if(empty($emailTo)){
        		$emailTo = "baljeetgaheer@gmail.com";
        	}
        	$ccTo = "munishsethi777@gmail.com";
        	MailerUtils::sendMandrillEmailNotification($message,$subject,$from,$emailTo,$ccTo);
        }
        
        public static function sendHighValueLogsNotification($emailTo,$notificationType,$attachements,$fromDate,$toDate){
        	$message = "<html><body>"; 
        	$message .= "Please find attached CPCB reports for High value reminders and station status for the time period ";
        	$message .= $fromDate . " to " . $toDate ."</body></html>";
        	$subject = "";
        	if($notificationType == "monthly"){        		      	
        		$subject  = "Monthly (" . $fromDate . " to " . $toDate . ") CPCB Reports";
        	}else{        		
        		$subject  = "Weekly (" . $fromDate . " to " . $toDate . ") CPCB Reports";
        	}
        	$from = "noreply@envirotechlive.com";
        	$ccAddress[0] = "munishsethi777@gmail.com";
        	$ccAddress[1] = "amandeepdubey@gmail.com";
        	$ccTo = "munishsethi777@gmail.com";
        	MailerUtils::sendEmailNotificationWithAttachment($message, $subject, $from, $emailTo, $attachements,$ccAddress);
        }


			public static function sendStationMarkedOffline($folder){
					$message = "Dear Sir/Madam";
					$message .= "<br/>Your Station - " . $folder->getStationName() . ", Industry - " . $folder->getIndustryName();					
					$lastsynch = $folder->getLastSynchedOn();					
					$dated = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastsynch);
					$dated = $dated->format("d-m-Y H:i");
					$message .= " has stopped uploading data since -  ".$dated;
					$message .= "<br/><br/>Regards";
					$message .= "<br/>Envirotechlive Support";
					$subject = "Data uploading Status for ". $folder->getIndustryName() ." - ". $folder->getStationName();
					$from = "noreply@envirotechlive.com";
					$to = $folder->getEmail();
					if(empty($to)){
						$to = "baljeetgaheer@gmail.com";
					}
					$cc = null;
					MailerUtils::sendEmail($to,$from,$subject,$message);
					$locationSeq = $folder->getLocationSeq();
					if($locationSeq == 15){					
						$mobileNumber = $folder->getMobile();
						if(!empty($mobileNumber)){
							$smsMessage = "Your Station - " . $folder->getStationName();
							$lastsynch = $folder->getLastSynchedOn();
							$dated = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastsynch);
							$dated = $dated->format("d-m-Y H:i");
							$smsMessage .= " has stopped uploading data since -  ".$dated;
							ReminderMgr::sendSMSNew($mobileNumber,$smsMessage);
						}
					}
				}
				
				public static function sendStationMarkedOnline($folder){
					$message = "Dear Sir/Madam";
					$message .= "<br/>Your Station - " . $folder->getStationName() . ", Industry - " . $folder->getIndustryName();					
					$dated = new DateTime();
					$dated = $dated->format("d-m-Y H:i");
					$message .= " has started uploading data on -  ".$dated;
					$message .= "<br/><br/>Regards";
					$message .= "<br/>Envirotechlive Support";
					$subject = "Data uploading Status for ". $folder->getIndustryName() ." - ". $folder->getStationName();
					$from = "noreply@envirotechlive.com";
					$to = $folder->getEmail();
					if(empty($to)){
						$to = "baljeetgaheer@gmail.com";
					}
					$cc = null;
					MailerUtils::sendEmail($to,$from,$subject,$message);					
				}
				
				public static function sendUpdateStationStatus($seq,$isEnabled){
                    if($seq == null || $seq == 0){
                        return;
                    }
					$status = "Disabled";
					if(!empty($isEnabled)){
						$status = "Enabled";
					}
					$folderDataStore = FolderDataStore::getInstance();
					$folder = $folderDataStore->FindBySeq($seq);
					$stationName = $folder->getStationName();
					if(empty($stationName)){
						$stationName = $folder->getFolderName();
					}
					$message = "Dear Sir/Madam";
					$message .= "<br/>Your Station - " . $stationName . ", Industry - " . $folder->getIndustryName();
					$dated = new DateTime();
					$dated = $dated->format("d-m-Y H:i");
					$message .= " is ". $status ." on -  ".$dated;
					$message .= "<br/><br/>Regards";
					$message .= "<br/>Envirotechlive Support";
					$subject = $status . " Station ". $folder->getIndustryName() ." - ". $folder->getStationName();
					$from = "noreply@envirotechlive.com";
					$to = "baljeetgaheer@gmail.com";
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
					// More headers
					$headers .= 'From: ' . $from . "\r\n";
					$headers .= 'Cc: munishsethi777@gmail.com' . "\r\n";
					$result = mail($to,$subject,$message,$headers);
					if(!$result) {						
						return false;
					} else {						
						return true;
					}
				}
				
				public static function sendEmailNew($to,$from,$subject,$message,$cc = null){
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";						
					// More headers
					$headers .= 'From: ' . $from . "\r\n";
					if(!empty($cc)){
						$headers .= 'Bcc: ' .$cc . "\r\n";
					}
					$result = mail($to,$subject,$message,$headers);
					if(!$result) {
						echo "<br>Failed :" . $subject ." Email Sending failed to address :- ". $to ;
						return false;
					} else {
						echo "<br>Success :" . $subject ." Email Sent to address :- ". $to ;
						return true;
					}
						
				}
				public static function sendEmail($to,$from,$subject,$message){
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
					
					// More headers
					$headers .= 'From: ' . $from . "\r\n";
					$headers .= 'Bcc: munishsethi777@gmail.com' . "\r\n";
					$result = mail($to,$subject,$message,$headers);
					if(!$result) {   
					     echo "<br>Failed :" . $subject ." Email Sending failed to address :- ". $to ;
					     return false;
					} else {
					    echo "<br>Success :" . $subject ." Email Sent to address :- ". $to ;
					    return true;
					}
				}
				
				public static function replacePlaceHolders($placeHolders,$body){
				    foreach ($placeHolders as $key=>$value){
				        $placeHolder = "{".$key."}";
				        $body = str_replace($placeHolder, $value, $body);
				    }
				    return $body;
				}	
		}
		


?>
