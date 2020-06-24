<? 
ini_set('display_errors',1); 
 error_reporting(E_ALL);
if(strtolower(substr($_SERVER['REQUEST_METHOD'],-4,4))!="post"){die("Access Denied");}//Direct access security
if(isset($_POST['identifier'])&&$_POST['identifier']=="login")
{
	include "config.php";
	include "functions.php";
	if($_POST['pass']==""||$_POST['user']=="")//a missing field
	{
		$_SESSION['error']="You did not fill in all information. Please make sure all fields are completed.";
		header("Location: $mainbase/admin/login.php");
	}
	else//fields not empty
	{
		$pass1=hashandsalt($_POST['user'],$_POST['pass']);
		$pass2=hashandsalt($_POST['user'],$pass1);
		$uq=mysql_query("SELECT username,password FROM admin_users WHERE username='$_POST[user]' AND password='*$pass2'");
		$un=mysql_num_rows($uq);
		if($un>0)//user found
		{
			$ua=mysql_fetch_assoc($uq);
			$_SESSION['adminpass']=$pass1;
			$_SESSION['adminuser']=$ua['username'];
			header("Location: $mainbase/admin.php".((isset($_SESSION['query']))?"?".$_SESSION['query']:""));
		}
		else//user not found
		{
			$_SESSION['error']="There was a problem with your email or password, please try again.";
			header("Location: $mainbase/admin/login.php");
		}
	}
}
?>