<?php
  class FilterUtil{
      public static function getFilters(){
        $pagenum = intval($_GET['pagenum']);
        $pagesize = intval($_GET['pagesize']);
        $start = $pagenum;
        if($pagenum > 1){
             $start = $pagenum * $pagesize;    
        }       
        $filter["start"] = $start;
        $filter["limit"] = $pagesize;
        return $filter;
      }
  
  private static function appendLimit($query){
    if(isset($_GET['pagenum'])){
        $pagenum = intval($_GET['pagenum']);
        $pagesize = intval($_GET['pagesize']);
        $start = $pagenum;
        //if($pagenum > 1){
             $start = $pagenum * $pagesize;    
        //}       
        $query = $query . " limit " . $start . "," . $pagesize;
    }    
    return $query;         
  }
  
  public static function appendPagger($queryString){
  	$infoType = $_GET["infoTypeRadio"];
  	if($infoType == "graph"){
  		return $queryString;
  	}
  	if(isset($_GET['pagenum'])){
  		$pagenum = intval($_GET['pagenum']);
  		//$pagesize = intval($_GET['pagesize']);
  		$start = $pagenum;
  		$pagesize = FilterUtil::getPageSize();
  		if($pagenum > 1){
  			$start = $pagenum * $pagesize - $pagesize;  			
        }else{
            $start = 0;
        }
  		$query =  $queryString . " limit " . $start . "," . $pagesize;
  	}
  	return $query;
  }
  
  public static function getPageSize(){
  	$interval = $timeBase = $_GET['timeBase'];
  	$quickReportType = $_GET['quickReportType'];
  	$timeBaseQuick = $_GET['timeBaseQuick'];
  	if($quickReportType != "null"){
  		$interval = $timeBaseQuick;
  	}
  	switch ($interval) {
  		case "instant":
  			$pagesize = 50;
  			break;
  		case "5min":
  			$pagesize = 50;
  			break;
  		case "10min":
  			$pagesize = 50 * 2;
  			;
  			break;
  		case "15min":
  			$pagesize = 50 * 4
  			;
  			break;
  		case "30min":
  			$pagesize = 50 * 6
  			;
  			break;
  		default:
  			$pagesize = 50 * 8;
  			;
  	}
  	return $pagesize;
  }
  
  private static function appendSorting($query){
    if (isset($_GET['sortdatafield']))
    {
        $sortfield = $_GET['sortdatafield'];
        $sortorder = $_GET['sortorder'];            
        if ($sortfield != NULL)
        {
            if ($sortorder == "desc")
            {
                $query = $query . " ORDER BY" . " " . $sortfield . " DESC";
            }
            else if ($sortorder == "asc")
            {
                $query = $query . " ORDER BY" . " " . $sortfield . " ASC";
            }            
        }
    }  
    return $query;  
  }
  
  public static function applyFilter($query,$isApplyLimit = true){
    // filter data.
    if (isset($_GET['filterscount']))
    {
        $filterscount = $_GET['filterscount'];
        
        if ($filterscount > 0)
        {
            if (strpos(strtolower ($query),'where') !== false) {
                $where = " AND (";
            }else{
                 $where = " WHERE (";    
            }
           
            $tmpdatafield = "";
            $tmpfilteroperator = "";
            for ($i=0; $i < $filterscount; $i++)
            {
                // get the filter's value.
                $filtervalue = $_GET["filtervalue" . $i];
                // get the filter's condition.
                $filtercondition = $_GET["filtercondition" . $i];
                // get the filter's column.
                $filterdatafield = $_GET["filterdatafield" . $i];
                // get the filter's operator.
                $filteroperator = $_GET["filteroperator" . $i];
                
                if ($tmpdatafield == "")
                {
                    $tmpdatafield = $filterdatafield;            
                }
                else if ($tmpdatafield <> $filterdatafield)
                {
                    $where .= ")AND(";
                }
                else if ($tmpdatafield == $filterdatafield)
                {
                    if ($tmpfilteroperator == 0)
                    {
                        $where .= " AND ";
                    }
                    else $where .= " OR ";    
                }
                
                // build the "WHERE" clause depending on the filter's condition, value and datafield.
                switch($filtercondition)
                {
                    case "NOT_EMPTY":
                    case "NOT_NULL":
                        $where .= " " . $filterdatafield . " NOT LIKE '" . "" ."'";
                        break;
                    case "EMPTY":
                    case "NULL":
                        $where .= " " . $filterdatafield . " LIKE '" . "" ."'";
                        break;
                    case "CONTAINS_CASE_SENSITIVE":
                        $where .= " BINARY  " . $filterdatafield . " LIKE '%" . $filtervalue ."%'";
                        break;
                    case "CONTAINS":
                        $where .= " " . $filterdatafield . " LIKE '%" . $filtervalue ."%'";
                        break;
                    case "DOES_NOT_CONTAIN_CASE_SENSITIVE":
                        $where .= " BINARY " . $filterdatafield . " NOT LIKE '%" . $filtervalue ."%'";
                        break;
                    case "DOES_NOT_CONTAIN":
                        $where .= " " . $filterdatafield . " NOT LIKE '%" . $filtervalue ."%'";
                        break;
                    case "EQUAL_CASE_SENSITIVE":
                        $where .= " BINARY " . $filterdatafield . " = '" . $filtervalue ."'";
                        break;
                    case "EQUAL":
                        $where .= " " . $filterdatafield . " = '" . $filtervalue ."'";
                        break;
                    case "NOT_EQUAL_CASE_SENSITIVE":
                        $where .= " BINARY " . $filterdatafield . " <> '" . $filtervalue ."'";
                        break;
                    case "NOT_EQUAL":
                        $where .= " " . $filterdatafield . " <> '" . $filtervalue ."'";
                        break;
                    case "GREATER_THAN":
                        $where .= " " . $filterdatafield . " > '" . $filtervalue ."'";
                        break;
                    case "LESS_THAN":
                        $where .= " " . $filterdatafield . " < '" . $filtervalue ."'";
                        break;
                    case "GREATER_THAN_OR_EQUAL":
                        $where .= " " . $filterdatafield . " >= '" . $filtervalue ."'";
                        break;
                    case "LESS_THAN_OR_EQUAL":
                        $where .= " " . $filterdatafield . " <= '" . $filtervalue ."'";
                        break;
                    case "STARTS_WITH_CASE_SENSITIVE":
                        $where .= " BINARY " . $filterdatafield . " LIKE '" . $filtervalue ."%'";
                        break;
                    case "STARTS_WITH":
                        $where .= " " . $filterdatafield . " LIKE '" . $filtervalue ."%'";
                        break;
                    case "ENDS_WITH_CASE_SENSITIVE":
                        $where .= " BINARY " . $filterdatafield . " LIKE '%" . $filtervalue ."'";
                        break;
                    case "ENDS_WITH":
                        $where .= " " . $filterdatafield . " LIKE '%" . $filtervalue ."'";
                        break;
                }
                                
                if ($i == $filterscount - 1)
                {
                    $where .= ")";
                }
                
                $tmpfilteroperator = $filteroperator;
                $tmpdatafield = $filterdatafield;            
            }
            // build the query.
            $query = $query . $where; 
           
            
        }
        
        
          
      }
      //apply Sorting
        $query = FilterUtil::appendSorting($query);
        
      //apply limit
      if($isApplyLimit){
        $query = FilterUtil::appendLimit($query);
      }
      
      return $query;
  }
}
?>
