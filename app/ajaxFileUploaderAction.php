<?php

/**
 * Handle file uploads via XMLHttpRequest
 */
require_once('IConstants.inc');  
require_once($ConstantsArray['dbServerUrl'] ."Parsers/ParserSLM.php");
require_once($ConstantsArray['dbServerUrl'] ."log4php/Logger.php");
$loggerDB = null;
Logger::configure($ConstantsArray['dbServerUrl'] .'log4php/log4php.xml');
$loggerDB = Logger::getLogger($Log4PHP_MyDBLogger);
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */ 

    function save($path,$fileType,$loggerDB) {    
        $input = fopen("php://input", "r");
        $theData = fgets($input);
        if($fileType == "SLM"){
           $parserSLM = new ParserSLM();
           $loggerDB->info("Calling SLM parser");
           try{
                $parserSLM->parseSLMFromString($theData,$loggerDB);
           }catch(Exception $e){
               $loggerDB->error($e->getMessage());
               return false;
           }
           fclose($input); 
        }
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}


class qqFileUploader {
    private $allowedExtensions = array("txt");
    private $sizeLimit = 10485760;
    private $file;
    private $fileType;
    
    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760,$fileType){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        $this->fileType = $fileType;
        //$this->checkServerSettings();       
        $this->file = new qqUploadedFileXhr();
    }
       
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE,$loggerDB){
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large. Allowed size is less than 2MB');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        

            if ($this->file->save($uploadDirectory . $filename . '.' . $ext,"SLM",$loggerDB)){
                return array('success'=>true);
            } else {
                return array('error'=> 'Could not save uploaded file.' .
                    'The upload was cancelled, or server error encountered. Refer logs for more details.');
            }
        }
        
    }    


// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array();
// max file size in bytes  2MB
$sizeLimit = 1 * 1024 * 1024;
$loggerDB->info("Starting to upload SLM file");
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit,"SLM");
$result = $uploader->handleUpload('files/',FALSE,$loggerDB);
$loggerDB->info("Finishing to upload SLM file with status as ". $result);
// to pass data through iframe you will need to encode all html tags
$result['errMessage'] = "Failed to Upload the File";
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
