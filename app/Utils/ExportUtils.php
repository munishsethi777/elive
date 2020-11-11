<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "/Plugin/PHPExcel.php");
    require_once($ConstantsArray['dbServerUrl'] . "/Plugin/PHPExcel/IOFactory.php");
    require_once($ConstantsArray['dbServerUrl'] . "/Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] . "/BusinessObjects/ChannelConfiguration.php");
Class ExportUtils{
    private static $isGangaBasedIndustries = array(0=>45,1=>46,2=>47,3=>35,4=>34);
    private static function num_to_letter($num, $uppercase = TRUE){
        $num -= 1;
        $letter =     chr(($num % 26) + 97);
        $letter .=     (floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
        return         ($uppercase ? strtoupper($letter) : $letter); 
    }
    private static function getChannelName($channelsInfo,$chNo){
        foreach($channelsInfo as $channel){
            $c = new ChannelConfiguration();
            $c = $channel;
            if($c->getChannelNumber() == $chNo){
                return $c->getChannelName();
            }
        }
    }
    
    public static function exportStationGridReport($gridJsonArr, $folderName){
       $objPHPExcel = new PHPExcel();
       $rowNo = 1;
       $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, 'Date & Time');

       $channelsArr = $gridJsonArr['channels'];
       $alphabetInt = 0;
       foreach ($channelsArr as $key=>$value){
            $alphabet = self::num_to_letter($alphabetInt+2);
            $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $key);
            $alphabetInt++;
       }
       
       $rowNo++;
       $rows = $gridJsonArr['data'];
       foreach($rows as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, $item['dated']);
            $valArray = $item['values'];
            $alphabetInt = 0;
            foreach ($valArray as $val) {
                $alphabet = self::num_to_letter($alphabetInt+2);
                $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $val);
                $alphabetInt++;
            }
            $rowNo++;
       }
       $dateTime = new DateTime();
       $fileName = "CSVFiles/". $folderName ."_" . $dateTime->format("Y-m-d_H-i-s") . ".csv" ;
       $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV'); 
       $path = $ConstantsArray['dbServerUrl'] . $fileName;          
       $objWriter->save($path);
        if(file_exists($path)){
          header("Content-Type: application/download",false);
          header ("Content-Disposition: attachment; filename=" . $fileName,false);
          header("Content-Length: " . filesize($fileName));   
          $fp = fopen($path,"r");
          fpassthru($fp);
        } 
    }
    
    public static function exportAllTypeStatusReport($folders,$isEmail = false){
    	$sheet = 0;		PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
    	$objPHPExcel = new PHPExcel();		
   		foreach ($folders as $reportType=>$statesAndstations){ 
   			if($sheet > 0){
   				$objPHPExcel->createSheet();
   			}
		    $objPHPExcel->setActiveSheetIndex($sheet);
		    $objPHPExcel->getActiveSheet()->setTitle($reportType);
		    $col = 0;
	    	$row = 1;
	    	$title = $reportType ." Industry List";
// 	    	if($reportType == "Offline"){
// 	    		$objPHPExcel = ExportUtils::exportOfflineStationsStatusReport($objPHPExcel, $statesAndstations, $channels);
// 	    		$sheet++;
// 	    		continue;
// 	    	}
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row, $title);
	    	$s = $objPHPExcel->getActiveSheet()->getStyle("A1");
	    	$s->getFont()->setBold(true);
	   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,3, "SNo.");    				
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);	
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,3, "Industry Code");
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,3, "CompanyName");			
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,3, "Address");			
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,3, "State");			
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,3, "Industry_Type");			
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);				
	    	
	    	$styleArray = array(
	    			'font'  => array(
	    					'bold' => true,
	    					'color' => array('rgb' => '#FFFFFF'),
	    					'size'  => 12,
	    					'name' => 'Verdana'
	    			));
	    	$objPHPExcel->getActiveSheet()->getStyle('A3:F3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F81BD');
	    	
	    	$row = 5;
	    	$locationArr = array();
	    	$count = 0;
	    	foreach ($statesAndstations as $stations){
	    		$slNo = 1;
	    		foreach($stations as $station){
		    		$locationSeq = $station->getLocationSeq();    		
		    		if(!in_array($locationSeq, $locationArr)){
		    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row, $slNo);
		    			$state = $station->getState();
		    			$category = $station->getCategory();
		    			$industry = $station->getIndustryName();
		    			$addresss = $station->getAddress();
		    			$industryCode = $station->getIndustryCode();
		    			if(empty($industryCode)){
		    				$industryCode = "n.a";
		    			}
		    			$lastSynchedDate = $station->getLastSynchedOn();
	    				$lastSynchedDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastSynchedDate);
	    				$lastSynchedDate = $lastSynchedDate->format("d/m/Y H:i:s A"); 
	    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row, $industryCode);
	    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row, $industry);
	    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row, $addresss);
		    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row, $state);
		    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$row, $category);
		    			array_push($locationArr, $station->getLocationSeq());
		    			$row++;
		    			$slNo++;
		    			$count++;
		    		}
	    		}
	    		$row++;
	    	}
	    	$sheet++;
   		}
   		if($isEmail){
   			ob_start();
   			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   			$objWriter->save('php://output');
   			$excelOutput = ob_get_contents();
   			ob_end_clean();
   			return $excelOutput;
   		}
   		$fileName = "IndustryStatus.xls";
   		header('Content-Type: application/download');
   		header('Content-Disposition: attachment;filename="'.$fileName.'"');
   		header('Cache-Control: max-age=0');
   		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   		$objWriter->save('php://output');
    }
    
    
    
    public static function exportStationsStatusReport($statesAndstations,$reportType,$isEmail = false){
    	$objPHPExcel = new PHPExcel();    	
    	$dateTime = new DateTime();
    	$col = 0;
    	$row = 1;
    	$content = "Live";
    	if($reportType == "delay"){
    		$content = "Delay";
    	}
    	$title = "List of Industries found " . $content .", submitting data to CPCB Portal as on " . $dateTime->format("d/m/Y H:i:s A");
    	
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row, $title);
    	$s = $objPHPExcel->getActiveSheet()->getStyle("A1");
    	$s->getFont()->setBold(true);
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,3, "Sl. No.");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,3, "State");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,3, "Category as per CPCB");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,3, "Name of the Industry"); 
    	if($reportType == "delay"){
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,3, "Last Fetched Date");
    	}
    	$row = 4;
    	$locationArr = array();
    	$count = 0;
    	foreach ($statesAndstations as $stations){
    		$slNo = 1;
    		foreach($stations as $station){
	    		$locationSeq = $station->getLocationSeq();    		
	    		if(!in_array($locationSeq, $locationArr)){
	    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row, $slNo);
	    			//$station = new Folder();
	    			$state = $station->getState();
	    			$category = $station->getCategory();
	    			$industry = $station->getIndustryName();
	    			$lastSynchedDate = $station->getLastSynchedOn();
    				$lastSynchedDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastSynchedDate);
    				$lastSynchedDate = $lastSynchedDate->format("d/m/Y H:i:s A"); 
	    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row, $state);
	    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row, $category);
	    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row, $industry);
	    			if($reportType == "delay"){
	    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row, $lastSynchedDate);
	    			}
	    			array_push($locationArr, $station->getLocationSeq());
	    			$row++;
	    			$slNo++;
	    			$count++;
	    		}
    		}
    		$row++;
    	}    	
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row, "Total = " . $count);
    	if($isEmail){
    		ob_start();    		
    		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    		$objWriter->save('php://output');
    		$excelOutput = ob_get_contents();
    		ob_end_clean();
    		return $excelOutput;
    	}
    	$fileName = "IndustryStatus.xls";
    	header('Content-Type: application/download');
    	header('Content-Disposition: attachment;filename="'.$fileName.'"');
    	header('Cache-Control: max-age=0');
    	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    	$objWriter->save('php://output');
    }
    
//     public static function exportOfflineStationsStatusReport($statesAndstations,$channels,$isEmail = false){
//     	$objPHPExcel = new PHPExcel();
//     	$dateTime = new DateTime();
//     	$col = 0;
//     	$row = 1;
//     	$title = "List of Industries found Offline, submitting data to CPCB Portal as on " . $dateTime->format("d/m/Y H:i:s A");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row, $title);
//     	$s = $objPHPExcel->getActiveSheet()->getStyle("A1");
//     	$s->getFont()->setBold(true);
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,3, "SNo.");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,3, "State");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,3, "Category as per CPCB");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,3, "Company Name");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,3, "Station Name");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,3, "Parameter Name");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,3, "Last data Fetched Date and time");
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,3, "Data is not Received Since");
    	
    	
//     	$row = 4;
//     	$locationArr = array();
//     	$count = 0;
//     	foreach ($statesAndstations as $stations){
//     		$slNo = 1;
//     		foreach($stations as $station){
//     			$locationSeq = $station->getLocationSeq();
//     			if(!in_array($locationSeq, $locationArr)){
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row, $slNo);
//     				//$station = new Folder();
//     				$seq = $station->getSeq();
//     				$state = $station->getState();
//     				$category = $station->getCategory();
//     				$industry = $station->getIndustryName();
//     				$lastSynchedDate = $station->getLastSynchedOn();
//     				$lastSynchedDateStr = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastSynchedDate);
//     				$lastSynchedDateStr = $lastSynchedDateStr->format("d/m/Y H:i:s A"); 
//     				$stationName = $station->getStationName();
//     				$date = new DateTime($lastSynchedDate);
//     				$offlineSince = DateUtils::getTimeDiffTillNow($date);
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row, $state);
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row, $category);
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row, $industry);    				
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row, $stationName);
//     				$parameters = $channels[$seq];
//     				if(!empty($parameters)){
//     					$parameters = implode(", ",$parameters);
//     					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$row, $parameters);
//     				}
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$row, $lastSynchedDateStr);
//     				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$row, $offlineSince);
//     				array_push($locationArr, $station->getLocationSeq());
//     				$row++;
//     				$slNo++;
//     				$count++;
//     			}
//     		}
//     		$row++;
//     	}
//     	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row, "Total = " . $count);
//     	if($isEmail){
//     		ob_start();
//     		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//     		$objWriter->save('php://output');
//     		$excelOutput = ob_get_contents();
//     		ob_end_clean();
//     		return $excelOutput;
//     	}
//     		$fileName = "IndustryStatus.xls";
//     		header('Content-Type: application/download');
//     		header('Content-Disposition: attachment;filename="'.$fileName.'"');
//     		header('Cache-Control: max-age=0');
//     		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//     		$objWriter->save('php://output');
    	
    	
//     }
    private static $vendorShiftingLocations = array(0=>5,1=>13);
    public static function getOfflineStationsStatusReport($statesAndstations,$channels,$isBackupReport){
    	$objPHPExcel = new PHPExcel();
    	$dateTime = new DateTime();
    	$col = 0;
    	$row = 1;
    	$from = (new \DateTime())->modify("-1 months")->format("jS F Y");
       
    	$to = $dateTime->format("jS F Y ");
    	//$title = "List of Industries found Offline, submitting data to CPCB Portal as on " . $dateTime->format("S F Y ");
    	$portalName = "Name of Portal Envirotechlive";
    	$title = "List of Industries Not Submitting Data from ".$from." to ".$to;
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row, $portalName);
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,2, $title);
    	
    	$s = $objPHPExcel->getActiveSheet()->getStyle("A1");
    	$s->getFont()->setBold(true);
    	$lastDateTitle = "Last Data Fetched Date and Time";
    	if($isBackupReport){
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,3, "1");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,3, "2");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,3, "3");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,3, "4");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,3, "5");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,3, "6");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,3, "7");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,3, "8");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,3, "9");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,3, "10");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,3, "11");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11,3, "12");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12,3, "13");
	    	$lastDateTitle = "Last Data Received (Time Stamp)";
    	}
    	
    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setAutoSize(true);
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,4, "Sl No.");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,4, "Category");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,4, "Industry Code");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,4, "Name of Industry");    	
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,4,"Address");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,4,"Contact No (In which SMSAlerts has been configured)");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,4, "State");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,4, "Name of Station Name*");    	 
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,4, "Parameter Name**");
    	
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,4, $lastDateTitle);
    	$currDate = new DateTime();
    	$currDate = $currDate->format("jS M Y");
    	$t = "No of days w.r.t. ". $currDate ." when data is not submitted by the industry to be provided in Descending order";
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,4, $t);
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11,4, "In Ganga Industry");
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12,4, "Remarks");
    	$objPHPExcel->getActiveSheet()->getStyle('A4:M4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F81BD');
    	$whiteStyle['white_text'] = array(
    			'name' => 'Arial',
    			'color' => array(
    					'rgb' => 'FFFFFF'
    			)
    	);
    	$objPHPExcel->getActiveSheet()->getStyle('A4:M4')->getFont()->applyFromArray($whiteStyle['white_text']);
    	$row = 5;
    	$locationArr = array();
    	$count = 0;
    	foreach ($statesAndstations as $stations){
    		$slNo = 1;
    		foreach($stations as $station){
    			$locationSeq = $station->getLocationSeq();
    			if(!in_array($locationSeq, $locationArr)){
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row, $slNo);
    				//$station = new Folder();
    				$seq = $station->getSeq();
    				$state = $station->getState();
    				$industryCode = $station->getIndustryCode();
    				$category = $station->getCategory();
    				$addresss = $station->getAddress();
    				$addresss = html_entity_decode($addresss);
    				$industry = $station->getIndustryName();
    				$lastSynchedDate = $station->getLastSynchedOn();
    				$lastSynchedDateStr = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $lastSynchedDate);
    				$lastSynchedDateStr = $lastSynchedDateStr->format("d-M-Y H:i");
    				$stationName = $station->getStationName();
    				$mobile = $station->getMobile();
    				$date = new DateTime($lastSynchedDate);
    				$offlineSince = DateUtils::getDaysDiffTillNow($date);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row, $category);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row, $industryCode);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row, $industry);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row, $addresss);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$row, $mobile);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$row, $state);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$row, $stationName);
    				
    				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$row, $stationName);
    				$parameters = $channels[$seq];
    				if(!empty($parameters)){
    					$parameters = implode(", ",$parameters);
    					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$row, $parameters);
    				}
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9,$row, $lastSynchedDateStr);
    			//	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$row, $lastSynchedDateStr);
    			 	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10,$row, $offlineSince);
    			 	
    			 	if(in_array($locationSeq, self::$isGangaBasedIndustries)){
    			 		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11,$row, "Yes");
    			 	}else{
    			 	    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11,$row, "No");
    			 	}
    			 	if(in_array($locationSeq, self::$vendorShiftingLocations)){
    			 		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12,$row, "Vendor Shifting");
    			 		$style['red_text'] = array(
    			 				'name' => 'Arial',
    			 				'color' => array(
    			 						'rgb' => 'FF0000'
    			 				)
    			 		);
    			 		$objPHPExcel->getActiveSheet()
    			 		->getStyleByColumnAndRow(12, $row)
    			 		->getFont()
    			 		->applyFromArray($style['red_text']);
    			 	}
    				array_push($locationArr, $station->getLocationSeq());
    				$row++;
    				$slNo++;
    				$count++;
    			}
    		}
    		$row++;
    	 }
    	
    		ob_start();
    		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    		$objWriter->save('php://output');
    		$excelOutput = ob_get_contents();
    		ob_end_clean();
    		return $excelOutput;
    }
    
    public static function exportMultiStationGridReport($gridJsonArr,$exportType){
       $objPHPExcel = new PHPExcel();
       
       $channelsArr = $gridJsonArr['channels'];
       $col = 0;
       $row = 1; // ROWS starts from 1
       $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row, "Dated");
       $col++;
       //set headers here
       try{
           foreach ($channelsArr as $ch){
                $thVal = str_replace("<br>"," ",$ch);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$thVal);
                $col++;
           }
       }catch(Exception $e){
            $e; 
       }
       //set data here
       $rows = $gridJsonArr['data'];
       $row = 2; 
       try{
           foreach($rows as $key=>$value) {
                $col = 0;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$key);
                foreach ($value as $val) {
                    $col++;
                    if($val != "" && $val != "n.o" && $val != "n.a"){
                        try{
                            $val = number_format($val,2);
                        }catch(Exception $e){}
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$val);
                }
                $row++;
           }
       }catch(Exception $e){
           $e;
       }
       $dateTime = new DateTime();
       if($exportType == "pdf"){
           $objPHPExcel = self::setPDFStyle($objPHPExcel);
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".pdf" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
           $objWriter->save('php://output');
           
       }else if($exportType == "csv"){
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".csv" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
           $objWriter->save('php://output');
       
       }else if($exportType == "html"){
           $objPHPExcel = self::setHTMLStyle($objPHPExcel);
           $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".html" ;
           header('Content-Type: application/download');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
           $objWriter->save('php://output');
       
       }else if($exportType == "xls"){
           //$objPHPExcel = self::setHTMLStyle($objPHPExcel);
//            $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".xls" ;
//            header('Content-Type: application/download');
//            header('Content-Disposition: attachment;filename="'.$fileName.'"');
//            header('Cache-Control: max-age=0');
//            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//            $objWriter->save('php://output');
       	   $fileName = "MultiStation_" . $dateTime->format("Y-m-d_H-i-s") . ".xls" ;
           header('Content-Type: application/vnd.ms-excel');
           header('Content-Disposition: attachment;filename="'.$fileName.'"');
           header('Cache-Control: max-age=0');
           // If you're serving to IE 9, then the following may be needed
           header('Cache-Control: max-age=1');
           
           // If you're serving to IE over SSL, then the following may be needed
           header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
           header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
           header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
           header ('Pragma: public'); // HTTP/1.0
           
           $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
           ob_end_clean();
           $objWriter->save('php://output');
       }   
       
    }
    private static function setPDFStyle(PHPExcel $objPHPExcel){
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setScale(50);
        
        $highCol = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $highRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        //set header back color
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->getStartColor()->setRGB('0099FF');
        //set dates back color
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->getStartColor()->setRGB('F0F0F0 ');
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.$highRow)->getFont()->setSize(7);
        $objPHPExcel->getActiveSheet()->setShowGridlines(false);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_NONE);
        return $objPHPExcel;        
    }
    private static function setHTMLStyle(PHPExcel $objPHPExcel){
        //$sharedStyle1 = new PHPExcel_Style();
//        $default_border = array(
//            'style' => PHPExcel_Style_Border::BORDER_THIN,
//            'color' => array('rgb'=>'1006A3')
//        );
//        $style_header = array(
//            'borders' => array(
//                'bottom' => $default_border,
//                'left' => $default_border,
//                'top' => $default_border,
//                'right' => $default_border,
//            ),
//            'fill' => array(
//                'type' => PHPExcel_Style_Fill::FILL_SOLID,
//                'color' => array('rgb'=>'E1E0F7'),
//            ),
//            'font' => array(
//                'bold' => true,
//            )
//        );
        //header decorations;
        $highCol = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $highRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFill()->getStartColor()->setRGB('0099FF');
        //set dates back color
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
        $objPHPExcel->getActiveSheet()->getStyle('A2:A'.$highRow)->getFill()->getStartColor()->setRGB('F0F0F0 ');
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getFont()->getColor()->setRGB('FFFFFF');
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$highCol.'1')->getAlignment()->setWrapText(true);
        //cells formatting;
        $styleArray = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );
        for($i=2;$i<=$highRow;$i++){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':'.$highCol.$i)->applyFromArray($styleArray);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(17);
        return $objPHPExcel;

    }
    public static function exportCSV($rows,$channelNos,$folderName,$isPLimits,$ChannelsInfo){
        $channelNosArr = explode(",", $channelNos);
        $objPHPExcel = new PHPExcel();
        $rowNo = 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, 'Date & Time');
        for ($i = 0, $l = count($channelNosArr); $i < $l; ++$i) {
            $alphabet = self::num_to_letter($i+2);
            $channelName = self::getChannelName($ChannelsInfo,$channelNosArr[$i]);
            $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $channelName);
        }
        
                                      
        $rowNo++;
        foreach($rows as $item) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowNo, $item[0]);
            for ($i = 0, $l = count($channelNosArr); $i < $l; ++$i) {
                $alphabet = self::num_to_letter($i+2);
                $chValue =  $item['ch'.$channelNosArr[$i].'value'];
                $channelName = self::getChannelName($ChannelsInfo,$channelNosArr[$i]);
                if($isPLimits == 1){
                    $chValue = ConvertorUtils::getPrescribedValue($channelName, $chValue);
                    $unit = ConvertorUtils::getPrescribedUnit($channelName);
                }
                
                $objPHPExcel->getActiveSheet()->setCellValue($alphabet.$rowNo, $chValue);
            }
            $rowNo++;
       }
       $dateTime = new DateTime();
       $fileName = "CSVFiles/". $folderName ."_" . $dateTime->format("Y-m-d_H-i-s") . ".csv" ;
       $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV'); 
       $path = $ConstantsArray['dbServerUrl'] . $fileName;          
       $objWriter->save($path);
       
       
        if(file_exists($path)){
          header("Content-Type: application/download",false);
          header ("Content-Disposition: attachment; filename=" . $fileName,false);
          header("Content-Length: " . filesize($fileName));   
          $fp = fopen($path,"r");
          fpassthru($fp);
        }
    }
    
public static function exportCPCBReport($rows,$from,$to,$isSendEmail = false){
    	$objPHPExcel = new PHPExcel();

// Set document properties
        $objPHPExcel->getProperties()->setCreator("Manger")
                                     ->setLastModifiedBy("Manger")
                                     ->setTitle("Office 2007 XLSX Test Document")
                                     ->setSubject("Office 2007 XLSX Test Document")
                                     ->setDescription("High Value Logs")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("High Value Logs");


                                     
       
            $alphas = range('A', 'Z');
            $rowCount = 1;
            $count = 0;
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1", "SMS Report From " . $from . " to " . $to);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2", "S.No");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B2", "Category");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C2", "Industry Code");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D2", "Industry Name");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E2", "Full Address");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F2", "State");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G2", "Station Name");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H2", "Parameter Standard limit's");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I2", "Parameters");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J2", "Exceedence");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("K2", "Total SMS");
            $count = 3;
            foreach($rows as $row){
                $i = 0;  
                $colName = $alphas[$i]. $count;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $rowCount);
                $i++;
                foreach($row as $col=>$value){                       
                   //  if($count < 3){
                  //       $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alphas[$i] . $count, $col);
                 //       $count++;
                //  }
                    $colName = $alphas[$i]. $count;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $value);
                    $i++; 
                }
                $count++;
                $rowCount++;
            }



        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('High Value Logs');
        $objPHPExcel->getActiveSheet()->mergeCells("A1:E1");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        if($isSendEmail){
        	ob_start();
        	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        	$objWriter->save('php://output');
        	$excelOutput = ob_get_contents();
        	ob_end_clean();
        	return $excelOutput;
        }else{
        	// Redirect output to a client’s web browser (Excel5)
        	header('Content-Type: application/vnd.ms-excel');
        	header('Content-Disposition: attachment;filename="HighValueLogs.xls"');
        	header('Cache-Control: max-age=0');
        	// If you're serving to IE 9, then the following may be needed
        	header('Cache-Control: max-age=1');
        	
        	// If you're serving to IE over SSL, then the following may be needed
        	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        	header ('Pragma: public'); // HTTP/1.0
        	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        	ob_end_clean();
        	$objWriter->save('php://output');
        	
        }
         
    }
    public static function exportMonthlyCPCBReport($rows,$from,$to,$isSendEmail = false){
    	$objPHPExcel = new PHPExcel();
    
    	// Set document properties
    	$objPHPExcel->getProperties()->setCreator("Manger")
    	->setLastModifiedBy("Manger")
    	->setTitle("Office 2007 XLSX Test Document")
    	->setSubject("Office 2007 XLSX Test Document")
    	->setDescription("High Value Logs")
    	->setKeywords("office 2007 openxml php")
    	->setCategory("High Value Logs");
    
    
    	 
    	 
    	$alphas = range('A', 'Z');
    	$rowCount = 1;
    	$count = 0;
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1", "SMS Report From " . $from . " to " . $to);
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2", "S.No");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C2", "Category");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B2", "Industry Code");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D2", "Industry Name");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E2", "Full Address");    	
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F2", "Contact (In Which SMSAlerts generated)");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G2", "State");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H2", "Station Name");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I2", "Parameter Standard limit's");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J2", "Parameters");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K2", "Exceedence");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L2", "Total SMS");
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M2", "In Ganga Industry");
    	$count = 3;
    	foreach($rows as $row){
    		$i = 0;
    		$colName = $alphas[$i]. $count;
    		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $rowCount);
    		$i++;
    		foreach($row as $col=>$value){
    			//  if($count < 3){
    			//       $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alphas[$i] . $count, $col);
    			//       $count++;
    			//  }
    			$colName = $alphas[$i]. $count;
    			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $value);
    			$i++;
    		}
    		$count++;
    		$rowCount++;
    	}
    
    
    
    	// Rename worksheet
    	$objPHPExcel->getActiveSheet()->setTitle('High Value Logs');
    	$objPHPExcel->getActiveSheet()->mergeCells("A1:E1");
    
    	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    	$objPHPExcel->setActiveSheetIndex(0);
    	if($isSendEmail){
    		ob_start();
    		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    		$objWriter->save('php://output');
    		$excelOutput = ob_get_contents();
    		ob_end_clean();
    		return $excelOutput;
    	}else{
    		// Redirect output to a client’s web browser (Excel5)
    		header('Content-Type: application/vnd.ms-excel');
    		header('Content-Disposition: attachment;filename="HighValueLogs.xls"');
    		header('Cache-Control: max-age=0');
    		// If you're serving to IE 9, then the following may be needed
    		header('Cache-Control: max-age=1');
    		 
    		// If you're serving to IE over SSL, then the following may be needed
    		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    		header ('Pragma: public'); // HTTP/1.0
    		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    		ob_end_clean();
    		$objWriter->save('php://output');
    		 
    	}
    	 
    }
    public static function ExportData($rows){
        $objPHPExcel = new PHPExcel();

// Set document properties
        $objPHPExcel->getProperties()->setCreator("Manger")
                                     ->setLastModifiedBy("Manger")
                                     ->setTitle("Office 2007 XLSX Test Document")
                                     ->setSubject("Office 2007 XLSX Test Document")
                                     ->setDescription("High Value Logs")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("High Value Logs");


                                     
       
            $alphas = range('A', 'Z');
            $rowCount = 1;
            $count = 0;
            foreach($rows as $row){
                $i = 0;       
                foreach($row as $col=>$value){                       
                    if($count < 3){
                        $count = 1;
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alphas[$i] . $count, $col);
                        $count++;
                    }
                    $colName = $alphas[$i]. $count;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $value);
                    $i++; 
                }
                $count++;
                $rowCount++;
            }



        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('High Value Logs');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="HighValueLogs.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');  
    }
    
    public static function ExportCalibrationDetail($dataAndChannels,$exportDetail){
    	$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Admin")
		->setLastModifiedBy("Admin")
		->setTitle("CalibrationReport")
		->setSubject("CalibrationReport")
		->setDescription("CalibrationReport")
		->setKeywords("office 2007 openxml php")
		->setCategory("Report");
		$alphas = range('A', 'Z');
		$rowCount = 1;
		$count = 1;
		$i = 0;
    	$channels = $dataAndChannels["channels"];
    	$data = $dataAndChannels["data"];
    	$colName = $alphas[$i]. $count;
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $exportDetail);
    	$count++;
    	$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($alphas[$i])->setAutoSize(true);
    	$colName = $alphas[$i++]. $count;
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, "Date");
    	
    	foreach($channels as $channel){
    		if(strtolower($channel) == "sox" || strtolower($channel) == "nox"){
    			$colName = $alphas[$i++]. $count;
    			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $channel);
    			$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($alphas[$i])->setAutoSize(true);
    			$objPHPExcel->setActiveSheetIndex(0)->getStyle($colName)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
				);
    		}
    	}
    	$count++;
    	foreach($data as $key=>$chData){
    		$i = 0;
    		$colName = $alphas[$i++]. $count;
    		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $key);
    		foreach($channels as $chNo=>$channel){
    			if(strtolower($channel) == "sox" || strtolower($channel) == "nox"){
    				$colName = $alphas[$i++]. $count;
    				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colName, $chData[$chNo]);
    				$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($alphas[$i])->setAutoSize(true);
    			}
    		}
    		$count++;
    	}
    	// Rename worksheet
    	$objPHPExcel->getActiveSheet()->setTitle('CalibrationDetail');
    	$objPHPExcel->getActiveSheet()->mergeCells("A1:C1");
    	$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2:C2')->getFont()->setBold(true);
    	$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2:C2')
    	->getFill()
    	->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    	->getStartColor()
    	->setRGB('D3D3D3');
    
    	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    	$objPHPExcel->setActiveSheetIndex(0);
			
			
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="CalibrationDetail"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
			
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		$objWriter->save('php://output');
    } 
}
?>
