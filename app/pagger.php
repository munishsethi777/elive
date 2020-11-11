<script type="text/javascript" src="js/jquery.simplePagination.js"></script>
<link type="text/css" rel="stylesheet" href="css/simplePagination.css"/>
<div id="pagger"></div>
<script type="text/javascript">
function initPagger(count,itemCount){
	$("#pagger").pagination({
        items: count,
        itemsOnPage: itemCount,
        cssStyle: 'light-theme',
        onPageClick : function(pageNum,event) {
        	loadData(pageNum) ;
        }
    });
}

</script>