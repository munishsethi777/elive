<?php
  class AppLog{
      private $seq,$message,$stackTrace,$type,$dated;
      public function setSeq($seq_){
        $this->seq = $seq_;
      }
      public function getSeq(){
        return $this->seq;
      }
      
      public function setMessage($message_){
        $this->message = $message_;
      }
      public function getMessage(){
        return $this->message;
      }
      
      public function setStackTrace($stackTrace_){
        $this->stackTrace = $stackTrace_;
      }
      public function getStackTrace(){
        return $this->stackTrace;
      }
      
      public function setLogType($type_){
        $this->type = $type_;
      }
      public function getLogType(){
        return $this->type;
      }
      
      public function setDated($dated_){
        $this->dated = $dated_;
      }
      public function getDated(){
        return $this->dated;
      }
  }
?>
