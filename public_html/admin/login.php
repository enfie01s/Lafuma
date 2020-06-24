<? 

include "../content/config.php";
include "../content/functions.php";
if($_POST['identifier']=="lostpass"&&!isset($_SESSION['error']))
{
	$emailcheck_q=mysql_query("SELECT email, password, username, date_created FROM admin_users WHERE email='$_POST[email]'")or die(mysql_error());
	$emailcheck=mysql_num_rows($emailcheck_q);list($demail,$dpass,$dname,$dsign)=mysql_fetch_row($emailcheck_q);
	if($emailcheck==0){$_SESSION['error']="Sorry, no admin account was found with that email address.";}
}
//error checks for password reset
if($_POST['identifier']=="dopassreset")
{
	$emailcheck_q=mysql_query("SELECT email, password, username, date_created,admin_id FROM admin_users WHERE username='$_POST[user]'")or die(mysql_error());
	$emailcheck=mysql_num_rows($emailcheck_q);list($demail,$dpass,$dname,$dsign,$admin_id)=mysql_fetch_row($emailcheck_q);
	if($emailcheck==0){$_SESSION['error']="Sorry, no admin account was found with that email address.";}
	if($_POST['code']!=md5($dname.$demail.$dpass.$dsign)&&!isset($_SESSION['error'])){$_SESSION['error']="Invalid security code.";}
}
//no errors
if(!isset($_SESSION['error']) && isset($_POST['identifier']))
{
	$eheaders = "From: Lafuma UK <sales@llc-ltd.co.uk>\r\n";
	$eheaders .= "Reply-To: sales@llc-ltd.co.uk\r\n";
	$eheaders .= "Return-Path: sales@llc-ltd.co.uk\r\n";
	$eheaders .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
	switch($_POST['identifier'])
	{
		case "dopassupdate":
			$_SESSION['adminpass']=hashandsalt($_POST['user'],$_POST['password1']);
			$newpass=hashandsalt($_POST['user'],$_SESSION['adminpass']);
			mysql_query("UPDATE admin_users SET password='*$newpass',date_edited='".date("U")."' WHERE cust_id='$_POST[admin_id]'");
			//set confirm email details
			$msg="========================================<br />
			You password change at Lafuma UK<br />
			========================================<br />
			Hi ".$_POST['user'].",<br />
			<br />
			You have changed your admin account password on the Lafuma UK website, please see your new login details below.<br />
			<br />
			<strong>Username:</strong>&#160;".$_POST['user']."<br />
			<strong>Password:</strong>&#160;".$_POST['password1']."<br />";
			$to=$_POST['cust_email'];
			$subject="Password changed at Lafuma UK";
			break;
		case "lostpass":
			$thehash=md5($dname.$demail.$dpass.$dsign);
			$msg="===========================================<br />
			Request to reset your password on the Lafuma UK website<br />
			===========================================<br />
			Hi ".$dname.",<br />
			<br />
			A request was made to reset your password. If you did not make this request or have since remembered your password, please ignore this email.<br /><br />
			Click <a href='$mainbase/admin/login.php?resetpassform=".$thehash."'>HERE</a> to reset your password.<br />";
			$to=$demail;
			$subject="Request to reset your password on Lafuma UK";
			break;
		case "dopassreset":
			$newpass1=hashandsalt($_POST['user'],$_POST['password1']);
			$newpass2=hashandsalt($_POST['user'],$newpass1);
			mysql_query("UPDATE admin_users SET password='*$newpass2',date_edited='".date("U")."' WHERE admin_id='$admin_id'");
			$msg="===========================================<br />
			Successfully reset your password on the Lafuma UK website<br />
			===========================================<br />
			Hi ".$dname.",<br />
			<br />
			Your password was successfully reset. Please find your new login details below.<br />
			<br>
			<strong>Username:</strong>&#160;".$dname."<br />
			<strong>Password:</strong>&#160;".$_POST['password1']."<br />";
			$to=$demail;
			$subject="Request to reset your password on Lafuma UK";
			break;
	}
	$msg .= "<br />
	Kind Regards<br />
	<a href='http://www.lafuma.co.uk/'>Lafuma UK</a><br />
	01489 557 600<br />
	<a href='mailto:sales@llc-ltd.co.uk'>sales@llc-ltd.co.uk</a>";
	
	if(mail($to,$subject,$msg,$eheaders,"-f".$admin_email)){$_SESSION['allgood']="Please check your inbox for further instructions";}else{$_SESSION['error']="There was an error sending the password retrieval details";}
	if($_POST['identifier']=="dopassreset"){$_SESSION["success"]="Password reset successful, you may now log in with your new details";header("Location: $mainbase/admin/login.php");}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="en-gb" />
<meta name="robots" content="noindex, nofollow" />
<meta http-equiv="imagetoolbar" content="false" />
<meta name="MSSmartTagsPreventParsing" content="true" />
<meta name="author" content="Lafuma UK" />
<!--<meta name="google-site-verification" content="sesA5RpHgdlVBvVDFb8PFUlMj8-Yl0SNDwlxAUKrxKI" />-->
<!--for Froogle-->
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="../content/adminstyle.css" type="text/css" />
<title>Lafuma UK Administration</title>
</head>
<body>
<div id="outer">
<div id="inner">
	<!--HEADER-->
	<div id="header">
		<div id="logo"><a href="<?=$mainbase?>"><img src="../content/img/admin/logo.jpg" alt="Lafuma" /></a></div>
	</div>
	<!--/HEADER-->
	<!--MAIN-->
	<div id="mid" style="width:350px;margin:50px auto 0;">
		<? 
if(basename($_SERVER['PHP_SELF'])!="login.php"){die("Access Denied");}//direct access security
if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }
else if(isset($_SESSION['allgood'])){?><div id="errorbox"><?=$_SESSION['allgood']?></div><? unset($_SESSION['allgood']); }
$match=(isset($_POST['identifier']))?$_POST:"";
/* ----- PASSWORD RESET FORM ----- */
if(isset($_GET['resetpassform'])&&$_GET['resetpassform']!=""){
?>
		<form action="<?=$mainbase?>/admin/login.php?resetpassform=<?=$_GET['resetpassform']?>" method="post">
			<input type="hidden" name="identifier" value="dopassreset" />
			<input type="hidden" name="code" value="<?=$_GET['resetpassform']?>" />
			<div class="tabletitle">Password Reset</div>
			All fields marked (*) are required
			<table cellspacing="1" cellpadding="0" width="100%">
				<tr>
					<td class="first"><label for="emaillogin">Username <span>*</span></label></td>
					<td><input type="text" name="user" id="emaillogin" value="" onfocus="this.select()" class="formfield" />
					</td>
				</tr>
				<tr>
					<td><label for="passlogin">Password <span>*</span></label></td>
					<td><input type="password" name="password1" id="passlogin" value="" onfocus="this.select()" class="formfield" />
					</td>
				</tr>
				<tr>
					<td><label for="passlogin">Confirm Password <span>*</span></label></td>
					<td><input type="password" name="password2" id="passlogin" value="" onfocus="this.select()" class="formfield" />
					</td>
				</tr>
			</table>
			<br />
			<input type="submit" name="submitpasschange" value="Modify Password" class="formbutton" />
		</form>
		<? }else{
/* ----- CUSTOMER LOGIN FORM ----- */
if(isset($_SESSION['timedout'])&&$_SESSION['timedout']==1){?>
<p style="text-align:center">Your session timed out due to <?=$aidle_minutes?> minutes of inactivity</p>
<? }
?>
		<form action="<?=$mainbase?>/content/aauth.php" method="post">
			<input type="hidden" name="identifier" value="login" />
			<div class="tabletitle">Admin Login</div>
			<table cellspacing="1" cellpadding="0" width="100%">
				<tr>
					<td class="first"><label for="emaillogin">Username <span>*</span></label></td>
					<td><input type="text" name="user" id="emaillogin" value="" onfocus="this.select()" class="formfield" />
					</td>
				</tr>
				<tr>
					<td><label for="passlogin">Password <span>*</span></label></td>
					<td><input type="password" name="pass" id="passlogin" value="" onfocus="this.select()" class="formfield" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="loginsubmit" value="Login" class="formbutton" /></td>
				</tr>
			</table>
		</form>
		<br />
		<form action="<?=$mainbase?>/admin/login.php" method="post">
			<input type="hidden" name="identifier" value="lostpass" />
			<div class="tabletitle">Lost your password?</div>
			<table cellspacing="1" cellpadding="0" width="100%">
				<tr>
					<td class="first"><label for="emaillost">Email Address</label></td>
					<td><input type="text" name="email" id="emaillost" value="email address" onfocus="this.select()" class="formfield" /></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="passsubmit" value="Request Password" class="formbutton" /></td>
				</tr>
			</table>
		</form>
		<p style="text-align:center">Your IP address is: <?=$_SERVER['REMOTE_ADDR']?></p>
		<p style="text-align:center"><a href="<?=$mainbase?>">Return to shop front</a></p>
		<? }?>
	</div>
	<!--/MAIN-->
	<!-- FOOTER -->
	<!--/FOOTER-->
</div>
</div>
</body>
</html>
