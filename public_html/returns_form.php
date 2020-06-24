<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security ?>
<h2 id="pagetitle">Lafuma Returns</h1>
<?
	/* Error testing */
	$req=array();
	$req['name']="";
	$req['address']="";
	$req['description']="";
	$req['fault']="";
	if(!isset($_POST['email'])||strlen($_POST['email'])<1||$_POST['contact']=="phone")
	{
		$req['phone']="";
	}
	if(!isset($_POST['phone'])||strlen($_POST['phone'])<1||$_POST['contact']=="email"||$_POST['label']=="email")
	{
		$req['email']="";
	}
	$err=array();
	if(isset($_POST['name']))
	{
		foreach($req as $f => $v)
		{
			if(array_key_exists($f,$_POST))
			{
				if(!is_array($_POST[$f]))
				{
					if(strlen($_POST[$f])<1){$err[$f]="Please fill in this field";}/* empty */
					else if($f=="email"&&!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$/",$_POST[$f]))
					{$err[$f]="Please enter a valid email address (eg: user@web.com)";}/* email */				
				}
			}	
			else
			{
				$err[$f]="Please tick at least one box.";/* checkboxes */	
			}	
		}
		if(strlen($err['phone'])>0&&strlen($err['email'])>0){$err['contact']="Please enter atleast one form of contact";}	
		foreach($err as $f => $v)
		{
			$req[$f]=strlen($v)>0?"border:1px solid #CD071E;":"";
		}
		if(count($err)>0||strlen($_POST['space'])>0)
		{
			?>
			<div style="border:1px solid #CD071E;color:#CD071E;padding-left:10px;margin-bottom:10px;">
			<strong>Sorry, encountered an error with the following fields:</strong>
			<ul style="padding-left:10px;margin:0 0 3px 0">
			<? foreach($err as $f => $m){if(!(strlen($err['contact'])>1&&($f=="email"||$f=="phone"))){?><li><label for="form_<?=$f=="contact"?"phone":$f?>"><?=ucwords($f)." - ".$m?></label></li><? }}?>
			</ul>
			</div>
			<?
		}
		else
		{
			//print_r($_POST);
			/* EMAIL BODY */
			ob_end_flush();
			ob_start();			
			?>
			<style tyle="text/css">		
				body, table, td, div{
					font-size: 12px;
					font-family: Arial,Helvetica,sans-serif;
				}
				td{}
				.question {font-weight:bold;font-size:13px;color:#000;padding:3px !important;}
				.answer {font-size:12px;}
				.answer a{font-size:12px;font-weight:normal;color:#555}
				tr.row1 td{background:#FFF;}
				tr.row2 td{background:#e4e4e4;}
				table{border-collapse:separate;margin:auto}
			</style>
			<div>Please see below for a summary of this return request.</div>
			<table style="border:1px solid #000;width:<?=$mainwid?>;margin:auto" align="center">
			<tr class="row1">
				<td style="width:30%" class="question">Name:</td>
				<td style="width:70%" class="answer"><?=isset($_POST['name'])?$_POST['name']:""?></td>
			</tr>
			<tr class="row2">
				<td class="question">Address:</td>
				<td class="answer"><?=isset($_POST['address'])?$_POST['address']:""?></td>
			</tr>		
			<tr class="row1">
				<td class="question">Phone:</td>
				<td class="answer"><?=isset($_POST['phone'])?$_POST['phone']:""?></td>
			</tr>
			<tr class="row2">
				<td class="question">Email:</td>
				<td class="answer"><?=isset($_POST['email'])?"<a href='mailto:".$_POST['email']."'>".$_POST['email']."</a>":""?></td>
			</tr>
			<tr class="row1">
				<td class="question">Lafuma Order No:</td>
				<td class="answer"><?=isset($_POST['onum'])?$_POST['onum']:""?></td>
			</tr>
			<tr class="row2">
				<td class="question">Item Description:</td>
				<td class="answer"><?=isset($_POST['description'])?$_POST['description']:""?></td>
			</tr>
			<tr class="row1">
				<td class="question">Fault Description:</td>
				<td class="answer"><?=isset($_POST['fault'])?$_POST['fault']:""?></td>
			</tr>
			<tr class="row2">
				<td class="question">How would you prefer us to contact you?:</td>
				<td class="answer"><?=$_POST['contact']?></td>
			</tr>
			<tr class="row1">
				<td class="question">How would you prefer to receive your Parcelforce label?:</td>
				<td class="answer"><?=$_POST['label']?></td>
			</tr>	
			</table>
			<?
			$html = ob_get_clean();/* html */
			ob_end_flush();
			ob_start();
			?>
Please see below for a summary of this return request.\r\n\r\n
Name: <?=isset($_POST['name'])?$_POST['name']:""?>\r\n
Address: <?=isset($_POST['address'])?$_POST['address']:""?>\r\n
Phone: <?=isset($_POST['phone'])?$_POST['phone']:""?>\r\n
Email: <?=isset($_POST['email'])?$_POST['email']:""?>\r\n
Lafuma Order No: <?=isset($_POST['onum'])?$_POST['onum']:""?>\r\n
Item Description: <?=isset($_POST['description'])?$_POST['description']:""?>\r\n
Fault Description: <?=isset($_POST['fault'])?$_POST['fault']:""?>\r\n
How would you prefer us to contact you?: <?=$_POST['contact']?>\r\n
How would you prefer to receive your Parcelforce label?: <?=$_POST['label']?>
			<?
			$plain = ob_get_clean();/* plain */
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

--PHP-mixed-<?=$random_hash?>--
			<?
			$body=ob_get_clean();
			/* /EMAIL BODY */
			$headers = "From: ".$sitename." <"."sales@llc-ltd.co.uk".">\r\nReply-To: sales@llc-ltd.co.uk";
			$headers .= "\r\nX-Mailer: PHP/".phpversion();
			$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";		
			$tostr="sales@llc-ltd.co.uk";//"senfield@gmk.co.uk";
			$mail_sent_sales = mail( $tostr, "Lafuma return request from ".$_POST['name'], $body, $headers,"-fsales@llc-ltd.co.uk" );	
			if(isset($_POST['email'])&&strlen($_POST['email'])>0){$mail_sent_cust = @mail( $_POST['email'], "Lafuma return request", $body, $headers,"-fsales@llc-ltd.co.uk" );}
			//if($mail_sent_sales==0){echo $html;}
			?><div style="font-size:14px;">Thank you, your request has been entered successfully and a member of our sales team will contact you shortly.</div><?
		}
	/* /Error testing required */
	}
	?>
<div id="tradeform">
	<dfn>* = Required information</dfn>
	<div id="in">	
		<form action="index.php?p=returns_form" method="post">
		<input type="hidden" name="space" value="" />
		<table>
		<tr>
			<td style="width:20%">Name<dfn>*</dfn>:<?=isset($err['name'])&&strlen($err['name'])>0?"<br /><span style='color:red'>".$err['name']."</span>":""?></td>
			<td style="width:80%"><input type="text" name="name" id="form_name" value="<?=isset($_POST['name'])?$_POST['name']:""?>" style=" <?=$req['name']?>" /></td>
		</tr>
		<tr>
			<td>Address<dfn>*</dfn>:<?=isset($err['address'])&&strlen($err['address'])>0?"<br /><span style='color:red'>".$err['address']."</span>":""?></td>
			<td><textarea name="address" id="form_address" style=" <?=$req['address']?>"><?=isset($_POST['address'])?$_POST['address']:""?></textarea></td>
		</tr>
		
		<tr>
			<td>Contact Details:<?=isset($err['contact'])&&strlen($err['contact'])>0?"<br /><span style='color:red'>".$err['contact']."</span>":""?></td>
			<td><div><label for="form_phone">Phone </label><input id="form_phone" type="tel" name="phone" value="<?=isset($_POST['phone'])?$_POST['phone']:""?>" style="width:110px !important; <?=$req['phone']?>" /></div> <div><label for="form_email">Email </label><input id="form_email" type="text" name="email" value="<?=isset($_POST['email'])?$_POST['email']:""?>" style=" <?=$req['email']?>" /></div></td>
		</tr>
		<tr>
			<td>Lafuma Order No:<?=isset($err['onum'])&&strlen($err['onum'])>0?"<br /><span style='color:red'>".$err['onum']."</span>":""?></td>
			<td><input type="text" name="onum" id="form_onum" value="<?=isset($_POST['onum'])?$_POST['onum']:""?>" style=" <?=$req['onum']?>" /></td>
		</tr>
		<tr>
			<td>Item Description<dfn>*</dfn>:</td>
			<td><textarea name="description" id="form_description" style=" <?=$req['description']?>"><?=isset($_POST['description'])?$_POST['description']:""?></textarea></td>
		</tr>
		<tr>
			<td>Fault Description<dfn>*</dfn>:</td>
			<td><textarea name="fault" id="form_fault" style=" <?=$req['fault']?>"><?=isset($_POST['fault'])?$_POST['fault']:""?></textarea></td>
		</tr>
		<tr>
			<td colspan="2">How would you prefer us to contact you?<dfn>*</dfn>:</td>
		</tr>
		<tr>
			<td colspan="2">
			<div><label for="form_contact_email">Email </label><input id="form_contact_email" type="radio" name="contact" value="email" <?=(isset($_POST['contact'])&&$_POST['contact']=="email")||!isset($_POST['contact'])?"checked='checked'":""?> /></div> 
			<div><label for="form_contact_phone">Phone </label><input id="form_contact_phone" type="radio" name="contact" value="phone" <?=isset($_POST['contact'])&&$_POST['contact']=="phone"?"checked='checked'":""?> /></div> 
			</td>
		</tr>
		<tr>
			<td colspan="2">How would you prefer to receive your Parcelforce label?<dfn>*</dfn>:<?=isset($err['print'])&&strlen($err['print'])>0?"<br /><span style='color:red'>".$err['print']."</span>":""?></td>
		</tr>
		<tr>
			<td colspan="2">
			<div><label for="form_label_email">Email (print at home) </label><input id="form_label_email" type="radio" name="label" value="email" <?=(isset($_POST['label'])&&$_POST['label']=="email")||!isset($_POST['label'])?"checked='checked'":""?> /></div> 
			<div><label for="form_label_post">Post </label><input id="form_label_post" type="radio" name="label" value="post" <?=isset($_POST['label'])&&$_POST['label']=="post"?"checked='checked'":""?> /></div> 
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center"><input type="submit" value="Submit" /></td>
		</tr>
		</table>
		</form>
	</div>
</div>