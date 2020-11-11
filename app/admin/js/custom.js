    function AddUserDiv(allowDelete){      
        showFolders_UserDivId++; 
        var usersDropDown = getDropDown(showFolders_UsersJSON,"userSelect");
        var html = "<Div class='AddUserDiv' id='"+ showFolders_UserDivId +"_AddUserDiv' style='clear:both;padding:2px 2px 2px 2px;'>";
        html += "Select User: "+ usersDropDown;
        html += " Select Permission: "+ getPermissionsDropDown();
        if(showFolders_UserDivId == 1){
            html += " <input type='button' value='Add User' onclick='javascript:AddUserDiv(true);' /> ";
        }
        if(showFolders_UserDivId != 1){
            html += " <input type='button' value='Remove User'  onclick='javascript:RemoveUserDiv("+showFolders_UserDivId+");' /><br/>"
        }
        $(".UserDivs").append(html);
                 
    }
    function RemoveUserDiv(userDivId){      
       $("#"+ userDivId +"_AddUserDiv").remove();
    }
    
    function AddFolderDiv(allowDelete){      
        showFolders_UserDivId++; 
        var folderDropDown = getDropDown(showFolders_FoldersJSON,"userSelect");
        var html = "<Div class='AddUserDiv' id='"+ showFolders_UserDivId +"_AddUserDiv' style='clear:both;padding:2px 2px 2px 2px;'>";
        html += "Select Folder: "+ folderDropDown;
        html += " Select Permission: "+ getPermissionsDropDown();
        if(showFolders_UserDivId == 1){
            html += " <input type='button' value='Add Folder' onclick='javascript:AddFolderDiv(true);' /> ";
        }
        if(showFolders_UserDivId != 1){
            html += " <input type='button' value='Remove Folder'  onclick='javascript:RemoveUserDiv("+showFolders_UserDivId+");' /><br/>"
        }
        $(".UserDivs").append(html);
                 
    }
    
   
   
    function buildShowUsersDialog(isFolder){
            $('#showUsersDialog').dialog({
                autoOpen: false,
                width: 600,
                height: 300,
                buttons: {
                    "Ok": function() {
                    if(isFolder){ 
                        saveUserFolders();
                    }else{
                        saveFoldersPermissions()
                    }
                    }, 
                    "Close": function() { 
                        $(this).dialog("close"); 
                    } 
                },
                open: function(event, ui){
                     $("#showFolders_loadingAjax").show();
                     if(isFolder){                        
                         getUsersJSONByFolder();
                     }else{                         
                         getFoldersJSONBUser();                        
                     }
                       $(".UserDivs").html("");  
                      showFolders_UserDivId = 0;                 
                }
            });
    }
    
    function showUsers(folderSeq,dialogtitle){
       $(".msg").html("");
       $("#folderSeqSelected").html(folderSeq);
       $('#showUsersDialog').dialog('option', 'title', dialogtitle);       
       $('#showUsersDialog').dialog("open");
    }
    function getDropDown(keyValueArray,$selectName){
      var html = "<select name="+$selectName+" class='"+ $selectName +"'>";
      $.each(keyValueArray, function(index, value){
          if(index != 0 && value != null) {
            html+= "<option value='"+ index +"'>"+ value +"</option>";
          }
      });
      html += "</select>"
      return html;
    }
    function getPermissionsDropDown(){
       var html = "<select name='permissionSelect' class='permissionSelect'>";
       html+= "<option value='user'>User</option>";
       html+= "<option value='manager'>Manager</option>"; 
       html+= "</select>";
       return html;  
    }
    
    function buildFoldersJson(){
        $.getJSON("ajaxCalls.php?action=getAllFolders", function(json){
                var array = new Array(); 
                $.each(json, function(index,value){
                    array[index]= value.location;
                });
                showFolders_FoldersJSON = array;                  
        });
    }
    function buildUsersJson(){
       $.getJSON("ajaxCalls.php?action=getAllUsers", function(json){
                var array = new Array(); 
                $.each(json, function(index,value){
                    array[index]= value.username;
                });
                showFolders_UsersJSON = array;                  
        });
    }
    
    function saveUserFolders(isFolder){
         $(".msg").html("");
        $("#showFolders_loadingAjax").show();
        var userDivs = $(".AddUserDiv");
        usersArray = new Array();
        permissionsArray = new Array();
        jsonStr = "userData:[{";
        $.each(userDivs, function(index,value){
            userDiv = value;  
            userSelected = $(userDiv)[0].children[0].value;
            permissionSelected = $(userDiv)[0].children[1].value;
            usersArray[index] = userSelected;
            permissionsArray[index] = permissionSelected;
        });
        var folderSeq = $("#folderSeqSelected")[0].innerHTML;       
        $.getJSON("ajaxCalls.php?action=saveFolderUsers&folderSeq="+folderSeq, { "users": usersArray, "permissions": permissionsArray }, function(json){
            $("#showFolders_loadingAjax").hide();  
            $(".msg").html(json);
            
        });
    }
     function saveFoldersPermissions(){
      $(".msg").html("");
        $("#showFolders_loadingAjax").show();
        var userDivs = $(".AddUserDiv");
        usersArray = new Array();
        permissionsArray = new Array();
        jsonStr = "userData:[{";
        $.each(userDivs, function(index,value){
            userDiv = value;  
            folderSelected = $(userDiv)[0].children[0].value;
            permissionSelected = $(userDiv)[0].children[1].value;
            usersArray[index] = folderSelected;
            permissionsArray[index] = permissionSelected;
        });
        var userSeq = $("#folderSeqSelected")[0].innerHTML;       
        $.getJSON("ajaxCalls.php?action=savePermissions&userSeq="+userSeq, { "folders": usersArray, "permissions": permissionsArray }, function(json){
            $("#showFolders_loadingAjax").hide();  
            $(".msg").html(json);
            
        });
    }
    
    function getUsersJSONByFolder(){
        var folderSeq = $("#folderSeqSelected")[0].innerHTML;
        $.getJSON("ajaxCalls.php?action=getUsersByFolder&folderSeq="+folderSeq, function(json){
         $("#showFolders_loadingAjax").hide(); 
            if(json.length != 0){
                $.each(json, function(index,value){
                    AddUserDiv(true);
                    $("#"+showFolders_UserDivId +"_AddUserDiv .userSelect").val(value.userseq);
                    $("#"+showFolders_UserDivId +"_AddUserDiv .permissionSelect").val(value.permission);
                });
            }else{
               AddUserDiv(false);
            }
        });
    }
    function getFoldersJSONBUser(){
        var userSeq = $("#folderSeqSelected")[0].innerHTML; 
        $.getJSON("ajaxCalls.php?action=getFoldersByUser&userSeq="+userSeq, function(json){
         $("#showFolders_loadingAjax").hide(); 
            if(json.length != 0){
                $.each(json, function(index,value){
                    AddFolderDiv(true);
                    $("#"+showFolders_UserDivId +"_AddUserDiv .userSelect").val(value.folderseq);
                    $("#"+showFolders_UserDivId +"_AddUserDiv .permissionSelect").val(value.permission);
                });
            }else{
               AddFolderDiv(false);
            }
        });
        
    }