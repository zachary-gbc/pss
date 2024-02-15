<?php include('../other/pretitle.php'); ?>
<title>Graphic</title>
<?php include('../other/posttitle.php'); ?>

<?php
  $id="";
  if(isset($_POST['submit']))
  {
    $id=$_POST['id'];
    $oldname=str_replace("'","''",$_POST['oldname']);
    $newname=str_replace("'","''",$_POST['newname']);
    $category=$_POST['category'];
    $uploadtype=$_POST['type'];
    if(isset($_POST['delete'])) { $delete="Y"; $newname=($newname . " - Deleted " . mktime()); } else { $delete="N"; }
    $namemessage=""; $uploadmessage=""; $uploaderror=false; $dbinserterr=false; $x=0;

    if($_FILES["uploadfile"]["error"] == 0) { $uploadname=$_FILES["uploadfile"]["name"]; $extarr=explode(".",$uploadname); $uploadext=end($extarr); }
    if($uploadext != "" && $uploadext != "mp4" && $uploadext != "png" && $uploadext != "jpg") { $uploadmessage.=("Only png, jpg, and mp4 files are currently allowed"); $uploaderror=true; }
    else
    {
      $filetypecheck=strtoupper($uploadtype . $uploadext);
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
      $update="UPDATE Graphics SET Gr_Name='$newname', Gr_Category='$category', Gr_UpdateDateTime=now(), Gr_Delete='$delete' WHERE (Gr_ID='$id')";
      if(!$rs=mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
    }

    if($nameerror == false && $uploaderror == false) { echo("<h3>Success</h3>\n<h4>$namemessage $uploadmessage</h4><hr>"); }
    else { echo("<h3>Some Errors, See Below</h3>\n<h4>$namemessage<br>$uploadmessage</h4><br><hr>"); }
  }

  if(isset($_GET['id'])) { $id=$_GET['id']; } else { $id="new"; }
  $categories=array("Caring", "Events", "Kids", "Mens", "Missions", "Womens", "Youth", "Other"); $active="checked='checked'";

  $name=""; $category=""; $updated="";
  $data="SELECT * FROM Graphics WHERE (Gr_ID='$id')";
  if(!$rs=mysqli_query($db,$data)) { echo("Unable to Run Query: $data"); exit; }
  while($row = mysqli_fetch_array($rs))
  {
    $name=$row['Gr_Name']; $category=$row['Gr_Category']; $updated=strtotime($row['Gr_UpdateDateTime']);
    if($row['Gr_Delete'] == "N") { $delete=""; } else { $delete="checked='checked'"; }
  }

  if($id != "new") { echo("<h4>Edit: $name</h4>\n"); } else { echo("<br>"); }

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

  if($id == "new")
  {
    echo("<tr>\n<th colspan='2'><input type='submit' name='submit' value='Add New Graphic' /></th>\n<td colspan='2'></td>\n</tr>\n");
  }
  else
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
    echo("<br><br>"); $graphiclist=true; include('graphiclist.php');
  }
?>

<?php include('../other/footer.php'); ?>