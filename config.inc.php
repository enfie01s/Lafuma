<? 
$username = "lafuma";
$password = "password";
$database = "lafuma_main";
$host = "localhost";
$db=mysql_connect($host, $username, $password) or die(mysql_error()); 
@mysql_select_db("$database",$db) or die(mysql_error());
?>
