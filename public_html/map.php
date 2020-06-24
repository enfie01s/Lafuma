<?
$username = "bearhold1";
$password = "fdolvwbk";
$database = "uk2bearholdcouk25295_1";
$host = "localhost";

$county = trim(htmlspecialchars(mysql_real_escape_string($_GET['name'])));

$db=mysql_connect("localhost", "gmk", "cwuio745bjd") or die(mysql_error()); 
mysql_select_db("gmk_global",$db) or die(mysql_error());
$query = "SELECT * FROM dealerlistings as dl LEFT JOIN dealerlistings_latlng as dll ON dl.`accountid`=dll.`acid` WHERE `County`='" . $county . "' AND `LLC`='Y' AND (`Lafuma`='Y' OR `Lafuma_p`='Y') ORDER BY `Account` ASC";	
$result = mysql_query($query) or die(mysql_error());
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
var curCounty = new google.maps.LatLng(50.99014, -1.06791);
</script>
<h2 id="pagetitle">Local Stockists</h2>
<?
/* GEOCODES */
if ($submit !== "submitted")
{ ?>
Select your area:
<?	} 
?>
<form action="<?=$mainbase?>/index.php" method="get">
	<input type="hidden" name="p" value="map" />
	<input type="hidden" name="submit" value="submitted" />
	<input type="hidden" name="dealertype" value="furniture" />
	<select name="name" size="1" class="stockistlist" id="furniture_name">
		<option disabled="disabled" <?=$submit !== "submitted"?"selected='selected'":""?> value="no">Please Select...</option>
		<?	
		$query = "SELECT `County` FROM dealerlistings WHERE `LLC`='Y' AND (`Lafuma`='Y' OR `Lafuma_p`='Y') GROUP BY `County`";
		$result = mysql_query($query) or die(mysql_error()); 
		while ($info = mysql_fetch_array($result))
		{ 
			if (strlen(trim($info['County'])) > 0) 
			{ 
				?><option <?=trim(htmlspecialchars($info['County'])) == $county?'selected="selected"':''?> value="<?=trim($info['County'])?>" class="suboption"><?=trim($info['County'])?></option><? 	
			}
		} ?>
	</select>
	<br />
	<br />
	<input type="submit" value=" Submit " class="formbutton" />
</form><br />
<span class="orangebold"><?=ucwords($county)?></span><br />
<a href="index.php?p=dealers&amp;name=<?=$county?>&amp;submit=submitted">Back to dealers</a>
<div id="map_canvas" style="width: 546px; height: 500px;border:2px solid #ccc"></div>