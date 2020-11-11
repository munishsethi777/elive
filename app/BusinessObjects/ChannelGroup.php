<?php
  class ChannelGroup{
  	
     private $seq,$name,$channelSeqs,$folderSeq,$subfolderseq;
     public function setSeq($seq_){
        $this->seq = $seq_;
     }
     public function getSeq(){
        return $this->seq;
     }
      public function setName($name_){
        $this->name = $name_;
     }
     public function getName(){
        return $this->name;
     }
      public function setChannelSeqs($seqs_){
        $this->channelSeqs = $seqs_;
     }
     public function getChannelSeqs(){
        return $this->channelSeqs;
     }

     public function setFolderSeq($folderSeq_){
        $this->folderSeq = $folderSeq_;
     }
     public function getFolderSeq(){
        return $this->folderSeq;
     } 
     public function getSubFolderSeq(){
     	return $this->subfolderseq;
     }
     public function setSubFolderSeq($subFolderSeq_){
     	$this->subfolderseq = $subFolderSeq_;
     }
     
  }
?>
