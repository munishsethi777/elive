<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "/Crons/Encryption.php");
$crypt = new Encryption("c2l0ZV85MTcsdmVyXzEuMCxkZWZhdWx0LDIwMTgtMDYtMDctMTE6MzI6MTI=####");
$key = "c2l0ZV85MTcsdmVyXzEuMCxkZWZhdWx0";
//$key = "c2l0ZV85MTcsdmVyXzEuMCxkZWZhdWx0LDIwMTgtMDYtMDctMTE6MzI6MTI=####";
echo "key --- $key<br>";
$test = $crypt->decryptN("IOKkR30CKQRiStxjthWX7zNr/GsKi4AQsJgNc7FU9vGi1ghYInwaX3yEln6ePiUMPJ+6CApaSF/Rth1sRc4h4pg390gk1b0gF0tGdizbd/gFUugwD991wwZSKQjMfFaK0MJ4cH9yz/1aioT7I3pHJO0FiwFgRbntRdgfpJha8z++7teCaYv5R8I6PKgj3H95JhjFLsetOfLN83VIP36fSWWQnbGfsiKdHs1LoocaBmc2P/CC/Pl31WGCkUcrBofQ2NBlZwtQXKEOWlfnS2eC8+gXI7FHlZLIahCoENbuFeFfuN9tRXH33IvBznQNyQWvm+qil2UblcPammkMXXpC92R/pfp/dQ+ruPHVMRaBz8d5BB1F6wh/+8/9jhqVyzs2nuyl2bspjKResJxxdr9Ko/ttVyplWZHPPOZq1EPdfdm24VUAuUiNKTSW9Uh+i82kGe5gDnjOH0sBL+jLeKlmIsQL3sLH3gBj5PW6dvfhGvzxGtbPKAtN+OviZz5nnllOUXjqMoqTjAS5rri/FVZcOA==", $key);
echo $test;