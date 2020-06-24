<? 
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
if(isset($_SESSION['error'])){echo $_SESSION['error']; unset($_SESSION['error']); }
/* ----- PASSWORD RESET FORM ----- */
if(isset($_GET['resetpassform'])&&$_GET['resetpassform']!=""){
$match=(isset($_POST['identifier']))?$_POST:"";
?><h2 id="pagetitle">Password Reset</h2>
	<form action="<?=$mainbase?>/index.php?p=customer_login&resetpassform=<?=$_GET['resetpassform']?>" method="post">
	<input type="hidden" name="identifier" value="dopassreset" />
	<input type="hidden" name="code" value="<?=$_GET['resetpassform']?>" />
	<div class="tabletitle">Password Reset</div>
	All fields marked (*) are required
	<table>
	<? formrows(array("email"=>"Email","password1"=>"New Password","password2"=>"Confirm New Password"),$requireds['dopassreset'],array(),array(),array(),$match,"resetpass");?>
	</table>
	<br />
	<input type="submit" name="submitpasschange" value="Modify Password" class="formbutton" />
	</form>
<? }else{
/* ----- CUSTOMER LOGIN FORM ----- */
?><h2 id="pagetitle">Customer Login</h2><?
if(isset($_POST['identifier'])&&$_POST['identifier']=="lostpass"&&$errormsg==""){?>Request Successful. Please check your email for further instructions.<? }
if(isset($_SESSION["success"])&&$_SESSION["success"]!=""){echo $_SESSION["success"];unset($_SESSION["success"]);}
?>

<h2><a href="<?=$mainbase?>/index.php?p=my_account&amp;registerform">Not Registered? Sign up here &#62;</a></h2>
<form action="<?=$mainbase?>/content/auth.php" method="post">
<input type="hidden" name="identifier" value="login" />
<input type="hidden" name="redirectstring" value="<?=str_replace(array("p=customer_login&","p=customer_login","to_","&hash="),array("","","","#"),$_SERVER['QUERY_STRING'])?>" />
<div class="tabletitle">Customer Login</div>
<table>
<? formrows(array("email"=>"Email Address","pass"=>"Password"),array("email","pass"),array(),array(),array(),$match,"login");?>
<tr>
<td></td>
<td><input type="submit" name="loginsubmit" value="Customer Login" class="formbutton" /></td>
</tr>
</table>
</form>
<br />
<form action="<?=$mainbase?>/index.php?p=customer_login" method="post">
<input type="hidden" name="identifier" value="lostpass" />
<div class="tabletitle">Lost your password?</div>
<table>
<tr>
<td class="first"><label for="emaillost">Email Address</label></td>
<td><input type="text" name="email" id="emaillost" value="email address" onfocus="this.select()" class="formfield" /></td>
</tr>
<tr>
<td></td>
<td><input type="submit" name="passsubmit" value="Send Password" class="formbutton" /></td>
</tr>
</table>
</form>
<? }?>