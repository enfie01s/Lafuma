<?
include "../content/config.php";
include "../content/functions.php";
include("includes.php");

/**************************************************************************************************
* Sage Pay Direct PHP Transaction Registration Page
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============

* 02/04/2009 - Simon Wolfe - Updated UI for re-brand
* 11/02/2009 - Simon Wolfe - Created for VSP protocol 2.23. Adapted from ASP version.
***************************************************************************************************
* Description
* ===========

* This page handles the PayPal callback POSTs from Sage Pay Direct after the PayPal Authentication, as
* well as the final completion request to accept/cancel the paypal transaction. This page & process
* will only be called upon for transations where the customer has chosen a PayPal payment method and
* your Sage Pay account has this feature enabled. This page should be made externally visible so that 
* Sage Pay Direct servers can send messages to here over either HTTP or HTTPS.
***************************************************************************************************/

// Check for the proceed button click, this will go through to either a success or failure page 
if ($_REQUEST["navigate"]=="proceed") 
{
	// Retrieve the VPSTxId from the session. We need this to complete the transaction  
	$strVPSTxId = $_SESSION["VPSTxId"];
	if (strlen($strVPSTxId) == 0) 
	{
		errorRedirect("your session timed out");
	}
	else
	{
		// Using the VPSTxId we can retrieve our order transaction amount from our database.
		// This ensures we have the correct amount and its a valid existing order.
		$strSQL = "SELECT total_price FROM orders WHERE VPSTxId = '" . mysql_real_escape_string($strVPSTxId) . "'";
		$rsPrimary = mysql_query($strSQL) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
		if ($row = mysql_fetch_array($rsPrimary))
		{
			$strAmount = $row["total_price"];	
			// The status indicates Ok to try to POST the completion request to Sage Pay Direct 
			if ($_REQUEST["status"] == "PAYPALOK")
			{
				postCompletionRequest();
			}
			else
			{
				errorRedirect($_SESSION["StatusDetail"]);
			}
		}
		// else the order was NOT found in the database then we can't complete the transaction  
		else
		{        
			errorRedirect("there is no record of your order in our system"); 
		}
	}
} 
else
{
	// Information is POSTed to this page from Sage Pay Direct. The POST will ALWAYS contain the Status and StatusDetail fields.  
	// We'll extract these first and use them to decide how to respond to the POST. 
	$strStatus = cleanInput($_REQUEST["Status"], "Text");
	$strStatusDetail = cleanInput($_REQUEST["StatusDetail"], "Text");
	$strVPSTxId = cleanInput($_REQUEST["VPSTxId"], "Text");
	$_SESSION["VPSTxId"] = $strVPSTxId;
	$_SESSION["Status"] = $strStatus;
	$_SESSION["StatusDetail"] = $strStatusDetail;
	
	// If we don't have a VPSTxId and Status from the incoming request POST then we can't complete the transaction  
	if ((strlen($strVPSTxId) == 0) || (strlen($strStatus) == 0))
	{
		errorRedirect("the response from PayPal was invalid");
	}
	else
	{
		// Using the VPSTxId we can retrieve our order transaction amount from our database 
		// This ensures we have the correct amount and its a valid existing order.
		$strSQL = "SELECT * FROM orders WHERE VPSTxId = '" . mysql_real_escape_string($strVPSTxId) . "'";
		$rsPrimary = mysql_query($strSQL) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
		if ($row = mysql_fetch_array($rsPrimary))
		{
			$strAmount = $row["total_price"];
			$strVendorTxCode = $row["VendorTxCode"];
			$_SESSION["VendorTxCode"] = $strVendorTxCode;
		}
		
		// If order was NOT found in the database then we can't complete the transaction  
		if (strlen($strAmount) == 0) 
		{
			errorRedirect("there is no record of your order in our system");
		}
		// Check the status returned from Sage Pay Direct and take appropriate actions. 
		// If Status is Ok then you can decide if to proceed or cancel transtion based on results 
		// of the AddressStatus and PayerStatus. If Status is NOT ok then you will not be able to 
		// complete paypal transction. 
		elseif ($strStatus == "PAYPALOK")
		{
			// Great, the customer has completed the paypal checkout successfully. 
			// Extract the values from the response and update our database with the results 
			// from the request and build a completion post request to Sage Pay Direct. 
			$strVPSTxId = cleanInput($_REQUEST["VPSTxId"], "Text");
			$strAddressStatus = cleanInput($_REQUEST["AddressStatus"], "Text");
			$strPayerStatus = cleanInput($_REQUEST["PayerStatus"], "Text");
			$strDeliverySurname = cleanInput($_REQUEST["DeliverySurname"], "Text");
			$strDeliveryFirstnames = cleanInput($_REQUEST["DeliveryFirstnames"], "Text");
			$strDeliveryAddress1 = cleanInput($_REQUEST["DeliveryAddress1"], "Text");
			$strDeliveryAddress2 = cleanInput($_REQUEST["DeliveryAddress2"], "Text");
			$strDeliveryCity = cleanInput($_REQUEST["DeliveryCity"], "Text");
			$strDeliveryPostCode = cleanInput($_REQUEST["DeliveryPostCode"], "Text");
			$strDeliveryCountry = cleanInput($_REQUEST["DeliveryCountry"], "Text");
			$strDeliveryState = cleanInput($_REQUEST["DeliveryState"], "Text");
			$strDeliveryPhone = cleanInput($_REQUEST["DeliveryPhone"], "Text");
			$strCustomerEmail = cleanInput($_REQUEST["CustomerEmail"], "Text");
			$strPayerID = cleanInput($_REQUEST["PayerID"], "Text");
			
			// Store the details in our database 
			updateDatabase();
			////
			// Retrieve the VPSTxId from the session. We need this to complete the transaction  
			$strVPSTxId = $_SESSION["VPSTxId"];
			if (strlen($strVPSTxId) == 0) 
			{
				errorRedirect("your session timed out");
			}
			else
			{
				// Using the VPSTxId we can retrieve our order transaction amount from our database.
				// This ensures we have the correct amount and its a valid existing order.
				$strSQL = "SELECT total_price FROM orders WHERE VPSTxId = '" . mysql_real_escape_string($strVPSTxId) . "'";
				$rsPrimary = mysql_query($strSQL) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
				if ($row = mysql_fetch_array($rsPrimary))
				{
					$strAmount = $row["total_price"];	
					// The status indicates Ok to try to POST the completion request to Sage Pay Direct 
					postCompletionRequest();
					
				}
				// else the order was NOT found in the database then we can't complete the transaction  
				else
				{        
					errorRedirect("there is no record of your order in our system"); 
				}
			}
			////
		}
		else // "ERROR" "MALFORMED" "INVALID"
		{
			$strPageError = $strStatusDetail;		
			// Update the status in database 
			updateDatabase();
			errorRedirect($strPageError);
			return;
		}
	}   
}


function postCompletionRequest()
{
	global $securebase;
	// Here we will proceed with accepting the transaction, however you should check the 
	// Address Status and Payer Status returned from PayPal and before choosing if to  
	// proceed with either accepting or cancelling transactions. 
	
	// Now to build the Sage Pay Direct POST for the PayPal Completion.  For more details see the Sage Pay Server Protocol 2.23 
	// NB: Fields potentially containing non ASCII characters are URLEncoded when included in the POST 
	$strPost = "VPSProtocol=" . urlencode($GLOBALS["strProtocol"]);
	$strPost = $strPost . "&TxType=COMPLETE";
	$strPost = $strPost . "&VPSTxId=" . urlencode($GLOBALS["strVPSTxId"]);
	$strPost = $strPost . "&Amount=" . urlencode(number_format($GLOBALS["strAmount"],2));
	$strPost = $strPost . "&Accept=YES";
	
	/* Send the post to the target URL
	** if anything goes wrong with the connection process:
	** - $arrResponse["Status"] will be 'FAIL';
	** - $arrResponse["StatusDetail"] will be set to describe the problem 
	** Data is posted to $strPayPalRedirectURL which is set in the includes file. */
	$arrResponse = requestPost($GLOBALS["strPayPalCompletionURL"], $strPost);
	
	/* Analyse the response from Sage Pay Direct to check that everything is okay
	** Registration results come back in the Status and StatusDetail fields */
	$GLOBALS["strStatus"] = $arrResponse["Status"];
	$GLOBALS["strStatusDetail"] = $arrResponse["StatusDetail"];
	$GLOBALS["strTxAuthNo"] = $arrResponse["TxAuthNo"];
	
	// Check for network/transport errors
	if ($GLOBALS["strStatus"] == "FAIL")   
	{
		$GLOBALS["strPageError"]="An Error has occurred whilst trying to post the PayPal completion request to Sage Pay.<BR>
		Check that you do not have a firewall restricting the POST and that your server 
		can correctly resolve the address " . $GLOBALS["strPayPalCompletionURL"] . "<BR>
		The Description given is: " . $GLOBALS["strStatusDetail"];
		
		// Update our database with the status
		$strSQL="UPDATE orders SET Status='" .  mysql_real_escape_string($GLOBALS["strStatus"]) . " - PAYPAL COMPLETION FAILED: " . mysql_real_escape_string($GLOBALS["strStatusDetail"]) . "'";
		$strSQL=$strSQL . " WHERE VPSTxId='" . mysql_real_escape_string($GLOBALS["strVPSTxId"]) . "'";
		
		mysql_query($strSQL) or die ("Query failed");
		errorRedirect($GLOBALS["strStatusDetail"]);
		exit();
	}
	else
	{
		// No transport level errors, so the message got the Sage Pay 
		
		// Update our database with the results from the response POST 
		$strSQL="UPDATE orders SET Status='" .  mysql_real_escape_string($GLOBALS["strStatus"]) . " - " . mysql_real_escape_string($GLOBALS["strStatusDetail"]) . "'";
		
		if (strlen($GLOBALS["strTxAuthNo"])>0) 
		{ 
			$strSQL=$strSQL . ",TxAuthNo=" . mysql_real_escape_string($GLOBALS["strTxAuthNo"]);
		}
		$strSQL=$strSQL . ",`pay_status`='1' WHERE VPSTxId='" . mysql_real_escape_string($GLOBALS["strVPSTxId"]) . "'";
		
		mysql_query($strSQL) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
		
		// Now decide where to redirect the customer 
		if ($GLOBALS["strStatus"]=="OK"||$GLOBALS["strStatus"]=="PAYPALOK")
		{	// If a transaction status is OK then we should send the customer to the success page 
			$strRedirectPage=$securebase."/index.php?p=orderSuccessful"; 
		}
		else
		{	// The status indicates a failure of one state or another, so send the customer to orderFailed instead 
			errorRedirect($GLOBALS["strStatusDetail"]);
			return;
			//echo $GLOBALS["strStatus"];
		}
		
		ob_end_flush();
		redirect($strRedirectPage);
		exit();
	}
}


function errorRedirect($strErrorMessage)
{
	global $securebase;
	// We cannot find a record of this order in the database, or the session has expired and lost our VPSTxId  
	// or the callback post is malformed and the requried information is missing. 
	// We will NOT post a completion message to Sage Pay Direct so this will prevent the authorisation of the transaction. 
	// Redirect customer to our orderFailure page, passing details of the error in the Session so that the 
	// page knows how to respond to the customer. 
	
	$_SESSION["ErrorMessage"] = $strErrorMessage;
	ob_end_flush();
	redirect($securebase."/index.php?p=orderFailed");
	exit();
}


// Updates our database with the results from the PayPal callback response 
function updateDatabase()
{    
	$strSQL="UPDATE orders set Status='". ($_SESSION['test']==1?"TESTING - ":"") . $GLOBALS["strStatus"] . " - " . $GLOBALS["strStatusDetail"] . "'";
	if (strlen($GLOBALS["strAddressStatus"])>0) $strSQL=$strSQL . ",AddressStatus='" . mysql_real_escape_string($GLOBALS["strAddressStatus"]) . "'";
	if (strlen($GLOBALS["strPayerStatus"])>0) $strSQL=$strSQL . ",PayerStatus='" . mysql_real_escape_string($GLOBALS["strPayerStatus"]) . "'";
	if (strlen($GLOBALS["strPayerID"])>0) $strSQL=$strSQL . ",PayPalPayerID='" . mysql_real_escape_string($GLOBALS["strPayerID"]) . "'";
	if (strlen($GLOBALS["strDeliveryFirstnames"])>0||strlen($GLOBALS["strDeliverySurname"])>0)
	{
		$alt_name="";
		$alt_name.=strlen($GLOBALS["strDeliveryFirstnames"])>0?$GLOBALS["strDeliveryFirstnames"]:"";
		$alt_name.=strlen($GLOBALS["strDeliverySurname"])>0&&strlen($GLOBALS["strDeliveryFirstnames"])>0?" ":"";
		$alt_name.=strlen($GLOBALS["strDeliverySurname"])>0?$GLOBALS["strDeliverySurname"]:"";
		$strSQL=$strSQL . ",alt_name='" . mysql_real_escape_string($alt_name) . "'";
	}
	if (strlen($GLOBALS["strDeliveryAddress1"])>0) $strSQL=$strSQL . ",alt_address1='" . mysql_real_escape_string($GLOBALS["strDeliveryAddress1"]) . "'";
	if (strlen($GLOBALS["strDeliveryAddress2"])>0) $strSQL=$strSQL . ",alt_address2='" . mysql_real_escape_string($GLOBALS["strDeliveryAddress2"]) . "'";
	if (strlen($GLOBALS["strDeliveryCity"])>0) $strSQL=$strSQL . ",alt_city='" . mysql_real_escape_string($GLOBALS["strDeliveryCity"]) . "'";
	if (strlen($GLOBALS["strDeliveryPostCode"])>0) $strSQL=$strSQL . ",alt_postcode='" . mysql_real_escape_string($GLOBALS["strDeliveryPostCode"]) . "'";
	if (strlen($GLOBALS["strDeliveryCountry"])>0) $strSQL=$strSQL . ",alt_country='" . mysql_real_escape_string($GLOBALS["strDeliveryCountry"]) . "'";
	if (strlen($GLOBALS["strDeliveryState"])>0) $strSQL=$strSQL . ",alt_state='" . mysql_real_escape_string($GLOBALS["strDeliveryState"]) . "'";
	if (strlen($GLOBALS["strDeliveryPhone"])>0) $strSQL=$strSQL . ",alt_phone='" . mysql_real_escape_string($GLOBALS["strDeliveryPhone"]) . "'";
	if (strlen($GLOBALS["strCustomerEMail"])>0) $strSQL=$strSQL . ",email='" . mysql_real_escape_string($GLOBALS["strCustomerEMail"]) . "'";
	$strSQL.=",`pay_status`='".($GLOBALS["strStatus"]=="PAYPALOK"?1:0)."'";
	$strSQL=$strSQL . " where VPSTxId='" . mysql_real_escape_string($GLOBALS["strVPSTxId"]) . "'";
	mysql_query($strSQL) or die ("SQL failure");
}
?>
<html>
	<head>
	<title>Direct PHP PayPal Callback Page</title>
	<link rel="STYLESHEET" type="text/css" href="images/directKitStyle.css">
	<script type="text/javascript" language="javascript" src="scripts/common.js" ></script>
	</head>
	<body <? if($strConnectTo == "LIVE"){?>onload="javascript:submitForm('mainForm','proceed');"<? }?>>
<?
if ($strConnectTo != "LIVE")
{
	// NEVER show this level of detail when the account is LIVE
	?>	
	<div id="pageContainer">
	<div id="content">
	<div id="contentHeader">PayPal Callback Page</div>
	<? 
	if (strlen($strPageError)==0) 
	{
		// There are no errors to display, so show the detail of the POST to Sage Pay Direct
		?>
		<p>Success... This is in <?=$strConnectTo?> mode.</p>
		<?
	} 
	else 
	{
		// An error occurred during transaction registration.  Show the details here
		?>
		<p>Fail... This is in <?=$strConnectTo?> mode.</p>
		<? 
	}
	?>
	<div class="greyHzShadeBar">&#160;</div>
	<div class="<?
	if ($strStatus=="PAYPALOK")
		echo "infoheader";
	else 
		echo "errorheader";
	?>" align="center">Sage Pay Direct returned a Status of <? echo $strStatus ?><br>
	<span class="warning"><? echo $strPageError ?></span> </div>
	
	<table class="formTable">
	<tr>
	<td colspan="2"><div class="subheader">Reply from Sage Pay Direct</div></td>
	</tr>
	<tr>
	<td class="fieldLabel">Status:</td>
	<td class="fieldData"><? echo $strStatus ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">StatusDetail:</td>
	<td class="fieldData"><? echo $strStatusDetail ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">VPSTxId:</td>
	<td class="fieldData"><? echo $strVPSTxId ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">AddressStatus:</td>
	<td class="fieldData"><? echo $strAddressStatus ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">PayerStatus:</td>
	<td class="fieldData"><? echo $strPayerStatus ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliverySurname:</td>
	<td class="fieldData"><? echo $strDeliverySurname ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryFirstnames:</td>
	<td class="fieldData"><? echo $strDeliveryFirstnames ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryAddress1:</td>
	<td class="fieldData"><? echo $strDeliveryAddress1 ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryAddress2:</td>
	<td class="fieldData"><? echo $strDeliveryAddress2 ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryCity:</td>
	<td class="fieldData"><? echo $strDeliveryCity ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryPostCode:</td>
	<td class="fieldData"><? echo $strDeliveryPostCode ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryCountry:</td>
	<td class="fieldData"><? echo $strDeliveryCountry ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryState:</td>
	<td class="fieldData"><? echo $strDeliveryState ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">DeliveryPhone:</td>
	<td class="fieldData"><? echo $strDeliveryPhone ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">CustomerEmail:</td>
	<td class="fieldData"><? echo $strCustomerEmail ?>&#160;</td>
	</tr>
	<tr>
	<td class="fieldLabel">PayerID:</td>
	<td class="fieldData"><? echo $strPayerID ?>&#160;</td>
	</tr>
	<tr>
	<td colspan="2"><div class="subheader">Order Details stored in your Database</div></td>
	</tr>
	<tr>
	<td class="fieldLabel">VendorTxCode:</td>
	<td class="fieldData"><? echo $strVendorTxCode ?></td>
	</tr>
	<tr>
	<td class="fieldLabel">Order Total:</td>
	<td class="fieldData"><? echo number_format($strAmount,2) . " " . $strCurrency ?>&#160;</td>
	</tr>
	</table>
	<?
	ordercontents("o.`VendorTxCode`='$strVendorTxCode'","100%");
	?>
	<div class="greyHzShadeBar">&#160;</div>
	<div class="formFooter">
	</div>
	</div>
	</div>
	<?
} 

?>
	<form name="mainForm" action="paypalCallback.php" method="POST">
	<input type="hidden" name="navigate" value="proceed">
	<input type="hidden" name="status" value="<? echo $strStatus  ?>" />
	<? if ($strConnectTo!="LIVE"){?>
	<a href="javascript:submitForm('mainForm','proceed');" title="Proceed to complete the order"><img src="images/proceed.gif" alt="Proceed" border="0" align="right" /></a>
	<? }?>
	</form>
<?
if($strConnectTo=="LIVE")
{
	?>
	<div style="text-align:center">
	<h1>Processing transaction, please wait...</h1>
	<img src="content/img/main/processing.gif" alt="" /><br />
	If this page take longer than 5 seconds, please click <a href="#" onclick="javascript:submitForm('mainForm','proceed');" title="Proceed to complete the order">here</a>.
	<p>&nbsp;</p></div>
	<?
}
?>
</body>
</html>
