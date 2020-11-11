<?php 
require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAqLKZlP_X_3oTs4NkLiHfVKr-rh6UZQME&sensor=false"></script>
</head>
<body style="margin:0px 0px 0px 0px">
<?
session_start();
$appMode = $_GET['mode'];
$isLoggedIn = 0;
if($appMode == "login"){
    header("Location:logincpcb.php");
}
$menuItem = "mapMenu";?>
<?include("cpcbHeader.php");?>
<?include("cpcbMenu.php");?>
<style>
    .mapDataTable td, .mapDataInfoTable td{
        font-size:11px;
        border:1px solid grey;
    }
    .mapDataTable .chName{
       background-color:#EEE;
    }

    .mapDataTable .chData{

    }
</style>
<div id="map" style="width:100%;height:600px"></div>
<div class="mapLegends" style="padding:10px;">
    <img src ="images/factory.png" />AQMS
    <img src ="images/factoryB.png" style="margin-left:10px;"/>CEMS
    <img src ="images/factoryG.png" style="margin-left:10px;"/>EFFLUENT
</div>

<script>
    $(document).ready(function() {
        $(".btn").button();
        loadMap();
    });
	var map;
	function loadMap(){
            var latlng = new google.maps.LatLng(21.89, 83.39);
            var options = {
                zoom: 5,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var icn = "images/factory.png";
            map = new google.maps.Map(document.getElementById('map'), options);
            $.getJSON("ajax_cpcb.php?method=requestMapLocationsJSON&isLoggedIn="+<?=$isLoggedIn?>,function(data){
                $.each(data,function(key,value){
                    getCurrentInfo(value,map);
                });
            });

      }
      function getCurrentInfo(json,map){
        $.get("ajax_GetChannelsCurrentInfoFullTable.php?",json,function(str){
                var marker = null;
                marker = getGoogleMarker(map,json['longitude'],json['latitude'],json['owner'] +' : ' +json['location']+' (' +json['stationType'] +')'
                            ,json['stationType']);
                var infowindow = new google.maps.InfoWindow();
                infowindow.setContent(str);

                google.maps.event.addListener(marker, "click", makeInfoWindowEvent(map,marker,infowindow));
        });


      }
      function makeInfoWindowEvent(map,marker,infowindow){
          return function() {
              infowindow.open(map, marker);
              $(".buttonSmall").button();
           };
      }
      function getGoogleMarker(map,long,lat,name,station){
        var icn = "images/factory.png";
        if(station == "CEMS" || station == "stack"){
            icn = "images/factoryB.png";
        }else if(station == "effluent"){
            icn = "images/factoryG.png";
        }
        var marker_Raigarh =null;
        marker_Raigarh = new google.maps.Marker({
            position: new google.maps.LatLng(lat,long),
            map: map,
            title: name,
            clickable: true,
            icon: icn
        });

        return marker_Raigarh;
    }
</script>