<?php
  require_once('IConstants.inc');
  require_once("MainDB.php");
  require_once($ConstantsArray['dbServerUrl'] . "BusinessObjects/WQDChannel.php");
  class WQDDataChannelDataStore   {
  
    private static $WQDDataChannelStore;
     private static $db;
    private static $INSERT = "insert into wqdfiledatachannels (wqdfiledataseq,wqdfiledatachannelnumber,wqdfiledatachannelname,wqdfiledatachannelvalue,wqdfiledatachannelstatus) values(:dataSeq,:channelNumber,:channelName,:channelValue,:channelStatus)";
    
    public function __construct(){
        self::$db = MainDB::getInstance();
    }

    public static function getInstance()
    {
        if (!self::$WQDDataChannelStore)
        {
            self::$WQDDataChannelStore = new WQDDataChannelDataStore();           
            return self::$WQDDataChannelStore;
        }
        return self::$WQDDataChannelStore;        
    }
    
    public function Save(WQDChannel $wqdChannel){
      $SQL = self::$INSERT;
      $conn = self::$db->getConnection();
      $stmt = $conn->prepare($SQL);
      $stmt->bindValue(':dataSeq', $wqdChannel->getFileDataSeq()); 
      $stmt->bindValue(':channelNumber', $wqdChannel->getChannelNumber());
      $stmt->bindValue(':channelName',$wqdChannel->getChannelName());
      $stmt->bindValue(':channelValue',$wqdChannel->getChannelValue());
      $stmt->bindValue(':channelStatus',$wqdChannel->getChannelValue());
      try{
        $stmt->execute(); 
      }catch(Exception $e){
          return $e->getMessage();
      }
      $error = $stmt->errorInfo();
    }
     public function SaveArray($wqdChannelArr,$dataSeq){
      $wqdChannel = new WQDChannel();
          if(count($wqdChannelArr) > 0){
                  foreach($wqdChannelArr as $wqdChannel){
                      $wqdChannel->setFileDataSeq($dataSeq);
                      self::Save($wqdChannel);
                  }
          }
    }
    
    
    
        
     public function FindAllDataByDataSeq($limit,$offset,$dataSeq){        
        $parms = array();
        $sql = "Select * from wqdfiledatachannels where wqdfiledataseq = ". $dataSeq;
        try{
            //$sql = self::buildQuery($sql,$filter);
            if($limit <> "" && $limit <> null){
                $sql =  $sql . " limit " . $limit . " offset " . $offset;
            }
            $rows = self::$db->executeQuery($sql,$parms);
            return self::populateObjectArray($rows);
        }catch (Exception $e){
             $error = $e;
        }
    }
    
     public function populateObjectArray($rows){
          $objArr = array();
          foreach($rows as $rsItem){
            $obj = self::populateObject($rsItem);
            $objArr[$obj->getSeq()] = $obj; 
          }
          return $objArr;
      }
      
      public function populateObject($rsItem){
            $seq = $rsItem["wqdfiledatachannelseq"] ;
            $dataSeq = $rsItem["wqdfiledataseq"];
            $channelNo =  $rsItem["wqdfiledatachannelnumber"] ;
            $channelName = $rsItem["wqdfiledatachannelname"] ;
            $channelValue = $rsItem["wqdfiledatachannelvalue"] ;
            $channelStatus = $rsItem["wqdfiledatachannelstatus"] ;
            
            $channel = new WQDChannel();
            $channel->setSeq($seq);
            $channel->setFileDataSeq($dataSeq);
            $channel->setChannelNumber($channelNo);
            $channel->setChannelName($channelName);
            $channel->setChannelValue($channelValue);
            $channel->setChannelStatus($channelStatus);
            return $channel;
      }
    
  }
?>
