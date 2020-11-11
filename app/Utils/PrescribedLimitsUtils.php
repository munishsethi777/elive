<?php
  class PrescribedLimitsUtils{
    public static function getPrescribedLimit($pollutant, $stationType = "aqms"){
        $presArray = new ArrayObject();
        $presArray['PM10']= array("min"=>60,"max"=>100);
        $presArray['PM2.5']= array("min"=>40,"max"=>60);
        if($stationType == "aqms"){
            $presArray['SO2']= array("min"=>50,"max"=>80);
            $presArray['NO2']= array("min"=>40,"max"=>80);
        }
        $presArray['Ozone(O3)']= array("min"=>100,"max"=>180);
        $presArray['NH3']= array("min"=>100,"max"=>400);
        $presArray['Benzene']= array("min"=>0,"max"=>5);
        $presArray['CO']= array("min"=>2,"max"=>4);

        //Effluent values
        $presArray['PH']= array("min"=>6.5,"max"=>8.5);
        $presArray['TAN']= array("min"=>0,"max"=>50);
        if($stationType == "effluent"){
            $presArray['SO2']= array("min"=>0,"max"=>50);
        }
        $presArray['Flow']= array("min"=>0,"max"=>3240);

        //Emission values - stack
        $presArray['BOD']= array("min"=>0,"max"=>30);
        $presArray['COD']= array("min"=>0,"max"=>200);
        $presArray['TSS']= array("min"=>0,"max"=>100);
        $presArray['PH']= array("min"=>6.0,"max"=>9.5);
        $presArray['PM']= array("min"=>0,"max"=>150);
        //JK Lakshmi
        $presArray['PM-stack1']= array("min"=>0,"max"=>150);
        $presArray['PM-stack2']= array("min"=>0,"max"=>150);
        //CLP India
        $presArray['Unit1PM']= array("min"=>0,"max"=>150);
        $presArray['Unit2PM']= array("min"=>0,"max"=>150);
		//MCL of Bhoomi PM2.5 is Dust here
		$presArray['Dust Kiln Unit I Bag House']= array("min"=>0,"max"=>100);
        $presArray['Dust Kiln Unit II Bag House']= array("min"=>0,"max"=>100);
        $presArray['Dust Cooler Unit I ESP']= array("min"=>0,"max"=>100);
        $presArray['Dust Cooler Unit II ESP']= array("min"=>0,"max"=>100);
        $presArray['Dust Cement Mill I Bag House']= array("min"=>0,"max"=>100);
        $presArray['Dust Cement Mill II Bag House']= array("min"=>0,"max"=>100);
        $presArray['Dust Cement Mill Unit III Bag House']= array("min"=>0,"max"=>100);
        $presArray['Dust CPP I ESP']= array("min"=>0,"max"=>100);
        $presArray['Dust CPP II ESP']= array("min"=>0,"max"=>100);
        
        if($stationType == "stack"){
            $presArray['SO2']= array("min"=>0,"max"=>100);
            $presArray['NO2']= array("min"=>0,"max"=>800);
            //MCL
            $presArray['SO2']= array("min"=>0,"max"=>100);
            $presArray['SO2 Kiln Unit I']= array("min"=>0,"max"=>100);
            $presArray['SO2 Kiln Unit II']= array("min"=>0,"max"=>100);
            //CLP India
            $presArray['Unit1SO2']= array("min"=>0,"max"=>100);
            $presArray['Unit2SO2']= array("min"=>0,"max"=>100);
        }
        if($pollutant!= null){
            return $presArray[$pollutant];
        }
      }
  }
?>