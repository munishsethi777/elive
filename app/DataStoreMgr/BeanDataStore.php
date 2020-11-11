<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "/DataStoreMgr/MainDB.php");
require_once ($ConstantsArray ['dbServerUrl'] . "log4php/Logger.php");
require_once ($ConstantsArray ['dbServerUrl'] . "Utils/FilterUtil.php");
class BeanDataStore {
	private $className;
	private $tableName;
	public function __construct($className_, $tableName) {
		$this->className = $className_;
		$this->tableName = $tableName;
	}
	private function key_implode($array) {
		$fields = array ();
		foreach ( $array as $field => $val ) {
			if (is_null ( $val )) {
				$fields [] = "$field = NULL";
			} else {
				$fields [] = "$field = '$val'";
			}
		}
		$result = join ( ', ', $fields );
		return $result;
	}
	public function save($object) {
	    $id = null;
		try {
			$columnValueArry [] = array ();
			$columns [] = array ();
			$count = 0;
			$class = new ReflectionClass ( $this->className );
			$methods = $class->getMethods ( ReflectionMethod::IS_PUBLIC );
			$id;
			if($this->className == "ChatroomChat"){
				$id = $object->getPost_Id();
			}else{
				$id = $object->getSeq ();
			}
			foreach ( $methods as $method ) {
				$methodName = $method->name;
				if (! $this->startsWith ( $methodName, "set" )) {
					if ($count > 0) {
						$reflect = new ReflectionMethod ( $object, $methodName );
						if ($reflect->isPublic ()) {
							$val = call_user_func ( array (
									$object,
									$methodName 
							) );
							$column = strtolower ( substr ( $methodName, 3 ) );
							$columns [] = $column;
							$value = call_user_func ( array (
									$object,
									$methodName 
							) );
							if ($value instanceof DateTime) {
								if($column == "createdon" && $id > 0){
									continue;
								}
								$value = $value->format ( 'Y-m-d H:i:s' );
							}
							// if($id > 0){
							// $value = "'" . $value . "'";
							// }
							$columnValueArry [$column] = $value;
						}
					}
					$count ++;
				}
			}
			unset ( $columnValueArry [0] );
			unset ( $columns [0] );
			$SQL = "";
			$db_New = MainDB::getInstance ();
			$conn = $db_New->getConnection ();
			
			if ($id > 0) { // update query
				$columnString = implode ( '=?,', array_keys ( $columnValueArry ) );
				$columnString .= "=?";
				$SQL = "Update " . strtolower ( $this->tableName ) . " set " . $columnString . " where seq = " . $id;
				$STH = $conn->prepare ( $SQL );
				$STH->execute ( array_values ( $columnValueArry ) );
			} else { // Insert Query
				$columnString = implode ( ',', array_keys ( $columnValueArry ) );
				$valueString = implode ( ',', array_fill ( 0, count ( $columnValueArry ), '?' ) );
				$SQL = "INSERT INTO " . $this->tableName . " ({$columnString}) VALUES ({$valueString})";
				$STH = $conn->prepare ( $SQL );
				$STH->execute ( array_values ( $columnValueArry ) );
				$id = $conn->lastInsertId ();
			}
			$this->throwException ( $STH->errorInfo () );
		} catch ( Exception $e ) {
			$logger = Logger::getLogger($ConstantsArray["logger"]);
			$logger->error("Error occured in BeanDataStore : - " . $e);
			throw $e ;
		}
		return $id;
	}
	function findBySeq($seq) {
		try {
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			$STH = $conn->prepare ( "select * from " . $this->tableName . " where seq = " . $seq );
			$STH->execute ();
			$obj = $STH->fetchObject ( $this->className );
			$this->throwException ( $STH->errorInfo () );
			return $obj;
		} catch ( Exception $e ) {
			$this->logger->error ( "Error occured :" . $e );
			throw $e ;
		}
	}
	function findAll($isApplyFilter = false) {
		try {
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			$sql = "select * from " . $this->tableName;
			if ($isApplyFilter) {
				$sql = FilterUtil::applyFilter ( $sql );
			}
			$STH = $conn->prepare ( $sql );
			$STH->execute ();
			$objList = $STH->fetchAll ( PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->className );
			$this->throwException ( $STH->errorInfo () );
			return $objList;
		} catch ( Exception $e ) {
			$logger = Logger::getLogger($ConstantsArray["logger"]);
			throw $e;
		}
	}
	
	function findAllArray($seq) {
		try {
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			$STH = $conn->prepare ( "select * from " . $this->tableName . " where seq = " . $seq );
			$STH->execute ();
			$obj = $STH->fetchAll(PDO::FETCH_ASSOC);
			$this->throwException ( $STH->errorInfo () );
			return $obj;
		} catch ( Exception $e ) {
			$this->logger->error ( "Error occured :" . $e );
			throw $e;
		}
	}
	
	public function findArrWithCondition($colValuePair, $isApplyFilter = false) {
		try {
			$query_array = array ();
			foreach ( $colValuePair as $key => $value ) {
				if ($value != null || $value==0) {
					$query_array [] = $this->tableName.".".$key . ' = ' . "'" . $value . "'";
				}
			}
			$query = "SELECT ".$this->tableName.".* FROM " . $this->tableName;
				
			if (count ( $query_array ) > 0) {
				$query .= " WHERE " . implode ( " AND ", $query_array );
			}
			if ($isApplyFilter) {
				$query = FilterUtil::applyFilter ( $query );
			}
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			$STH = $conn->prepare ( $query );
			$STH->execute ();
			$this->throwException ( $STH->errorInfo () );
			$objList = $STH->fetchAll ( PDO::FETCH_ASSOC);
			return $objList;
		} catch ( Exception $e ) {
			$this->logger->error ( "Error occured :" . $e );
			throw $e ;
		}
	}
	
	public function executeCountQuery($colValuePair = null, $isApplyFilter = false) {
		try {
			$query = "SELECT  COUNT(DISTINCT ".$this->tableName.".seq) FROM " . $this->tableName;
			if ($colValuePair != null) {
				foreach ( $colValuePair as $key => $value ) {
					$query_array [] = $this->tableName .".".$key . ' = ' . "'" . $value . "'";
				}
				$query .= " WHERE " . implode ( " AND ", $query_array );
			}
			
			if ($isApplyFilter) {
				$query = FilterUtil::applyFilter ( $query, false );
			}
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			$STH = $conn->prepare ( $query );
			$STH->execute ();
			$this->throwException ( $STH->errorInfo () );
			$result = $STH->fetch ( PDO::FETCH_NUM );
			$count = intval ( $result [0] );
			return $count;
		} catch ( Exception $e ) {
			$this->logger->error ( "Error occured :" . $e );
			throw  $e ;
		}
	}
	
	public function throwException($error) {
	    if ($error [2] != "") {
		    throw new Exception ( $error [2] );
		}
	}
	function startsWith($haystack, $needle) {
		$length = strlen ( $needle );
		return (substr ( $haystack, 0, $length ) === $needle);
	}
	function endsWith($haystack, $needle) {
		$length = strlen ( $needle );
		if ($length == 0) {
			return true;
		}
		return (substr ( $haystack, - $length ) === $needle);
	}
    public function executeQuery($query) {
        try {
            $db = MainDB::getInstance ();
            $conn = $db->getConnection ();
            $sth = $conn->prepare ( $query );
            $sth->execute();
            $this->throwException ( $sth->errorInfo () );
            $objList = $sth->fetchAll();
            return $objList;
        } catch ( Exception $e ) {
            $this->logger->error ( "Error occured :" . $e );
            throw $e ;
        }
    }
    
    public function deleteWithQuery($query) {
		try {
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			$STH = $conn->prepare ( $query );
			$flag = $STH->execute ();
			$this->throwException ( $STH->errorInfo () );
		} catch ( Exception $e ) {
			$this->logger->error ( "Error occured :" . $e );
			throw $e ;
		}
		return $flag;
	}
	
	public function executeQueryNew($query, $isApplyFilter = false, $ommitIntegerArrayElements =false,$isGroupBy = false) {
		try {
			$db = MainDB::getInstance ();
			$conn = $db->getConnection ();
			if ($isApplyFilter) {
				$query = FilterUtil::applyFilter ( $query,true,$isGroupBy );
			}
			$sth = $conn->prepare ( $query );
			$sth->execute ();
			$this->throwException ( $sth->errorInfo () );
			$objList = null;
			if($ommitIntegerArrayElements){
				$objList = $sth->fetchAll(PDO::FETCH_ASSOC);
			}else{
				$objList = $sth->fetchAll();
			}
			return $objList;
		} catch ( Exception $e ) {
			$this->logger->error ( "Error occured :" . $e );
			throw $e ;
		}
	}
}
?>
