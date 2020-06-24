<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 

if($_SESSION['loggedin']!=0&&$errormsg==""){?>
	<ul id="deptsubnav">
		<li><a href="<?=$mainbase?>/index.php?p=my_account&amp;updateform">Update Information</a></li>
		<li><a href="<?=$mainbase?>/index.php?p=my_account&amp;updatepassform">Change Password</a></li>
		<li><a href="<?=$mainbase?>/index.php?logout=1">Logout</a></li>
	</ul>
	<? 
} 

/* ----- UPDATE INFO FORM ----- */
if((isset($_GET['updateform'])&&$_SESSION['loggedin']!=0&&$errormsg=="")||($_POST['identifier']=="doupdate"&&$errormsg!="")){
	$tomatch=(isset($_POST['identifier']))?$_POST:$ua;?>
	<div class="tabletitle">Update Information</div>
	All fields marked (*) are required
	<form action="<?=$mainbase?>/index.php?p=my_account" method="post">
	<input type="hidden" name="identifier" value="doupdate" />
	<input type="hidden" name="cust_id" value="<?=$ua['cust_id']?>" />
	<input type="hidden" name="cust_email" value="<?=$ua['email']?>" />
	<table class="details">
		<!--formrows(fieldsarray(fieldname=>label),requiredfieldsarray,dropdownsarray,radiosarray(fieldname=>radiovalue:radioname),what to match(array/text))-->
		<? formrows(array("firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company","mailing"=>"Email List"),$requireds['doupdate'],array("state"=>"SELECT `county_id`,`countyname` FROM counties ORDER BY `countyname` ASC","country"=>"SELECT `country_id`,`countryname` FROM countries ORDER BY `countryname` ASC"),array("mailing"=>"2:Plain Text,1:HTML,0:None"),array(),$tomatch,"updateform");?>
	</table>
	<br />
	<input type="submit" name="submitupdate" value="Submit Request" class="formbutton" />
	</form>
<? 
/* ----- PASSWORD CHANGE FORM ----- */
}else if((isset($_GET['updatepassform'])&&$_SESSION['loggedin']!=0&&$errormsg=="")||($_POST['identifier']=="dopassupdate"&&$errormsg!="")){?>
	<form action="<?=$mainbase?>/index.php?p=my_account" method="post">
	<input type="hidden" name="identifier" value="dopassupdate" />
	<input type="hidden" name="cust_id" value="<?=$ua['cust_id']?>" />
	<input type="hidden" name="cust_email" value="<?=$ua['email']?>" />
	<input type="hidden" name="firstname" value="<?=$ua['firstname']?>" />
	<input type="hidden" name="lastname" value="<?=$ua['lastname']?>" />
	<div class="tabletitle">Change Password</div>
	All fields marked (*) are required
	<table>
	<? formrows(array("password1"=>"New Password","password2"=>"Confirm New Password"),$requireds['dopassupdate'],array(),array(),array(),"","passchange");?>
	</table>
	<br />
	<input type="submit" name="submitpasschange" value="Modify Password" class="formbutton" />
	</form>
	<? 
/* ----- DISPLAY INFO ----- */
}else if($_SESSION['loggedin']!=0&&$errormsg==""){?>
	<h3>Current Information</h3>
	<table class="details">
		<tr>
			<td class="first">First Name</td>
			<td><?=$ua['firstname']?></td>
		</tr>
		<tr>
			<td>Last Name</td>
			<td><?=$ua['lastname']?></td>
		</tr>
		<tr>
			<td>Email</td>
			<td><?=($ua['email']!="")?"<a href='mailto:$ua[email]'>$ua[email]</a>":"";?></td>
		</tr>
		<tr>
			<td>Phone</td>
			<td><?=$ua['phone']?></td>
		</tr>
		<tr>
			<td style="vertical-align:top">Address</td>
			<td>
			<?=$ua['address1']?><br />
			<?=$ua['address2']?><br />
			<?=$ua['city']?><br />
			<?=$ua['countyname']?><br />
			<?=$ua['postcode']?><br />
			<?=$ua['countryname']?><br />
			</td>
		</tr>
		<tr>
			<td>Website</td>
			<td><?=($ua['homepage']!="")?"<a href='$ua[homepage]'>$ua[homepage]</a>":""?></td>
		</tr>
		<tr>
			<td>Company</td>
			<td><?=$ua['company']?></td>
		</tr>
		<tr>
			<td>Receive Marketing Emails</td>
			<td><?=$mailtype[$ua['mailing']]?></td>
		</tr>
		<tr>
			<td>Date Registered</td>
			<td><?=date("F d\, Y",$ua['signup_date'])?></td>
		</tr>
	</table>
	<br />
	<?
	$ordersq=mysql_query("SELECT `date_ordered`,`invoice` FROM orders WHERE `cust_id`='$ua[cust_id]' ORDER BY `date_ordered` DESC");
	$orders=mysql_num_rows($ordersq);
	if($orders>0){
		?>
		<h3>My Orders</h3>
		<table class="details">
		<tr>
			<td class="head">Order Date</td>
			<td class="head">Invoice</td>
			<td class="head">Details</td>
		</tr>
		<? while($order=mysql_fetch_assoc($ordersq)){?>
			<tr>
				<td><?=date("F j, Y",$order['date_ordered'])?></td>
				<td><?=$order['invoice']?></td>
				<td><a href="<?=$mainbase?>/index.php?p=receipt&amp;invoice=<?=$order['invoice']?>">Details</a></td>
			</tr>
		<? }?>
		</table>
		<? 
	}
} else {
	$tomatch=(isset($_POST['identifier']))?$_POST:"";?>
	<div class="tabletitle">Customer Registration Form</div>
	All fields marked (*) are required
	<form action="<?=$mainbase?>/index.php?p=my_account" method="post">
	<input type="hidden" name="identifier" value="doregister" />
	<input type="hidden" name="cust_id" value="<?=$ua['cust_id']?>" />
	<table class="details">
	<? formrows(array("password1"=>"Password","password2"=>"Confirm Password","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company"),$requireds['doregister'],array("state"=>"SELECT `county_id`,`countyname` FROM counties ORDER BY countyname ASC","country"=>"SELECT `country_id`,`countryname` FROM countries ORDER BY `countryname` ASC"),array(),array(),$tomatch,"regform");?>
	</table><input type="hidden" name="opt_in" value="0" /><input type="checkbox" name="opt_in" id="opt_in" value="1" checked="checked" /><label for="opt_in">I would like to receive updates and special offers from Lafuma</label><br />
	<input type="checkbox" name="terms_agree" id="terms_agree" value="1" /><label for="terms_agree">I agree to the <a href="<?=$mainbase?>/index.php?p=terms" target="_blank">Terms &amp; Conditions</a></label><br /><br />
	<input type="submit" name="submitreg" value="Process Request" class="formbutton" />
	</form>
	<? 
}
?>