    function getAllLocationsAndStations(){
        $.getJSON("ajax_cpcb.php?method=requestAllLocationsAndStationsJSON&folderType=all",function(data){
            $(".locationsSelectDiv").html("<select class='locationsSelect' name='locationsSelect' onChange='locationChange()'></select>");
            $.each(data.locations,function(key,value){
                $('.locationsSelect')
                .append($("<option></option>")
                .attr("value",key)
                .text(value));
            });
            loadStationsDiv(data.folders);
        });
    }
    function locationChange(){
        $locSeq = $(".locationsSelect").val();
        if($locSeq == 0){
            getAllLocationsAndStations();//located in the actual file
        }else{
            getStationsByLocation($locSeq,"ALL");
        }
    }
    function getStationsByLocation($locSeq,$stationType){
        $.getJSON("ajax_cpcb.php?method=requestStationsByLocationJSON&locSeq="+$locSeq+"&stationType="+$stationType,function(data){
            loadStationsDiv(data);
        });
    }
    function loadStationsDiv(data){
        $(".stationsSelectDiv").html("<select class='stationsSelect' name='stationsSelect' onChange='stationChange()'></select>");
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
        $(".pollutantsSelectDiv").html("<select class='pollutantSelect' name='pollutantSelect'></select>");
        $('.pollutantSelect')
                .append($("<option></option>")
                .attr("value",'0')
                .text('NO POLLUTANTS'));
    }
    function stationChange(){
        $folSeq = $(".stationsSelect").val();
        loadPollutantsDiv($folSeq);
    }
    function loadPollutantsDiv(folSeq){
        $.getJSON("ajax_cpcb.php?method=requestChannelsByFolderJSON&folSeq="+folSeq,function(data){
            channelGroups = data.channels;
			$(".pollutantsSelectDiv").html("<select class='pollutantSelect' name='pollutantSelect'></select>");
			$('.pollutantSelect')
						.append($("<option></option>")
						.attr("value",'0')
						.text('SELECT POLLUTANT'));
			$.each(channelGroups,function(dataKey,data){
				if( typeof isLoadPollutatntsInSelect !== 'undefined' && isLoadPollutatntsInSelect == false) {
					 str="";
					 $.each(data,function(key,value){
						str += "<span class='parameterCheckSpan'>";
						str += "<input class='exportParamsCheck' type='checkbox' name='channelNos[]' value='"+key+"'/>";
						str +=   value  +"</span>";
					})
					$(".pollutantsTD").html(str);
				}else{
					$.each(data,function(key,value){
						if(key != "video"){
						$('.pollutantSelect')
						.append($("<option></option>")
						.attr("value",key)
						.text(value ));
						}
					});
				}
			});
		});
    }
    function loadFromToDatesPicker(){
        $('#fromDate').datetimepicker({
            maxDate: new Date(),
            dateFormat: 'dd-mm-yy',
            changeMonth: true,

        });
        $('#toDate').datetimepicker({
            maxDate: new Date(),
            dateFormat: 'dd-mm-yy',
            changeMonth: true,

        });
    }
