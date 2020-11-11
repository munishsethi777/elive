<?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] . "/SecurityUtil/SecurityUtil.php");
  $username = $_POST["username"];
  $Password = $_POST["password"];
  $emailId = $_POST["emailId"];
  $active = $_POST["active"];
  $user = new User();
  $user->setUserName($username);
//  if($Password != null && $Password <> ""){
      
//  }
  $encodedPassword = SecurityUtil::Encode($Password);  
  $user->setPassword($encodedPassword);
  $user->setEmailId($emailId);
  $user->setIsActive($active); 
  //set current date
  $user->setDateOfRegistration(Date("Y/m/d"));
  $UDS = new UserDataStore();
  $UDS->Save($user);   
?>
