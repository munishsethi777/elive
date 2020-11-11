<?php 
 class SecurityUtil{
      
    private static $secretPass ="Munish3525";
    //pass encoded text to get back real text
    public static function Decode($data){
        $decoded =  self::Encoder(self::hex2bin($data),self::$secretPass); 
        return $decoded;
    }
    //passs real text and get back an ecoded string
    public static function Encode($data){
        $encoded = bin2hex(self::Encoder($data,self::$secretPass)); 
        return $encoded;    
    }
    
    public function getEncodedMobileVerificationId($mobileNumber){
        $encoded = bin2hex(self::Encoder($mobileNumber,self::$secretPass)); 
        return substr($encoded,strlen($encoded)-6,6);    
    }
    
    
    private static function Encoder($data,$pwd)
    {
        
        $pwd_length = strlen($pwd);
        $x = null;
        $a = null;
        $j = null;
        $Zcrypt = null;
        for ($i = 0; $i < 255; $i++) {
            $key[$i] = ord(substr($pwd, ($i % $pwd_length)+1, 1));
            $counter[$i] = $i;
        }
        for ($i = 0; $i < 255; $i++) {
            $x = ($x + $counter[$i] + $key[$i]) % 256;
            $temp_swap = $counter[$i];
            $counter[$i] = $counter[$x];
            $counter[$x] = $temp_swap;
        }
        for ($i = 0; $i < strlen($data); $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $counter[$a]) % 256;
            $temp = $counter[$a];
            $counter[$a] = $counter[$j];
            $counter[$j] = $temp;
            $k = $counter[(($counter[$a] + $counter[$j]) % 256)];
            $Zcipher = ord(substr($data, $i, 1)) ^ $k;
            $Zcrypt .= chr($Zcipher);
        }
        return $Zcrypt;
    }

    private static function hex2bin($hexdata) {
        for  ($i=0;$i<strlen($hexdata);$i+=2) {
            $bindata.=chr(hexdec(substr($hexdata,$i,2)));
        }  
        return $bindata;
    }   
      
  }
?>
