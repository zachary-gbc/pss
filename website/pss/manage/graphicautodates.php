<?php
  include('../scripts/dblogin.php'); $form="";
  $allmonths="<option value='0'><option><option value='1'>January</option><option value='2'>February</option><option value='3'>March</option><option value='4'>April</option>";
  $allmonths.="<option value='5'>May</option><option value='6'>June</option><option value='7'>July</option><option value='8'>August</option><option value='9'>September</option>";
  $allmonths.="<option value='10'>October</option><option value='11'>November</option><option value='12'>December</option>";

  if(isset($_GET['graphic']) && isset($_GET['loop']))
  {
    $graphic=$_GET['graphic']; $loop=$_GET['loop'];

    if(isset($_POST['submit']))
    {
      $delete="DELETE FROM AutomaticLoopDates WHERE (AD_Graphic='$graphic') AND (AD_Loop='$loop')";
      if(!$rs=mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; }

      $maxx=$_POST['maxx']; $dates=array(); $months=array(); $starts=array(); $ends=array(); $values="";
      for($x=0;$x<=$maxx;$x++)
      {
        if(isset($_POST["include-$x"]))
        {
          if($_POST["datevalue-$x"] == "") { $datevalue="0000-00-00"; } else { $datevalue=$_POST["datevalue-$x"]; }
          if($_POST["month-$x"] == "") { $monthvalue="0"; } else { $monthvalue=$_POST["month-$x"]; }
          if($_POST["start-$x"] == "") { $startvalue="0000-00-00"; } else { $startvalue=$_POST["start-$x"]; }
          if($_POST["end-$x"] == "") { $endvalue="0000-00-00"; } else { $endvalue=$_POST["end-$x"]; }
          $dates[$x]=$datevalue; $months[$x]=$monthvalue; $starts[$x]=$startvalue; $ends[$x]=$endvalue;
        }
      }
      if(isset($_POST['newdate']) && $_POST['newdate'] != "") { $dates[$x]=$_POST['newdate']; $months[$x]="0"; $starts[$x]="0000-00-00"; $ends[$x]="0000-00-00"; $x++; }
      if(isset($_POST['newmonth']) && $_POST['newmonth'] != "0") { $dates[$x]="0000-00-00"; $months[$x]=$_POST['newmonth']; $starts[$x]="0000-00-00"; $ends[$x]="0000-00-00"; $x++; }
      if(isset($_POST['newrangestart']) && $_POST['newrangestart'] != "" && isset($_POST['newrangeend']) && $_POST['newrangeend'] != "")
      { $dates[$x]="0000-00-00"; $months[$x]="0"; $starts[$x]=$_POST['newrangestart']; $ends[$x]=$_POST['newrangeend']; $x++; }

      foreach($dates as $x => $date) { $month=$months[$x]; $start=$starts[$x]; $end=$ends[$x]; $values.="('$graphic', '$loop', '$date', '$month', '$start', '$end'),"; }
      if($values != "")
      {
        $insert="INSERT INTO AutomaticLoopDates(AD_Graphic, AD_Loop, AD_Date, AD_Month, AD_StartDateRange, AD_EndDateRange) VALUES " . substr($values,0,-1) . ";";
        if(!$rs=mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
      }
    }

    $autodates="SELECT * FROM AutomaticLoopDates WHERE (AD_Graphic='$graphic') AND (AD_Loop='$loop')"; $x=0;
    if(!$rs=mysqli_query($db,$autodates)) { echo("Unable to Run Query: $autodates"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
      $date=""; $month=""; $range="";
      if($row['AD_Date'] != "0000-00-00") { $date=$row['AD_Date']; }
      if($row['AD_Month'] != "0") { $month=date("F",mktime(1,1,1,$row['AD_Month'],1,1)); }
      if($row['AD_StartDateRange'] != "0000-00-00" && $row['AD_EndDateRange'] != "0000-00-00") { $range=($row['AD_StartDateRange'] . " to " . $row['AD_EndDateRange']); }

      $form.=("<input type='checkbox' name='include-$x' value='" . $row['AD_Date'] . "' checked='checked' />" . $date . $month . $range . " &nbsp; &nbsp; ");
      $form.=("<input type='hidden' name='datevalue-$x' value='" . $row['AD_Date'] . "' />");
      $form.=("<input type='hidden' name='month-$x' value='" . $row['AD_Month'] . "' />");
      $form.=("<input type='hidden' name='start-$x' value='" . $row['AD_StartDateRange'] . "' />");
      $form.=("<input type='hidden' name='end-$x' value='" . $row['AD_EndDateRange'] . "' />");
      $x++;
    }

    $form.=("<strong>New</strong> Date: <input type='date' name='newdate' /> &nbsp; or Month: <select name='newmonth'>$allmonths</select> &nbsp; or ");
    $form.=("Range: <input type='date' name='newrangestart' /> to <input type='date' name='newrangeend' /> &nbsp; <input type='submit' name='submit' value='Submit Changes' />");
    $form.=("<input type='hidden' name='maxx' value='$x' />");

    echo("<script type='text/javascript'>\n");
    echo("window.parent.document.getElementById('$graphic-$loop').innerHTML=\"$form\";");
    echo("</script>\n");
  }
?>