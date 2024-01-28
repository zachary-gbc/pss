<?php $root=true; include('other/pretitle.php'); ?>
<title>Pi Screen Scheduler</title>
<?php include('other/posttitle.php'); ?>

<h3>Screen Status</h3>

<?php
  $status="SELECT * FROM Locations ORDER BY Loc_BuildingRoom, Loc_Name"; $table=""; $locationselect=""; $x=0;
  if(!$rs=mysqli_query($db,$status)) { echo("Unable to Run Query: $status"); exit; }
  while($row = mysqli_fetch_array($rs))
  {
    $input=$row['Loc_Input']; $loop=$row['Loc_Loop'];
    if($row['Loc_Power'] == "On") { $power="On"; $powercolor="#00FF00"; } else { $power="Off"; $powercolor="#FF0000"; }
    if(substr($loop,0,1) == "L")
    {
      $loopname="SELECT Lop_Name FROM Loops WHERE (Lop_ID='" . substr($loop,2) . "')";
      if(!$rs1=mysqli_query($db,$loopname)) { echo("Unable to Run Query: $loopname"); exit; }
      while($row1 = mysqli_fetch_array($rs1)) { $loop=$row1['Lop_Name']; }
    }
    elseif(substr($loop,0,1) == "G")
    {
      $graphicname="SELECT Gr_Name FROM Graphics WHERE (Gr_ID='" . substr($loop,2) . "')";
      if(!$rs1=mysqli_query($db,$graphicname)) { echo("Unable to Run Query: $graphicname"); exit; }
      while($row1 = mysqli_fetch_array($rs1)) { $loop=$row1['Gr_Name']; }
    }
    else { $loop="None"; }

    $locationselect.=("<option value='" . $row['Loc_Device'] . "'>" . $row['Loc_BuildingRoom'] . " - " . $row['Loc_Name'] . "</option>");
    $updatelatecolor=""; if(strtotime($row['Loc_UpdateDateTime']) < strtotime('now - 1 day')) { $updatelatecolor="style='background-color:#FF0000'"; }

    if($x%2 == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); } $x++;
    $table.=("<th>" . $row['Loc_BuildingRoom'] . "</th>\n");
    $table.=("<th>" . $row['Loc_Name'] . "</th>\n");
    $table.=("<th style='background-color:$powercolor'>" . $power . "</th>\n");
    $table.=("<th>" . $input . "</th>\n");
    $table.=("<td>" . $loop . "</td>\n");
    $table.=("<td $updatelatecolor>" . date("m/d/Y H:i",strtotime($row['Loc_UpdateDateTime'])) . "</td>\n");
    $table.=("</tr>\n");
  }

  if($table == "") { echo("<h4>No Status Available</h4>"); }
  else
  {
    $roomorbuilding="SELECT VariableValue FROM Variables WHERE (VariableName='RoomOrBuilding)"; $roombuilding="Room";
    if(!$rs=mysqli_query($db,$roomorbuilding)) { echo("Unable to Run Query: $roomorbuilding"); exit; }
    while($row = mysqli_fetch_array($rs)) { $roombuilding=$row['VariableValue']; }

    echo("<table>\n<tr>\n<th> &nbsp; $roombuilding &nbsp; </th>\n<th> &nbsp; Location &nbsp; </th>\n<th> &nbsp; TV On/Off &nbsp; </th>\n");
    echo("<th> &nbsp; TV Input &nbsp; </th>\n<th> &nbsp; Loop &nbsp; </th>\n<th>Date Updated</th>\n");
    echo("</tr>\n$table</table>\n<br><br>\n");
  }
?>

<?php include('other/footer.php'); ?>