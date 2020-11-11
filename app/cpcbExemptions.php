<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"

      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body style="margin:0px 0px 0px 0px" class="fullBody">
<? $menuItem = "exemption";?>
<?php include("sessioncheckPrivateOnly.php");?>
<?include("cpcbHeader.php");?>
<?include("cpcbMenu.php");?>

<script src="js/comments1.js"></script>
<script>
    var isLoadPollutatntsInSelect = false;
    $(document).ready(function() {
        $("#tabs").tabs();

        $(".btn").button();
        getAllLocationsAndStations();
        loadFromToDatesPicker();
        $('.submitForm').click(function (){
            saveExemption();
        });
        callAllExemptions(1);
        callAllExemptions(0);
    });
    function saveExemption(){
        $(".responseDiv").hide();
        $(".fullBody").fadeTo("slow", 0.33);
        var formData = $("#commentsMasterForm").serialize();
        $.getJSON("ajax_cpcb.php?method=saveExemption",formData,function(data){
            $(".fullBody").fadeTo("fast", 100);
            if(data.RESPONSE == "SUCCESS"){
                $(".responseDiv").show();
                $(".responseDiv").html("Exemption Saved Successfully!");
                $(".responseDiv").addClass("ui-state-highlight");
                if($('#isExemption').is(':checked')){
                    callAllExemptions(1);
                }else{
                    callAllExemptions(0);
                }

                $(".commentsDetails").html("");
            }
        });
    }
    function callAllExemptions(isExemption){
        if(isExemption == 1){
            $(".exemptionsDiv").html("Loading Exemption Requests...");
        }else{
           $(".exemptionsCommentsDiv").html("Loading Exemption Requests...");
        }
        $.getJSON("ajax_cpcb.php?method=requestExemptions&isExemption="+isExemption,function(data){
            loadExemptionsData(data,isExemption);
            $('.deleteExemption').click(function (){
                deleteExemptions(this.id);
            });
            $('.approveExemption').click(function (){
                approveExemptions(this.id,1);
            });
            $('.disapproveExemption').click(function (){
                approveExemptions(this.id,0);
            });
            $('.viewExemptionComments').click(function (){
                callComments(this.id);
            });
        });
    }
    function approveExemptions(id,flag){
        $.getJSON("ajax_cpcb.php?method=approveExemption&seq="+id+"&flag="+flag,function(data){
            callAllExemptions(1);
        });
    }
    function deleteExemptions(id){
        $.getJSON("ajax_cpcb.php?method=deleteExemption&seq="+id,function(data){
            callAllExemptions(1);
            callAllExemptions(0);
        });
    }
    function loadExemptionsData(exemptions,isExemption){
        var $str = "<table cellspacing='0' cellpadding='0' class='commentsMasterTable' style='width:100%;border:1px silver solid;'><tr>";
        $str += "<th width='12%' class='ui-state-active'>Dated</th>";
        $str += "<th width='12%' class='ui-state-active'>Station</th>";
        $str += "<th width='18%' class='ui-state-active'>Channels</th>";
        $str += "<th width='10%' class='ui-state-active'>FromDate</th>";
        $str += "<th width='10%' class='ui-state-active'>ToDate</th>";
        if(isExemption == 1){
            $str += "<th width='4%' class='ui-state-active'>Approved</th>";
        }
        $str += "<th width='25%' class='ui-state-active'>Comments</th>";
        $str += "<th width='5%' class='ui-state-active'>Action</th>";
        $str += "</tr>";
        if(exemptions.length == 0){
            $str += "<tr><td colspan=6>No Items Found</td></tr>"
        }else{
            $.each(exemptions,function(key,value){
                if(value.isApproved == "pending"){
                   $str += "<tr class='boldRow' id='"+ value.seq +"'>";
                }else{
                    $str += "<tr id='"+ value.seq +"'>";
                }

                $str += "<td class='exemptionTD'>"+ value.dated +"</td>";
                $str += "<td class='exemptionTD'>"+ value.locationName +" : "+value.folderName +"</td>";
                $str += "<td class='exemptionTD'>"+ value.channels +"</td>";
                $str += "<td class='exemptionTD'>"+ value.fromDate +"</td>";
                $str += "<td class='exemptionTD'>"+ value.toDate +"</td>";
                if(isExemption == 1){
                    $str += "<td class='exemptionTD'>"+ value.isApproved +"</td>";
                }
                $str += "<td class='exemptionTD'>"+ value.comments +"</td>";
                $str += "<td>";
                <? if(($userLogged->getUserName() != "cpcb")){?>
                    $str +="<img alt='Delete' title='Delete' id='"+value.seq+"' class='deleteExemption' src='images/delete.png'>";
                <?}else{?>
                    if(isExemption == 1){
                        $str +="<a title='Approve' alt='Approve' class='approveExemption' id='"+value.seq+"'><img style='padding-top:3px;' src='images/tick.png'/></a>  &nbsp;&nbsp;";
                        $str +=" <a title='Disapprove' alt='Disapprove' class='disapproveExemption' id='"+value.seq+"'><img src='images/delete.png'/></a>";
                    }
                <?}?>
                $str += " </td>";
                $str += "</tr>";
            });
        }
        $str += "</table>";
        if(isExemption == 1){
            $(".exemptionsDiv").html($str);
        }else{
            $(".exemptionsCommentsDiv").html($str);
        }
        $(".exemptionTD").click(function() {
            $(this).closest("tr").siblings().removeClass("ui-state-highlight");
            $(this).parents("tr").toggleClass("ui-state-highlight", this.clicked);
            callComments($(this).closest("tr")[0].id);
        });
    }
    function callComments(id){
        $(".commentsDetails").html("Loading Comments...");
        $.getJSON("ajax_cpcb.php?method=requestExemptionComments&seq="+id,function(data){
            loadComments(data,id);
            $('.saveCommentsDetails').click(function (){
                saveComment();
            });
        });
    }
    function loadComments(details,exemptionSeq){

        var $str = "<table id='"+exemptionSeq+"' cellspacing='0' cellpadding='0' class='commentsMasterTable' style='width:100%;border:1px silver solid'><tr>";
        $str += "<th width='11%' class='ui-state-active'>Dated</th>";
        $str += "<th width='11.5%' class='ui-state-active'>User</th>";
        $str += "<th width='74%' class='ui-state-active'>Comments</th>";
        $str += "<th width='4%' class='ui-state-active'>Action</th>";
        $str += "</tr>";
        if(details.length == 0){
            $str += "<tr><td colspan=5>No Comments Found</td></tr>"

        }else{
            $.each(details,function(key,value){
                $str += "<tr>";
                $str += "<td>"+ value.dated +"</td>";
                $str += "<td>"+ value.userName +"</td>";
                $str += "<td>"+ value.comments +"</td>";
                $str += "<td><img title='Delete' alt='Delete' src='images/delete.png' id='"+value.seq+"' class='deleteCommentDetail'></td>";
                $str += "</tr>";
            });
        }
        $str += "<tr bgcolor='#EEE'><td height='40px' colspan=3>";
        $str += "<form name='commentsDetailsForm' id='commentsDetailsForm'>";
        $str += "<input type='hidden' name='commentMasterSeq' id='commentMasterSeq' value='"+exemptionSeq+"'/>";
        $str += "<input type='hidden' name='userSeq' id='usreSeq' value='"+<?echo $userLogged->getSeq()?>+"'/>";
        $str += "Your Comments: <input type = 'text' name = 'newComments' size='150'>";
        $str += "</form>"
        $str += "</td><td><img alt='Save New comment' title='Save New comment' src='images/save.png' class='saveCommentsDetails'></td></tr>"
        $str += "</table>";

        $(".commentsDetails").html($str);
        $('.deleteCommentDetail').click(function (){
            $commentMasterId = $(this).closest("table")[0].id;
            deleteCommentDetail(this.id,$commentMasterId);
        });
    }
    function saveComment(){
        $(".responseDiv").hide();
        $(".fullBody").fadeTo("slow", 0.33);
        var formData = $("#commentsDetailsForm").serialize();
        var formDataArr = $("#commentsDetailsForm").serializeArray();
        comMasterSeq = formDataArr[0].value;

        $.getJSON("ajax_cpcb.php?method=saveExemptionComment",formData,function(data){
            $(".fullBody").fadeTo("fast", 100);
            if(data.RESPONSE == "SUCCESS"){
                $(".responseDiv").show();
                $(".responseDiv").html("Comments Details Saved Successfully!");
                $(".responseDiv").addClass("ui-state-highlight");
                callComments(comMasterSeq);
            }
        });

    }
</script>
<div style="margin:auto;width:100%;min-height:25    0px;margin-top:3px;padding:4px;" class="ui-widget-content">
    <div style="border:1px silver solid;padding:10px;">
        <div class="responseDiv" style="padding:10px;display:none"></div>
        <p>Please Select Station, Pollutant, From and To Dates Ranges for Data Exemption application from CPCB.</p>
        <form name="commentsMasterForm" id="commentsMasterForm" method="POST" action="#">
            <table cellspacing='0' cellpadding='0' width="100%"  class="comentsMasterFormTable" border="0">
                <tr>
                    <th class="ui-state-active">Station</th>
                    <td colspan="3" class="stationsSelectDiv"></td>
                </tr>
                <tr>
                    <th class="ui-state-active">From Date</th>
                    <td><input type="text" size="15" name="fromDate" id="fromDate"></td>
                    <th class="ui-state-active">To Date</th>
                    <td><input type="text" size="15" name="toDate" id="toDate"></td>
                </tr>
                <tr>
                    <td colspan="4" style="padding:8px;" class="pollutantsTD">
                        select station to load pollutants
                    </td>
                </tr>
                <tr>
                    <th colspan="1" class="ui-state-active">Comments</th>
                    <td colspan="3" ><textarea name="comments" style="width:100%" cols="100" rows="4"></textarea></td>
                </tr>
               <tr>
                    <td align="right" colspan="4">
                        <input type="checkbox" name="isExemption" id="isExemption" /> Submit as Exemption
                        <input type ="button" value="Submit" class="btn submitForm" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
 <div id="tabs" style="width:100%px;padding:5px;">
        <ul>
            <li><a href="#Exemptions">Exemption Notifications</a></li>
            <li><a href="#Comments">Comments System</a></li>
        </ul>
        <div id="Exemptions">
            <div class="exemptionsDiv"></div>
        </div>
        <div id="Comments">
            <div class="exemptionsCommentsDiv"></div>
        </div>
 </div>
 <div class="commentsDetails"></div>