<?

error_reporting(-1);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
$strVendorTxCode='lafuma-120525212934-176462720';
//echo phpinfo();
$username = "lafuma";
$password = "Klsjfsd873sdd";
$database = "lafuma_main";
$host = "localhost";
$db=mysql_connect("localhost", "$username", "$password") or die(mysql_error()); 
@mysql_select_db("$database",$db) or die(mysql_error());
$strSQL="SELECT * FROM orders WHERE `VendorTxCode`='lafuma-120525212934-176462720'";
//Execute the SQL command
$rsPrimary = mysql_query($strSQL)
or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
$strSQL="";
$row=mysql_fetch_array($rsPrimary);
$random_hash = md5(date('r', time())); 
$rowescape=mysql_real_extracted($row);
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
Payment Method: Credit/Debit Card\r\n
Postage Method: <?=htmlentities($row['ship_description'],ENT_QUOTES,"ISO-8859-15")?>\r\n
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
$sstring="SELECT `qty`,`prod_id`,`order_prod_id`,`sku`,`oitem`,`oname`,`title`,op.`price` as price,o.`discount` as odiscount, op.`discount` as opdiscount,`discount_code`,`ship_description`,`ship_total`,`total_price`,`tax_rate`,`tax_price`,`fusionId`,`goptid`,`exclude_discount` FROM orders as o,orderproducts as op LEFT JOIN fusion as f ON op.`prod_id`=f.`iSubId_FK` WHERE o.`VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "' AND o.`order_id`=op.`order_id` GROUP BY op.`order_prod_id`";

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
Price(ex. VAT): &pound;<?=number_format($order['price'],2)?>\r\n
Sub Total: &pound;<?=number_format($order['price']*$order['qty'],2)?>\r\n

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
Sub Total: &pound;<?=number_format($runtotal,2)?>\r\n
<? if(strlen($odiscountcode)>0&&$odiscountcode!="discount code"){?>
<?=$odiscount?>% Discount (<?=$odiscountcode?>): - &pound;<?=number_format($discount,2)?>\r\n
<? }?>
VAT @<?=$otaxrate?>%: &pound;<?=number_format($otaxprice,2)?>\r\n
Postage <?=htmlentities($oshipdesc,ENT_QUOTES,"ISO-8859-15")?>: &pound;<?=number_format($oshiptotal,2)?>\r\n\r\n
Total: &pound;<?=number_format($ototalprice,2)?>
<? /*order contents */?>

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
Company Registration No.: <?=$coreg?>

Thank you for your order, we appreciate your custom.
If you could spare a few moments, we would be very grateful if you could add review(s) of the products you have purchased.

If for any reason you are unhappy with your purchase, please contact us at <?=$admin_email?>.

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
<p class="note">Credit/Debit Card</p>
</td>
<td width="50%" style="vertical-align:top;">
<h3>Postage Method</h3>
<p class="note"<? if($row['ship_description']=="Saturday Delivery"){?> style='font-weight:bold;'<? }?>>
<?=$row['ship_description']?>
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
<p>
Thank you for your order, we appreciate your custom.<br />
If you could spare a few moments, we would be very grateful if you could add review(s) of the products you have purchased.<br /><br />
If for any reason you are unhappy with your purchase, please contact us at <?=$admin_email?>.
</p>
</body></html>
--PHP-alt-<?php echo $random_hash; ?>--
<?
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();
$headers = "From: Lafuma UK <".$admin_email.">\r\nReply-To: ".$admin_email;
$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
//$sat=$row['ship_description']=="Saturday Delivery"?" - Saturday Delivery":"";
$to_llc_orders="senfield@gmk.co.uk";

//$mail_sent_llc = mail( $to_llc_orders, "TEST EMAIL", $message, $headers );	
$mail_sent_cust = mail( $to_llc_orders, "Thank You for your order at Lafuma UK", $message, $headers );
//echo $message;
//if($mail_sent_llc){echo "all sent";}
/* /SEND EMAILS OUT*/
?>