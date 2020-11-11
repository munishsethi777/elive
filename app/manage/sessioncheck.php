<?php
    
	session_start();
    if($_SESSION["managerSession"]=="")
	{
	    header("location: index.php"); 
	}	
?>