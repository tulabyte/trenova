<?php
header('Access-Control-Allow-Origin: *');
// die($_POST['old_avatar']);
$location = '../../../img/user-avatars/';
// die( "Valid Folder?: " . is_dir($location) . " | " . "Writable Folder?: " . is_writable($location) );

$uploadfilename = $_FILES['file']['tmp_name'];
 
if(move_uploaded_file($uploadfilename, $location . $_FILES['file']['name'])){ //$filename)){
        echo 'File successfully uploaded!';
} else {
        echo 'Upload error!';
}