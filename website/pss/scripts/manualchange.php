<?php
  if(isset($_POST['submit']))
  {
    include('dblogin.php'); $task=""; $input=""; $looporpower=""; $ip="";

    if(isset($_POST['device'])) { $device=$_POST['device']; }
    if(isset($_POST['task'])) { $task=$_POST['task']; }
    if(isset($_POST['input'])) { $input=$_POST['input']; }
    if($task == "start") { if(isset($_POST['loop'])) { $looporpower=$_POST['loop']; } }
    if($task == "stop") { if(isset($_POST['power'])) { $looporpower=$_POST['power']; } }

    $devip="SELECT Dev_IP FROM Devices WHERE (Dev_ID='$device')";
    if(!$rs=mysqli_query($db,$ipaddress)) { echo("Unable to Run Query: $devip"); exit; }
    while($row = mysqli_fetch_array($rs)) { $ip=$row['Dev_IP']; }

    if($ip != "") { $link="http://$ip/pss/scripts/manualchange.php?task=$task&input=$input&looporpower=$looporpower"; header("Location: $link"); }
  }
  else
  {
    $ip=str_replace(".","",$_SERVER['SERVER_ADDR']); $filename="manualchange-$ip"; $contents=""; $task=""; $input=""; $looporpower="";

    if(isset($_GET['task'])) { $task=$_GET['task']; }
    if(isset($_GET['input'])) { $input=$_GET['input']; }
    if(isset($_GET['looporpower'])) { $looporpower=$_GET['looporpower']; }
  
    if($task == "start" || $task == "stop") { $contents=$task . "-" . $input . "-" . $looporpower; }
    
    if($contents != "") { file_put_contents($filename,$contents,FILE_APPEND); }  
  }
?>