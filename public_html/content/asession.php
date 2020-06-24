<? 
if(!in_array(basename($_SERVER['PHP_SELF']),array("admin.php","index.php"))){die("Access Denied");}//direct access security
if(isset($_SESSION['adminuser'])&&isset($_SESSION['adminpass']))
{
	if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']!=0&&time()-$_SESSION['aloggedin']>$adminidletime){unset($_SESSION['adminpass']);unset($_SESSION['adminuser']);$_SESSION['timedout']=1;}
	$pass=hashandsalt($_SESSION['adminuser'],$_SESSION['adminpass']);
	$uqa=mysql_query("SELECT * FROM admin_users as au JOIN admin_permissions as ap ON ap.user_id=au.admin_id WHERE username='$_SESSION[adminuser]' AND password='*$pass'");
	$una=mysql_num_rows($uqa);
	if($una>0){
		$uaa=mysql_fetch_assoc($uqa);
		mysql_query("UPDATE admin_users SET date_lastin='".date("U")."' WHERE admin_id='".$uaa['admin_id']."'");
		$_SESSION['aloggedin']=time();
		$_SESSION['timedout']=0;
		unset($_SESSION['query']);
	}
	else{$_SESSION['aloggedin']=0;}
}
if((!isset($_SESSION['aloggedin'])||$_SESSION['aloggedin']==0)&&basename($_SERVER['PHP_SELF'])=="admin.php"){
	unset($_SESSION['error']);
	$_SESSION['query'] = $_SERVER['QUERY_STRING'];//hold requested url to redirct after login
	header('Location: admin/login.php');
}
?>