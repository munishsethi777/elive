<?php
require_once('Utils/ses/ses.php');
//require_once('Utils/ses/SimpleEmailService.php');
//require_once('Utils/ses/SimpleEmailServiceMessage.php');
//require_once('Utils/ses/SimpleEmailServiceRequest.php');

$ses = new SimpleEmailService('AKIAII6XFP5O3JNV3DKQ', 'yoUcs5Dh7yCsACIi9GAOF+nYPYD/scMZeJT8NRtM');
$m = new SimpleEmailServiceMessage();
$m->addTo('munishsethi777@gmail.com');
$m->setFrom('aman.dubey@envirotechlive.com');
$m->setSubject('You have got Email!');
$m->setMessageFromString('Your message');
//var_dump($ses->sendEmail($m));


//$ses = new SES(awsAccessKey, awsSecretKey);
// List your Verified Email Addresses
//echo "SES::listVerifiedAddresses(): ".print_r($ses->listVerifiedAddresses(), 1)."\n";
//Verify your Email Address
echo "SES::verifyAddress(): ".print_r($ses->verifyAddress('aman.dubey@envirotechlive.com'), 1)."\n";
//Delete a Verified Email Address
//echo "SES::deletedVerifiedAddress(): ".print_r($ses->deletedVerifiedAddress('my_verfied_email_address@example.com'), 1)."\n";
// Get your Send Quota
//echo "SES::getSendQuota(): ".print_r($ses->getSendQuota(), 1)."\n";
// Get your Send Statistics
//echo "SES::getSendStatistics(): ".print_r($ses->getSendStatistics(), 1)."\n";
// Compose and Send an Email

$email = array(
	'Destination' => array(
		'ToAddresses'=> array('munishsethi777@gmail.com'),
		'CcAddresses'=> array(),
		'BccAddresses'=> array()
	),
	'Message' => array(
		'Subject' => array(
			'Data'=>'Test from PHP-SES',
			'Charset'=>'us-ascii'//Not required if is US-ASCII
		),
		'Body'=> array(
			'Html'=>array(
				"Data" => "Hello! <br/> This is an email body <br/> Good Luck.",
				'Charset'=>'us-ascii'//Not required if is US-ASCII
			),
			'Text'=>array(
				"Data" => "Hello! \n This is an email body \n Good Luck.",
				'Charset'=>'us-ascii'//Not required if is US-ASCII
			)
		)
	),
	'ReplyToAddresses' => array('aman.dubey@envirotechlive.com'),
	'ReturnPath' => 'aman.dubey@envirotechlive.com',
	'Source' => 'aman.dubey@envirotechlive.com'
);

echo "SES::sendEmail(): ".var_dump($ses->sendEmail($email))."\n";
// Compose and Send a Raw Email
/*$raw_message = "";
$raw_email = array(
	'RawMessage' => $raw_message,
	'Source' => 'my_verfied_email_address@example.com'
);
*/
//echo "SES::sendRawEmail(): ".print_r($ses->sendRawEmail($raw_email), 1)."\n";
?>
