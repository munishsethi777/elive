<?php
  class Files{
       //fileSeq
//        fileType (SLM, WLM, DPCC)
//        fileName
//        dateofUpload
//        fileUserSeq
//        fileFolderSeq
        private $seq,$type,$name,$dataOfUpload,$userSeq,$folderSeq;
        
        public function setFileSeq($seq_){
            $this->seq = $seq_;
        }
        public function getFileSeq(){
            return $this->seq;
        }                                           
        
        public function setFileType($type_){
            $this->type = $type;
        }
        public function getFileType(){
            return $this->type;
        }
        public function setFileName($name_){
            $this->name = $name_;
        }
        public function getFileName(){
            return $this->name;
        }
        
        public function setDateOfUpload($date){
            $this->dataOfUpload = $date_;
        }
        public function getDateOfUpload(){
            return $this->dataOfUpload;
        }
        
        public function setUserSeq($userSeq_){
            $this->userSeq = $userSeq_;
        }
        public function getUserSeq(){
            $this->userSeq;
        }
        
        public function setFolderSeq($folderSeq_){
            $this->folderSeq = $folderSeq_;
        }
        public function getFolderSeq(){
            $this->folderSeq;
        }
            
  }
?>
