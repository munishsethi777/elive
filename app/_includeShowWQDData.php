<?php

      require_once('IConstants.inc');
      require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");
      require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");  
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/LocationDataStore.php");
      require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php"); 
      if(empty($_GET['folSeq'])){
        die;   
      }
      $folderSeq = $_GET['folSeq'];
      $CCDS = ChannelConfigurationDataStore::getInstance();
      $ChannelsInfo = $CCDS->FindByFolder($folderSeq);  
      $totalChannels = count($ChannelsInfo);
      
?>

  <script>
        var totalChannels = <?echo $totalChannels;?>;
         function showChart(chSeq,folderSeq){
             var date = $("#dated").val();
             var isPL = 0;
             if($("#isConvertUnits").is(':checked') == true){
             	isPL = 1;
             }
             window.open("showChart.php?chSeq="+chSeq +"&folderSeq=" + folderSeq + "&fromDate=" + date + "&isPL="+ isPL,'_blank');
         }
         function showTable(chSeq){
             var date = $("#dated").val();
             var isPL = 0;
             if($("#isConvertUnits").is(':checked') == true){
             	isPL = 1;
             }
             window.open("showTable.php?chSeq=" + chSeq +"&fromDate=" + date +"&isPL="+ isPL,'_blank');
         }
         
        getData(<?=$folderSeq?>);
        var auto_refresh = setInterval(
        function()
        {
            getData(<?=$folderSeq?>);
        }, 20000);
        
        function getData(){
            if($("#isConvertUnits").is(':checked') == true){
                getCurrentChannelsInfo(<?=$folderSeq?>,'1');
            }else{
                getCurrentChannelsInfo(<?=$folderSeq?>,'0');
            }
        }
    
    
    </script>
    <style type="text/css">
        .ui-widget-header{
            padding:10px;
            font-size:16px;
            color:navy;
        }
    </style>    

    
        <input type='hidden' id="folderSeq" name="folderseq"/>
        <input type='hidden' id="chNo" name="chNo"/>
        <input type='hidden' id="dated" name="dated"/>
       
        <div style="width:700px;margin-top:5px;">
             <Div style="float:left;font-size:13px;">Live Data from <? echo $folder->getFolderName(); ?> on 
        Dated : <label class="CurrentDateFormated"></label></Div>
            <a style="float:right">
                <input type="checkbox" name="isConvertUnits" id="isConvertUnits" onclick="getData()"/>
                Convert to Prescribed units
            </a>
        </div>
        <table width="700" border="1">
            <tr>
                <td class="ui-state-active">Parameters</td>
                <td class="ui-state-active">Date & Time</td>
                <td class="ui-state-active">Channel Reading</td>
                <td class="ui-state-active">Last 24 Hours Status</td>
            </tr>
<?
    foreach($ChannelsInfo as $channelConfig){
        echo ("<tr>");
        echo ("<td class='ui-widget-content'>". $channelConfig->getChannelName()."</td>");
        echo ("<td class='ui-widget-content CurrentDateFormated'></td>");
        echo ("<td style='text-transform:lowercase' class='ui-widget-content' id='ch". $channelConfig->getChannelNumber() ."cell'>
                <label id='ch". $channelConfig->getChannelNumber() ."value'></label>
                <label id='ch". $channelConfig->getChannelNumber() ."unit'>". $channelConfig->getChannelUnit() ."</label></td>");
        
        echo ("<td class='ui-widget-content'><a href='#' onClick='javascript:showChart(" . '"' . $channelConfig->getSeq() .'",' . $channelConfig->getFolderSeq() . ")'>Show Graph</a> <a href='#' onClick='javascript:showTable(" . $channelConfig->getSeq(). ")'>Show Table</a></td>");
        echo ("</tr>");

}

?>    
       
        </table>
