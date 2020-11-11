<?include("_jsIncludeCpcb.php");?>
<script>
setInterval(function() {
    //$('.Timer').text((new Date - start) / 1000 + " Seconds");
    callActions();
}, 2000);


$(document).ready(function () {
	
	
});
lastSeq = 0;
function callActions(){
	$url = "ajaxCalls.php?action=getCameraActions&fromSeq="+lastSeq;
 	$.getJSON($url, function(data){
        var valArr=[];
 		$.each(data.actions, function(key,val){
               valArr = val;
               var div = document.getElementById('Timer');
               url = "http://117.252.6.41/cgi-bin/ptz.cgi?action=start&channel=0&code="+val.action+"&arg1=0&arg2=1&arg3=0";
               if(lastSeq > 0){
                    window.open(url,"_blank");
               setTimeout(function(){
                             window.open("http://117.252.6.41/cgi-bin/ptz.cgi?action=stop&channel=0&code=Left&arg1=0&arg2=1&arg3=0","_blank");
                          }, 2000);
               
                   //if(val.action == "Stop"){
                    //url = "http://117.252.6.41/cgi-bin/ptz.cgi?action=stop&channel=0&code=Left&arg1=0&arg2=1&arg3=0";
                   //}
               }
               div.innerHTML += val.dated +' <a target="new" href="'+url+'" >'+ val.action + "</a><BR>";
			
 		});
              if(valArr[0] != null){
                lastSeq = valArr.seq;
              }
 	});
}
function stopId(){
   

}

</script>
<html>
	<div id="Timer"></div>
</html>
