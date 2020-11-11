<?php 

require 'class.phpmailer.php';
require 'class.smtp.php';


$host = 'ssl://email-smtp.us-west-2.amazonaws.com';
$port = 443;

$user = 'AKIAJYPTWUNYHKCXUVRA';//'AKIAJYPTWUNYHKCXUVRA';
$pass = 'AsHTg3oScaZcc+yjZdqANdON9ubyJfJ417Hzs9IBF19/';//'AsHTg3oScaZcc+yjZdqANdON9ubyJfJ417Hzs9IBF19/';
$from = "notification@envirotechlive.com";
$to = "munishsethi777@gmail.com";
$mail = new PHPMailer();
$mail->SMTPDebug = 1;
$mail->IsSMTP(true);
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth   = true;
$mail->Mailer = "smtp";
$mail->Host = $host;
$mail->Port = $port;
$mail->Username = $user;
$mail->Password = $pass;
$mail->SetFrom($from, 'EnvirotechLive');
$mail->AddReplyTo($from,'Technical Support');
$mail->Subject = "test subject";
$mail->MsgHTML("this is body");
$address = $to;
$mail->AddAddress($to, $to);
$mail->AddAddress("baljeetgaheer@gmail.com","baljeetgaheer@gmail.com");
if(!$mail->Send()){
echo($mail->ErrorInfo);
}else{
echo "true";
}



?>

