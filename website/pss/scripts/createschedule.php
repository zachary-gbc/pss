<?php
	include('dblogin.php');
	if(isset($_GET['device']))
	{
		$devicemac=$_GET['device']; $cron="";
		$schedule="SELECT * FROM Schedules INNER JOIN Devices ON Schedules.Sch_Device=Devices.Dev_ID WHERE (Dev_MAC='$devicemac') AND (Sch_Active='1')";
		if(!$rs=mysqli_query($db,$schedule)) { echo("Unable to Run Query: $schedule"); exit; }
		while($row = mysqli_fetch_array($rs))
		{
			$s=0; $e=0;
			if($row['Sch_OneTimeRecurring'] == "O")
			{
				if(substr($row['Sch_OTStartDateTime'],0,4) == date("Y"))
				{
					$sdatetime=$row['Sch_OTStartDateTime'];
					$smonth=substr($row['Sch_OTStartDateTime'],4,2); if(substr($smonth,0,1) == 0) { $smonth=substr($smonth,1); }
					$sdom=substr($row['Sch_OTStartDateTime'],6,2); if(substr($sdom,0,1) == 0) { $sdom=substr($sdom,1); }
					$shour=substr($row['Sch_OTStartDateTime'],8,2); if(substr($shour,0,1) == 0) { $shour=substr($shour,1); }
					$sminute=substr($row['Sch_OTStartDateTime'],10,2); if(substr($sminute,0,1) == 0) { $sminute=substr($sminute,1); }
					$duration=$row['Sch_DurationMinutes'];
					$sdow="*"; $edow="*"; $s=1;
					if($duration != 0)
					{
						$enddate=strtotime("+$duration minutes",strtotime($sdatetime));
						$emonth=date("n",$enddate);
						$edom=date("j",$enddate);
						$ehour=date("G",$enddate);
						$eminute=intval(date("i",$enddate));
						$e=1;
					}
				}
			}
			else
			{
				$sminute=$row['Sch_RMinute'];
				$shour=$row['Sch_RHour'];
				$sdom=$row['Sch_RDOM']; if($sdom == "*") { $ddom=date("j"); } else { $ddom=$sdom; }
				$smonth=$row['Sch_RMonth']; if($smonth == "*") { $dmonth=date("n"); } else { $dmonth=$smonth; }
				$sdow=$row['Sch_RDOW'];
				$duration=$row['Sch_DurationMinutes'];
				$edow=$sdow; $s=1;
				$sdatetime=mktime($shour,$sminute,0,$dmonth,$ddom,date("Y"));
				if($duration != 0)
				{
					$enddate=strtotime("+$duration minutes",$sdatetime);
					if($smonth == "*") { $emonth="*"; } else { $emonth=date("n",$enddate); }
					if($sdom == "*") { $edom="*"; } else { $edom=date("j",$enddate); }
					$ehour=date("G",$enddate);
					$eminute=intval(date("i",$enddate));
					$e=1;
				}
		}
			$onoff=$row['Sch_ScreenOnOff'];
			$input=$row['Sch_ScreenInput'];
			$lorg=$row['Sch_LoopGraphic'];

			if($s == 1) { $cron.="$sminute $shour $sdom $smonth $sdow pi bash /home/pi/scripts/loopstart.sh $lorg $onoff $input &\n"; }
			if($e == 1) { $cron.="$eminute $ehour $edom $emonth $edow pi bash /home/pi/scripts/loopstop.sh $onoff $input &\n"; }
		}
	echo($cron);
	}
?>
