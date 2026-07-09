<?php
include('dblogin.php');

$type=""; $device=""; $devname="rpi-xx"; $devip=""; $update=""; $dbupdate=""; $now=date("Y-m-d H:i:s");
if(isset($_GET['type'])) { $type=$_GET['type']; }
if(isset($_GET['device'])) { $device=$_GET['device']; }
if(isset($_GET['devname'])) { $devname=$_GET['devname']; }
if(isset($_GET['lanip'])) { $devip=$_GET['lanip']; }

if($device != "")
{
    switch($type)
    {
    case "cronsandmirror":
        $update="UPDATE Devices SET Dev_CronMirror='$now' WHERE (Dev_MAC='$device')";
        break;

    case "ghupdate":
        $update="UPDATE Devices SET Dev_PSSGHUpdate='$now' WHERE (Dev_MAC='$device')";
        break;

    case "ipchange":
        $ipaddress=$_GET['ipaddress'];
        $update="UPDATE Devices SET Dev_IP='$ipaddress' WHERE (Dev_MAC='$device')";
        break;

    case "locationstatus":
        if(isset($_GET['power']) && $_GET['power'] != "unknown") { $dbupdate.=("Dev_Power='" . $_GET['power'] . "', "); }
        if(isset($_GET['input']) && $_GET['input'] != "unknown") { $dbupdate.=("Dev_Input='" . $_GET['input'] . "', "); }
        if(isset($_GET['loop']) && $_GET['loop'] != "unknown") { $dbupdate.=("Dev_Loop='" . $_GET['loop'] . "', "); }
        if($dbupdate != "") { $dbupdate=substr($dbupdate,0,-2); $update="UPDATE Devices SET $dbupdate WHERE (Dev_MAC='$device')"; }
        break;
    
    case "manualaction":
        $manualactions="SELECT MA_Number, MA_Variables FROM pss_ManualActions WHERE (MA_Device='$device') AND (MA_Acknowledge IS NULL) ORDER BY MA_ID"; $actions="";
        if(!$rs=mysqli_query($db,$manualactions)) { exit; }
        while($row = mysqli_fetch_array($rs)) { $actions.=($row['MA_Number'] . "-" . $row['MA_Variables'] . "\n"); }
        if($actions == "") { echo("null"); } else { echo($actions); }
        $updateactions="UPDATE pss_ManualActions SET MA_Acknowledge=now() WHERE (MA_Device='$device')";
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
