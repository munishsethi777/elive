<?php
class Location{
    
   private $seq,$locationFolder,$locationName,$details,$isPrivate,$hasDirectory,$repositoryFolder,$createdOn;
      
      public function setIsPrivate($isPrivate){
        $this->isPrivate = $isPrivate;
      }
      public function getIsPrivate(){
        return $this->isPrivate;
      }
      public function setSeq($seq_){
        $this->seq = $seq_;
      }
      public function getSeq(){
        return $this->seq;
      }
      public function getHasDirectory(){
        return $this->hasDirectory;
      }
      public function setHasDirectory($hasDirectory_){
        $this->hasDirectory = $hasDirectory_;
      }
      
      public function setLocationFolder($foldername_){
        $this->locationFolder = $foldername_;
      }
      public function getLocationFolder(){
        return $this->locationFolder;
      }
      
      public function setLocationName($location_){
        $this->locationName = $location_;
      }
      public function getLocationName(){
        return $this->locationName;
      }  
      
      public function setLocationDetails($details_){
        $this->details = $details_;
      }
      public function getLocationDetails(){
        return $this->details;
      }
      
      public function setRepositoryFolder($val){
      	$this->repositoryFolder = $val;
      }
      public function getRepositoryFolder(){
      	return $this->repositoryFolder;
      }
      
      public function setCreatedOn($val){
      	$this->createdOn = $val;
      }
      public function getCreatedOn(){
      	return $this->createdOn;
      }
      
}
?>
