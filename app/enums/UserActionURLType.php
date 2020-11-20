<?php
require_once($ConstantsArray['dbServerUrl'] ."Enums/BasicEnum.php");
class UserActionURLType extends BasicEnum{
	const map = "MAP";
	const cpcbMap = "MAP";
	const cpcbReportMultiStation = "AQMS Report Page";
	const cpcbStackReportMultiStation = "Stack Report Page";
	const cpcbEffluentReportMultiStation = "EFFLUENT Report Page";
	const cpcbValidationExemptionsComments = "Data Validations and Comments";
	const cpcbExemptions = "Exemptions";
	const remoteCalibration = "Remote Calibration";
	const logout = "Logout";
}