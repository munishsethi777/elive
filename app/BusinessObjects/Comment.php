<?php

  Class Comment{

     private $seq,$folderSeq,$folderName, $channelNumber, $channelName,$fromDateRange, $toDateRange, $startedOn, $lastUpdatedOn
     ,$request;


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

     public function setFolderName($folderName_){
        $this->folderName = $folderName_;
     }
     public function getFolderName(){
        return $this->folderName;
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


     public function setFromDateRange($fromDTRange){
        $this->fromDateRange = $fromDTRange;
     }
     public function getFromDateRange(){
        return $this->fromDateRange;
     }

     public function setToDateRange($toDTRange){
        $this->toDateRange = $toDTRange;
     }
     public function getToDateRange(){
        return $this->toDateRange;
     }

     public function setStartedOn($startedOn){
        $this->startedOn = $startedOn;
     }
     public function getStartedOn(){
        return $this->startedOn;
     }

     public function setLastUpdatedOn($lastUpdatedOn){
        $this->lastUpdatedOn = $lastUpdatedOn;
     }
     public function getLastUpdatedOn(){
        return $this->lastUpdatedOn;
     }

     public function setRequest($req_){
        $this->request = $req_;
     }
     public function getRequest(){
        return $this->request;
     }


  }
?>
