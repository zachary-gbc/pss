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
					$smonth=substr($row['Sch_OTStartDateTime'],4,2);
					$sday=substr($row['Sch_OTStartDateTime'],6,2);
					$shour=substr($row['Sch_OTStartDateTime'],8,2);
					$sminute=substr($row['Sch_OTStartDateTime'],10,2);
					$duration=$row['Sch_DurationMinutes'];
					$sdom="*"; $s=1;
					if($duration != 0)
					{
						$end=date('YmdHi',strtotime("+$duration minutes",strtotime("1111-$month-$day $hour:$minute:00"))); $e=1;
						$emonth=date("m",$end);
						$edom=date("j",$end);
						$ehour=date("H",$end);
						$eminute=date("i",$end);
					}
				}
			}
			else
			{
				$sminute=$row['Sch_RMinute'];
				$shour=$row['Sch_RHour'];
				$sdom=$row['Sch_RDOM'];
				$smonth=$row['Sch_RMonth'];
				$sdow=$row['Sch_RDOW'];
				$duration=$row['Sch_DurationMinutes'];
				if($duration != 0)
				{
					$daystoadd=floor(($duration / 86400));
					$hourstoadd=floor(($duration / 60));
					$minutestoadd=ceil(($duration / 60));

					if($sdom == "*") { $edom=$sdom; } else { $edom=($sdom + $daystoadd); }
					if($shour == "*") { $ehour=$shour; } else { $ehour=($shour + $hourstoadd); }
					if($sminute == "*") { $eminute=$sminute; } else { $eminute=($sminute + $minutestoadd); }
					$emonth=$smonth; $e=1;
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
