<?php 

    Class CommentsMgr{
        private static $commentsMgr;
        public static function getInstance(){
            if (!self::$commentsMgr)
            {
                self::$commentsMgr = new CommentsMgr();
                return self::$commentsMgr;
            }
            return self::$commentsMgr;
        }
        public function saveCommentsMaster($GET){
            $fromDate = new DateTime($GET['fromDate']);
            $toDate = new DateTime($GET['toDate']);

            $comment = new Comment();
            $comment->setFolderSeq($GET['stationsSelect']);
            $comment->setChannelNumber($GET['pollutantSelect']);
            $comment->setFromDateRange($fromDate->format('Y-m-d H:i:s'));
            $comment->setToDateRange($toDate->format('Y-m-d H:i:s'));
            $comment->setStartedOn(date('Y-m-d H:i:s'));
            $comment->setLastUpdatedOn(date('Y-m-d H:i:s'));
            $CDS = CommentsDataStore::getInstance();
            $CDS->SaveCommentMaster($comment);
            return array("RESPONSE"=>"SUCCESS");
        }
        public function saveCommentsDetail($GET,$userSeq){

            $commentDetail = new CommentDetails();
            $commentDetail->setCommentSeq($GET['commentMasterSeq']);
            $commentDetail->setCommentsUser($userSeq);
            $commentDetail->setIsPrivate($GET['isPrivate']);
            $commentDetail->setComments($GET['newComments']);
            $CDS = CommentsDataStore::getInstance();
            $CDS->SaveCommentDetail($commentDetail);
            return array("RESPONSE"=>"SUCCESS");
        }
        public function findAllCommentsMasterJSON($locationSeq){
            $CDS = CommentsDataStore::getInstance();
            $commentsArr = $CDS->FindAllCommentsMaster($locationSeq);
            $commentsJSON = self::getMasterJsonData($commentsArr);
            return $commentsJSON;
        }
        public function deleteCommentsMasterBySeq($seq){
            $CDS = CommentsDataStore::getInstance();
            return $CDS->deleteCommentMasterBySeq($seq);
        }
        public function deleteCommentsDetailsBySeq($seq){
            $CDS = CommentsDataStore::getInstance();
            return $CDS->deleteCommentDetailsBySeq($seq);
        }
        public function findAllCommentsDetailJSON($seq){
            $CDS = CommentsDataStore::getInstance();
            $commentsArr = $CDS->FindCommentsDetailByCommentSeq($seq);
            $commentsJSON = self::getDetailsJsonData($commentsArr);
            return $commentsJSON;

        }


        //New api from new industry common interface to validate/exempt/record
        public function saveValidationOrExeptionMaster($GET){
            $fromDate = new DateTime($GET['fromDate']);
            $toDate = new DateTime($GET['toDate']);

            $comment = new Comment();
            $comment->setFolderSeq($GET['stationsSelect']);
            $comment->setChannelNumber($GET['pollutantSelect']);
            $comment->setFromDateRange($fromDate->format('Y-m-d H:i:s'));
            $comment->setToDateRange($toDate->format('Y-m-d H:i:s'));
            $comment->setStartedOn(date('Y-m-d H:i:s'));
            $comment->setLastUpdatedOn(date('Y-m-d H:i:s'));
            $comment->setRequest($GET['request']);
            if($GET['request'] == "exemption"){
                self::saveExemption($GET);
            }
            $CDS = CommentsDataStore::getInstance();
            $CDS->SaveCommentMaster($comment);
            return array("RESPONSE"=>"SUCCESS");
        }
        //new function for temp usage for saving exemption in exm table also.
        private static function saveExemption($GET){
            $fromDate = new DateTime($GET['fromDate']);
            $toDate = new DateTime($GET['toDate']);

            $exemption = new Exemption();
            $exemption->setDated(date('Y-m-d H:i:s'));
            $exemption->setFromDateRange($fromDate->format('Y-m-d H:i:s'));
            $exemption->setToDateRange($toDate->format('Y-m-d H:i:s'));
            $exemption->setFolderSeq($GET['stationsSelect']);
            $exemption->setIsExemption(true);
            $EDS = ExemptionDataStore::getInstance();
            $lastId = $EDS->SaveExemptionMaster($exemption);

            $chNo =  $GET['pollutantSelect'];
            $exemptionDetail = new ExemptionDetail();
            $exemptionDetail->setExemptionSeq($lastId);
            $exemptionDetail->setChannelNumber($chNo);
            $EDS->SaveExemptionDetail($exemptionDetail);
            return array("RESPONSE"=>"SUCCESS");
        }
        private static function getMasterJsonData($comm){
            $fullArr = array();
            if($comm != null){
                $cds = CommentsDataStore::getInstance();
                foreach($comm as $comment){
                    $com = new Comment();
                    $com = $comment;
                    $arr = array();
                    $arr['seq'] = $com->getSeq();
                    $arr['channelNumber'] = $com->getChannelNumber();
                    $arr['folderSeq'] = $com->getFolderSeq();
                    $arr['channelName'] = $com->getChannelName();
                    $arr['folderName'] = $com->getFolderName();
                    $arr['fromDateRange'] = $com->getFromDateRange();
                    $arr['toDateRange'] = $com->getToDateRange();
                    $arr['commentDetailCount'] = $cds->CommentsDetailCountByCommentSeq($com->getSeq());
                    $arr['lastUpdatedOn'] = $com->getLastUpdatedOn();
                    $arr['request'] = ucfirst($com->getRequest());
                    array_push($fullArr,$arr);
                }
            }
            return json_encode($fullArr);
        }
        private static function getDetailsJsonData($comm){
            $fullArr = array();
            if($comm != null){
                foreach($comm as $comment){
                    $com = new CommentDetails();
                    $com = $comment;
                    $arr = array();
                    $arr['seq'] = $com->getSeq();
                    $arr['commentSeq'] = $com->getCommentSeq();
                    $arr['dated'] = $com->getDated();
                    $arr['comments'] = $com->getComments();
                    $arr['user'] = $com->getCommentsUser();
                    $arr['isPrivate'] = $com->getIsPrivate();
                    array_push($fullArr,$arr);
                }
            }
            return json_encode($fullArr);
        }



    }


?>