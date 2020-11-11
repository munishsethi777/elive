
<script>
    $(function() {
        $("#dialog").dialog({
            autoOpen : false,                     
            modal : true,  
            width:"700px",
            open: function(event, ui) { 
                $(event.target).parent().css('position', 'fixed');
                $(event.target).parent().css('top', '5px');
                $(event.target).parent().css('left', '10px');
                $('#exportParamsUL').html('')
            },
            buttons: [
            {
                text: "Select All",
                click: function () {
                    $('.exportParamsCheck').each(function() {
                        $(this).attr('checked',!$(this).attr('checked'));
                    });
                },
            },{
                text: "Unselect All",
                click: function () {
                    $('#exportParamsUL').find(':checkbox').removeAttr('checked');;
                },
            },{
                text: "Export Data",
                "id": "exportData",
                click: function () {
                    exportDataAction();
                },
            },{
                text: "Cancel",
                click: function () {
                    $("#dialog").dialog("close");
                },
            }],
        });
    });
    function exportDataAction(){
        var fromDate = $("#fromDate")[0].value
        var toDate = $("#toDate")[0].value
        var folSeq = $("#folSeqs")[0].value;
        var interval = $("#interval")[0].value;
        
        var channelVals = [];
        $('#exportParamsUL :checked').each(function() {
            channelVals.push($(this).val());
        });   
        var url = "ajaxCalls.php?action=exportCSV&fromDate="+fromDate;
        url += "&toDate="+toDate +"&folSeq="+folSeq+"&interval="+interval +"&isPLimits=<?echo $isPrescribedLimits?>";
        url += "&channels="+channelVals;
        location.href = url;
    }
    
    
    function exportData(){
        $("#dialog").dialog("open");
        var fromDate = $("#fromDate")[0].value
        var toDate = $("#toDate")[0].value
        $("#fromDateExport").html(fromDate);
        $("#toDateExport").html(toDate);
        $('#channel option').each(function(key){
            var option = this;    
            $("#exportParamsUL").append(
            "<li style='float:left;width:150px;'><input class='exportParamsCheck' type='checkbox' name='exportParamsCheck' value='"+option.value+"'/>"+   option.text  +"</li>");
        });
        
    }
</script>

<div id="dialog" title="Select Parameters to Export Data">
    Export Data between <label id="fromDateExport"></label> and <label id="toDateExport"></label>
    <p>Select Parameters</p>
    <ul id="exportParamsUL" style="list-style: none;margin:15px;">
       
    </ul>
    
</div>
