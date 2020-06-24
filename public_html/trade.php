<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
if(!isset($_SESSION['test'])){$_SESSION['test']=0;}
if(isset($_GET['test123'])){$_SESSION['test']=$_GET['test123'];}
$req=array();
$req['sname']="";
$req['address']="";
$req['cname']="";
$req['tel']="";
$req['email']="";
$req['type']="";
$req['outlet']="";
$sitename="Lafuma UK";
$random_hash = md5(date('r', time()));
$linechars=70;
$to=array("kslatter@gmk.co.uk","warrent@gmk.co.uk");
//$to=array("senfield@gmk.co.uk","senfield@llc-ltd.co.uk");
$tostr=count($to)>0?implode(",",$to):"";
if(isset($_POST['sname']))
{
	/* Error testing */
	$err=array();
	foreach($req as $f => $v)
	{
		if(array_key_exists($f,$_POST))
		{
			if(!is_array($_POST[$f]))
			{
				if(strlen($_POST[$f])<1){$err[$f]="Please fill in this field";}/* empty */
				else if($f=="email"&&!preg_match("/([\w-\.]+)@((?:[\w]+\.)+)([a-zA-Z]{2,4})/",$_POST[$f])){$err[$f]="Please enter a valid email address (eg: user@web.com)";}/* email */				
				if(array_key_exists($f,$err)){$req[$f]="border:1px solid red;";}
			}
		}	
		else
		{
			$err[$f]="Please tick at least one box.";/* checkboxes */	
			$req[$f]="border:1px solid red;";
		}	
	}
	/* /Error testing */
	if(count($err)==0)
	{
		ob_end_flush();
		ob_start(); //Turn on output buffering - no html structuring of the output below!!
		?>

Thank you for your interest in an account with <?=$sitename?>. Please see below a summary of your enquiry.

Shop Name: <?=$_POST['sname']?>
Shop Address: <?=$_POST['address']?>
Website Address: <?=!isset($_POST['url'])||strlen($_POST['url'])<1?"Not entered":$_POST['url']?>
Contact Name:  <?=$_POST['cname']?>
Contact Number (Main): <?=$_POST['tel']?>
Contact Number (Mobile): <?=!isset($_POST['mob'])||strlen($_POST['mob'])<1?"Not entered":$_POST['mob']?>
Contact Number (Fax): <?=!isset($_POST['fax'])||strlen($_POST['fax'])<1?"Not entered":$_POST['fax']?>
Email Address: <?=$_POST['email']?>
Shop Type: <?=implode(",",$_POST['type'])?>
Outlet: <?=implode(",",$_POST['outlet'])?>

<? 
$plain = ob_get_clean();/* plain text */ 
$mainwid='100%';
ob_end_flush();
ob_start();
?>
<html>
<head>
<style type="text/css">
@media print {
	body{
		background: none repeat scroll 0 0 #FFF;
		color: #000;
		font-family: Arial,Helvetica,sans-serif;
		margin:0 20px;
	}
	a {text-decoration:none}
	body, table, td, div{font-size: 7pt;}
	.question {font-weight:bold;font-size:5pt;color:#000;}
	.tickboxes {font-size:5pt;color:#000;padding:1px;text-align:center}
	.answer {font-size:4pt;border-bottom:1px solid #000;}
	.answer a{font-size:4pt;font-weight:normal;color:#555}
	table{border-collapse:separate;margin:auto}
	img{width:50px}
	h1{font-size:7pt;}
	hr{border-collapse:collapse;background:#000;border:1px solid #000}
}
@media screen {
	body{
		background: none repeat scroll 0 0 #FFF;
		color: #000;
		font-family: Arial,Helvetica,sans-serif;
		margin:10px;
	}
	body, table, td, div{font-size: 12px;}
	.question {font-weight:bold;font-size:13px;color:#000;}
	.tickboxes {font-size:13px;color:#000;padding:1px;text-align:center}
	.answer {border-bottom:1px solid #000;font-size:12px;}
	.answer a{font-size:12px;font-weight:normal;color:#555}
	table{border-collapse:separate;margin:auto}
	img{width:123px;}
	h1{font-size:22px;}
	hr{border-collapse:collapse;background:#000}
}
</style>
</head>
<body>
Thank you for your interest in an account with <?=$sitename?>. Please see below a summary of your enquiry.<br /><br />
<table style="border-collapse:separate !important;border:1px solid #000;width:<?=$mainwid?>;margin:auto" cellspacing="3" align="center">
	<tr>
		<td colspan="5" style="text-align:center"><img src="http://www.lafuma.co.uk/content/img/main/logo.jpg" alt="" /><br /><h1>Account Enquiry</h1></td>
	</tr>
	<tr>
		<td class="question" style="width:16%;">Shop Name:&nbsp;</td>
		<td class="answer" style="width:84%;border-bottom:1px solid #000;" colspan="4"><?=$_POST['sname']?></td>
	</tr>
	<?
	$addy=array($_POST['address']);
	if(isset($_POST['address'])&&strlen($_POST['address'])>$linechars)
	{
		$addy=str_split($_POST['address'],$linechars);
	}
	$addycount=count($addy);
	$addyrows=5-$addycount;
	?>
	<tr>
		<td class="question">Shop&nbsp;Address:&nbsp;</td>
		<td colspan="4" class="answer" style="border-bottom:1px solid #000;"><?=$addy[0]?></td>
	</tr>
	<? for($x=1;$x<$addycount;$x++){?>
	<tr>
		<td colspan="5" class="answer" style="border-bottom:1px solid #000;"><?=$addy[$x]?></td>
	</tr>
	<? }?>
	<? for($x=0;$x<$addyrows;$x++){?>
	<tr>
		<td colspan="5" class="answer" style="border-bottom:1px solid #000;">&nbsp;</td>
	</tr>
	<? }?>
	<tr>
		<td class="question">Website&nbsp;Address:&nbsp;</td>
		<td colspan="4" class="answer" style="border-bottom:1px solid #000;"><?=!isset($_POST['url'])||strlen($_POST['url'])<1?"Not entered":$_POST['url']?></td>
	</tr>
	<tr>
		<td class="question">Contact&nbsp;Name:&nbsp;</td>
		<td colspan="4" class="answer" style="border-bottom:1px solid #000;"><?=$_POST['cname']?></td>
	</tr>
	<tr>
		<td class="question">Telephone&nbsp;Number:&nbsp;</td>
		<td class="question" style="width:5%;">(Shop)</td>
		<td class="answer" style="width:35%;border-bottom:1px solid #000;"><?=$_POST['tel']?></td>
		<td class="question" style="width:5%;">(Mobile)</td>
		<td class="answer" style="width:35%;border-bottom:1px solid #000;"><?=!isset($_POST['mob'])||strlen($_POST['mob'])<1?"Not entered":$_POST['mob']?></td>
	</tr>
	<tr>
		<td class="question">Fax&nbsp;Number:&nbsp;</td>
		<td class="answer" colspan="4" style="border-bottom:1px solid #000;"><?=!isset($_POST['fax'])||strlen($_POST['fax'])<1?"Not entered":$_POST['fax']?></td>
	</tr>
	<tr>
		<td class="question">Email&nbsp;Address:&nbsp;</td>
		<td class="answer" colspan="4" style="border-bottom:1px solid #000;"><a href="mailto:<?=$_POST['email']?>&amp;subject=RE:%20<?=rawurlencode($sitename)?>%20Account%20Enquiry&amp;body=Dear%20<?=rawurlencode($_POST['cname'])?>,"><?=$_POST['email']?></a></td>
	</tr>
	<tr>
		<td colspan="5">
			<table style="border-collapse:separate !important;width:100%" cellspacing="0" align="center">
				<tr>
					<td width="33%" class="question">Retail&nbsp;Premises? <?=in_array("Retail Premises",$_POST['type'])?"Y":"N"?></td>
					<td width="34%" class="question">Online&nbsp;Shopping? <?=in_array("Online Shopping",$_POST['type'])?"Y":"N"?></td>
					<td width="33%" class="question">Mail&nbsp;Order? <?=in_array("Mail Order",$_POST['type'])?"Y":"N"?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<div style="width:90%;margin:10px auto 0;font-weight:bold" class="question">Outlet:</div>
<table style="border-collapse:collapse !important;width:90%;margin:auto;border:1px solid #000" align="center" cellspacing="0" border="0">
<tr>
	<td style="border-right:1px solid #000" class="tickboxes">Garden</td>
	<td style="border-right:1px solid #000" class="tickboxes"><?=in_array("Garden",$_POST['outlet'])?"&#10004;":"&nbsp;"?></td>
	<td style="border-right:1px solid #000" class="tickboxes">Camping</td>
	<td style="border-right:1px solid #000" class="tickboxes"><?=in_array("Camping",$_POST['outlet'])?"&#10004;":"&nbsp;"?></td>
	<td style="border-right:1px solid #000" class="tickboxes">Caravan</td>
	<td class="tickboxes"><?=in_array("Caravan",$_POST['outlet'])?"&#10004;":"&nbsp;"?></td>
</tr>
<tr>
	<td style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000" class="tickboxes">Dept Store</td>
	<td style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000" class="tickboxes"><?=in_array("Dept Store",$_POST['outlet'])?"&#10004;":"&nbsp;"?></td>
	<td style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000" class="tickboxes">Reflexology</td>
	<td style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000" class="tickboxes"><?=in_array("Reflexology",$_POST['outlet'])?"&#10004;":"&nbsp;"?></td>
	<td style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000" class="tickboxes">Other</td>
	<td style="border-top:1px solid #000;border-bottom:1px solid #000" class="tickboxes"><?=in_array("Other",$_POST['outlet'])?"&#10004;":"&nbsp;"?></td>
</tr>
</table>
<br />
<?
$pinfo=array($_POST['info']);
if(isset($_POST['info'])&&strlen($_POST['info'])>$linechars)
{
	$pinfo=str_split($_POST['info'],$linechars);
}
$infocount=count($pinfo);
$rows=4-$infocount;
?>
<table style="border-collapse:separate;border:1px solid #000;width:<?=$mainwid?>;margin:auto" cellspacing="3" align="center">
	<tr>
		<td class="question" style="width:29%;">Additional Information:&nbsp;</td>
		<td class="answer" style="width:71%;border-bottom:1px solid #000;"><?=$pinfo[0]?>&nbsp;</td>
	</tr>
	<? for($x=1;$x<$infocount;$x++){?>
	<tr>
		<td class="answer" colspan="2" style="width:99%;border-bottom:1px solid #000;"><?=stripslashes($pinfo[$x])?></td>
	</tr>
	<? }?>
	<? for($x=0;$x<$rows;$x++){?>
	<tr>
		<td class="answer" colspan="2" style="width:99%;border-bottom:1px solid #000;">&nbsp;</td>
	</tr>
	<? }?>
</table>
<br />
<table style="border-collapse:collapse;border:1px solid #000;width:<?=$mainwid?>;margin:auto" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td class="question" style="width:65%;padding:3px">Enquiry Taken By:<br /><span style="font-weight:normal">Online Submission</span></td>
		<td class="question" style="width:45%;border-left:1px solid #000;padding:3px">Date:<br /><span style="font-weight:normal"><?=date("d/m/Y")?></span></td>
	</tr>
</table>
<hr style="margin:10px auto 0;width:<?=$mainwid?>;height:1px" />
<div class="question" style="width:100%;margin:auto;text-align:center">Internal</div>
<table style="border-collapse:collapse;border:1px solid #000;width:<?=$mainwid?>;margin:auto" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="question" style="width:65%;padding:3px">Details passed on to:<br />&nbsp;</td>
		<td class="question" style="width:45%;padding:3px;border-left:1px solid #000">Date:<br />&nbsp;</td>
	</tr>
</table>
</body>
</html>
<? 
$html = ob_get_clean();/* html */
ob_end_flush();
ob_start();
?>
--PHP-mixed-<?=$random_hash?>

Content-Type: multipart/alternative; boundary="PHP-alt-<?=$random_hash?>"

<? /* plain email */ ?>
--PHP-alt-<?=$random_hash?>

Content-Type: text/plain; charset = "iso-8859-1"
Content-Transfer-Encoding: 7bit

<?=$plain?>

--PHP-alt-<?=$random_hash?>

Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<?=$html?>

--PHP-alt-<?=$random_hash?>--
--PHP-mixed-<?=$random_hash?> 
Content-Type: text/html; name="<?=$_POST['sname']." - ".$sitename." Account Enquiry.html"?>" 
Content-Transfer-Encoding: 7bit 
Content-Disposition: attachment 

<?=$html?>

--PHP-mixed-<?=$random_hash?>--
<? /* end bit */?>
<?
		$body=ob_get_clean();
		//$file=@fopen($fname,"w+");
		//@fwrite($file,$html);
		//@fclose($file);
		/* send emails */
		$headers = "From: ".$sitename." <"."sales@llc-ltd.co.uk".">\r\nReply-To: sales@llc-ltd.co.uk";
		$headers .= "\r\nX-Mailer: PHP/".phpversion();
		$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";		
	
		if($_SESSION['test']==0)
		{	
			$mail_sent_sales = mail( $tostr, $sitename." Account Enquiry", $body, $headers,"-fsales@llc-ltd.co.uk" );	
			$mail_sent_cust = @mail( $_POST['email'], $sitename." Account Enquiry", $body, $headers,"-fsales@llc-ltd.co.uk" );
			?><div style="text-align:center;width:100%">
			<h1>Thank you for your enquiry</h1>
			<? if($mail_sent_cust){?>A confirmation will be emailed to you shortly<? }?>
			<? if(!$mail_sent_sales){?><br />An error occurred while sending your enquiry, please try again.<? }?>
			</div><?
		}
		else
		{
			$mail_sent_sales = mail( "senfield@gmk.co.uk", $sitename." Account Enquiry", $body, $headers,"-fsales@llc-ltd.co.uk" );	
			?>
			Mail to: <?=$tostr?><br />
			Body...
			<p><?=$body?></p>
			Headers: <?=$headers?>
			<div style="text-align:center;width:100%">
			<h1>Test Enquiry Sent</h1>
			<? if($mail_sent_sales){?>A confirmation will be emailed to you shortly<? }?>
			</div>
			<?
		}
	}
	else
	{
	}
}
if(!isset($_POST['sname'])||count($err)>0){
	if($_SESSION['test']==1){echo "TESTING - ".$tostr;}
?>
<h2 id="pagetitle">LLC Trade Account Enquiry</h1>
<div id="tradeform">
	<dfn>* = Required information</dfn>
	<div id="in">
	<form action="index.php?p=trade" method="post">
	<table>
	<tr>
		<td style="width:20%">Shop Name<dfn>*</dfn>:<?=isset($err['sname'])&&strlen($err['sname'])>0?"<br /><span style='color:red'>".$err['sname']."</span>":""?></td>
		<td style="width:80%"><input type="text" name="sname" required value="<?=isset($_POST['sname'])?$_POST['sname']:""?>" style=" <?=$req['sname']?>" /></td>
	</tr>
	<tr>
		<td>Shop Address<dfn>*</dfn>:<?=isset($err['address'])&&strlen($err['address'])>0?"<br /><span style='color:red'>".$err['address']."</span>":""?></td>
		<td><textarea name="address" required style=" <?=$req['address']?>"><?=isset($_POST['address'])?$_POST['address']:""?></textarea></td>
	</tr>
	<tr>
		<td>Website Address:<br /><dfn>Including 'http://'</dfn></td>
		<td><input type="url" name="url" value="<?=isset($_POST['url'])?$_POST['url']:""?>" /></td>
	</tr>
	<tr>
		<td>Contact Name<dfn>*</dfn>:<?=isset($err['cname'])&&strlen($err['cname'])>0?"<br /><span style='color:red'>".$err['cname']."</span>":""?></td>
		<td><input type="text" required name="cname" value="<?=isset($_POST['cname'])?$_POST['cname']:""?>" style=" <?=$req['cname']?>" /></td>
	</tr>
	<tr>
		<td>Contact Numbers:<?=isset($err['tel'])&&strlen($err['tel'])>0?"<br /><span style='color:red'>".$err['tel']."</span>":""?></td>
		<td><div style=" <?=$req['tel']?>">Shop<dfn>*</dfn> <input type="tel" required name="tel" value="<?=isset($_POST['tel'])?$_POST['tel']:""?>" style="width:110px !important" /></div> <div>Mobile <input type="tel" name="mob" value="<?=isset($_POST['mob'])?$_POST['mob']:""?>" style="width:110px !important" /></div> <div>Fax <input type="tel" name="fax" value="<?=isset($_POST['fax'])?$_POST['fax']:""?>" style="width:110px !important" /></div></td>
	</tr>
	<tr>
		<td>Email Address<dfn>*</dfn>:<?=isset($err['email'])&&strlen($err['email'])>0?"<br /><span style='color:red'>".$err['email']."</span>":""?></td>
		<td><input type="email" name="email" required value="<?=isset($_POST['email'])?$_POST['email']:""?>" style=" <?=$req['email']?>" /></td>
	</tr>
	<tr>
		<td>Shop Type<dfn>*</dfn>:<?=isset($err['type'])&&strlen($err['type'])>0?"<br /><span style='color:red'>".$err['type']."</span>":""?></td>
		<td><? if(strlen($req['type'])>0){?><div style="padding:1px;<?=$req['type']?>"><? }?>
		<div><label for="st1">Retail Premises </label><input id="st1" type="checkbox" name="type[]" value="Retail Premises" <?=isset($_POST['type'])&&in_array("Retail Premises",$_POST['type'])?"checked='checked'":""?> /></div> <div><label for="st2">Online Shopping </label><input id="st2" type="checkbox" name="type[]" value="Online Shopping" <?=isset($_POST['type'])&&in_array("Online Shopping",$_POST['type'])?"checked='checked'":""?> /></div> <div><label for="st3">Mail Order </label><input id="st3" type="checkbox" name="type[]" value="Mail Order" <?=isset($_POST['type'])&&in_array("Mail Order",$_POST['type'])?"checked='checked'":""?> /></div><? if(strlen($req['type'])>0){?></div><? }?></td>
	</tr>
	<tr>
		<td>Outlet<dfn>*</dfn>:<?=isset($err['outlet'])&&strlen($err['outlet'])>0?"<br /><span style='color:red'>".$err['outlet']."</span>":""?></td>
		<td>
		<? $outlets=array("Garden","Camping","Caravan","Dept Store","Reflexology","Other");?>
		<? if(strlen($req['outlet'])>0){?><div style="padding:1px;<?=$req['outlet']?>"><? }?>
		<? foreach($outlets as $key => $out){?>
		<div><label for="o<?=$key?>"><?=$out?> </label><input id="o<?=$key?>" type="checkbox" name="outlet[]" value="<?=$out?>" <?=isset($_POST['outlet'])&&in_array($out,$_POST['outlet'])?"checked='checked'":""?> /></div> 
		<? }?>
		<? if(strlen($req['outlet'])>0){?></div><? }?>
		</td>
	</tr>
	<tr>
		<td>Additional Information:</td>
		<td><textarea name="info"><?=isset($_POST['info'])?$_POST['info']:""?></textarea></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:center"><input type="submit" value="Submit" /></td>
	</tr>
	</table>
	</form>
	</div>
</div>
<? }?>