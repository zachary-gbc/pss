<?php include('../other/pretitle.php'); ?>
<title>Schedule</title>
<?php include('../other/posttitle.php'); ?>

<?php
  if(isset($_GET['devid'])) { $devid=$_GET['devid']; } elseif(isset($POST['devid'])) { $devid=$POST['devid']; } else { $devid=""; }
	if(isset($_POST['submit']) && $devid != "")
	{
		$x=0;
		while(isset($_POST["id$x"]))
		{
      $id=str_replace("'","''",$_POST["id$x"]);
      $name=str_replace("'","''",$_POST["name$x"]);
      $loopgraphic=str_replace("'","''",$_POST["loopgraphic$x"]);
      $duration=str_replace("'","''",$_POST["duration$x"]);
      $screen=str_replace("'","''",$_POST["screenonoff$x"]);
      $input=str_replace("'","''",$_POST["input$x"]);
      if(isset($_POST["active$x"])) { $active='1'; } else { $active='0'; }
      if($_POST["otr$x"] == "O")
      {
        $date=str_replace("'","''",$_POST["date$x"]);
        $time=str_replace("'","''",$_POST["time$x"]);
        $startdatetime=date("YmdHi",strtotime($date . " " . $time));
        $update="UPDATE Schedules SET Sch_Name='$name', Sch_LoopGraphic='$loopgraphic', Sch_OTStartDateTime='$startdatetime', Sch_DurationMinutes='$duration', Sch_ScreenOnOff='$screen', Sch_ScreenInput='$input',  Sch_Active='$active', Sch_UpdateDateTime=now() WHERE (Sch_ID='$id')";
        if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
      }
        else
      {
        $hour=str_replace("'","''",$_POST["hour$x"]);
        $minute=str_replace("'","''",$_POST["minute$x"]);
        $day=str_replace("'","''",$_POST["day$x"]);
        $month=str_replace("'","''",$_POST["month$x"]);
        $dow=str_replace("'","''",$_POST["dow$x"]);
        $update="UPDATE Schedules SET Sch_Name='$name', Sch_LoopGraphic='$loopgraphic', Sch_RMinute='$minute', Sch_RHour='$hour', Sch_RDOM='$day', Sch_RMonth='$month', Sch_RDOW='$dow', Sch_DurationMinutes='$duration', Sch_ScreenOnOff='$screen', Sch_ScreenInput='$input',  Sch_Active='$active', Sch_UpdateDateTime=now() WHERE (Sch_ID='$id')";
        if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
      }

			if(isset($_POST["delete$x"])) { $delete="DELETE FROM Schedules WHERE (Sch_ID='$id')"; if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; } }
			$x++;
		}

		if(isset($_POST['otnew']) && trim($_POST['otnew']) != "")
		{
			$newschedule=str_replace("'","''",$_POST["otnew"]);
			$insert="INSERT INTO Schedules(Sch_Name, Sch_Device, Sch_LoopGraphic, Sch_ScreenInput, Sch_OneTimeRecurring, Sch_UpdateDateTime) VALUES('$newschedule', '$devid', '0', '0', 'O', now())";
			if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
		}
		if(isset($_POST['recnew']) && trim($_POST['recnew']) != "")
		{
			$newschedule=str_replace("'","''",$_POST["recnew"]);
			$insert="INSERT INTO Schedules(Sch_Name, Sch_Device, Sch_LoopGraphic, Sch_ScreenInput, Sch_OneTimeRecurring, Sch_UpdateDateTime) VALUES('$newschedule', '$devid', '0', '0', 'R', now())";
			if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
		}
	}

  $locations=""; $orientation=""; $room=""; $locationname=""; $x=0;
  $alldevices="SELECT * FROM Devices ORDER BY Dev_RoomBuilding, Dev_LocName";
  if(!$rs=mysqli_query($db,$alldevices)) { echo("Unable to Run Query: $alldevices"); exit; }
  while($row = mysqli_fetch_array($rs))
  { $rooms[$row['Dev_RoomBuilding']][$row['Dev_ID']]=$row['Dev_LocName']; $orientations[$row['Dev_ID']]=$row['Dev_Orientation']; }
  foreach($rooms as $roomkey => $devices)
  {
    $locations.="<optgroup label='$roomkey'>";
    foreach($devices as $deviceid => $locname)
    {
      $s=""; if($devid == $deviceid) { $s="selected='selected'"; $locationname=$locname; $orientation=$orientations[$deviceid]; }
      $locations.="<option $s value='$deviceid'>$locname</option>";
    }
    $locations.="</optgroup>";
  }

  if($devid != "")
  {
    $lgselect=array(); $lgoptions=""; $ottable=""; $rtable=""; $otitem=false; $ritem=false; $x=0;

    $allloops="SELECT * FROM Loops WHERE (Lop_Orientation='$orientation') ORDER BY Lop_Name";
    if(!$rs=mysqli_query($db,$allloops)) { echo("Unable to Run Query: $allloops"); exit; }
    while($row = mysqli_fetch_array($rs)) { $lgselect["Loops"]["L-".$row['Lop_ID']]=$row['Lop_Name']; }

    $allgraphics="SELECT * FROM Graphics WHERE (Gr_Delete='N') ORDER BY Gr_Category, Gr_Name";
    if(!$rs=mysqli_query($db,$allgraphics)) { echo("Unable to Run Query: $allgraphics"); exit; }
    while($row = mysqli_fetch_array($rs)) { $lgselect[($row['Gr_Category'] . " Graphics")]["G-".$row['Gr_ID']."-$orientation"]=$row['Gr_Name']; }

    $schedules="SELECT * FROM Schedules WHERE (Sch_Device='$devid') ORDER BY Sch_Active DESC, Sch_Name";
    if(!$rs=mysqli_query($db,$schedules)) { echo("Unable to Run Query: $schedules"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
      if($row['Sch_OneTimeRecurring'] == "O")
      {
        $date=(substr($row['Sch_OTStartDateTime'],0,4) . "-" . substr($row['Sch_OTStartDateTime'],4,2) . "-" . substr($row['Sch_OTStartDateTime'],6,2));
        $time=(substr($row['Sch_OTStartDateTime'],8,2) . ":" . substr($row['Sch_OTStartDateTime'],10,2));
        $loopgraphic=$row['Sch_LoopGraphic'];
        $duration=$row['Sch_DurationMinutes'];
        $screenonoff=$row['Sch_ScreenOnOff'];
        $input=$row['Sch_ScreenInput'];
        $active=$row['Sch_Active'];
        $otitem=true;

        $actives=""; if($row['Sch_Active'] == '1') { $actives="checked='checked'"; }
        if($row['Sch_ScreenOnOff'] == "1") { $yes="selected='selected'"; $no=""; } else { $yes=""; $no="selected='selected'"; }
        if($row['Sch_LoopGraphic'] == "1") { $nols="selected='selected'"; } else { $nols=""; } $lgoptions="";
        foreach($lgselect as $groupname => $group)
        {
          $lgoptions.="<optgroup label='$groupname'>\n";
          foreach($group as $lgid => $lgname)
          { $s=""; if($lgid == $row['Sch_LoopGraphic']) { $s="selected='selected'"; } $lgoptions.="<option value='$lgid' $s>$lgname</option>\n"; }
          $lgoptions.="</optgroup>\n";
        }

        if($row['Sch_Active'] == "0") { $ottable.=("<tr bgcolor='#DE5D5D'>\n"); } else { $ottable.=("<tr>\n"); }
        $ottable.=("<th>" . $row['Sch_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Sch_ID'] . "\" /><input type='hidden' name='otr$x' value='O' /></th>\n");
        $ottable.=("<th><input type='text' style='width:200px' name='name$x' value=\"" . $row['Sch_Name'] . "\" /></th>\n");
        $ottable.=("<th><select name='loopgraphic$x'>\n<option value='0'>Loop Off</option>\n<option value='1' $nols>No Loop</option>\n$lgoptions</select></th>\n");
        $ottable.=("<th><input type='date' style='width:75px' name='date$x' value='$date' /></th>\n");
        $ottable.=("<th><input type='time' style='width:75px' name='time$x' value='$time' /></th>\n");
        $ottable.=("<th><input type='number' style='width:50px' name='duration$x' value='$duration' /></th>\n");
        $ottable.=("<th><select name='screenonoff$x'><option value='1' $yes>Yes</option><option value='0' $no>No</option></select></th>\n");
        $ottable.=("<th><input type='number' name='input$x' value=\"" . $row['Sch_ScreenInput'] . "\" min='0' max='3' /></th>\n");
        $ottable.=("<th><input type='checkbox' $actives name='active$x' /></th>\n");
        $ottable.=("<th><input type='checkbox' name='delete$x' /></th>\n");
        $ottable.=("</tr>\n");
        $x++;
      }
      else
      {
        $months=""; $days=""; $dows=""; $hours=""; $minutes=""; $alldows=array(1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday",7=>"Sunday");
        $loopgraphic=$row['Sch_LoopGraphic'];
        $duration=$row['Sch_DurationMinutes'];
        $screenonoff=$row['Sch_ScreenOnOff'];
        $input=$row['Sch_ScreenInput'];
        $active=$row['Sch_Active'];
        $ritem=true;

        $actives=""; if($row['Sch_Active'] == '1') { $actives="checked='checked'"; }
        if($row['Sch_ScreenOnOff'] == "1") { $yes="selected='selected'"; $no=""; } else { $yes=""; $no="selected='selected'"; }
        if($row['Sch_LoopGraphic'] == "1") { $nols="selected='selected'"; } else { $nols=""; } $lgoptions="";
        foreach($lgselect as $groupname => $group)
        {
          $lgoptions.="<optgroup label='$groupname'>\n";
          foreach($group as $lgid => $lgname)
          { $s=""; if($lgid == $row['Sch_LoopGraphic']) { $s="selected='selected'"; } $lgoptions.="<option value='$lgid' $s>$lgname</option>\n"; }
          $lgoptions.="</optgroup>\n";
        }

        for($y=1; $y<=12; $y++) { $s=""; if($row['Sch_RMonth'] == $y) { $s="selected='selected'"; } $months.=("<option value='$y' $s>" . date("F",mktime(1,1,1,$y,1,1)) . "</option>"); }
        for($y=1; $y<=31; $y++) { $s=""; if($row['Sch_RDOM'] == $y) { $s="selected='selected'"; } $days.=("<option value='$y' $s>" . date("j",mktime(1,1,1,1,$y,1)) . "</option>"); }
        for($y=1; $y<=7; $y++) { $s=""; if($row['Sch_RDOW'] == $y) { $s="selected='selected'"; } $dows.=("<option value='$y' $s>" . $alldows[$y] . "</option>"); }
        for($y=1; $y<=23; $y++) { $s=""; if($row['Sch_RHour'] == $y) { $s="selected='selected'"; } $hours.=("<option value='$y' $s>" . date("g a",mktime($y,1,1,1,1,1)) . "</option>"); }
        for($y=1; $y<=59; $y++) { $s=""; if($row['Sch_RMinute'] == $y) { $s="selected='selected'"; } $minutes.=("<option value='$y' $s>" . date("i",mktime(1,$y,1,1,1,1)) . "</option>"); }

        if($row['Sch_Active'] == "0") { $rtable.=("<tr bgcolor='#DE5D5D'>\n"); } else { $rtable.=("<tr>\n"); }
        $rtable.=("<th>" . $row['Sch_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Sch_ID'] . "\" /><input type='hidden' name='otr$x' value='R' /></th>\n");
        $rtable.=("<th><input type='text' style='width:200px' name='name$x' value=\"" . $row['Sch_Name'] . "\" /></th>\n");
        $rtable.=("<th><select name='loopgraphic$x'>\n<option value='0'>Loop Off</option>\n<option value='1' $nols>No Loop</option>\n$lgoptions</select></th>\n");
        $rtable.=("<th><select name='month$x'><option value='*'>All</option>$months</select></th>\n");
        $rtable.=("<th><select name='day$x'><option value='*'>All</option>$days</select></th>\n");
        $rtable.=("<th><select name='dow$x'><option value='*'>All</option>$dows</select></th>\n");
        $rtable.=("<th><select name='hour$x'>$hours</select></th>\n");
        $rtable.=("<th><select name='minute$x'><option value='0'>0</option>$minutes</select></th>\n");
        $rtable.=("<th><input type='number' style='width:50px' name='duration$x' value='$duration' /></th>\n");
        $rtable.=("<th><select name='screenonoff$x'><option value='1' $yes>Yes</option><option value='0' $no>No</option></select></th>\n");
        $rtable.=("<th><input type='number' name='input$x' value=\"" . $row['Sch_ScreenInput'] . "\" min='0' max='3' /></th>\n");
        $rtable.=("<th><input type='checkbox' $actives name='active$x' /></th>\n");
        $rtable.=("<th><input type='checkbox' name='delete$x' /></th>\n");
        $rtable.=("</tr>\n");
        $x++;
      }
    }

    echo("<form method='post' action='?devid=$devid'>\n<h3>Schedule for $locationname</h3>\n");
    
    echo("<h4 style='margin:0px'>One-Time Schedules:</h4>\n<table>\n");
    if($otitem == true)
    {
      echo("<tr>\n<th><br>ID</th>\n<th><br>Name</th>\n<th><br>Loop/Graphic</th>\n<th><br>Date</th>\n<th><br>Time</th>\n<th>Duration<br>(Minutes)</th>\n");
      echo("<th>Screen<br>On/Off</th>\n<th>Change<br>Input</th>\n<th><br>Active</th>\n<th><br>Delete</th>\n</tr>\n$ottable");
    }
    echo("<tr><th>&nbsp;</th></tr>\n<tr bgcolor='94DE94'>\n<th colspan='10' style='text-align:left'> -- NEW ONE TIME SCHEDULE -- </th>\n</tr>\n");
    echo("<tr>\n<td colspan='10'><input type='text' name='otnew' placeholder='Input New Schedule Name Here'><td>\n</tr>\n");
    echo("</table>\n");

    echo("<h4 style='margin:0px'><br>Recurring Schedules:</h4>\n<table>\n");
    if($ritem == true)
    {
      echo("<tr>\n<th><br>ID</th>\n<th><br>Name</th>\n<th><br>Loop/Graphic</th>\n<th><br>Month</th>\n<th>Day of<br>Month</th>\n<th>Day of<br>Week</th>\n<th><br>Hour</th>\n");
      echo("<th><br>Minute</th>\n<th>Duration<br>(Minutes)</th>\n<th>Screen<br>On/Off</th>\n<th>Change<br>Input</th>\n<th><br>Active</th>\n<th><br>Delete</th>\n</tr>\n$rtable");
    }
    echo("<tr><th>&nbsp;</th></tr>\n<tr bgcolor='94DE94'>\n<th colspan='13' style='text-align:left'> -- NEW RECURRING SCHEDULE -- </th>\n</tr>\n");
    echo("<tr>\n<td colspan='13'><input type='text' name='recnew' placeholder='Input New Schedule Name Here'><td>\n</tr>\n");
    echo("</table>\n");

    echo("<input type='hidden' value='$devid' name='devid' /><input type='submit' name='submit' value='Submit Changes' />\n</form>\n");
  }
  else { echo("<form method='get' action=''><h3>Schedule for: <select name='devid'>$locations</select> &nbsp; <input type='submit' value='Open Schedule' /></h3></form>"); }
?>

<?php include('../other/footer.php'); ?>
