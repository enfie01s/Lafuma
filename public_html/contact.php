<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security  ?>
<h2 id="pagetitle">Contact Us</h2>
<script src="content/js/common.js" type="text/javascript"></script>
<? 
$mysql_real_post=mysql_real_extracted($_POST);
if(isset($_POST['identifier'])&&$_POST['identifier']=="contactform"&&count($contact_errors)<1&&strlen($_POST['spacey'])<1){
//insert to db
mysql_query("INSERT INTO contactus(`name`,`address1`,`address2`,`country`,`workphone`,`email`,`comments`,`date_created`) VALUES('$mysql_real_post[sName]','$mysql_real_post[sAddress1]','$mysql_real_post[sAddress2]','$mysql_real_post[sCountry]','$mysql_real_post[sPhone]','$mysql_real_post[sEmail]','$mysql_real_post[sComments]','".date("U")."')");
//email sales
$headers = "From: ".$mysql_real_post['sName']." <".$mysql_real_post['sEmail'].">\r\n";
$headers .= "Reply-To: ".$mysql_real_post['sEmail']."\r\n";
$headers .= "Return-Path: ".$mysql_real_post['sEmail']."\r\n";
$subject="Contact request from Lafuma UK";
$to=($testing==1)?"senfield@gmk.co.uk":$admin_email;
$message="
========================================
Contact request from Lafuma UK
========================================
Name: 	".$mysql_real_post['sName']."
Address: 	".$mysql_real_post['sAddress1']."\r\n".((strlen($mysql_real_post['sAddress2'])>0)?",":"").$mysql_real_post['sAddress2']."\r\n
Country: ".$mysql_real_post['sCountry']."
Phone: ".$mysql_real_post['sPhone']."
Comments:
".$mysql_real_post['sComments']."
";
@mail($to,$subject,$message,$headers,"-f".$mysql_real_post['sName']);
?>
<p>Thank you for posting your comments, we will get back to you shortly.</p>
<p>Please return to the <a href="<?=$mainbase?>/index.php">home page</a>.</p>
<? }else{
//$count_errors listed here
?>
<p style="font-size:1.1em;">Lafuma UK<br />
	Bear House,<br />
	Concorde Way,<br />
	Fareham,<br />
	Hampshire<br />
	United Kingdom<br />
	PO15 5RL<br />
	Phone No.
	<?=$sales_phone?>
</p>
<p style="font-size:1.1em;">vat.Registration No.:
	<?=$vatreg?>
	<br />
	Company Registration No.:
	<?=$coreg?>
</p>
<p style="font-size:1.1em;"><strong>Outside UK/Ireland?</strong> We are the distributors of Lafuma for the UK &amp; Ireland.  If you are contacting us from outside the UK &amp; Ireland, please click <a href="lafumadistributors.pdf" target="_blank">here</a> to find a list of the Lafuma distributors for different countries.  If your country is not listed here, please contact Lafuma directly using the French contact details.<br /><br />For a list of frequently asked questions, click <a href='index.php?p=faq'>here</a>.</p>

<form action="<?=$mainbase?>/index.php?p=contact" method="post">
<input type="hidden" name="identifier" value="contactform" />
<input type="hidden" name="spacey" value="" />
	<fieldset>
	<legend>Contact Form</legend>
	<p>All fields marked (*) are required</p>
	<table class="details">
		<tr>
			<td class="first"><label for="sName">Name <span>*</span></label><?=((isset($contact_errors)&&in_array("sName",$contact_errors))?"<div style='color:red;line-height:3px;font-size:0.9em'>Please enter your name</div>":"")?></td>
			<td><input class="formfield" type="text" name="sName" id="sName" value="<?=((isset($_POST['sName']))?$_POST['sName']:"")?>" maxlength="50" <?=((isset($contact_errors)&&in_array("sName",$contact_errors))?"style='border:1px solid red'":"")?> /></td>
		</tr>
		<tr>
			<td><label for="sEmail">Email Address <span>*</span></label><?=((isset($contact_errors)&&in_array("sEmail",$contact_errors))?"<div style='color:red;line-height:3px;font-size:0.9em'>Please enter valid email</div>":"")?></td>
			<td><input class="formfield" type="text" name="sEmail" id="sEmail"  value="<?=((isset($_POST['sEmail']))?$_POST['sEmail']:"")?>" maxlength="50" <?=((isset($contact_errors)&&in_array("sEmail",$contact_errors))?"style='border:1px solid red'":"")?> /></td>
		</tr>
		<tr>
			<td><label for="sAddress1">Address 1 </label></td>
			<td><input class="formfield" type="text" name="sAddress1" id="sAddress1" value="<?=((isset($_POST['sAddress1']))?$_POST['sAddress1']:"")?>" maxlength="100"  /></td>
		</tr>
		<tr>
			<td><label for="sAddress2">Address 2 </label></td>
			<td><input class="formfield" type="text" name="sAddress2" id="sAddress2" value="<?=((isset($_POST['sAddress2']))?$_POST['sAddress2']:"")?>" maxlength="50" /></td>
		</tr>
		<tr>
			<td><label for="sPhone">Phone</label></td>
			<td><input class="formfieldm" type="text" name="sPhone" id="sPhone" value="<?=((isset($_POST['sPhone']))?$_POST['sPhone']:"")?>" maxlength="20" /></td>
		</tr>
		<tr>
			<td><label for="sCountry">Country</label></td>
			<td><select class="formfieldm" id="sCountry" name="sCountry">
					<option value="United Kingdom" selected='selected'>United Kingdom</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top"><label for="Comments">Request <span>*</span></label><?=((isset($contact_errors)&&in_array("sComments",$contact_errors))?"<div style='color:red;line-height:3px;font-size:0.9em'>Please enter your request</div>":"")?></td>
			<td><textarea class="formfield" id="Comments" name="sComments" rows="8" cols="35" <?=((isset($contact_errors)&&in_array("sComments",$contact_errors))?"style='border:1px solid red'":"")?>><?=((isset($_POST['sComments']))?$_POST['sComments']:"")?></textarea></td>
		</tr>		
		<tr>
			<td style="vertical-align:top">Captcha<?=((isset($contact_errors)&&in_array("sCaptcha",$contact_errors))?"<div style='color:red;line-height:3px;font-size:0.9em'>Captcha incorrect.</div>":"")?></td>
			<td>
			<div <?=((isset($contact_errors)&&in_array("sCaptcha",$contact_errors))?"style='border:1px solid red'":"")?>>
			<? echo recaptcha_get_html($publickey);?>
			</div>
			</td>
		</tr>
	</table>
	</fieldset>
	<input type="submit" name="submit_contact" value="Submit Request" class="formbutton" />
	<input type="reset" name="reset" value="Reset" class="formbutton" />
</form>
<? }?>