<?php 
<!DOCTYPE html>
<html>
    <head>
    <? include("_jsAdminInclude.php");?>
    </head>
    <body>
    
    <? include("leftButtons.php");?>
    
    <Div class="rightAdminPanel">
        <? include("logOutButton.php"); ?>
    
         
    <table width="80%" border="0">
      <tr>
        <td class="ui-widget-header" style="padding:10px 10px 10px 10px;">Enter New User </td>
        </tr>
      <tr>
        <td class="ui-widget-content">
        <table width="60%" border="0" style="padding:10px 10px 10px 10px;float:left">
          <tr>
            <td width="22%">User Full Name </td>
            <td width="78%"><input name="folderLocation" type="text" size="50">
              &nbsp;</td>
          </tr>
          <tr>
            <td>Enter Username </td>
            <td><input name="folderLocation" type="text" size="50">
              &nbsp;</td>
          </tr>
          <tr>
            <td>Enter Password </td>
            <td><input name="folderLocation" type="text" size="50">
              &nbsp;</td>
          </tr>
          <tr>
            <td>Enter Email id </td>
            <td><input name="folderLocation" type="text" size="50">
              &nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
             
                <input type="submit" name="Submit" value="Submit">
                <input type="reset" name="Reset" value="Reset">
            </form>
            </td>
          </tr>
        </table>
        <table width="40%" height="100%" class="ui-widget-content">
            <tr><td>
                <Div class="ui-widget-header" style="padding:5px 5px 5px 5px;">Grant Permissions to Various Location Folders</Div>
                
                <Div class="FolderDivs">
                    <Div class="AddFolderDiv" id="1_AddFolderDiv" style="clear:both">
                        Location:
                        <select name="locations" class="locations">
                            <option>Delhi</option>
                            <option>Gurgaon</option> 
                        </select>
                        Permission:
                        <select name="permissions" class="permissions">
                            <option>Manager</option>
                            <option>User</option> 
                        </select>
                    </Div>
                   
                </Div> 
                  <a href="javascript:AddMoreFolderDiv();">Add More</a>
        
        
            </td></tr>
        </table>
        
        
        </td>
        </tr>
    </table>

    
    
    
    
    </Div>
     
    </body>
</html>


