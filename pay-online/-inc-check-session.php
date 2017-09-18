<?php 
if (!isset($_SESSION)) {
  session_start();
}

if(isset($_SESSION['marist_applicant'])) {
	//die('logged in');
	//get logged in applicant's info
	mysql_select_db($database_dbconn, $dbconn);
	$applicant_query = sprintf("SELECT * FROM applicant WHERE app_id = %s", GetSQLValueString($_SESSION['marist_applicant'], "int"));
	$applicant_RS = mysql_query($applicant_query, $dbconn) or die(mysql_error());
	$applicant = mysql_fetch_assoc($applicant_RS);
} else {
	//die('not logged in');
	//not logged in, redirect to login page with error
	$_SESSION['PrevUrl'] = $_SERVER['REQUEST_URI']; //source page for login redirect
	
	header("location: apply-access.php?err=Please supply your Email and Access Code to access the application form!");
	exit;
}