<?php
  
  Class Exemption{

     private $seq,$folderSeq,$folderName, $dated,$fromDateRange, $toDateRange, $userSeq ,$isApproved, $approvedOn, $comments, $approvalComments, $exemptionDetails,$channelNumbers ,$isExemption, $locationName;
     
     
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
     
     public function setDated($dated){
        $this->dated = $dated;
     }
     public function getDated(){
        return $this->dated;
     }
     
     public function setUserSeq($useq_){
        $this->userSeq = $useq_;
     }
     public function getUserSeq(){
        return $this->userSeq;
     }
     
     
     public function setIsApproved($isApproved){
        $this->isApproved = $isApproved;
     }
     public function getIsApproved(){
        return $this->isApproved;
     }
     
     public function setApprovedOn($approvedOn){
        $this->approvedOn = $approvedOn;
     }
     public function getApprovedOn(){
        return $this->approvedOn;
     }
     
     public function setComments($comments){
        $this->comments = $comments;
     }
     public function getComments(){
        return $this->comments;
     }
     
     public function setApprovalComments($approvalComments){
        $this->approvalComments = $approvalComments;
     }
     public function getApprovalComments(){
        return $this->approvalComments;
     }
     
     public function setExemptionDetails($exemptionDetails){
        $this->exemptionDetails = $exemptionDetails;
        $this->setChannelNumbers(self::getChannelsArrayFromDetails($exemptionDetails));
     }
     public function getExemptionDetails(){
        return $this->exemptionDetails;
     }
     
     public function setChannelNumbers($var){
        $this->channelNumbers = $var;
     }
     public function getChannelNumbers(){
        return $this->channelNumbers;
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
     public function setIsExemption($isexm){
        $this->isExemption = $isexm;
     }
     public function getIsExemption(){
        return $this->isExemption;
     }
     
     public function setLocationName($locationName_){
        $this->locationName = $locationName_;
     }
     public function getLocationName(){
        return $this->locationName;
     }
     private static function getChannelsArrayFromDetails($detailsArr){
        $channelNumbers = array();
        if($detailsArr != null){
            foreach($detailsArr as $details){
                $exemptionDetail = new ExemptionDetail();
                $exemptionDetail = $details;
                array_push($channelNumbers, $exemptionDetail->getChannelNumber());
            }
        }
        return $channelNumbers;   
    }
     
  }
?>
