<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/BeanDataStore.php");
$ds = new BeanDataStore("WQDData", "wqdaqmspfiledata");
$limit = 2000;
$query = "SELECT wqdfiledataseq
 FROM wqdaqmspfiledata
WHERE (wqdfolderseq, wqdfiledatadated) 
IN (SELECT wqdfolderseq , wqdfiledatadated FROM wqdaqmspfiledata
                           GROUP BY wqdfolderseq , wqdfiledatadated
                          HAVING COUNT(*) > 1)";
$AllSeqsDuplicates = $ds->executeQueryNew($query,false,true);
if(!empty($AllSeqsDuplicates)){
	$AllSeqsDuplicates = array_map(create_function('$o', 'return $o["wqdfiledataseq"];'), $AllSeqsDuplicates);
	echo "Total " . count($AllSeqsDuplicates) . " found<br>";
	$query = "SELECT wqdfiledataseq FROM wqdaqmspfiledata GROUP BY wqdfolderseq, wqdfiledatadated HAVING COUNT(*) > 1";
	$seqsHasDuplicates = $ds->executeQueryNew($query,false,true);
	$seqsHasDuplicates = array_map(create_function('$o', 'return $o["wqdfiledataseq"];'), $seqsHasDuplicates);
	$seqsToRemove = array_diff($AllSeqsDuplicates, $seqsHasDuplicates);
	$first100Seqs = array_slice($seqsToRemove,0,$limit);
	$first100SeqsStr = implode(",", $first100Seqs);
//	$deleteQuery = "delete from wqdaqmspfiledata where wqdfiledataseq in ($first100SeqsStr)";
//	$flag = $ds->deleteWithQuery($deleteQuery);
	if($flag){
		echo "<br>seqs - $first100SeqsStr deleted successfully";
	}
}else{
	echo "No duplicate row found";
}
