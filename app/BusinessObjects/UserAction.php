<?php
 class UserAction{
    
    private $seq,$userseq,$dated,$actionname,$actionvalue,$ipaddress;
    
    public function setSeq($val){
        $this->seq = $val;
    }   
    public function getSeq(){
        return $this->seq;
    }
    public function setUserSeq($val){
        $this->userseq = $val;
    }
    public function getUserSeq(){
        return $this->userseq;
    }
    public function setDated($val){
        $this->dated = $val;
    }
    public function getDated(){
        return $this->dated;
    }
    public function setActionName($val){
        $this->actionname = $val;
    }
    public function getActionName(){
        return $this->actionname;
    }
    public function setActionValue($val){
        $this->actionvalue = $val;
    }
    public function getActionValue(){
        return $this->actionvalue;
    }
    public function setIpAddress($val){
        $this->ipaddress = $val;
    }
    public function getIpAddress(){
        return $this->ipaddress;
    }
    
}