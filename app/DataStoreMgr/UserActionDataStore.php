<?php
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/UserAction.php");
  require_once($ConstantsArray['dbServerUrl'] ."enums/UserActionType.php");
  

  class UserActionDataStore{
    
    private static $db;
    private static $userActionDataStore;
    private Static $INSERT = "INSERT INTO `useractions` (userseq,dated,actionname,actionvalue) VALUES(:userseq,:dated,:actionname,:actionvalue)";
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
        $stmt->execute();
        $error = $stmt->errorInfo();
    }

    public function saveURLOpenAction($url,$userSeq){
        $userAction = new UserAction();
        $userAction->setUserSeq($userSeq);
        $userAction->setActionName(UserActionType::openurl);
        $userAction->setActionValue($url);
        $this->saveAction($userAction);
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
