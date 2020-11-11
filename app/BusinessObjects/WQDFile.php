<?php
  class WQDFile{
       private $seq,$date,$name,$folderseq,$locationseq,$dataArr;
       
       public function setSeq($seq_){
            $this->seq = $seq_;
        }
        public function getSeq(){
            return $this->seq;
        }  
        
        public function setFileDate($date_){
            $this->date = $date_;
        }
        public function getFiledate(){
            return $this->date;
        } 
         
        public function setName($name_){
            $this->name = $name_;
        }
        public function getName(){
            return $this->name;
        } 
         
        public function setFolderSeq($folderSeq_){
            $this->folderseq = $folderSeq_;
        }
        public function getFolderSeq(){
            return $this->folderseq;
        }  
           public function setLocationSeq($locationSeq_){
            $this->locationseq = $locationSeq_;
        }
        public function getLocationSeq(){
            return $this->locationseq;
        } 
        
        public function setData($dataArr_){
             $this->dataArr = $dataArr_; 
        }
         public function getData(){
             return $this->dataArr; 
        } 
  }
 

?>
