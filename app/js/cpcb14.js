    var chart = null;
    function loadStationsDiv(data){
        $(".stationsSelectDiv").html("<select class='stationsSelect' name='stationsSelect'></select>");
            $('.stationsSelect')
                .append($("<option></option>")
                .attr("value",'0')
                .text('SELECT STATION'));
            $.each(data,function(key,value){
                loc = value.folderLocation;
                if(value.folderLocation == ""){
                    loc = $('.locationsSelect :selected').text();
                }
                $('.stationsSelect')
                .append($("<option></option>")
                .attr("value",key)
                .text(loc +" : "+ value.folderName ));
            });
            $(".stationsSelect").html($('.stationsSelect option').sort(function(x, y) {
            	if($(y).text() != "SELECT STATION"){
            		return $(x).text() < $(y).text() ? -1 : 1;
                }
            }));
    }
    function locationChange($stationType){
        $locSeq = $(".locationsSelect").val();
        if($locSeq == 0){
            getAllLocationsAndStations();//located in the actual file
        }else{
            getStationsByLocation($locSeq,$stationType);
        }
    }
    function getStationsByLocation($locSeq,$stationType){
        $.getJSON("ajax_cpcb.php?method=requestStationsByLocationJSON&locSeq="+$locSeq+"&stationType="+$stationType,function(data){
            loadStationsDiv(data);
        });
    }
    function selectAllChannels(){
        $('.stationTabDiv').each(function(i, obj) {
            if($(obj).is(":visible")){
                $('#'+ obj.id).find(':checkbox').each(function(){
                    $(this).prop('checked','checked');
                });
            }
        });


    }
    function unselectAllChannels(){
        $('.stationTabDiv').each(function(i, obj) {
            if($(obj).is(":visible")){
                $('#'+ obj.id).find(':checkbox').each(function(){
                    $(this).prop('checked',false);
                });
            }
        });

    }
    function layRecentGrid(json){
    	$("#pagger").html("");
        $(".stationReport").html("");
        $.each(json,function(folkey,folderData){
            var str = "<div class='recentReportStation'>";

            str += "<p class='ui-state-default recentReportStationTitle'>Recent Air Quality Data on "+ folderData.dated +" for "+ folderData.location +" - "+ folderData.station +"</p>";
            str += "<table class='stationReportTable' cellspacing=0 cellpadding=0 width='100%'>";
            str +="<tr><td width='150px' class='tableHeader'>Parameter</td>";
            $.each(folderData.channelsData,function(key,value){
                str += "<td class='tableHeader'>"+ value["ch"+(key+1)+"name"] +"<br>"+ value["ch"+(key+1)+"unit"] +"</td>";
                //added 1 to key, becos keys started from 0 and channels from 1
            });
            str +="</tr><tr><td class='tableHeader'>Value</td>";
            $.each(folderData.channelsData,function(key,value){
                str += "<td class='odd'>"+ value["ch"+(key+1)+"value"] +"</td>";
            });
            str +="</tr><tr><td class='tableHeader'>Last 24 Hrs Avg</td>";
            $.each(folderData.channelsData,function(key,value){
                str += "<td class='even'>"+ value["ch"+(key+1)+"avg"] +"</td>";
            });
            str +="</tr><tr><td class='tableHeader'>Last 24 Hrs Min</td>";
            $.each(folderData.channelsData,function(key,value){
                str += "<td class='odd'>"+ value["ch"+(key+1)+"min"] +"</td>";
            });
            str +="</tr><tr><td class='tableHeader'>Last 24 Hrs Max</td>";
            $.each(folderData.channelsData,function(key,value){
                str += "<td class='even'>"+ value["ch"+(key+1)+"max"] +"</td>";
            });
            str +="</tr>";
            str += "</table>";
            str += "</div>"
            $(".stationReport").append(str);
        });
    }
    function layGridReport(json,isPagger){
        if(json == null){
           $(".stationReport").html("Invalid Execution !!");
           return;
        }else{
        	
        	if(json.isCombustionError == 1){
        		$(".stationReport").html("   Please Select Co2 and Co parameters only");
        		return
        	}
        }
        
        var colsCount = json.channels.length;
        var str = "<table class='stationReportTable' cellspacing=0 cellpadding=0><thead><tr><th class='tableHeader' style='width:140px;'>Dated</th>";
        $.each(json.channels,function(key,value){
            var indx = jQuery.inArray(value, json.channels);
            str += "<th class='tableHeader' id='chId"+ indx +"'>"+ value +"</th>"
        });
        str +="</tr></thead>";
        var trclassName = "";
        $(".stationReport").html(str)
        str+= "<tbody>";
        isExceeding = null;
        if($("#isExceedingCheckbox")[0] != undefined    ){
            isExceeding = $("#isExceedingCheckbox")[0].checked;
        }

        var exceedingType = $('input:radio[name=exceedingTypeRadio]:checked').val();
        var exceedingValue = $("#exceedingValue").val();
        if(isExceeding == false){
            exceedingValue = null;
            exceedingType = null;
        }
            //Average calcultions
			if(json.avgminmax != null){
				str += "<tr class='odd'>";
				str += "<td>Average</td>";
				$.each(json.avgminmax.avg, function (key,value){
					str += "<td>" + value +"</td>";
				});
				str += "</tr>";

				str += "<tr class='odd'>";
				str += "<td>Minimum</td>";
				$.each(json.avgminmax.min, function (key,value){
					str += "<td>" + value +"</td>";
				});
				str += "</tr>";

				str += "<tr class='odd' style='border-bottom;black solid 1px'>";
				str += "<td >Maximum</td>";
				$.each(json.avgminmax.max, function (key,value){
					str += "<td>" + value +"</td>";
				});
				str += "</tr>";
			}
        $.each(json.data,function(key,value){
            $("#lastDataDate").val(key);
            trclassName = trclassName == 'odd' ? 'even' : 'odd';
            str += "<tr class='"+ trclassName +"'>";
            str += "<td>"+ key +"</td>";
            var cnt = colsCount -1;
            var c = 0;
            $.each(value,function(keyVal,valueVal){
                if(c==0){
	            	if(keyVal != 0){
	            		k = parseInt(keyVal);
	            		for(var i=0;i<k;i++){
	                        str += "<td>n.a</td>";
	                        cnt--
	                    }
	            	}
            	}
                tdclassName = "noExceeding";
                if(exceedingType == "above" && parseFloat(valueVal) > parseFloat(exceedingValue)){
                    tdclassName="exceedingAbove";
                }
                if(exceedingType == "below" && parseFloat(valueVal) < parseFloat(exceedingValue)){
                    tdclassName="exceedingBelow";
                }
                if(valueVal == "n.o"){
                    tdclassName="-";
                }
                //str += "<td class='"+ tdclassName +"'><a>"+ valueVal +"</a></td>";
                str += "<td class='"+ tdclassName +"'>"+ valueVal +"</td>";
                cnt--;
                c++;
            });
            while(cnt >= 0){
                str += "<td>n.a</td>";
                cnt--;
            }
            str += "</tr>";
        });
        str +="</tbody></table>";
        $(".stationReport").html(str);
        //bind the value click function here
        //$('table.stationReportTable td a').click(function(e){
//            var dated = this.parentElement.parentElement.firstChild.textContent;
//            var cellIndex = this.parentElement.cellIndex;
//            var headerText = $("#chId"+cellIndex).html();
//            headerText = headerText.replace("<br>","_");
//            var html = "<p>Edit value for channel:<b>"+ headerText +"</b> for dated <b>"+ dated +"</b></p>";
//            html += "<div>Enter your Comments :<br><textarea rows='3' cols='28'></textarea>";
//            html += "<br><center><input type='button' value='submit' onclick='submitComments()'></center>";
//
//            TINY.box.show({html:html,animate:true,close:true,boxid:'success'});
//        });
        if(typeof isPagger == 'undefined'){
        	initPagger(json.count,Object.keys(json.data).length);
        }	
    }
    function submitComments(){
       TINY.box.show({html:"Comments saved successfully!!",animate:true,close:false,boxid:'success'});
    }
    function getGraphSeries(json){
        //var str = '[{"key":"India","data":[1,2,3,5,2]}]';
        var str = '[';
        var i = 0;
        $.each(json, function(id, item) {
            if(i != 0){
                str += ',';
            }
            str += '{"key":"'+id+'","data":';
            str += JSON.stringify(item);
            str += '}';
            i++;
        });
        str +=']';
        str = str.replace(/""/g,'null');
        return str;
    }
    function addStation(){
        $folSeq = $(".stationsSelect").val();
        $folderName = $(".stationsSelect option:selected").text();
        if($folSeq == 0){
            return;
        }
        addStationFolder($folSeq,$folderName);
    }
    function addStationFolder(folSeq,folderName){
        $.getJSON("ajax_cpcb.php?method=requestChannelsByFolderJSON&folSeq="+folSeq,function(data){
            var infoType = $('input:radio[name=infoTypeRadio]:checked').val();
            if(infoType == "windRose"){
                channles = data.channels;
                checkWindRose(channles);
            }else{            	
                addStationTab(data,folSeq,folderName);
                showFlowVideo(data);
            }

        });
    }
    function checkWindRose(data){
        $(".channelsDiv").html("Fetching Channels From Server...");
        isWD = false;
        isWS = false;
        $.each(data,function(key,value){
            if(value == "Wind Speed"){
                isWS = true;
            }
            if(value == "Wind Direction"){
                isWD = true;
            }
        });
        if(isWS == true && isWD == true){
            $(".channelsDiv").html("<label style='color:green'>Station captures Wind Speed and Wind Direction</label>");
        }else{
            $(".channelsDiv").html("<label style='color:red'>Station does not capture Wind Speed and Wind Direction</label>");
        }
    }
    
    // function showFlowVideo(data){    	
    // 	var cameraUrl = data.cameraurl;
    // 	var str = "";
    // 	$("#flowVideoDiv").html("");
    // 	if(cameraUrl != null && cameraUrl != ""){
    // 		//str = "<video id='videoOutput' autoplay width='480px' height='360px' poster='images/webrtc.png'></video>";
    // 		//str += '<input id="address" type="hidden">'
    // 		//str += getIndustryDeail(data.industrydetail);
    // 		//str += getCameraDetail(data);
    // 	    $("#flowVideoDiv").html(cameraUrl)
    // 	    //start(cameraUrl);
    // 	}
    // }
    function showFlowVideo(data){    	
    	var cameraUrl = data.cameraurl;
    	var str = "";
    	$("#flowVideoDiv").html("");
    	if(cameraUrl != null && cameraUrl != ""){
    		//str = "<video id='videoOutput' autoplay width='480px' height='360px' poster='images/webrtc.png'></video>";
    		//str += '<input id="address" type="hidden">'
    		//str += getIndustryDeail(data.industrydetail);
    		//str += getCameraDetail(data);
    		var str = '<form name="frm1" id="frm1" method="post" action="changePassword.php" class="form-horizontal">';
    		str += '<div class="form-group">';
    		str +=	'<div class="col-lg-offset-2 col-lg-10">';
    			str +='<button class="moveCamera" id="Up" type="button"><img width="25px;" src="images/icons8-upward-arrow-50.png"></button>';
    			str +='<button class="moveCamera" id="Down" type="button"><img width="25px;" src="images/icons8-below-50.png"></button>';
    			str +='<button class="moveCamera" id="Left" type="button"><img width="25px;" src="images/icons8-go-back-50.png"></button>';
    			str +='<button class="moveCamera" id="Right" type="button"><img width="25px;" src="images/icons8-circled-right-50.png"></button>';
    			str +='<button class="moveCamera" id="Stop" type="button"><img width="25px;" src="images/icons8-pause-button-50.png"></button>';
    			str +='<button class="moveCamera" id="ZoomTele" type="button"><img width="25px;" src="images/icons8-expand-50.png"></button>';
    			str +='<button class="moveCamera" id="ZoomWide" type="button"><img width="25px;" src="images/icons8-collapse-50.png"></button>';
    			str += '</div></div></form>';
    			str += cameraUrl;
    	    $("#flowVideoDiv").html(str)
    	    $(".moveCamera").click(function(e){
    	    	moveCamera(this.id);
    	    });
    	    //start(cameraUrl);
    	}
    }
    function moveCamera(direction){
    	var folderSeq = $(".stationsSelect").val();
    	var url ="ajax_cpcb.php?method=saveCameraPTZ&action="+direction+"&folderSeq="+folderSeq;
    	$.get(url, function(data){
      		//alert("success");
        });
    }
    function getIndustryDeail(industrydetail){
    	str = '<div style="width:100%">';
		str += '<div style="float:left; width:49%;">';
    	str += '<h3 class="header smaller lighter purple"> Industry Details </h3>';
    	str += '<div style="padding-left: 10px;">';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span class="blue" style="float: left; width: 45%;">Industry Category:</span>';
    	str += '<span style="float: left; width: 55%;">'+industrydetail.category+'</span>';
    	str += '</div>';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span style="float: left; width: 45%;" class="blue">Industry Name:</span>';
    	str += '<span style="float: left; width: 55%;">'+industrydetail.name+'</span>';
    	str += '</div>';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span style="float:left; width:45%;" class="blue">Industry Location:</span>';
    	str += '<span style="float:left; width:55%;">'+industrydetail.locationname+'</span>';
    	str += '</div></div>';
        return str;
    }
    
    function getCameraDetail(data){
    	str = '<div style="float:right; width:49%;">';
    	str += '<h3 class="header smaller lighter orange">Camera Detalis</h3>';
    	str += '<div style="padding-left: 10px;">';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span class="blue" style="float: left; width: 45%;">Camera Make:</span>';
    	str += '<span style="float: left; width: 55%;">CPPLUS</span>';
    	str += '</div>';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span style="float: left; width: 45%;" class="blue">Camera Model No:</span>';
    	str += '<span style="float: left; width: 55%;">CPPLUS</span>';
    	str += '</div>';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span style="float:left; width:45%;" class="blue">PTZ:</span>';
    	str += '<span style="float:left; width:55%;">Yes</span>';
    	str += '</div>';
    	str += '<div style="min-height: 20px; height: auto; width: 100%; display: table; margin-bottom: 5px;">';
    	str += '<span style="float:left; width:45%;" class="blue">10x Zoom:</span>';
    	str += '<span style="float:left; width:55%;">Yes</span>';
    	str += '</div></div>'
    	return str;
    }
    function addStationTab(data,folSeq,folderName){
        if ($('#tabs'+folSeq).length>0 ) {
            return;
        }
                
        chData = data.channels;
        folderInfo = data.folderInfo;
        var str = "<p style='margin-bottom:5px'>" + folderInfo + "<p>";
        if(folSeq == 140 || folSeq == 141){
        	var locSeq = $(".locationsSelect").val();
        	var folderSeq = 44;
        	str += "<p style='margin-bottom:5px'>Please click <a target='new' href='cpcbStackReportMultiStation.php?ishistory=1&locSeq="+locSeq+"&folderSeq="+folderSeq+"'>here</a> to see the historical(before 17 Sept. 2019) data for the selected station<p>";
        }
        if(data.calliberationURL != undefined && data.calliberationURL != ""){
        	str = "<a target='new' style='margin:10px;padding:10px' class='button ui-button ui-widget ui-state-default' href='"+data.calliberationURL+"'>Remote Calibration</a>" + str;
        }
        $.each(chData,function(key,value){  
            if(key == "video"){
                str += "<p>";
                str += "<a class='videoFeed48' href='#'>Live Video Feed</a>";
                str += "</p>";
                videoPopupStr = value;
            }else{
            	//if(value.length > 0){
	                 str +="<div style='display:block'><b>" + key + "</b></div>"; 
	                 $.each(value,function(k,channel){
	                      str += "<span class='parameterCheckSpan'>";
	                      str += "<input class='exportParamsCheck' type='checkbox' name='channelNos_"+folSeq+"[]' value='"+k+"'/>";
	                      str +=   channel  +"</span>";
	                 });  
	            //}
            }
        });
        

        id = "tabs" + folSeq;
        li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, folderName ) );
        tabs.find( ".ui-tabs-nav" ).append( li );
        tabs.append( "<div class='stationTabDiv' id='" + id + "'><p>" + str + "</p></div>" );
        tabs.tabs( "refresh" );

        //MCL Video Feed
        $('.videoFeed48').click(function (){
            TINY.box.show({height:200,html:videoPopupStr,animate:true,close:true});
        });
        
        $( ".stationsTab" ).tabs( "option", "active", tabCounter );
        tabCounter++;
        //binding close tab method
        tabs.delegate( "span.ui-icon-close", "click", function() {
              var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
              $( "#" + panelId ).remove();
              tabs.tabs( "refresh" );
              tabCounter--;
        });
    }
    function resetQuickButton(){
        $(".quickButton").removeClass("menuHoverFix");
    }

    function submitRecentReport(formData){
        $("#graphDiv").hide();
        $(".stationReport").html("<img src ='admin/images/ajax.gif'/> Loading data...");
        $.getJSON("ajax_cpcb.php?method=requestRecent",formData,function(data){
            if(data == null){
               $(".stationReport").html("Invalid Execution !!");
               return;
            }
            layRecentGrid(data,true);
        });
    }
    function submitAdvanceSearch(){
        submitReport(null);
    }
	function layMultipleGraphReport(jsonRaw){
		if(jsonRaw == null){
	        $("#mainGraphDiv").html("Invalid Execution !!");
	        return;
	    }
		var str = getGraphSeries(jsonRaw.values);
        var json = jQuery.parseJSON(str);
		var units = jsonRaw.units;
		var presLimits = jsonRaw.presLimits;
		$.each(json, function(key, value) {
		    unitText = units[key];
			var presLimit = presLimits[key];
		    divId = "graphDiv" + key;
			$("#mainGraphDiv").append('<div id="' + divId + '" class="graphDiv" style="width:1200px;margin:auto;margin-top:10px;"></div>');
			
	        var arrLen = jsonRaw.dates.length;
	        var tickInterval = 1;
	        if(arrLen>10){
	            tickInterval = arrLen/10;
	        }
	        renderChart(tickInterval,divId,unitText);
	        graphType = $('input[name=graphTypeRadio]:checked').val();
	        valType = $('input[name=valueTypeRadio]:checked').val();

	        options.series.reverse();
	        var seriesArray = new Array();	      
	        var series = {};
		    series.name = value.key;
			series.data = value.data;
			if(graphType != "windrose" && graphType != "polar"){
				series.type = graphType;
			}
			seriesArray.push(series);
	       
	        options.series = seriesArray;
			if(graphType == "polar"){
				options.polar = true;
			}

			chart = new Highcharts.Chart(options);
			chart.xAxis[0].setCategories(jsonRaw.dates,false);
			chart.redraw(); //redraw, showing all the changes

			valueType = $('input[name=valueTypeRadio]:checked').val();
			if(valueType == "normal"){
				if(presLimit != null && presLimit > 0){
					chart.yAxis[0].addPlotBand({
						from: 0,//jsonRaw['presLimit']['min'],
						to: presLimit,
						color: 'rgba(125, 255, 74, 0.5)',
						id: 'plotband1',
						label : {
							text : 'Prescribed Limit Range'
						}
					});
				}
			}
			WinMove();
		});
	    
	}
    function layGraphReport(jsonRaw){
        if(jsonRaw == null){
           $("#graphDiv").html("Invalid Execution !!");
           return;
        }
        var arrLen = jsonRaw.dates.length;
        var tickInterval = 1;
        if(arrLen>10){
            tickInterval = arrLen/10;
        }
        renderChart(tickInterval);
        graphType = $('input[name=graphTypeRadio]:checked').val();
        valType = $('input[name=valueTypeRadio]:checked').val();

        options.series.reverse();
        var str = getGraphSeries(jsonRaw.values);
        //var str = '[{"key":"India","data":[1,2,3,5,2]}]';
        var json = jQuery.parseJSON(str);
        var seriesArray = new Array();
        $.each(json, function(key, value) {
            var series = {};
            series.name = value.key;
            series.data = value.data;
            if(graphType != "windrose" && graphType != "polar"){
                series.type = graphType;
            }
            seriesArray.push(series);
			
        });
        options.series = seriesArray;
	        if(graphType == "polar"){
	            options.polar = true;
	        }

	        chart = new Highcharts.Chart(options);
	        chart.xAxis[0].setCategories(jsonRaw.dates,false);
	        chart.redraw(); //redraw, showing all the changes

	        valueType = $('input[name=valueTypeRadio]:checked').val();
	        if(valueType == "normal"){
	            if(jsonRaw['presLimit']!= null){
	                chart.yAxis[0].addPlotBand({
	                    from: 0,//jsonRaw['presLimit']['min'],
	                    to: jsonRaw['presLimit']['max'],
	                    color: 'rgba(125, 255, 74, 0.5)',
	                    id: 'plotband1',
	                    label : {
	                        text : 'Prescribed Limit Range'
	                    }
	                });
	            }
	        }
    //});
    }
    function renderChart(tickInterval,divId,txt){
	    if(divId == undefined){
			divId = "graphDiv";
		}
		if(txt == undefined){
			txt = "Monitor Readings";
		}
        options = null;
        options = {
            chart: {
                renderTo:divId,
                defaultSeriesType: 'column',
                zoomType: 'x',
            },
            title: {
                text: ''
            },
            plotOptions: {
                series: {
                    connectNulls: true
                }
            },
            xAxis: {
                offset:0,
               tickInterval:parseInt(tickInterval),
               minorTickInterval:10,
               maxZoom:parseInt(tickInterval),
               showLastLabel: true,
			   title: {
                    text: 'Date & Time',
					style: {
							fontSize: '14px',
							whiteSpace: 'nowrap',
							paddingLeft: '10px',
							paddingRight: '10px',
							paddingTop: '20px',
							paddingBottom: '10px',


						}
                 },
     
               labels : {
                    itemStyle: {
                        fontSize: '8px',
                        fontFamily: 'Arial',
                    },
					x:5,y : 20, align: 'right'
	           },events: {
                    setExtremes: function() {
                        //chart.xAxis[0].options.tickInterval = 7;
//                        chart.xAxis[0].isDirty = true;
//                        chart.redraw();
                    }
                }
            },
            yAxis:{
				min : 0,
                minRange:2,
                title: {
                    text: txt
                 }
            },
            series: [],
            legend: {
                y:5,
                x:5,
                itemStyle: {
                    fontSize: '8px'
                }
            }
			
        };
    }
	//dragable panels
	function WinMove() {
	    var element = "[class*=mainGraphDiv]";
	    var handle = ".graphDiv";
	    var connect = "[class*=mainGraphDiv]";
	    $(element).sortable(
	        {
	            handle: handle,
	            connectWith: connect,
	            tolerance: 'pointer',
	            forcePlaceholderSize: true,
	            opacity: 0.8
	        })
        .disableSelection();
}
    //windrose functions
    function layWindRoseGraphReport(json){
        if(json == null){
           $(".stationReport").html("Invalid Execution !!");
           return;
        }
        var str = '<table id="freq" border="1" cellspacing="2" cellpadding="2" style="font-family:arial;font-size:10px;">';
        str += '<tr nowrap bgcolor="#CCCCFF">';
        str += '<th colspan="9" class="hdr">Table of Frequencies (percent)</th>';
        str += '</tr>';
        str += '<tr nowrap bgcolor="#CCCCFF">';
        str += '<th class="freq">Direction</th>';
        str += '<th class="freq">&lt; 1 m/s</th>';
        str += '<th class="freq">1-5 m/s</th>';
        str += '<th class="freq">5-10 m/s</th>';
        str += '<th class="freq">10-15 m/s</th>';
        str += '<th class="freq">15-20 m/s</th>';
        str += '<th class="freq">20-* m/s</th>';
        str += '</tr>';
        $.each(json,function(plotDirection,plotArr){
                str += '<tr nowrap>';
                str += '<td class="dir">'+plotDirection+'</td>';
                $.each(plotArr,function(key,val){
                    str+= '<td class="data">'+val+'</td>';
                });
                str += '</tr>';
        });
        $(".stationReport").html(str);
		divId = "graphDiv";
		$("#mainGraphDiv").append('<div id="' + divId + '" class="graphDiv" style="width:1200px;margin:auto;margin-top:10px;"></div>');
        renderWindRose();
        $(".stationReport").html("");
    }
    function renderWindRose() {
        Highcharts.data({
            table: 'freq',
            startRow: 1,
            endRow: 17,
            endColumn: 7,
            complete: function (options) {
                options.series.reverse(); // to get the stacking right
                window.chart = new Highcharts.Chart(Highcharts.merge(options, {
                    chart: {
                        renderTo: 'graphDiv',
                        polar: true,
                        type: 'column'
                    },
                    title: {
                        text: 'Wind rose Diagram'
                    },
                    subtitle: {
                        text: 'Source: envirotechlive.com'
                    },
                    pane: {
                        size: '85%'
                    },
                    legend: {
                        reversed: true,
                        align: 'right',
                        verticalAlign: 'top',
                        y: 100,
                        layout: 'vertical'
                    },
                    xAxis: {
                        tickmarkPlacement: 'on'
                    },
                    yAxis: {
                        min: 0,
                        endOnTick: false,
                        showLastLabel: true,
                        title: {
                            text: 'Frequency (%)'
                        },
                        labels: {
                            formatter: function () {
                                return this.value + '%';
                            }
                        }
                    },
                    tooltip: {
                        valueSuffix: '%'
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                            shadow: false,
                            groupPadding: 0,
                            pointPlacement: 'on'
                        }
                    }
                }));
            }
        });
        //$(".stationReport").html("");
    }
    function isValidDates(quickReportType){
    	var fromDate = $("#fromDate").val();
    	var toDate = $("#toDate").val();
    	fromDate = fromDate.split("-");
    	var fromYear = fromDate[2].split(" ")
    	var fromMin = fromYear[1].split(":");
    	toDate = toDate.split("-"); 
    	var toYear = toDate[2].split(" ");
    	var toMin = toYear[1].split(":");
    	var date1 = new Date(fromYear[0],fromDate[1]-1,fromDate[0],fromMin[0],fromMin[1]);
    	var date2 = new Date(toYear[0],toDate[1]-1,toDate[0],toMin[0],toMin[1]);
    	var timeDiff = Math.abs(date2 - date1);
    	var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
    	if(diffDays > 15 || quickReportType == "last15days"){
    		return false;
    	}
    	return true;
     }
    function submitCumulativeReport(){
        resetQuickButton();
        var formData = $("#stationReportForm").serialize();
        var graphType = $('input:radio[name=graphTypeRadio]:checked').val();
        var infoType = $('input:radio[name=infoTypeRadio]:checked').val();

        if(infoType == "export"){
            url = "ajax_cpcb.php?method=requestStationReport&"+formData+"&quickReportType="+quickReportType;
            location.href = url;
            return;
        }
        $(".stationReport").html("<img src ='admin/images/ajax.gif'/> Loading data...");
        $(".fullBody").fadeTo("slow", 0.33);
        $.getJSON("ajax_cpcb.php?iscumulative=true&method=requestStationReport",formData,function(data){
            if(infoType == "grid"){
                $("#graphDiv").hide();
                layGridReport(data);
                $(".graphDiv").hide();
            }else if(infoType == "graph"){
                $(".stationReport").html("");
                $("#graphDiv").show();
                layGraphReport(data);				
            }
            $(".fullBody").fadeTo("fast", 100);
            $(".reportTitle").html("Continuous Effluent Monitoring system reporting for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val());
        });
    }
    
   
    function submitReport(quickReportType,isPagger){
    	iscombustion = false;
    	reportType = quickReportType;
		$("#mainGraphDiv").html("");
        resetQuickButton();
        $("#"+quickReportType).addClass("menuHoverFix");
        if(typeof isPagger == 'undefined'){
        	$("#pagenum").val(1);
        }
        var formData = $("#stationReportForm").serialize();
        if(quickReportType == "recent"){
            submitRecentReport(formData);
            return;
        }
        var infoType = $('input:radio[name=infoTypeRadio]:checked').val();
        var graphType = $('input:radio[name=graphTypeRadio]:checked').val();
        if(infoType == "export"){
        	if(isValidDates(quickReportType)){
        		url = "ajax_cpcb.php?method=requestStationReport&"+formData+"&quickReportType="+quickReportType;
        		location.href = url;
        	}else{
        		alert("Date difference should be less than 15 days")
        	}
            return;
        }
        $(".stationReport").html("<img src ='admin/images/ajax.gif'/> Loading data...");
        $(".fullBody").fadeTo("slow", 0.33);
        var method = "requestStationReport";
        var isWindRose = false;
        if(infoType == "graph"){
        	$("#pagger").html("");
        	isPagger = false;
        }
        
        if(infoType == "graph" && graphType == "windrose"){
            method = "requestWindRoseReport";
            isWindRose = true;
        }
        
        $.getJSON("ajax_cpcb.php?method="+method+"&quickReportType="+quickReportType,formData,function(data){
            if(data.isMultiStationSelected){
            	$("#pagger").html("");
            	isPagger = false;
            }
            if(infoType == "grid"){
                $("#graphDiv").hide();
                layGridReport(data,isPagger);
                $(".graphDiv").hide();
            }else if(infoType == "graph"){
                $(".stationReport").html("");
                $("#graphDiv").show();
                if(isWindRose == false){
                   //layGraphReport(data);
				   layMultipleGraphReport(data);
                }else{
                   layWindRoseGraphReport(data);
                }
            }
            $(".fullBody").fadeTo("fast", 100);
            var valueTypeRadio = $('input:radio[name=valueTypeRadio]:checked').val();
            if($("#stationReportForm")[0].stationType.value == "stack"){
                if($("#stationReportForm")[0].stationType2 != undefined && 
							$("#stationReportForm")[0].stationType2.value == "effluent"){
                    $(".reportTitle").html("Continuous Effluent Monitoring system reporting for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val());
                }else{
                    $(".reportTitle").html(valueTypeRadio.toUpperCase()+ " value Continuous Stack Emission Monitoring system reporting for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val() + "&nbsp;");
                }
            }else if($("#stationReportForm")[0].stationType.value == "aqmsp"){
            	$(".reportTitle").html(valueTypeRadio.toUpperCase()+ " value Air Quality Monitoring system reporting for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val());
            }
            else{
                $(".reportTitle").html(valueTypeRadio.toUpperCase()+ " value Air Quality Data Report for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val());
            }
            
        });
    }
    function submitCombustionReport(quickReportType,isPagger){
    	iscombustion = true;
		$("#mainGraphDiv").html("");
        resetQuickButton();
        $("#"+quickReportType).addClass("menuHoverFix");

        var formData = $("#stationReportForm").serialize();
        if(quickReportType == "recent"){
            submitRecentReport(formData);
            return;
        }
        var infoType = $('input:radio[name=infoTypeRadio]:checked').val();
        var graphType = $('input:radio[name=graphTypeRadio]:checked').val();
        if(infoType == "export"){
            url = "ajax_cpcb.php?iscombustion=true&method=requestStationReport&"+formData+"&quickReportType="+quickReportType;
            location.href = url;
            return;
        }
        $(".stationReport").html("<img src ='admin/images/ajax.gif'/> Loading data...");
        $(".fullBody").fadeTo("slow", 0.33);
        var method = "requestStationReport";
        var isWindRose = false;

        if(infoType == "graph" && graphType == "windrose"){
            method = "requestWindRoseReport";
            isWindRose = true;
        }
        $.getJSON("ajax_cpcb.php?iscombustion=true&method="+method+"&quickReportType="+quickReportType,formData,function(data){
            if(infoType == "grid"){
                $("#graphDiv").hide();
                layGridReport(data,isPagger);
                $(".graphDiv").hide();
            }else if(infoType == "graph"){
                $(".stationReport").html("");
                $("#graphDiv").show();
                if(isWindRose == false){
                   //layGraphReport(data);
				   layMultipleGraphReport(data);
                }else{
                   layWindRoseGraphReport(data);
                }
            }
            $(".fullBody").fadeTo("fast", 100);
            var valueTypeRadio = $('input:radio[name=valueTypeRadio]:checked').val();
            if($("#stationReportForm")[0].stationType.value == "stack"){
                if($("#stationReportForm")[0].stationType2 != undefined && 
							$("#stationReportForm")[0].stationType2.value == "effluent"){
                    $(".reportTitle").html("Continuous Effluent Monitoring system reporting for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val());
                }else{
                    $(".reportTitle").html(valueTypeRadio.toUpperCase()+ " value Continuous Stack Emission Monitoring system reporting for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val() + ". &nbsp;");
                }
            }else{
                $(".reportTitle").html(valueTypeRadio.toUpperCase()+ " value Air Quality Data Report for dates "+ $("#fromDate").val() +" to "+ $("#toDate").val());
            }

        });
    }
    $(function() {
    $("#toTop").addClass("menuHoverFix");
    $(window).scroll(function() {
        if($(this).scrollTop() != 0) {
            $('#toTop').fadeIn();
        } else {
            $('#toTop').fadeOut();
        }
    });

    $('#toTop').click(function() {
        $('body,html').animate({scrollTop:0},800);
    });
});

//comments page js here

    function loadPollutantsDropDownDiv(folSeq){
        $.getJSON("ajax_cpcb.php?method=requestChannelsByFolderJSON&folSeq="+folSeq,function(data){
            $(".pollutantsSelectDiv").html("<select class='pollutantSelect' name='pollutantSelect'></select>");
            $('.pollutantSelect')
                .append($("<option></option>")
                .attr("value",'0')
                .text('SELECT POLLUTANT'));
            $.each(data,function(key,value){
                loc = value.folderLocation;
                if(value.folderLocation == ""){
                    loc = $('.locationsSelect :selected').text();
                }
                $('.stationsSelect')
                .append($("<option></option>")
                .attr("value",key)
                .text(loc +" : "+ value.folderName ));
            });
        });
    }
