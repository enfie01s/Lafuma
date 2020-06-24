<?
include("sagepay/includes.php");
 
/**************************************************************************************************
* Sage Pay Direct PHP Kit 3D-Completion Page
***************************************************************************************************
*
***************************************************************************************************
* Change history
* ==============
*
* 11/02/2009 - Simon Wolfe - Updated for VSP protocol 2.23
* 18/12/2007 - Nick Selby - New PHP kit version adapted from ASP
***************************************************************************************************
* Description
* ===========
*
* This page is the 3D-Secure completion page that redeives the MD and PaRes from the Issuing Bank 
* site, POSTs it to Sage Pay, then reads the authorisation response and updates the database accordingly.
***************************************************************************************************/
if ($_REQUEST["navigate"]=="proceed") {
	//The user wants to proceed to the confirmation page.  Send them there
	redirect($_REQUEST["CompletionURL"]);
	exit();
}

$strCart=$_SESSION["cart"];

//Otherwise, create the POST for Sage Pay ensuring to URLEncode the PaRes before sending it
$strMD = $_REQUEST["MD"];
$strPaRes=$_REQUEST["PARes"];
$strVendorTxCode=$_SESSION["VendorTxCode"];
// POST for Sage Pay Direct 3D completion page
$strPost = "MD=" . $strMD . "&PARes=" . urlencode($strPaRes);

//Use cURL to POST the data directly from this server to Sage Pay. cURL connection code is in includes.php.
$arrResponse = requestPost($str3DCallbackPage, $strPost);
	  
//Analyse the response from Sage Pay Direct to check that everything is okay
$arrStatus=split(" ",$arrResponse["Status"]);
$strStatus=array_shift($arrStatus);
$arrStatusDetail=split("=",$arrResponse["StatusDetail"]);
$strStatusDetail = array_shift($arrStatusDetail);
		
//Get the results form the POST if they are there
$arrVPSTxId=split(" ",$arrResponse["VPSTxId"]);
$strVPSTxId=array_shift($arrVPSTxId);
$arrSecurityKey=split(" ",$arrResponse["SecurityKey"]);
$strSecurityKey=array_shift($arrSecurityKey);
$arrTxAuthNo=split(" ",$arrResponse["TxAuthNo"]);
$strTxAuthNo=array_shift($arrTxAuthNo);
$arrAVSCV2=split(" ",$arrResponse["AVSCV2"]);
$strAVSCV2=array_shift($arrAVSCV2);
$arrAddressResult=split(" ",$arrResponse["AddressResult"]);
$strAddressResult=array_shift($arrAddressResult);
$arrPostCodeResult=split(" ",$arrResponse["PostCodeResult"]);
$strPostCodeResult=array_shift($arrPostCodeResult);
$arrCV2Result=split(" ",$arrResponse["CV2Result"]);
$strCV2Result=array_shift($arrCV2Result); 
$arr3DSecureStatus=split(" ",$arrResponse["3DSecureStatus"]);
$str3DSecureStatus=array_shift($arr3DSecureStatus);
$arrCAVV=split(" ",$arrResponse["CAVV"]);
$strCAVV=array_shift($arrCAVV);

//Update the database and redirect the user appropriately
if ($strStatus=="OK")
	$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
elseif ($strStatus=="MALFORMED")
	$strDBStatus="MALFORMED - The StatusDetail was: " . mysql_real_escape_string(substr($strStatusDetail,0,255));
elseif ($strStatus=="INVALID")
	$strDBStatus="INVALID - The StatusDetail was: " . mysql_real_escape_string(substr($strStatusDetail,0,255));
elseif ($strStatus=="NOTAUTHED")
	$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
elseif ($strStatus=="REJECTED")
	$strDBStatus="REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
elseif ($strStatus=="AUTHENTICATED")
	$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
elseif ($strStatus=="REGISTERED")
	$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
elseif ($strStatus=="ERROR")
	$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . mysql_real_escape_string($strStatusDetail);
else
	$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . mysql_real_escape_string($strStatus) . ", with StatusDetail:" . mysql_real_escape_string($strStatusDetail);
	
$strSQL="UPDATE orders SET `Status`='" . mysql_real_escape_string($strDBStatus) . "'";
if (strlen($strVPSTxId)>0) $strSQL.=",`VPSTxId`='" . mysql_real_escape_string($strVPSTxId) . "'";
if (strlen($strSecurityKey)>0) $strSQL.=",`SecurityKey`='" . mysql_real_escape_string($strSecurityKey) . "'";
if (strlen($strTxAuthNo)>0) $strSQL.=",`TxAuthNo`='" . mysql_real_escape_string($strTxAuthNo)."'";
if (strlen($strAVSCV2)>0) $strSQL.=",`AVSCV2`='" . mysql_real_escape_string($strAVSCV2) . "'";
if (strlen($strAddressResult)>0) $strSQL.=",`AddressResult`='" . mysql_real_escape_string($strAddressResult) . "'";
if (strlen($strPostCodeResult)>0) $strSQL.=",`PostCodeResult`='" . mysql_real_escape_string($strPostCodeResult) . "'";
if (strlen($strCV2Result)>0) $strSQL.=",`CV2Result`='" . mysql_real_escape_string($strCV2Result) . "'";
if (strlen($strGiftAid)>0) $strSQL.=",`GiftAid`='" . mysql_real_escape_string($strGiftAid)."'";
if (strlen($str3DSecureStatus)>0) $strSQL.=",`ThreeDSecureStatus`='" . mysql_real_escape_string($str3DSecureStatus) . "'";
if (strlen($strCAVV)>0) $strSQL.=",`CAVV`='" . mysql_real_escape_string($strCAVV) . "'";
$strSQL.=",`pay_method`='cc'";
$strSQL.=",`pay_status`='".(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?1:0)."'";
$strSQL.=",`order_status`='New'"; 
$strSQL.=" WHERE `VendorTxCode`='" . mysql_real_escape_string($strVendorTxCode) . "'";
							
$rsPrimary = mysql_query($strSQL)
	or die (lamysql_error("Query failed","'$strSQL' failed with error message: \"" . mysql_error () . '"'));

$strSQL="";
$rsPrimary="";

//Work out where to send the customer
$_SESSION["VendorTxCode"]=$strVendorTxCode;
if ($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")
	$strCompletionURL="index.php?p=orderSuccessful";
else {
	$strCompletionURL="index.php?p=orderFailed";
	$strPageError=$strDBStatus;
}
	
//Finally, if we're in LIVE then go straight to the success page
//In other modes, we allow this page to display and ask for Proceed to be clicked
if ($strConnectTo=="LIVE"){
	ob_end_flush();
	redirect($strCompletionURL);
}


?>
<script type="text/javascript" language="javascript" src="sagepay/scripts/common.js" ></script>
<?
if(strlen($strPageError)!=0){
	?>
	<h2>Your order has <span style="text-decoration:underline">NOT</span> been successful.</h2>
	<p>Your transaction was not successful for the following reason:
		<br />
		<span class="warning"><strong><?=$strPageError?></strong></span><br />
	</p>
	<p>To complete your order, please contact our sales team on <?=$sales_phone?> and quote the invoice number above.</p>
	<h3>Your order contents</h3>
	<? ordercontents("o.VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'","100%");
	unset($_SESSION['cart']);
	unset($_SESSION['address_details']);
	unset($_SESSION['shipping']);
	unset($_SESSION['terms_agree']);
	unset($_SESSION['discount_code']);
	unset($_SESSION['discount_amount']);
}		
if ($strConnectTo!=="LIVE") { 
?><p>*** TEST INFORMATION BELOW - NOT SHOWN ON LIVE SITE ***</p><?
//NEVER show this level of detail when the account is LIVE%>
	if (strlen($strPageError)==0){
	ordercontents("o.VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'","100%");?>		
	<br />	
	<form name="customerform" method="POST">
	<input type="hidden" name="navigate" value="" />
	<input type="hidden" name="CompletionURL" value="<?=$strCompletionURL ?>" />
	<input type="hidden" name="PageState" value="Completion" />
	<div class="formFooter">
	<a href="javascript:submitForm('customerform','back');" title="Go back to the order confirmation page" style="float: left;">Back</a>
	<a href="javascript:submitForm('customerform','proceed');" title="Proceed to the completion screens" style="float: right;">Proceed</a>
	</div>
	</form>
	<br /><br />
	<?
	if (strlen($strPageError)==0)
	{ 
	//There are no errors to display, so show the detail of the POST to Sage Pay Direct							
	?><p>This page shows the contents of the POST sent to Sage Pay Direct (based on your selections on the previous screens)
	and the response sent back by the system. Because you are in SIMULATOR mode, you are seeing this information
	and having to click Proceed to continue to the payment pages. In LIVE mode, the POST and redirect 
	happen invisibly, with no information sent to the browser and no user involvement.</p><?
	}
	else
	{
	//An error occurred during transaction registration. Show the details here
	?><p>A problem occurred whilst attempting to register this transaction with the Sage Pay systems.
	The details of the error are shown below. This information is provided for your own debugging 
	purposes and especially once LIVE you should avoid displaying this level of detail to your customers. 
	Instead you should modify the transactionRegistration.php page to automatically handle these errors and 
	redirect your customer appropriately (e.g. to an error reporting page, or alternative customer 
	services number to offline payment)</p><?
	}
	
	?>
	<div class="<?=(($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED")?"infoheader":"errorheader")?>">Sage Pay Direct returned a Status of <?=$strStatus?><br /><span class="warning" ><?=$strPageError?></span>
	</div>
		
	<table class="details">
	<tr>
		<td colspan="2" class="head">Post Sent to Sage Pay Direct</td>
	</tr>
	<tr>
		<td colspan="2" class="code"><?=$strPost?></td>
	</tr>
	<tr>
		<td colspan="2" class="head">Reply from Sage Pay Direct</td>
	</tr>   
	<tr>
		<td colspan="2" class="code"><?= $_SESSION["rawresponse"] ?></td>
	</tr>
	<tr>
		<td colspan="2" class="head">Order Details stored in your Database</td>
	</tr>
	<tr>
		<td>VendorTxCode:</td>
		<td><?= $strVendorTxCode?></td>
	</tr>
	<tr>
		<td>VPSTxId:</td>
		<td><?= $strVPSTxId ?></td>
	</tr>
	<? if (strlen($strSecurityKey)>0) {?>
	<tr>
		<td>SecurityKey:</td>
		<td><?= $strSecurityKey ?></td>
	</tr>
	<? }
	if (strlen($strTxAuthNo)>0) {?>
	
	<tr>
		<td>TxAuthNo:</td>
		<td><?= $strTxAuthNo ?></td>
	</tr>
	<?
	}
	if (strlen($str3DSecureStatus)>0) {?>
	<tr>
		<td>3D-Secure Status:</td>
		<td><?= $str3DSecureStatus ?></td>
	</tr><?
	}
	if (strlen($strCAVV)>0) {?>
	<tr>
		<td>CAVV:</td>
		<td><?= $strCAVV ?></td>
	</tr><?
	}?>
	</table>
	<?
}}
$strSQL="";
$rsPrimary="";
mysql_close();
?>
