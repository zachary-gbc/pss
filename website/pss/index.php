<?php $root=true; include('other/pretitle.php'); ?>
<title>Pi Screen Scheduler</title>
<?php include('other/posttitle.php'); ?>

<h3>Screen Status</h3>

<?php
  $devices=array(); $lgselect=array(); $table=""; $locationselect=""; $x=0;
  $status="SELECT * FROM Devices ORDER BY Dev_RoomBuilding, Dev_LocName";
  if(!$rs=mysqli_query($db,$status)) { echo("Unable to Run Query: $status"); exit; }
  while($row = mysqli_fetch_array($rs))
  {
    $devices[($row['Dev_MAC']."-".$row['Dev_Orientation'])]=($row['Dev_RoomBuilding'] . " - " . $row['Dev_LocName']);
    $input=$row['Dev_Input']; $loop=$row['Dev_Loop'];
    if($row['Dev_Power'] == "On") { $power="On"; $powercolor="#00FF00"; } else { $power="Off"; $powercolor="#FF0000"; }
    if(substr($loop,0,1) == "L")
    {
      $loopname="SELECT Lop_Name FROM Loops WHERE (Lop_ID='" . substr($loop,2) . "')";
      if(!$rs1=mysqli_query($db,$loopname)) { echo("Unable to Run Query: $loopname"); exit; }
      while($row1 = mysqli_fetch_array($rs1)) { $loop=("Loop: " . $row1['Lop_Name']); }
    }
    elseif(substr($loop,0,1) == "G")
    {
      $graphicname="SELECT Gr_Name FROM Graphics WHERE (Gr_ID='" . substr($loop,2) . "')";
      if(!$rs1=mysqli_query($db,$graphicname)) { echo("Unable to Run Query: $graphicname"); exit; }
      while($row1 = mysqli_fetch_array($rs1)) { $loop=("Graphic: " . $row1['Gr_Name']); }
    }
    else { $loop="None"; }

    $updatelatecolor=""; if(strtotime($row['Dev_CronMirrorDateTime']) < strtotime('now - 1 day')) { $updatelatecolor="style='background-color:#FF0000'"; }

    if($x%2 == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); } $x++;
    $table.=("<th>" . $row['Dev_RoomBuilding'] . "</th>\n");
    $table.=("<th>" . $row['Dev_LocName'] . "</th>\n");
    $table.=("<th style='background-color:$powercolor'>" . $power . "</th>\n");
    $table.=("<th>" . $input . "</th>\n");
    $table.=("<td>" . $loop . "</td>\n");
    $table.=("<td $updatelatecolor>" . date("m/d/Y h:i a",strtotime($row['Dev_CronMirrorDateTime'])) . "</td>\n");
    $table.=("</tr>\n");
  }

  if($table == "") { echo("<h4>No Status Available</h4>"); }
  else
  {
    $roomorbuilding="SELECT Var_Value FROM Variables WHERE (Var_Name='RoomOrBuilding')"; $roombuilding="Room";
    if(!$rs=mysqli_query($db,$roomorbuilding)) { echo("Unable to Run Query: $roomorbuilding"); exit; }
    while($row = mysqli_fetch_array($rs)) { $roombuilding=$row['Var_Value']; }

    echo("<table>\n<tr>\n<th> &nbsp; $roombuilding &nbsp; </th>\n<th> &nbsp; Location &nbsp; </th>\n<th> &nbsp; TV On/Off &nbsp; </th>\n");
    echo("<th> &nbsp; TV Input &nbsp; </th>\n<th> &nbsp; Loop/Graphic &nbsp; </th>\n<th>Date Updated</th>\n");
    echo("</tr>\n$table</table>\n<br><br>\n");
  }

  echo("<h3>Manual Screen Update</h3>\n");
  $numbers=array(0 => "Choose Action", 11 => "Start Loop", 12 => "Stop Loop", 13 => "Turn TV On", 14 => "Turn TV Off", 15 => "Download Graphic or Loop", 16 => "Download Crons", 21 => "Change TV to Input 1", 22 => "Change TV to Input 2", 23 => "Change TV to Input 3", 24 => "Change TV to Input 4", 25 => "Change TV to Input 5");

  $allloops="SELECT * FROM Loops ORDER BY Lop_Name";
  if(!$rs=mysqli_query($db,$allloops)) { echo("Unable to Run Query: $allloops"); exit; }
  while($row = mysqli_fetch_array($rs)) { $lgselect["Loops"]["L-".$row['Lop_ID']]=$row['Lop_Name']; }
  $allgraphics="SELECT * FROM Graphics WHERE (Gr_Delete='N') ORDER BY Gr_Category, Gr_Name";
  if(!$rs=mysqli_query($db,$allgraphics)) { echo("Unable to Run Query: $allgraphics"); exit; }
  while($row = mysqli_fetch_array($rs)) { $lgselect[($row['Gr_Category'] . " Graphics")]["G-".$row['Gr_ID']]=$row['Gr_Name']; }

  echo("<form method='post' action='/pss/scripts/manualaction.php?addmanualaction=true' target='manual_change_iframe'><table>");
  echo("<tr>\n<th style='text-align:right'>Device: </th>\n<td><select name='device'>");
  foreach($devices as $id => $name) { echo("<option value='$id'>$name</option>"); }
  echo("</select></td>\n<td rowspan='4'>");
  echo("<iframe name='manual_change_iframe' src='/pss/scripts/manualaction.php' style='height:75px;width:220px;border:0' title='Manual Change Confirmation'></iframe></td>\n</tr>\n");
  echo("<tr><th style='text-align:right'>Change: </th><td><select name='number'>");
  foreach($numbers as $id => $name) { echo("<option value='$id'>$name</option>"); }
  echo("</select></td>\n</tr>\n");
  echo("<tr><th style='text-align:right'>Loop/Graphic: </th><td><select name='variables'>");
  foreach($lgselect as $groupname => $group)
  {
    echo("<optgroup label='$groupname'>\n");
    foreach($group as $lgid => $lgname) { echo("<option value='$lgid'>$lgname</option>\n"); }
    echo("</optgroup>\n");
  }
  echo("</select></td>\n</tr>\n");
  echo("<tr>\n<th colspan='2'><input type='submit' value='Submit Change' /></th>\n</tr>\n</table>\n");
?>

<?php include('other/footer.php'); ?>
