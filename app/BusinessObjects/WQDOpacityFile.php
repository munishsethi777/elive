<?php
class WQDOpacityFile{
	private $seq,$folderseq,$dated,$channels;
	
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
	
	public function setDated($date_){
		$this->dated = $date_;
	}
	public function getDated(){
		return $this->dated;
	}
	
	public function setChannels(array $channels_){
		$this->channels = $channels_;
	}
	public function getChannels(){
		return $this->channels;
	}
	
}