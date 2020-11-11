<?php
  class FolderUser{
   private $folderSeq, $userSeq, $permission;
   
   public function getFolderSeq(){
        return $this->folderSeq;
   }
   public function setFolderSeq($folderSeq_){
        $this->folderSeq = $folderSeq_;
   }
   
   public function getUserSeq(){
        return $this->userSeq;
   }
   public function setUserSeq($userSeq_){
        $this->userSeq = $userSeq_;
   }
   
   public function getPermission(){
        return $this->permission;
   }
   public function setPermission($permission_){
        $this->permission = $permission_;
   }
  
  }
?>
