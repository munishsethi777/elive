<?php
  class ConvertorUtils{
    public static $convertedUnitName = array(
                        'CO'=>"mg/m3",
                        'Ozone(O3)' => "µg/m3",
                        'NO' => "µg/m3",
                        'NO2' => "µg/m3",
                        'NOx' => "mg/Nm3",
                        'NH3' => "µg/m3",
                        'SO2' => "mg/Nm3",
                        'Benzene' => "µg/m3",
                        'Toluene' => "µg/m3",
                        'P-Xylene' => "µg/m3"
                        );
                        
     public static $conversionFactor = array(
                        'CO' => 1.250,
                        'Ozone(O3)' => 2.143,
                        'NO' => 1.340,
                        'NO2' => 2.054,
                        'NOx' => 1.890,
                        'NH3' => 0.760,
                        'SO2' => 2.860,
                        'Benzene' => 3.487,
                        'Toluene' => 4.113,
                        'P-Xylene' => 4.740 
                        );
    
    public static function object_to_array($data){
      if(is_array($data) || is_object($data)){
        $result = array(); 
        foreach($data as $key => $value)
        { 
          $result[$key] = object_to_array($value); 
        }
        return $result;
      }
      return $data;
    }
    public static function getPrescribedValue($chName, $chValue){
        if($chValue == "n.o" || $chValue == "n.a" || $chValue ==StringUtils::$exemptedString){
            return $chValue;   
        }
        $chConvertedValue = $chValue;
        //if(array_key_exists($chName,self::$convertedUnitName)){
            //$conFactor = self::$conversionFactor[$chName];
            //$chConvertedValue = round(($chValue * $conFactor),2);
        //}
        return $chConvertedValue;
    }
    public static function getPrescribedUnit($chName){
        return self::$convertedUnitName[$chName];
    }
    
    public static function getUTF8Encoded($chConvertedUnitVal){
     return mb_check_encoding($chConvertedUnitVal, 'UTF-8') ? $chConvertedUnitVal : utf8_encode($chConvertedUnitVal);
    }
    
    public static function getPLConvertedValueByChannel($dateDataArr,$channelsArray){
            //ArrStructure {"dated":xxxx,"values":[22,33,44]}
           $mainArray = array();
           $channelNamesArr = array();
           foreach($channelsArray as $chKey => $chUnit){
               array_push($channelNamesArr ,$chKey);
           }
           foreach($dateDataArr as $dateData){
                $arrItem = new ArrayObject();
                $arrItem['dated'] = $dateData['dated'];
                $arrItemValuesOnly = array();
                foreach($dateData['values'] as $key => $value){
                    $plValue = ConvertorUtils::getPrescribedValue($channelNamesArr[$key],$value);
                    array_push($arrItemValuesOnly,$plValue);
                }
                $arrItem['values'] = $arrItemValuesOnly;
                array_push($mainArray,$arrItem);
           }
           return $mainArray;
           $arrObj = new ArrayObject($rows);        
           $it = $arrObj->getIterator();
           $WQDChannelsInfo = array();
           $dateArr = array();
           $valArr = array(); 
           $jsonArry = array();
           while( $it->valid()){
             $key = $it->key();
                 $value = $it->current();
                 $date = new DateTime($value[0]);
                 $dateArr[$key] = "'" . $date->format("d-m H:i" ) . "'";    
                 if($isConvertPL == true){
                    $valArr[$key]  = ConvertorUtils::getPrescribedValue($chName,$value[1]);
                 }else{
                    $valArr[$key]  = $value[1];
                 }
                 $it->next();                                           
           } 

            $jsondataDate =  json_encode($dateArr);
            $josnDataValue = json_encode($valArr);
            $jsondataDate = str_replace("\"","",$jsondataDate); 
            $josnDataValue = str_replace("\"","",$josnDataValue);    
            $jsonArry[0] = $jsondataDate;
            $jsonArry[1] = $josnDataValue; 
            return $jsonArry;                            
      }  
  }
?>
