<?
if(!in_array(basename($_SERVER['PHP_SELF']),array("index.php","auth.php","aauth.php","transactionRegister.php"))){die("Access Denied");}//direct access security
//if(isset($_COOKIE['email'])&&isset($_COOKIE['pass']))
$seid=session_id();
if(isset($_SESSION['epart1'])&&isset($_SESSION['pass']))
{
	if(isset($_SESSION['loggedin'])&&$_SESSION['loggedin']!=0&&time()-$_SESSION['loggedin']>$idletime){unset($_SESSION['pass']);unset($_SESSION['epart1']);unset($_SESSION['epart2']);}
	$join=$_SESSION['epart1'].'@'.$_SESSION['epart2'];
	$pass=hashandsalt($join,$_SESSION['pass']);
	$uq=mysql_query("SELECT * FROM (customers LEFT JOIN counties ON customers.state=counties.county_id) LEFT JOIN countries ON customers.country=countries.country_id WHERE email='$join' AND gpassword='$pass'");
	$un=mysql_num_rows($uq);
	if($un>0){$ua=mysql_fetch_assoc($uq);$_SESSION['loggedin']=time();}
	else{$_SESSION['loggedin']=0;}
	
}
?>