<?php
	$numbers=array(0 => "Choose Action", 11 => "Start Loop", 12 => "Stop Loop", 13 => "Turn TV On", 14 => "Turn TV Off", 15 => "Download Graphic or Loop", 16 => "Download Crons", 21 => "Change TV to Input 1", 22 => "Change TV to Input 2", 23 => "Change TV to Input 3", 24 => "Change TV to Input 4", 25 => "Change TV to Input 5");

	if(isset($_GET['addmanualaction']))
	{
		include('dblogin.php');

		if(isset($_GET['addmanualaction']) && isset($_POST['device']) && isset($_POST['number']) && isset($_POST['variables']))
		{
			$device=substr($_POST['device'],0,-2); $number=$_POST['number']; $variables=($_POST['variables'] . "-" . substr($_POST['device'],-1));
			$insert="INSERT INTO ManualActions(MA_Device, MA_Number, MA_Variables) VALUES('$device', '$number', '$variables')";
			if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); }
			else
			{
				$getip="SELECT Dev_IP FROM Devices WHERE (Dev_MAC='$device')"; $ip="";
				if(!$rs=mysqli_query($db,$getip)) { echo("Unable to Run Query: $getip"); exit; }
				while($row = mysqli_fetch_array($rs)) { $ip=$row['Dev_IP']; }
				if($ip != "") { header("Location: http://$ip/pss/scripts/manualaction.php?confirmationnumber=$number"); }
			}
		}
	}
	elseif(isset($_GET['confirmationnumber']))
	{
		file_put_contents("manualaction","yes");
		$number=$_GET['confirmationnumber'];
		echo("<h3 style='color:#008000'>Manual Action Added</h3>$numbers[$number]");
	}
?>
