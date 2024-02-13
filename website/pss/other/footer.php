<br />
<div class="footer">
	<?php echo($systemname); ?>
	<?php #if((time() - $loadnow) == 1) { $s=""; } else { $s="s"; } echo("<br>Page Load Time: " . (time() - $loadnow) . " Second$s\n"); ?>
</div>
</body>

</html>
<?php mysqli_close($db); ?>
