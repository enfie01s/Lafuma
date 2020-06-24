<?
$searchby=isset($_GET['searchby'])?mysql_real_escape_string($_GET['searchby']):"";
$searchterm=isset($_GET['searchterm'])?mysql_real_escape_string($_GET['searchterm']):"";
if(isset($_GET['orderby']))
{
	$orderbyclean=mysql_real_escape_string($_GET['orderby']);
	$orderbits=explode("--",$orderbyclean);
	$orderby=$orderbits[0];
	$orderdir=$orderbits[1];
}
else
{
	$orderby="lastname";
	$orderdir="ASC";
}

if($searchterm!=""){$search="WHERE `$searchby` LIKE '%$searchterm%'";$searchurl="&amp;searchby=$searchby&amp;searchterm=$searchterm";}
?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <?=$action!=""?"<a href='$self'>":""?>Customers<?=$action!=""?"</a>":""?></div>
<div id="main">
	<h2 id="pagetitle">Customers</h2>
	<div id="pagecontent">
	<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
	<!-- CONTENT -->
	<?
	switch($action)
	{
		case "view":
			$custq=mysql_query("SELECT * FROM (customers as cu LEFT JOIN counties as c ON c.`county_id`=cu.`state`) LEFT JOIN countries as co ON co.`country_id`=cu.`country` WHERE `cust_id`='$custid'");
			$cust=mysql_fetch_assoc($custq);
			$ordersq=mysql_query("SELECT `date_ordered`,`invoice` FROM orders WHERE `cust_id`='$cust[cust_id]' ORDER BY `date_ordered` DESC");
			$num_orders=mysql_num_rows($ordersq);
			?>
			<table class="details">
			<tr>
				<td class="head" colspan="2"><div class="titles"><?=$titleback." ".helplink($page)?> Customer Details</div><div class="links"><a href="<?=$self?>&amp;act=edit&amp;cust_id=<?=$custid?>">Edit customer info</a></div></td>
			</tr>
			<tr>
				<td class="infohead" colspan="2"><div class="titles">Signup date: <?=date("F d\, Y",$cust['signup_date'])?></div><div class="links">Orders to date: <?=$num_orders?></div></td>
			</tr>
			<tr>
				<td class="first">First Name</td>
				<td><?=$cust['firstname']?></td>
			</tr>
			<tr>
				<td>Last Name</td>
				<td><?=$cust['lastname']?></td>
			</tr>
			<tr>
				<td>Email</td>
				<td><?=($cust['email']!="")?"<a href='mailto:$cust[email]'>$cust[email]</a>":"";?></td>
			</tr>
			<tr>
				<td style="vertical-align:top">Address</td>
				<td>
				<?=$cust['address1']?><br />
				<?=($cust['address2']?$cust['address2']."<br />":"")?>
				<?=$cust['city']?><br />
				<?=$cust['countyname']?><br />
				<?=$cust['postcode']?><br />
				<?=$cust['countryname']?><br />
				</td>
			</tr>
			<tr>
				<td>Phone</td>
				<td><?=$cust['phone']?></td>
			</tr>
			<tr>
				<td>Website</td>
				<td><?=$cust['homepage']!=""?"<a href='$ua[homepage]'>$ua[homepage]</a>":""?></td>
			</tr>
			<tr>
				<td>Company</td>
				<td><?=$cust['company']?></td>
			</tr>
			<tr>
				<td>Receive Marketing Emails</td>
				<td><?=$mailtype[$cust['mailing']]?></td>
			</tr>
			</table>
			<br />
			<?
			$orders=mysql_num_rows($ordersq);
			if($orders>0)
			{
				?>
				<table class="details">
				<tr>
					<td colspan="3" class="head">Customer Orders</td>
				</tr>
				<tr>
					<td class="subhead">Order Date</td>
					<td class="subhead">Invoice</td>
					<td class="subhead">Details</td>
				</tr>
				<? while($order=mysql_fetch_assoc($ordersq)){?>
					<tr>
						<td><?=date("F j, Y",$order['date_ordered'])?></td>
						<td><?=$order['invoice']?></td>
						<td><a href="<?=$mainbase?>/admin.php?p=invoices&amp;act=view&amp;invoice=<?=$order['invoice']?>">Details</a></td>
					</tr>
				<? }?>
				</table>
				<? 
				}
			break;
		case "edit":
		case "add":
			$errorlist=$higherr;
			if($action=="edit"){
				$custq=mysql_query("SELECT * FROM (customers as cu LEFT JOIN counties as c ON c.`county_id`=cu.`state`) LEFT JOIN countries as co ON co.`country_id`=cu.`country` WHERE `cust_id`='$custid'");
				$cust=mysql_fetch_assoc($custq);
				$data=isset($_POST['firstname'])?$_POST:$cust;
			}
			else
			{
				$data=isset($_POST['firstname'])?$_POST:array();
			}
			if(!array_key_exists("mailing",$data)){$data['mailing']=1;}
			if(!array_key_exists("status",$data)){$data['status']=1;}
			?>
			<form action="<?=$self?>&amp;act=<?=$action?><?=$custid!=""?"&amp;cust_id=$custid":""?>" method="post" style="width:60%">
			<table class="details">
				<tr>
					<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> <?=$action=="add"?"New":"Update"?> Customer</td>
				</tr>
				<tr>
					<td class="first"><label for="pass">New Password</label></td>
					<td><input type="password" name="password" id="pass" value="" class="formfield" <?=highlighterrors($higherr,"password")?> /></td>
				</tr>
				<? formrows(array("firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company","mailing"=>"Email List"),$requireds['admindoupdate'],array("state"=>"SELECT county_id,countyname FROM counties ORDER BY countyname ASC","country"=>"SELECT country_id,countryname FROM countries ORDER BY countryname ASC"),array("mailing"=>"1:HTML,0:None"),array(),$data,"updateform");?>
				<tr>
					<td class="first">Status</td>
					<td><input type="radio" name="status" value="1" id="status1" <?=is_selected("status","","1",$data,"check")?> /><label for="status1"> Enabled</label> <input type="radio" name="status" value="0" id="status0" <?=is_selected("status","","0",$data,"check")?> /><label for="status0"> Disabled</label></td>
				</tr>
			</table>
			<p class="submit"><input type="submit" value="<?=$action=="add"?"Add":"Update"?> User" /></p>
			</form>
			<?
			break;
		default:
			$pgnums=pagenums("SELECT * FROM (customers as cu LEFT JOIN counties as c ON c.`county_id`=cu.`state`) LEFT JOIN countries as co ON co.`country_id`=cu.`country` $search ORDER BY `$orderby` $orderdir","admin.php?p=customers",30,5);
			$query=$pgnums[0];
			$custsq=mysql_query($query);
			$custsnum=mysql_num_rows($custsq);
			?>
			<table class="details">
			<tr>
				<td class="head"><div class="titles"><?=helplink($page)?> Customers</div><div class="links"><a href="<?=$self?>&amp;genmailing=1">Generate mailing list</a> | <a href="<?=$self?>&amp;act=add">Add new customer</a></div></td>
			</tr>
			<? if(strlen($pgnums[1])>0){?>
			<tr>
				<td class="infohead"><?=$pgnums[1]?></td>
			</tr>
			<? }?>
			<tr>
				<td>
				<form action="<?=$self?>" method="get">
				<input type="hidden" name="p" value="customers" />
				Search <input type="text" name="searchterm" value="<?=posted_value("searchterm","","",$_GET)?>" /> by 
				<select name="searchby">
				<option value="lastname" <?=is_selected("searchby","","lastname",$_GET,"select")?>>Last Name</option>
				<option value="firstname" <?=is_selected("searchby","","firstname",$_GET,"select")?>>First Name</option>
				<option value="email" <?=is_selected("searchby","","email",$_GET,"select")?>>Email</option>
				<option value="city" <?=is_selected("searchby","","city",$_GET,"select")?>>City</option>
				<option value="state" <?=is_selected("searchby","","state",$_GET,"select")?>>County/State</option>
				<option value="country" <?=is_selected("searchby","","country",$_GET,"select")?>>Country</option>
				</select> 
				<input type="submit" value="Search" class="formbutton" />
				</form>
				</td>
			</tr>
			</table>
			<form action="<?=$self.$searchurl?>" method="post">
			<table class="details">
			<tr>
				<td class="subhead"><a href="<?=$self?>&amp;orderby=lastname--<?=$orderby=="lastname"&&$orderdir=="ASC"?"DESC":"ASC"?>">Customer</a></td>
				<td class="subhead"><a href="<?=$self?>&amp;orderby=countyname--<?=$orderby=="countyname"&&$orderdir=="ASC"?"DESC":"ASC"?>">County</a></td>
				<td class="subhead"><a href="<?=$self?>&amp;orderby=countryname--<?=$orderby=="countryname"&&$orderdir=="ASC"?"DESC":"ASC"?>">Country</a></td>
				<td class="subhead" style="text-align:center">Newsletter</td>
				<td class="subhead"><a href="<?=$self?>&amp;orderby=signup_date--<?=$orderby=="signup_date"&&$orderdir=="ASC"?"DESC":"ASC"?>">Signup</a></td>
				<td class="subhead" style="text-align:center">Details</td>
				<td class="subhead" style="text-align:center"><a href="<?=$self?>&amp;orderby=status--<?=$orderby=="status"&&$orderdir=="ASC"?"DESC":"ASC"?>">Status</a></td>	
				<td class="subhead" style="text-align:center">Delete</td>		
			</tr>
			<? while($custs=mysql_fetch_assoc($custsq)){$row=((!isset($row)||$row=="1")?"0":"1");?>
			<tr class="row<?=$row?>">
				<td><?=(($custs['email'])?"<a href='mailto:$custs[email]'>":"")?><?=$custs['lastname'].", ".$custs['firstname']?><?=(($custs['email'])?"</a>":"")?></td>
				<td><?=$custs['countyname']?></td>
				<td><?=$custs['countryname']?></td>
				<td style="text-align:center"><?=$mailtype[$custs['mailing']]?></td>
				<td><?=date("F d, Y",$custs['signup_date'])?></td>
				<td style="text-align:center"><a href="<?=$self?>&amp;act=view&amp;cust_id=<?=$custs['cust_id']?>">Details</a></td>
				<td style="text-align:center">
				<input type="hidden" name="status[<?=$custs['cust_id']?>]" value="0" />
				<input type="checkbox" name="status[<?=$custs['cust_id']?>]" value="1" <?=is_selected("status",$custs['cust_id'],"1",$custs,"check")?> />
				</td>		
				<td style="text-align:center"><input type="checkbox" name="delete[<?=$custs['cust_id']?>]" value="1" /></td>
			</tr>
			<? }
			if($custsnum<1)
			{
				?><tr><td colspan="8" style="text-align:center">No customers found</td></tr><?
			}
			else
			{
				?>
				<tr>
					<td colspan="6">&#160;</td>
					<td style="text-align:center"><input type="submit" name="items" value="Update" class="formbutton" onclick="return decision('Are you sure you wish to alter the status of the displayed customers?\n\n(Ticked: Enabled, Unticked: Disabled)\n\n','<?=$self?>')" /></td>
					<td style="text-align:center"><input type="submit" name="items" value="Delete" class="formbutton" onclick="return decision('Are you sure you wish to delete the selected customers?','<?=$self?>')" /></td>
				</tr>
				<? 
			}?>
			</table>
			</form>
			<? if(strlen($pgnums[1])>0){?>
			<div class="infohead"><?=$pgnums[1]?></div>
			<? }?>
			<?
			break; 
	}
	?>
	<!-- /CONTENT -->
	</div>
</div>