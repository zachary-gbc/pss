<?php
	include('dblogin.php');
	$aloopstobuild=array(); $mloopstobuild=array(); $amissing=array(); $mmissing=array(); $now=date("Y-m-d H:i:s"); $month=date("n"); $todaydate=date("Y-m-d");

	$automaticloops="SELECT Lop_ID, Lop_Orientation FROM Loops INNER JOIN AutomaticLoopDates ON Loops.Lop_ID=AutomaticLoopDates.AD_Loop";
	if(!$rs=mysqli_query($db,$automaticloops)) { echo("Unable to Run Query: $automaticloops"); exit; }
	while($row = mysqli_fetch_array($rs)) { $aloopstobuild[$row['Lop_ID']]=$row['Lop_Orientation']; }

	if(count($aloopstobuild) > 0)
	{
		foreach($aloopstobuild as $loopid => $orientation)
		{
			$graphics="SELECT Gr_ID, Gr_Name FROM AutomaticLoopDates INNER JOIN Graphics ON AutomaticLoopDates.AD_Graphic=Graphics.Gr_ID WHERE (AD_Loop='$loopid') AND ((AD_Date='$todaydate') OR (AD_Month='$month') OR ((AD_StartDateRange<='$todaydate') AND (AD_EndDateRange>='$todaydate')))"; $vlcfilecontents=""; $concatfilecontents="";
			if(!$rs=mysqli_query($db,$graphics)) { echo("Unable to Run Query: $graphics"); exit; }
			while($row = mysqli_fetch_array($rs))
			{
				$graphicid=$row['Gr_ID']; $exists=false;
				if(file_exists("/var/www/html/pss/files/$graphicid-$orientation.mp4"))
				{
					$vlcfilecontents.="/var/www/html/pss/files/$graphicid" . "-$orientation.mp4\n";
					$concatfilecontents.="file '/var/www/html/pss/files/$graphicid" . "-$orientation.mp4'\n";
					$exists=true;
				}
				if($exists == false) { $amissing[$loopid][$graphicid]=$row['Gr_Name']; }
			}
			if($vlcfilecontents != "") { file_put_contents("/var/www/html/pss/files/loop-$loopid.m3u", $vlcfilecontents); }
			if($concatfilecontents != "") { file_put_contents("/var/www/html/pss/files/loop-$loopid.concat", $concatfilecontents); }
		}
	}

	$manualloops="SELECT Lop_ID, Lop_Orientation FROM Loops INNER JOIN LoopGraphics ON Loops.Lop_ID=LoopGraphics.LG_Loop INNER JOIN Graphics ON LoopGraphics.LG_Graphic=Graphics.Gr_ID WHERE (Lop_UpdateDateTime > Lop_LastCreateDateTime) OR (Gr_UpdateDateTime > Lop_LastCreateDateTime) OR (Lop_LastCreateDateTime IS NULL) GROUP BY Lop_ID, Lop_Orientation";
	if(!$rs=mysqli_query($db,$manualloops)) { echo("Unable to Run Query: $manualloops"); exit; }
	while($row = mysqli_fetch_array($rs)) { $mloopstobuild[$row['Lop_ID']]=$row['Lop_Orientation']; }

	if(count($mloopstobuild) > 0)
	{
		foreach($mloopstobuild as $loopid => $orientation)
		{
			$graphics="SELECT Gr_ID, Gr_Name FROM LoopGraphics INNER JOIN Graphics ON LoopGraphics.LG_Graphic=Graphics.Gr_ID WHERE (LG_Loop='$loopid') ORDER BY LG_Order"; $vlcfilecontents=""; $concatfilecontents="";
			if(!$rs=mysqli_query($db,$graphics)) { echo("Unable to Run Query: $graphics"); exit; }
			while($row = mysqli_fetch_array($rs))
			{
				$graphicid=$row['Gr_ID']; $exists=false;
				if(file_exists("/var/www/html/pss/files/$graphicid-$orientation.mp4"))
				{
					$vlcfilecontents.="/var/www/html/pss/files/$graphicid" . "-$orientation.mp4\n";
					$concatfilecontents.="file '/var/www/html/pss/files/$graphicid" . "-$orientation.mp4'\n";
					$exists=true;
				}
				if($exists == false) { $mmissing[$loopid][$graphicid]=$row['Gr_Name']; }
			}
			if($vlcfilecontents != "") { file_put_contents("/var/www/html/pss/files/loop-$loopid.m3u", $vlcfilecontents); }
			if($concatfilecontents != "") { file_put_contents("/var/www/html/pss/files/loop-$loopid.concat", $concatfilecontents); }
		}
	}

	$updatevar="UPDATE Variables SET Var_Value='$now' WHERE (Var_Name='Last-Loop-Created')";
	if(!mysqli_query($db,$updatevar)) { echo("Unable to Run Query: $updatevar"); exit; }
?>