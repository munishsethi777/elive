<?php
  
  Class CommentDetails{
     
     private $seq,$commentSeq,$dated, $comments, $commentsUser,$isPrivate;
     
     
     public function setSeq($seq_){
        $this->seq = $seq_;
     }
     public function getSeq(){
        return $this->seq;
     }
     
     public function setCommentSeq($comSeq_){
        $this->commentSeq = $comSeq_;
     }
     public function getCommentSeq(){
        return $this->commentSeq;
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
     
     public function setCommentsUser($comUser_){
        $this->commentsUser = $comUser_;
     }
     public function getCommentsUser(){
        return $this->commentsUser;
     }
     
     
     public function setIsPrivate($bool){
        $this->isPrivate = $bool;
     }
     public function getIsPrivate(){
        return $this->isPrivate;
     }
      
  }
?>
