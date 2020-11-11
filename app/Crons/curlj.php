<?
$url = "https://jsac.jharkhand.gov.in/Pollution/WebService.asmx/GET_PM_DATA?vender_id=10&industry_id=ASIPL&stationId=ASIPL_STACK_1&analyserId=1603003&processValue=38.00&scaledValue=38.00&flag=U&timestamp=2018-03-05:2007:15:01.AM&unit=mg/nm3&parameter=PM10";
    $ch = curl_init();
           
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
 
    //A given cURL operation should only take
    //30 seconds max.
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $result = curl_exec ($ch);
    if ($result === FALSE) {
        echo curl_error($ch) ."<br>";
        curl_close ($ch);
    }else{
        curl_close ($ch);
        echo  "Result: " . $result ."<br>\n";
    }
?>