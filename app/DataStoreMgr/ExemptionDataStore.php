<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/MainDB.php"); 
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/Exemption.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/ExemptionDetail.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/ExemptionComment.php");
    

    class ExemptionDataStore{ 
        private static $exemptionDataStore;
        private static $db;
        private static $INSERT = "INSERT INTO exemption (folderseq,dated,fromdate,todate,userseq,isapproved,approvedon,comments,approvalcomments,isexemption) VALUES(:folderseq,:dated,:fromdate,:todate,:userseq,:isapproved,:approvedon,:comments,:approvalcomments,:isExemption)";
        
        private static $INSERTDETAILS = "insert into exemptiondetails(exemptionseq, channelno)
values(:exemptionseq, :channelno);";

        private static $INSERT_EXEMPTION_COMMENTS = "insert into exemptioncomments(exemptionseq, dated, userseq, comments) values(:exemptionSeq, now(), :userSeq, :comments);";
        
        private static $SELECTALL = "select exemption.*,folder.foldername,location.name as locationname from exemption join folder on exemption.folderseq = folder.seq join location on location.seq = folder.locationseq and exemption.isexemption=:isExemption order by exemption.isapproved";

        private static $SELECT_BY_LOCATION = "select exemption.*,folder.foldername,location.name as locationname from exemption,folder, location where exemption.folderseq = folder.seq and exemption.isexemption=:isExemption and folder.seq in (select seq from folder where locationseq = :locationSeq) and location.seq = folder.locationseq order by exemption.isapproved";
        
        private static $SELECTDETAILS_BY_EXEMPTION = "select channelconfiguration.channelname,exemptiondetails.seq,exemptiondetails.exemptionseq,exemptiondetails.channelno from exemption, exemptiondetails, channelconfiguration where exemptiondetails.channelno = channelconfiguration.channelnumber and exemptiondetails.exemptionseq = exemption.seq and exemption.folderseq = channelconfiguration.folderseq and exemptiondetails.exemptionseq = :seq";

        private static $SELECT_EXEMPTIONS_BY_FOLDER_DATES_CHANNELS = "select exemption.* from exemption,exemptiondetails    where exemptiondetails.exemptionseq = exemption.seq and exemptiondetails.channelno in(:channelNumbers) and exemption.folderseq = :folderSeq AND ((exemption.fromdate >= :fromDate and exemption.todate <= :toDate) OR (exemption.fromdate >= :fromDate and exemption.fromdate <= :toDate) OR (exemption.fromdate <= :fromDate and exemption.todate >= :toDate) OR (exemption.fromdate <= :fromDate and exemption.todate <= :toDate)) and exemption.isapproved = '1' group by seq";
        
        private static $SELECT_COMMENTS_BY_EXEMPTION ="select exemptioncomments.*,user.username from exemptioncomments join user On user.seq = exemptioncomments.userseq where exemptioncomments.exemptionseq = :exemptionSeq order by exemptioncomments.dated DESC";
        
        private static $DELETE = "delete from exemption where seq = :seq"; 
        private static $DELETE_DETAILS = "delete from exemptiondetails where exemptionseq = :exemptionSeq"; 
        
        private static $FIND_BY_SEQ = "select * from exemption where seq = :seq";   
        
        private static $APPROVE = "update exemption set isapproved=:isApprove where seq=:seq";
        public function __construct(){
            self::$db = MainDB::getInstance();       
        }

        public static function getInstance(){
            if (!self::$exemptionDataStore)
            {
                self::$exemptionDataStore = new ExemptionDataStore();           
                return self::$exemptionDataStore;
            }
            return self::$exemptionDataStore;        
        }  

        public function SaveExemptionMaster(Exemption $exemption){
            try{
                $SQL = self::$INSERT;
                $conn = self::$db->getConnection();
                $stmt = $conn->prepare($SQL);

                $stmt->bindValue(':folderseq', $exemption->getFolderSeq()); 
                $stmt->bindValue(':dated',$exemption->getDated());
                $stmt->bindValue(':fromdate',$exemption->getFromDateRange());
                $stmt->bindValue(':todate',$exemption->getToDateRange());
                $stmt->bindValue(':userseq',$exemption->getUserSeq());
                $stmt->bindValue(':isapproved',$exemption->getIsApproved());
                $stmt->bindValue(':approvedon',$exemption->getApprovedOn());
                $stmt->bindValue(':comments',$exemption->getComments());
                $stmt->bindValue(':approvalcomments',$exemption->getApprovalComments());
                $stmt->bindValue(':isExemption',$exemption->getIsExemption());

                $stmt->execute();  
                $error = $stmt->errorInfo();
                if($error[0] == "00000"){
                    return self::$db->getLastInsertedId();   
                }
            }catch(Exception $e){
                $e;  
            }
        }
        
        public function SaveExemptionDetail(ExemptionDetail $exemptionDetails){
            try{
                $SQL = self::$INSERTDETAILS;
                $conn = self::$db->getConnection();
                $stmt = $conn->prepare($SQL);
                $stmt->bindValue(':exemptionseq', $exemptionDetails->getExemptionSeq()); 
                $stmt->bindValue(':channelno', $exemptionDetails->getChannelNumber());
                $stmt->execute();  
                $error = $stmt->errorInfo();
                
            }catch(Exception $e){
                $e;  
            }
        }
        public function SaveExemptionComment(ExemptionComment $exemptionComment){
            try{
                $SQL = self::$INSERT_EXEMPTION_COMMENTS;
                $conn = self::$db->getConnection();
                $stmt = $conn->prepare($SQL);
                $stmt->bindValue(':exemptionSeq', $exemptionComment->getExemptionSeq()); 
                $stmt->bindValue(':comments', $exemptionComment->getComments());
                $stmt->bindValue(':userSeq', $exemptionComment->getUserSeq());
                
                $stmt->execute();  
                $error = $stmt->errorInfo();
                
            }catch(Exception $e){
                $e;  
            }
        } 
        public function FindAllExemptions($isException){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECTALL);
            $stmt->bindValue(':isExemption', $isException);
                
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $exemption = new Exemption();
                $exemption =  self::populateObject($row);
                $exemptionDetailArr = self::FindExemptionDetailByExemptionSeq($exemption->getSeq());
                $exemption->setExemptionDetails($exemptionDetailArr);
                $exemptionArray[$exemption->getSeq()] = $exemption;
            }
            return $exemptionArray;
        }
        public function FindExemptionsByLocation($locationSeq,$isExemption){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECT_BY_LOCATION);
            $stmt->bindValue(':locationSeq', $locationSeq); 
            $stmt->bindValue(':isExemption', $isExemption); 
            
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $exemption = new Exemption();
                $exemption =  self::populateObject($row);
                $exemptionDetailArr = self::FindExemptionDetailByExemptionSeq($exemption->getSeq());
                $exemption->setExemptionDetails($exemptionDetailArr);
                $exemptionArray[$exemption->getSeq()] = $exemption;
            }
            return $exemptionArray;
        }
        public function FindExemptionsByFolderDatesChannels($folderSeq,$fromDate,$toDate,$channels){
            $conn = self::$db->getConnection();
            $SQL = "select exemption.* from exemption,exemptiondetails    where exemptiondetails.exemptionseq = exemption.seq and exemptiondetails.channelno in($channels) and exemption.folderseq = :folderSeq AND ((exemption.fromdate >= :fromDate and exemption.todate <= :toDate) OR (exemption.fromdate >= :fromDate and exemption.fromdate <= :toDate) OR (exemption.fromdate <= :fromDate and exemption.todate >= :toDate) OR (exemption.fromdate <= :fromDate and exemption.todate <= :toDate)) and exemption.isapproved = '1' and exemption.isexemption=1 group by seq";
        
            
            
            $stmt = $conn->prepare($SQL);
            $stmt->bindValue(':folderSeq', $folderSeq); 
            $stmt->bindValue(':fromDate', $fromDate); 
            $stmt->bindValue(':toDate', $toDate); 
            //$stmt->bindValue(':channelNumbers', $channels); 
                    
            $stmt->execute();
            
            $exemptionArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $exemption = new Exemption();
                $exemption =  self::populateObject($row);
                $exemptionDetailArr = self::FindExemptionDetailByExemptionSeq($exemption->getSeq());
                $exemption->setExemptionDetails($exemptionDetailArr);
                $exemptionArray[$exemption->getSeq()] = $exemption;
            }
            return $exemptionArray;
        }
        
        
        public function FindExemptionDetailByExemptionSeq($exemptionSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECTDETAILS_BY_EXEMPTION);
            $stmt->bindValue(':seq', $exemptionSeq); 
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $exemptionDetail = new ExemptionDetail();
                $exemptionDetail =  self::populateDetailObject($row);
                $exemptionDArray[$exemptionDetail->getSeq()] = $exemptionDetail;
            }
            return $exemptionDArray;
        }
        
        public function FindExemptionCommentsByExemptionSeq($exemptionSeq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECT_COMMENTS_BY_EXEMPTION);
            $stmt->bindValue(':exemptionSeq', $exemptionSeq); 
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $obj = new ExemptionComment();
                $obj =  self::populateCommentObject($row);
                $exemptionCArray[$obj->getSeq()] = $obj;
            }
            return $exemptionCArray;
        }

        public function deleteExemptionBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$DELETE);
            $stmt->bindValue(':seq', $seq); 
            $stmt->execute();
            return assert($stmt->errorCode() === '00000');
        }

        public function approveExemptionBySeq($isApprove,$seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$APPROVE);
            $stmt->bindValue(':isApprove', $isApprove); 
            $stmt->bindValue(':seq', $seq); 
            $stmt->execute();
            return assert($stmt->errorCode() === '00000');
        }
        
        
        public static function populateObject($row){
            $exemption = new Exemption();
            $exemption->setSeq($row['seq']);
            $exemption->setFolderSeq($row['folderseq']);
            $exemption->setFolderName($row['foldername']);
            
            $exemption->setDated($row['dated']);
            $exemption->setFromDateRange($row['fromdate']);
            $exemption->setToDateRange($row['todate']);
            $exemption->setUserSeq($row['userseq']);
            $approved = "pending";
            if($row['isapproved'] == '1'){
                $approved = "Yes";  
            }else if($row['isapproved'] == '0'){
                $approved = "No";  
            }
            $exemption->setIsApproved($approved);
            $exemption->setApprovedOn($row['approvedon']);
            $exemption->setComments($row['comments']);
            $exemption->setApprovalComments($row['approvalcomments']);
            $exemption->setLocationName($row['locationname']);
            return $exemption;
        }
        public static function populateDetailObject($row){
            $exemptionDetail = new ExemptionDetail();
            $exemptionDetail->setSeq($row['seq']);
            $exemptionDetail->setExemptionSeq($row['exemptionseq']);
            $exemptionDetail->setChannelNumber($row['channelno']);
            $exemptionDetail->setChannelName($row['channelname']);
            return $exemptionDetail;
        }
        
        public static function populateCommentObject($row){
            $obj = new ExemptionComment();
            $obj->setSeq($row['seq']);
            $obj->setExemptionSeq($row['exemptionSeq']);
            $obj->setComments($row['comments']);
            $obj->setDated($row['dated']);
            $obj->setUserSeq($row['userseq']);
            $obj->setUserName($row['username']);
            return $obj;
        }
    }

?>
