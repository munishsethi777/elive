<?php
  class CameraPTZ{
  	
  	public static $tableName = "cameraptzaction";
  	public static $className = "CameraPTZ";
  	
      private $seq, $folderSeq, $action, $dated;
      
      public function setSeq($seq){
        $this->seq = $seq;
      }
      public function getSeq(){
        return $this->seq;
      }
	  
	  
	  public function setFolderSeq($folderSeq){
      	$this->folderSeq = $folderSeq;
      }
      public function getFolderSeq(){
      	return $this->folderSeq;
      }
      
      
      public function setAction($action){
      	$this->action = $action;
      }
      public function getAction(){
      	return $this->action;
      }
      
      
      public function setDated($dated){
        $this->dated = $dated;
      }
      public function getDated(){
        return $this->dated;
      }
  }
?>