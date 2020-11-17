<?php
  require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
  require_once($ConstantsArray['dbServerUrl'] . "SecurityUtil/SecurityUtil.php");
  require_once($ConstantsArray['dbServerUrl'] . "Utils/FilterUtil.php");
  require_once($ConstantsArray['dbServerUrl'] . "SecurityUtil/SecurityUtil.php");


  class UserDataStore{

    private static $userDataStore;
    private static $db;
    private static $db_New;
    private static $SELECTALL = "select * from user" ;
    private static $FIND_LOCATION_USERS = "select * from locationusers where userseq= :userseq" ;
    private static $SELECTALLMANAGERS = "select user.*,location.name as locationName from user,location where user.ismanager = 1 and user.locationseq = location.seq" ;
    private static $SELECTALLMANAGERSBYLOCATION = "select user.*,location.name as locationName from user,location where user.ismanager = 1 and user.locationseq = location.seq and location.seq = :locSeq" ;
    private static $SELECTALLUSERS = "select * from user where ismanager = 0" ;
    private static $SELECT_ALL_USERS_LOCATION_USERS = "select DISTINCT user.seq,user.* from user inner join locationusers lu on user.seq = lu.userseq where user.ismanager = 0" ;
    
    private static $SELECT_ALL_MANAGERS = "select DISTINCT user.seq,user.* from user inner join locationusers lu on user.seq = lu.userseq where user.ismanager = 1" ; 
    private static $SELECT_MANAGER_USERNAME_PASSWORD = "select * from user where ismanager = 1 and username=:username and password=:password" ;
    private static $FIND_BY_SEQ = "select * from user where seq = :seq";
    private Static $INSERT = "INSERT INTO `user` (fullname,username,password,emailid,dateofregistration,isactive,locationseq,folderseq,ismanager) VALUES(:fullname, :username, :password, :emailid, :dateofregistration, :isactive, :locationseq, :folderseq, :ismanager)";

    private static $UPDATE = "UPDATE user set fullname = :fullname , username = :username , password = :password , emailid = :emailid , dateofregistration = :dateofregistration , isactive = :isactive, locationseq=:locationseq,folderseq=:folderseq,ismanager =:ismanager where seq = :seq";
    private static $DELETE_LOCATION_USERS = "delete from locationusers where userseq = :userseq";
    private static $FIND_SEQ = "select seq from user where username = :username";
    private static $FIND_BY_USERNAME = "select * from user where username = :username";
    private static $DELETE = "delete from user where seq = :seq";
    Private Static $UPDATE_PASSWORD = "update user set password = :password where seq= :seq";
    Private Static $UPDATE_EMAIL = "update user set emailid = :email where seq= :seq";
    private Static $GET_ALL_FOLDERS = "select f.seq from folder f inner join locationusers lu on f.locationseq  = lu.locationseq where lu.userseq = :userseq";
    private Static $GET_FOLDERS = "select f.*, l.name as locationname from folder f inner join locationusers lu on f.locationseq  = lu.locationseq inner join location l on f.locationseq  = l.seq
where lu.userseq = :userseq";
    private Static $GET_ALL_STATION_TYPE = "select distinct f.stationtype from folder f inner join locationusers lu on f.locationseq  = lu.locationseq where lu.userseq = :userseq";
    private static $INSERT_LOCATION_USER = "insert into locationusers (locationseq,userseq,permission) Values(:locationseq ,:userseq ,:permission)";
    private static $UPDATE_OTP = "UPDATE user set otp=:otp, otpgeneratedate=:otpgeneratedate where seq = :seq";
    
    private static $UPDATE_LOGIN_OTP = "UPDATE user set loginotp=:loginotp, loginotpgeneratedate=:loginotpgeneratedate where seq = :seq";
    public function __construct(){
       self::$db = MainDB::getInstance();
       self::$db_New = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$userDataStore)
        {
            self::$userDataStore = new UserDataStore();
            return self::$userDataStore;
        }
        return self::$userDataStore;
    }

   public function SaveLocationUser($user){
       $SQL = self::$INSERT_LOCATION_USER;
       $conn = self::$db_New->getConnection();
       $stmt = $conn->prepare($SQL);
       $stmt->bindValue(':locationseq', $user->getLocationSeq());
       $stmt->bindValue(':userseq', $user->getSeq());
       $stmt->bindValue(':permission',"user"); 
       $stmt->execute();
       $error = $stmt->errorInfo(); 
   }
   
	public function deleteLocationUsers($userseq){
        $conn = self::$db_New->getConnection();
        $stmt = $conn->prepare(self::$DELETE_LOCATION_USERS);
        $stmt->bindValue(':userseq', $userseq); 
        $stmt->execute();   
    }
    private function saveOtherLocationUser($locationSeqs,$userseq){
        $this->deleteLocationUsers($userseq);
        $conn = self::$db_New->getConnection();
        $stmt = $conn->prepare(self::$INSERT_LOCATION_USER);
        foreach($locationSeqs as $locationSeq){             
            $stmt->bindValue(':locationseq', $locationSeq);
            $stmt->bindValue(':userseq', $userseq);
            $stmt->bindValue(':permission',"user");
            $stmt->execute();
            //i will put the code here for return the error and show on the screen
            $error = $stmt->errorInfo();    
        }
    }
   public function Save(User $user){
      try{
          $SQL = self::$INSERT;
          $isUpdate = false;
          if($user->getSeq() != null && $user->getSeq()<> "" && $user->getSeq() > 0){
             $SQL = self::$UPDATE;
             $isUpdate = true;
          }
          $conn = self::$db_New->getConnection();
          $stmt = $conn->prepare($SQL);

          $stmt->bindValue(':fullname', $user->getFullName());
          $stmt->bindValue(':username', $user->getUserName());
          $stmt->bindValue(':password',$user->getPassword());
          $stmt->bindValue(':emailid',$user->getEmailId());
          $stmt->bindValue(':dateofregistration',$user->getDateOfRegistration());
          $isActive = 0;
          if($user->getIsActive() == "true" || $user->getIsActive()== 1){
             $isActive = 1 ;
          }
          $stmt->bindValue(':isactive', $isActive);
          $isManager = 0;
          if($user->getIsManager() == "true" || $user->getIsManager()== 1){
             $isManager = 1 ;
          }
          $stmt->bindValue(':ismanager', $isManager);
          $stmt->bindValue(':locationseq', $user->getLocationSeq());
          $stmt->bindValue(':folderseq', $user->getFolderSeq());
          if($isUpdate){
            $stmt->bindValue(':seq', $user->getSeq());
            $stmt->execute();
          }else{
            $stmt->execute();
            $id = $conn->lastInsertId(); 
            $user->setSeq($id);
          }
          
          //i will put the code here for return the error and show on the screen
          $error = $stmt->errorInfo();
          if($error[2] <> ""){
                $logger = Logger::getLogger("logger");
                $logger->error("Error occured :" . $error[2]);
                throw new Exception($error[2]);
           }
          $otherLocations = $user->getOtherLocationSeqs();
          if(empty($otherLocations)){
             $otherLocations = array();    
          }
          $locationSeq = $user->getLocationSeq();
          if(!empty($locationSeq) && !in_array($user->getLocationSeq(),$otherLocations)){
            array_push($otherLocations,$user->getLocationSeq());   
          }         
          $this->saveOtherLocationUser($otherLocations,$user->getSeq());
      }catch(Exception $e){
          $logger = Logger::getLogger($ConstantsArray["logger"]);
          $logger->error($e->getMessage());
      }
   }

        public function FindAllUers(){
            $SQL = "select * from users";
            $result = self::$db->query($SQL);
            $usersRS = self::$db->fetch_rows($result);
            if($userRS == null){
                return null;
            }
            $user = new  User();
            //should be array of user
            $user = self::populateObject($userRS);
            return $user;

        }

        public function getUserByEmail($emailid){
              $SQL = "SELECT * from user where useremail = '{$emailid}'";
              $result = self::$db->query($SQL);
              $userRS = self::$db->fetch_rows($result);
                  if($userRS == null){
                    return null;
                  }
              $user = new User();
              $user = self::populateObject($userRS);
              return $user;
          }

          public function getUserByUserName($userName){
            $SQL =  "SELECT * from user where username='".$userName."'";
            $result = self::$db->query($SQL);
            $userRS = self::$db->fetch_rows($result);
                 if($userRS == null){
                     return null;
                 }
            $user = new  User();
            $user = self::populateObject($userRS);
            return $user;
          }

          public function getUserByseq($seq){
            $SQL =  "SELECT * from user where seq=".$seq;
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare($SQL);
            $stmt->execute();

            $UserObj = new User();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $UserObj =  self::populateObject($row);
            }
            return $UserObj;


            $result = self::$db->query($SQL);
            $userRS = self::$db->fetch_rows($result);
                 if($userRS == null){
                     return null;
                 }
            $user = new  User();
            $user = self::populateObject($userRS);
            return $user;
          }

          public function validateUserLogin($username, $password){
          	$password = SecurityUtil::Encode($password);
            $SQL =  "SELECT * from user where username='".$username."' and password='".$password."'";
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare($SQL);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserObj = null;
            if($row){
            	$UserObj =  self::populateObject($row);
          	}
            $error = $stmt->errorInfo();
            return $UserObj;
          }
         public function FindAll(){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$SELECTALL);
            $stmt->execute();
            $userArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $UserObj = new User();
                $UserObj =  self::populateObject($row);
                $userArray[$UserObj->getSeq()] = $UserObj;
            }
            return $userArray;
         }
         public function FindAllManagers(){
            $userArray = Array();
             try{
                $conn = self::$db_New->getConnection();
                $stmt = $conn->prepare(self::$SELECTALLMANAGERS);
                $stmt->execute();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $UserObj = new User();
                    $UserObj =  self::populateObject($row);
                    $userArray[$UserObj->getSeq()] = $UserObj;
                    }
            }catch(Exception $e){
                $a = $e->getMessage();
            }
            return $userArray;
         }
         public function FindAllManagersByLocation($locSeq){
            $userArray = Array();
             try{
                $conn = self::$db_New->getConnection();
                $stmt = $conn->prepare(self::$SELECTALLMANAGERSBYLOCATION);
                $stmt->bindValue(':locSeq', $locSeq);
                $stmt->execute();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $UserObj = new User();
                    $UserObj =  self::populateObject($row);
                    $userArray[$UserObj->getSeq()] = $UserObj;
                    }
            }catch(Exception $e){
                $a = $e->getMessage();
            }
            return $userArray;
         }
         public function FindAllUsers($locSeq){
            $conn = self::$db_New->getConnection();
            if($locSeq != null && $locSeq >0){
                self::$SELECTALLUSERS .= " and locationseq in (".$locSeq.")";
            }
            $stmt = $conn->prepare(self::$SELECTALLUSERS);
            $stmt->execute();
            $userArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $UserObj = new User();
                $UserObj =  self::populateObject($row);
                $userArray[$UserObj->getSeq()] = $UserObj;
            }
            return $userArray;
         }
          private function getTotalCount($sql){
            $conn = self::$db->getConnection();
            $query = FilterUtil::applyFilter($sql,false);
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $count = $stmt->rowCount();
            return $count;  
          } 
         public function FindAllUsersArr($locSeq,$isManager = false){
            $query = self::$SELECT_ALL_USERS_LOCATION_USERS;
            if($isManager){
                $query = self::$SELECT_ALL_MANAGERS;
            }
            $conn = self::$db_New->getConnection();
            if($locSeq != null && $locSeq >0){
                $query .= " and lu.locationseq in (".$locSeq.")";
            }
            $select_query = FilterUtil::applyFilter($query);
            $stmt = $conn->prepare($select_query);
            $stmt->execute();
            $userArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $locationNames = $this->getLocationNamesByUser($row["seq"]);
                $password =  SecurityUtil::Decode($row["password"]);
                $row["password"] = $password;
                $row["name"] =  $row["username"];
                if(count(explode(",",$locSeq)) > 1){
                     $row["username"] .= " (" . implode(", ",$locationNames) . ")";    
                }               
                array_push($userArray,$row);
            }
            $mainArr["Rows"] = $userArray;
            $mainArr["TotalRows"] = $this->getTotalCount($query);
            return $mainArr;
            
         }
         
          public function FindAllUsersByAdmin($isManager = false){
            $query = self::$SELECTALLUSERS;
            if($isManager){
                $query = self::$SELECT_ALL_MANAGERS;
            }
            $conn = self::$db_New->getConnection();
            if($locSeq != null && $locSeq >0){
                $query .= " and lu.locationseq in (".$locSeq.")";
            }
            $select_query = FilterUtil::applyFilter($query);
            $stmt = $conn->prepare($select_query);
            $stmt->execute();
            $userArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $locationNames = $this->getLocationNamesByUser($row["seq"]);
                $password =  SecurityUtil::Decode($row["password"]);
                $row["password"] = $password;
                $row["name"] =  $row["username"];
                if(count(explode(",",$locSeq)) > 1){
                     $row["username"] .= " (" . implode(", ",$locationNames) . ")";    
                }               
                array_push($userArray,$row);
            }
            $mainArr["Rows"] = $userArray;
            $mainArr["TotalRows"] = $this->getTotalCount($query);
            return $mainArr;
            
         }
         public function FindUsersByLocSeqs($locSeqs){
            $conn = self::$db_New->getConnection();
            $SQL = "select * from user where locationseq in ($locSeqs) order by locationseq";
            $stmt = $conn->prepare($SQL);
            $stmt->execute();
            $userArray = Array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $UserObj = new User();
                $UserObj =  self::populateObject($row);
                $userArray[$UserObj->getSeq()] = $UserObj;
            }
            return $userArray;
         }
         public function FindBySeq($seq){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_SEQ);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserObj =  self::populateObject($row);
            $error = $stmt->errorInfo();
            return $UserObj;
          }
          public function deleteBySeq($seq){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$DELETE);
            $stmt->bindValue(':seq', $seq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            $this->deleteLocationUsers($seq);
          }

           public function updatePassword(User $user){
            $password = $user->getPassword();
            $userSeq = $user->getSeq();
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_PASSWORD);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':seq', $userSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            if(!$user->getIsManager()){
                $_SESSION['userlogged']=$user;    
            }
            
            //;
          }

          public function updateManagerEmail($userSeq, $email){

            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_EMAIL);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':seq', $userSeq);
            $stmt->execute();
            $error = $stmt->errorInfo();
            //$user = self::FindBySeq($userSeq);
            //$_SESSION['managerlogged']=$user;
          }
          public function updateEmail(User $user){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_EMAIL);
            $stmt->bindValue(':email', $user->getEmailId());
            $stmt->bindValue(':seq', $user->getSeq());
            $stmt->execute();
            $error = $stmt->errorInfo();
            if(!$user->getIsManager()){
                $_SESSION['userlogged']=$user;
            }
            //$user = self::FindBySeq($userSeq);
            //$_SESSION['managerlogged']=$user;
           }
        public function updateOtp(User $user){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_OTP);
            $stmt->bindValue(':otp',$user->getOTP());
            $datetime = $user->getOTPGenerateDate();
            $stmt->bindValue(':otpgeneratedate',$datetime->format('Y-m-d H:i:s'));
            $stmt->bindValue(':seq',$user->getSeq());
            $stmt->execute();
            $error = $stmt->errorInfo();
            if(!$user->getIsManager()){
                $_SESSION['userlogged'] = $user;
            }
        }
        public function updateLoginOtp(User $user){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$UPDATE_LOGIN_OTP);
            $stmt->bindValue(':loginotp',$user->getOTP());
            $datetime = $user->getOTPGenerateDate();
            $stmt->bindValue(':loginotpgeneratedate',$datetime->format('Y-m-d H:i:s'));
            $stmt->bindValue(':seq',$user->getSeq());
            $stmt->execute();
            $error = $stmt->errorInfo();
        }
          public function isExist($userName){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$FIND_SEQ);
            $stmt->bindValue(':username', $userName);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $seqExist = "";
            if($row){
             $seqExist =  $row['seq'];
            }
            return $seqExist;
          }
           public function FindByUserName($userName){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$FIND_BY_USERNAME);
            $stmt->bindValue(':username', $userName);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserObj = null;
            if($row != FALSE){
                $UserObj = self::populateObject($row);
            }
            return $UserObj;
          }
          public function FindManagerByUsernamePassword($username, $password){
            $conn = self::$db_New->getConnection();
            $stmt = $conn->prepare(self::$SELECT_MANAGER_USERNAME_PASSWORD);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':password', $password);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserObj = null;
            if($row != FALSE){
                $UserObj = self::populateObject($row);
            }
            return $UserObj;
          }
        private function getLocationUsersLocationSeqs($userSeq,$lSeq = null){
                $conn = self::$db_New->getConnection();
                $stmt = $conn->prepare(self::$FIND_LOCATION_USERS);
                $stmt->bindValue(':userseq', $userSeq);
                $stmt->execute();
                $locationSeqs = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $locationseq =  $row["locationseq"];
                   // if(!empty($lSeq) && $lSeq == $locationseq){  
                    //}else{
                        array_push($locationSeqs,$locationseq);
                    //}
                }
                return $locationSeqs;    
          }
         private static $FIND_LOCATION_NAMES = "select l.* from locationusers lu inner join location l on lu.locationseq = l.seq where lu.userseq = :userseq ";
         
         private function getLocationNamesByUser($userSeq){
                $conn = self::$db_New->getConnection();
                $stmt = $conn->prepare(self::$FIND_LOCATION_NAMES);
                $stmt->bindValue(':userseq', $userSeq);
                $stmt->execute();
                $$locationNames = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $locationName =  $row["name"];
                    array_push($$locationNames,$locationName);
                }
                return $$locationNames;    
          }
          
          public function getAllFolderSeqs($userSeq){
             $conn = self::$db_New->getConnection();
             $stmt = $conn->prepare(self::$GET_ALL_FOLDERS);
             $stmt->bindValue(':userseq', $userSeq);
             $stmt->execute();
             $folderSeqs = array();
             while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                array_push($folderSeqs,$row["seq"]);
            }
             return $folderSeqs;        
          }
          public function getAllFolder($userSeq){
          	$conn = self::$db_New->getConnection();
          	$stmt = $conn->prepare(self::$GET_FOLDERS);
          	$stmt->bindValue(':userseq', $userSeq);
          	$stmt->execute();
          	$folders = array();
          	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          		array_push($folders,$row);
          	}
          	return $folders;
          }
          
           public function getAllStationType($userSeq){
             $conn = self::$db_New->getConnection();
             $stmt = $conn->prepare(self::$GET_ALL_STATION_TYPE);
             $stmt->bindValue(':userseq', $userSeq);
             $stmt->execute();
             $stationTypes = array();
             while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                array_push($stationTypes ,$row["stationtype"]);
             }
             return $stationTypes;        
          }
          public function populateObject($rsItem){
                $seq_ = $rsItem["seq"] ;
                $fullname = $rsItem["fullname"];
                $username_ =  $rsItem["username"] ;
                $password_ = $rsItem["password"] ;
                $emailId_ = $rsItem["emailid"] ;
                $dateOfRegistration_ = $rsItem["dateofregistration"] ;
                $isActive_ = $rsItem["isactive"];
                $isManager_ = $rsItem["ismanager"];
                $locationSeq_ = $rsItem["locationseq"];
                $folderSeq_ = $rsItem["folderseq"];
                 $locationName_ = null;
                if(isset($rsItem["locationName"])){
                	$locationName_ = $rsItem["locationName"];
                }
				$mobile_ = $rsItem["mobile"];
                $otp_ = $rsItem["otp"];
                $otpemailid_ = $rsItem["otpemailid"];
                $otpgeneratedate_ = $rsItem["otpgeneratedate"];
                $loginotp_ = $rsItem["loginotp"];
                $loginotpgeneratedate_ = $rsItem["loginotpgeneratedate"];
                $user = new User();
                $user->setSeq($seq_);
                $user->setFullName($fullname);
                $user->setUserName($username_);
                $user->setPassword($password_);
                $user->setDecodedPassword(SecurityUtil::Decode($password_));
                $user->setEmailId($emailId_);
                $user->setDateOfRegistration($dateOfRegistration_);
                $user->setConfirmPassword($password_);
                $user->setIsActive($isActive_);
                $user->setIsManager($isManager_);
                $user->setLocationSeq($locationSeq_);
                $user->setFolderSeq($folderSeq_);
                $user->setLocationName($locationName_);
				$user->setMobile($mobile_);
				$otherLocationSeqs = $this->getLocationUsersLocationSeqs($user->getSeq(),$locationSeq_);
                $user->setOtherLocationSeqs($otherLocationSeqs);
                $user->setOTP($otp_);
                $user->setOTPEmailId($otpemailid_);
                $user->setOTPGenerateDate($otpgeneratedate_);
                $user->setLoginOTP($loginotp_);
                $user->setLoginOTPGenerateDate($loginotpgeneratedate_);
                return $user;
        }

}



?>
