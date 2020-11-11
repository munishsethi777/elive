<?php

  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects//Folder.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils//ParserType.php");
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects//ChannelConfiguration.php");

  class DropDownUtils{
      private static $stationType = array("aqms","stack","effluent","aqmps");
      private static $ruleTypes = array(lowvalue => "Low Value",highvalue =>"HighValue");
      private static $categoryType = array("Aluminium","Biomedical Waste Incineration","Cement","Chlor Alkali","Commom Hazardous Waste Incinerator","Copper","Distillery","Dye & Dye Intermediates","Electroplating","Fertilizer","Iron & Steel","Oil Refinery","Pesticide","Petrochemicals","Pharmaceuticals","Power Plant","Pulp & Paper","Rolling Mills","Sugar","Tannery","Zinc","Textile","Food & Beverages","Slaughter House","CETP","STP", "Others");
      
      public static function getAllLocationsDropDown($selectName,$onChangeMethod,$selectedValue,$noSelectionValue = null,$styleClass = "form-control m-b"){
        $LDS = LocationDataStore ::getInstance();
        $locations = $LDS->FindAll("name");
        $str = "<select class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        if(!empty($noSelectionValue)){
            $str .= "<option value='0'>$noSelectionValue</option>";
        }else{
            $str .= "<option value='0'>Select Location</option>";
        }
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationName() . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }
    public static function getUserLocationsDropDown($seq,$selectName,$onChangeMethod,$selectedValue,$noSelectionValue = null){
        $LDS = LocationDataStore ::getInstance();
        $locations = $LDS->FindLocationArrByUser($seq);
        $str = "<select required='' class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        if(!empty($noSelectionValue)){
            $str .= "<option value=''>$noSelectionValue</option>";
        }else{
            $str .= "<option value=''>Select Location</option>";
        }
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationName() . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }
     public static function getAllLocationsMultiDropDown($selectName,$onChangeMethod,$selectedValuel){
        $LDS = LocationDataStore ::getInstance();
        $locations = $LDS->FindAll();
        $str = "<select multiple class='chosen-select' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select Location</option>";
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationName() . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }
     public static function getUserLocationsMultiDropDown($locations,$selectName,$onChangeMethod,$selectedValuel,$styleClass = "form-control m-b"){
        $str = "<select multiple required  class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        if($locations != null && $locations <> "" ){
            foreach($locations as $location){
                $select = $selectedValue == $location->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $location->getSeq() . "'" . $select . ">" . $location->getLocationDetails() . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }

    public static function getFoldersDropDownWithStationName($folders,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value=''>Select Station</option>";
        if($folders != null && $folders <> "" ){
            foreach($folders as $folder){
                $folderObj = new Folder();
                $folderObj = $folder;
                $name = $folder->getStationName();
                if(empty($name)){
                    $name =  $folderObj->getFolderName();
                }
                $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $folderObj->getLocation(). " : ".$name . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }
    public static function getRuleTypeDropDown($selectName,$onChangeMethod,$selectedValue){
    	$str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
    	
    		foreach(self::$ruleTypes as $key=>$value){    			
    			$select = $selectedValue == $key ? 'selected' : null;
    			$str .= "<option value='" . $key . "'" . $select . ">" . $value . "</option>";
    		}
    	
    	$str .= "</select>";
    	return $str;
    }
    //For Get Manager's Staions
    public static function getStationsDropDown($folders,$selectName,$onChangeMethod,$selectedValue,$defaultSelected = null,$styleClass = "form-control m-b"){

        $str = "<select required class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        if(!empty($defaultSelected)){
            $str .= "<option value=''>$defaultSelected</option>";
        }
        if($folders != null && $folders <> "" ){
            foreach($folders as $folder){
                $folderObj = new Folder();
                $folderObj = $folder;
                $name = $folder->getStationName();
                if(empty($name)){
                    $name =  $folderObj->getFolderName();
                }
                $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $folderObj->getLocation(). " : ".$name . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }

    public static function getChannelsDropDown($channels,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select a ChanelConfig</option>";
        if($channels != null && $channels <> "" ){
            foreach($channels as $key=>$channel){
                $channelObj = new ChannelConfiguration();
                $channelObj = $channel;
                $chNo = $channelObj->getChannelNumber();
                if(empty($chNo)){
                	$chNo = $key;
                }
                $select = $selectedValue == $chNo ? 'selected' : null;                
                $chName = $channelObj->getChannelName();
                $pLimit = $channelObj->getPrescribedLimit();
                $channelInfo = $chName ." ". $channelObj->getChannelStation();
                $channelInfo .= empty($pLimit) ? "" : " (Pres. ".$pLimit.")";
                $str .= "<option value='" . $chNo . "'" . $select . ">" . $channelInfo . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }
    public static function getChannelsMultiDropDown($channels,$selectName,$onChangeMethod,$selectedValue){
    	$str = "<select multiple required class='chosen-select form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
    	$str .= "<option value='0'>Select a ChanelConfig</option>";
    	if($channels != null && $channels <> "" ){
    		foreach($channels as $channel){
    			$channelObj = new ChannelConfiguration();
    			$channelObj = $channel;
    			$select = $selectedValue == $channelObj->getChannelNumber() ? 'selected' : null;
    			$chNo = $channelObj->getChannelNumber();
    			$chName = $channelObj->getChannelName();
    			$pLimit = $channelObj->getPrescribedLimit();
    			$channelInfo = $chName ." ". $channelObj->getChannelStation();
    			$channelInfo .= empty($pLimit) ? "" : " (Pres. ".$pLimit.")";
    			$str .= "<option value='" . $chNo . "'" . $select . ">" . $channelInfo . "</option>";
    		}
    	}
    	$str .= "</select>";
    	return $str;
    }
    
    public static function getStationTypeDropDown($folders,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select Station Type</option>";
        $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;
        $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $folderObj->getFolderName() . "</option>";
        $str .= "</select>";
        return $str;
    }

    public static function getFolderTypeDropDown($selectName,$selectedValue,$onChangeMethod = null,$styleClass = "form-control m-b"){
        $str = "<select class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $aqms = $selectedValue == "aqms" ? "selected" : null;
        $stack = $selectedValue == "stack" ? "selected" : null;
        $effluent = $selectedValue == "effluent" ? "selected" : null;
        $aqmsp = $selectedValue == "aqmsp" ? "selected" : null;
        $str .= "<option value='aqms' $aqms>AQMS</option>";
        $str .= "<option value='stack' $stack>CEMS</option>";
        $str .= "<option value='effluent' $effluent>EFFLUENT</option>";
        $str .= "<option value='aqmsp' $aqmsp>AQMSP</option>";
        $str .= "</select>";
        return $str;
    }
    public static function getCategoryTypeDropDown($selectName,$selectedValue,$onChangeMethod = null,$styleClass = "form-control m-b"){
        $str = "<select required class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        sort(self::$categoryType);
        $str .= "<option value=''>Select a Category</option>";
            foreach(self::$categoryType as $category){
                $select = $selectedValue == $category ? 'selected' : null;
                $str .= "<option value='" . $category . "'" . $select . ">" . $category . "</option>";
            }
        $str .= "</select>";
        return $str;
    }
    public static function getCategoryTypeDDByFolders($folders,$selectName,$selectedValue,$onChangeMethod = null,$styleClass = "form-control m-b"){
        $str = "<select required class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value=''>Select a Category</option>";
        $arr = array();
        if($folders != null && $folders <> "" ){
            foreach($folders as $folder){
                $category = $folder->getCategory();
                if(!in_array($category,$arr) && !empty($category)){
                    $select = $selectedValue == $folder->getSeq() ? 'selected' : null;
                    $str .= "<option value='" . $category . "'" . $select . ">" . $category. "</option>";
                    array_push($arr,$category);
                }
            }
        }
        $str .= "</select>";
        return $str;
    }
     public static function getStationAndChannelDD($folders,$selectName,$selectedValue,$onChangeMethod = null,$styleClass = "form-control m-b"){
        $str = "<select required class='$styleClass' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value=''>Select a Category</option>";
        $arr = array();
        if($folders != null && $folders <> "" ){
            foreach($folders as $key=>$folder){
                $nameAndSeq = explode("_",$key);
                $stationName = $nameAndSeq[0];
                $seq = $nameAndSeq[1];
                $str .= "<optgroup value='".$seq."' label='". $stationName . "'>";
                foreach($folder as $k=>$value){
                     if(!empty($value)){
                        $str .= "<option value='" . $seq ."_" .$k . "'>" . $value . "</option>";
                     }

                }
                $str .= "</optgroup>";
            }
        }
        $str .= "</select>";
        return $str;
    }
    public static function getFoldersDropDown($folders,$selectName,$onChangeMethod,$selectedValue){
        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select a Station</option>";
        if($folders != null && $folders <> "" ){
            foreach($folders as $folder){
                $folderObj = new Folder();
                $folderObj = $folder;
                $select = $selectedValue == $folderObj->getSeq() ? 'selected' : null;
                $str .= "<option value='" . $folderObj->getSeq() . "'" . $select . ">" . $folderObj->getFolderName() . "</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }


    public static function getM2MVendorsDropDown($selectName,$selectedValue){
        $envirozoneSelected = "";
        $bhoomiSelected = "";
        $alAminSelected = "";
        $eliveSelected = "";
        $heatSelected = "";
        $envirotechSelected = "";
        if($selectedValue == "envirozone"){
            $envirozoneSelected = "selected";
        }else if($selectedValue == "envirotech"){
            $envirotechSelected = "selected";
        }else if($selectedValue == "bhoomi"){
            $bhoomiSelected = "selected";
        }else if($selectedValue == "alamin"){
            $alAminSelected = "selected";
        }else if($selectedValue == "elive"){
            $eliveSelected = "selected";
        }else if($selectedValue == "heat"){
            $heatSelected = "selected";
        }


        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."'>";
        $str .= "<option ". $eliveSelected ." value='elive'>Elive</option>";
        $str .= "<option ". $envirozoneSelected ." value='envirozone'>Envirozone</option>";
        $str .= "<option ". $envirotechSelected ." value='envirotech'>Envirotech</option>";
        $str .= "<option ". $bhoomiSelected ." value='bhoomi'>Bhoomi</option>";
        $str .= "<option ". $alAminSelected ." value='alamin'>Al-Amin</option>";
        $str .= "<option ". $heatSelected ." value='heat'>Heat</option>";
        $str .= "</select>";
        return $str;
    }
    
     public static function getParserTypesDropDown($selectName,$onChangeMethod,$selectedValue){
        $str = "<select required class='form-control m-b' name='". $selectName ."' id='". $selectName ."' onchange='". $onChangeMethod ."'>";
        $str .= "<option value='0'>Select a Parser</option>";
        foreach (ParserType::enum() as $key => $value) {
             //echo "$key -> $value<br>";
             $select = $selectedValue == $value ? 'selected' : null;
             $str .= "<option value='" . $value . "'" . $select . ">" . $key . "</option>";
        }
        $str .= "</select>";
        return $str;
    }

  }
?>
