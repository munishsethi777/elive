<?php
    
	session_start();
	if($_SESSION["adminlogged"]=="")
	{
	 header("location: index.php"); 
	}	
?>