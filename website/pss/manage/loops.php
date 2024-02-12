<?php include('../other/pretitle.php'); ?>
<title>Loops</title>
<?php include('../other/posttitle.php'); ?>

<?php
  $id=""; $currentloop=""; $loopname=""; $updatedatetime=""; $new=""; $table=""; $graphics=array(); $y=0;
  if(isset($_POST['submit']))
  {
    $id=$_POST['id']; $x=1; $newname=str_replace("'","''",$_POST['newname']); $unewname=""; $dowupdate="";
    $delete="DELETE FROM LoopGraphics WHERE (LG_Loop='$id')";
    if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; }

    while(isset($_POST["graphic-$y"]))
    {
      $order=$y;
      $graphic=$_POST["graphic-$y"];
      if($graphic != "")
      {
        $insert="INSERT INTO LoopGraphics(LG_Loop, LG_Graphic, LG_Order) VALUES('$id', '$graphic', '$order')";
        if(!mysqli_query($db,$insert)) { echo("<h4>Duplicate Order Number, Graphic Skipped!</h4>\n"); }
      }
      $y++;
    }

    if($newname != "")
    {
      $check="SELECT * FROM Loops WHERE (Lop_Name='$newname')"; $updatename=true;
      if(!$rs=mysqli_query($db,$check)) { echo("Unable to Run Query: $check"); exit; }
      while($row = mysqli_fetch_array($rs)) { echo("Name Already Exists, Choose New Name"); $updatename=false; }

      if($updatename == true) { $unewname=", Lop_Name='$newname'"; }
    }

    $updateloop="UPDATE Loops SET Lop_UpdateDateTime=now()$unewname WHERE (Lop_ID='$id')";
    if(!mysqli_query($db,$updateloop)) { echo("Unable to Run Query: $updateloop"); exit; }
    echo("<h3>Changes Successful</h3>\n");

    if(isset($_POST['deleteloop']))
    {
      $delete1="DELETE FROM Loops WHERE (Lop_ID='$id')";
      $delete2="DELETE FROM LoopGraphics WHERE (LG_Loop='$id')";
      if(!mysqli_query($db,$delete1)) { echo("Unable to Run Query: $delete1"); exit; }
      if(!mysqli_query($db,$delete2)) { echo("Unable to Run Query: $delete2"); exit; }
      echo("<h3>Loop Deleted</h3>");
    }
  }

  if(isset($_POST['new']) && strlen($_POST['new']) > 1)
  {
    $name=str_replace("'","''",$_POST['new']); $add=true;
    $orientation=$_POST['orientation'];
    $type=$_POST['type'];
    $category=$_POST['category'];
    $check="SELECT * FROM Loops WHERE (Lop_Name='$name')";
    if(!$rs=mysqli_query($db,$check)) { echo("Unable to Run Query: $check"); exit; }
    while($row = mysqli_fetch_array($rs)) { echo("Name Already Exists, Choose New Name"); $add=false; }

    if($add == true)
    {
      $insert="INSERT INTO Loops(Lop_Name, Lop_Category, Lop_UpdateDateTime, Lop_Orientation, Lop_Type) VALUES('$name', '$category', now(), '$orientation', '$type')";
      if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
    }
  }

  if(isset($_GET['id']))
  {
    $id=$_GET['id']; $type="Automatic"; $manualnote=""; $table=""; $preview="";
    $manualcheck="SELECT Lop_Type FROM Loops WHERE (Lop_ID='$id')";
    if(!$rs=mysqli_query($db,$manualcheck)) { echo("Unable to Run Query: $manualcheck"); exit; }
    while($row = mysqli_fetch_array($rs)) { $type=$row['Lop_Type']; }
    
    if($type == "Manual")
    {
      $autodow=""; $y=0;
      $table.="<table>\n<tr>\n<th>Graphic</th>\n</tr>\n";

      $graphicslist="SELECT * FROM Graphics WHERE (Gr_Delete='N') ORDER BY Gr_Category, Gr_Name";
      if(!$rs=mysqli_query($db,$graphicslist)) { echo("Unable to Run Query: $graphicslist"); exit; }
      while($row = mysqli_fetch_array($rs)) { $graphics[$row['Gr_Category']][$row['Gr_ID']]=$row['Gr_Name']; }

      $details="SELECT * FROM LoopGraphics WHERE (LG_Loop='$id') ORDER BY LG_Order";
      if(!$rs=mysqli_query($db,$details)) { echo("Unable to Run Query: $details"); exit; }
      while($row = mysqli_fetch_array($rs))
      {
        $order=$row['LG_Order'];
        $table.=("<tr>\n<td><select name='graphic-$y'><option value=''>Remove</option>");
        foreach($graphics as $catid => $category)
        {
          $table.=("<optgroup label='$catid'>");
          foreach($graphics[$catid] as $gid => $graphic)
          { $s=""; if($row['LG_Graphic'] == $gid) { $s="selected='selected'"; } $table.=("<option value='$gid' $s>$graphic</option>"); }
          $table.=("</optgroup>");
        }
        $table.=("</select></td>\n</tr>\n");
        $y++;
      }
      for($x=0; $x<=5; $x++)
      {
        $table.=("<tr>\n<td><select name='graphic-$y'><option value=''>None</option>");
        foreach($graphics as $catid => $category)
        {
          $table.=("<optgroup label='$catid'>");
          foreach($graphics[$catid] as $gid => $graphic) { $table.=("<option value='$gid'>$graphic</option>"); }
          $table.=("</optgroup>");
        }
        $table.=("</select></td>\n</tr>\n");
        $y++;
      }
      $table.=("</table>\n");
    }
    else
    {
      //need to add ability to choose date and see what graphics are included
      $manualnote.="<h4>Only Manual Loops Editable Here<br>Add Graphics To Automatic Loops</h4>\n";
    }
  }

  $list="SELECT * FROM Loops ORDER BY Lop_Category, Lop_Type, Lop_Name"; $loops=""; $currentloop=""; $oldcat="";
  if(!$rs=mysqli_query($db,$list)) { echo("Unable to Run Query: $list"); exit; }
  while($row = mysqli_fetch_array($rs))
  {
    if($row['Lop_Category'] != $oldcat) { $oldcat=$row['Lop_Category']; $loops.=("<br><h4 style='margin:0px'>$oldcat</h4>\n"); }
    if($row['Lop_Orientation'] == "L") { $orientation="Landscape"; } else { $orientation="Portrait"; }
    $loops.=("<a href='?id=" . $row['Lop_ID'] . "'>" . $row['Lop_Name'] . "</a> (" . $orientation . " - " . $row['Lop_Type'] . ")<br>\n");

    if(isset($_GET['id']) && $_GET['id'] == $row['Lop_ID']) { $currentloop=$row['Lop_Name']; }
  }

  if($currentloop != "")  echo("<h3>$currentloop <a style='font-size:smaller' href='/pss/files/loop-" . $_GET['id'] . ".mp4' target='_blank'>(Preview)</a></h3>"); 

  if(isset($_GET['id']))
  {
    echo("<form method='post' action=''>\n");
    echo("<h3>$loopname<input type='hidden' name='id' value='$id' /></h3>\n");
    echo("New Name: <input type='text' style='width:200px' name='newname' /><br> Delete Loop: <input type='checkbox' name='deleteloop' /><br><br>\n");
    echo("$table$preview$manualnote<input type='submit' name='submit' value='Submit Changes' />\n</form>\n<br>\n");
  }

  $categories=array("Other", "Caring", "Events", "Kids", "Mens", "Missions", "Womens", "Youth"); $active="checked='checked'";
  echo("<form method='post' action=''>\n$loops\n<br>");
  echo("Add New Loop: <input type='text' name='new' style='width:200px' /> &nbsp; ");
  echo("Category: <select name='category'>"); foreach($categories as $cat) { echo("<option value='$cat'>$cat</option>"); } echo("</select> &nbsp; ");
  echo("Orientation: <select name='orientation'><option value='L'>Landscape</option><option value='P'>Portrait</option>\n</select> &nbsp; ");
  echo("Type: <select name='type'><option value='Manual'>Manual</option><option value='Automatic'>Automatic</option>\n</select> &nbsp; ");
  echo("<input type='submit' value='Add New Loop' />\n</form>");
?>

<?php include('../other/footer.php'); ?>
