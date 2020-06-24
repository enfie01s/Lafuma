<?
include("sagepay/includes.php");
session_start(); 

/**************************************************************************************************
* Sage Pay Direct PHP Kit Order Successful Page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============

* 02/04/2009 - Simon Wolfe - Updated UI for re-brand
* 18/12/2007 - Nick Selby - New PHP version adapted from ASP
****************************************************************************************************
* Description
* ===========

* This is a placeholder for your Successful Order Completion Page.  It retrieves the VendorTxCode
* from the crypt string and displays the transaction results on the screen.  You wouldn't display 
* all the information in a live application, but during development this page shows everything
* sent back in the confirmation screen.
****************************************************************************************************/

// Check for the proceed button click, and if so, go to the buildOrder page


//Now check we have a VendorTxCode passed to this page
$strVendorTxCode=$_SESSION["VendorTxCode"];
if (strlen($strVendorTxCode)==0) { 
	//No VendorTxCode, so take the customer to the home page
	ob_end_flush();
	session_destroy();
	redirect("$mainbase/index.php");
	exit();
}

//Empty the cart, we're done with it now because the order is successful
unset($_SESSION['cart']);
unset($_SESSION['address_details']);
unset($_SESSION['shipping']);
unset($_SESSION['terms_agree']);
unset($_SESSION['discount_code']);
unset($_SESSION['discount_amount']);
unset($_SESSION['checkoutnew']);

$strSQL="SELECT * FROM orders WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
//Execute the SQL command
$rsPrimary = mysql_query($strSQL)
	or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
$strSQL="";
$row=mysql_fetch_array($rsPrimary);
$rowescape=mysql_real_extracted($row);
/* GENERATE DATA FOR NAV */
if(!file_exists('orders/L' . $row['invoice'] . '.txt')&&$_SESSION['test']==0)/*prevent data duplication and excessive emailing*/
{
	$fp = fopen('orders/L' . $row['invoice'] . '.txt', 'w') or die("can't open file");
	$cref = $row['cust_id'];
	if($cref<1)
	{
		$pref = "L" . $row['invoice'];
	}
	else
	{
		$pref = "L" . $row['invoice'] . "-" . $cref;
	}
	
// ==========DELIVERY DATE STAMP ========================== 		
date_default_timezone_set('Europe/London');
$today= mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
if ($rowescape['ship_method_id']=="6")
{ 	$inc=5;
	date("N",$today)==7?$inc=4:"";
	date("N",$today)==1?$inc=3:"";
	date("N",$today)==2?$inc=3:"";
} 

else if ($rowescape['ship_method_id']=="15" || $rowescape['ship_method_id']=="16" || $rowescape['ship_method_id']=="18")
{ 	$inc=0;	
	date('G')>=12?$inc=1:"";
	if (date('G')>=12 && date("N",$today)==5) { $inc=3; }
	date("N",$today)==6?$inc=2:"";
	date("N",$today)==7?$inc=1:"";
	$rowescape['ship_method_id']=="18"?$messagetwo=" for PRE 9am delivery":"";
} 
	$za= mktime(0, 0, 0, date("m")  , date("d",$today)+$inc, date("Y"));
	$rowescape['ship_method_id']=="8"?($za="Friday").($messagetwo=" for Saturday delivery"):$za=date("l",$za);
	$messagetwo=$za." dispatch please".$messagetwo; 
// END ========== also on line 105 =============
	
	
	$navacc=$row['currency']=="GBP"?"ZZWEB":"ZZWEBIE";
	$sales_head	= "HED" . ",";/* label*/
	$sales_head.= $pref . ",";/*customer order num*/
	$sales_head.= $navacc . ",";/*navision customer no*/
	$sales_head.= ",";/*delivery cust code*/
	$sales_head.= substr(str_replace(","," ",$rowescape['alt_name']),0,25)."-".substr(str_replace(","," ",$rowescape['alt_phone']),0,14).",";/*cust name*/
	$sales_head.= substr(str_replace(","," ",$rowescape['alt_address1']),0,35).",";/*cust addy 1*/
	$sales_head.= (strlen($rowescape['alt_address2'])>0?substr(str_replace(","," ",$rowescape['alt_address2']),0,35):'').",";/*cust addy 2*/
	$sales_head.=	substr(str_replace(","," ",$rowescape['alt_city']),0,35).",";/*cust addy 3*/
	$sales_head.= substr(str_replace(","," ",get_county($row['alt_state'])),0,35).",";/*cust addy 4*/
	
	$postalcode=str_replace(" ","",$rowescape['alt_postcode']); $postalcode = substr_replace( $postalcode, ' ', -3, 0 );
	
	$sales_head.= substr(str_replace(","," ",$postalcode),0,10).",";/*cust post code*/
	$sales_head.= date("d-m-Y") . ",";/*order date*/
	$sales_head.= date("d-m-Y") . ",,,";/*required date required time,booking ref*/
	$sales_head.= substr((strlen($rowescape['ship_option'])>0?"[".$rowescape['ship_option']."] ":"").str_replace(","," - ",$rowescape['comments']),0,70) .",";/*comments (del instruct 1)*/
	$sales_head.= ",".$messagetwo.",,,";/*del instruct 2-4,contact name*/
	$sales_head.= substr(str_replace(","," ",$rowescape['alt_phone']),0,20).",";/*contact tel*/
	$sales_head.= ",";/*contact country*/
	$sales_head.= substr(str_replace(","," ",$rowescape['alt_email']),0,200) . PHP_EOL;	/*contact email*/
	
	fwrite($fp, $sales_head);
	
	$i = 1;
	$order_prodsq = mysql_query("SELECT * FROM orderproducts WHERE `order_id`='".$row['order_id']."'") or die(mysql_error());
	while ($order_prods = mysql_fetch_array($order_prodsq))
	{
		if($order_prods['ispack']==0)
		{
			$sales_line	= "LNE" . ",";/*label*/
			$sales_line.= "L".$row['invoice'] . ",";/*customer order num*/
			$sales_line.= $i . ",";/*line id*/
			$sales_line.= $order_prods['sku'] . ",";/*nav product code*/
			$sales_line.= substr(str_replace(","," ",$order_prods['title']),0,40) . ",";/*product description*/
			$sales_line.= $order_prods['qty'] . ",";/*line qty*/
			$sales_line.= ",";/*pack size*/
			$sales_line.= number_format($order_prods['price']+(($order_prods['price']/100)*$vat),2).",";/*price + vat*/
			$sales_line.= ",,";/*cust line no,order cust*/
			$sales_line.= ",";/*cust name*/
			$sales_line.= ",";/*cust addy 1*/
			$sales_line.= ",";/*cust addy 2*/
			$sales_line.=	",";/*cust addy 3*/
			$sales_line.= ",";/*cust addy 4*/
			$sales_line.= ",";/*cust post code*/
			$sales_line.= ",,";/*req'd date,req'd time*/
			$sales_line.= $order_prods['variant_id'] . PHP_EOL;/*variant code*/
			fwrite($fp, $sales_line);
			$i++;
			mysql_query("UPDATE nav_stock SET `nav_qty`=`nav_qty`-".$order_prods['qty']." WHERE `nav_skuvar`='".$order_prods['sku']."-v-".$order_prods['variant_id']."'");//not pack
		}
		else
		{
			$order_kitsq=mysql_query("SELECT * FROM orderkits WHERE `order_prod_id`='".$order_prods['order_prod_id']."'");
			while($order_kits=mysql_fetch_assoc($order_kitsq))
			{
				$howmany=$order_kits['item_qty']*$order_prods['qty'];
				
				$unitprice=$order_prods['price']/$order_kits['item_qty'];/*pack price divided by qty in pack*/
				
				$howmuch=$unitprice+(($unitprice/100)*$vat);
				
				$okskuvar=explode("-v-",$order_kits['okit_skuvar']);
				$sales_line	= "LNE" . ",";/*label*/
				$sales_line.= "L".$row['invoice'] . ",";/*customer order num*/
				$sales_line.= $i . ",";/*line id*/
				$sales_line.= $okskuvar[0] . ",";/*nav product code*/
				$sales_line.= substr(str_replace(","," ",$order_kits['kit_title']),0,40) . ",";/*product description*/
				$sales_line.= $howmany . ",";/*line qty*/
				$sales_line.= ",";/*pack size*/
				$sales_line.= number_format($howmuch,2).",";/*unit price + vat*/
				$sales_line.= ",,";/*cust line no,order cust*/
				$sales_line.= ",";/*cust name*/
				$sales_line.= ",";/*cust addy 1*/
				$sales_line.= ",";/*cust addy 2*/
				$sales_line.=	",";/*cust addy 3*/
				$sales_line.= ",";/*cust addy 4*/
				$sales_line.= ",";/*cust post code*/
				$sales_line.= ",,";/*req'd date,req'd time*/
				$sales_line.= $okskuvar[1] . "\r\n";/*variant code*/
				fwrite($fp, $sales_line);
				$i++;
				mysql_query("UPDATE nav_stock SET `nav_qty`=`nav_qty`-".($order_kits['item_qty']*$order_prods['qty'])." WHERE `nav_skuvar`='".$order_kits['okit_skuvar']."'");//pack
			}
		}
	}
	
	fclose($fp);
	
	/* GENERATE DATA FOR NAV */
	
	
	
	/* SEND EMAILS OUT*/
	 
	$random_hash = md5(date('r', time())); 
	
	ob_start(); //Turn on output buffering
	?>
	
--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/plain; charset = "ISO-8859-15"
Content-Transfer-Encoding: 7bit

Invoice Number <?=$row['invoice']?> - Order Date <?=date("d/m/Y",$row['date_ordered'])?>\r\n
============================================================\r\n
\r\n
Order Status: <?=$row['order_status']?>\r\n
Order Comments: <?=((strlen($row['comments'])>0)?$rowescape['comments']:"None")?>\r\n
Payment Method: <?=$row['pay_method']=="paypal"?"PayPal":"Credit/Debit Card"?>\r\n
Postage Method: <?=htmlentities($row['ship_description'],ENT_QUOTES,"ISO-8859-15")?>\r\n
<? if(strlen($row['ship_option'])>0){?>
Postage Option: <?=htmlentities(ucwords($row['ship_option']),ENT_QUOTES,"ISO-8859-15")?>\r\n
<? }?>
\r\n
\r\n
Billing Address\r\n
----------------------------
<?=$rowescape['firstname']?> <?=$rowescape['lastname']?>\r\n
<?=$rowescape['address1']?>\r\n
<?=((strlen($row['address2'])>0)?$rowescape['address2']."\r\n":"")?>
<?=$rowescape['city']?>\r\n
<?=get_county($row['state'])?>\r\n
<?=get_country($row['country'])?>\r\n
<?=$rowescape['postcode']?>\r\n
<?=$rowescape['email']?>\r\n
<?=$rowescape['phone']?>
\r\n
\r\n
Delivery Address\r\n
----------------------------
<? if($row['sameasbilling']==1){?>
Same as billing address
<? }else{?>
<?=$rowescape['alt_name']?>\r\n
<?=$rowescape['alt_address1']?>\r\n
<?=((strlen($rowescape['alt_address2'])>0)?$rowescape['alt_address2']."\r\n":"")?>
<?=$rowescape['alt_city']?>\r\n
<?=get_county($row['alt_state'])?>\r\n
<?=get_country($row['alt_country'])?>\r\n
<?=$rowescape['alt_postcode']?>\r\n
<?=$rowescape['alt_phone']?>
<? }?>
\r\n
\r\n
<? /* order contents */?>
<? 
$runtotal=0;
$removefromdiscount=0;
$discount=0;
$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount`,`currency` FROM orders as o,`orderproducts` as op LEFT JOIN fusion as f ON op.`prod_id`=f.`iSubId_FK` WHERE o.`VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "' AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";

$orderq=mysql_query($sstring);
while($order=mysql_fetch_assoc($orderq))
{
$iQuantity=$order['qty'];
$iProductId=$order['prod_id'];
$orderkitq=mysql_query("SELECT `fusionId`,`kit_title`,`item_qty`,`oname`,`oitem`,`prod_id` FROM orderkits as ok LEFT JOIN fusion as f ON ok.`prod_id`=f.`iSubId_FK` AND `vtype`='product' WHERE `order_prod_id`='$order[order_prod_id]' GROUP BY `okit_id`");
$ispack=mysql_num_rows($orderkitq);
?>

Product: <?=$order['title']." (".$order['sku'].")"?>\r\n
Quantity: <?=$order['qty']?><? if($order['exclude_discount']==1){?>(Discount exempt)<? }?>\r\n
<? 
if($ispack==0)
{
echo ucwords($order['oname']).": ".$order['oitem']."\r\n";
}
else
{?>
Pack Contents:\r\n<? 
while($orderkit=mysql_fetch_assoc($orderkitq))
{
?><?=$orderkit['kit_title']?> (<?=$orderkit['item_qty']?>)\r\n<?
echo ucwords($orderkit['oname']).": ".$orderkit['oitem']."\r\n";
}
}?>
Price(ex. VAT): <?=$currarr[$order['currency']][3]?><?=number_format($order['price'],2)?>\r\n
Sub Total: <?=$currarr[$order['currency']][3]?><?=number_format($order['price']*$order['qty'],2)?>\r\n

<?
$itemprice=$order['price']*$order['qty'];
$runtotal+=$itemprice;
$odiscount=$order['odiscount'];//discount percentage
$odiscountcode=$order['discount_code'];
$oshipdesc=strlen($order['ship_description'])?$order['ship_description']:"";
$oshiptotal=$order['ship_total'];
$ototalprice=$order['total_price'];
$otaxrate=$order['tax_rate'];
$otaxprice=$order['tax_price'];
$discount+=$order['opdiscount']*$order['qty'];
echo "-----------------------------------------------\r\n\r\n";
}
?>
=================
Sub Total: <?=$currarr[$order['currency']][3]?><?=number_format($runtotal,2)?>\r\n
<? if(strlen($odiscountcode)>0&&$odiscountcode!="discount code"){?>
<?=$odiscount?>% Discount (<?=$odiscountcode?>): - <?=$currarr[$order['currency']][3]?><?=number_format($discount,2)?>\r\n
<? }?>
VAT @<?=$otaxrate?>%: <?=$currarr[$order['currency']][3]?><?=number_format($otaxprice,2)?>\r\n
Postage <?=htmlentities($oshipdesc,ENT_QUOTES,"ISO-8859-15")?>: <?=$currarr[$order['currency']][3]?><?=number_format($oshiptotal,2)?>\r\n\r\n
Total: <?=$currarr[$order['currency']][3]?><?=number_format($ototalprice,2)?>
<? /*order contents */?>

	Thank you for your order, we appreciate your custom.\r\n
We would be grateful if you could add a review of the product you have purchased.  By completing a review, you will be automatically entered into a prize draw*, no matter if you leave positive or negative feedback.  At the end of the month, a customer will be randomly selected as the winner.  The winner will receive a 20% voucher which can be used on any subsequent order with www.lafuma.co.uk.\r\n
\r\n
If for any reason you are unhappy with your purchase, please contact us at <?=$admin_email?>.\r\n
\r\n
Lafuma UK\r\n
Bear House,\r\n
Concorde Way\r\n
Fareham\r\n
PO15 5RL\r\n
Email: <?=$admin_email?>\r\n
Tel: <?=$sales_phone?>\r\n
Hampshire\r\n
United Kingdom\r\n
\r\n
\r\n
VAT. Registration No: <?=$vatreg?>\r\n
Company Registration No.: <?=$coreg?>\r\n
\r\n
* Terms and Conditions - 1) Only customers who purchase from www.lafuma.co.uk and enter a subsequent review within 10 days of delivery will be entered into the draw. 2) The random draw will take place on the first working day of each month. The previous months entrants will be eligible in this draw. 3) The winner will be notified by email, and will be sent a voucher code. 4) The voucher code is only useable for one transaction with www.lafuma.co.uk. 5) The voucher code cannot be used to get additional discount on already discounted items. 6) The voucher code cannot be exchanged for any cash value. 7) The voucher code cannot be transferred to a third party. 8) Decisions pertaining to any aspect of the draw shall be made by LLC Ltd; these decisions are final. 9) There is no obligation for the winner to use the voucher code. 10) The voucher code will remain valid 12 months on the date of issue.    

--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/html; charset="ISO-8859-15"
Content-Transfer-Encoding: 7bit

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Mail</title>
<style>
body{font-size:10pt;color:#555555;font-family:Arial,Helvetica,sans-serif;}
address{font-style:normal;font-size:11pt;}
table{border-collapse:collapse;}
td{border:1px solid #FFF;}
table .price{
color:#13689D;
font-size:10pt;
font-weight:bold;
}
table.details{background:#ffffff;border:1px solid #dddddd;}
table.details tr td{
background:#eeeeee;
padding:3px;
color:#555555;
font:10pt Arial,Helvetica,sans-serif;
mso-margin-top-alt:2px;
margin-right:0cm;
mso-margin-bottom-alt:2px;
margin-left:0cm;
}
table.details tr td.head{
background:#DDDDDD;
font-weight:bold;
mso-margin-top-alt:0cm;
margin-right:0cm;
mso-margin-bottom-alt:0cm;
margin-left:0cm;
}
.note{font-size:10pt;}
h3{
clear:both;
color:#999999;
font-weight:normal;
padding:0;
margin:1em 0;
font-size:13pt;
}
h2{
color:#FF5200;
font-size:15pt;
font-weight:normal;
height:19px;
line-height:15pt;
margin:1em 0;
padding:0 0 0 20px;
}
a{text-decoration:none;color:#309A95;}
a:hover{color:#555555;}
.pack_contents{
border:1px solid #cccccc;
background:#f9f9f9;
padding:0px 3px;
mso-margin-top-alt:0cm;
margin-right:0cm;
mso-margin-bottom-alt:0cm;
margin-left:0cm;
}
</style>
</head>
<body>
<h2>Invoice Number <?=$row['invoice']?> - Order Date <?=date("d/m/Y",$row['date_ordered'])?></h2>
<table style="width:720px;border:10px solid #FFF;">
<tr>
<td width="50%" style="vertical-align:top;">
<h3>Order Status</h3>
<p class="note"><?=$row['order_status']?></p>
</td>
<td width="50%" style="vertical-align:top;">
<h3>Order Comments</h3>
<p class="note">
<?=((strlen($rowescape['comments'])>0)?$rowescape['comments']:"None")?>
</p>
</td>
</tr>
<tr>
<td width="50%" style="vertical-align:top;">
<h3>Payment Method</h3>
<p class="note"><?=$row['pay_method']=="paypal"?"PayPal":"Credit/Debit Card"?></p>
</td>
<td width="50%" style="vertical-align:top;">
<h3>Postage Method</h3>
<p class="note"<? if($row['ship_description']=="Saturday Delivery"){?> style='font-weight:bold;'<? }?>>
<?=$row['ship_description'].(strlen($row['ship_option'])>0?"<br />".ucwords($row['ship_option']):"")?>
</p>
</td>
</tr>
<tr>
<td width="50%" style="vertical-align:top;">
<h3>Billing Address</h3>
<address>
<?=$rowescape['firstname']?> <?=$rowescape['lastname']?><br />
<?=$rowescape['address1']?><br />
<?=((strlen($row['address2'])>0)?$rowescape['address2']."<br />":"")?>
<?=$rowescape['city']?><br />
<?=get_county($row['state'])?><br />
<?=get_country($row['country'])?><br />
<?=$rowescape['postcode']?><br />
<?=$rowescape['email']?><br />
<?=$rowescape['phone']?>
</address>
</td>
<td width="50%" style="vertical-align:top;">
<h3>Delivery Address</h3>
<address>
<? if($row['sameasbilling']==1){?>
Same as billing address
<? }else{?>
<?=$rowescape['alt_name']?><br />
<?=$rowescape['alt_address1']?><br />
<?=((strlen($rowescape['alt_address2'])>0)?$rowescape['alt_address2']."<br />":"")?>
<?=$rowescape['alt_city']?><br />
<?=get_county($row['alt_state'])?><br />
<?=get_country($row['alt_country'])?><br />
<?=$rowescape['alt_postcode']?><br />
<?=$rowescape['alt_phone']?>
<? }?>
</address>
</td>
</tr>
</table><p>&#160;</p>
<? ordercontents("o.VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'","720");?>
<p>
Thank you for your order, we appreciate your custom.<br />
We would be grateful if you could add a review of the product you have purchased.  By completing a review, you will be automatically entered into a prize draw*, no matter if you leave positive or negative feedback.  At the end of the month, a customer will be randomly selected as the winner.  The winner will receive a 20% voucher which can be used on any subsequent order with www.lafuma.co.uk.<br /><br />
If for any reason you are unhappy with your purchase, please contact us at <?=$admin_email?>.
</p>
<p style="font-style:italic;">
Lafuma UK<br />
Bear House,<br />
Concorde Way<br />
Fareham<br />
PO15 5RL<br />
Email: <a href="mailto:<?=$admin_email?>"><?=$admin_email?></a><br />
Tel: <?=$sales_phone?><br />
Hampshire<br />
United Kingdom<br />
</p>
<p>
VAT. Registration No: <?=$vatreg?><br />
Company Registration No.: <?=$coreg?>
</p>
<p style='font-size:9px'>
* Terms and Conditions - 1) Only customers who purchase from www.lafuma.co.uk and enter a subsequent review within 10 days of delivery will be entered into the draw. 2) The random draw will take place on the first working day of each month. The previous months entrants will be eligible in this draw. 3) The winner will be notified by email, and will be sent a voucher code. 4) The voucher code is only useable for one transaction with www.lafuma.co.uk. 5) The voucher code cannot be used to get additional discount on already discounted items. 6) The voucher code cannot be exchanged for any cash value. 7) The voucher code cannot be transferred to a third party. 8) Decisions pertaining to any aspect of the draw shall be made by LLC Ltd; these decisions are final. 9) There is no obligation for the winner to use the voucher code. 10) The voucher code will remain valid 12 months on the date of issue. </p>
</body></html>
--PHP-alt-<?php echo $random_hash; ?>--
	<?
	//copy current buffer contents into $message variable and delete current output buffer
	$message = ob_get_clean();
	
	$headers = "From: Lafuma UK <".$admin_email.">\r\nReply-To: ".$admin_email;
	$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"\r\n";
	$sat=$row['ship_description']=="Saturday Delivery"?" - Saturday Delivery":"";
	$to_llc_orders=($strConnectTo=="LIVE")?$orders_email:"senfield@gmk.co.uk";
	

	if($strConnectTo!="SIMULATOR"&&$_SESSION['test']==0)
	{	
		$mail_sent_llc = @mail( $to_llc_orders, "Lafuma UK Invoice ".$row['invoice'].$sat, $message, $headers,"-f".$admin_email );	
		$mail_sent_cust = @mail( $rowescape['email'], "Thank You for your order at Lafuma UK", $message, $headers,"-f".$admin_email );
		//@mail( "senfield@gmk.co.uk", "Thank You for your order at Lafuma UK", $message, $headers );
	}
	/* /SEND EMAILS OUT*/
}
?>
<p>Your order has been placed successfully.</p>
<p><a href="<?=$securebase?>/index.php?p=receipt">View your receipt</a></p>
<?		
if ($strConnectTo=="SIMULATOR")
{ 
	?>
	<script type="text/javascript" language="javascript" src="sagepay/scripts/common.js" ></script>
	<script type="text/javascript" language="javascript" src="sagepay/scripts/countrycodes.js" ></script>
	<p>*** TEST INFORMATION BELOW - NOT SHOWN ON LIVE SITE ***</p><?
	//echo $mail_sent_llc ? "LLC Mail sent<br />" : "LLC Mail failed<br />";
	//echo $mail_sent_cust ? "Customer Mail sent<br />" : "Customer Mail failed<br />";	
	?>
	<p>The Sage Pay Direct transaction has completed successfully and the customer has been returned to this order completion page<br>
		<br>
		The order number, for your customer's reference is: <span class="arrowbullets"><strong><? echo $strVendorTxCode ?></strong></span> <br>
		<br>
		They should quote this in all correspondence with you, and likewise you should use this reference when sending queries to Sage Pay about this transaction (along with your Sage Pay Vendor Name).<br>
		<br>
		The table below shows everything in the database about this order.  You would not normally show this level of detail to your customers, but it is useful during development.<br>
		<br>
		You can customise this page to send confirmation e-mails, display delivery times, present download pages, whatever is appropriate for your application.  The code is in orderSuccessful.php.
	</p>
												
	
	<table class="details">
		<tr>
			<td colspan="2" class="head">Order Details stored in your Database</td>
		</tr>
		<tr>
			<td>VendorTxCode:</td>
			<td><?=$strVendorTxCode?></td>
		</tr>
		<tr>
			<td>Transaction Type:</td>
			<td><?=$row["TxType"]?></td>
		</tr>
		<tr>
			<td>Status:</td>
			<td><?=$row["Status"]?></td>
		</tr>
		<tr>
		<td>Amount:</td>
		<td><?=number_format($row["total_price"],2) . " " . $strCurrency; ?></td>
	</tr>
	<tr>
		<td>Billing Name:</td>
		<td><?=$row["firstname"] . " " . $row["lastname"]; ?></td>
	</tr>
	<tr>
		<td>Billing Phone:</td>
		<td><?=$row["phone"]; ?>&#160;</td>
	</tr>
	<tr>
		<td style="vertical-align:top">Billing Address:</td>
		<td><?=$row["address1"] ?><br />
			<? if (isset($row["address2"])&&$row["address2"]!=null){ echo $row["address2"]. "<br />";} ?>
			<?=$row["city"] ?>&#160;
			<? if (isset($row["state"])) echo "<br />".get_county($row["state"]); ?>
			<br />
			<?=$row["postcode"]; ?><br />
			<script type="text/javascript" language="javascript">
				            document.write( getCountryName( "<? echo $row["country"]; ?>" ));
				        </script>
		</td>
	</tr>
	<tr>
		<td>Billing e-Mail:</td>
		<td><?=$row["email"] ?>&#160;</td>
	</tr>
	<tr>
		<td>Delivery Name:</td>
		<td><?=$row["alt_name"] ?></td>
	</tr>
	<tr>
		<td style="vertical-align:top">Delivery Address:</td>
		<td><?=$row["alt_address1"]; ?><br />
			<? if (isset($row["alt_address2"])&&$row["alt_address2"]!=null) {echo $row["alt_address2"] . "<br />"; }?>
			<?=$row["alt_city"]; ?>&#160;
			<? if (isset($row["alt_state"])) echo "<br />".get_county($row["alt_state"]); ?>
			<br />
			<? echo $row["alt_postcode"]; ?><br />
			<script type="text/javascript" language="javascript">
				            document.write( getCountryName( "<? echo $row["DeliveryCountry"]; ?>" ));
				        </script>
		</td>
	</tr>
	<tr>
		<td>Delivery Phone:</td>
		<td><?=$row["alt_phone"]; ?>&#160;</td>
	</tr>

		<tr>
			<td>VPSTxId:</td>
			<td><?=$row["VPSTxId"]?>&#160;</td>
		</tr>
		<tr>
			<td>SecurityKey:</td>
			<td><?=$row["SecurityKey"]?>&#160;</td>
		</tr>
		<tr>
			<td>VPSAuthCode (TxAuthNo):</td>
			<td><?=$row["TxAuthNo"]?>&#160;</td>
		</tr>
		<tr>
			<td>AVSCV2 Results:</td>
			<td><?=$row["AVSCV2"]?><span class=\"smalltext\"> - Address:<?=$row["AddressResult"]?> 
			, Post Code:<?=$row["PostCodeResult"]?>, CV2:<?=$row["CV2Result"]?></span></td>
		</tr>
		<tr>
			<td>Gift Aid Transaction?:</td>
			<td>
			<? if ($row["GiftAid"]==1) { echo "Yes"; } else { echo "No"; } ?>
			
			</td>
		</tr>
		<tr>
			<td>3D-Secure Status:</td>
			<td><?=$row["ThreeDSecureStatus"]?>&#160;</td>
		</tr>
		<tr>
			<td>CAVV:</td>
			<td><?=$row["CAVV"]?>&#160;</td>
		</tr>
		<tr>
			<td>Card Type:</td>
			<td><?=$row["CardType"]?>&#160;</td>
		</tr>
			<tr>
				<td>Address Status:</td>
				<td><span style=\"float:right; font-size: smaller;\">&#160;*PayPal transactions only</span><?=$row["AddressStatus"]?></td>
			</tr>
			<tr>
				<td>Payer Status:</td>
				<td><span style=\"float:right; font-size: smaller;\">&#160;*PayPal transactions only</span><?=$row["PayerStatus"]?></td>
			</tr>
			<tr>
				<td>PayerID:</td>
				<td><span style=\"float:right; font-size: smaller;\">&#160;*PayPal transactions only</span><?=$row["PayPalPayerID"]?></td>
			</tr>
	</table>
	<? ordercontents("o.VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'","100%");?>
	<? if(strlen($message)>0){?><br /><br />
	<h2>Email Content...</h2><?=str_replace("\r\n","<br />",$message)?>
	<? }
}
?>
<? 
$_SESSION['invoice']=$row['invoice'];
$_SESSION['date_ordered']=date("d/m/Y",$row['date_ordered']);
?>