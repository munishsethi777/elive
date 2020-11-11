<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "Utils/ses/class.phpmailer.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/ses/class.smtp.php");

$email = new PHPMailer();
$email->From      = 'noreply@envirotechlive.com';
$email->FromName  = 'Envirotech Live';
$email->Subject   = 'Testing';
$email->Body      = "testing mail";
$email->AddAddress( 'baljeetgaheer@gmail.com' );

$file_to_attach = 'PATH_OF_YOUR_FILE_HERE';

//$email->AddAttachment( $file_to_attach , 'NameOfFile.pdf' );
if($email->send()){
	echo "success";
}else{
	echo "failed";
}
