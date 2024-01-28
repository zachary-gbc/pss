<?php include('../other/pretitle.php'); ?>
<title>Variables</title>
<?php include('../other/posttitle.php'); ?>

<h3 style="margin: 3px 0px 0px 5px">Variables</h3>
<?php
	if(isset($_POST['submit']))
	{
		$x=0;
		while(isset($_POST["id$x"]))
		{
			$id=str_replace("'","''",$_POST["id$x"]);
			$name=str_replace("'","''",$_POST["name$x"]);
			$value=str_replace("'","''",$_POST["value$x"]);
      
			$update="UPDATE Variables SET Var_Name='$name', Var_Value='$value' WHERE (Var_ID='$id')";
			if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
			$x++;
		}

		if(isset($_POST['newvar']) && trim($_POST['newvar']) != "")
		{
			$newvar=str_replace("'","''",$_POST["newvar"]);
			$insert="INSERT INTO Variables(Var_Name) VALUES('$newvar')";
			if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
		}
	}

	$variables="SELECT * FROM Variables ORDER BY Var_Name"; $table=""; $x=0;
	if(!$rs=mysqli_query($db,$variables)) { echo("Unable to Run Query: $variables"); exit; }
	while($row = mysqli_fetch_array($rs))
	{
		$table.=("<th>" . $row['Var_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Var_ID'] . "\" /></th>\n");
		$table.=("<td><input type='text' name='name$x' value=\"" . $row['Var_Name'] . "\" /></td>\n");
		$table.=("<td><input type='text' name='value$x' value=\"" . $row['Var_Value'] . "\" /></td>\n");
		$table.=("</tr>\n"); $x++;
	}

	echo("<form method='post' action=''>\n<table>\n<tr>\n<th>ID</th>\n<th>Name</th>\n<th>Value</th>\n</tr>\n$table</table>\n");
	echo("<br>New Variable Name: <input type='text' name='newvar' />\n <br><br><input type='submit' name='submit' value='Submit Changes' />\n</form>\n");
?>

<?php include('../other/footer.php'); ?>
