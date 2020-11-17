<?php
require_once($ConstantsArray['dbServerUrl'] ."Enums/BasicEnum.php");
class UserActionURLType extends BasicEnum{
	const map = "MAP";
	const cpcbMap = "AQMS";
	const cpcbStackReportMultiStation = "STACK";
	const cpcbEffluentReportMultiStation = "EFFLUENT";
	const cpcbValidationExemptionsComments = "Data Validations and Comments";
	const cpcbExemptions = "Exemptions";
	const remoteCalibration = "Remote Calibration";
	const logout = "Logout";
}