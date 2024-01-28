<?php
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