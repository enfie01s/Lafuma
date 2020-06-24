<?
//if($_SERVER['REQUEST_METHOD']!="POST"){die("Access Denied");}//direct access security 
//session_start();
include("../content/config.php");
include("../content/functions.php");
include("../content/usession.php");
include("includes.php");
$strCart=$_SESSION['cart'];
if(!is_array($strCart)||count($strCart)==0) 
{
	redirect("$mainbase/index.php?p=shopping_basket");
	exit();
}

	/* HERE */
	if(!isset($_SESSION['shipping'])||isset($_POST['shipping']))
	{
		$_SESSION['shipping']=$_POST['shipping'];
		$postdq=mysql_query("SELECT pmd.description,pmd.options FROM postage_methods as pm JOIN postage_method_details as pmd ON pm.post_id=pmd.post_id WHERE pmd.post_details_id='".$_SESSION['shipping']."' AND availability LIKE '%-".$_SESSION['address_details']['delivery']['country']."-%'");
		$postd=mysql_fetch_row($postdq);
		$_SESSION['shipping_opt']=isset($_POST['option'][$_POST['shipping']])?$_POST['option'][$_POST['shipping']]:"";
		$_SESSION['postdesc']=$postd[0];
	}
if(isset($_SESSION['shipping']))
{
	if($freepost!=1)
	{
		$postageq=mysql_query("SELECT `restraints`,postage_method_details.`description` as description,`field1`,`field2`,`field3`,postage_method_details.`post_id` as post_id FROM postage_methods,postage_method_details WHERE postage_methods.`post_id`=postage_method_details.`post_id` AND `post_details_id`='".$_SESSION['shipping']."'");
		$postage=mysql_fetch_assoc($postageq);
		$restraints=explode("#",$postage['restraints']);
		if(strlen($restraints[0])>0)
		{
			$stamp=strtotime($restraints[2]);
		}
	}
	if(postage_expired($stamp)&&strlen($restraints[0])>0&&$freepost!=1)
	{
		redirect("$mainbase/index.php?p=postage_choice");
		exit();
		//echo "Time restrains for chosen postage method have expired, please go back and choose a different method";
	}
	else
	{
		/* All required fields are present, so first store the order in the database then format the POST to Sage Pay Direct 
		** First we need to generate a unique VendorTxCode for this transaction
		** We're using VendorName, time stamp and a random element.  You can use different methods if you wish
		** but the VendorTxCode MUST be unique for each transaction you send to Sage Pay Direct */
		$strTimeStamp = date("y/m/d : H:i:s", time());
		$intRandNum = rand(0,32000)*rand(0,32000);
		$strVendorTxCode=cleanInput($strVendorName . "-" . $strTimeStamp . "-" . $intRandNum,"VendorTxCode");
		$_SESSION["VendorTxCode"] = $strVendorTxCode;
			
		/* Calculate the transaction total based on basket contents.  For security
		** we recalculate it here rather than relying on totals stored in the session or hidden fields
		** We'll also create the basket contents to pass to Sage Pay Direct. See the Sage Pay Direct Protocol for
		** the full valid basket format.  The code below converts from our "x of y" style into
		** the Sage Pay system basket format (using a 17.5% VAT calculation for the tax columns) */
		$sngTotal=count($strCart)+1;
		$strThisEntry=$strCart;
		$strBasket="";
		$iBasketItems=0;
		$runningTotal=0;	
		$vattotal=0;	
		$postageDescrip="";	
		$postageDid=0;
		$thisdiscount=0;	
		$totaldiscount=0;
								
		foreach($strCart as $cartid => $cartarray)
		{
			
			$iQuantity=$cartarray['qty'];
			$iProductId=$cartarray['prod_id'];
			
			$strSQL="SELECT * FROM products WHERE `prod_id`='" . $iProductId . "' AND `".WHICHLIST."` = '1'";
			$rsPrimary = mysql_query($strSQL)
				or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
			$row = mysql_fetch_array($rsPrimary);
			$rsPrimary="";
			$strSQL="";
			
			$thisdiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($row['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $row['exclude_discount']!=1?(($row["price".PRICECUR]/100)*$_SESSION['discount_amount']):0;
			
			$totaldiscount+=$thisdiscount;
			$thisprice=$row["price".PRICECUR]-$thisdiscount;
			
			$vattoadd=$vat*($thisprice/100);
			$runningTotal+=($thisprice+$vattoadd)*$iQuantity;
			$vattotal+=$vattoadd*$iQuantity;
			
			$strBasket.=":" . $row["title"] . ":" . $iQuantity;
			$strBasket.=":" . number_format($thisprice,2); /** Price ex-Vat **/
			$strBasket.=":" . number_format($vattoadd,2); /** VAT component **/
			$strBasket.=":" . number_format($thisprice+$vattoadd,2); /** Item price **/
			$strBasket.=":" . number_format(($thisprice+$vattoadd)*$iQuantity,2); /** Line total **/			
			/*:rsx recliner:2:50:13:63:126*/
		}					
		// We've been right through the cart, so add delivery to the total and the basket
		$discount=0;
		list($price,$postdesc,$postid)=postagecalc($runningTotal,$_SESSION['shipping']);
		$strBasket=$sngTotal . $strBasket . ":".$_SESSION['postdesc'].(strlen($_SESSION['shipping_opt'])>0?" - [".$_SESSION['shipping_opt']."]":"").":---:---:---:---:".$price;
	
		$runningTotal+=$price;
		
		$postageDescrip=$_SESSION['postdesc'];
		$postageDid=$_SESSION['shipping'];
		
		// Gather customer details from the session
		$strCustomerEMail = $_SESSION['address_details']['billing']['email'];
		$strBillingFirstnames = $_SESSION['address_details']['billing']['firstname'];
		$strBillingSurname = $_SESSION['address_details']['billing']['lastname'];
		$strBillingAddress1  = $_SESSION['address_details']['billing']['address1'];
		$strBillingAddress2 = $_SESSION['address_details']['billing']['address2'];
		$strBillingCity = $_SESSION['address_details']['billing']['city'];
		$strBillingPostCode = $_SESSION['address_details']['billing']['postcode'];
		$strBillingCountry = $_SESSION['address_details']['billing']['country'];
		$strBillingState = $_SESSION['address_details']['billing']['county'];
		$strBillingPhone = $_SESSION['address_details']['billing']['phone'];
		$strBillingCompany = $_SESSION['address_details']['billing']['company'];
		$strBillingHomepage = $_SESSION['address_details']['billing']['website'];
		$bIsDeliverySame = $_SESSION['address_details']['delivery']['sameasbilling'];
		$strDeliveryFirstnames = $_SESSION['address_details']['delivery']['firstname'];
		$strDeliverySurname = $_SESSION['address_details']['delivery']['lastname'];
		$strDeliveryAddress1 = $_SESSION['address_details']['delivery']['address1'];
		$strDeliveryAddress2 = $_SESSION['address_details']['delivery']['address2'];
		$strDeliveryCity = $_SESSION['address_details']['delivery']['city'];
		$strDeliveryPostCode = $_SESSION['address_details']['delivery']['postcode'];
		$strDeliveryCountry = $_SESSION['address_details']['delivery']['country'];
		$strDeliveryState = $_SESSION['address_details']['delivery']['county'];
		$strDeliveryPhone = $_SESSION['address_details']['delivery']['phone'];
		
		$strCustId = ((isset($_SESSION['loggedin'])&&$_SESSION['loggedin']!=0)?$ua['cust_id']:0);
		$strComments=(strtolower($_REQUEST["comments"])!="special requirements")?cleanInput($_REQUEST["comments"],"Text"):"";	
		$strPostOpts=strlen($_SESSION['shipping_opt'])>0?cleanInput($_SESSION['shipping_opt'],"Text"):"";	
	
			// Now create the Sage Pay Direct POST
		/* Now to build the Sage Pay Direct POST.  For more details see the Sage Pay Direct Protocol 2.23
		** NB: Fields potentially containing non ASCII characters are URLEncoded when included in the POST */
		$strPost="VPSProtocol=" . $strProtocol;
		$strPost.="&TxType=" . $strTransactionType; //PAYMENT by default.  You can change this in the includes file
		$strPost.="&Vendor=" . $strVendorName;
		$strPost.="&VendorTxCode=" . $strVendorTxCode; //As generated above
		
		// Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
			if (strlen($strPartnerID) > 0)
							$strPost.="&ReferrerID=" . URLEncode($strPartnerID);  //You can change this in the includes file
	
		$strPost.="&Amount=" . number_format($runningTotal,2); //Formatted to 2 decimal places with leading digit but no commas or currency symbols **
		$strPost.="&Currency=" . $strCurrency;
		
		// Up to 100 chars of free format description
		$strPost.="&Description=" . urlencode("Lafuma UK products");
		
		$strPost.="&NotificationURL=" . $strYourSiteFQDN . $strVirtualDir . "sagepay/notificationPage.php";
		
		
		 /* Billing Details 
		 ** This section is optional in its entirety but if one field of the address is provided then all non-optional fields must be provided 
		** If AVS/CV2 is ON for your account, or, if paypal cardtype is specified and its not via PayPal Express then this section is compulsory */
		$strPost.="&BillingFirstnames=" . urlencode($strBillingFirstnames);
		$strPost.="&BillingSurname=" . urlencode($strBillingSurname);
		$strPost.="&BillingAddress1=" . urlencode($strBillingAddress1);
		if (strlen($strBillingAddress2) > 0) $strPost=$strPost . "&BillingAddress2=" . urlencode($strBillingAddress2);
		$strPost.="&BillingCity=" . urlencode($strBillingCity);
		$strPost.="&BillingPostCode=" . urlencode($strBillingPostCode);
		$strPost.="&BillingCountry=" . urlencode($strBillingCountry);
		if (strlen($strBillingState) > 0&&$strBillingCountry=="US") $strPost.="&BillingState=" . urlencode($strBillingState);
		if (strlen($strBillingPhone) > 0) $strPost.="&BillingPhone=" . urlencode($strBillingPhone);

					/* Delivery Details
					** This section is optional in its entirety but if one field of the address is provided then all non-optional fields must be provided
					** If paypal cardtype is specified then this section is compulsory */
		$strPost.="&DeliveryFirstnames=" . urlencode($strDeliveryFirstnames);
		$strPost.="&DeliverySurname=" . urlencode($strDeliverySurname);
		$strPost.="&DeliveryAddress1=" . urlencode($strDeliveryAddress1);
		if (strlen($strDeliveryAddress2) > 0) $strPost.="&DeliveryAddress2=" . urlencode($strDeliveryAddress2);
		$strPost.="&DeliveryCity=" . urlencode($strDeliveryCity);
		$strPost.="&DeliveryPostCode=" . urlencode($strDeliveryPostCode);
		$strPost.="&DeliveryCountry=" . urlencode($strDeliveryCountry);
		if (strlen($strDeliveryState) > 0&&$strDeliveryCountry=="US") $strPost.="&DeliveryState=" . urlencode($strDeliveryState);
		if (strlen($strDeliveryPhone) > 0) $strPost.="&DeliveryPhone=" . urlencode($strDeliveryPhone);     
		 		
		
	
		// Set other optionals
		$strPost.="&CustomerEMail=" . urlencode($strCustomerEMail);
		$strPost.="&Basket=" . urlencode($strBasket); //As created above
	
		// For charities registered for Gift Aid, set to 1 to makr this as a Gift Aid transaction
		$strPost.="&AllowGiftAid=0";
		
		/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default
		** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
		if ($strTransactionType!=="AUTHENTICATE") $strPost.="&ApplyAVSCV2=0";
	
		// Send the IP address of the person entering the card details
		//$strPost.="&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];
	
		/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default **
		** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
		$strPost.="&Apply3DSecure=".(ISLOCALHN==1?2:0);
		$strPost.="&Profile=NORMAL"; //NORMAL is default setting. Can also be set to LOW for the simpler payment page version.
		/* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
		** If you are developing back-office applications for Mail Order/Telephone order, use M **
		** If your back office application is a subscription system with recurring transactions, use C **
		** Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E **/
		//$strPost.="&AccountType=E";
		
		$arrResponse = requestPost($strPurchaseURL, $strPost);
		
			/* Analyse the response from Sage Pay Server to check that everything is okay
		** Registration results come back in the Status and StatusDetail fields */
		$strStatus=$arrResponse["Status"];
		$strStatusDetail=$arrResponse["StatusDetail"];
		
		//print_r($arrResponse);
		//echo urldecode($strBasket);
		if(substr($strStatus,0,2)=="OK")
		{
			$strVPSTxId=$arrResponse["VPSTxId"];
			$strSecurityKey=$arrResponse["SecurityKey"];
			$strNextURL=$arrResponse["NextURL"];
			/* Now store the order total and order details in your database for use in your own order fulfilment
			** These kits come with a table called tblOrders in which this data is stored
			** accompanied by the tblOrderProducts table to hold the basket contents for each order */
			$strSQL="SELECT MAX(`invoice`) FROM orders";
			$rsPrimary = mysql_query($strSQL)
				or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
			list($lastinvoice)=mysql_fetch_row($rsPrimary);
			$rsPrimary="";
			$strSQL="";
			
			$strSQL="INSERT INTO orders(`VendorTxCode`,`TxType`, `total_price`, `cust_id`, `invoice`, `date_ordered`, `ship_description`, `ship_option`, `ship_method_id`, `ship_total`, `session_id`, `discount_code`, `discount`, `firstname`, `lastname`, `email`, `address1`, `address2`,`city`, `postcode`, `country`, `state`, `phone`, `sameasbilling`,`alt_name`, `alt_address1`, `alt_address2`, `alt_city`,`alt_postcode`, `alt_country`, `alt_state`, `alt_phone`, `tax_rate`, `tax_price`, `comments`, `CardType`,`from_site`,`currency`,`exchrate`,`Status`, `VPSTxId`, `SecurityKey`) VALUES (";
		
			$strSQL.="'" . mysql_real_escape_string($strVendorTxCode) . "',"; //Add the VendorTxCode generated above
			$strSQL.="'" . mysql_real_escape_string($strTransactionType) . "',"; //Add the TxType from the includes file
			$strSQL.="'" . number_format($runningTotal,4,".","") . "',"; //Add the formatted total amount
			//$strSQL.="'" . mysql_real_escape_string($strCurrency) . "',"; //Add the Currency
			$strSQL.="'" . mysql_real_escape_string($strCustId)."',";//customer id
			$strSQL.="'" . ($lastinvoice+1) ."',";//invoice
			$strSQL.="'" . date("U")."',";//date ordered
			$strSQL.="'".mysql_real_escape_string($postageDescrip)."',";//ship desc
			$strSQL.="'".mysql_real_escape_string($strPostOpts)."',";//postage option
			$strSQL.="'".mysql_real_escape_string($postageDid)."',";//ship method id
			$strSQL.="'".number_format($price,2,".","")."',";//ship total
			/** Now save the fields returned from the Sage Pay System and extracted above **/
			$strSQL.="'" . session_id() . "',";
			$strSQL.="'".mysql_real_escape_string($_SESSION['discount_code'])."',";
			$strSQL.="'".mysql_real_escape_string($_SESSION['discount_amount'])."',";
				
			//** If this is a PaypalExpress checkout method then NO billing and delivery details are available here **
			if ($isPaypalExpress == true) 
			{
					$strSQL.=" null, null, null, null, null, null, null, null, null, null, null, null, ";
					$strSQL.=" null, null, null, null, null, null, null, null, null, null, 'PAYPAL',";
			}
			else
			{
				// Add the Billing details 
				$strSQL.="'" . mysql_real_escape_string($strBillingFirstnames) . "',";   
				$strSQL.="'" . mysql_real_escape_string($strBillingSurname) . "',";  
				
				// -Customer email 
				$strSQL.=((strlen($strCustomerEMail)>0)?"'" . mysql_real_escape_string($strCustomerEMail) . "'":"null").","; 
				
				$strSQL.="'" . mysql_real_escape_string($strBillingAddress1) . "',";  
				$strSQL.=((strlen($strBillingAddress2)>0)?"'" . mysql_real_escape_string($strBillingAddress2) . "'":"null").",";
				$strSQL.="'" . mysql_real_escape_string($strBillingCity) . "',";  
				$strSQL.="'" . mysql_real_escape_string($strBillingPostCode) . "',"; 
				$strSQL.="'" . mysql_real_escape_string(str_replace("GB","100",$strBillingCountry)) . "',";  
				$strSQL.=((strlen($strBillingState)>0)?"'" . mysql_real_escape_string($strBillingState) . "'":"null").","; 
				$strSQL.=((strlen($strBillingPhone)>0)?"'" . mysql_real_escape_string($strBillingPhone) . "'":"null").",";
				
				// Add the Delivery details 
				$strSQL.=(strlen($bIsDeliverySame)>0?"'".$bIsDeliverySame."'":"null").",";
				$strSQL.="'" . mysql_real_escape_string($strDeliveryFirstnames." ".$strDeliverySurname) . "',";  
				$strSQL.="'" . mysql_real_escape_string($strDeliveryAddress1) . "',"; 
				$strSQL.=(strlen($strDeliveryAddress2)>0?"'" . mysql_real_escape_string($strDeliveryAddress2) . "'":"null").",";
				$strSQL.="'" . mysql_real_escape_string($strDeliveryCity) . "',";  
				$strSQL.="'" . mysql_real_escape_string($strDeliveryPostCode) . "',"; 
				$strSQL.="'" . mysql_real_escape_string(str_replace("GB","100",$strDeliveryCountry)) . "',";  
				$strSQL.=(strlen($strDeliveryState)>0?"'" . mysql_real_escape_string($strDeliveryState) . "'":"null").","; 
				$strSQL.=(strlen($strDeliveryPhone)>0?"'" . mysql_real_escape_string($strDeliveryPhone) . "'":"null").",";
				$strSQL.="'".number_format($vat,2)."',";//tax_rate
				$strSQL.="'".number_format($vattotal,2)."',";//tax_price
				$strSQL.=(strlen($strComments)>0?"'" . mysql_real_escape_string($strComments) . "'":"null").",";//comments
				$strSQL.="'".mysql_real_escape_string($strCardType)."',";// Card Type
			}
			$strSQL.="'".mysql_real_escape_string(DOMAINEXT)."',";// Record which site they were on
			$strSQL.="'".mysql_real_escape_string($currarr[$domainext][0])."',";// currency
			$strSQL.="'',";// exch rate
			$strSQL.="'".($_SESSION['test']==1?"TESTING":"")."',";		
			$strSQL.="'" . mysql_real_escape_string($strVPSTxId) . "',"; //Save the Sage Pay System's unique transaction reference
			$strSQL.="'" . mysql_real_escape_string($strSecurityKey) . "'"; //Save the MD5 Hashing security key, used in notification
			$strSQL.=")";
			
			//Execute the SQL command to insert this data to the tblOrders table
			mysql_query($strSQL) or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');	
			$order_insert_id=mysql_insert_id();
			
			$strSQL="";
			$strPageState="Posted";
			/** Now add the basket contents to the orderproducts table, one line at a time **/
			
			
			foreach($strCart as $cart_id => $cartarray)
			{
				// Extract the Quantity and Product from the list of "x of y," entries in the cart
				$iQuantity=$cartarray['qty'];
				$iProductId=$cartarray['prod_id'];
				$excldiscount=$cartarray['exclude_discount'];
				
				//Look up the current price of the items in the database
				$strSQL = "SELECT * FROM products WHERE `prod_id`='" . $iProductId . "' AND `".WHICHLIST."` = '1'";
				$rsPrimary = mysql_query($strSQL)
					or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
									
				$row = mysql_fetch_array($rsPrimary);
				$sngPrice=$row["price".PRICECUR];
				$sngTitle=$row['title'];
				$sngSeoTitle=$row['seo_title'];
				$sngShort=$row['shortdesc'];
				$sngTaxable=$row['taxable'];
				$sngShipnotes=$row['shipnotes'];
				$sngDiscount=(isset($_SESSION['discount_amount']) && isset($_SESSION['discount_list'])&&count($_SESSION['discount_list'])>0 && in_array($row['prod_id'],$_SESSION['discount_list'])) || (isset($_SESSION['discount_amount']) && (!isset($_SESSION['discount_list'])||count($_SESSION['discount_list'])<1)) && $_SESSION['discount_amount']>0 && $row['exclude_discount']!=1?(($row["price".PRICECUR]/100)*$_SESSION['discount_amount']):0;
				$strSQL="";
				$rsPrimary = "";
				$skuvars="";
				if($cartarray['ispack']==0)
				{
					foreach($cartarray['skuvariant'] as $ident => $newsku)
					{
						$expsku=explode("-qty-",$newsku);
					}
					$optInfostr = "SELECT ov.`opt_id`,`description`,`item_desc`,`variant_id` FROM product_options as po,fusion_options as fo, option_values as ov WHERE po.`opt_id`=fo.`opt_id` AND ov.`opt_id`=fo.`opt_id` AND `variant_id`='".$expsku[0]."'";
					$optInfoq=mysql_query($optInfostr)
						or die ("Query '$optInfostr' failed with error message: \"" . mysql_error () . '"');
					list($goptid,$oname,$oitem,$var_id)=mysql_fetch_row($optInfoq);
				}
				else
				{
					$goptid="";$oname="";$oitem="";$var_id="";
				}
				$getskuvar=explode("-v-",$var_id);
				$sngSku=$cartarray['ispack']==0?$getskuvar[0]:$row['sku'];
				/** Save the basket contents with price included so we know the price at the time of order **
				** so that subsequent price changes will not affect the price paid for items in this order **/
				$strSQL="INSERT INTO orderproducts(`order_id`,`prod_id`,`VendorTxCode`,`qty`,`price`,`discount`,`exclude_discount`,`title`,`seo_title`,`sku`,`short_desc`,`taxable`,`postage_notes`,`goptid`,`oname`,`oitem`,`variant_id`,`ispack`) VALUES(";
				$strSQL.="'".$order_insert_id."',";
				$strSQL.="'".$iProductId."',";
				$strSQL.="'" . mysql_real_escape_string($strVendorTxCode) . "',"; //Add the VendorTxCode generated above
				$strSQL.="'".$cartarray['qty']."',";
				$strSQL.="'".$sngPrice."',";
				$strSQL.="'".$sngDiscount."',";
				$strSQL.="'".$excldiscount."',";
				$strSQL.="'".mysql_real_escape_string($sngTitle)."',";
				$strSQL.="'".mysql_real_escape_string($sngSeoTitle)."',";
				$strSQL.="'".mysql_real_escape_string($sngSku)."',";
				$strSQL.="'".mysql_real_escape_string($sngShort)."',";
				$strSQL.="'".$sngTaxable."',";
				$strSQL.="'".mysql_real_escape_string($sngShipnotes)."',";
				$strSQL.="'".$goptid."',";
				$strSQL.="'".mysql_real_escape_string($oname)."',";
				$strSQL.="'".mysql_real_escape_string($oitem)."',";
				$strSQL.="'".mysql_real_escape_string($getskuvar[1])."',";
				$strSQL.="'".$cartarray['ispack']."'";
				$strSQL.=")";
				$rsPrimary = mysql_query($strSQL)
					or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
				
				$rsPrimary="";
				$strSQL="";
				$orderprods_insert_id=mysql_insert_id();
				if($cartarray['ispack']!=0)
				{
					foreach($cartarray['skuvariant'] as $ident => $newsku)
					{
						$expsku=explode("-qty-",$newsku);
					
						$optInfostr = "SELECT p.`prod_id` as prod_id,`title`,`description`,`item_desc` FROM products as p,product_options as po,fusion_options as fo,option_values as ov WHERE p.`prod_id`=fo.`prod_id` AND po.`opt_id`=fo.`opt_id` AND ov.`opt_id`=fo.`opt_id` AND ov.`variant_id`='".$expsku[0]."' AND p.`prod_id`='$ident' AND `".WHICHLIST."` = '1'";
						
						$optInfoq=mysql_query($optInfostr)
							or die ("Query '$optInfostr' failed with error message: \"" . mysql_error () . '"');
						$opts=mysql_fetch_assoc($optInfoq);
						
						$strSQL="INSERT INTO orderkits(`order_id`,`order_prod_id`,`kprod_id`,`prod_id`,`kit_title`,`okit_skuvar`,`item_qty`,`oname`,`oitem`) VALUES(";
						
						$strSQL.="'".$order_insert_id."',";
						$strSQL.="'".$orderprods_insert_id."',";
						$strSQL.="'".$iProductId."',";
						$strSQL.="'".$opts['prod_id']."',";
						$strSQL.="'".$opts['title']."',";
						$strSQL.="'".$expsku[0]."',";
						$strSQL.="'".$expsku[1]."',";
						$strSQL.="'".$opts['description']."',";
						$strSQL.="'".$opts['item_desc']."'";
						
						$strSQL.=")";	
						$rsPrimary = mysql_query($strSQL)
							or die ("Query '$strSQL' failed with error message: \"" . mysql_error () . '"');
						$rsPrimary="";
						$strSQL="";
					}
				}
				
			}
			if ($strConnectTo!=="SIMULATOR")
			{
				ob_flush();
				redirect($strNextURL);
				exit();			
			}
		}
		elseif ($strStatus=="MALFORMED")
		{	
			//echo $strPost."<br />";
			/** A MALFORMED status occurs when the POST sent above is not correctly formatted **
			** or is missing compulsory fields. You will normally only see these during **
			** development and early testing **/
			$strPageError="Sage Pay returned an MALFORMED status. The POST was Malformed because \"" . $strStatusDetail . "\"";	
			//echo $strPageError;	
			redirect("$mainbase/index.php?p=orderFailed&error=001");			
		}
		elseif ($strStatus=="INVALID")
		{
			//echo $strPost."<br />";
			/** An INVALID status occurs when the structure of the POST was correct, but **
			** one of the fields contains incorrect or invalid data.  These may happen when live **
			** but you should modify your code to format all data correctly before sending **
			** the POST to Sage Pay Server **/
			$strPageError="Sage Pay returned an INVALID status. The data sent was Invalid because \"" . $strStatusDetail . "\"";
			//echo $strPageError;	
			redirect("$mainbase/index.php?p=orderFailed&error=001");			
		}
		else
		{
			//echo $strPost."<br />";
			/** The only remaining status is ERROR **
			** This occurs extremely rarely when there is a system level error at Sage Pay **
			** If you receive this status the payment systems may be unavailable **<br>
			** You could redirect your customer to a page offering alternative methods of payment here **/
			$strPageError="Sage Pay returned an ERROR status. The description of the error was \"" . $strStatusDetail . "\"";
			//echo $strPageError;	
			//redirect("$mainbase/index.php?p=orderFailed&error=002");			
		}
	}
}
?>