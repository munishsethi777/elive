<?php

   class validator{
       public static function validateform($objName,$objval,$length,$isNullAllow){      
          If(trim($objval) <> null and trim($objval) <> ""){
             if(strlen($objval)>$length){
                return $objName." can't be greater than ".$length." charactars.<br/>" ;  
            }else{
              return null;
            } 
           }else{
               if(!$isNullAllow){
                return $objName." can't be null.<br/>" ;
               }else{
                  return null;
               } 
           }
       }
       
       Public static function validateFormNumeric($value,$length,$isNulAllow){
               if(trim($value)<>null and trim($value)<>""){
                if(is_numeric($value)){
                    if(strlen($objval)>$length){
                        return "Value can't be greater than ".$length." charactars" ;  
                    }else{
                        return null;
                    } 
                }else{
                    return "Value should be numeric";
                }
               } 
           else {
           if(!$isNullAllow){
                return "value can't be null" ;
               }else{
                  return null;
               } 
            }   
           
          
   }
   
    Public static function validateNumeric($objName,$value,$length,$isNulAllow){
               if(trim($value)<>null and trim($value)<>""){
                if(is_numeric($value)){
                    if(strlen($value)>$length){
                        return "$objName can't be greater than ".$length." charactars<br/>" ;  
                    }else{
                        return null;
                    } 
                }else{
                    return "$objName should be numeric<br/>";
                }
               } 
           else {
           if(!$isNullAllow){
                return "$objName can't be null<br/>" ;
               }else{
                  return null;
               } 
            }   
           
          
   }
   
   }      
 
?>
