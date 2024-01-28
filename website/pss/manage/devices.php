<?php include('../other/pretitle.php'); ?>
<title>Devices</title>
<?php include('../other/posttitle.php'); ?>

<h3 style="margin: 3px 0px 0px 5px">Devices</h3>
<?php
	if(isset($_POST['submit']))
	{
		$x=1;
		while(isset($_POST["id$x"]))
		{
			$id=str_replace("'","''",$_POST["id$x"]);
			$name=str_replace("'","''",$_POST["name$x"]);
			$type=str_replace("'","''",$_POST["type$x"]);
			$mac=str_replace("'","''",$_POST["mac$x"]);
			$ip=str_replace("'","''",$_POST["ip$x"]);
			$locname=str_replace("'","''",$_POST["locname$x"]);
			$roombuilding=str_replace("'","''",$_POST["roombuilding$x"]);
			$orientation=str_replace("'","''",$_POST["orientation$x"]);

			$update="UPDATE Devices SET Dev_Name='$name', Dev_Type='$type', Dev_MAC='$mac', Dev_IP='$ip', Dev_LocName='$locname', Dev_RoomBuilding='$roombuilding', Dev_Orientation='$orientation', Dev_UpdateDateTime=now() WHERE (Dev_ID='$id')";
			if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
			if(isset($_POST["delete$x"])) { $delete="DELETE FROM Devices WHERE (Dev_ID='$id')"; if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; } }
			$x++;
		}

		if(isset($_POST['newdev']) && trim($_POST['newdev']) != "")
		{
			$newdev=str_replace("'","''",$_POST["newdev"]);
			$insert="INSERT INTO Devices(Dev_Name, Dev_Type, Dev_LocName, Dev_RoomBuilding, Dev_UpdateDateTime) VALUES('$newdev', 'Unknown', 'Unknown', 'Unknown', now())";
			if(!mysqli_query($db,$insert)) { echo("Unable to Add New Device"); exit; }
		}
	}

	$devices="SELECT * FROM Devices ORDER BY Dev_RoomBuilding, Dev_LocName, Dev_Name"; $table=""; $x=0;
	if(!$rs=mysqli_query($db,$devices)) { echo("Unable to Run Query: $devices"); exit; }
	while($row = mysqli_fetch_array($rs))
	{
		if(($x%2) == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); } $x++;
		if($row['Dev_Orientation'] == "L") { $ls="selected='selected'"; $ps=""; } else { $ls=""; $ps="selected='selected'"; }
		$table.=("<th>" . $row['Dev_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Dev_ID'] . "\" /></th>\n");
		$table.=("<td><input type='text' name='room$x' value=\"" . $row['Dev_RoomBuilding'] . "\" /></td>\n");
		$table.=("<td><input type='text' name='name$x' value=\"" . $row['Dev_LocName'] . "\" /></td>\n");
		$table.=("<td><select name='orientation$x'><option value='L' $ls>Landscape</option><option value='P' $ps>Portrait</option></select></td>\n");
		$table.=("<td><input type='text' name='name$x' value=\"" . $row['Dev_Name'] . "\" /></td>\n");
		$table.=("<td><input type='text' name='type$x' value=\"" . $row['Dev_Type'] . "\" /></td>\n");
		$table.=("<td><input type='text' name='mac$x' value=\"" . $row['Dev_MAC'] . "\" /></td>\n");
		$table.=("<td><input type='text' name='ip$x' value=\"" . $row['Dev_IP'] . "\" /></td>\n");
		$table.=("<td><input type='checkbox' name='delete$x' /></td>\n");
		$table.=("</tr>\n");
	}

	$roomorbuilding="SELECT VariableValue FROM Variables WHERE (VariableName='RoomOrBuilding)"; $roombuilding="Room";
	if(!$rs=mysqli_query($db,$roomorbuilding)) { echo("Unable to Run Query: $roomorbuilding"); exit; }
	while($row = mysqli_fetch_array($rs)) { $roombuilding=$row['VariableValue']; }

	echo("<form method='post' action=''>\n<table>\n<tr>\n<th>ID</th>\n<th>$roombuilding</th>\n<th>Location</th>\n<th>Orientation</th>\n");
	echo("<th>Name</th>\n<th>Type</th>\n<th>MAC Address</th>\n<th>IP Address</th>\n<th>Delete</th>\n</tr>\n$table</table>\n");
	echo("<br>New Device Name: <input type='text' name='newdev' />\n <br><br><input type='submit' name='submit' value='Submit Changes' />\n</form>\n");
?>

<?php include('../other/footer.php'); ?>
