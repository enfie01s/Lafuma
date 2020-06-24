<?php 
function pprint_r($print)
{
	?><pre><? print_r($print)?></pre><?
}
date_default_timezone_set('Europe/London');
//error_reporting(E_ALL);
//ini_set('error_reporting', E_ALL);
include "content/config.php";
function ahmysql_real_extracted($inputarray)
{
	$extracted_array=array();
	foreach($inputarray as $field => $value)
	{
		$extracted_array[$field]=is_array($value)?ahmysql_real_extracted($value):mysql_real_escape_string($value);
	}
	return $extracted_array;
}
$postescaped=ahmysql_real_extracted($_POST);
$getescaped=ahmysql_real_extracted($_GET);
include "admin/aheaderfunctions.php";
$prods_per_page=30;//re define this var for admin
include "content/functions.php";
require "content/asession.php";
if(isset($_GET['logout'])){unset($_SESSION['adminpass']);unset($_SESSION['adminuser']);$_SESSION['aloggedin']=0;header("Location: $mainbase/admin/login.php");}

if(($page!="promotions"||!isset($action)||strlen($action)<1)&&isset($_SESSION['promotions'])){unset($_SESSION['promotions']);}
$title=ucwords($page).(isset($_GET['name'])?": ".ucwords($_GET['name']):"");
$o=mysql_query("SELECT `order_id` FROM orders WHERE `order_status`='New'");
$neworders=mysql_num_rows($o);
$q=mysql_query("SELECT `contactus_id` FROM contactus");
$pqueries=mysql_num_rows($q);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex, nofollow" />
<meta name="author" content="Lafuma UK" />
<meta name="google-site-verification" content="sesA5RpHgdlVBvVDFb8PFUlMj8-Yl0SNDwlxAUKrxKI" /><!--for Froogle-->
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="content/adminstyle.css" type="text/css" />
<link rel="stylesheet" href="content/print.css" type="text/css" media="print" />
<title>Lafuma UK ACP - <?=$title?></title>
<script type="text/javascript">
<!--
function decision(message, url){
if(confirm(message)) window.location.assign(url);
else return false;
}
// -->
</script>
<script type="text/javascript" src="<?=$mainbase?>/content/js/jquery.tools.min.js"></script>
<?
if(isset($_GET['p'])){$menu=$_GET['p'];}
else {$menu=$page;}
if($menu!=""){?><style type="text/css">
#left ul li#menu<?=$menu?> a{background:url(content/img/main/arrow2.gif) no-repeat scroll 8px 1em #DDD;}
#left ul li#menu<?=$menu?> a:hover {background:url(content/img/main/arrow2.gif) no-repeat scroll 8px 1em #E4E4E4;}
</style><? }
?>
</head>
<body>
<div id="outer">
	<div id="inner">
		<!--HEADER-->
		<div id="header">
		<div id="logo"><a href="<?=$mainbase?>"><img src="content/img/admin/logo.jpg" alt="Lafuma" /></a></div>
		<div id="headerinfo">
		Welcome to the Admin CP, <?=ucwords($uaa['username'])?><br />
		<?=$pqueries?> <a href="<?=$mainbase?>/admin.php?p=enquiries">Enquiries Pending</a><br />
		<?=$neworders?> <a href="<?=$mainbase?>/admin.php?p=invoices&amp;ssortby=invoice&amp;sstatus=New&amp;ssortdir=DESC">New Order<?=$neworders==1?"":"s"?></a>
		</div>
		</div>
		<!--/HEADER-->
		<!--MAIN-->
		<div id="mid">
		<? if(isset($_SESSION['aloggedin'])&&$_SESSION['aloggedin']>0){/*extra security if not logged in*/?>
			<!--LEFT SIDE-->
			<?php  
					
			$mods=explode(",",$uaa['permissions']);
			?>
			<div id="left">
				<h3 class="vert_title">&#10063; Editor</h3>
				<ul id="editor">
					<li id="menuhome"><a href="<?=$mainbase?>/admin/"><span>Admin Home</span></a></li>
					<li><a href="<?=$mainbase?>"><span>Preview Site</span></a></li>
					<li><a href="<?=$mainbase?>/admin.php?logout=1"><span>Admin Logout</span></a></li>
				</ul>
				
				<? 
				foreach($menusection as $sectname => $items)
				{
					if(count(array_intersect($mods,$items))>0)
					{
						?>
						<h3 class="vert_title">&#10063; <?=ucwords($sectname)?></h3>
						<ul id="<?=$sectname?>">
							<? foreach($items as $i)
							{
								if(in_array($i,$mods))
								{
									?>
									<li id="menu<?=$modules_pages[$i]?>"><a href="<?=$mainbase?>/admin.php?p=<?=$modules_pages[$i]?>"><span><?=$modules[$i]?></span></a></li>
									<? 
								}
							}?>
						</ul>
						<? 
					}
				}?>
				<h3 class="vert_title">&#10063; My Notes</h3>
				<ul id="mynotes">
					<li>
						<div style="text-align:center">
							<form action="<?=$_SERVER['PHP_SELF']."?".str_replace("&","&amp;",$_SERVER['QUERY_STRING'])?>" method="post">
							<input type="hidden" name="updatenotes" value="<?=$uaa['admin_id']?>" />
							<textarea name="notes" style="width:170px;height:80px" rows="5" cols="4"><?=$uaa['notes']?></textarea>
							<input type="submit" value="Update Notes" class="formbutton" />
							</form>
						</div>
					</li>
				</ul>
			</div>
			<!--/LEFT SIDE-->
			<!--RIGHT SIDE-->
			<div id="right">
				<? 
				$key = array_search($page, $modules_pages); 
				if(in_array($key,$mods)||in_array($page,array("home","images","help"))){
					include "admin/". $page . ".php";
				}else{
				?>
				<table cellpadding="0" cellspacing="1" class="details" style="width:300px;margin:50px auto 0px auto;">
				<tr>
					<td class="head">Message</td>
				</tr>
				<tr>
					<td>Sorry you are not authorized to view this module</td>
				</tr>
				</table>
				<?
				}
				/*
				<div id="bread">You are here: <a href="<?=$mainbase?>">Admin Home</a> &#187; Reports</div>
				<div id="main">
					<h2 id="pagetitle"><?=((isset($_GET['report']))?ucwords($_GET['report'])." Report":"Reports")?></h2>
					<div id="pagecontent">
						<div id="errorbox" style="<?=$errorboxdisplay?>"><p>Error</p><?=$errormsg?></div>
						<!-- CONTENT -->
						<!-- /CONTENT -->
					</div>
				</div>
				*/
				?>
			</div>
			<!--/RIGHT SIDE-->
			<div class="clear"><br /></div>
		<? }else{?>
		<p style="text-align:center">Please log in <a href="<?=$mainbase?>/admin/login.php">here</a>.</p>
		<? }?>
		</div>
		<!--/MAIN-->
		<!-- FOOTER -->
		
		<!--/FOOTER-->
	</div>
</div>

<script type="text/javascript">
	if($(":date")!==null){$(":date").dateinput({format: 'yyyy-mm-dd',selectors: true});}
</script>
</body>
</html>
<? /*if(isset($_GET['genfroogle'])&&$_GET['genfroogle']==1&&$islocal==0){include "admin/genfroogle.php";}*//*regenerate google products*/ ?>