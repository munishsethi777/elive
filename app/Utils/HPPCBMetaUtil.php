<?php
class HPPCBMetaUtils{
    private static $mainArr = array();
    private static $locationMetaArr = array();
    private static $keys = array();
    private static $urls = array();
    private static $versionCodes = array();
    private static $util;
    public static function getInstance(){
        if(empty(self::$util)){
            self::initMeta();
            self::initLocationMeta();
            self::$util = new HPPCBMetaUtils();
        }
        return self::$util;
    }
    
    public static function initMeta(){
        $metaArr = array();
        //251 Station id
        $metaArr["monitiring_id"] = "Stack_12";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3753"] = $metaArr;
        
        //254 Station id
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_8";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3764"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_9";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13	";
        self::$mainArr["3765"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_10";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13	";
        self::$mainArr["3768"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_11";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13	";
        self::$mainArr["3769"] = $metaArr;
        
        //252 Station Id
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_5";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3754"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_6";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3755"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_7";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3756"] = $metaArr;
        
        //253 Station Id
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_3";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3757"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack_4";
        $metaArr["parameter_id"] = "parameter_3";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3758"] = $metaArr;
        
        //MPCB Parameters
        //Manikgarh
        $metaArr = array();
        $metaArr["monitiring_id"] = "AAQMS_Fruitgarden";
        $metaArr["parameter_id"] = "parameter_7";
        $metaArr["analyzer_id"] = "analyzer_89";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["2754"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "AAQMS_Fruitgarden";
        $metaArr["parameter_id"] = "parameter_60";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["2755"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "AAQMS_Fruitgarden";
        $metaArr["parameter_id"] = "parameter_8";
        $metaArr["analyzer_id"] = "analyzer_426";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["2750"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "AAQMS_Fruitgarden";
        $metaArr["parameter_id"] = "parameter_62";
        $metaArr["analyzer_id"] = "analyzer_426";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["2753"] = $metaArr;
        
        //--------------Lloyds----------------
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS1";
        $metaArr["parameter_id"] = "parameter_7";
        $metaArr["analyzer_id"] = "analyzer_89";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3791"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS1";
        $metaArr["parameter_id"] = "parameter_60";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3792"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS1";
        $metaArr["parameter_id"] = "parameter_63";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_3";
        self::$mainArr["3793"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS1";
        $metaArr["parameter_id"] = "parameter_9";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_3";
        self::$mainArr["3794"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS1";
        $metaArr["parameter_id"] = "parameter_62";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_3";
        self::$mainArr["3795"] = $metaArr;
        
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS1";
        $metaArr["parameter_id"] = "parameter_8";
        $metaArr["analyzer_id"] = "analyzer_267";
        $metaArr["unit_ID"] = "unit_4";
        self::$mainArr["3796"] = $metaArr;
        //--------------**********----------------
        
        //Vayunanda
        //Location 181 & Station Id - 306 
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS";
        $metaArr["parameter_id"] = "parameter_7";
        $metaArr["analyzer_id"] = "analyzer_439";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3961"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS";
        $metaArr["parameter_id"] = "parameter_60";
        $metaArr["analyzer_id"] = "analyzer_439";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3962"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS";
        $metaArr["parameter_id"] = "parameter_8";
        $metaArr["analyzer_id"] = "analyzer_439";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3963"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS";
        $metaArr["parameter_id"] = "parameter_63";
        $metaArr["analyzer_id"] = "analyzer_439";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3964"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS";
        $metaArr["parameter_id"] = "parameter_9";
        $metaArr["analyzer_id"] = "analyzer_439";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3965"] = $metaArr;
        
        $metaArr = array();
        $metaArr["monitiring_id"] = "CAAQMS";
        $metaArr["parameter_id"] = "parameter_62";
        $metaArr["analyzer_id"] = "analyzer_439";
        $metaArr["unit_ID"] = "unit_30";
        self::$mainArr["3966"] = $metaArr;
        
        //Vayunda Stack Station Id 307
        $metaArr = array();
        $metaArr["monitiring_id"] = "Stack";
        $metaArr["parameter_id"] = "parameter_10";
        $metaArr["analyzer_id"] = "analyzer_96";
        $metaArr["unit_ID"] = "unit_13";
        self::$mainArr["3973"] = $metaArr;
    }
    
    private static function initLocationMeta(){
        self::$locationMetaArr["5"] = "site_2058";
        self::$locationMetaArr["157"] = "site_1400";
        self::$locationMetaArr["11"] = "site_693";
        self::$locationMetaArr["163"] = "site_805";
        self::$locationMetaArr["181"] = "site_1333";
        
        self::$keys["5"] = "c2l0ZV8yMDU4LHZlcl8zLjE=########";
        self::$keys["157"] = "c2l0ZV8xNDAwLHZlcl8zLjE=########";
        self::$keys["11"] = "c2l0ZV85MTcsdmVyXzEuMCxkZWZhdWx0";
        self::$keys["163"] = "c2l0ZV84MDUsdmVyXzEuMCxkZWZhdWx0";
        self::$keys["181"] = "c2l0ZV8xMzMzLHZlcl8xLjAsZGVmYXVs";
        
        $realTimeUrlForHppcb = "http://hppcb.glensserver.com/HPPCB/realTimeUpload";
        $delayUrlForHppcb = "http://hppcb.glensserver.com/HPPCB/delayedUpload";
        
        
        
        $urlsHppcb = array(0=>$realTimeUrlForHppcb,1=>$delayUrlForHppcb);
        self::$urls[StringConstants::HPPCB] = $urlsHppcb;
        
        $realTimeUrlFormppcb = "http://onlinecems.ecmpcb.in/mpcb/realtimeUpload";
        $delayUrlFormpcb = "http://onlinecems.ecmpcb.in/mpcb/delayedUpload";
        
        //$realTimeUrlFormppcb = "http://onlinecems.ecmpcb.in/mpcb/realtimeUploadTester";
        //$delayUrlFormpcb = "http://onlinecems.ecmpcb.in/mpcb/realtimeUploadTester";
        
        $urlsmpcb = array(0=>$realTimeUrlFormppcb,1=>$delayUrlFormpcb);
        self::$urls[StringConstants::MPCB] = $urlsmpcb;
        
        self::$versionCodes[StringConstants::HPPCB] = "ver_3.1";
        self::$versionCodes[StringConstants::MPCB] = "ver_1.0";
    }
    
    public function getSiteIdByLocation($locationSeq){
        $locationSeq = strval($locationSeq);
        return self::$locationMetaArr[$locationSeq];
    }
    
    public function getKeyByLocation($locationSeq){
        $locationSeq = strval($locationSeq);
        return self::$keys[$locationSeq];
    }
    
    public function getTargetUrl($stateBoardCode){
        return self::$urls[$stateBoardCode];
    }
    
    public function getVersionCode($stateBoardCode){
        return self::$versionCodes[$stateBoardCode];
    }
    
    
    
    public function getMetaByChannel($id){
        $id = strval($id);
        return self::$mainArr[$id];
    }
    
}