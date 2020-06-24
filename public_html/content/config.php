<?php
if(!in_array(basename($_SERVER['PHP_SELF']),array("admin.php","auth.php","aauth.php","index.php","login.php","3DRedirect.php","3DCallBack.php","paypalCallback.php","notificationPage.php","transactionRegister.php"))){die("Access Denied");}//direct access security
$host=str_replace(array("http://","https://","www."),array("","",""),$_SERVER['HTTP_HOST']);
$inhouse=$host=="bhweb1"?1:0;
$domainext="uk";//default

$domainbits=explode(".",$host);
$last=count($domainbits)-1;
$domainext=$domainbits[$last]=="ie"?"ie":"uk";

if(isset($_COOKIE['lafumasite'])){$domainext=$_COOKIE['lafumasite'];}
session_name("Lafuma".$domainext);
session_start();
$ipcheckloc=in_array(basename($_SERVER['PHP_SELF']),array("auth.php","aauth.php","login.php","paypalCallback.php","notificationPage.php","transactionRegister.php"))?'../../ipcheck.php':'../ipcheck.php';
include($ipcheckloc);
$testing=0;//Site in test mode?
/* SAGEPAY CONFIG IN /sagepay/includes.php */

$admin_email="sales@llc-ltd.co.uk";
$sales_phone="01489 557 600";
$sales_fax="01489 557 705";
$cart_order_email="lafumaorders@llc-ltd.co.uk";
$froogle_serv="uploads.google.com";
$froogle_user="lafumauk";
$froogle_pass="lafumaHost1";
$vatreg="GB795030523";
$coreg="4379849";
$addy="LLC Ltd, Bear House, Concorde Way, Fareham, PO15 5RL";

$deadline=strtotime("12pm");
$idle_minutes=30;//site user inactivity
$aidle_minutes=60;//admin user inactivity

$per_row=4;//home page departments per row
$prods_per_page=10;//PAGINATION: how many products to show before splitting to next page
$maxpagelinks=5;//PAGINATION: how many page links to show (<back 12345 next>)

$latlng_daterenew=2;//renew google lat & lng if older than 2 days

$postal=array("Royal Mail","Parcelforce","DHL","Yorkshire Parcels","UPS","FedEx");
$postaltracking=array(
"Royal Mail"=>"http://www.royalmail.com/portal/rm/track?trackNumber=",
"Parcelforce"=>"http://www.parcelforce.com/portal/pw/track?trackNumber=",
"DHL"=>"http://www.dhl.co.uk/content/gb/en/express/tracking.shtml?brand=DHL&amp;AWB=",
"Yorkshire Parcels"=>"",
"UPS"=>"http://wwwapps.ups.com/WebTracking/processInputRequest?tracknum=",
"FedEx"=>"http://fedex.com/Tracking?action=track&amp;cntry_code=uk&amp;tracknumber_list="
);

/* IMAGE PATHS AND SIZES */
$images_arr=array(
	"product"=>array(
		"path"=>"content/img/products",/*base path*/
		"images"=>array(/*sub paths*/
			"small"=>"100x75",
			"medium"=>"220x165",
			"large"=>"780x585"
		)
	),
	"department"=>array(
		"path"=>"content/img/categories",
		"images"=>array(
			"main"=>"110x110"/*main is base path, not sub*/
		)
	),
	"product_options"=>array(
		"path"=>"content/img/products/options",
		"images"=>array(
			"main"=>"45x45",
			"small"=>"15x15"
		)
	)
);
$images_arr["option_values"]=$images_arr["product_options"];


include "vars.php";
$vat=$domainext=="uk"?20:20;//%
$days=array("monday","tuesday","wednesday","thursday","friday","saturday","sunday");
?>