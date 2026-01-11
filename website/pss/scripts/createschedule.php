<?php
	include('dblogin.php');
	if(isset($_GET['device']))
	{
		$devicemac=$_GET['device']; $cron="";
		$schedule="SELECT * FROM Schedules INNER JOIN Devices ON Schedules.Sch_Device=Devices.Dev_ID WHERE (Dev_MAC='$devicemac') AND (Sch_Active='1')";
		if(!$rs=mysqli_query($db,$schedule)) { echo("Unable to Run Query: $schedule"); exit; }
		while($row = mysqli_fetch_array($rs))
		{
			$use=false;
			if($row['Sch_OneTimeRecurring'] == "O")
			{
				if(substr($row['Sch_OTStartDateTime'],0,4) == date("Y"))
				{
					$smonth=substr($row['Sch_OTStartDateTime'],4,2); if(substr($smonth,0,1) == 0) { $smonth=substr($smonth,1); }
					$sdom=substr($row['Sch_OTStartDateTime'],6,2); if(substr($sdom,0,1) == 0) { $sdom=substr($sdom,1); }
					$shour=substr($row['Sch_OTStartDateTime'],8,2); if(substr($shour,0,1) == 0) { $shour=substr($shour,1); }
					$sminute=substr($row['Sch_OTStartDateTime'],10,2); if(substr($sminute,0,1) == 0) { $sminute=substr($sminute,1); }
					$sdow="*";
					$use=true;
				}
			}
			else
			{
				$smonth=$row['Sch_RMonth'];
				$sdom=$row['Sch_RDOM'];
				$shour=$row['Sch_RHour'];
				$sminute=$row['Sch_RMinute'];
				$sdow=$row['Sch_RDOW'];
				$use=true;
			}
			$powerstart=("PS-" . $row['Sch_ScreenPowerStart']);
			$powerend=("PE-" . $row['Sch_ScreenPowerEnd']);
			$inputstart=("IS-" . $row['Sch_ScreenInputStart']);
			$inputend=("IE-" . $row['Sch_ScreenInputEnd']);
			$lorg=$row['Sch_LoopGraphic'];
			if(($row['Sch_DurationMinutes'] == "") || ($row['Sch_DurationMinutes'] == "NULL"))
			{ $duration="DM-0"; } else { $duration=("DM-" . $row['Sch_DurationMinutes']); }

			if($use == true) { $cron.="$sminute $shour $sdom $smonth $sdow pi bash /home/pi/scripts/loopstart.sh $lorg $powerstart $powerend $inputstart $inputend $duration &\n"; }
		}
		echo($cron);
	}
?>
