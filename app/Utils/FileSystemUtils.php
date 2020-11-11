<?php

Class FileSystemUtils{
  
  private static $fileSystemUtils;  
  private static $dir_tree;
  public static $FILE_TYPES = array("WQD", "LSI", "WAD", "CFG" , "TXT","CSV", "DATA");
  public static function getInstance(){
    if (!self::$fileSystemUtils){
        self::$fileSystemUtils = new FileSystemUtils();           
        return self::$fileSystemUtils;
    }
    return self::$fileSystemUtils;       
  }
    
      
  
  
  public function delete_NestedDirectory($dirname) {
       if (is_dir($dirname))
          $dir_handle = opendir($dirname);
       if (!$dir_handle)
          return false;
       while($file = readdir($dir_handle)) {
          if ($file != "." && $file != "..") {
             if (!is_dir($dirname."/".$file)){
                unlink($dirname."/".$file);
             }else{
                self::delete_NestedDirectory($dirname.'/'.$file);    
             }
           } 
       }
       closedir($dir_handle);
       rmdir($dirname);
       return true;
     
  }
  
  public static function deleteFile($file){
  	if (!unlink($file))
  	{
  		return false;
  	}
  	else
  	{
  		return false;
  	}
  }
  
  private function isValidFile($fileName){
      $extention = substr($fileName, strrpos($fileName, '.')+1);
      $isValid = false;
      if(in_array(strtoupper($extention),self::$FILE_TYPES)){
         $isValid = true;     
      }
      return $isValid;
  }
   public function Read_Directory($directory){
      try{
          $dir = $directory;
          $filesArr = Array();
          if(is_dir($dir)){
              foreach (scandir($dir) as $entry) {
                if (is_file($dir."/".$entry)) {
                    if($this->isValidFile($entry)){
                        array_push($filesArr,$directory."/".$entry);    
                    }
                }
                if(count($filesArr) == 500){
                	return $filesArr;
                }
              }
          }
          return $filesArr;
      }catch(Exception $e){
          throw $e;
      }
          
  }   
  public function Read_AllNestedFiles($directory){
    try{
    unset($this->dir_tree);
    if($this->isDirEmpty($directory) == false){
        if ($handle = @opendir($directory)){
            while(false !== ($file = readdir($handle))){
                if ($file != ".." && $file != "." ){
                    if( is_dir( $directory.'/'.$file)){
                        $this->dir_tree['directories'][] = $directory.'/'.$file;
                        $this->Read_Directory( $directory.'/'.$file );
                    }else{
                        $this->dir_tree['files'][] = $directory.'/'.$file;
                    }
                }
            }
        }
        if($this->dir_tree == null || $this->dir_tree == ""){
            return null;
        }else{
            return $this->dir_tree; 
        }
    }
   }catch(Exception $e){
      echo ("Exception occured while reading directory");
      throw $e;
   }
  }
  public function isDirEmpty($dir){
    $directory_empty = true;
    $files = @scandir($dir);
    if (count($files) > 2){      
        $directory_empty = false;  
    }  
    return  $directory_empty;
  }
  
  
  public function delete_directory(){
    if( is_array( $this->dir_tree['files'] ) ){
        foreach( $this->dir_tree['files'] as $value){
            @unlink($value);
        }
        if(is_array($this->dir_tree['directories'])){
            $this->dir_tree['directories'] = array_reverse($this->dir_tree['directories']);
            foreach($this->dir_tree['directories'] as $value){
                rmdir($value);
            }
        }
    }
  }      
   
   public function getLatestFileName($directory){
        $dir = $directory;
        $lastMod = 0;
        $lastModFile = '';
        foreach (scandir($dir) as $entry) {
            if (is_file($dir."/".$entry) && filectime($dir."/".$entry) > $lastMod) {
                $lastMod = filectime($dir."/".$entry);
                $lastModFile = $entry;
            }
        }
        return $lastModFile;
   }
   
   public function getFileType($fileName){
       $fileNameTotalCount = strlen($fileName);
       $fileType = substr($fileName,$fileNameTotalCount-3,3);
       return $fileType; 
   }
   public static function getFileName($filePath){
     return basename($filePath);  
   }
   
   public static function createZip($zipname,$files){
   	$zip = new ZipArchive;
   	$zip->open($zipname, ZipArchive::CREATE);
   	foreach ($files as $file) {
   		$zip->addFile($file);
   	}
   	$zip->close();
   }
}
?>
