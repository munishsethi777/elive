    
    
    function getCurrentChannelsInfo(folderSeq,isConvert){
        var url = "ajax_GetChannelsCurrentInfo.php?folSeq="+ folderSeq +"&isConvertUnits="+isConvert;
        $.getJSON(url,function(data){
            $(".CurrentDateFormated").html(data['formatedDated']);
            $("#dated").val(data['dated']);
            channelsArr = data['channelsInfo'];
            $.each(channelsArr, function(key, value){
               if(value != null && value != "null"){
                    $("#"+key).html(value);
                    if(key.indexOf("status") != -1){
                        setStatusIndicator(value,key.replace("status",""));
                    }
                    
               }    
            });
        });  
    }
    function setStatusIndicator(statusValue, cellId){
        var color = "white";
        var text = "";
        if(statusValue == "131"){
            color = "pink";
        }else if(statusValue == "130"){
            color = "cyan";  
        }else if(statusValue == "0"){
            text = "No Data";  
        }else if(statusValue == "1"){
            text = "Not Enough Data";  
        }else if(statusValue == "64"){
            text = "Calibration All Ok";  
        }else if(statusValue == "65"){
            text = "Calibration Suf Data";  
        }else if(statusValue == "33"){
            text = "Instrument Fault";  
        }else if(statusValue == "34"){
            text = "Power Failure";  
        }else if(statusValue == "36"){
            text = "Out of Service";  
        }
        
        $("#"+cellId+"cell").css("background",color);
        if(text != ""){
            $("#"+cellId+"cell").html(text);
        }
    }