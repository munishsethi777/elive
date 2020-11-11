<?php
  class FileData{
      private $seq,$folderseq,$dated,$reportno,$checksum,$chvaluestatusList;
      
      public function setSeq($seq_){
        $this->seq = $seq_;
      }
      public function getSeq(){
        return $this->seq;
      }
      
      public function setFolderSeq($folderSeq_){
        $this->folderseq = $folderSeq_;
      }
      public function getFolderSeq(){
        return $this->folderseq;
      }
      
      public function setDated($dated_){
        $this->dated = $dated_;
      }
      public function getDated(){
        return $this->dated;
      }
      
      public function setReportNo($reportno_){
        $this->reportno = $reportno_;
      }
      public function getReportNo(){
        return $this->reportno;
      }
      
      public function setCheckSum($checksum_){
        $this->checksum = $checksum_;
      }
      public function getCheckSum(){
        return $this->checksum;
      }
      
      public function setChValueStatusList($chValueStatusList_){
        $this->chvaluestatusList = $chValueStatusList_;
      }
      public function getChValueStatusList(){
        return $this->chvaluestatusList;
      }
      
      
      
  }
?>
