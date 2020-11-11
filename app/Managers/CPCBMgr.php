<?php 

Class CPCBMgr{
    private static $cpcbMgr;
    public static function getInstance(){
        if (!self::$cpcbMgr)
        {
            self::$cpcbMgr = new CPCBMgr();
            return self::$cpcbMgr;
        }
        return self::$cpcbMgr;
    }
    public static function getCPCBLocSeqsArray(){
        $locSeqs =  array(5,6,7,8);
        return $locSeqs;
    }
    public static function getCPCBMapsJson($isLoggedIn,$folderSeqs = array()){

        $jsplNorth = array("owner" => "JSPL North side","stationType"=>"AQMS","station"=>"AQMS","location"=>"Residential area near Air Strip, Raigarh","folderSeq" => "12","longitude" => "21.936393","latitude" => "83.352769","dateOfEstablishment"=>"Jun-07","dateOfAvailability"=>"24.06.2102");
        $jsplEast = array("owner" => "JSPL East Side","stationType"=>"AQMS","station"=>"AQMS","location"=>"Residential area near STP II, Raigarh","folderSeq" => "21","longitude" => "21.930819","latitude" => "83.359582","dateOfEstablishment"=>"Apr-08","dateOfAvailability"=>"01.07.2013");
        $jsplWest = array("owner" => "JSPL West Side","stationType"=>"AQMS","station"=>"AQMS","location"=>"Residential area near STP I, Raigarh","folderSeq" => "22","longitude" => "21.931606","latitude" => "83.336839","dateOfEstablishment"=>"Apr-08","dateOfAvailability"=>"01.07.2013");
	    $jsplSouth = array("owner" => "JSPL South Side","stationType"=>"AQMS","station"=>"AQMS","location"=>"Near Ash Dyke, Raigarh","folderSeq" => "23","longitude" => "21.912998","latitude" => "83.329108","dateOfEstablishment"=>"Dec-08","dateOfAvailability"=>"01.07.2013");



        $acc = array("owner" => "ACC Gagal","stationType"=>"AQMS","station"=>"AQMS","location"=>"Gagal","folderSeq" => "9","longitude" => "31.412256","latitude" => "76.838551");
        $itc1 = array("owner" => "ITC Bhadrachalam","stationType"=>"AQMS","station"=>"AQMS 1","location"=>"106 Sardar Patel Road , <br>Secunderabad <br>Andhra Pradesh <br>500003","folderSeq" => "13","longitude" => "17.668791","latitude" => "80.893593");
        $itc2 = array("owner" => "ITC Bhadrachalam","stationType"=>"AQMS","station"=>"AQMS 2","location"=>"106 Sardar Patel Road , <br>Secunderabad <br>Andhra Pradesh <br>500003","folderSeq" => "14","longitude" => "17.668558","latitude" => "80.899029");
        $itc3 = array("owner" => "ITC Bhadrachalam","stationType"=>"AQMS","station"=>"AQMS 3","location"=>"106 Sardar Patel Road , <br>Secunderabad <br>Andhra Pradesh <br>500003","folderSeq" => "15","longitude" => "17.674242","latitude" => "80.898085");
        $itc4 = array("owner" => "ITC Bhadrachalam","stationType"=>"AQMS","station"=>"AQMS 4","location"=>"106 Sardar Patel Road , <br>Secunderabad <br>Andhra Pradesh <br>500003","folderSeq" => "16","longitude" => "17.667986","latitude" => "80.907269");

        $bspcb1= array("owner" => "BSPCB","stationType"=>"AQMS","station"=>"AQMS 1","location"=>"Patna","folderSeq" => "18","longitude" => "25.61046","latitude" => "85.14167");
        $bspcb2= array("owner" => "BSPCB","stationType"=>"AQMS","station"=>"AQMS 2","location"=>"Gaya","folderSeq" => "24","longitude" => "24.792766","latitude" => "85.007923");
        $bspcb3= array("owner" => "BSPCB","stationType"=>"AQMS","station"=>"AQMS 3","location"=>"Muzaffarpur","folderSeq" => "25","longitude" => "26.122353","latitude" => "85.380528");

        $laxmi1 = array("owner" => "JK Lakshmi","stationType"=>"CEMS","station"=>"CEMS","location"=>"Jhajjar","folderSeq" => "34","longitude" => "28.290000","latitude" => "76.210000");
        $ambuja1 = array("owner" => "Ambuja Cements","stationType"=>"CEMS","station"=>"CEMS","location"=>"Rauri Plant","folderSeq" => "35","longitude" => "31.241819","latitude" => "76.945678");
        $ambuja2 = array("owner" => "Ambuja Cements","stationType"=>"CEMS","station"=>"CEMS","location"=>"Suli Plant","folderSeq" => "36","longitude" => "31.233637","latitude" => "76.948131");

        $lt1 = array("owner" => "Larsen & Turbo","stationType"=>"CEMS","station"=>"CEMS","location"=>"Station1","folderSeq" => "38","longitude" => "30.515917","latitude" => "76.580207");
        $lt2 = array("owner" => "Larsen & Turbo","stationType"=>"CEMS","station"=>"CEMS","location"=>"Station2","folderSeq" => "39","longitude" => "30.503494","latitude" => "76.594627");

        $indogulf1 = array("owner" => "Indogulf","stationType"=>"EFFLUENT","station"=>"EFFLUENT","location"=>"Amethi","folderSeq" => "41","longitude" => "26.525971", "latitude" => "81.669708");

		$clpindia1 = array("owner" => "CLPIndia","stationType"=>"CEMS","station"=>"CEMS","location"=>"CLP India","folderSeq" => "44","longitude" => "28.4995978", "latitude" => "76.382539");
		
		$clpindia2 = array("owner" => "CLPIndia","stationType"=>"EFFLUENT","station"=>"EFFLUENT","location"=>"CLP India","folderSeq" => "46","longitude" => "28.4995978", "latitude" => "76.382539");
        
        $bhoomi_mcl = array("owner" => "M/s Mangalam Cement Ltd.","stationType"=>"CEMS","station"=>"stack","location"=>"Morak, Rajasthan","folderSeq" => "48","longitude" => "24.721167", "latitude" => "75.959222");
        $bhoomi_medicare = array("owner" => "Medicare Enviornmental Management Pvt.Ltd.","stationType"=>"CEMS","station"=>"Incinerator","location"=>"Ludhiana, Punjab","folderSeq" => "49","longitude" => "30.900965", "latitude" => "75.857276");
        $bhoomi_harekrishna = array("owner" => "Shri HareKrishna Sponge Iron Ltd.","stationType"=>"CEMS","station"=>"Sponge Iron Division","location"=>"Raipur, Chattissgarh","folderSeq" => "71","longitude" => "21.3742461", "latitude" => "81.660776");
        
        $stationsMapData =  array(12=>$jsplNorth,21=>$jsplEast,22=>$jsplWest,23=>$jsplSouth, 9=>$acc, 13=>$itc1, 14=>$itc2, 15=>$itc3, 16=>$itc4, 18=>$bspcb1, 24=>$bspcb2, 25=>$bspcb3, 34=>$laxmi1, 35=>$ambuja1,36=>$ambuja2, 38=>$lt1,39=>$lt2,41=>$indogulf1,44=>$clpindia1,46=>$clpindia2,48=>$bhoomi_mcl,49=>$bhoomi_medicare,71=>$bhoomi_harekrishna);
        $stationMapArr = array();
        foreach($folderSeqs as $seq){
            $seq = intval($seq);
            if(array_key_exists($seq,$stationsMapData)){
                $stationMapArr[$seq] = $stationsMapData[$seq];
            }    
        }
        if(count($folderSeqs) > 0){
             $stationsMapData = $stationMapArr;
        }    
        if($isLoggedIn == "1"){
             //$agra = array("owner" => "UPPCB Agra","station"=>"AQMS","location"=>"Agra","folderSeq" => "6","longitude" => "27.1766701","latitude" => "78.0080745");
             //$kanpur= array("owner" => "UPPCB Kanpur","station"=>"AQMS","location"=>"Kanpur","folderSeq" => "7","longitude" => "26.471447","latitude" => "80.323391");
             //$varanasi= array("owner" => "UPPCB Varanasi","station"=>"AQMS","location"=>"Varanasi","folderSeq" => "8","longitude" => "25.347568","latitude" => "82.980927");
             //$lucknow= array("owner" => "UPPCB Lucknow","station"=>"AQMS","location"=>"Lucknow","folderSeq" => "10","longitude" => "26.871339","latitude" => "80.956763");
             //$stationsMapData[6] = $agra;
             //$stationsMapData[7] = $kanpur;
             //$stationsMapData[8] = $varanasi;
             //$stationsMapData[10] = $lucknow;


             //$rkpuram= array("owner" => "DPCC Rk Puram","station"=>"AQMS","location"=>"Rk Puram","folderSeq" => "4","longitude" => "28.562818","latitude" => "77.18612");
             //$punjabibagh= array("owner" => "DPCC Punjabi Bagh","station"=>"AQMS","location"=>"Punjabi Bagh","folderSeq" => "5","longitude" => "28.645059","latitude" => "77.098325");
             //$mandirmarg= array("owner" => "DPCC Mandir Marg","station"=>"AQMS","location"=>"Mandir Marg","folderSeq" => "3","longitude" => "28.637038","latitude" => "77.200981");
             //$anandvihar= array("owner" => "DPCC Anand Vihar","station"=>"AQMS","location"=>"Anand Vihar","folderSeq" => "17","longitude" => "28.647622","latitude" => "77.315793");
             //$stationsMapData[4] = $rkpuram;
             //$stationsMapData[5] = $punjabibagh;
             //$stationsMapData[3] = $mandirmarg;
             //$stationsMapData[17] = $anandvihar;
        }
        return $stationsMapData;
    }

    public function getDynamicData($locSeq){
       $dynamicDataArray = self::getLatestInfo($locSeq);
       $dynamicDataArray = self::processChannelNumbersToParameters($dynamicDataArray,1);
       return $dynamicDataArray;
    }

	public function getMapData($folders){
		$mainArray = array();
		foreach($folders as $folder){
			$array= array("owner" => $folder["locationname"],
						  "stationType"=>$folder["stationtype"],
						  "station"=>$folder["stationtype"],
						  "location"=>$folder["stationname"],
					      "folderSeq" =>$folder["seq"],
					      "longitude" => $folder["longitude"],
					      "latitude" => $folder["latitude"]);
			$mainArray[$folder["seq"]] = $array;
		}
		return $mainArray;
	}

    //Private Functions
    private function getLatestInfo($locSeq){
        $folders = FolderDataStore::getInstance()->FindByLocation($locSeq);
        $WQD = WQDDataDataStore::getInstance();
        $channelInfo = new ArrayObject();
        foreach($folders as $folder){
            $cifo = $WQD->getChannelsLatestInfo($folder->getSeq());
            $cifo['folderSeq'] = $folder->getSeq();
            $cifo['folderName'] = $folder->getFolderName();
            $channelInfo[$folder->getSeq()] = $cifo;
        }
        return $channelInfo;
    }
    private function processChannelNumbersToParameters($dataArray,$isConvertUnits){
        //method will set channelnames and units
        $CCDS = ChannelConfigurationDataStore::getInstance();
        $processedData = new ArrayObject();
        foreach($dataArray as $data){
            $processedDataItem = new ArrayObject();
            $processedDataItem['folderSeq'] = $data['folderSeq'];
            $processedDataItem['folderName'] = $data['folderName'];
            $processedDataItem['dated'] = $data['dated'];

            $channelNameWiseData = new ArrayObject();
            $channelsInfo = $CCDS->FindByFolder($data['folderSeq']);
            $channelsNumberWiseData = $data['channelsInfo'];

            foreach($channelsInfo as $channel){
                $chNo = $channel->getChannelNumber();
                $chName = $channel->getChannelName();
                $chUnit = $channel->getChannelUnit();
                if($isConvertUnits == 1){
                    $chNoData = $channelsNumberWiseData['ch'. $chNo .'value'];
                    $presValue = ConvertorUtils::getPrescribedValue($chName,$chNoData);

                    $chConvertedUnitVal = ConvertorUtils::getPrescribedUnit($chName);
                    if($chConvertedUnitVal == null){
                        $chConvertedUnitVal =  $chUnit;
                    }
                    $presUnit = ConvertorUtils::getUTF8Encoded($chConvertedUnitVal);
                    if($presValue == null){
                        $channelNameWiseData[$chName] = 'no data';
                    }else{
                        $channelNameWiseData[$chName] = $presValue .' '. $presUnit;
                    }

                }else{
                    $unitVal = $unitName[$chName];
                    $channelNameWiseData["ch". $chNo ."unit"] = ConvertorUtils::getUTF8Encoded($unitVal);;
                }
            }
            $processedDataItem['data'] = $channelNameWiseData;
            $processedData[] = $processedDataItem;

        }
        return $processedData;
    }




}


?>
