<?php include("sessioncheckPrivateOnly.php");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body style="margin:0px 0px 0px 0px" class="fullBody">

<? $menuItem = "comments";?>
<?include("cpcbHeader.php");?>
<?include("cpcbMenu.php");?>
<script src="js/comments1.js"></script>
<script>
    $(document).ready(function() {
        $(".btn").button();
        <?if($lsp  == null){?>
            getAllLocationsAndStations();
        <?}else{?>
            getStationsByLocation(<?echo $lsp. ",'ALL'" ?>);
        <?}?>

        loadFromToDatesPicker();
        $('.submitForm').click(function (){
            saveCommentMaster();
        });
        callCommentsMaster();
    });
    function saveCommentMaster(){
        var fromDateStr = $("#fromDate").val();
        var toDateStr = $("#toDate").val();
        if(fromDateStr == ""){
            alert("From Date cannot be null");
            return;
        }
        if(toDateStr == ""){
            alert("To Date cannot be null");
            return;
        }
        var fromDate = new Date(fromDateStr);
        var toDate = new Date(toDateStr);
        if(fromDate >=  toDate){
            alert("To Date Should be greater than From date");
            return;    
        }
        $(".responseDiv").hide();
        $(".fullBody").fadeTo("slow", 0.33);
        var formData = $("#commentsMasterForm").serialize();
        $.getJSON("ajax_cpcb.php?method=saveValidationExemptionRequest",formData,function(data){
            $(".fullBody").fadeTo("fast", 100);
            if(data.RESPONSE == "SUCCESS"){
                $(".responseDiv").show();
                $(".responseDiv").html("Comments Saved Successfully!");
                $(".responseDiv").addClass("ui-state-highlight");

                callCommentsMaster();
            }
        });

    }
    function saveCommentDetails(){
        var comment = $("#newComments").val();
        if(comment == ""){
            alert("Please write some comments");
            return;
        }
        $(".responseDiv").hide();
        $(".fullBody").fadeTo("slow", 0.33);
        var formData = $("#commentsDetailsForm").serialize();
        var formDataArr = $("#commentsDetailsForm").serializeArray();
        comMasterSeq = formDataArr[0].value;

        $.getJSON("ajax_cpcb.php?method=saveCommentsDetails",formData,function(data){
            $(".fullBody").fadeTo("fast", 100);
            if(data.RESPONSE == "SUCCESS"){
                $(".responseDiv").show();
                $(".responseDiv").html("Comments Details Saved Successfully!");
                $(".responseDiv").addClass("ui-state-highlight");
                viewCommentsDetail(comMasterSeq);
            }
        });

    }
    function deleteCommentDetail(commentDetailId,commentsMasterId){
        $.getJSON("ajax_cpcb.php?method=deleteCommentsDetails&seq="+commentDetailId,function(data){
            viewCommentsDetail(commentsMasterId);
        });
    }
    function deleteCommentMaster(id){
        $.getJSON("ajax_cpcb.php?method=deleteCommentsMaster&seq="+id,function(data){
            callCommentsMaster();
        });
    }
    function viewCommentsDetail(id){
        $(".commentsDetails").html("Loading Comments Details...");
        $.getJSON("ajax_cpcb.php?method=requestCommentsDetail&seq="+id,function(data){
            loadCommentsDetails(data,id);
            $('.saveCommentsDetails').click(function (){
                saveCommentDetails();
            });
        });
    }
    function callCommentsMaster(){
        $(".commentsMaster").html("Loading Comments Threads...");
        $.getJSON("ajax_cpcb.php?method=requestCommentsMaster<? echo ("&lsp=". $lsp); ?>",function(data){
            loadCommentsMaster(data);
            $('.deleteCommentMaster').click(function (){
                deleteCommentMaster(this.id);
            });
            $('.viewCommentsDetails').click(function (){
                viewCommentsDetail(this.id);
            });
        });
    }
    function loadCommentsMaster(comments){

        var $str = "<table cellspacing='0' cellpadding='0' class='commentsMasterTable' style='width:100%;border:1px silver solid'><tr>";
        $str += "<th width='12%' class='ui-state-active'>Status</th>";
        $str += "<th width='12%' class='ui-state-active'>Channel</th>";
        $str += "<th width='15%' class='ui-state-active'>Station</th>";
        $str += "<th width='20%' class='ui-state-active'>FromDate</th>";
        $str += "<th width='20%' class='ui-state-active'>ToDate</th>";
        $str += "<th width='5%' class='ui-state-active'>Comments Count</th>";
        $str += "<th width='20%' class='ui-state-active'>LastUpdatedOn</th>";
        $str += "<th width='6%' class='ui-state-active'>Action</th>";
        $str += "</tr>";
        if(comments.length == 0){
            $str += "<tr><td colspan=6>No Items Found</td></tr>"
        }else{
            $.each(comments,function(key,value){
                $str += "<tr id='"+ value.seq +"'>";
                $str += "<td class='comMasterTD'>"+ value.request +"</td>";
                $str += "<td class='comMasterTD'>"+ value.channelName +"</td>";
                $str += "<td class='comMasterTD'>"+ value.folderName +"</td>";
                $str += "<td class='comMasterTD'>"+ value.fromDateRange +"</td>";
                $str += "<td class='comMasterTD'>"+ value.toDateRange +"</td>";
                 $str += "<td class='comMasterTD'>"+ value.commentDetailCount +"</td>";
                $str += "<td class='comMasterTD'>"+ value.lastUpdatedOn +"</td>";
                $str += "<td><img alt='Delete' title='Delete' id='"+value.seq+"' class='deleteCommentMaster' src='images/delete.png'>";
                $str += " </td>";
                $str += "</tr>";
            });
        }
        $str += "</table>";
        $(".commentsMaster").html($str);
        $(".comMasterTD").click(function() {
            $(this).closest("tr").siblings().removeClass("ui-state-highlight");
            $(this).parents("tr").toggleClass("ui-state-highlight", this.clicked);
            viewCommentsDetail($(this).closest("tr")[0].id);
        });

    }
    function loadCommentsDetails(details,commentMasterSeq){

        var $str = "<table id='"+commentMasterSeq+"' cellspacing='0' cellpadding='0' class='commentsMasterTable' style='width:100%;border:1px silver solid'><tr>";
        $str += "<th width='14%' class='ui-state-active'>Dated</th>";
        $str += "<th width='16%' class='ui-state-active'>User</th>";

        $str += "<th width='65%' class='ui-state-active'>Comments</th>";
        $str += "<th width='5%' class='ui-state-active'>Action</th>";
        $str += "</tr>";
        if(details.length == 0){
            $str += "<tr><td colspan=5>No Comments Found</td></tr>"

        }else{
            $.each(details,function(key,value){
                $str += "<tr>";
                $str += "<td>"+ value.dated +"</td>";
                $str += "<td>"+ value.user +"</td>";
                $str += "<td>"+ value.comments +"</td>";
                $str += "<td><img src='images/delete.png' id='"+value.seq+"' class='deleteCommentDetail'></td>";
                $str += "</tr>";
            });
        }
        $str += "<tr bgcolor='#EEE'><td height='40px' colspan=4>";
        $str += "<form name='commentsDetailsForm' id='commentsDetailsForm'>";
        $str += "<input type='hidden' name='commentMasterSeq' id='commentMasterSeq' value='"+commentMasterSeq+"'/>";
        $str += "Your Comments: <input type = 'text' id = 'newComments' name = 'newComments' size='140'>";
        $str += "</form>"
        $str += "</td><td><img src='images/save.png' class='saveCommentsDetails'></td></tr>"
        $str += "</table>";

        $(".commentsDetails").html($str);
        $('.deleteCommentDetail').click(function (){
            $commentMasterId = $(this).closest("table")[0].id;
            deleteCommentDetail(this.id,$commentMasterId);
        });

    }
</script>
<div style="margin:auto;width:1200px;min-height:250px;margin-top:3px;padding:4px;" class="ui-widget-content">
    <div style="border:1px silver solid;padding:10px;">
        <div class="responseDiv" style="padding:10px;display:none"></div>
        <p>Please Select Station, Pollutant, From and To Dates Ranges to validate data or add comments to it.</p>
        <form name="commentsMasterForm" id="commentsMasterForm" method="POST" action="#">
            <table cellspacing='0' cellpadding='0'  class="comentsMasterFormTable" border="0">
                <tr>
                    <th class="ui-state-active">Station</th>
                    <td class="stationsSelectDiv"></td>
                    <th class="ui-state-active">From Date</th>
                    <td><input type="text" size="15" name="fromDate" id="fromDate"></td>
                </tr>
                <tr>
                    <th class="ui-state-active">Pollutant</th>
                    <td class="pollutantsSelectDiv"></td>
                    <th class="ui-state-active">To Date</th>
                    <td><input type="text" size="15" name="toDate" id="toDate"></td>
                </tr>
                <tr>
                    <td align="left" colspan="2">
                        <input name="request" value="validation" type="radio" />Validation
                        <input name="request" value="exemption" type="radio" />Exemption
                        <input name="request" value="record" type="radio" checked/>Record
                    </td>
                    <td align="right" colspan="2">
                        <input type ="button" value="Submit" class="btn submitForm" />
                    </td>
                </tr>

            </table>
        </form>
    </div>
    <p>Select a comments thread and view or add comments to it.</p>
    <div class="commentsMaster" >

    </div>

   <div style="display:block;width:100%;clear:both">
        <div class="commentsDetails" >

        </div>
    </div>
</div>
