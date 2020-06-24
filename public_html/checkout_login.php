<? 
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security

if(isset($_GET['lostpass'])){
if(isset($_POST['identifier'])&&$_POST['identifier']=="lostpass"&&$errormsg==""){?>Request Successful. Please check your email for further instructions.<? }
?>
<form action="<?=$mainbase?>/index.php?p=checkout_login&lostpass=1" method="post">
<input type="hidden" name="identifier" value="lostpass" />
<div class="tabletitle">Enter your email address to retrieve your password</div>
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
<p>Proceed as <a href="<?=$mainbase?>/index.php?p=checkout_login">new customer &#62;</a></p>
<? }else{?>
<div>If you are not a registered customer, please enter your email address to continue.</div>
<div class="tabletitle">New Customers</div>
<form action="<?=$securebase?>/index.php?p=checkout_address" method="post">
<input type="hidden" name="identifier" value="checkoutnew" />
<table>
<? formrows(array("email"=>"Email Address"),$requireds['checkout_newcust'],array(),array(),array(),"","newcust");?>
<tr>
	<td></td>
	<td><input type="submit" name="submitnewcust" value="Continue" class="formbutton" /></td>
</tr>
</table>
</form>
<p>If you are a registered customer, please enter your email address and password to login.</p>
<div class="tabletitle">Registered Customers</div>
<form action="<?=$securebase?>/content/auth.php" method="post">
<input type="hidden" name="identifier" value="login" />
<input type="hidden" name="redirectstring" value="p=checkout_address" />
<table>
<? formrows(array("email"=>"Email Address","pass"=>"Password"),$requireds['checkout_registered'],array(),array(),array(),"","regcust");?>
<tr>
	<td></td>
	<td><input type="submit" name="submitregcust" value="Customer Login" class="formbutton" /></td>
</tr>
</table>
</form>
<p><a href="<?=$mainbase?>/index.php?p=checkout_login&amp;lostpass=1">Lost your password?</a></p>
<? }
if(isset($_SESSION['error'])){unset($_SESSION['error']);  }?>