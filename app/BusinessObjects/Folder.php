<?php
  class Folder{

      private $seq,$folderName,$location,$locationFolder,$details,$locationSeq,$actualName,$email;
      private $lastSynchedOn,$lastParsedOn,$lastRemindedOn,$stationType,$category, $latitude, $longitude,$zicode,$isvisible, $isenable;
      private $industryCode, $industryName, $address, $city, $state, $stationName, $model, $mobile, $vendor,$make,$certificationsystem, $deviceid,$m2mCode,$isOnline,$m2mVendor,$uploadFrequency;
      private $parsertype,$parentseq,$lastsyncedseq,$createdon,$lastsentsmson,$repositoryFolder,$isOpacity,$stationVendor;
	  private $hasNotifyUploadFailure,$cameraurl,$haspendingcpcbdata,$iscalliberationenabled,$calliberationurl,$lastspcbsyncedseq,$aqmslastuploadeddate;
	  private $loggerId;
      public function setSeq($seq_){
        $this->seq = $seq_;
      }
      public function getSeq(){
        return $this->seq;
      }
      public function setParserType($parserType_){
        $this->parsertype = $parserType_;
      }
      public function getParserType(){
        return $this->parsertype;
      }
      public function setFolderName($foldername_){
        $this->folderName = $foldername_;
      }
      public function getFolderName(){
        return $this->folderName;
      }
       public function setActualName($actualName_){
        $this->actualName = $actualName_;
      }
      public function getActualName(){
        return $this->actualName;
      }
      public function setLocation($location_){
        $this->location = $location_;
      }
      public function getLocation(){
        return $this->location;
      }
      public function setLocationFolder($locationFolder_){
        $this->locationFolder = $locationFolder_;
      }
      public function getLocationFolder(){
        return $this->locationFolder;
      }
      public function setDetails($details_){
        $this->details = $details_;
      }
      public function getDetails(){
        return $this->details;
      }
      public function getLocationSeq(){
         return $this->locationSeq;
      }
      public function setLocationSeq($locationSeq_){
          $this->locationSeq = $locationSeq_;
      }

      public function getLastSynchedOn(){
         return $this->lastSynchedOn;
      }
      public function setLastSynchedOn($lastSynchedOn_){
          $this->lastSynchedOn = $lastSynchedOn_;
      }

      public function getLastParsedOn(){
         return $this->lastParsedOn;
      }
      public function setLastParsedOn($lastParsedOn_){
          $this->lastParsedOn = $lastParsedOn_;
      }

      public function getLastRemindedOn(){
         return $this->lastRemindedOn;
      }
      public function setLastRemindedOn($lastRemindedOn_){
          $this->lastRemindedOn = $lastRemindedOn_;
      }

      public function setStationType($type_){
        $this->stationType = $type_;
      }
      public function getStationType(){
        return $this->stationType;
      }

      public function setStationName($val){
        $this->stationName = $val;
      }
      public function getStationName(){
        return $this->stationName;
      }

      public function setCategory($cat_){
        $this->category = $cat_;
      }
      public function getCategory(){
        return $this->category;
      }

      public function setIndustryCode($val){
        $this->industryCode = $val;
      }
      public function getIndustryCode(){
        return $this->industryCode;
      }

      public function setIndustryName($val){
        $this->industryName = $val;
      }
      public function getIndustryName(){
        return $this->industryName;
      }

      public function setAddress($val){
        $this->address = $val;
      }
      public function getAddress(){
        return $this->address;
      }

      public function setCity($val){
        $this->city = $val;
      }
      public function getCity(){
        return $this->city;
      }

      public function setState($val){
        $this->state = $val;
      }
      public function getState(){
        return $this->state;
      }

      public function setModel($val){
        $this->model = $val;
      }
      public function getModel(){
        return $this->model;
      }

      public function setMobile($val){
        $this->mobile = $val;
      }
      public function getMobile(){
        return $this->mobile;
      }

      public function setVendor($val){
        $this->vendor = $val;
      }
      public function getVendor(){
        return $this->vendor;
      }

      public function setMake($val){
          $this->make = $val;
      }
      public function getMake(){
          return $this->make;
      }

      public function setCertificationsSystem($val){
          $this->certificationsystem = $val;
      }
      public function getCertificationsSystem(){
          return $this->certificationsystem;
      }

      public function setLatitude($val){
          $this->latitude = $val;
      }
      public function getLatitude(){
          return $this->latitude;
      }

      public function setLongitude($val){
          $this->longitude = $val;
      }
      public function getLongitude(){
          return $this->longitude;
      }

      public function setDeviceId($val){
          $this->deviceid = $val;
      }
      public function getDeviceId(){
          return $this->deviceid;
      }

      public function setEmail($val){
          $this->email = $val;
      }
      public function getEmail(){
          return $this->email;
      }

      public function setZipcode($val){
          $this->zicode = $val;
      }
      public function getZipcode(){
          return $this->zicode;
      }

      public function setIsVisible($val){
        $this->isvisible = $val;
      }
      public function getIsVisible(){
        return $this->isvisible;
      }

      public function setIsEnable($val){
        $this->isenable = $val;
      }
      public function getIsEnable(){
        return $this->isenable;
      }

      public function setM2MCode($val){
        $this->m2mCode = $val;
      }
      public function getM2MCode(){
        return $this->m2mCode;
      }
    public function setIsOnline($isOnline_){
        $this->isOnline = $isOnline_;
    }
    public function getIsOnline(){
        return $this->isOnline;
    }

    public function setM2MVendor($val){
        $this->m2mVendor = $val;
    }
    public function getM2MVendor(){
        return $this->m2mVendor;
    }

    public function setUploadFrequency($val){
        $this->uploadFrequency = $val;
    }
    public function getUploadFrequency(){
        return $this->uploadFrequency;
    }
    
    public function setParentSeq($parentSeq_){
    	$this->parentseq = $parentSeq_; 
    }
	public function getParentSeq(){
		return $this->parentseq;
	}
	
	public function setLastSyncedSeq($lastSyncedOn_){
		$this->lastsyncedseq = $lastSyncedOn_;
	}
	
	public function getLastSyncedSeq(){
		return $this->lastsyncedseq;
	}
	
	public function setCreatedOn($createdOn_){
		$this->createdon = $createdOn_;
	}
	public function getCreatedOn(){
		return $this->createdon;
	}
	
	public function setLastSentSMSOn($sentSMSOn_){
		$this->lastsentsmson = $sentSMSOn_;
	}
	public function getLastSentSMSOn(){
		return $this->lastsentsmson;
	}
	
	public function setRepositoryFolder($value){
		$this->repositoryFolder = $value;
	}
	public function getRepositoryFolder(){
		return $this->repositoryFolder;
	}
	
	public function setIsOpacity($isOpacity_){
		$this->isOpacity = $isOpacity_;
	}
	public function getIsOpacity(){
		return $this->isOpacity;
	}
	public function getStationVendor(){
		return $this->stationVendor;
	}
	public function setStationVendor($stationVendor_){
		$this->stationVendor = $stationVendor_;
	}
	
	public function setHasNotifyUploadFailure($notifyFailure_){
		$this->hasNotifyUploadFailure = $notifyFailure_;
	}
	public function getHasNotifyUploadFailure(){
		return $this->hasNotifyUploadFailure;	
	}
	
	public function setCameraUrl($cameraUrl_){
		$this->cameraurl = $cameraUrl_;
	}
	public function getCameraUrl(){
		return $this->cameraurl;
	}
      
      public function setHasPendingCpcbData($hasPendingCpcbData){
          $this->haspendingcpcbdata = $hasPendingCpcbData;
      }
      public function getHasPendingCpcbData(){
          return $this->haspendingcpcbdata;
      }

      public function setIsCalliberationEnabled($isCalliberationEnabled){
      	$this->iscalliberationenabled = $isCalliberationEnabled;
      }
      public function getIsCalliberationEnabled(){
      	return $this->iscalliberationenabled;
      }
      public function setCalliberationURL($calliberationURL){
      	$this->calliberationurl = $calliberationURL;
      }
      public function getCalliberationURL(){
      	return $this->calliberationurl;
      }
      
      public function setLastSpcbSyncedSeq($lastSeq_){
      	$this->lastspcbsyncedseq = $lastSeq_;
      }
      public function getLastSpcbSyncedSeq(){
      	return $this->lastspcbsyncedseq;
      }
      
      public function setLoggerId($loggerId_){
      		$this->loggerId = $loggerId_;
      }
      public function getLoggerId(){
      	return $this->loggerId;
      }
      
      public function setAQMSLastUploadedDate($lastDate_){
      	$this->aqmslastuploadeddate = $lastDate_;
      }
      public function getAQMSLastUplodgtedDate(){
      	return $this->aqmslastuploadeddate;
      }
      
      
  }
?>
