<?php 
   $managerSession = $_SESSION["managerSession"];
   
    $isAdmin = $managerSession["isAdmin"];
    $managerSeq = $managerSession["seq"];
    $page = basename($_SERVER['PHP_SELF']);
    $dashboard = null;
    $showLogs = null;
    $mainManager = null;
    $createManager = null;
    $showManagers = null;
    $mainStation = null;
    $createStation = null; 
    $showStations = null;
    $showM2MSites = null;
    $mainUsers = null;
    $mainLocation = null;
    $createLocation = null;
    $showLocation = null;
    $createUser = null;
    $showUsers = null;
    $mainRules = null;
    $createRule = null;
    $showRules = null;
    $showOccurencies = null;
    $editMeta = null;
    $editParams = null;
    $mainSettings = null;
    $changePassword = null;
    $changeEmail = null;
    $createGroup = null;
    $showGroups = null;
    $managerGroup = null;
    $reports = null;

    if($page == "managerTabs.php"){
        $dashboard = "active";
    }elseif($page == "showFolders.php"){
        $showStations = "active";
    }elseif($page == "createUserForm.php"){
        $mainUsers = "active";
        $createUser = "active";
    }elseif($page == "showUsers.php"){
        $mainUsers = "active";
        $showUsers = "active";
    }elseif($page == "createHighValueRuleForm.php"){
        $mainRules = "active";
        $createRule = "active";
    }elseif($page == "showHighValueRules.php"){
        $mainRules = "active";
        $showRules = "active";
    }elseif($page == "showHighValueOccurences.php"){
        $showOccurencies = "active";
    }elseif($page == "editMeta.php"){
        $editMeta = "active";
    }elseif($page == "editParameter.php"){
        $editParams = "active";
    }elseif($page == "changePassword.php"){
        $mainSettings = "active";
        $changePassword = "active";
    }elseif($page == "changeEmailId.php"){
        $mainSettings = "active";
        $changeEmail = "active";
    }if($page == "dashboard.php"){
        $dashboard = "active";
    }else if($page == "createLocation.php"){
        $createLocation = "active";
        $mainLocation = "active";
    }else if($page == "showLocations.php"){
        $mainLocation = "active";
        $showLocation = "active";
    }
    else if($page == "createFolder.php"){
        $createStation = "active";
        $mainStation = "active";
    } else if($page == "showFolders.php"){
        $showStations = "active";
        $mainStation = "active";
    } else if($page == "createManagerForm.php"){
        $mainManager = "active";
        $createManager = "active";
    } else if($page == "showManagers.php"){
         $mainManager = "active";
         $showManagers = "active";   
    }else if($page == "showLogs.php"){
         $showLogs = "active";    
    }else if($page == "CreateChannelGroup.php"){
    	 $createGroup = "active";
    	 $managerGroup = "active";
    }else if($page == "showChannelGroups.php"){
    	 $showGroups = "active";
    	 $managerGroup = "active";
    }else if($page == "showM2MSites.php"){
    	 $mainStation = "active";
    	 $showM2MSites = "active";
    }else if($page == "generateCPCBReports.php"){
    	 $reports= "active";
    }
?>


<nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element"> <span>
                             </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><? echo $managerSession['username']?></strong>
                             </span> <span class="text-muted text-xs block">Manager</span> </span> </a>
                    </div>
                    <div class="logo-element">
                        ELIVE
                    </div>
                </li>
                <li class="<?=$dashboard?>">
                    <a href="dashboard.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboards</span> <span class="fa arrow"></span></a>
                </li>
                 <?if($isAdmin || $managerSeq == 83){?>
	                <li class="<?=$managerGroup?>">
	               	 	<a href="#"><i class="fa fa-desktop"></i> <span class="nav-label">Manage Sub Station</span><span class="fa arrow"></span></a>
	                 	<ul class="nav nav-second-level collapse">
	                    	<li class="<?=$createGroup?>"><a href="CreateChannelGroup.php">Create Sub Station</a></li>
	                    	<li class="<?=$showGroups?>"><a href="showChannelGroups.php">Show Sub Stations</a></li>                          
	                    </ul>
	                </li>
                <? }?>
                <?if($isAdmin){?>
                    <li class="<?=$showLogs?>"><a href="showLogs.php"><i class="fa fa-outdent"></i> <span class="nav-label">Show Logs</span></a></li>
                    <li class="<?=$mainManager?>">
                        <a href="#"><i class="fa fa-group"></i> <span class="nav-label">Manage Managers</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="<?=$createManager?>"><a href="createManagerForm.php">Create Manager</a></li>
                            <li class="<?=$showManagers?>"><a href="showManagers.php">Show Managers</a></li>
                        </ul>
                    </li>
                    <li class="<?=$mainStation?>">
                        <a href="#"><i class="fa fa-desktop"></i> <span class="nav-label">Manage Stations</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="<?=$createStation?>"><a href="createFolder.php">Create Station</a></li>
                            <li class="<?=$showStations?>"><a href="showFolders.php">Show Stations</a></li>
                            <?if($isAdmin){?>
                            	<li class="<?=$showM2MSites?>"><a href="showM2MSites.php">M2M Sites</a></li>
                            <?php }?>
                        </ul>
                    </li>
                    
                <?}else{?>
                    <li class="<?=$showStations?>"><a href="showFolders.php"><i class="fa fa-desktop"></i> <span class="nav-label">Show Stations</span></a></li>
                <?}?>
                
                <?if($isAdmin){?>
                    <li class="<?=$mainLocation?>">
                        <a href="#"><i class="fa fa-location-arrow"></i> <span class="nav-label">Manage Locations</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="<?=$createLocation?>"><a href="createLocation.php">Create Location</a></li>
                            <li class="<?=$showLocation?>"><a href="showLocations.php">Show Locations</a></li>
                        </ul>
                    </li>    
                <?}?>
                 
                <li class="<?=$mainUsers?>">
                    <a href="#"><i class="fa fa-group"></i> <span class="nav-label">Manage Users</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li class="<?=$createUser?>"><a href="createUserForm.php">Create new User</a></li>
                        <li class="<?=$showUsers?>"><a href="showUsers.php">Show Users</a></li>
                    </ul>
                </li>

                <li class="<?=$mainRules?>">
                    <a href="#"><i class="fa fa-plug"></i> <span class="nav-label">High Value Rules</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li class="<?=$createRule?>"><a href="createHighValueRuleForm.php">Create new Rule</a></li>
                        <li class="<?=$showRules?>"><a href="showHighValueRules.php">Show Rules</a></li>
                    </ul>
                </li>
                <li class="<?=$showOccurencies?>">
                    <a href="showHighValueOccurences.php"><i class="fa fa-bell-o"></i><span class="nav-label">High Value Occurences</span></a>
                </li>
                <li class="<?=$editMeta?>"><a href="editMeta.php"><i class="fa fa-building-o"></i> <span class="nav-label">Edit Meta Information</span></a></li>
                <li class="<?=$editParams?>">
                    <a href="editParameter.php"><i class="fa fa-cog"></i> <span class="nav-label">Edit Parameters</span></a>
                </li>
                <li class="<?=$mainSettings?>">
                    <a href="#"><i class="fa fa-cogs"></i> <span class="nav-label">Settings</span>  <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li class="<?=$changePassword?>"><a href="changePassword.php">Change Password</a></li>
                        <li class="<?=$changeEmail?>"><a href="changeEmailId.php">Change Email</a></li>
                    </ul>
                </li>
                <li class="<?=$reports?>">
                    <a href="generateCPCBReports.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Reports</span> </a>
                </li>
            </ul>

        </div>
    </nav>


<div id="page-wrapper" class="gray-bg">
<div class="row border-bottom">
<nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
<div class="navbar-header">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
</div>
    <ul class="nav navbar-top-links navbar-right">
        <li>
            <span class="m-r-sm text-muted welcome-message">Welcome to EnvirotechLive - Realtime Air & Water Monitoring.</span>
        </li>
        <li>
            <a href="logout.php">
                <i class="fa fa-sign-out"></i> Log out
            </a>
        </li>
    </ul>

</nav>
</div>