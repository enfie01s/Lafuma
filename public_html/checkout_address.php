<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
$tomatch=$_SESSION['loggedin']!=0?$ua:(isset($_POST['identifier'])?$_POST:"");
$strCart=$_SESSION["cart"];
if (!is_array($strCart)||count($strCart)==0) 
{
	redirection("$mainbase/index.php?p=shopping_cart");
	exit();
}
?>
<form action="<?=$securebase?>/index.php?p=checkout_address" method="post">
<input type="hidden" name="identifier" value="checkout_customer" />
<? if($_SESSION['loggedin']==0){?>
<fieldset>
<legend>Registration - optional</legend>
<p>If you wish to register on our site then please enter an account password. Registration enables you to access the My Account section of our site to update your details, view your invoice history and status, setup a wish list or signup to our mailing list.</p>
<table class="details">
<? formrows(array("password1"=>"Password","password2"=>"Confirm Password"),array(),array(),array(),array(),"","registerpass");?>
<tr><td class="row_dark" colspan="2"><input type="hidden" name="opt_in" value="0" /><input type="checkbox" name="opt_in" id="opt_in" value="1" checked="checked" /><label for="opt_in">I would like to receive updates and special offers from Lafuma</label></td></tr>
</table>
</fieldset>
<? }else{?>
<p>If you are not <?=$ua['firstname']." ".$ua['lastname']?>, <a href="<?=$mainbase?>/index.php?p=checkout_login&amp;logout">please sign in or register.</a></p>
<? }?>
<fieldset>
<legend>Billing address</legend>
<p>All fields marked (*) are required</p>
<table class="details">
<? formrows(array("firstname"=>"First Name","lastname"=>"Last Name","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","email"=>"Email Address","phone"=>"Telephone","homepage"=>"Website","company"=>"Company"),$requireds['doregister'],array("state"=>"SELECT county_id,countyname FROM counties ORDER BY countyname ASC","country"=>"SELECT cshortname,countryname,country_id FROM countries ORDER BY countryname ASC"),array(),array(),$tomatch,"billingaddy");?>
</table>
</fieldset>
<fieldset>
<legend>Delivery address</legend>
<table class="details">
<? $prefix="deliver_";formrows(array("matchbilling"=>"Same as billing",$prefix."firstname"=>"First Name",$prefix."lastname"=>"Last Name",$prefix."address1"=>"Address 1",$prefix."address2"=>"Address 2",$prefix."city"=>"City",$prefix."state"=>"County/State",$prefix."postcode"=>"Postcode/Zip",$prefix."country"=>"Country",$prefix."phone"=>"Telephone"),$requireds['doregister'],array($prefix."state"=>"SELECT county_id,countyname FROM counties ORDER BY countyname ASC",$prefix."country"=>"SELECT cshortname,countryname,country_id FROM countries ORDER BY countryname ASC"),array(),array("matchbilling"=>"1: "),$tomatch,$prefix);?>
<!--
<tr>
<td><label for="comments">Special requirements</label><br /><dfn>* Please note: All goods MUST be signed for and will not be left unattended.</dfn></td>
<td><textarea name="comments" id="comments" style="width:250px;height:45px;" onfocus="this.select()"></textarea></td>
</tr>-->
</table>
</fieldset>
<input type="checkbox" name="terms_agree" id="terms_agree" value="1" /><label for="terms_agree">I agree to the <a href="<?=$mainbase?>/index.php?p=terms" target="_blank">Terms &amp; Conditions</a></label>
<br />
<br />
<input type="submit" name="checkout_customer" value="Continue" class="formbutton" />
</form>
<script src="<?=$mainbase?>/content/js/countrycodes.js" type="text/javascript"></script>