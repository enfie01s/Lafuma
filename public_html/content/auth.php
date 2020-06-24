<? 
if(strtolower($_SERVER['REQUEST_METHOD'])!="post"){die("Access Denied");}//Direct access security
if(isset($_POST['identifier'])&&$_POST['identifier']=="login")
{
	include "config.php";
	include "functions.php";
	$post_extracted=mysql_real_extracted($_POST);
	if($post_extracted['pass']==""||$post_extracted['email']=="")//a missing field
	{
		$_SESSION['error']="You did not fill in all information. Please make sure all fields are completed.";
		header("Location: $mainbase/index.php?p=customer_login");
	}
	else//fields not empty
	{
		$pass1=hashandsalt($post_extracted['email'],$post_extracted['pass']);
		$pass2=hashandsalt($post_extracted['email'],$pass1);
		$uq=mysql_query("SELECT email,gpassword FROM customers WHERE email='$post_extracted[email]' AND gpassword='$pass2'");
		$un=mysql_num_rows($uq);
		if($un>0)//user found
		{
			$ua=mysql_fetch_assoc($uq);
			$_SESSION['pass']=$pass1;
			$eparts=explode("@",$ua['email']);
			$_SESSION['epart1']=$eparts[0];
			$_SESSION['epart2']=$eparts[1];
			$goto=$_POST['redirectstring'];
			$whichbase=$goto=="p=checkout_address"?$securebase:$mainbase;
			header("Location: $whichbase/index.php?".$goto);
		}
		else//user not found
		{
			$_SESSION['error']="There was a problem with your email or password, please try again.";
			$goto=(isset($_POST['redirectstring'])&&$_POST['redirectstring']=="p=checkout_address")?"p=checkout_login":"p=customer_login&$_POST[pass]=$_POST[email]";
			//echo $pass2;
			header("Location: $mainbase/index.php?".$goto);
		}
	}
}
?>