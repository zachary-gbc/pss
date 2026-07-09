<?php
include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');

$aloopstobuild=array(); $mloopstobuild=array(); $amissing=array(); $mmissing=array(); $now=date("Y-m-d H:i:s"); $month=date("n"); $todaydate=date("Y-m-d"); $lastconcat=0;

$lastconcatvar="SELECT Var_Value FROM Variables WHERE (Var_System='pss') AND (Var_Name='Last-Loop-Created')";
if(!$rs=mysqli_query($db,$lastconcatvar)) { echo("Unable to Run Query: $lastconcatvar"); exit; }
while($row = mysqli_fetch_array($rs)) { $lastconcat=$row['Var_Value']; }

$automaticloops="SELECT Lop_ID, Lop_Orientation FROM pss_Loops INNER JOIN pss_AutomaticLoopDates ON pss_Loops.Lop_ID=pss_AutomaticLoopDates.AD_Loop";
if(!$rs=mysqli_query($db,$automaticloops)) { echo("Unable to Run Query: $automaticloops"); exit; }
while($row = mysqli_fetch_array($rs)) { $aloopstobuild[$row['Lop_ID']]=$row['Lop_Orientation']; }

if(count($aloopstobuild) > 0)
{
    foreach($aloopstobuild as $loopid => $orientation)
    {
        $concat=false;
        $graphics="SELECT Gr_ID, Gr_Name, Gr_UpdateDateTime FROM pss_AutomaticLoopDates INNER JOIN pss_Graphics ON pss_AutomaticLoopDates.AD_Graphic=pss_Graphics.Gr_ID WHERE (Gr_Delete='N') AND (AD_Loop='$loopid') AND ((AD_Date='$todaydate') OR (AD_Month='$month') OR ((AD_StartDateRange<='$todaydate') AND (AD_EndDateRange>='$todaydate')))"; $vlcfilecontents=""; $concatfilecontents="";
        if(!$rs=mysqli_query($db,$graphics)) { echo("Unable to Run Query: $graphics"); exit; }
        while($row = mysqli_fetch_array($rs))
        {
            if(strtotime($row['Gr_UpdateDateTime']) >= strtotime($lastconcat)) { $concat=true; }
            $graphicid=$row['Gr_ID']; $exists=false;
            if(file_exists("/var/www/html/pss/files/$graphicid-$orientation.mp4"))
            {
                $vlcfilecontents.="/var/www/html/pss/files/$graphicid" . "-$orientation.mp4\n";
                $concatfilecontents.="file '/var/www/html/pss/files/$graphicid" . "-$orientation.mp4'\n";
                $exists=true;
            }
            if($exists == false) { $amissing[$loopid][$graphicid]=$row['Gr_Name']; }
        }
        if($vlcfilecontents != "")
        {
            $currentm3u=file_get_contents("/var/www/html/pss/files/loop-$loopid.m3u");
            if($currentm3u != $vlcfilecontents) { $concat=true; }
            file_put_contents("/var/www/html/pss/files/loop-$loopid.m3u", $vlcfilecontents);
        }
        if($concat == true) { if($concatfilecontents != "") { file_put_contents("/var/www/html/pss/files/loop-$loopid.concat", $concatfilecontents); } }
    }
}

$manualloops="SELECT Lop_ID, Lop_Orientation FROM pss_Loops INNER JOIN pss_LoopGraphics ON pss_Loops.Lop_ID=pss_LoopGraphics.LG_Loop INNER JOIN pss_Graphics ON pss_LoopGraphics.LG_Graphic=pss_Graphics.Gr_ID WHERE (Gr_Delete='N') AND (Lop_UpdateDateTime > Lop_LastCreateDateTime) OR (Gr_UpdateDateTime > Lop_LastCreateDateTime) OR (Lop_LastCreateDateTime IS NULL) GROUP BY Lop_ID, Lop_Orientation";
if(!$rs=mysqli_query($db,$manualloops)) { echo("Unable to Run Query: $manualloops"); exit; }
while($row = mysqli_fetch_array($rs)) { $mloopstobuild[$row['Lop_ID']]=$row['Lop_Orientation']; }

if(count($mloopstobuild) > 0)
{
    foreach($mloopstobuild as $loopid => $orientation)
    {
        $concat=false;
        $graphics="SELECT Gr_ID, Gr_Name, Gr_UpdateDateTime FROM pss_LoopGraphics INNER JOIN pss_Graphics ON pss_LoopGraphics.LG_Graphic=pss_Graphics.Gr_ID WHERE (LG_Loop='$loopid') ORDER BY LG_Order"; $vlcfilecontents=""; $concatfilecontents="";
        if(!$rs=mysqli_query($db,$graphics)) { echo("Unable to Run Query: $graphics"); exit; }
        while($row = mysqli_fetch_array($rs))
        {
            if(strtotime($row['Gr_UpdateDateTime']) >= strtotime($lastconcat)) { $concat=true; }
            $graphicid=$row['Gr_ID']; $exists=false;
            if(file_exists("/var/www/html/pss/files/$graphicid-$orientation.mp4"))
            {
                $vlcfilecontents.="/var/www/html/pss/files/$graphicid" . "-$orientation.mp4\n";
                $concatfilecontents.="file '/var/www/html/pss/files/$graphicid" . "-$orientation.mp4'\n";
                $exists=true;
            }
            if($exists == false) { $mmissing[$loopid][$graphicid]=$row['Gr_Name']; }
        }
        if($vlcfilecontents != "")
        {
            $currentm3u=file_get_contents("/var/www/html/pss/files/loop-$loopid.m3u");
            if($currentm3u != $vlcfilecontents) { $concat=true; }
            file_put_contents("/var/www/html/pss/files/loop-$loopid.m3u", $vlcfilecontents);
        }
        if($concat == true) { if($concatfilecontents != "") { file_put_contents("/var/www/html/pss/files/loop-$loopid.concat", $concatfilecontents); } }
    }
}

$updatevar="UPDATE Variables SET Var_Value='$now' WHERE (Var_System='pss') AND (Var_Name='Last-Loop-Created')";
if(!mysqli_query($db,$updatevar)) { echo("Unable to Run Query: $updatevar"); exit; }
?>