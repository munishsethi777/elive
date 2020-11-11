<?php
class SessionUtil{
    private static $sessionUtil;
    public static function getInstance(){
        if(!self::$sessionUtil){
            session_start();
            self::$sessionUtil = new SessionUtil();
            return self::$sessionUtil;
        }
        return self::$sessionUtil;
    }
    
       
} 

?>
