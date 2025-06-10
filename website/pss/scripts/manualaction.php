<?php
	include('dblogin.php');

	if(isset($_GET['addmanualaction']) && isset($_POST['device']) && isset($_POST['number']) && isset($_POST['variables']))
	{
		$numbers=array(0 => "Choose Action", 11 => "Start Loop", 12 => "Stop Loop", 13 => "Turn TV On", 14 => "Turn TV Off", 15 => "Download Graphic or Loop", 16 => "Download Crons", 21 => "Change TV to Input 1", 22 => "Change TV to Input 2", 23 => "Change TV to Input 3", 24 => "Change TV to Input 4", 25 => "Change TV to Input 5");
		$device=$_POST['device']; $number=$_POST['number']; $_POST['variables'];
		$insert="INSERT INTO ManualActions(MA_Device, MA_Number, MA_Variables) VALUES('$device', '$number', '$variables')";
		if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); }
		else { echo("<h3 style='color:#008000'>Manual Action Added</h3>$numbers[$number]"); }
	}
?>
