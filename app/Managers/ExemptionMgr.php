<?php 

    Class ExemptionMgr{
        private static $exemptionMgr;
        public static function getInstance(){
            if (!self::$exemptionMgr)
            {
                self::$exemptionMgr = new ExemptionMgr();
                return self::$exemptionMgr;
            }
            return self::$exemptionMgr;        
        }
        public function saveExemptionMaster($GET){
            $fromDate = new DateTime($GET['fromDate']);
            $toDate = new DateTime($GET['toDate']);

            $exemption = new Exemption();
            $exemption->setDated(date('Y-m-d H:i:s'));
            $exemption->setFromDateRange($fromDate->format('Y-m-d H:i:s'));
            $exemption->setToDateRange($toDate->format('Y-m-d H:i:s'));
            $exemption->setFolderSeq($GET['stationsSelect']);
            $exemption->setComments($GET['comments']) ;
            $exemption->setIsExemption(false);
            if($GET['isExemption'] == "on"){
                $exemption->setIsExemption(true);   
            }
            $EDS = ExemptionDataStore::getInstance();
            $lastId = $EDS->SaveExemptionMaster($exemption);
            
            $chNosArr =  $GET['channelNos'];
            if($chNosArr != null){
                foreach($chNosArr as $key => $val){
                    $exemptionDetail = new ExemptionDetail();
                    $exemptionDetail->setExemptionSeq($lastId);
                    $exemptionDetail->setChannelNumber($val);
                    $EDS->SaveExemptionDetail($exemptionDetail);  
                }
            }
            return array("RESPONSE"=>"SUCCESS");
        }
        public function saveExemptionComment($GET){

            $obj = new ExemptionComment();
            $obj->setExemptionSeq($GET['commentMasterSeq']);
            $obj->setUserSeq($GET['userSeq']);
            $obj->setComments($GET['newComments']);
            $EDS = ExemptionDataStore::getInstance();
            $EDS->SaveExemptionComment($obj);
            return array("RESPONSE"=>"SUCCESS");
        }
        public function findByLocationSeqJSON($locationSeq,$isExemption){
            $EDS = ExemptionDataStore::getInstance();
            $exemptArr = $EDS->FindExemptionsByLocation($locationSeq,$isExemption);
            $exmpJSON = self::getJsonData($exemptArr);
            return $exmpJSON;
        }
        
        public function findAllExemptionJSON($isExemption){
            $EDS = ExemptionDataStore::getInstance();
            $exemptArr = $EDS->FindAllExemptions($isExemption);
            $exmpJSON = self::getJsonData($exemptArr);
            return $exmpJSON;
        }
        
        public function findExemptionByFolderChannel(){
            $EDS = ExemptionDataStore::getInstance();
            $exemptArr = $EDS->FindAllExemptions();
            $exmpJSON = self::getJsonData($exemptArr);
            return $exmpJSON;
        }
        
        public function findCommentsByExemptionSeqJSON($eSeq){
            $EDS = ExemptionDataStore::getInstance();
            $exemptCommentsArr = $EDS->FindExemptionCommentsByExemptionSeq($eSeq);
            $jsonData = self::getCommentsJsonData($exemptCommentsArr);
            return $jsonData;        
        }
        public function deleteExemptionBySeq($seq){
            $EDS = ExemptionDataStore::getInstance();
            return $EDS->deleteExemptionBySeq($seq);  
        }
        public function approveExemption($seq, $flag){
            $EDS= ExemptionDataStore::getInstance();
            return $EDS->approveExemptionBySeq($flag, $seq);   
        }
        private static function getJsonData($exemptions){
            $fullArr = array();
            if($exemptions != null){
                foreach($exemptions as $exemption){
                    $exm = new Exemption();
                    $exm = $exemption;
                    $arr = array();
                    $arr['seq'] = $exm->getSeq();
                    $arr['folderSeq'] = $exm->getFolderSeq();
                    $arr['folderName'] = $exm->getFolderName();
                    $arr['fromDate'] = $exm->getFromDateRange();
                    $arr['toDate'] = $exm->getToDateRange();
                    $arr['comments'] = $exm->getComments();
                    $arr['isApproved'] = $exm->getIsApproved();
                    $arr['approvalComments'] = $exm->getApprovalComments();
                    $arr['approvedOn'] = $exm->getApprovedOn();
                    $arr['dated'] = $exm->getDated( );
                    $arr['locationName'] = $exm->getLocationName();
                    $exemptionDetails = $exm->getExemptionDetails();
                    $chNamesArr = array();
                    if($exemptionDetails != null){
                        foreach($exemptionDetails as $exmDetail){
                            $exemptionDetail  = new ExemptionDetail();
                            $exemptionDetail = $exmDetail;
                            array_push($chNamesArr,$exemptionDetail->getChannelName());  
                        }
                    }
                    $arr['channels'] = $chNamesArr;
                    array_push($fullArr,$arr);        
                }
            }
            return json_encode($fullArr);
        }
        
        private static function getCommentsJsonData($comm){
            $fullArr = array();
            if($comm != null){
                foreach($comm as $comment){
                    $com = new ExemptionComment();
                    $com = $comment;
                    $arr = array();
                    $arr['seq'] = $com->getSeq();
                    $arr['exemptionSeq'] = $com->getExemptionSeq();
                    $arr['dated'] = $com->getDated();
                    $arr['comments'] = $com->getComments();
                    $arr['userName'] = $com->getUserName();
                    $arr['userSeq'] = $com->getUserSeq();
                    array_push($fullArr,$arr);        
                }
            }
            return json_encode($fullArr);
        }
        
    }


?>