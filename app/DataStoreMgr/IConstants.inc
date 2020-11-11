<?php                                        
  include('/Applications/MAMP/htdocs/elive/app/log4php/Logger.php');
  Logger::configure('/Applications/MAMP/htdocs/elive/app/log4php/log4php.xml');                                         
  $ConstantsArray = array('dbServerUrl' => '/Applications/MAMP/htdocs/elive/app/',
  'baseUrl' => '/Applications/MAMP/htdocs/elive/app/',
  'rootURL'=>'http://localhost/elive/',
  'applicationURL'=>'http://localhost/elive/app/',
  'logger' => 'myDBLogger'
 );
  
//   $ConstantsArray = array('dbServerUrl' => '/home/enviro/public_html/site/',
//  'applicationURL'=>'http://www.envirotechlive.com/site/'
//  );
  $PERMISSION_MANAGER = "Manager";
  $PERMISSION_USER = "User";
  
  $Log4PHP_MyDBLogger = "myDBLogger";
  $Log4PHP_MyMailerLogger = "myMailerLogger";
  $Log4PHP_MyFileLogger = "myFileLogger";
  //FOLLOWING IS THE SERVER URL, COMMENT THIS PATH BEFORE LOCAL USAGE AND SET LOCAL PATH IN ARRAY ABOVE
  //$ConstantsArray = array('dbServerUrl' => '/home/oorjaint/public_html/mbb/'); 
  
?>