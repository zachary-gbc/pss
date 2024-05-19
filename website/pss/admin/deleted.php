<?php include('../other/pretitle.php'); ?>
<title>Deleted Graphics</title>
<?php include('../other/posttitle.php'); ?>

<h3 style="margin: 3px 0px 0px 5px">Locations</h3>
<?php
  if(isset($_POST['submit']))
  {
    for($x=0;$x<=$_POST['maxx'];$x++)
    {
      if(isset($_POST["recover$x"]))
      {
        $name=$_POST["name$x"]; substr($name,0," - Deleted ");
        $update="UPDATE Graphics SET Gr_Name='$name', Gr_Delete='N'";
        if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
      }
      if(isset($_POST["delete$x"]))
      {
        $id=$_POST["id$x"];
        $delete="DELETE FROM Graphics WHERE (Gr_ID='$id')"
        if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; }
      }
    }
  }

  $graphics="SELECT * FROM Graphics WHERE Gr_Delete='Y'"; $table=""; $x=0;
	if(!$rs=mysqli_query($db,$graphics)) { echo("Unable to Run Query: $graphics"); exit; }
	while($row = mysqli_fetch_array($rs))
	{
    $name=substr($row['Gr_Name'],0,strpos($row['Gr_Name'],"-deleted"));
    if(($x%2) == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); }
    $table.=("<th><input type='checkbox' name='delete$x' /></th><th><input type='checkbox' name='recover$x' /></th>");
    $table.=("<td>" . $row['Gr_Name'] . "<input type='hidden' name='name$x' value='" . $row['Gr_Name'] . "' /></td><td>");
    if(file_exists("../files/$id-L.mp4")) { $table.=("<a href='/pss/files/$id-L.mp4' target='_blank'>Preview Landscape</a> &nbsp; "); }
    if(file_exists("../files/$id-P.mp4")) { $table.=(" &nbsp; <a href='/pss/files/$id-P.mp4' target='_blank'>Preview Portrait</a> &nbsp; "); }
    $table.=("</td></tr>\n"); $x++;
  }

  if($table == "") { echo("No Deleted Graphics"); }
  else
  {
    echo("<form method='post' action=''><table>\n<tr>\n<th>Permanently<br>Delete</th>\n<th>Recover</th>\n<th>Graphic</th>\n<th>Preview</th>");
    echo("$table<br><input type='hidden' name='maxx' value='$x' /><input type='submit' name='submit' value='Delete Graphics' /></form>"); }
?>

<?php include('../other/footer.php'); ?>