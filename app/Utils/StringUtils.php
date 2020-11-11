<?php
  class StringUtils{
      public static $exemptedString = "exmp";
      private static $smsStatusMessage = array(1=>"Invalid Number",
      		2=>"Absent Subscriber",
      		3=>"Memory Capacity Exceeded",
      		4=>"Mobile Equipment Error",
      		5=>"Network Error",
      		6=>"Barring",
      		7=>"Invalid Sender ID",
      		8=>"Dropped",
      		9=>"NDNC Failed",
      		10=>" Misc. Error"
      );
      public static function getMessage($crtlName,$msgText ,$isError){
       $div = "";
       if($isError){
           $div = "<div class='ui-widget'>
                       <div  class='ui-state-error ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Error during " . $crtlName . " :</strong> <br/>" . $msgText . "</p>
                       </div></div>";
       }else{
           $div = "<div class='ui-widget'>
                       <div  class='ui-state-default ui-corner-all' style='padding: 0 .7em;'> 
                               <p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span> 
                               <strong>Message:</strong>&nbsp;" . $msgText . "</p> 
                       </div></div>"; 
            } 
         return $div;  
      }
      
     
      
      public static function getSMSStatusMessage($errorCode){
      	 $message = self::$smsStatusMessage[$errorCode];
      	 if(empty($message)){
      	 	$message = "Error Occurred";
      	 }
      	 return $message;
      }	  	  public static function is_json($string,$return_data = false) {      	$data = json_decode($string);      	return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;      }
  }
?>
