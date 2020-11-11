<?session_start();
$managerSession = $_SESSION["managerSession"];
$protocol  = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
$path=$protocol.getenv('HTTP_HOST')."/app/";?>

<!-- Mainly CSS --> 
    <link href="<?echo$path?>inspinia/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?echo$path?>inspinia/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?echo$path?>inspinia/css/animate.css" rel="stylesheet">
    <link href="<?echo$path?>css/custom.css" rel="stylesheet">
    <link type="text/css" href="<?echo$path?>admin/css/chosen/chosen.css" rel="stylesheet"/>
    <link href="<?echo$path?>inspinia/css/style.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?echo$path?>manage/css/jquery.datetimepicker.css"/> 
    <link href="<?echo$path?>inspinia/css/plugins/iCheck/custom.css" rel="stylesheet"> 
<!-- Sweet Alert -->
    <link href="<?echo$path?>inspinia/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">     
<!-- Toastr style -->
    <link href="<?echo$path?>inspinia/css/plugins/toastr/toastr.min.css" rel="stylesheet">
<!-- Multiple Select 
    <link href="<?//echo$path?>css/multiple-select/multiple-select.css" rel="stylesheet"/>-->
<!-- Mainly scripts -->
    <script src="<?echo$path?>inspinia/js/jquery-2.1.1.js"></script>      
    <script src="<?echo$path?>inspinia/js/bootstrap.min.js"></script>
    <script src="<?echo$path?>inspinia/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="<?echo$path?>inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="<?echo$path?>inspinia/js/inspinia.js"></script>
    <script src="<?echo$path?>inspinia/js/plugins/pace/pace.min.js"></script>
<!-- Toastr script -->
    <script src="<?echo$path?>inspinia/js/plugins/toastr/toastr.min.js"></script>
 <!-- BootBox -->
    <script src="<?echo$path?>inspinia/js/plugins/bootBox/bootbox.min.js"></script> 
 <!-- DatePicker --> 
    <script src="<?echo$path?>js/jquery.datetimepicker.full.min.js"></script>
  <!-- ChartJS-->
    <script src="<?echo$path?>inspinia/js/plugins/chartJs/Chart.min.js"></script>
   <!-- iCheck -->
    <script src="<?echo$path?>inspinia/js/plugins/iCheck/icheck.min.js"></script>
   <!-- Sweet alert -->
    <script src="<?echo$path?>inspinia/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script type="text/javascript" src="<?echo $path?>admin/js/chosen/chosen.jquery.js"></script>
   <!-- Muliple Select 
     <script src="<?//echo$path?>js/multiple-select/multiple-select.js"></script> -->
   <!--ajax Form
    <link rel="stylesheet" href="<?//echo $path?>css/ladda.min.css">
    <link rel="stylesheet" href="<?//echo $path?>css/ladda-themeless.min.css">
    <script src="<?//echo$path?>js/jquery.form.min.js"></script>
    <script src="<?//echo$path?>js/spin.min.js"></script>
    <script src="<?//echo$path?>js/ladda.min.js"></script>-->
    <script language="javascript">
        $('.deleteConfirm').click(function () {
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this imaginary file!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function () {
                swal("Deleted!", "Your imaginary file has been deleted.", "success");
            });
        });
        function showNotification($msg,status){
            toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "progressBar": true,
                  "positionClass": "toast-top-center",
                  "onclick": null,
                  "showDuration": "400",
                  "hideDuration": "1000",
                  "timeOut": "7000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
            }
            if(status == "success"){
                 toastr.success($msg); 
            }else{
                 toastr.error($msg);    
            }
           
        }
        function getErrorDiv(message){
                var errorDiv = '<div id="errorDiv" class="alert alert-danger alert-dismissable">';
                    errorDiv += '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>';
                    errorDiv += message;
                    errorDiv += '</div>';
                    return errorDiv;
            }
            function getStatusDiv(message){
                 var statusDiv = '<div id="msgDiv" class="alert alert-success alert-dismissable">';
                    statusDiv += '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>';
                    statusDiv += message;
                    statusDiv += '</div>';
                    return statusDiv;
            }
            function removeMessagesDivs(){
                $("#msgDiv").remove();
                $("#errorDiv").remove();
            }
            function showResponseToastr(data,formId,divClassName){
                var obj = $.parseJSON(data);
                $("#msgDiv").remove();
                $("#errorDiv").remove();
                var message = obj.message;
                if(obj.success == 1){
                    if(formId != null){
                        $("#" + formId)[0].reset();
                    }
                    toastr.success(message);
                }else{
                   removeMessagesDivs();
                   var errorDiv = getErrorDiv(message);
                   $("." + divClassName).append(errorDiv);
                }
            }
            function showResponseNotification(isSuccess,msg,divClassName,formId){
                removeMessagesDivs();
                //var obj = $.parseJSON(data);
                if(msg == ""){
                    return;
                }
                var message = msg;
                $("#msgDiv").remove();
                $("#errorDiv").remove();
                if(isSuccess){
                    var statusDiv = getStatusDiv(message)
                    $("." + divClassName).append(statusDiv);
                    $("#" + formId)[0].reset();
                }else{
                    var errorDiv = getErrorDiv(message);
                    $("." + divClassName).append(errorDiv);
                }
            }
            
    function deleteObj(seq,objValue,objName,url){
         swal({
            title: "Are you sure you want to delete " + objValue + " "  + objName + "?",
            text:  "You will not be able to recover " +  objValue + " " + objName  + "!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false
        }, function () {
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    call: "delete",
                    editSeq: seq,
                },
                complete: function () {
                    $("#jqxgrid").jqxGrid('updatebounddata');
                    swal("Deleted!", objName  + " Deleted Successfully.", "success"); 
                }
            });  
            
        });
    }       
    </script>
