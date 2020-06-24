<?
function pprint_r($print)
{
	?><pre><? print_r($print)?></pre><?
}
date_default_timezone_set('Europe/London');
//todo: phone handset icon increase height
$basket_total=0;
$basket_qty=0;

$sub_total=0;
$discount=0;
$fusionOwn=0;
$totaldiscount=0;
$vattoadd=0;
include "content/config.php";
include('../ipcheck.php');
if(isset($_GET['setcountry']))
{
	setcookie('lafumasite',''.$_GET['setcountry'].'',time()+3600*24*365,'/',''.HOST.'');
	header("Location: index.php");	
}
include "content/functions.php";
include "content/Mobile_Detect.php";
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$_SESSION['dt']=isset($_GET['dt'])?$_GET['dt']:(isset($_SESSION['dt'])?$_SESSION['dt']:$deviceType);
$deviceType=$_SESSION['dt'];
//$deviceType = 'computer';
require "content/usession.php";
require "content/asession.php";
include "content/headerfunctions.php";
$_SESSION['pageloads']=!isset($_SESSION['pageloads'])||$_SESSION['pageloads']>=2?1:$_SESSION['pageloads']+1;

//if session == 11 and freepost == 0 change session
if($_SESSION['shipping']==$freepostsqlid&&$freepost==0){$_SESSION['shipping']=$defpostid;$_SESSION['postdesc']=$defpostdesc;}
$_SESSION['postdesc']=isset($_SESSION['postdesc'])?$_SESSION['postdesc']:($freepost==1?$freepostedsc:$defpostdesc);
$_SESSION['shipping']=isset($_SESSION['shipping'])?$_SESSION['shipping']:($freepost==1?$freepostsqlid:$defpostid);
$_SESSION['shipping_opt']=isset($_SESSION['shipping_opt'])?$_SESSION['shipping_opt']:'';
//echo $defpostid;

if(!isset($_SESSION['cart'])){$_SESSION['cart']=array();}
$cart_ids=array();
if(count($_SESSION['cart'])>0)
{
	$qqqqqq="<--- for Chris";
	foreach($_SESSION['cart'] as $id => $item)
	{
		$itemprice=$item['price']*$item['qty'];
		$itemdiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($item['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $item['exclude_discount']!=1?$itemprice:0;
		
		$basket_qty+=$item['qty'];
		$sub_total+=$itemprice;
		$totaldiscount+=$itemdiscount;//add up all items which are excluded from discount
		if(!in_array($item['prod_id'],$cart_ids)){array_push($cart_ids,$item['prod_id']);}
	}
	list($price,$postdesc,$postid)=postagecalc($sub_total,$_SESSION['shipping']);
	$defaultpostage=$basket_qty>0&&$_SESSION['shipping']>0?$price:0;
	
	$discount=isset($_SESSION['discount_amount'])&&$_SESSION['discount_amount']>0?(($totaldiscount)/100)*$_SESSION['discount_amount']:0;
	$total=$sub_total-$discount;
	$vattoadd=$vat*($total/100);//total vat for all items (including non discountable)
	$basket_total=$total+$vattoadd+$defaultpostage;//total, vat, postage cost and discount
	$totalforpostage=number_format($total+$vattoadd,2);
}

if(isset($_GET['logout'])){unset($_SESSION['pass']);unset($_SESSION['epart1']);unset($_SESSION['epart2']);$_SESSION['loggedin']=0;if($page=="checkout_login"){header("Location: $mainbase/index.php?p=checkout_login");}else{header("Location: $mainbase");}}
if(isset($_GET['pid'])||isset($_GET['cat'])&&$page!="sitemap")
{
	$query_string=(isset($_GET['pid']))?"SELECT `prod_id`,products.`title` as title,`seo_title`,`shortdesc`,`metatitle`,`metadesc`,`content`,`img_filename`,`sku`,`list_price".PRICECUR."` as list_price,`price".PRICECUR."` as price,`item_weight`,`exclude_discount`,`discount`,`sale`,`kit`,`fusionId`,`iOwner_FK`,`iSubId_FK`, `vtype`,`iSort`,`iState`,`vOwnerType`,MAX(`rank`) as maxrank,MIN(`rank`) as minrank,AVG(`rank`) as avgrank,count(`cust_rev_id`) as countrevs,`allowoffer` FROM (products JOIN fusion ON fusion.`iSubId_FK`=products.`prod_id` AND `vType`='product' AND (`iState`='1' OR `allowoffer`='1')) LEFT JOIN customerreviews as cr ON cr.`item_id`=products.`prod_id` WHERE `fusionId`='$_GET[pid]' GROUP BY `prod_id`":"SELECT * FROM categories,fusion WHERE `cat_id`='$_GET[cat]' AND fusion.`iSubId_FK`=categories.`cat_id` AND `vType`='department'";
	$the_query=mysql_query($query_string,$db) or die(mysql_error());
	$the_array=mysql_fetch_assoc($the_query);//array for product pages
	$allowlist=array();
	if($the_array['iState']==0&&$the_array['allowoffer']==1)
	{
		
		//allow list
		$getallowedq=mysql_query("SELECT * FROM fusion WHERE `iSubId_FK`='".$the_array['prod_id']."' AND `vOwnerType`='product'");
		while($getallowed=mysql_fetch_assoc($getallowedq))
		{
			if(!in_array($getallowed['iOwner_FK'],$allowlist)){array_push($allowlist,$getallowed['iOwner_FK']);}
			//if($getallowed['iState']==1){$allowlist=array();break;}
		}
	}
	if($the_array['iState']==1||(count(array_intersect($allowlist,$cart_ids))>0&&$the_array['allowoffer']==1))
	{
		$title=$the_array['title'];
	}
	else
	{
		$title="Product Unavailable";
	}
	$crumbtitle=$crumbsep.$title;
	$pagetitle=$title;
}
else if($page!="search")
{
	$title="The Official Online Store";
	$thepage=ucwords(str_replace("_"," ",$page));
	$crumbtitle=(!isset($crumbarray[$page])||$page=="home")?"":$crumbsep.$crumbarray[$page];
	$pagetitle=(!isset($pgheaderarray[$page]))?$pgheaderarray["home"]:$pgheaderarray[$page];
}
else
{
	$title="The Official Online Store";
	$thepage=ucwords(str_replace("_"," ",$page));
	$crumbtitle=" $crumbsep Search Results";
	$searchq=mysql_query("
	(SELECT p.`title` as title,p.`shortdesc` as tdesc,f.`vtype` as ftype,f.`fusionId` as fid,f.`iOwner_FK` as iown  
	FROM products as p JOIN fusion as f ON p.`prod_id`=f.`iSubId_FK` AND f.`vtype`='product' AND f.`iState`='1' 
	WHERE `".WHICHLIST."` = '1' AND (p.`title` LIKE '%$_POST[searchall]%' OR p.`shortdesc` LIKE '%$_POST[searchall]%' OR p.`content` LIKE '%$_POST[searchall]%' OR p.`sku`='%$_POST[searchall]%') 
	GROUP BY p.`prod_id`) 
	UNION all 
	(SELECT c.`title` as title,`vSeoTitle` as tdesc,f.`vtype` as ftype,c.`cat_id` as fid,f.`iOwner_FK` as iown  
	FROM categories as c JOIN fusion as f ON c.`cat_id`=f.`iSubId_FK` AND f.`vtype`='department' AND f.`iState`='1' 
	WHERE c.`title` LIKE '%$_POST[searchall]%' OR c.`content` LIKE '%$_POST[searchall]%' 
	GROUP BY c.`cat_id`) 
	ORDER BY `ftype`,`iown`
	",$db);
	$searchrows=mysql_num_rows($searchq);
	$pagetitle=$searchrows>0?$searchrows." Records Found":"Sorry, your search returned no results, please try again";
}
$submit = trim(htmlspecialchars($_GET['submit']));
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="description" content="Official Lafuma UK Website. Shop online for Lafuma Garden Furniture, Camping Furniture, Chairs, Sunbeds, Recliners, Relaxers, Camping Tables, Loungers and Lafuma RSX Chairs." />
<meta name="keywords" content="lafuma uk, lafuma rsx, lafuma relaxer, lafuma recliner, lafuma Chairs, rsx, recliner, relaxer, garden furniture, camping gurniture, camping chair, camping table, lounger, sunbed, garden, lafuma, rsxa" />
<meta name="robots" content="all" />
<meta name="Revisit-After" content="7 days" />
<meta name="author" content="Lafuma UK" />
<meta name="google-site-verification" content="sesA5RpHgdlVBvVDFb8PFUlMj8-Yl0SNDwlxAUKrxKI" /><!--for Froogle-->

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="content/style<?=$deviceType=="phone"?"_phone":""?>.css" type="text/css" />
<link rel="stylesheet" href="content/print.css" type="text/css" media="print" />
<title><?="Lafuma UK - ".$title?></title>
<script src="content/js/thumbnailviewer.js" type="text/javascript"></script>
<? if($page=="payment"){?><script src="content/js/validate.js" type="text/javascript"></script><? }
if($menu!=""){?>
	<style type="text/css">
	#left ul li#menu<?=$menu?> a{background:url(content/img/main/arrow2.gif) no-repeat scroll 8px 1em #FFFFFF;}
	</style>
<? }?>
<? if(($page=="dealers"||$page=="map")&&$submit=="submitted"){?>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&amp;region=GB"></script>
<script type="text/javascript">
/* <![CDATA[ */
var map;
var markersArray = [];
var dealers=[];
function initialize() {
  var mapOptions = {
    zoom: 8,
    center: curCounty,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map =  new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
	showOverlays();
}

function addMarker(location,image,titlename,infocontent) {
  location = new google.maps.Marker({
    position: location,
    map: map,
		icon: image,
		title: titlename
  });
	google.maps.event.addListener(location, 'click', function(event) {
		infowindow.setContent(infocontent);
		//infowindow.setPosition();
		infowindow.open(map,location);
	});
	markersArray.push(location);
}

var infowindow = new google.maps.InfoWindow();

// Shows any overlays currently in the array
function showOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(map);
    }
  }
}
/* ]]> */
</script>
<? }?>
<script type="text/javascript">
//<{CDATA[
function decision(message){
if(confirm(message)) return true;
else return false;
}
//]]>
</script>
<script type="text/javascript" src="<?=$mainbase?>/content/js/jquery.tools.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" /> 
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
</head>
<body <? if(($page=="dealers"||$page=="map")&&$submit=="submitted"){?>onload="initialize()"<? }?>>

<script type="text/javascript">
		function screenRot(){
			document.location = '<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>';
		}
		document.body.addEventListener('orientationchange', screenRot );
		var menuState=0;
		function phonemenu(){
			var sideMenu=document.getElementById('sidemenu');
			var menButt=document.getElementById('menubutt');
			if(menButt.innerHTML=="SHOW MENU")
			{
				sideMenu.style.display="block";
				menButt.innerHTML="HIDE MENU";	
			}
			else
			{
				sideMenu.style.display="none";
				menButt.innerHTML="SHOW MENU";	
			}
		}
</script>
<?
$freedeq=mysql_query("SELECT `field1` FROM postage_method_details WHERE `field1`>'0' AND `field3`='0' AND `availability` LIKE '%-".DEFAULTDEL."-%'");
list($ordersover)=mysql_fetch_row($freedeq);
ob_flush();
ob_start();
?>
<div id="freeship"><strong <?=$deviceType=="phone"?($domainext=="uk"?"style='margin-top:14px;'":"style='margin-top:8px;'"):""?>>FREE <?=$domainext=="ie"?"UK ":""?>SHIPPING AVAILABLE</strong>ON ALL ORDERS OVER <?=$currarr[$domainext][2]?><?=$domainext=="ie"?"60":number_format(($ordersover+0.01),0)?></div><div style="float:left;margin-left:13px;"><a href="index.php?p=products&amp;pid=474&amp;prodname=lafuma%20futura%20air%20comfort" title="Air Comfort"><img src="./content/img/main/top1.jpg" alt="" /></a></div><div style="float:left;margin-left:13px"><a href="index.php?p=products&amp;pid=745&amp;prodname=lafuma%20r%20clip%20recliner" title="R Clip Recliner"><img src="./content/img/main/top2.jpg" alt="" /></a></div>
<?
$hometop=ob_get_clean();
ob_flush();
ob_start();
?>
<!-- http://www.LiveZilla.net Text Chat Link Code -->
<? 
if($deviceType!="phone"){?><a href="javascript:void(window.open('http://www.llc-ltd.co.uk/chat/chat.php?acid=ac8dd','','width=590,height=760,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))"><? if(is_file("https://www.llc-ltd.co.uk/chat/image.php?acid=1b923&amp;id=5&amp;type=inlay")||$http=="http://"){?>Live Support<? }else{?>Live Support<? }?></a> <? }else{?>
	<a href="http://www.llc-ltd.co.uk/chat/chat.php?site=lafuma" target="_blank">Live Support</a>
<? }
$livezillalink=ob_get_clean();
?>
<div id="fb-root"></div>
<div id="outer">
	<div id="inner">
		<?
		if(!isset($_COOKIE['lafumasite']))
		{
			?>
			<div id="flags">
			<h2 style="text-align:center">Please select your country.</h2>
			<div><a href="<?=$inhouse==1?"":"http://www.lafuma.co.uk/"?>index.php?setcountry=uk"><img src="./content/img/main/flags_large_uk.jpg" alt="UK" /><br />United Kingdom</a></div>
			<div><a href="<?=$inhouse==1?"":"http://www.lafumafurniture.ie/"?>index.php?setcountry=ie"><img src="./content/img/main/flags_large_ie.jpg" alt="IE" /><br />Ireland</a></div>
			</div>		
			<?
		}
		?>
		<!--HEADER-->
		<div id="header"><div style="text-align:right"><a href="<?=$inhouse==1?"":"http://www.lafuma.co.uk/"?>index.php?setcountry=uk" title="Lafuma UK"><img src="./content/img/main/flag_small_uk.jpg" alt="UK" style="vertical-align:top;" /></a><img src="./content/img/main/spacer.gif" alt="" style="width:<?=$deviceType=="phone"?"5":"10"?>px;height:2px" /><a href="<?=$inhouse==1?"":"http://www.lafumafurniture.ie/"?>index.php?setcountry=ie" title="Lafuma Ireland"><img src="./content/img/main/flag_small_ie.jpg" alt="IE" style="vertical-align:top;" /></a></div>
			<div id="logo"><a href="<?=$mainbase?>"><img src="./content/img/main/logo.jpg" alt="Lafuma" /></a></div>
			<div id="headerlinks"><? if($_SESSION['loggedin']==0){?><a href="<?=$mainbase?>/index.php?p=customer_login">Sign In / Register</a><? }else{?>Welcome, <?=$ua['firstname']?> <a href="<?=$mainbase?>/index.php?logout=1">(Logout)</a><? }?>&#160;&#160;&#160;&#183;&#160;&#160;&#160;
			<?=$livezillalink?>
			<!-- http://www.LiveZilla.net Tracking Code -->
			<!-- for om support <a href="http://86.188.176.163:14220/LiveSupport/Default.aspx?from=<?=$mainbase?>/index.php&amp;d=Lafuma%20Visitors,Lafuma%20Sales" target="_blank">Live Support</a>-->
&#160;&#160;&#160;&#183;&#160;&#160;&#160;<img src="content/img/main/white-phone.jpg" alt="" /> 01489 557 600</div>
			<form id="search" method="post" name="search" action="<?=$mainbase?>/index.php?p=search">
			<? whiteboxSTART(150,13);?><div style="float:left;height:13px;margin:0;padding:0;"><input type="text" name="searchall" id="searchkeyword" onFocus="if(this.value=='Search')this.value='';" onBlur="if(this.value=='')this.value='Search';" value="Search" maxlength="50" /></div><div style="float:right;height:13px;margin:0;padding:0;"><input type="image" id="searchsubmit" src="content/img/main/search-icon.jpg" alt="GO" /></div><div class="clear"></div><? whiteboxEND(150,13);?>
			</form>
			<div id="linkstrip">
				<ul style="border-top:1px solid #464646;	border-right:1px solid #464646;">
					<li><a href="index.php">Home</a></li><li><a class="hide" href="#" style="border-left: 1px solid #464646 !important;
  border-right: 1px solid #000000 !important;">Information</a><ul><li><a href="<?=$mainbase?>/index.php?p=whylafuma">Why Lafuma?</a></li><li><a href="<?=$mainbase?>/index.php?p=frames_fabrics">Frames &amp; Fabrics</a></li><li><a href="<?=$mainbase?>/index.php?p=history">History</a></li><li><a href="<?=$mainbase?>/index.php?p=faq">FAQ</a></li></ul></li><li><a href="<?=$mainbase?>/index.php?p=dealers">Local Stockists</a></li><li><a href="<?=$mainbase?>/content/Lafuma-Product-list.pdf" target="_blank">Compare</a></li><li><a href="<?=$inhouse?$mainbase:str_replace("http://","https://",$mainbase)?>/index.php?p=trade">Trade Enquiry</a></li><li><a href="<?=$mainbase?>/index.php?p=warranty">Warranty</a></li><li><a href="<?=$mainbase?>/index.php?p=contact">Contact Us</a></li>
				</ul>
			</div>
			<div id="bread">You are here: <a href="<?=$mainbase?>">Home</a><?=(isset($the_array)&&$the_array['iOwner_FK']!=0&&$the_array['vOwnerType']=='department'?getcrumbs($the_array['iOwner_FK']):"")?><?=$crumbtitle?></div>
		</div>
		<!--/HEADER-->
		<!--MAIN-->
		<div id="mid">
			<!--LEFT SIDE-->
			<?php 
			if(isset($_GET['cat'])){$menu=$_GET['cat'];}
			else {$menu=$page;}
			?><?=$deviceType=="phone"?$hometop:""?>
			<div id="left" style=" <?=$deviceType=="phone"?($page=="home"?"width:100%;":"display:none;"):""?>">
				
				<div id="leftmenu">					
					<div style="margin-top:10px;padding:0 10px;">
					<!-- http://www.LiveZilla.net Chat Button Link Code -->
					<? if($deviceType!="phone"){?><a href="javascript:void(window.open('http://www.llc-ltd.co.uk/chat/chat.php?acid=ac8dd','','width=590,height=760,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))"><? if(is_file("https://www.llc-ltd.co.uk/chat/image.php?acid=1b923&amp;id=5&amp;type=inlay")||$http=="http://"){?><img src="<?=$http?>www.llc-ltd.co.uk/chat/image.php?acid=1b923&amp;id=5&amp;type=inlay" alt="Live Support" /><? }else{?><img src="content/img/main/support.png" alt="Live Support" /><? }?></a>
					<? }?>				
					<!-- http://www.LiveZilla.net Chat Button Link Code -->
					
					<!-- Outlook Messenger Support -->
					<!--http://86.188.176.163:14220/LiveSupport/Default.aspx?from=<?//=$mainbase?>/index.php&amp;d=Lafuma%20Visitors,Lafuma%20Sales-->
					<?
					/* 
					if($http=="http://")
					{
						?>
						<script src='http://86.188.176.163:14220/LiveSupport/Scripts/om.livesupport.js' type='text/javascript'></script>
						<script type='text/javascript'>
						var om_url = 'http://86.188.176.163:14220/LiveSupport';
						var om_d = 'Lafuma Visitors,Lafuma Sales';
						</script>
						<img src='http://86.188.176.163:14220/LiveSupport/LiveSupport.aspx' alt='LiveSupport' onerror='this.style.display="none"' onclick='javascript:omlivesupport.open(document.location);' style='cursor:pointer;' />
						<? 
					}
					else
					{
						?>
						<a href="http://86.188.176.163:14220/LiveSupport/Default.aspx?from=<?=$mainbase?>/index.php&amp;d=Lafuma%20Visitors,Lafuma%20Sales" target="_blank"><img src="content/img/main/support.jpg" alt="Live Support" /></a>
						<?
					}*/
					?>
					<!-- Outlook Messenger Support -->
					</div>
					<? if(date('U')<strtotime("7 March 2014")){?>
					<div style="margin:10px;background:#e4e4e4;border:1px solid #666;">
					<a href="Vacancy.pdf" target="_blank" style="display:block;color:#333;font-weight:bold;padding:5px;">Job Vacancy</a>
					</div>
					<? }?>
					<? if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0){?>
					<h3 class="vert_title">Admin Menu</h3>
					<ul id="admin">
						<li id="menuadmin"><a href="admin.php" target="_blank"><span>Load Admin</span></a></li>
						<li id="menuadmin1"><a href="admin.php?logout=1" target="_blank"><span>Admin Logout</span></a></li>
						<li id="menuadmin2"><a href="javascript:history.go(-1)"><span>Previous Page</span></a></li>
						<? if($page=="products"||$page=="home"){?><li id="menuadmin3"><a href="<?=$mainbase?>/admin.php?p=builder&amp;act=attach&amp;what=<?=((isset($_GET['cat']))?"department":"product")?>&amp;id=<?=$the_array['fusionId']?>&amp;name=<?=urlencode($pagetitle)?>&amp;nextsort=0" target="_blank"><span>Associate Product</span></a></li><? }
						 
						if(isset($_GET['cat'])){$editurl="department&amp;id=".(($the_array['iOwner_FK']>0)?theparent($the_array['fusionId']):"0")."&amp;cid=$_GET[cat]";}
						else{$editurl="product&amp;id=".theparent($_GET['pid'])."&amp;pid=$the_array[iSubId_FK]";}
						
						if(isset($_GET['cat'])||isset($_GET['pid'])){?>
						<li id="menuadmin4"><a href="<?=$mainbase?>/admin.php?p=builder&amp;act=update&amp;what=department&amp;onitem=<?=$editurl?>" target="_blank"><span>Edit This Page</span></a></li><? }?>
					</ul>
					<? }?>
					<h3 class="vert_title" <? if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0){?>style="margin-top:10px"<? }?>><a href="<?=$mainbase?>/index.php?p=products&amp;cat=10" style="color:#cd071e;font-weight:normal;">Special Offers</a></h3>
					<h3 class="vert_title">Shop by Department</h3>
					<ul id="products">
						<? 
						$cats_query=mysql_query("SELECT * FROM fusion as f,categories as c WHERE f.`iSubId_FK`=c.`cat_id` AND `iOwner_FK`='0' AND `iState`='1' AND `displayed`='1' AND `vType`='department' AND `cat_id`!='10' ORDER BY `iSort`",$db)or die(mysql_error());
						while($cat=mysql_fetch_assoc($cats_query)){?>
						<li id="menu<?=$cat['cat_id']?>"><a href="<?=$mainbase?>/index.php?p=products&amp;cat=<?=$cat['cat_id']?>&amp;catname=<?=urlencode(strtolower(str_replace(" ","_",$cat['title'])))?>"><span><?=$cat['title']?></span></a></li>
						<? }?>
					</ul>
					<h3 class="vert_title">My Tools</h3>
					<ul id="tools">
						<li id="menumy_account">
						<?=($_SESSION['loggedin']==0)?"<a href='$mainbase/index.php?p=customer_login'>":"<a href='$mainbase/index.php?p=my_account'>"?><span>My Account</span></a></li>
						<li id="menushopping_basket"><a href="<?=$mainbase?>/index.php?p=shopping_basket"><span>View Basket/Checkout</span></a></li>
						<? if($_SESSION['loggedin']==0){?>
							<li id="menucustomer_login"><a href="<?=$mainbase?>/index.php?p=customer_login"><span>Sign in/Register</span></a></li>
						<? }else{?>
							<li><a href="<?=$mainbase?>/index.php?logout=1"><span>Logout</span></a></li>
						<? }?>
					</ul>
					<h3 class="vert_title">Information</h3>
					<ul id="information">
						<li><a href="<?=$mainbase?>/index.php?p=whylafuma" target="_blank"><span>Why Lafuma?</span></a></li>
						<li id="menuframes_and_fabrics"><a href="<?=$mainbase?>/index.php?p=frames_fabrics"><span>Frames and Fabrics</span></a></li>
						<li><a href="<?=$mainbase?>/content/Lafuma-Product-list.pdf" target="_blank"><span>Product Comparison</span></a></li>
						<li><a href="<?=$mainbase?>/index.php?p=faq" target="_blank"><span>FAQ</span></a></li>
					</ul>
				</div>
				<div style="margin-top:6px"><img src="content/img/main/mastercard.gif" alt="Mastercard" style="height:23px" /> <img src="content/img/main/visa.gif" alt="Visa" style="height:23px" /> <img src="content/img/main/maestro.gif" alt="Maestro" style="height:23px" /> <img src="content/img/main/solo.gif" alt="Solo" style="height:23px" /><? if(PAYPALON==1){?> <img src="content/img/main/paypal.gif" alt="PayPal" style="height:23px" /><? }?></div>
				<div style="margin-top:0px"><a href="http://www.llcliving.co.uk"><img src="content/img/main/llclivinglogo.jpg" alt="Part of LLC Living" style="border:1px solid #D6D6D6" /></a><hr style="width:170px;" /></div>
			</div>
			<!--/LEFT SIDE-->
			<!--RIGHT SIDE-->
			<div id="right">
				
				<div id="main">
					<!--<h2 id="pagetitle"><?=$pagetitle?></h2>-->
					<div id="pagecontent">
					<? if(isset($_SESSION['error'])&&((is_array($_SESSION['error'])&&array_key_exists($the_array['prod_id'],$_SESSION['error']))||!is_array($_SESSION['error'])))
					{ 
						if(is_array($_SESSION['error'])&&array_key_exists($the_array['prod_id'],$_SESSION['error']))//for adding prods to cart
						{
							$errormsg.=$_SESSION['error'][$the_array['prod_id']];
							$errorboxdisplay="display:block;";unset($_SESSION['error']);
						}
						else if(!is_array($_SESSION['error']))
						{
							$errormsg.=$_SESSION['error'];
							$errorboxdisplay="display:block;";unset($_SESSION['error']);
						}
					}?>
						<div id="errorbox" style="<?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div>
						<? include $page . ".php";?>
					</div>
				</div>
			</div>
			<!--/RIGHT SIDE-->
			<div class="clear"></div>
		</div>
		<!--/MAIN-->
		<!-- FOOTER -->
		<!-- AddThis Follow BEGIN -->
<div class="addthis_toolbox addthis_32x32_style addthis_default_style" style="float:left;margin:20px 2px 5px;">
<a class="addthis_button_facebook_follow" addthis:userid="lafuma.uk"></a>
<a class="addthis_button_twitter_follow" addthis:userid="LafumaUK"></a>
<a class="addthis_button_pinterest_follow" addthis:userid="lafumauk"></a>
</div>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-52ea738a42c02bb8"></script>
<!-- AddThis Follow END -->
		<div id="footer">
			<ul>
				<li><a href="<?=$mainbase?>/index.php?p=accessibility">Accessibility Statement</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=terms">Terms &amp; Conditions</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=privacy">Privacy Policy</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=returns">Returns Policy</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=buyguide">Buying Guide</a></li>
				<? if($deviceType=="phone"){?>
				<li><a href="<?=$mainbase?>/index.php?p=history">History</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=trade">Trade Enquiry</a></li>				
				<? }?>
				<li><a href="<?=$mainbase?>/index.php?p=dealers">Local Stockists</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=contact">Contact Us</a></li>
				<li><a href="<?=$mainbase?>/index.php?p=sitemap">Site Map</a></li>
			</ul>			
		</div>	
<!--		<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
		<div style="float:left;width:68px;position:relative;top:3px;left:0;"><a href="https://twitter.com/LafumaUK" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @LafumaUK</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
<div style="position:relative;top:3px;left:0;" class="fb-like" data-href="https://www.facebook.com/lafuma.uk" data-send="false" data-layout="button_count" data-width="300" data-show-faces="false"></div>-->




<div style="clear:both;"></div>
		<!--/FOOTER-->
		<div id="info">
			<div id="basketimg"><a href="<?=$mainbase?>/index.php?p=shopping_basket"><img src="content/img/main/basket.jpg" alt="" /></a></div>
			<div id="basketinfo"><span>Items in Basket<br /></span><div id="btotal"><?=$basket_qty?> Total: <?=$currarr[$domainext][2]?><?=number_format($basket_total,2)?></div><div id="blink"><a href="<?=$mainbase?>/index.php?p=shopping_basket" style="color:#aaa;font-size:110%">View Basket</a></div></div><?=$deviceType=="phone"?"<div style='float:right;position:relative;top:-2px;'>".$sales_phone."</div>":""?><div class="clear"></div>
		</div>
		<? 
		/* CART */
		$countcart=count($_SESSION['cart']);
		if(isset($_SESSION['cart'])&&$countcart>0){
		?>
		<style>
		#basketsummary_contents{		
			-webkit-transition-duration:<?=$countcart*0.5?>s;
			transition-duration:<?=$countcart*0.5?>s;
		}
		#basketsummary:hover #basketsummary_contents{height:<?=($countcart*38)+23?>px;}
		</style>
		<!-- this is at the bottom to catch new total without needing JS-->
		<div id="basketsummary">
		<a style="height:64px;width:163px;z-index:100;display:block;position:absolute;top:0;right:0;" href="<?=$mainbase?>/index.php?p=shopping_basket"><img src="content/img/main/spacer.gif" alt="" style="width:163px;height:64px;" /></a>
			<div id="basketsummary_contents">
				<div id="basketsummary_head">
					<p style="width:30px;">QTY</p>
					<p>Product</p>
				</div>
				<? 
				foreach($_SESSION['cart'] as $id => $cart)
				{
					$skuvars="";
					foreach($cart['skuvariant'] as $ident => $newsku)
					{
						$expsku=explode("-qty-",$newsku);
						$skuvars.=(($skuvars!="")?",":"")."'".$expsku[0]."'";
					}
					$query="SELECT * FROM products as p LEFT JOIN fusion as f ON f.`iSubId_FK`=p.`prod_id` AND `vtype`='product' WHERE p.`prod_id`='$cart[prod_id]' AND `".WHICHLIST."` = '1'";
					$prodinfoq=mysql_query($query,$db);
					$prodinfo=mysql_fetch_assoc($prodinfoq);
					$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
					?>
					<div class="basketsummary_<?=$row_class?>">
						<div style="width:30px;font-size:22px;"><?=$cart['qty']?></div>
						<div style="line-height:14px;">
							<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prodinfo['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prodinfo['seo_title']))?>"<? if(isset($_SESSION['added'])&&$_SESSION['added'][0]==$id&&$_SESSION['added'][1]=='new'){?> style="color:#CD071E;"<? }?>><?=$prodinfo['title']." (".$prodinfo['sku'].")"?></a>
							<? $choice=variants($skuvars);?>
							<br /><?=((is_array($choice))?ucwords($choice['description']).": ".$choice['item_desc']:"")?>
						</div>
					</div>
					<?
				}
				?>				
			</div>
		</div>
		<? }?>
	</div>
</div>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-1536461-3', 'auto');
  ga('send', 'pageview');

</script>

<script type="text/javascript" src="content/js/common.js"></script>

<? if($_SESSION['test']==1){?><div style="position:fixed;bottom:0;left:0;width:100%;background:red;color:white;font-weight:bold;z-index:1000;text-align:center">...TESTING - TESTING - TESTING - TESTING... <?=isset($strConnectTo)?"<<< ".$strConnectTo." >>>":""?> ...TESTING - TESTING - TESTING - TESTING...</div><? } ?>
</body>
</html>
