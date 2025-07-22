<?php include('../other/pretitle.php'); ?>
<title>Graphic</title>
<?php include('../other/posttitle.php'); ?>

<?php
  $processing="SELECT Var_Value FROM Variables WHERE (Var_Name='Background-Processing')"; $backgroundprocessing="1";
  if(!$rs=mysqli_query($db,$processing)) { echo("Unable to Run Query: $processing"); exit; }
  while($row = mysqli_fetch_array($rs)) { $backgroundprocessing=$row['Var_Value']; }

  $id="";
  if($backgroundprocessing == "1") { echo("<h3 style='color:red'>Background Processing in Progress, Try Again Later</h3>\n"); }
  elseif(isset($_POST['submit']))
  {
    $id=$_POST['id'];
    $oldname=str_replace("'","''",$_POST['oldname']);
    $newname=str_replace("'","''",$_POST['newname']);
    $category=$_POST['category'];
    $uploadtype=$_POST['type'];
    if(isset($_POST['delete'])) { $delete="Y"; $newname=($newname . " - Deleted " . mktime()); } else { $delete="N"; }
    $namemessage=""; $uploadmessage=""; $uploaderror=false; $dbinserterr=false; $x=0;

    if($_FILES["uploadfile"]["error"] == 0) { $uploadname=$_FILES["uploadfile"]["name"]; $extarr=explode(".",$uploadname); $uploadext=strtolower(end($extarr)); }
    if($uploadext != "" && $uploadext != "mp4" && $uploadext != "png" && $uploadext != "jpg") { $uploadmessage.=("Only png, jpg, and mp4 files are currently allowed"); $uploaderror=true; }
    else
    {
      $filetypecheck=strtoupper($uploadtype . $uploadext);
      unlink("/var/www/html/pss/files/$id-$uploadtype.$uploadext");
      move_uploaded_file($_FILES["uploadfile"]["tmp_name"], ("/var/www/html/pss/files/$id-$uploadtype.$uploadext"));
      switch($_FILES["uploadfile"]["error"])
      {
        case 0: { $uploadmessage.=("File Uploaded Successfully<br>"); break; } 
        case 1: { $uploadmessage.=("File Size Too Large<br>"); $uploaderror=true; break; } 
        default: { $uploadmessage.=("Issue Uploading File, Please Try Again<br>"); $uploaderror=true; break; }
      }
    }
    if($_FILES["uploadfile"]["error"] == 4) { $uploadmessage=""; $uploaderror=false; }

    if($id != "new") { if($oldname != $newname) { $namemessage.=("Name Updated<br>"); } }

    if($id == "new")
    {
      $insert="INSERT INTO Graphics (Gr_Name, Gr_Category, Gr_UpdateDateTime, Gr_Delete) VALUES('$newname', '$category', now(), '$delete')";
      if(!$rs=mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); $dbinserterr=true; exit; }
      
      if($dbinserterr == true) { $uploadmessage.=("Error Updating Database, Please Try Again"); }
      else
      {
        $getid="SELECT Gr_ID FROM Graphics WHERE (Gr_Name='$newname') AND (Gr_Category='$category') ORDER BY Gr_ID";
        if(!$rs=mysqli_query($db,$getid)) { echo("Unable to Run Query: $getid"); exit; }
        while($row = mysqli_fetch_array($rs)) { $id=$row['Gr_ID']; }
        if($uploaderror == false) { rename("/var/www/html/pss/files/new-$uploadtype.$uploadext", "/var/www/html/pss/files/$id-$uploadtype.$uploadext"); }
      }
    }
    else
    {
      $update="UPDATE Graphics SET Gr_Name='$newname', Gr_Category='$category', Gr_UpdateDateTime=now(), Gr_Converted='N', Gr_Delete='$delete' WHERE (Gr_ID='$id')";
      if(!$rs=mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
    }

    if($uploaderror == false) { echo("<h3>Success</h3>\n<h4>$namemessage $uploadmessage</h4><hr>"); }
    else { echo("<h3>Some Errors, See Below</h3>\n<h4>$namemessage<br>$uploadmessage</h4><br><hr>"); }
  }

  if(isset($_GET['id'])) { $id=$_GET['id']; } else { $id="new"; }
  $categories=array("Caring", "Events", "Kids", "Mens", "Missions", "Womens", "Youth", "Other");

  $name=""; $category=""; $updated="";
  $data="SELECT * FROM Graphics WHERE (Gr_ID='$id')";
  if(!$rs=mysqli_query($db,$data)) { echo("Unable to Run Query: $data"); exit; }
  while($row = mysqli_fetch_array($rs))
  {
    $name=$row['Gr_Name']; $category=$row['Gr_Category']; $updated=strtotime($row['Gr_UpdateDateTime']);
    if($row['Gr_Delete'] == "N") { $delete=""; } else { $delete="checked='checked'"; }
  }

  if($id != "new") { echo("<h4>Edit: $name</h4>\n"); } else { echo("<h4>Add New Graphic</h4>\n"); }

  echo("<form method='post' action='' enctype='multipart/form-data'>\n");
  echo("<input type='hidden' name='id' value='$id' />\n<table>\n");
  
  echo("<tr>\n<th>Name: </th>\n<td><input type='text' name='newname' value=\"$name\" style='width:175px' /><input type='hidden' name='oldname' value='$name' /></td>\n");
  echo("<th colspan='2'><!--Check for Name Here--></td>\n</tr>\n");

  echo("<tr>\n<th>Category: </th>\n<td><select name='category'>");
  foreach($categories as $cat) { if($cat == $category) { $s="selected='selected'"; } else { $s=""; } echo("<option value='$cat' $s>$cat</option>"); }
  echo("</select>\n</td>\n");
  echo("<th>Upload: </th>\n<td><input type='file' name='uploadfile' /></td>\n</tr>\n");
  
  echo("<tr>\n<th>Upload Type: </th>\n<td><input type='radio' name='type' value='L' checked='checked'> Landscape &nbsp; <input type='radio' name='type' value='P'> Portrait</td>\n");
  if($id != "new") { echo("<th>Delete:</th>\n<th><input name='delete' type='checkbox' /></td>"); }
  else { echo("<td colspan='2'></td>\n"); }
  echo("</tr>\n");

  if($id == "new" && $backgroundprocessing != "1")
  { echo("<tr>\n<th colspan='2'><input type='submit' name='submit' value='Add New Graphic' /></th>\n<td colspan='2'></td>\n</tr>\n"); }
  elseif ($backgroundprocessing != "1")
  {
    echo("<tr>\n<th colspan='2'><input type='submit' name='submit' value='Submit Changes' /></th>\n");
    echo("<td colspan='2'><small>Last Updated: " . date("M d, Y g:i a", $updated) . "</small></td>\n</tr>\n");
  }
  echo("</table>\n</form>");

  if($id != "new")
  {
    echo("<br><div>\n");
    if(file_exists("../files/$id-L.mp4")) { echo(" &nbsp; <a href='/pss/files/$id-L.mp4' target='_blank'>Preview Landscape</a> &nbsp; "); }
    if(file_exists("../files/$id-P.mp4")) { echo(" &nbsp; <a href='/pss/files/$id-P.mp4' target='_blank'>Preview Portrait</a> &nbsp; "); }
    echo("</div>\n");

    echo("<br><h3 style='margin:0px'>Add Graphic to Automatic Loops</h3>");

    $aloops="SELECT Lop_ID, Lop_Name, Lop_Orientation FROM Loops WHERE (Lop_Type='Automatic')";
    if(!$rs=mysqli_query($db,$aloops)) { echo("Unable to Run Query: $aloops"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
      if($row['Lop_Orientation'] == "L") { $orientation="Landscape"; } else { $orientation="Portrait"; }
      $loop=$row['Lop_ID'];
      echo("<br><strong>" . $row['Lop_Name'] . " ($orientation):</strong><br>\n");
      echo("<form method='post' action='graphicautodates.php?graphic=$id&loop=$loop' id='$id-$loop' target='iframeform-$id-$loop'>\n");
      echo("</form>\n");
      echo("<iframe src='graphicautodates.php?graphic=$id&loop=$loop' id='iframeform' name='iframeform-$id-$loop' hidden></iframe>\n");
    }
  }

  echo("<br>"); $categories="";
  if(isset($_GET['graphicfilter']) && $_GET['graphicfilter'] != "All")
  {
    $graphicfilter="AND (Gr_Category='" . $_GET['graphicfilter'] . "')";
    echo("<h3>" . $_GET['graphicfilter'] . " Graphics (<a href='graphic.php'>All Graphics</a>)</h3>");
  }
  else
  {
    $graphicfilter=""; echo("<h3>All Graphics</h3>");
    $dbcategories="SELECT Gr_Category FROM Graphics GROUP BY Gr_Category ORDER BY Gr_Category";
    if(!$rs=mysqli_query($db,$dbcategories)) { echo("Unable to Run Query: $dbcategories"); exit; }
    while($row = mysqli_fetch_array($rs)) { $categories.=("<a href='?graphicfilter=" . $row['Gr_Category'] . "'>" . $row['Gr_Category'] . "</a> &nbsp; &nbsp; "); }
  }

  $graphics="SELECT * FROM Graphics WHERE (Gr_Delete='N') $graphicfilter ORDER BY Gr_Category, Gr_Name"; $oldcat=""; $table="";$x=0;
  if(!$rs=mysqli_query($db,$graphics)) { echo("Unable to Run Query: $graphics"); exit; }
  while($row = mysqli_fetch_array($rs))
  {
    if($row['Gr_Category'] != $oldcat)
    {
        $oldcat=$row['Gr_Category']; $table.=("<tr>\n<th colspan='7' style='text-align:left'><u>$oldcat</u></th>\n</tr>\n");
        $table.=("<tr>\n<th> &nbsp; Name &nbsp; </th>\n<th> &nbsp; Landscape &nbsp; </th>\n<th> &nbsp; Portrait &nbsp; </th>\n<th>Last Updated</th>\n</tr>\n");
        $x=0;
    }
    if($x%2 == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); }
    $table.=("<td><a href='graphic.php?id=" . $row['Gr_ID'] . "'>" . $row['Gr_Name'] . "</a></td>\n");
    if(file_exists("/var/www/html/pss/files/" . $row['Gr_ID'] . "-L.mp4")) { $table.=("<td style='text-align:center'>&check;</td>\n"); } else { $table.=("<td></td>\n"); }
    if(file_exists("/var/www/html/pss/files/" . $row['Gr_ID'] . "-P.mp4")) { $table.=("<td style='text-align:center'>&check;</td>\n"); } else { $table.=("<td></td>\n"); }
    $table.=("<td style='text-align:center'>" . date("M d, Y g:i a",strtotime($row['Gr_UpdateDateTime'])) . "</td>\n");
    $table.=("<td style='text-align:center'>" . $row['Gr_ID'] . "</td>\n");
    $table.=("</tr>\n");
    $x++;
  }

  if($table == "") { echo("No Graphics Available"); }
  else { echo("$categories<table>\n$table</table>\n"); }
?>

<?php include('../other/footer.php'); ?>