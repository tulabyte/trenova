<?php
//header('Access-Control-Allow-Origin: *');

// require_once 'dbHandler.php';
 
 $location = '../../img/videos/';
$video_name = basename($_FILES["file"]["name"]);
$uploadfilename = $_FILES['file']['tmp_name'];
$filename = $location.$video_name;
$vid_name = $_POST['video'];

   if (file_exists($filename)){
	unlink($filename);
   } else {
	 if(move_uploaded_file($uploadfilename, $filename) ){ 
		        echo 'File successfully uploaded!';
	} else {
	        echo 'Upload error!';
		}
   };
 