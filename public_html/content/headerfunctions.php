<?
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
$post_extracted=mysql_real_extracted($_POST);
$requireds['checkout_newcust']=array("email");
$requireds['checkout_registered']=array("email","pass");
$requireds['checkout_customer']=array("firstname","lastname","address1","city","state","postcode","country","email","phone");

if(isset($_POST['identifier'])&&$_POST['identifier']=="checkout_customer"&&$_POST['matchbilling'][0]!=1)
{
	$requireds['checkout_customer'][]="deliver_firstname";
	$requireds['checkout_customer'][]="deliver_lastname";
	$requireds['checkout_customer'][]="deliver_address1";
	$requireds['checkout_customer'][]="deliver_city";
	$requireds['checkout_customer'][]="deliver_state";
	$requireds['checkout_customer'][]="deliver_postcode";
	$requireds['checkout_customer'][]="deliver_country";
	$requireds['checkout_customer'][]="deliver_phone";
}
$_SESSION['checkoutnew']=isset($_POST['identifier'])&&$_POST['identifier']=="checkoutnew"?1:(!isset($_SESSION['checkoutnew'])?0:$_SESSION['checkoutnew']);

$regoncheckout=isset($_POST['identifier'])&&$_POST['identifier']=="checkout_customer"&&$_SESSION['checkoutnew']==1&&strlen($_POST['password1'])>0?1:0;
/* THIS FILE IS FOR QUERIES WHICH NEED TO BE HANDLED BEFORE THE PAGES LOAD */
if($page=="products"&&isset($_POST['comment'])&&$_SESSION['loggedin']!=0&&strlen($_POST['shouldbeempty'])<1)
{
	$higherr=array();
	if($_POST['comment']=="Your review"||strlen($post_extracted['comment'])<1 || $post_extracted['title']=="review title"||strlen($post_extracted['title'])<1)
	{
		if($post_extracted['title']=="review title"||strlen($post_extracted['title'])<1)
		{
			array_push($higherr,"title");
		}
		if($post_extracted['comment']=="Your review"||strlen($post_extracted['comment'])<1)
		{
			array_push($higherr,"comment");
		}
	}
	else
	{
		$headers = "From: Lafuma UK <sales@llc-ltd.co.uk>\r\n";
		$headers .= "Reply-To: sales@llc-ltd.co.uk\r\n";
		$headers .= "Return-Path: sales@llc-ltd.co.uk\r\n";
		$headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
		mysql_query("INSERT INTO customerreviews (item_id,cust_id,title,comment,rank,display,date_created,state)VALUES('".$post_extracted['item_id']."','$ua[cust_id]','".$post_extracted['title']."','".$post_extracted['comment']."','".$post_extracted['rank']."','".$post_extracted['display']."','".date("U")."','1')");
		@mail("sales@llc-ltd.co.uk","New Review on Lafuma","Sales,\r\nA new review has been posted, see below.\r\n\r\nTitle: ".$post_extracted['title']."\r\nComments: ".$post_extracted['comment']."\r\n\r\nLink to page: ".$mainbase."/index.php?p=products&pid=".$pid,$headers);
		header("Location: $mainbase/index.php?p=products&pid=$pid");
	}
}
if($page=="review")
{
	/* HERE */
	if(!isset($_SESSION['shipping'])||isset($_POST['shipping']))
	{
		$_SESSION['shipping']=$post_extracted['shipping'];
		$postdq=mysql_query("SELECT pmd.description,pmd.options FROM postage_methods as pm JOIN postage_method_details as pmd ON pm.post_id=pmd.post_id WHERE pmd.post_details_id='".$_SESSION['shipping']."' AND availability LIKE '%-".$_SESSION['address_details']['delivery']['country']."-%'");
		$postd=mysql_fetch_row($postdq);
		$_SESSION['shipping_opt']=isset($post_extracted['option'][$post_extracted['shipping']])?$post_extracted['option'][$post_extracted['shipping']]:"";
		$_SESSION['postdesc']=$postd[0];
	}
}
if(isset($_POST['identifier'])&&(in_array($_POST['identifier'],$customerforms)||$regoncheckout==1))
{ 
	$eheaders = "From: Lafuma UK <sales@llc-ltd.co.uk>\r\n";
	$eheaders .= "Reply-To: sales@llc-ltd.co.uk\r\n";
	$eheaders .= "Return-Path: sales@llc-ltd.co.uk\r\n";
	$eheaders .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
	//check for empty fields which are required
	foreach($_POST as $field => $value)
	{
		if(in_array($field,$requireds[$_POST['identifier']])&&$value==""){$errorlist[$field]=$fieldtitles[$field]." is empty.";}
	}
	if(isset($_POST['terms_agree'])&&$_POST['terms_agree']!=1){$errorlist['terms']="You must agree to the terms &amp; conditions to continue";}
	//check for duplicate email address
	if(isset($_POST['email'])&&($_POST['identifier']=="doregister"||$regoncheckout==1||($_POST['identifier']=="doupdate"&&$_POST['cust_email']!=$_POST['email'])))
	{
		$emailcheck_q=mysql_query("SELECT email FROM customers WHERE email='$post_extracted[email]'")or die(mysql_error());
		$emailcheck=mysql_num_rows($emailcheck_q);
		if($emailcheck>0){$errorlist["email"]="The email address you entered is already registered on another customer account.";}
	}
	//invalid email test
	if(isset($_POST['email'])&&$_POST['email']!=""&&!eregi($emailereg, $_POST['email'])){$errorlist["email"]="Please enter a valid email address (eg: user@host.com).";}
	//pass matching test
	if((in_array($_POST['identifier'],array("doregister","dopassupdate","dopassreset"))||$regoncheckout==1)&&$_POST['password1']!=$_POST['password2']){$errorlist["password2"]="Passwords don't match";}
	//ensure a registered customer is changing the details
	if(($_POST['identifier']=="dopassupdate"||$_POST['identifier']=="doupdate")&&$_POST['cust_id']==""){$errorlist["customer"]="There was an error with your customer ID";}
	if($_POST['identifier']=="lostpass"&&!isset($errorlist["email"]))
	{
		$emailcheck_q=mysql_query("SELECT email, gpassword, firstname, signup_date FROM customers WHERE email='$post_extracted[email]'")or die(mysql_error());
		$emailcheck=mysql_num_rows($emailcheck_q);list($demail,$dpass,$dname,$dsign)=mysql_fetch_row($emailcheck_q);
		if($emailcheck==0){$errorlist["email"]="Sorry, no customer was found with that email address.";}
	}
	//error checks for password reset
	if($_POST['identifier']=="dopassreset")
	{
		$emailcheck_q=mysql_query("SELECT email, gpassword, firstname, signup_date,cust_id FROM customers WHERE email='$post_extracted[email]'")or die(mysql_error());
		$emailcheck=mysql_num_rows($emailcheck_q);list($demail,$dpass,$dname,$dsign,$cust_id)=mysql_fetch_row($emailcheck_q);
		if($emailcheck==0){$errorlist["email"]="Sorry, no customer was found with that email address.";}
		if($_POST['code']!=md5($dname.$demail.$dpass.$dsign)&&!isset($errorlist["email"])){$errorlist["code"]="Invalid security code.";}
	}
	//no errors
	
	if(count($errorlist)==0)
	{
		switch($_POST['identifier'])
		{
			case "doupdate":
				$getcountry=mysql_query("SELECT countryname FROM countries WHERE country_id='$post_extracted[country]'");
				list($getcountry)=mysql_fetch_row($getcountry);
				$getcounty=mysql_query("SELECT countyname FROM counties WHERE county_id='$post_extracted[state]'");
				list($getcounty)=mysql_fetch_row($getcounty);
				$newpass=hashandsalt($_POST['email'],$_SESSION['pass']);
				
				$eparts=explode("@",$_POST['email']);
				$_SESSION['epart1']=$eparts[0];
				$_SESSION['epart2']=$eparts[1];
				mysql_query("UPDATE customers SET firstname='$post_extracted[firstname]', lastname='$post_extracted[lastname]',email='$post_extracted[email]',gpassword='$newpass',phone='$post_extracted[phone]',address1='$post_extracted[address1]',address2='$post_extracted[address2]', city='$post_extracted[city]', state='$post_extracted[state]',postcode='$post_extracted[postcode]',country='$post_extracted[country]',homepage='$post_extracted[homepage]', company='$post_extracted[company]',mailing='$post_extracted[mailing]' WHERE cust_id='$post_extracted[cust_id]'");
				//set confirm email details
				$msg="============================<br />
				You information update at Lafuma UK<br />
				============================<br />
				Hi ".$_POST['firstname']." ".$_POST['lastname'].",<br />
				<br />
				Your information was updated on the Lafuma UK website, please see your new details below.<br />
				<br />
				<strong>Email:</strong> ".$_POST['email']."<br />
				<strong>Phone:</strong> ".$_POST['phone']."<br />
				<strong>Address Line 1:</strong> ".$_POST['address1']."<br />";
				if($_POST['address2']!=""){$msg.="<strong>Address Line 2:</strong> ".$_POST['address2']."<br />";}
				$msg.="<strong>City:</strong> ".$_POST['city']."<br />
				<strong>County/State:</strong> ".$getcounty."<br />
				<strong>Postcode/Zip:</strong> ".$_POST['postcode']."<br />
				<strong>Country:</strong> ".$getcountry."<br />
				<strong>Website:</strong> ".$_POST['homepage']."<br />
				<strong>Company:</strong> ".$_POST['company']."<br />
				<strong>Receive Marketing Emails:</strong> ".(($_POST['mailing']==1)?"Yes":"No")."<br />
				";
				$to=$_POST['email'];
				$subject="Your information update at Lafuma UK";
				break;
			case "dopassupdate":
				$_SESSION['pass']=hashandsalt($_POST['cust_email'],$_POST['password1']);
				$newpass=hashandsalt($_POST['cust_email'],$_SESSION['pass']);
				mysql_query("UPDATE customers SET gpassword='$newpass' WHERE cust_id='$post_extracted[cust_id]'");
				//set confirm email details
				$msg="========================================<br />
				You password change at Lafuma UK<br />
				========================================<br />
				Hi ".$_POST['firstname']." ".$_POST['lastname'].",<br />
				<br />
				You have changed your account password on the Lafuma UK website, please see your new login details below.<br />
				<br />
				<strong>Username:</strong>&#160;".$_POST['cust_email']."<br />
				<strong>Password:</strong>&#160;".$_POST['password1']."<br />";
				$to=$_POST['cust_email'];
				$subject="Password changed at Lafuma UK";
				break;
			case "lostpass":
				$thehash=md5($dname.$demail.$dpass.$dsign);
				$msg="===========================================<br />
				Request to reset your password on the Lafuma UK website<br />
				===========================================<br />
				Hi ".$dname.",<br />
				<br />
				A request was made to reset your password. If you did not make this request or have since remembered your password, please ignore this email.<br /><br />
				Click <a href='$mainbase/index.php?p=customer_login&amp;resetpassform=".$thehash."'>HERE</a> to reset your password.<br />";
				$to=$demail;
				$subject="Request to reset your password on Lafuma UK";
				break;
			case "dopassreset":
				$newpass1=hashandsalt($post_extracted['email'],$post_extracted['password1']);
				$newpass2=hashandsalt($post_extracted['email'],$newpass1);
				mysql_query("UPDATE customers SET gpassword='$newpass2' WHERE cust_id='$cust_id'");
				$msg="===========================================<br />
				Successfully reset your password on the Lafuma UK website<br />
				===========================================<br />
				Hi ".$dname.",<br />
				<br />
				Your password was successfully reset. Please find your new login details below.<br />
				<br>
				<strong>Username:</strong>&#160;".$demail."<br />
				<strong>Password:</strong>&#160;".$_POST['password1']."<br />";
				$to=$demail;
				$subject="Request to reset your password on Lafuma UK";
				break;
			case "doregister":
			case "checkout_customer":
				$newpass1=hashandsalt($post_extracted['email'],$post_extracted['password1']);
				$newpass2=hashandsalt($post_extracted['email'],$newpass1);
				$_SESSION['pass']=$newpass1;
				$eparts=explode("@",$post_extracted['email']);
				$_SESSION['epart1']=$eparts[0];
				$_SESSION['epart2']=$eparts[1];
				$date=date('U');
				$country=str_replace("GB","100",$post_extracted['country']);
				mysql_query("INSERT INTO customers (firstname,lastname,email,gpassword,phone,address1,address2,city,state,postcode,country,homepage,company,mailing,signup_date,status) VALUES('$post_extracted[firstname]','$post_extracted[lastname]','$post_extracted[email]','$newpass2','$post_extracted[phone]','$post_extracted[address1]','$post_extracted[address2]','$post_extracted[city]','$post_extracted[state]','$post_extracted[postcode]','$country','$post_extracted[homepage]','$post_extracted[company]','$post_extracted[opt_in]','$date','1')");
				//set confirm email details
				$msg="==================<br />
				Welcome to Lafuma UK<br />
				==================<br />
				Hi ".$_POST['firstname']." ".$_POST['lastname'].",<br />
				<br />
				Thank you for signing up to Lafuma UK. Please find your login details below.<br />
				<br>
				<strong>Username:</strong>&#160;".$_POST['email']."<br />
				<strong>Password:</strong>&#160;".$_POST['password1']."<br />";
				$to=$_POST['email'];
				$subject="Welcome to Lafuma UK";
				break;
		}
		$msg .= "<br />
		Kind Regards<br />
		<a href='http://www.lafuma.co.uk/'>Lafuma UK</a><br />
		01489 557 600<br />
		<a href='mailto:sales@llc-ltd.co.uk'>sales@llc-ltd.co.uk</a>";
		@mail($to,$subject,$msg,$eheaders,"-f".$admin_email);
		if($_POST['identifier']=="dopassreset"){
			$_SESSION["success"]="Password reset successful, you may now log in with your new details";
			header("Location: $mainbase/index.php?p=customer_login");
		}
		else if($_POST['identifier']=="doregister"){
			$_SESSION["success"]="Registration successful, please check your email for confirmation.";
			header("Location: $mainbase/index.php?p=customer_login");
		}
	}
	if(count($errorlist)>0)//errors found
	{
		foreach($errorlist as $error=>$desc)
		{
			$errormsg.= $desc."<br />";
		}
		$errorboxdisplay="display:block;";
	}
}
/* 
 CART FORM HANDLERS
*/

$discountinfo="";
$cartforms=array("add_to_cart","add_many_to_cart","update_cart");

if(isset($_POST['identifier'])&&in_array($_POST['identifier'],$cartforms))
{
	if(($_POST['identifier']=="add_to_cart"||$_POST['identifier']=="add_many_to_cart"||$_POST['identifier']=="update_cart")&&$_POST['mode']!="Empty Basket"&&$_POST['mode']!="Start Checkout"&&$_POST['mode']!="Continue Shopping")
	{
		$ids_in_cart=array();
		$skuvar_count=array();
		foreach($_SESSION['cart'] as $cid => $values)
		{
			foreach($values['skuvariant'] as $product_id => $askuvar)
			{
				$skuinfo=explode("-qty-",$askuvar);
				if(!array_key_exists($skuinfo[0],$skuvar_count)){$skuvar_count[$skuinfo[0]]=0;}
				$skuvar_count[$skuinfo[0]]+=$values['qty']*$skuinfo[1];//qty*item qty
			}
			if(!in_array($values['prod_id'],$ids_in_cart)){array_push($ids_in_cart,$values['prod_id']);}
		}
	}
	function add_to_cart($post_skuvariant,$post_prodid,$post_quantity,$post_price,$post_ispack,$post_excldiscount,$post_allowlist,$post_title)
	{
		global $mainbase,$_SESSION,$pid,$skuvar_count;
		$newqty=0;
		$ok_to_add=2;//set the var
		foreach($post_skuvariant as $itemid => $itemsku)
		{
			if(strlen($itemsku)<1){$ok_to_add=0;}
			$cleansku=explode("-qty-",$itemsku);
			if($ok_to_add!=0)//stop as soon as we find stock unavailable
			{
				$newqty=(array_key_exists($cleansku[0],$skuvar_count))?$skuvar_count[$cleansku[0]]+($cleansku[1]*$post_quantity):($cleansku[1]*$post_quantity);
				$ok_to_add=($newqty<=checkstock($cleansku[0]))?1:0;//enough stock?, can we continue?
			}
		}
		
		$dupecheck="none";
		foreach($_SESSION['cart'] as $num => $array){
			if($array["prod_id"]==$post_prodid&&$array["skuvariant"]==$post_skuvariant){$dupecheck=$num;}//duplicate item check
		}
		if($ok_to_add==1)
		{
			if(!is_int($dupecheck))
			{
				$_SESSION['cart'][]=array(
				"prod_id"=>$post_prodid,
				"skuvariant"=>$post_skuvariant,
				"qty"=>$post_quantity,
				"price"=>$post_price,
				"ispack"=>$post_ispack,
				"exclude_discount"=>$post_excldiscount,
				"allowlist"=>$post_allowlist,
				"title"=>$post_title
				);
				$_SESSION['added']=array(count($_SESSION['cart'])-1,'new');
				$_SESSION['cartupdate']="Product added to basket";
				$_SESSION['pageloads']=3;
				$_SESSION['offerprod']=$post_prodid;
			}
			else
			{
				$_SESSION['cart'][$dupecheck]["qty"]+=$post_quantity;
				$_SESSION['added']=array($dupecheck,'update');
				$_SESSION['cartupdate']="Basket quantity increased";
				$_SESSION['pageloads']=3;
			}
		}
		else{
			if(strlen($itemsku)<1)
			{$_SESSION['error'][$post_prodid]="Please choose a colour option";}
			else
			{
				if(is_int($dupecheck))//some in basket
				{$_SESSION['error'][$post_prodid]="Sorry, there is not enough stock to add the specified quanitity of this ".(($post_ispack==1)?"package.":"product/colour.");}
				else//not in basket and out of stock
				{$_SESSION['error'][$post_prodid]="Sorry, this ".(($post_ispack==1)?"package":"product/colour")." is now out of stock";}
			}
		}
	}
	/* ADD SINGLE */
	if($_POST['identifier']=="add_to_cart")
	{
		$allowed=array();
		if(strlen($_POST['allowlist'])>0){$allowed=explode(",",$_POST['allowlist']);}
		$allowedmatches=strlen($_POST['allowlist'])<1?1:count(array_intersect($allowed,$ids_in_cart));
		
		if($allowedmatches>0){
			add_to_cart($_POST['skuvariant'],$_POST['prod_id'],$_POST['quantity'],$_POST['price'],$_POST['ispack'],$_POST['exclude_discount'],$allowed,$_POST['title']);
		}
		else
		{
			$_SESSION['error'][$_POST['prod_id']]="Product Unavailable";
		}
		if(isset($_SESSION['error']))
		{
			$returnpage=urldecode($_POST['returnpage']);
			exit(header("Location: $returnpage"));
		}
		else
		{
			header("Location: $mainbase/index.php?p=products&pid=$pid");//prevent refresh page adding more items
		}
	}
	/* END ADD SINGLE */
	/* ADD MANY */
	//print_r($_POST['skuvariant']);
	//print_r($_SESSION['error']);
	/*else if($_POST['identifier']=="add_many_to_cart")
	{
		foreach($_POST['qty'] as $product_id => $quantity)
		{
			if($quantity>0)
		{
			add_to_cart($_POST['skuvariant'][$product_id],$product_id,$quantity,$_POST['price'][$product_id],$_POST['ispack'][$product_id],$_POST['exclude_discount'][$product_id],$_POST['returnpage']);
			}
		}
		if(isset($_SESSION['error']))
		{
			$returnpage=urldecode($_POST['returnpage']);
			exit(header("Location: $returnpage"));
		}
		else
		{
			header("Location: $mainbase/index.php?p=products&pid=$pid");//prevent refresh page adding more items
			unset($_SESSION['offerprod']);//remove offers box
		}
		
	}*/
	/* END OF ADD MANY */
	
	else if($_POST['identifier']=="update_cart"&&$_POST['mode']!="Empty Basket"&&$_POST['mode']!="Apply"&&$_POST['mode']!="Start Checkout"&&$_POST['mode']!="Continue Shopping")
	{
		$errormsg="Sorry, not all of your items could be updated due to stock availability, see below:<br />";
		foreach($_POST['qty'] as $id => $qty)//each cart item
		{
			if($_SESSION['cart'][$id]['qty']!=$qty)
			{
				if($qty>0)
				{
					$skuvars="";
					$newqty=0;
					$ok_to_add=1;//set the var
					foreach($_SESSION['cart'][$id]['skuvariant'] as $itemid => $itemsku)
					{
						$cleansku=explode("-qty-",$itemsku);
						if($ok_to_add!=0)//stop as soon as we find stock unavailable
						{
							$newqty=$skuvar_count[$cleansku[0]]-($_SESSION['cart'][$id]['qty']*$cleansku[1])+($qty*$cleansku[1]);//total minus this cart item qty plus new posted qty
							$ok_to_add=($newqty<=checkstock($cleansku[0]))?1:0;//enough stock?, can we continue?
						}
						$skuvars.=(($skuvars!="")?",":"")."'".$cleansku[0]."'";
					}
					$variantlist=(count($_SESSION['cart'][$id]['skuvariant'])>1)?"IN($skuvars)":"=$skuvars";
					$uquery="SELECT title,item_desc FROM products as p,fusion_options as fo,option_values as ov,product_options as po WHERE p.prod_id=fo.prod_id AND po.opt_id=fo.opt_id AND ov.opt_id=fo.opt_id AND p.prod_id='".$_SESSION['cart'][$id]['prod_id']."' AND ".WHICHLIST." = 1 AND ov.variant_id $variantlist";
					$qty_chkq=mysql_query($uquery);
					$prod=mysql_fetch_assoc($qty_chkq);
					if($ok_to_add==0)
					{
						if($_SESSION['cart'][$id]['qty']!=$qty)
						{
							$errormsg .= "&bull; $prod[title]".(($_SESSION['cart'][$id]['ispack']!=1)?" ($prod[item_desc])":"")."<br />";
						}
						$errorboxdisplay="display:block;";
					}
					else{$_SESSION['cart'][$id]["qty"]=$qty;}
				}
				else{unset($_SESSION['cart'][$id]);}//qty = 0 (remove item)
			}
		}
		if($errormsg=="Sorry, not all of your items could be updated due to stock availability, see below:<br />"){unset($errormsg);$errorboxdisplay="display:none;";}
	}
	else if($_POST['identifier']=="update_cart"&&$_POST['mode']=="Apply")
	{
		$checkdiscount=mysql_query("SELECT * FROM discounts WHERE code='".$post_extracted['discount']."' AND ".WHICHLIST." = '1'");
		$thediscount=mysql_fetch_assoc($checkdiscount);
		$numdiscount=mysql_num_rows($checkdiscount);
		if($numdiscount>0){
			if($thediscount['date_start']<=date("U")&&$thediscount['date_end']>date("U"))
			{
				if($_POST['basket_total']>=$thediscount['mintotal'])
				{
					$_SESSION['discount_code']=$_POST['discount'];
					$_SESSION['discount_amount']=$thediscount['discount'];
					$_SESSION['discount_list']=$thediscount['uselist']==1&&strlen($thediscount['prodlist'])>0?explode(",",$thediscount['prodlist']):array();
				}
				else
				{
					$discountinfo="<br />Sorry, this code is invalid on orders below &#163;".$thediscount['mintotal'];
				}
			}
			else
			{
				$discountinfo="<br />Sorry, this code ($_POST[discount]) has expired.";//code outside of dates
			}
		}
		else{$discountinfo="<br />Sorry, this code ($_POST[discount]) is invalid.";}//invalid code
	}
	else if($_POST['identifier']=="update_cart"&&$_POST['mode']=="Empty Basket")
	{
		if(isset($_SESSION['cart'])){unset($_SESSION['cart']);unset($_SESSION['added']);unset($_SESSION['discount_code']);unset($_SESSION['discount_amount']);unset($_SESSION['discount_list']);}
	}
	else if($_POST['identifier']=="update_cart"&&$_POST['mode']=="Continue Shopping")
	{
		header("Location: $_POST[backto]");
	}
	else if($_POST['identifier']=="update_cart"&&$_POST['mode']=="Start Checkout")
	{
		header("Location: $_POST[checkout]");
	}
}
if(isset($_GET['remove_item'])&&$_GET['remove_item']!="")
{	
	$rcart_ids=array();
	if(isset($_SESSION['cart'][$_GET['remove_item']])){
		unset($_SESSION['cart'][$_GET['remove_item']]);
		foreach($_SESSION['cart'] as $rid => $rcartitems){if(!in_array($rcartitems['prod_id'],$rcart_ids)){array_push($rcart_ids,$rcartitems['prod_id']);}}
		foreach($_SESSION['cart'] as $id => $cartitems)
		{
			$allowedmatches=!is_array($cartitems['allowlist'])||count($cartitems['allowlist'])<1?1:count(array_intersect($cartitems['allowlist'],$rcart_ids));
			if($allowedmatches<1)
			{unset($_SESSION['cart'][$id]);}
		}
	}
}
/* 
CHECKOUT
*/
if(isset($_POST['identifier'])&&$_POST['identifier']=="checkout_customer")//$regoncheckout==0 - if 1, we've already error checked above.
{ 
	if($regoncheckout==0&&isset($_POST['email'])&&$_POST['email']!=""&&!eregi($emailereg, $_POST['email'])){$errorlist["email"]="Please enter a valid email address (eg: user@host.com).";}
	if($regoncheckout==0&&$_POST['terms_agree']!=1){$errorlist['terms']="You must agree to the terms &amp; conditions to continue";}
	if($regoncheckout==0)
	{
		foreach($_POST as $field => $value)
		{
			if(in_array($field,$requireds[$_POST['identifier']])&&$value==""){$errorlist[$field]=$fieldtitles[$field]." is empty.";}
		}
	}
	if(count($errorlist)>0)//errors found
	{
		if($regoncheckout==0)
		{
			foreach($errorlist as $error=>$desc)
			{
				$errormsg.= $desc."<br />";
			}
			$errorboxdisplay="display:block;";
		}
	}
	else
	{
		$_SESSION['terms_agree']=$_POST['terms_agree'];
	
	/* SET UP ADDRESS SESSION */
		$_SESSION['address_details']=array();
		$_SESSION['address_details']['billing']=array("firstname"=>$_POST['firstname'],"lastname"=>$_POST['lastname'],"address1"=>$_POST['address1'],"address2"=>$_POST['address2'],"city"=>$_POST['city'],"county"=>$_POST['state'],"postcode"=>$_POST['postcode'],"country"=>$_POST['country'],"phone"=>$_POST['phone'],"email"=>$_POST['email'],"website"=>$_POST['homepage'],"company"=>$_POST['company']);
		if($_POST['matchbilling'][0]==1)
		{
			$_SESSION['address_details']['delivery']=array("firstname"=>$_POST['firstname'],"lastname"=>$_POST['lastname'],"address1"=>$_POST['address1'],"address2"=>$_POST['address2'],"city"=>$_POST['city'],"county"=>$_POST['state'],"postcode"=>$_POST['postcode'],"country"=>$_POST['country'],"phone"=>$_POST['phone'],"comments"=>$_POST['comments'],"sameasbilling"=>$_POST['matchbilling'][0]);
		}
		else
		{
			$_SESSION['address_details']['delivery']=array("firstname"=>$_POST['deliver_firstname'],"lastname"=>$_POST['deliver_lastname'],"address1"=>$_POST['deliver_address1'],"address2"=>$_POST['deliver_address2'],"city"=>$_POST['deliver_city'],"county"=>$_POST['deliver_state'],"postcode"=>$_POST['deliver_postcode'],"country"=>$_POST['deliver_country'],"phone"=>$_POST['deliver_phone'],"comments"=>$_POST['comments'],"sameasbilling"=>$_POST['matchbilling'][0]);
		}
		header("Location: $mainbase/index.php?p=postage_choice");
		exit();
	}
}
/* redirect back if criteria not met */
if(($_SESSION['loggedin']!=0||$_SESSION['checkoutnew']==1)&&(!is_array($_SESSION['cart'])||count($_SESSION['cart'])==0)&&in_array($page,array("checkout_address","postage_choice","payment")))
{
	header("Location: $mainbase/index.php?p=shopping_basket");//logged in but cart empty
}
else if($_SESSION['loggedin']==0&&$_SESSION['checkoutnew']==0&&in_array($page,array("checkout_address","postage_choice","payment")))
{
	header("Location: $mainbase/index.php?p=checkout_login");//not logged in
}
if((!isset($_SESSION['address_details'])||count($_SESSION['address_details'])<1)&&in_array($page,array("postage_choice","payment")))
{
	header("Location: $mainbase/index.php?p=checkout_address");//no address in session
}
/* / redirect back if criteria not met */
if(!in_array($page,array("postage_choice","review","notificationPage"))){unset($_SESSION['terms_agree']);}

function checkstock($skuvar)
{
	$avail=0;
	$q=mysql_query("SELECT nav_qty FROM nav_stock WHERE nav_skuvar='$skuvar'");
	list($avail)=mysql_fetch_row($q);
	return $avail;
}
function debug($debug)
{
	if(isset($_GET['stella'])||$islocal){
	?><div style="position:absolute;top:0;left:0;color:red;background:#fff;padding:4px;border:1px solid red;"><pre><? print_r($debug);	?></pre></div><?
	}
}
?>