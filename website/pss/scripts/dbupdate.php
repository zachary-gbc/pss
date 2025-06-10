<?php
  include('dblogin.php');

  $type=""; $device=""; $devname="rpi-xx"; $devip=""; $update=""; $dbupdate=""; $now=date("YmdHis");
  if(isset($_GET['type'])) { $type=$_GET['type']; }
  if(isset($_GET['device'])) { $device=$_GET['device']; }
  if(isset($_GET['devname'])) { $devname=$_GET['devname']; }
  if(isset($_GET['lanip'])) { $devip=$_GET['lanip']; }
  
  if($device != "")
  {
    switch($type)
    {
      case "cronsandmirror":
        $update="UPDATE Devices SET Dev_CronMirrorDateTime='$now' WHERE (Dev_MAC='$device')";
        break;

      case "ghupdate":
        $update="UPDATE Devices SET Dev_GHUpdateDateTime='$now' WHERE (Dev_MAC='$device')";
        break;

      case "devicedetails":
        $subselect="SELECT Dev_Name FROM Devices WHERE (Dev_MAC='$device')"; $devicename="";
        if(!$rs=mysqli_query($db,$subselect)) { exit; }
        while($row = mysqli_fetch_array($rs)) { $devicename=$row['Dev_Name']; }
        if($devicename == "")
        {
          $insert="INSERT INTO Devices(Dev_Name, Dev_MAC, Dev_Type, Dev_LocName, Dev_RoomBuilding, Dev_UpdateDateTime) VALUES('$devname', '$device', 'Unknown', 'Unknown', 'Unknown', now())";
          if(!mysqli_query($db,$insert)) { echo("Device NOT Added"); exit; }
        }
        elseif($devicename != $devname) { $update="UPDATE Devices SET Dev_Name='$devname', Dev_IP='$devip' WHERE (Dev_MAC='$device')"; }
        break;

      case "ipchange":
        $ipaddress=$_GET['ipaddress'];
        $update="UPDATE Devices SET Dev_IP='$ipaddress' WHERE (Dev_MAC='$device')";
        break;

      case "locationstatus":
        if(isset($_GET['power'])) { $dbupdate.=("Dev_Power='" . $_GET['power'] . "', "); }
        if(isset($_GET['input'])) { $dbupdate.=("Dev_Input='" . $_GET['input'] . "', "); }
        if(isset($_GET['loop'])) { $dbupdate.=("Dev_Loop='" . $_GET['loop'] . "', "); }
        $dbupdate=substr($dbupdate,0,-2); $update="UPDATE Devices SET $dbupdate WHERE (Dev_MAC='$device')";
        break;

      case "pushover":
        $title=$_GET['title'];
        $response=$_GET['response'];
        $update="INSERT INTO PushoverLog (PO_Device, PO_Title, PO_Response, PO_DateTime) VALUES((SELECT Dev_ID FROM Devices WHERE (Dev_MAC='$device')), '$title', '$response', '$now')";
        break;

      case "updateconf":
        $update="UPDATE Devices SET Dev_ConfDateTime='$now' WHERE (Dev_MAC='$device')";
        break;

      case "omxorvlc":
        $subselect="SELECT Dev_OMXorVLC FROM Devices WHERE (Dev_MAC='$device')"; $omxorvlc="";
        if(!$rs=mysqli_query($db,$subselect)) { exit; }
        while($row = mysqli_fetch_array($rs)) { echo($row['Dev_OMXorVLC']); break; }
        break;
      
      case "manualaction":
        $manualactions="SELECT MA_Number, MA_Variables FROM ManualActions WHERE (MA_Device='$device') AND (MA_Acknowledge IS NULL) ORDER BY MA_ID"; $actions="";
        if(!$rs=mysqli_query($db,$manualactions)) { exit; }
        while($row = mysqli_fetch_array($rs)) { $actions.=($row['MA_Number'] . "-" . $row['MA_Variables'] . "\n"); }
        if($actions == "") { echo("null"); } else { echo($actions); }
        $updateactions="UPDATE ManualActions SET MA_Acknowledge=now() WHERE (MA_Device='$device')";
        if(!mysqli_query($db,$updateactions)) { exit; }
        break;
    }

    if($update != "")
    {
      if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
      echo("MESSAGE " . date("Y-m-d H:i:s") . ": Database Updated Successfully ($type)\n");
    }
  }
?>
