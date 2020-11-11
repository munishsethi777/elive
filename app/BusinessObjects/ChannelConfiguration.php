<?php

  Class ChannelConfiguration{
     private $seq,$folderSeq,$channelNumber,$channelName,$channelStatusFlag,$channelUnit,$channelStation, $prescribedlimit, $isVisible,$deviceid;
	 private $channelrealname,$prescribedlowlimit,$createdon,$lastmodifiedon,$isstatus,$statuschno; 

     public function setSeq($seq_){
        $this->seq = $seq_;
     }
     public function getSeq(){
        return $this->seq;
     }

     public function setFolderSeq($folderSeq_){
        $this->folderSeq = $folderSeq_;
     }
     public function getFolderSeq(){
        return $this->folderSeq;
     }

     public function setChannelNumber($channelNumber_){
        $this->channelNumber = $channelNumber_;
     }
     public function getChannelNumber(){
        return $this->channelNumber;
     }

     public function setChannelName($channelName_){
        $this->channelName = $channelName_;
     }
     public function getChannelName(){
        return $this->channelName;
     }

     public function setChannelStatusFlag($channelStatusFlag_){
        $this->channelStatusFlag = $channelStatusFlag_;
     }
     public function getChannelStatusFlag(){
        return $this->channelStatusFlag;
     }

     public function setChannelUnit($channelUnit_){
        $this->channelUnit = $channelUnit_;
     }
     public function getChannelUnit(){
        return $this->channelUnit;
     }

     public function setChannelStation($val_){
        $this->channelStation = $val_;
     }
     public function getChannelStation(){
        return $this->channelStation;
     }

     public function setPrescribedLimit($val_){
        $this->prescribedlimit = $val_;
     }
     public function getPrescribedLimit(){
        return $this->prescribedlimit;
     }

     public function setIsVisible($val_){
        $this->isVisible = $val_;
     }
     public function getIsVisible(){
        return $this->isVisible;
     }
  
      public function setDeviceId($val){
          $this->deviceid = $val;
      }
      public function getDeviceId(){
          return $this->deviceid;
      }
      
      public function setChannelRealName($realName_){
      	$this->channelrealname = $realName_;
      }
      public function getChannelRealName(){
      	return $this->channelrealname;
      }
      
      public function setPrescribedLowLimit($val_){
      	$this->prescribedlowlimit = $val_;
      }
      public function getPrescribedLowLimit(){
      	return $this->prescribedlowlimit;
      }
      
      public function setCreatedOn($createdOn_){
      	$this->createdon = $createdOn_;
      }
      public function getCreatedOn(){
      	return $this->createdon;
      }
      
      public function setLastModifiedOn($lastModifiedOn_){
      	$this->lastmodifiedon = $lastModifiedOn_;
      }
      public function getLastModifiedOn(){
      	return $this->lastmodifiedon;
      }
      
      public function setIsStatus($isStatus){
          $this->isstatus = $isStatus;
      }
      public function getIsStatus(){
          return $this->isstatus;
      }
      
      public function setStatusChNo($statusChNo_){
          $this->statuschno = $statusChNo_;
      }
      public function getStatusChNo(){
          return $this->statuschno;
      }

  }
?>
