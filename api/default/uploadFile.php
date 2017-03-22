<?php
//header('Access-Control-Allow-Origin: *');

// require_once 'dbHandler.php';
 
$location = '../../img/course-images';
// $uploadfile = $_POST['fileName'];
$uploadfilename = $_FILES['file']['tmp_name'];
// $db = new DbHandler();
// $filename = $db->randomPassword();
 
if(move_uploaded_file($uploadfilename, $location.'/'.$_FILES['file']['name'])){ //$filename)){
        echo 'File successfully uploaded!';
} else {
        echo 'Upload error!';
}