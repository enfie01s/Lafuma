<?php
if(!in_array(basename($_SERVER['PHP_SELF']),array("admin.php","auth.php","aauth.php","index.php","login.php","3DRedirect.php","3DCallBack.php","paypalCallback.php","notificationPage.php","transactionRegister.php"))){die("Access Denied");}//direct access security

$dbuser="lafuma";
$dbpass="Klsjfsd873sdd";
$dbname="lafuma_main";
$db=mysql_connect("localhost", $dbuser, $dbpass) or die(mysql_error());
@mysql_select_db($dbname,$db) or die(mysql_error());

/*SET GET VARS*/
$page = (isset($_GET['p'])&&file_exists(((basename($_SERVER['PHP_SELF'])=="admin.php")?"admin/":"").basename($_GET['p']).'.php')) ? basename($_GET['p']) : "home";
$higherr=array();//hold fields names here to highlight errors

$homebase=$inhouse?"bhweb1/lafuma/public_html":"www.".$host;
$securebase="http".($inhouse?"":"s")."://".$homebase;
$http=$_SERVER['SERVER_PORT']=="443"?"https://":"http://";
$mainbase=$http.$homebase;
$self=$mainbase."/admin.php?p=".$page;
$freepostsqlid=7;
if(!isset($_SESSION['test'])){$_SESSION['test']=0;}
if(isset($_GET['test123'])){$_SESSION['test']=$_GET['test123'];}

define("HOST",$host);
define("DOMAINEXT",$domainext);
define("WHICHLIST","on_".DOMAINEXT."_list");
define("EUROSON",(DOMAINEXT=="ie"?1:0));
define("PAYPALON",(DOMAINEXT=="ie"?0:1));
$cardtypes=array("paypal"=>"PayPal","cc"=>"Credit/Debit Card");

$currencylang=array(
"GBP"=>array("GBP","Pounds","&#163;","£",1,"Pound Sterling"),
"EUR"=>array("EUR","Euros","&#8364;","€",1.2504,"Europe Euros")
);
$currarr=array();
$currarr['uk']=$currencylang['GBP'];
if(EUROSON==1){$currarr['ie']=$currencylang['EUR'];}
else{$currarr['ie']=$currencylang['GBP'];}
$currkeys=array_keys($currarr);

$pricefield=DOMAINEXT=="ie"&&EUROSON==1?"_euro":"";
define("PRICECUR",$pricefield);
$whichcur=DOMAINEXT=="ie"&&EUROSON==1?"EUR":"GBP";
define("WHICHCUR",$whichcur);

$thepage=isset($_GET['p'])?$_GET['p']:"home";
$what=isset($_GET['what'])?$_GET['what']:"";
$action=isset($_GET['act'])?$_GET['act']:"";

if(isset($_GET['cid'])){$cp="cat_id";$cpid=$_GET['cid'];}
else if(isset($_GET['pid'])){$cp="prod_id";$cpid=$_GET['pid'];}
else if(isset($_GET['optval_id'])){$cp="optval_id";$cpid=$_GET['optval_id'];}

$cid=isset($_GET['cid'])?$_GET['cid']:"";
$pid=isset($_GET['pid'])?$_GET['pid']:"";
$fid=isset($_GET['fid'])?$_GET['fid']:"";
$custid=isset($_GET['cust_id'])?$_GET['cust_id']:"";

$invsort="";
if(isset($_GET['ssortby'])){$invsort.="&amp;ssortby=$_GET[ssortby]";}
if(isset($_GET['sstatus'])){$invsort.="&amp;sstatus=$_GET[sstatus]";}
if(isset($_GET['ssortdir'])){$invsort.="&amp;ssortdir=$_GET[ssortdir]";}
if(isset($_GET['from'])){$invsort.="&amp;from=$_GET[from]";}
if(isset($_GET['to'])){$invsort.="&amp;to=$_GET[to]";}

$founderrors="";
$douploads="";
/* /SET GET VARS*/

$bankhols=array(
"3-4-2015"=>"Good Friday",
"6-4-2015"=>"Easter Monday",
"4-5-2015"=>"Early May Bank Holiday",
"25-5-2015"=>"Spring Bank Holiday",
"31-8-2015"=>"Summer Bank Holiday",
"24-12-2015"=>"Christmas Shutdown",
"25-12-2015"=>"Christmas Day",
"28-12-2015"=>"Boxing Day (substitute)",
"29-12-2015"=>"Christmas Shutdown",
"30-12-2015"=>"Christmas Shutdown",
"31-12-2015"=>"Christmas Shutdown"
);
$modules=array(
"",
"Shop Builder",
"Product Options",
"Product Packages",
"Invoices",
"Customers",
"Enquiries",
"Promotions",
"Reports",
"Postage &amp; Packing",
"Administrators",
"Delete Voided",
"Reviews"
);
$modules_pages=array(
"",
"builder",
"product_options",
"packages",
"invoices",
"customers",
"enquiries",
"promotions",
"reports",
"postage",
"admins",
"voids",
"reviews"
);
$menusection=array("builder"=>array(1,2,3),"orders"=>array(4,5,6,12),"marketing"=>array(7,8),"admin"=>array(9,10,11));
$br=array("\r\n","<br />","\r\n");//opt out,html,plain line breaks
$contenttype=array("text/plain","text/html","text/plain");
$mailtype=array("None","HTML","Plain Text");
//if($islocal){ini_set("SMTP","smtp.murphx.net");ini_set("sendmail_from","senfield@gmk.co.uk");}/*testing email vars if on bhweb1*/
$crumbsep=" &#187; ";
$idletime=$idle_minutes*60;
$adminidletime=$aidle_minutes*60;
$date=date("U");
$alpha="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$prefixpath=($islocal)?"W:/Website/lafumaco":"/home/uk2bearholdcouk25295/lafumaco";
$prefixurl=($islocal)?"":$mainbase;
if($page!="builder"||!isset($_GET['act'])||(isset($_GET['act'])&&$_GET['act']!="attach_opts")){unset($_SESSION['cur_opts']);unset($_SESSION['avail_opts']);}
//sage tax code: T9
$basket="<a href='".$mainbase."/index.php?p=shopping_basket'>Shopping Basket</a>";
$emailereg = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$";
$titleback="<a href='javascript:history.go(-1);'><img src='$mainbase/content/img/main/go-back.gif' alt='&#171;' /></a>";
if((isset($_SESSION['VendorTxCode'])||isset($_GET['invoice']))&&in_array($page,array("3DComplete","receipt")))
{
	$where=(isset($_SESSION['VendorTxCode']))?"VendorTxCode='" . mysql_real_escape_string($_SESSION['VendorTxCode']) . "'":"invoice='$_GET[invoice]'";
	$invoiceq = mysql_query("SELECT * FROM orders WHERE $where")
		or die ("Query failed with error message: \"" . mysql_error () . '"');
	$invoice=mysql_fetch_array($invoiceq);
	if(isset($_GET['invoice'])){$_SESSION['invoice']=$invoice['invoice'];$_SESSION['date_ordered']=date("d/m/Y",$invoice['date_ordered']);}
}
if($thepage=="contact")
{
	require_once "content/recaptchalib.php";
	$publickey="6LdcLPASAAAAAFopaK-krPWQ5eRQ1HR22p5dKzdi";
	$privatekey="6LdcLPASAAAAAMJHBa-CqQJwVgyIEYNb1DNA6zWw";
}
if(isset($_POST['identifier'])&&$_POST['identifier']=="contactform"){
	$contact_errors=array();
	$resp = recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
	if (!$resp->is_valid)
	{
		array_push($contact_errors,"sCaptcha");
		
	}
	if(!eregi($emailereg, $_POST['sEmail']))
	{
		array_push($contact_errors,"sEmail");
	}
	foreach($_POST as $name => $value)
	{
		if(in_array($name,array("sEmail","sName","sComments"))&&strlen($value)<1)
		{
			array_push($contact_errors,$name);
		}
	}
}
$crumbarray=array(
	"home"						=>"",//<a href='javascript:history.go(-1);' title='Go back to the order review page'>
	"checkout_address"=>$basket.$crumbsep."Billing &amp; delivery address",
	"postage_choice"	=>$basket.$crumbsep."<a href='javascript:history.go(-1);' title='Go back to the address page'>Billing &amp; delivery address</a>".$crumbsep."Postage information",
	"customer_login"	=>((isset($_GET['resetpassform']))?"Change Password":"Customer Login"),
	"checkout_login"	=>"Login",
	"shopping_basket"	=>"Shopping Basket",
	"my_account"			=>((isset($_GET['updateform'])||isset($_GET['updatepassform']))?"<a href='".$mainbase."/index.php?p=my_account'>My Account</a>".$crumbsep.((isset($_GET['updateform']))?"Update Information":"Change Password"):((isset($_GET['registerform']))?"New Account":"My Account")),
	"frames_fabrics"	=>"Frames and Fabrics",
	"terms"						=>"Terms &amp; Conditions",
	"payment"					=>$basket.$crumbsep."<a href='javascript:history.go(-3);' title='Go back to the address page'>Billing &amp; delivery address</a>".$crumbsep."<a href='javascript:history.go(-2);' title='Go back to the postage options page'>Postage information</a>".$crumbsep."<a href='javascript:history.go(-1);' title='Go back to the order review page'>Review Order</a>".$crumbsep."Checkout",
	"review"					=>$basket.$crumbsep."<a href='javascript:history.go(-2);' title='Go back to the address page'>Billing &amp; delivery address</a>".$crumbsep."<a href='javascript:history.go(-1);' title='Go back to the postage options page'>Postage information</a>".$crumbsep."Review Order",
	"orderSuccessful"	=>"Order Processed",
	"receipt"					=>"View Receipt",
	"orderFailed"			=>"Order Failed",
	"3DComplete"			=>$basket.$crumbsep."<a href='javascript:history.go(-3);' title='Go back to the address page'>Billing &amp; delivery address</a>".$crumbsep."<a href='javascript:history.go(-2);' title='Go back to the postage options page'>Postage information</a>".$crumbsep."<a href='javascript:history.go(-1);' title='Go back to the order review page'>Review Order</a>".$crumbsep."Checkout",
	"accessibility"		=>"Accessibility",
	"privacy"					=>"Privacy",
	"returns"					=>"Returns",
	"dealers"					=>"Dealers",
	"contact"					=>((isset($_POST['identifier'])&&$_POST['identifier']=="contactform"&&count($contact_errors)<1)?"Thanks":"Contact Us"),
	"sitemap"					=>"Site Map",
	"warranty"        =>"Warranty",
	"trade"           =>"Trade Application"
	);
$pgheaderarray=array(
	"home"						=>"Lafuma UK - The Official Online Store",
	"checkout_address"=>"Billing &amp; delivery address",
	"postage_choice"	=>"Postage information",
	"customer_login"	=>((isset($_GET['resetpassform']))?"Change Password":"Customer Login"),
	"checkout_login"	=>((isset($_GET['lostpass']))?"Lost Password":"Checkout Login"),
	"shopping_basket"	=>"Shopping Basket",
	"my_account"			=>"My Account",
	"frames_fabrics"	=>"Frames and Fabrics",
	"terms"						=>"Terms &amp; Conditions",
	"payment"					=>"Checkout",
	"review"					=>"Review Order",
	"orderSuccessful"	=>"Order Processed - Complete",
	"receipt"					=>(((!isset($_SESSION["VendorTxCode"])||strlen($_SESSION["VendorTxCode"])==0)&&(!isset($_GET['invoice'])||(isset($_GET['invoice'])&&($_SESSION['loggedin']==0))))?"Expired Receipt":"Invoice Number ".(isset($_SESSION['invoice'])?$_SESSION['invoice']:"")." - Order Date ".(isset($_SESSION['date_ordered'])?$_SESSION['date_ordered']:"").""),
	"orderFailed"			=>"Invoice Number ".(isset($_SESSION['invoice'])?$_SESSION['invoice']:"")." - Order Date ".(isset($_SESSION['date_ordered'])?$_SESSION['date_ordered']:"")."",
	"3DComplete"			=>"Invoice Number ".(isset($invoice['invoice'])?$invoice['invoice']:"")." - Order Date ".(isset($invoice['date_ordered'])?date("d/m/Y",$invoice['date_ordered']):"")."",
	"accessibility"		=>"Accessibility Statement",
	"privacy"					=>"Privacy Policy",
	"returns"					=>"Returns Policy",
	"dealers"					=>"Local Stockists",
	"contact"					=>((isset($_POST['identifier'])&&$_POST['identifier']=="contactform"&&count($contact_errors)<1)?"Message Sent":"Contact Us"),
	"sitemap"					=>"Dynamic Site Map",
	"warranty"        =>"Warranty",
	"trade"           =>"Trade Application"
);
$extraimg=array();
/* CUSTOMER FORMS */
$errorlist=array();
$errorboxdisplay="display:none";
$requireds=array();
$requireds['admindoupdate']=array("firstname","lastname","email","address1","city","state","postcode","country");
$requireds['doupdate']=array("firstname","lastname","email","address1","city","state","postcode","country","phone");
$requireds['dopassupdate']=array("password1","password2");
$requireds['doregister']=array("password1","password2","firstname","lastname","email","address1","city","state","postcode","country","phone");
$requireds['lostpass']=array("email");
$requireds['dopassreset']=array("email","password1","password2");

$fieldtitles=array("password1"=>"Password","password2"=>"Confirm Password","firstname"=>"First Name","lastname"=>"Last Name","email"=>"Email","phone"=>"Phone","address1"=>"Address 1","address2"=>"Address 2","city"=>"City","state"=>"County/State","postcode"=>"Postcode/Zip","country"=>"Country","homepage"=>"Website","company"=>"Company","mailing"=>"Email List","deliver_firstname"=>"Delivery address - First Name","deliver_lastname"=>"Delivery address - Last Name","deliver_address1"=>"Delivery address - Address1","deliver_city"=>"Delivery address - City","deliver_state"=>"Delivery address - County/State","deliver_postcode"=>"Delivery address - Postcode/Zip","deliver_country"=>"Delivery address - Country","deliver_phone"=>"Delivery address - Phone");

$customerforms=array("doregister","doupdate","dopassupdate","dopassreset","lostpass");

$orderstatuses=array("New"=>"New","Pending"=>"Pending","Received"=>"Received","Backorder"=>"Backorder","Shipped"=>"Dispatched","Void"=>"Void");


$freepostq=mysql_query("SELECT `status`,pmd.`description` FROM postage_methods as pm JOIN postage_method_details as pmd ON pm.`post_id`=pmd.`post_id` WHERE pm.`post_id`='$freepostsqlid'");

/* SET DEFAULT POSTAGE */
$defaultdel=WHICHLIST=="on_uk_list"?"GB":"IE";
define("DEFAULTDEL",$defaultdel);
$defpostq=mysql_query("SELECT `post_details_id`,`description`,`field3".PRICECUR."` as field3 FROM postage_method_details WHERE `gdefault`='1' AND `availability` LIKE '%-".DEFAULTDEL."-%'");
list($defpostid,$defpostdesc,$defpostprice)=mysql_fetch_row($defpostq);
//echo "SELECT `post_details_id`,`description`,`field3".PRICECUR."` as field3 FROM postage_method_details WHERE `gdefault`='1' AND `availability` LIKE '%-".DEFAULTDEL."-%'";

$daysofweek=array("monday","tuesday","wednesday","thursday","friday","saturday","sunday");
$ranks=array("Unrated","Horrible","Poor","Fair","Good","Excellent");
?>