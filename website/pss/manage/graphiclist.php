<?php if(!isset($graphiclist)) { ?>
<?php include('../other/pretitle.php'); ?>
<title>Add/Edit Graphic</title>
<?php include('../other/posttitle.php'); ?>
<?php } ?>

<?php
  $categories="";
  if(isset($_GET['graphicfilter'])) { $graphicfilter="AND (Gr_Category='" . $_GET['graphicfilter'] . "')"; echo("<h3>" . $_GET['graphicfilter'] . " Graphics</h3>"); }
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

<?php if(!isset($graphiclist)) { include('../other/footer.php'); } ?>