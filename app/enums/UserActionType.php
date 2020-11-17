<?php
require_once($ConstantsArray['dbServerUrl'] ."Enums/BasicEnum.php");
class UserActionType extends BasicEnum{
	const login = "Login";
	const logout = "Logout";
	const openurl = "Open URL";
	const fetchstation = "Fetch Station";
	const fetchparameters = "Fetch Parameters";
}