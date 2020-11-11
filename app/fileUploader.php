
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="css/fileuploader.css" rel="stylesheet" type="text/css">    
</head>
<body>        
    
    <div id="demo"></div>
    <ul id="separate-list"></ul>
    
    <script src="js/fileuploader.js" type="text/javascript"></script>
    <script src="js/jquery-1.5.1.min.js" type="text/javascript"></script>
    <script>        
        function createUploader(){            
            var uploader = new qq.FileUploader({
                element: document.getElementById('demo'),
                listElement: document.getElementById('separate-list'),
                action: 'ajaxFileUploaderAction.php'
            });           
        }        
        window.onload = createUploader;     
    </script>
    <Div id="errMessage"></Div>    
</body>
</html>