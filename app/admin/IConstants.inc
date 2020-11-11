<?php
  include('/home/envirote/public_html/app/log4php/Logger.php');
  Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');                                        
  $ConstantsArray = array('dbServerUrl' => '/home/envirote/public_html/app/',
  'applicationURL'=>'http://www.envirotechlive.com/app/',
  'rootURL'=>'http://www.envirotechlive.com/',
  'logger' => 'myDBLogger'
  );
  $PERMISSION_MANAGER = "Manager";
  $PERMISSION_USER = "User";
  //FOLLOWING IS THE SERVER URL, COMMENT THIS PATH BEFORE LOCAL USAGE AND SET LOCAL PATH IN ARRAY ABOVE
  //$ConstantsArray = array('dbServerUrl' => '/home/oorjaint/public_html/mbb/'); 
  
?>