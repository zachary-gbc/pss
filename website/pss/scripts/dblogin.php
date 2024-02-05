<?php
	$conflines=file('/var/www/html/pss/conf/pss.conf');
	foreach($conflines as $line)
	{
		if(substr($line,0,11) == "database_ip") { $dbip=trim(str_replace('"','',substr($line,12))); }
		if(substr($line,0,13) == "database_name") { $dbname=trim(str_replace('"','',substr($line,14))); }
		if(substr($line,0,17) == "database_username") { $dbuser=trim(str_replace('"','',substr($line,18))); }
		if(substr($line,0,17) == "database_password") { $dbpass=trim(str_replace('"','',substr($line,18))); }
	}

	if(!$db=mysqli_connect('localhost',$dbuser,$dbpass)) { if(!$db=mysqli_connect($dbip,$dbuser,$dbpass)) { echo("DB Connection Error"); exit; } }
	if(!mysqli_select_db($db,$dbname)) { echo("Unable to Select Database"); exit; }
?>