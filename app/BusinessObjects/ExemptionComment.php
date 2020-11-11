<?php
  
  Class ExemptionComment{
     
     private $seq,$exemptionSeq,$dated, $comments, $userSeq, $userName;
     
     
     public function setSeq($seq_){
        $this->seq = $seq_;
     }
     public function getSeq(){
        return $this->seq;
     }
     
     public function setExemptionSeq($seq_){
        $this->exemptionSeq = $seq_;
     }
     public function getExemptionSeq(){
        return $this->exemptionSeq;
     }
     
     public function setDated($dated_){
        $this->dated = $dated_;
     }
     public function getDated(){
        return $this->dated;
     }
     
     public function setComments($comments_){
        $this->comments = $comments_;
     }
     public function getComments(){
        return $this->comments;
     }
     
     public function setUserSeq($userSeq_){
        $this->userSeq = $userSeq_;
     }
     public function getUserSeq(){
        return $this->userSeq;
     }
     
     public function setUserName($userName_){
        $this->userName = $userName_;
     }
     public function getUserName(){
        return $this->userName;
     }
      
  }
?>
