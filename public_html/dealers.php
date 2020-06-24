<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security ?>
<h2 id="pagetitle">Local Stockists</h2>
<?

$county = trim(htmlspecialchars(mysql_real_escape_string($_GET['name'])));
		
$gdb=mysql_connect("localhost", "gmk", "cwuio745bjd", true) or die(mysql_error()); 
mysql_select_db("gmk_global",$gdb) or die(mysql_error());
?>

<div style="float:left">
<?	
$query = "SELECT `County` FROM dealerlistings WHERE `LLC`='Y' AND (`Lafuma`='Y' OR `Lafuma_p`='Y') GROUP BY `County`";
$result = mysql_query($query,$gdb) or die(lamysql_error("Query failed",mysql_error())); 

if ($submit !== "submitted")
{ ?>
Select your area:
<?	} ?>
<form action="<?=$mainbase?>/index.php" method="get">
	<input type="hidden" name="p" value="dealers" />
	<input type="hidden" name="submit" value="submitted" />
	<input type="hidden" name="dealertype" value="furniture" />
	<select name="name" size="1" class="stockistlist" id="furniture_name">
		<option disabled="disabled" <?=$submit !== "submitted"?"selected='selected'":""?> value="no">Please Select...</option>
		<?
		while ($info = mysql_fetch_array($result))
    { 
      if (strlen(trim($info['County'])) > 0) 
      { 
				?>
				<option <? if (trim($info['County']) == $county) { print 'selected="selected"'; } ?> value="<? print trim($info['County']); ?>" class="suboption"><? print trim($info['County']); ?></option>
				<? 	
			} 
		} ?>
	</select>
	<br />
	<br />
	<input type="submit" value=" Submit " class="formbutton" />
</form>
<?
if ($submit=="submitted")
{ 
	$query = "SELECT * FROM dealerlistings as dl LEFT JOIN dealerlistings_latlng as dll ON dl.`accountid`=dll.`acid` WHERE `County`='" . $county . "' AND `LLC`='Y' AND (`Lafuma`='Y' OR `Lafuma_p`='Y') ORDER BY `Account` ASC";	
	$result = mysql_query($query,$gdb) or die(lamysql_error("Query failed",mysql_error()));
	/* GEOCODES */
	$i=1;
	while($info = mysql_fetch_array( $result ))  
	{ //http://maps.googleapis.com/maps/api/geocode/xml?address=Aberdeenshire,UK&sensor=false
		mapmarkers($info,$i);
		$i++;
	}
	$curcountylatlng=geocode($county.",UK");
	?>
	<script type="text/javascript">
	var curCounty = new google.maps.LatLng(<?=$curcountylatlng[0]?>, <?=$curcountylatlng[1]?>);
	</script>
	<?
	/* GEOCODES */
	?>
	<br />
	<span class="orangebold">
	<?=ucwords($county)?>
	</span> <br />
	<br />
	<span class="key"><span style="color:#0091B6; font-weight:bold;">P</span> = Premier Stockist</span><br />
	<a href="index.php?p=map&amp;name=<?=$county?>&amp;submit=submitted">View large map</a>
	</div>
	<div style="float:right"><div id="map_canvas" style="width: 350px; height: 200px;border:2px solid #ccc"></div></div>
	<div class="clear"></div>
	<hr />
	<? 
	mysql_data_seek($result,0);
	$i=1;
	while($info = mysql_fetch_array( $result ))  
	{ 
		$address=ucwords(strtolower($info['Address1']));
		if(strlen($info['Address2'])>0){$address.=", ".ucwords(strtolower($info['Address2']));}
		if(strlen($info['City'])>0){$address.=", ".ucwords(strtolower($info['City']));}
		if(strlen($info['County'])>0){$address.=", ".ucwords(strtolower($info['County']));}
		if(strlen($info['Postcode'])>0){$address.=", ".$info['Postcode'];}
		?>
		<div><strong><?=$i?>: <?=$info['Account']?></strong>
		<? if ($info['Lafuma_p'] == "True") { ?>
		- <span style="color:#0091B6; font-weight:bold;">P</span>
		<? } ?>
		<br />
		<?=$address?>
		<br />
		Tel: <? print $info['Mainphone']; ?>
		<hr />
		</div>
		<?	
		$i++;
	}
}  
else
{
	?>
	</div>
	<div style="float:right"></div>
	<div class="clear"></div>
	<? 
}
mysql_close($gdb);?>
