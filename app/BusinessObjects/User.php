<?php
  class User{
    
      private $seq,$username,$password,$emailid,$dateofregistration,$isactive,$fullName,$confirmPassword,$decodedPassword;
      private $locationSeq, $folderSeq, $isManager, $locationName,$mobile,$otherLocationSeqs;
      private $otp,$otpemailid,$otpgeneratedate;
      private $loginotp,$loginotpgeneratedate;
      
      public function setSeq($seq_){
        $this->seq = $seq_;
      }
      public function getSeq(){
        return $this->seq;
      }
    
      public function setUserName($username_){
        $this->username = $username_;
      }
      public function getUserName(){
        return $this->username;
      }
      
      public function setPassword($password_){
        $this->password = $password_;
      }
      public function getPassword(){
        return $this->password;
      }  
      
      public function setEmailId($emailId_){
        $this->emailid = $emailId_;
      }
      public function getEmailId(){
        return $this->emailid;
      }
      
      public function setDateOfRegistration($dateOfRegistration_){
        $this->dateofregistration = $dateOfRegistration_;
      }
      public function getDateOfRegistration(){
        return $this->dateofregistration;
      }
      
      public function setIsActive($isActive_){
        $this->isactive = $isActive_;
      }
      public function getIsActive(){
        return $this->isactive;
      }  
      public function getFullName(){
          return $this->fullName;
      }
      public function setFullName($fullName_){ 
          $this->fullName = $fullName_;
      } 
      
      public function setConfirmPassword($confirmPassword_){
        $this->confirmPassword = $confirmPassword_;
      }
      public function getConfirmPassword(){
        return $this->confirmPassword;
      } 
      public function setDecodedPassword($password){
          $this->decodedPassword = $password;
      } 
      public function getDecodedPassword(){
          return $this->decodedPassword;
      }
      
      public function setLocationSeq($seq_){
        $this->locationSeq = $seq_;
      }
      public function getLocationSeq(){
        return $this->locationSeq;
      }
      public function setOtherLocationSeqs($otherLocations_){
        $this->otherLocationSeqs = $otherLocations_;
      }
      public function getOtherLocationSeqs(){
        return $this->otherLocationSeqs;
      }
      public function setLocationName($name_){
        $this->locationName = $name_;
      }
      public function getLocationName(){
        return $this->locationName;
      }
      public function setFolderSeq($seq_){
        $this->folderSeq = $seq_;
      }
      public function getFolderSeq(){
        return $this->folderSeq;
      }
      
      public function setIsManager($isManager_){
        $this->isManager = $isManager_;
      }
      public function getIsManager(){
        return $this->isManager;
      }
	  
	  public function setMobile($mobile_){
        $this->mobile = $mobile_;
      }
    public function getMobile(){
      return $this->mobile;
    }
    
    /**
     * Method to get OTP
     */
    public function getOTP(){
      return $this->otp;
    }
    /**
     * Method to set OTP
     */
    public function setOTP($otp){
      $this->otp = $otp;
    }
    /**
     * Method to get OTP Email Id
     */
    public function getOTPEmailId(){
      return $this->otpemailid;
    }
    /**
     * Method to set OTP Email Id
     */
    public function setOTPEmailId($otpemailid){
      $this->otpemailid = $otpemailid;
    }
    /**
     * Method to get OTP Generate Date
     */
    public function getOTPGenerateDate(){
      return $this->otpgeneratedate;
    }
    /**
     * Method to set OTP Generate Date
     */
    public function setOTPGenerateDate($otpgeneratedate){
      $this->otpgeneratedate = $otpgeneratedate;
    }
    public function getLoginOTP(){
        return $this->loginotp;
    }
    public function setLoginOTP($otp){
        $this->loginotp = $otp;
    } 
    public function getLoginOTPGenerateDate(){
      return $this->loginotpgeneratedate;
    }
    public function setLoginOTPGenerateDate($otpgeneratedate){
      $this->loginotpgeneratedate = $otpgeneratedate;
    }
  }
?>
