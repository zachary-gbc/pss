<?php
  $systemname="Pi Screen Scheduler"; $loadnow=time(); $user="";
	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"iphone") === false && strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"android") === false) { $mobile=false; } else { $mobile=true; }

	$conf=file('/pss/conf/pss.conf');
	foreach($lines as $line)
	{
		if(substr($line,0,10) == "main_db_ip") { $dbip=substr($line,11); }
		if(substr($line,0,12) == "main_db_name") { $dbname=substr($line,13); }
		if(substr($line,0,17) == "database_username") { $dbuser=substr($line,18); }
		if(substr($line,0,17) == "database_password") { $dbpass=substr($line,18); }
	}

	if(!$db=mysqli_connect('localhost',$dbuser,$dbpass)) { if(!$db=mysqli_connect($dbip,$dbuser,$dbpass)) { echo("DB Connection Error"); exit; } }
	if(!mysqli_select_db($db,$dbname)) { echo("Unable to Select Database"); exit; }
?>

<!DOCTYPE html>
<html>
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta name="author" content="Zachary Flight" />
	<link rel="stylesheet" type="text/css" href="/pss/other/styles.css" />
