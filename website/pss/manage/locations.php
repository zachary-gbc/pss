<?php include('../other/pretitle.php'); ?>
<title>Locations</title>
<?php include('../other/posttitle.php'); ?>

<h3 style="margin: 3px 0px 0px 5px">Locations</h3>
<?php
	if(isset($_POST['submit']))
	{
		$x=1;
		while(isset($_POST["id$x"]))
		{
			$id=str_replace("'","''",$_POST["id$x"]);
			$name=str_replace("'","''",$_POST["name$x"]);
			$room=str_replace("'","''",$_POST["room$x"]);
			$device=str_replace("'","''",$_POST["device$x"]);
			$orientation=str_replace("'","''",$_POST["orientation$x"]);

			$update="UPDATE Locations SET Loc_Name='$name', Loc_Device='$device', Loc_BuildingRoom='$room', Loc_Orientation='$orientation' WHERE (Loc_ID='$id')";
			if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }

			if(isset($_POST["delete$x"])) { $delete="DELETE FROM Locations WHERE (Loc_ID='$id')";
			if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; } }
			$x++;
		}

		if(isset($_POST['newloc']) && trim($_POST['newloc']) != "")
		{
			$newloc=str_replace("'","''",$_POST["newloc"]);
			$insert="INSERT INTO Locations(Loc_Name, Loc_BuildingRoom, Loc_UpdateDateTime) VALUES('$newloc', 'Unknown', now())";
			if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
		}
	}

	$devices="SELECT Dev_Name, Dev_IP FROM Devices ORDER BY Dev_Name"; $deviceslist=array(); $ips=array();
	if(!$rs=mysqli_query($db,$devices)) { echo("Unable to Run Query: $devices"); exit; }
	while($row = mysqli_fetch_array($rs)) { $deviceslist[$row['Dev_Name']]=$row['Dev_Name']; $ips[$row['Dev_Name']]=$row['Dev_IP']; }

	$locations="SELECT * FROM Locations ORDER BY Loc_Name"; $table=""; $devices=""; $x=0;
	if(!$rs=mysqli_query($db,$locations)) { echo("Unable to Run Query: $locations"); exit; }
	while($row = mysqli_fetch_array($rs))
	{
		foreach($deviceslist as $id => $dev) { $s=""; if($id == $row['Loc_Device']) { $s="selected='selected'"; } $devices.="<option value='$id' $s>$dev</option>"; }
		if(($x%2) == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); } $x++;
		if(isset($ips[$row['Loc_Device']])) { $ip=$ips[$row['Loc_Device']]; } else { $ip=""; }
		if($row['Loc_Orientation'] == "L") { $ls="selected='selected'"; $ps=""; } else { $ls=""; $ps="selected='selected'"; }
		$table.=("<th>" . $row['Loc_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Loc_ID'] . "\" /></th>\n");
		$table.=("<td><input type='text' name='name$x' value=\"" . $row['Loc_Name'] . "\" /></td>\n");
		$table.=("<td><input type='text' name='room$x' value=\"" . $row['Loc_BuildingRoom'] . "\" /></td>\n");
		$table.=("<td><select name='device$x'><option value='0'>None</option>$devices</select></td>\n");
		$table.=("<td><select name='orientation$x'><option value='L' $ls>Landscape</option><option value='P' $ps>Portrait</option></select></td>\n");
		$table.=("<td>$ip</td>\n");
		$table.=("<td><input type='checkbox' name='delete$x' /></td>\n");
		$table.=("</tr>\n"); $devices="";
	}

	echo("<form method='post' action=''>\n<table>\n<tr>\n<th>ID</th>\n<th>Name</th>\n<th>Building/Room</th>\n<th>Device</th>\n<th>Orientation</th>\n<th>Device IP</th>\n<th>Delete</th>\n</tr>\n$table</table>\n");
	echo("<br>New Location Name: <input type='text' name='newloc' />\n <br><br><input type='submit' name='submit' value='Submit Changes' />\n</form>\n");
?>

<?php include('../other/footer.php'); ?>
