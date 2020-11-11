<?php
    require_once('IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/Comment.php");
    require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/CommentDetails.php");

    class CommentsDataStore{
        private static $commentsDataStore;
        private static $db;
        private static $INSERT = "INSERT INTO comment (folderseq,channelnumber,fromdaterange,todaterange,startedon,lastupdatedon,request) VALUES(:folderseq,:channelnumber,:fromdaterange,:todaterange,:startedon,:lastupdatedon,:request)";

        private static $INSERTDETAILS = "insert into commentdetails(commentseq, dated, commentsuser, comments)
values(:commentseq, now(), :commentsuser, :comments);";
        

        private static $SELECTALL = "SELECT comment.seq,comment.fromdaterange,comment.todaterange,comment.startedon,
        comment.lastupdatedon,comment.folderseq,comment.channelnumber,folder.foldername,channelconfiguration.channelname,comment.request
        FROM comment, folder, channelconfiguration WHERE comment.folderseq = folder.seq
        and channelconfiguration.channelnumber = comment.channelnumber
        and channelconfiguration.folderseq =comment.folderseq";

        private static $SELECTDETAILS_BY_COMMENTSEQ = "SELECT commentdetails.*, user.username from commentdetails left join user on commentdetails.commentsuser = user.seq where commentdetails.commentseq = :seq";

        private static $DELETE = "delete from comment where seq = :seq";
        private static $DELETEDETAILS = "delete from commentdetails where seq = :seq";

        private static $FIND_BY_SEQ = "select * from comment where seq = :seq";


        public function __construct(){
            self::$db = MainDB::getInstance();
        }

        public static function getInstance(){
            if (!self::$commentsDataStore)
            {
                self::$commentsDataStore = new CommentsDataStore();
                return self::$commentsDataStore;
            }
            return self::$commentsDataStore;
        }

        public function SaveCommentMaster(Comment $comment){
            try{
                $SQL = self::$INSERT;
                $conn = self::$db->getConnection();
                $stmt = $conn->prepare($SQL);

                $stmt->bindValue(':folderseq', $comment->getFolderSeq());
                $stmt->bindValue(':channelnumber', $comment->getChannelNumber());
                $stmt->bindValue(':fromdaterange',$comment->getFromDateRange());
                $stmt->bindValue(':todaterange',$comment->getToDateRange());
                $stmt->bindValue(':startedon',$comment->getStartedOn());
                $stmt->bindValue(':lastupdatedon',$comment->getLastUpdatedOn());
                $stmt->bindValue(':request',$comment->getRequest());

                $stmt->execute();
                $error = $stmt->errorInfo();

            }catch(Exception $e){
                $e;
            }
        }

        public function SaveCommentDetail(CommentDetails $commentDetails){
            try{
                $SQL = self::$INSERTDETAILS;
                $conn = self::$db->getConnection();
                $stmt = $conn->prepare($SQL);
                $stmt->bindValue(':commentseq', $commentDetails->getCommentSeq());
                $stmt->bindValue(':commentsuser', $commentDetails->getCommentsUser());
                $stmt->bindValue(':comments',$commentDetails->getComments());
                //$stmt->bindValue(':isprivate',$commentDetails->getIsPrivate());

                $stmt->execute();
                $error = $stmt->errorInfo();

            }catch(Exception $e){
                $e;
            }
        }

        public function FindAllCommentsMaster($locationSeq){
            $sql = self::$SELECTALL;
            if($locationSeq != ""){
                $sql .= " and folder.locationseq in( ". $locationSeq .")";
            }
            $sql .= " order by seq DESC";
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = new Comment();
                $comment =  self::populateObject($row);
                $commentArray[$comment->getSeq()] = $comment;
            }
            return $commentArray;
        }
        public function FindCommentsMasterByFolChannel($folSeq, $chNoArr){

            $conn = self::$db->getConnection();
            $stmt = $conn->prepare("select * from comment where folderseq=". $folSeq ." and channelnumber in(". implode($chNoArr,",") .")");
            $stmt->execute();
            $folderArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = new Comment();
                $comment =  self::populateObject($row);
                $commentArray[$comment->getSeq()] = $comment;
            }
            return $commentArray;
        }
        public function FindCommentsDetailByCommentSeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECTDETAILS_BY_COMMENTSEQ);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = new CommentDetails();
                $comment =  self::populateDetailObject($row);
                $commentArray[$comment->getSeq()] = $comment;
            }
            return $commentArray;
        }
         public function CommentsDetailCountByCommentSeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$SELECTDETAILS_BY_COMMENTSEQ);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $count = $stmt->rowCount();
            return $count;
        }

        public function deleteCommentMasterBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$DELETE);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            return(assert($stmt->errorCode() === '00000'));
            //this will return true if no err
        }
        public function deleteCommentDetailsBySeq($seq){
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare(self::$DELETEDETAILS);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            return(assert($stmt->errorCode() === '00000'));
            //this will return true if no err
        }


        public static function populateObject($row){
            $comment = new Comment();
            $comment->setSeq($row['seq']);
            $comment->setFolderSeq($row['folderseq']);
            $comment->setChannelNumber($row['channelnumber']);
            $comment->setFromDateRange($row['fromdaterange']);
            $comment->setToDateRange($row['todaterange']);
            $comment->setStartedOn($row['startedon']);
            $comment->setLastUpdatedOn($row['lastupdatedon']);
            $comment->setFolderName($row['foldername']);
            $comment->setChannelName($row['channelname']);
            $comment->setRequest($row['request']);

            return $comment;
        }
        public static function populateDetailObject($row){
            $commentDetail = new CommentDetails();
            $commentDetail->setSeq($row['seq']);
            $commentDetail->setCommentSeq($row['commentseq']);
            $commentDetail->setDated($row['dated']);
            $commentDetail->setComments($row['comments']);
            $userName = $row['username'];
            if($userName == null){
                $userName = "-";
            }
            $commentDetail->setCommentsUser($userName);
            $commentDetail->setIsPrivate($row['isprivate']);
            return $commentDetail;
        }
    }

?>
