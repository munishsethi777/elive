<?php
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/UserAction.php");
  require_once($ConstantsArray['dbServerUrl'] ."enums/UserActionType.php");
  require_once($ConstantsArray['dbServerUrl'] ."enums/UserActionURLType.php");

  class UserActionDataStore{
    
    private static $db;
    private static $userActionDataStore;
    private Static $INSERT = "INSERT INTO `useractions` (userseq,dated,actionname,actionvalue,ipaddress) VALUES(:userseq,:dated,:actionname,:actionvalue,:ipaddress)";
    public function __construct(){
        self::$db = MainDB::getInstance();
    }
    public static function getInstance(){
        if (!self::$userActionDataStore)
        {
            self::$userActionDataStore = new UserActionDataStore();
            return self::$userActionDataStore;
        }
        return self::$userActionDataStore;
    }

    public function saveAction($userAction)
    {
        $SQL = self::$INSERT;
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($SQL);
        $stmt->bindValue(':userseq', $userAction->getUserSeq());
        $dateTime = new DateTime();
        $stmt->bindValue(':dated', $dateTime->format('Y-m-d H:i:s'));
        $stmt->bindValue(':actionname', $userAction->getActionName());
        $stmt->bindValue(':actionvalue', $userAction->getActionValue());
        $stmt->bindValue(':ipaddress', $userAction->getIPAddress());
        $stmt->execute();
        $error = $stmt->errorInfo();
    }

    public function saveURLOpenAction($url,$userSeq){
        $userAction = new UserAction();
        $userAction->setUserSeq($userSeq);
        $userAction->setActionName(UserActionType::openurl);
        $userAction->setActionValue($url);
        $userAction->setIpAddress($_SERVER['REMOTE_ADDR']);
        $this->saveAction($userAction);
    }
    public function saveFetchParametersAction($params,$userSeq){
        $userAction = new UserAction();
        $userAction->setUserSeq($userSeq);
        $userAction->setActionName(UserActionType::fetchparameters);
        $userAction->setIpAddress($_SERVER['REMOTE_ADDR']);
        $paramsArr = array();
        
        foreach($params as $param){
            if(strpos($param, "<br>Pres Limit")){
                //$param = Ajnala<br>BOD-mg/l<br>Pres Limit - 100
                $param = substr($param,0,strripos($param,"<br>"));
            }
            array_push($paramsArr , str_replace("<br>", " - ", $param));
        }
        $userAction->setActionValue(implode(" | ", $paramsArr));
        $this->saveAction($userAction);
    }
    
    public function getLogsByFromToDates($fromDate,$toDate,$managerSeq){
        $sql = "select ua.dated,ua.ipaddress,user.emailid,user.username,ua.actionname,ua.actionvalue from useractions ua
                join user on ua.userseq = user.seq
                join locationusers on locationusers.userseq = user.seq
                where locationusers.locationseq in
                (select lu.locationseq from locationusers lu where lu.userseq = $managerSeq)
                and ua.dated <= :toDate and ua.dated >= :fromDate GROUP by ua.seq order by ua.dated ASC ";
        
        $conn = self::$db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':toDate', $toDate);
        $stmt->bindValue(':fromDate', $fromDate);
        
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        if($rows == FALSE){
            return null;
        }
        $responseArr = array();
        foreach($rows as $rowStd){
            $row = get_object_vars($rowStd);
            if($row['actionname'] == UserActionType::openurl){
                $row['actionvalue'] = UserActionURLType::getValue($row['actionvalue']);
            }
            array_push($responseArr, $row);
        }
        return $responseArr;
    }
    
    
    public function populateObject($rsItem)
    {
        $seq_ = $rsItem["seq"];
        $userseq_ = $rsItem["userseq"];
        $dated_ = $rsItem["dated"];
        $actionname_ = $rsItem["actionname"];
        $actionvalue_ = $rsItem["actionvalue"];
        
        $userAction = new UserAction();
        $userAction->setSeq($seq_);
        $userAction->setUserSeq($userseq_);
        $userAction->setDated($dated_);
        $userAction->setActionName($actionname_);
        $userAction->setActionValue($actionvalue_);
        return $user;
    }

}



?>
