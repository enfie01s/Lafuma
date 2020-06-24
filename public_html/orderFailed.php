<?
include("sagepay/includes.php");

/**************************************************************************************************
* Sage Pay Direct PHP Kit Order Failed Page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============

* 02/04/2009 - Simon Wolfe - Updated UI for re-brand
* 11/02/2009 - Simon Wolfe - Updated for VSP protocol 2.23
* 18/12/2007 - Nick Selby - New PHP version adapted from ASP
****************************************************************************************************
* Description
* ===========

* This is a placeholder for your Failed Order Completion Page.  It retrieves the VendorTxCode
* from the crypt string and displays the transaction results on the screen.  You wouldn't display 
* all the information in a live application, but during development this page shows everything
* sent back in the confirmation screen.
****************************************************************************************************/


//Now check we have a failure reason code passed to this page
$strVendorTxCode=$_SESSION["VendorTxCode"];
if (strlen($strVendorTxCode)==0){ 
	//No VendorTxCode, so take the customer to the home page
	ob_end_flush();
	session_destroy();
	redirect("$mainbase/index.php");
	exit();
}
else
{
	unset($_SESSION['cart']);
	unset($_SESSION['address_details']);
	unset($_SESSION['shipping']);
	unset($_SESSION['shipping_opt']);
	unset($_SESSION['terms_agree']);
	unset($_SESSION['discount_code']);
	unset($_SESSION['discount_amount']);
	unset($_SESSION['checkoutnew']);	
	$geterror=isset($_GET['error'])?$_GET['error']:0;
	$strSQL = "SELECT * FROM orders WHERE `VendorTxCode`='".$strVendorTxCode."'";
	$rsPrimary = mysql_query($strSQL)
		or die ("Query '$strSQL' failed with error message: '".mysql_error()."'");

	$row = mysql_fetch_array($rsPrimary);
	$strStatus=$row["Status"];
	
	//Work out what to tell the customer
	if (substr($strStatus,0,8)=="DECLINED")
		$strReason="You payment was declined by the bank.  This could be due to insufficient funds, or incorrect card details.";
	elseif (substr($strStatus,0,9)=="MALFORMED" || substr($strStatus,0,7)=="INVALID")
		$strReason="The Sage Pay Payment Gateway rejected some of the information provided without forwarding it to the bank.
		Please let us know about this error so we can determine the reason it was rejected.";
	elseif (substr($strStatus,0,8)=="REJECTED")
		$strReason="Your order did not meet our minimum fraud screening requirements.
		If you have questions about our fraud screening rules, or wish to contact us to discuss this.";
	elseif (substr($strStatus,5)=="ERROR")
		$strReason="We could not process your order because our Payment Gateway service was experiencing difficulties.";
	else
		$strReason="The transaction process failed.  Please contact us with the date and time of your order and we will investigate.";
}
?>

<h2>Your order has NOT been successful.</h2>
<p>Your transaction was not successful for the following reason:
	<br />
	<span class="warning"><strong>
	<?=$strReason ?>
	</strong></span><br />
</p>
<p>To complete your order, please contact our sales team on <?=$sales_phone?> and quote the invoice number (below).</p>
<h3>Your order details for Invoice Number <?=$row['invoice']?></h3>
<? ordercontents("o.VendorTxCode='".mysql_real_escape_string($strVendorTxCode)."'","100%");
if ($strConnectTo!=="LIVE")
{
	// NEVER show this level of detail when the account is LIVE
	?><p>*** TEST INFORMATION BELOW - NOT SHOWN ON LIVE SITE ***</p><?
	if (strlen($strVendorTxCode)>0){?>
	<p>The order number, for your customer's reference is: <strong><?=$strVendorTxCode?></strong><br />
	<br />
	They should quote this in all correspondence with you, and likewise you should use this reference when sending queries to Sage Pay about this transaction (along with your Sage Pay Vendor Name).<br />
	<br />
	The table below shows everything in the database about this order.  You would not normally show this level of detail to your customers, but it is useful during development.<br />
	<br />
	You can customise this page to offer alternative payment methods, links to customer support numbers, help and advice for online shopper, whatever is appropriate for your application.  The code is in orderFailed.php.
	</p>
	<? }
	$strSQL="SELECT * FROM orders WHERE `VendorTxCode`='".mysql_real_escape_string($strVendorTxCode)."'";
	//Execute the SQL command
	$rsPrimary = mysql_query($strSQL)
		or die ("Query '$strSQL' failed with error message: '".mysql_error()."'");
	$strSQL="";
	$row=mysql_fetch_array($rsPrimary);
	?>
	<table class="details">
	<tr>
		<td colspan="2" class="head">Order Details stored in your Database</td>
	</tr>
	<tr>
		<td>VendorTxCode:</td>
		<td><?=$strVendorTxCode; ?></td>
	</tr>
	<tr>
		<td>Transaction Type:</td>
		<td><?=$row["TxType"]; ?></td>
	</tr>
	<tr>
		<td>Status:</td>
		<td><?=$row["Status"]; ?></td>
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
		<td><?=$row["phone"]; ?>
			&#160;</td>
	</tr>
	<tr>
		<td style="vertical-align:top">Billing Address:</td>
		<td><?=$row["address1"] ?>
			<br />
			<? if (isset($row["address2"])&&$row["address2"]!=null){ echo $row["address2"]. "<br />";} ?>
			<?=$row["city"] ?>
			&#160;
			<? if (isset($row["state"])) echo "<br />".get_county($row["state"]); ?>
			<br />
			<?=$row["postcode"]; ?>
			<br />
			<script type="text/javascript" language="javascript">
										document.write( getCountryName( "<? echo $row["country"]; ?>" ));
								</script>
		</td>
	</tr>
	<tr>
		<td>Billing e-Mail:</td>
		<td><?=$row["email"] ?>
			&#160;</td>
	</tr>
	<tr>
		<td>Delivery Name:</td>
		<td><?=$row["alt_name"] ?></td>
	</tr>
	<tr>
		<td style="vertical-align:top">Delivery Address:</td>
		<td><?=$row["alt_address1"]; ?>
			<br />
			<? if (isset($row["alt_address2"])&&$row["alt_address2"]!=null) {echo $row["alt_address2"] . "<br />"; }?>
			<?=$row["alt_city"]; ?>
			&#160;
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
		<td><?=$row["alt_phone"]; ?>
			&#160;</td>
	</tr>
	<tr>
		<td>Postage Option:</td>
		<td><?=$row["ship_option"]; ?>
			&#160;</td>
	</tr>
	<tr>
		<td>VPSTxId:</td>
		<td><?=$row["VPSTxId"]; ?>
			&#160;</td>
	</tr>
	<tr>
		<td>SecurityKey:</td>
		<td><?=$row["SecurityKey"]; ?>
			&#160;</td>
	</tr>
	<tr>
		<td>VPSAuthCode (TxAuthNo):</td>
		<td><?=$row["TxAuthNo"]; ?>
			&#160;</td>
	</tr>
	<tr>
		<td>AVSCV2 Results:</td>
		<td>
			<?=((!isset($row["AVSCV2"]))?"-":$row["AVSCV2"])?><span class="smalltext">(Address: <?=((!isset($row["AddressResult"]))?"-":$row["AddressResult"])?>, Post Code: <?=((!isset($row["PostCodeResult"]))?"-":$row["PostCodeResult"])?>, CV2: <?=((!isset($row["CV2Result"]))?"-":$row["CV2Result"])?>)</span>
		</td>
	</tr>
	<tr>
		<td>Gift Aid Transaction?:</td>
		<td><?=(($row["GiftAid"]==1)?"Yes":"No")?></td>
	</tr>
	<tr>
		<td>3D-Secure Status:</td>
		<td><? echo $row["ThreeDSecureStatus"] ?>&#160;</td>
	</tr>
	<tr>
		<td>CAVV:</td>
		<td><? echo $row["CAVV"] ?>&#160;</td>
	</tr>
	<tr>
		<td>Card Type:</td>
		<td><? echo $row["CardType"] ?>&#160;</td>
	</tr>
	<tr>
		<td>Last 4 Digits:</td>
		<td><? echo $row["Last4Digits"] ?>&#160;</td>
	</tr>
</table>
<?
	}
?>



