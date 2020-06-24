<?
if(basename($_SERVER['PHP_SELF'])!="admin.php"){header("Location: ../admin.php");}//direct access security 
function hashit($in1,$in2)
{
	$salt=hash("sha256",$in1.$in2);
	return hash("sha256",$in2.$salt);
}
if(isset($_POST['updatenotes']))
{
	mysql_query("UPDATE admin_users SET `notes`='".$_POST['notes']."' WHERE `admin_id`='".$_POST['updatenotes']."'");
}

/*...............................................................
.......... GENERATE & DOWNLOAD CUSTOMERS MAILING LIST ...........
................................................................*/
if(isset($_GET['genmailing'])){
		genxml("SELECT `email`,`mailing` FROM customers WHERE `mailing` > '0' GROUP BY `email` ORDER BY `mailing`","lafuma_mailing.xls",array("email","mailing"),array("mailing"=>array("None","HTML","Plain Text")));
}
/*...............................................................
........................... PROMOTIONS .......................... 
................................................................*/
if($thepage=="promotions"&&((isset($_POST['formid'])&&!isset($_POST['submitdept'])&&!isset($_POST['backtodept'])&&!isset($_POST['prodpop']))||isset($_GET['delete_id'])))
{
	$founderrors="";
	if(isset($_GET['delete_id'])){mysql_query("DELETE FROM discounts WHERE `discount_id`='".$getescaped['delete_id']."'");}
	if($_POST['formid']=="add"||$_POST['formid']=="edit")
	{
		$fields=explode(",",$postescaped['requiredf']);
		$values=explode(",",$postescaped['requiredv']);
		$required=array_combine($fields,$values);
		$founderrors.=emptyfieldscheck($_POST,$required);
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		else
		{
			$start=explode("-",$postescaped['date_start']);
			$start_date=mktime(0,0,0,$start[1],$start[2],$start[0]);
			$end=explode("-",$postescaped['date_end']);
			$end_date=mktime(0,0,0,$end[1],$end[2],$end[0]);
			$onuk=$postescaped['on_uk_list']==1?1:0;
			$onie=$postescaped['on_ie_list']==1?1:0;
			if($_POST['formid']=="add")
			{
				mysql_query("INSERT INTO discounts(`code`,`discount`,`uselist`,`prodlist`,`date_start`,`date_end`,`date_created`,`on_uk_list`,`on_ie_list`,`mintotal`,`mintotal_euro`,`admin_id`)VALUES('".$postescaped['code']."','".$postescaped['discount']."','".$postescaped['uselist']."','".$postescaped['prodlist']."','".$start_date."','".$end_date."','".date("U")."','".$onuk."','".$onie."','".$postescaped['mintotal']."','".$postescaped['mintotal_euro']."','".$postescaped['admin_id']."')");
				header("Location: $mainbase/admin.php?p=promotions");
			}
			else
			{
				mysql_query("UPDATE discounts SET `code`='".$postescaped['code']."',`discount`='".$postescaped['discount']."',`date_start`='$start_date',`date_end`='$end_date',`date_edited`='".date("U")."',`on_uk_list`='$onuk',`on_ie_list`='$onie',`admin_id`='".$postescaped['admin_id']."', `uselist`='".$postescaped['uselist']."', `prodlist`='".$postescaped['prodlist']."',`mintotal`='".$postescaped['mintotal']."',`mintotal_euro`='".$postescaped['mintotal_euro']."' WHERE `discount_id`='".$getescaped['discount_id']."'");
				header("Location: $mainbase/admin.php?p=promotions");
			}
		}
	}
	else if($_POST['formid']=="stateupdate")
	{
		$ukstatuseson=implode("','",array_keys($postescaped['on_uk_list'],'1'));
		$ukstatusesoff=implode("','",array_keys($postescaped['on_uk_list'],'0'));
		$iestatuseson=implode("','",array_keys($postescaped['on_ie_list'],'1'));
		$iestatusesoff=implode("','",array_keys($postescaped['on_ie_list'],'0'));
		mysql_query("UPDATE discounts SET `on_uk_list`='1',`admin_id`='".$postescaped['admin_id']."' WHERE `discount_id` IN('$ukstatuseson')");
		mysql_query("UPDATE discounts SET `on_uk_list`='0',`admin_id`='".$postescaped['admin_id']."' WHERE `discount_id` IN('$ukstatusesoff')");	
		mysql_query("UPDATE discounts SET `on_ie_list`='1',`admin_id`='".$postescaped['admin_id']."' WHERE `discount_id` IN('$iestatuseson')");
		mysql_query("UPDATE discounts SET `on_ie_list`='0',`admin_id`='".$postescaped['admin_id']."' WHERE `discount_id` IN('$iestatusesoff')");
	}
}

/*...............................................................
......................... POSTAGE & PACKING ..................... 
................................................................*/
else if($thepage=="postage")
{
	$founderrors="";
	/*................. STATUS UPDATE ...........*/
	if(isset($_POST['status']))
	{
		$statuses=implode("','",$postescaped['status']);
		mysql_query("UPDATE postage_methods SET `status`='1' WHERE `post_id` IN('$statuses')");
		mysql_query("UPDATE postage_methods SET `status`='0' WHERE `post_id` NOT IN('$statuses')");
		if(in_array(7,$_POST['status'])){mysql_query("UPDATE postage_method_details SET `display`='1' WHERE `post_id`='7'");}//show freepost
		else{mysql_query("UPDATE postage_method_details SET `display`='0' WHERE `post_id`='7'");}
	}
	/*................ POSTAGE & PACKING INFO UPDATE ...............*/
	if(isset($_POST['pdid']))
	{
		$fieldstouse=array('5'=>array('description','field1','field2','field3','field1_euro','field2_euro','field3_euro'),'6'=>array('description','options','field1','field2','field3','field1_euro','field2_euro','field3_euro','restraints'));
		$fieldsrequired=array();
		$fieldsrequired['5']=array('description'=>'Description - Row ','field1'=>'Range start price (GBP) - Row ','field2'=>'Range end price (GBP) - Row ','field3'=>'Value (GBP) - Row ','field1_euro'=>'Range start price (EUR) - Row ','field2_euro'=>'Range end price (EUR) - Row ','field3_euro'=>'Value (EUR) - Row ');
		$fieldsrequired['6']=array('description'=>'Description - Row ','field1'=>'First item price (GBP) - Row ','field2'=>'Each additional item price (GBP) - Row ','field1_euro'=>'First item price (EUR) - Row ','field2_euro'=>'Each additional item price (EUR) - Row ');
		
		$founderrors.=emptyfieldscheck($_POST,$fieldsrequired[$_GET['postid']]);
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		else
		{
			foreach($_POST['pdid'] as $row => $pdid)
			{
				if($_POST['delete'][$pdid]==1)
				{
					mysql_query("DELETE FROM postage_method_details WHERE `post_details_id`='$pdid'");
				}
				else
				{
					/* HERE */
					$rday=strlen($_POST['restraint2'][$pdid])>0?$_POST['restraint2'][$pdid]." ":"";
					$rtime=strlen($rday)>0&&strlen($_POST['restraint3'][$pdid])==0?"00:00":(strlen($_POST['restraint3'][$pdid])>0?$_POST['restraint3'][$pdid]:"");
					$restraintstring=strlen($_POST['restraint2'][$pdid])>0||strlen($_POST['restraint3'][$pdid])>0?"time#".$_POST['restraint1'][$pdid]."#".$rday.$rtime:"";
					$avails="";
					foreach($_POST['avail'][$pdid] as $countr => $onoff)
					{
						if($onoff==1){if(strlen($avails)>0){$avails.=",";}$avails.="-".$countr."-";}
					}
					if($pdid=="new")
					{
						$query="INSERT INTO postage_method_details(`post_id`,";
						$query.=implode(",",$fieldstouse[$getescaped['postid']]);//"description,field1,field2,field3,restraints";
						$query.=",`availability`)VALUES('".$getescaped['postid']."',";
						$fieldvals="";
						foreach($fieldstouse[$_GET['postid']] as $field)
						{
							if(strlen($fieldvals)>0){$fieldvals.=",";}
							$fieldvals.="'".$postescaped[$field][$pdid]."'";
						}
						$query.=$fieldvals;
						$query.=",'".$avails."')";
						mysql_query($query);
					}
					else
					{
						/*
						UPDATE postage_method_details SET `description`='Saturday Delivery',`field1`='19.95',`field2`='0.00',`field3`='19.95',`field1_euro`='24',`field2_euro`='0',`field3_euro`='24',`restraints`='time#before#Friday 12:00',`availability`='-GB-,-IM-' WHERE `post_details_id`='8'
						
						*/
						$query="UPDATE postage_method_details SET ";
						$fieldvals="";
						foreach($fieldstouse[$_GET['postid']] as $field)
						{
							if(strlen($fieldvals)>0){$fieldvals.=",";}
							$fieldvals.="`".$field."`='".($field=="restraints"?$restraintstring:$postescaped[$field][$pdid])."'";
						}
						$fieldvals.=",`availability`='".$avails."'";
						$query.=$fieldvals;
						$query.=" WHERE `post_details_id`='$pdid'";
						mysql_query($query);
					}
				}
			}
			header("Location: $mainbase/admin.php?p=postage&act=edit&postid=".$getescaped['postid']."");
		}
	}
}

/*...............................................................
.......................... ADMINISTRATORS ....................... 
................................................................*/
else if($thepage=="admins")
{
	if(isset($_POST['username']))
	{
		$arequire=array("username"=>"Please enter a user name","email"=>"Please enter an email address");
		if($action=="add"){$arequire['password']="Please enter a password";}
		$founderrors="";
		$founderrors.=emptyfieldscheck($_POST,$arequire);
		$founderrors.=validemail($_POST['email'],"email");
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		else
		{
			extract($postescaped);
			if(strlen($password)>0){
				$firstwave=hashit($username,$password);
				$apass="*".hashit($username,$firstwave);
			}
			$passcol=(strlen($password)>0)?",`password`":"";
			$passequal =(strlen($password)>0)?"=":"";
			$passcomma =(strlen($password)>0)?",":"";
			$passval=(strlen($password)>0)?"'$apass'":"";
			$permissions=(count($amods)>0)?implode(",",array_keys($amods)):"";
			if($action=="add"){
			$query="INSERT INTO admin_users(`username`,`email`,`date_created` $passcol)VALUES('$username','$email','$date' $passcomma $passval)";
			$inauid=mysql_insert_id();
			$query1="INSERT INTO admin_permissions(`user_id`,`permissions`)VALUES('$inauid','$permissions')";
			}
			else {
			$query="UPDATE admin_users SET `username`='$username',`email`='$email',`date_edited`='$date' $passcol $passequal $passval WHERE `admin_id`='$getescaped[auid]'";
			$query1="UPDATE admin_permissions SET `permissions`='$permissions' WHERE `user_id`='$getescaped[auid]'";
			}
			mysql_query($query);
			mysql_query($query1);
		}
	}
	else if($action=="delete"&&isset($_GET['auid']))
	{
		mysql_query("DELETE FROM admin_users WHERE `admin_id`='$getescaped[auid]'");
		mysql_query("DELETE FROM admin_permissions WHERE `user_id`='$getescaped[auid]'");
	}
}

/*...............................................................
............................ ENQUIRIES ........................... 
................................................................*/
else if($thepage=="enquiries")
{
	if(($action=="delete" && isset($_GET['eid']))||(isset($_POST['delenq'])&&count($_POST['delenq'])>0))
	{
		$dels=$action=="delete"?array($_GET['eid']):array_keys($_POST['delenq']);
		$loops=count($dels);
		for($x=0;$x<$loops;$x++){mysql_query("DELETE FROM contactus WHERE `contactus_id`='$dels[$x]'");}
	}
}
/*...............................................................
............................ REVIEWS ........................... 
................................................................*/
else if($thepage=="reviews")
{
	if(($action=="delete" && isset($_GET['rid']))||(isset($_POST['delrev'])&&count($_POST['delrev'])>0))
	{
		$dels=$action=="delete"?array($_GET['rid']):array_keys($_POST['delrev']);
		$loops=count($dels);
		for($x=0;$x<$loops;$x++){mysql_query("DELETE FROM customerreviews WHERE `cust_rev_id`='$dels[$x]' OR `owner_id`='$dels[$x]'");}
	}
	else if(isset($_POST['comment']))
	{
		if($_POST['what']=="UPDATE")
		{
			mysql_query("UPDATE customerreviews SET comment='".mysql_real_escape_string($_POST['comment'])."' WHERE owner_id='".$_GET['rid']."'");
		}
		else
		{
			mysql_query("INSERT INTO customerreviews(`comment`,`owner_id`)VALUES('".mysql_real_escape_string($_POST['comment'])."','".$_GET['rid']."')");
		}
	}
}
/*...............................................................
............................ CUSTOMERS ........................... 
................................................................*/
else if($thepage=="customers")
{
	if($action=="edit"&&isset($_POST['lastname']))
	{
		//check email exists
		$founderrors="";
		$founderrors.=emptyfieldscheck($_POST,array("firstname"=>"Please enter a first name","lastname"=>"Please enter a last name","email"=>"Please enter an email address","address1"=>"Please enter the first line of the customer's address","city"=>"Please enter the city","state"=>"Please enter the county","postcode"=>"Please enter the postcode","country"=>"Please enter the country"));
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		else
		{
			extract($_POST);
			//encrypt password
			if(strlen($password)>0)
			{
				$firstwave=hashit($email,$password);
				$pass="`gpassword`='".hashit($email,$firstwave)."', ";
			}
			//update
			mysql_query("UPDATE customers SET `firstname`='$firstname', `lastname`='$lastname', `email`='$email',$pass `address1`='$address1', `address2`='$address2', `city`='$city', `state`='$state', `country`='$country', `postcode`='$postcode', `company`='$company', `phone`='$phone', `homepage`='$homepage', `mailing`='$mailing', `status`='$status' WHERE `cust_id`='$custid'");
			//redirect to user details
			header("Location: $mainbase/admin.php?p=customers&act=view&cust_id=$custid");
		}
	}
	else if($action=="add"&&isset($_POST['lastname']))
	{
		//check email exists
		$founderrors="";
		$dupecheckq=mysql_query("SELECT `cust_id` FROM customers WHERE `email`='$postescaped[email]'");
		$dupes=mysql_num_rows($dupecheckq);$dupe=mysql_fetch_row($dupecheckq);
		if($dupes>0){$founderrors.="That email address is already in use <a href='$mainbase/admin.php?p=customers&act=view&cust_id=$dupe[0]'>here</a>";}
		$founderrors.=emptyfieldscheck($_POST,array("password"=>"Please enter a password","firstname"=>"Please enter a first name","lastname"=>"Please enter a last name","email"=>"Please enter an email address","address1"=>"Please enter the first line of the customer's address","city"=>"Please enter the city","state"=>"Please enter the county","postcode"=>"Please enter the postcode","country"=>"Please enter the country"));
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		else
		{
			extract($_POST);
			//encrypt password
			$firstwave=hashit($email,$password);
			$pass=hashit($email,$firstwave);
			//insert
			mysql_query("INSERT INTO customers (`firstname`,`lastname`,`email`,`gpassword`,`address1`,`address2`,`city`,`state`,`country`,`postcode`,`company`,`phone`,`homepage`,`mailing`,`signup_date`,`status`)VALUES('$firstname','$lastname','$email','$pass','$address1','$address2','$city','$state','$country','$postcode','$company','$phone','$homepage','$mailing','".date("U")."','1')");
			$custid=mysql_insert_id();
			//redirect to new user details
			header("Location: $mainbase/admin.php?p=customers&act=view&cust_id=$custid");
		}
	}
	else if($_POST['items'])
	{
		if($_POST['items']=="Update")
		{
			$son="";
			$soff="";
			foreach($_POST['status'] as $cuid => $onoff)
			{
				if($onoff==1){if($son!=""){$son.=",";}$son.="'$cuid'";}
				else if($onoff==0){if($soff!=""){$soff.=",";}$soff.="'$cuid'";}
			}
			if(strlen($son)>0){mysql_query("UPDATE customers SET `status`='1' WHERE `cust_id` IN($son) AND `status`!='1'");}
			if(strlen($soff)>0){mysql_query("UPDATE customers SET `status`='0' WHERE `cust_id` IN($soff) AND `status`!='0'");}
		}
		else if($_POST['items']=="Delete"&&count($_POST['delete'])>0)
		{
			$del="";
			foreach($_POST['delete'] as $cuid => $onoff)
			{
				if($del!=""){$del.=",";}
				$del.="'$cuid'";
			}
			mysql_query("DELETE FROM customers WHERE `cust_id` IN($del)");
		}
	}
	//pprint_r($_POST);
}
/*...............................................................
............................ INVOICES ........................... 
................................................................*/
else if($thepage=="invoices")
{
	/*........................ UPDATE MULTIPE ORDERS ..........................*/
	if(isset($_POST['tracking']))
	{
		foreach($_POST['order_id'] as $invoice => $orderid)
		{
			//$invoice=$_POST['invoice'][$orderid];
			$datebits=explode("/",$_POST['date_shipped'][$orderid]);
			$shipper=(strlen($_POST['shipper'][$orderid])<1)?$_POST['shipper2'][$orderid]:$_POST['shipper'][$orderid];
			if(strlen($shipper)>0&&count($datebits)>2)
			{
				$day=($datebits[0]<10&&strlen($datebits[0])>1)?substr($datebits[0],1,1):$datebits[0];
				$month=($datebits[1]<10&&strlen($datebits[1])>1)?substr($datebits[1],1,1):$datebits[1];
				$year=(strlen($datebits[2])==2)?"20".$datebits[2]:$datebits[2];
				$shipdate=date("U",mktime(0,0,0,$month,$day,$year));
				$oshipq=mysql_query("SELECT `ordership_id` FROM ordership WHERE `order_id`='$orderid'");$oship=mysql_fetch_row($oshipq);
				if($oship)
				{
					mysql_query("UPDATE ordership SET `shipper`='$shipper',`tracking`='".$postescaped['tracking'][$orderid]."',`date_shipped`='$shipdate' WHERE `ordership_id`='$oship[0]'");
				}
				else
				{
					mysql_query("INSERT INTO ordership(`order_id`,`shipper`,`tracking`,`date_shipped`) VALUES('$orderid','$shipper','".$postescaped['tracking'][$orderid]."','$shipdate')");
				}
				mysql_query("UPDATE orders SET `order_status`='Shipped' WHERE `order_id`='$orderid'");
				if($_POST['notify'][$orderid]){
					$custq=mysql_query("SELECT orders.`email`,orders.`firstname`,orders.`lastname`,orders.`cust_id`,`mailing` FROM (orders LEFT JOIN customers ON customers.`cust_id`=orders.`cust_id` AND orders.`cust_id`!='0') WHERE `order_id`='$orderid'")or die("Error");
					$cust=mysql_fetch_assoc($custq);
					$conttype=$contenttype[$cust['mailing']];
					$headers = "From: Lafuma UK <$admin_email>\r\n";
					$headers .= "Reply-To: $admin_email\r\n";
					$headers .= "Return-Path: $admin_email\r\n";
					$headers .= "MIME-Version: 1.0\r\nContent-Type: $conttype; charset=UTF-8\r\n";
					$message="Dear $cust[firstname] $cust[lastname],".$br[$cust['mailing']].
					$br[$cust['mailing']].
					"Invoice Number: $invoice".$br[$cust['mailing']].
					$br[$cust['mailing']].
					"We are pleased to inform you that your order has been sent.".$br[$cust['mailing']].
					"================================================".$br[$cust['mailing']].
					"Method: $shipper ".$br[$cust['mailing']].
					"Tracking Number: ".$postescaped['tracking'][$orderid].$br[$cust['mailing']];
					if($cust['cust_id']!=0){
						$message.=$br[$cust['mailing']].
						"You can check the status of your order by going to the url below ".$br[$cust['mailing']].
						"$mainbase/index.php?p=receipt&invoice=$invoice ".$br[$cust['mailing']];
					}
					$message.="=================================================".$br[$cust['mailing']].
					"Thank you for your business. ".$br[$cust['mailing']].
					"Lafuma UK".$br[$cust['mailing']].
					"Bear House,".$br[$cust['mailing']].
					"Concorde Way,".$br[$cust['mailing']].
					"Fareham,".$br[$cust['mailing']].
					"Hampshire,".$br[$cust['mailing']].
					"PO15 5RL".$br[$cust['mailing']].
					"United Kingdom".$br[$cust['mailing']].
					"Email: $admin_email".$br[$cust['mailing']].
					"Tel: $sales_phone".$br[$cust['mailing']].
					$br[$cust['mailing']].
					"vat. Registration No: $vatreg".$br[$cust['mailing']].
					"Company Registration No.: $coreg".$br[$cust['mailing']].
					"=================================================".$br[$cust['mailing']];
					$to=($testing==1)?"senfield@gmk.co.uk":$cust['email'];
					mail($to,"Your order from Lafuma UK has shipped!",$message,$headers,"-f".$admin_email);/*send mail*/
				}
				if($action=="updatemany"){header("Location: $mainbase/admin.php?p=invoices".$invsort);}
				else{header("Location: $mainbase/admin.php?p=invoices&act=view&invoice=$_GET[invoice]");}
			}
			else
			{
				if(!isset($_SESSION['error'])){$_SESSION['error']="";}
				if(count($datebits)<3)
				{
					$_SESSION['error'].="Please specify a valid date for invoice ".$_POST['invoice'][$orderid].".<br />";
					array_push($higherr,"ship_date_".$orderid);
				}
				if(strlen($shipper)<1)
				{
					$_SESSION['error'].="Please specify the shipping method for invoice ".$_POST['invoice'][$orderid].".<br />";
					array_push($higherr,"shipper_".$orderid);
					array_push($higherr,"shipper2_".$orderid);
				}
			}
		}
	}
	/*........................UPDATE MULTIPE ORDERS.............................*/
	else if($action=="updatemany")
	{
		foreach($postescaped['inv'] as $inv => $stat)
		{
			if($stat!="Delete"&&$stat!="Shipped"){
				mysql_query("UPDATE orders SET `order_status`='$stat' WHERE `invoice`='$inv'");
			}
		}	
	}
	/*............................DELETE MULTIPLE ORDERS..........................*/
	/*else if(isset($_POST['delinv']))
	{
		$invoices=(count($_POST['delinv'])>1)?"IN('".implode("','",$_POST['delinv'])."')":"='".$_POST['delinv'][0]."'";
		$invtodelq=mysql_query("SELECT order_id FROM orders WHERE invoice $invoices")or die("SELECT order_id FROM orders WHERE invoice $invoices".mysql_error());
		$ordernums="";
		while($invtodel=mysql_fetch_row($invtodelq))
		{
			if($ordernums!=""){$ordernums.=",";}
			$ordernums.="'$invtodel[0]'";
		}
		mysql_query("DELETE FROM orders WHERE order_id IN($ordernums)");
		mysql_query("DELETE FROM orderproducts WHERE order_id IN($ordernums)");
		mysql_query("DELETE FROM orderkits WHERE order_id IN($ordernums)");
		mysql_query("DELETE FROM ordership WHERE order_id IN($ordernums)");
	}*/
	/*..............................UPDATE ORDER STATUS............................*/
	else if(isset($_POST['updateinv']))
	{
		$ps=($_POST['opaid']==1)?1:0;
		$ios=($_POST['ocomp']==1)?1:0;
		$os=$_POST['newstatus'];
		if(strtolower($os)!="delete")
		{
			mysql_query("UPDATE orders SET `pay_status`='$ps',`iorder_status`='$ios',`order_status`='$os' WHERE `invoice`='$getescaped[invoice]'");
			if(strtolower($os)!="shipped"){header("Location: $mainbase/admin.php?p=invoices&act=view&invoice=$_GET[invoice]");}
		}
	}
	/*...............................UPDATE ORDER ITEMS................................*/
	else if(isset($_POST['qty']))
	{
		$new_qty=0;
		$new_sub_total=0;
		$orderq=mysql_query("SELECT `ship_method_id`,discounts.`discount`,`price` FROM (orders JOIN orderproducts ON orders.`order_id`=orderproducts.`order_id`) LEFT JOIN discounts ON orders.`discount_code`=discounts.`code` WHERE `invoice`='$getescaped[invoice]'");
		$order=mysql_fetch_assoc($orderq);
		$shipid=($order['ship_method_id']==0)?5:$order['ship_method_id'];
		$postq=mysql_query("SELECT * FROM postage_methods JOIN postage_method_details ON postage_methods.`post_id`=postage_method_details.`post_id` WHERE `post_details_id`='$shipid'");
		$post=mysql_fetch_assoc($postq);
		
		foreach($postescaped['skuvariant'] as $spid => $val)
		{
			if(strlen(stristr($val,"-qty-"))>0)
			{
				$expqty=explode("-qty-",$val);
				$expsku=explode("-v-",$expqty[0]);
				$optvq=mysql_query("SELECT `optval_id`,`item_desc`,`description` FROM option_values as ov LEFT JOIN product_options as po ON po.`opt_id`=ov.`opt_id` WHERE `variant_id`='$expqty[0]'");
				list($gopt,$col,$type)=mysql_fetch_row($optvq);
				mysql_query("UPDATE orderproducts SET `goptid`='$gopt',`oitem`='$col',`oname`='$type' WHERE `order_prod_id`='".$postescaped['popttoorderopt'][$spid]."'");
			}
		}
		foreach($postescaped['qty'] as $opid => $qty)
		{
			mysql_query("UPDATE orderproducts SET `qty`='$qty' WHERE `order_prod_id`='$opid'");
			$new_qty+=$qty;
			$new_sub_total+=($postescaped['price'][$opid]*$qty);
		}
		$postage=($new_qty>0)?(($new_sub_total>$post['field2'])?$post['field1']:$post['field3']):0;
		$newvat=$vat*($new_sub_total/100);
		$newtotal=$new_sub_total+$newvat;
		
		if(strlen($order['discount_code'])>0){
			$discount=(($newtotal/100)*$order['discount']);
			$newtotal=($newtotal+$postage)-$discount;
		}
		else
		{
			$newtotal=$newtotal+$postage;
		}
		mysql_query("UPDATE orders SET `total_price`='$newtotal',`tax_price`='$newvat' WHERE `invoice`='$getescaped[invoice]'");
	}
}
/*...............................................................
............................. BUILDER ........................... 
................................................................*/
else if($thepage=="builder")
{
	/* .....................BUILDER: UNATTACHING PRODUCT OPTIONS TO PRODUCT.......................*/
	if($_POST['opt_unatt'])
	{
		$_SESSION['avail_opts'][count($_SESSION['avail_opts'])]=array($_SESSION['cur_opts'][$_POST['current']][0],$_SESSION['cur_opts'][$_POST['current']][1],count($_SESSION['avail_opts']));
		array_splice($_SESSION['cur_opts'],$_POST['current'],1);
		if(count($_SESSION['cur_opts'])<1&&$_POST['current']==0){$_SESSION['cur_opts']=array();}
		$highlight=0;
	}
	/* .......................BUILDER: ATTACHING PRODUCT OPTIONS TO PRODUCT.......................*/
	else if($_POST['opt_att'])
	{
		if(is_array($_SESSION['avail_opts']))
		{
			$_SESSION['cur_opts'][count($_SESSION['cur_opts'])]=array($_SESSION['avail_opts'][$_POST['avail']][0],$_SESSION['avail_opts'][$_POST['avail']][1],count($_SESSION['cur_opts']));
			array_splice($_SESSION['avail_opts'],$_POST['avail'],1);
			$highlight=count($_SESSION['cur_opts'])-1;
		}
	}
	/* ..........................BUILDER: SORTING PRODUCT OPTIONS TO PRODUCT.......................*/
	else if($_POST['opt_ord_up']&&$_POST['current']>0)
	{
		$temp=$_SESSION['cur_opts'][$_POST['current']-1];//give temp the higher item's array
		$_SESSION['cur_opts'][$_POST['current']-1]=$_SESSION['cur_opts'][$_POST['current']];//put array into higher array
		$_SESSION['cur_opts'][$_POST['current']]=$temp;//rehome higher array to lower sorting
		$highlight=$_POST['current']-1;
	}
	else if($_POST['opt_ord_dn']&&$_POST['current']<count($_SESSION['cur_opts'])-1)
	{
		$temp=$_SESSION['cur_opts'][$_POST['current']+1];//give temp the lower item's array
		$_SESSION['cur_opts'][$_POST['current']+1]=$_SESSION['cur_opts'][$_POST['current']];//put array into higher array
		$_SESSION['cur_opts'][$_POST['current']]=$temp;//rehome higher array to lower sorting
		$highlight=$_POST['current']+1;
	}
	/* ..........................BUILDER: SAVE PRODUCT OPTIONS TO PRODUCT.......................*/
	else if($_POST['save_attachments'])
	{
		if(isset($_SESSION['cur_opts']))
		{
			/*DELTE OLD*/
			mysql_query("DELETE FROM fusion_options WHERE `prod_id`='".$getescaped['pid']."'");
			/*INSERT NEW*/
			foreach($_SESSION['cur_opts'] as $sortord => $array)
			{
				mysql_query("INSERT INTO fusion_options(`prod_id`,`opt_id`,`vsort`) VALUES('".$getescaped['pid']."','".$array[0]."','".($sortord+1)."')");
			}
			$successattach="Changes have been saved, please click <a href='$mainbase/admin.php?p=images&amp;act=view&amp;id=".$_GET['id']."&amp;what=product&amp;pid=".$_GET['pid']."'>HERE</a> to update product images.";
		}
	}
	/* ...........................BUILDER: SORTING AND SETTING ON/OFF..............................*/
	else if(isset($_POST['sort']))
	{
		foreach($postescaped['sort'] as $ffid => $sort)
		{
			$onoff=($postescaped['onoff'][$ffid]==1)?1:0;
			$allowoffer=($postescaped['allowoffer'][$ffid]==1)?1:0;
			$on_uk_list=($postescaped['on_uk_list'][$ffid]==1)?1:0;
			$on_ie_list=($postescaped['on_ie_list'][$ffid]==1)?1:0;
			mysql_query("UPDATE fusion SET `iSort`='$sort', `iState`='$onoff', `allowoffer`='$allowoffer' WHERE `fusionId`='$ffid'") or die(mysql_error());
			$ppidq=mysql_query("SELECT `iSubId_FK` FROM fusion WHERE `fusionId`='$ffid' AND `vtype`='product'");
			list($ppid)=mysql_fetch_row($ppidq);
			mysql_query("UPDATE products SET `on_uk_list`='$on_uk_list', `on_ie_list`='$on_ie_list' WHERE `prod_id`='$ppid'");
		}
		header("Location: $mainbase/admin.php?".$_SERVER['QUERY_STRING']);
	}
	/* ..........................BUILDER: ATTACHING AN ITEM TO CAT/PROD............................*/
	else if(isset($_POST['item']))
	{
		$sorting=$_POST['nextsort'];
		foreach($postescaped['item'] as $arrid => $ppid)
		{
			mysql_query("INSERT INTO fusion(`iOwner_FK`,`iSubId_FK`,`vOwnerType`,`vtype`,`iSort`,`iState`) VALUES('".intval($getescaped['oid'])."','$ppid','$what','product','$sorting','1')");
			$sorting++;
		}
		header("Location: $mainbase/admin.php?p=builder&act=attach&what=$what&id=$_GET[id]&name=$_GET[name]");
	}
	/* ...........................BUILDER: UNATTACHING AN ITEM FROM CAT/PROD........................*/
	else if(isset($_GET['unattach']))
	{
		mysql_query("DELETE FROM fusion WHERE `fusionId`='".intval($getescaped['unattach'])."'");
		$newact=($action=="delete"||$action=="view")?"view":"attach";
		header("Location: $mainbase/admin.php?p=builder&act=$newact&what=$what&id=$_GET[id]&name=$_GET[name]");
	}
	/* ............................BUILDER: DELETING PRODUCT/DEPARTMENT.............................*/
	else if(isset($_GET['dodelete']))
	{
		if($fid!=""){
			mysql_query("DELETE FROM fusion WHERE `fusionId`='$fid'");
		}
		if($pid!=""){
			
			foreach($images_arr['product']['images'] as $dir => $size)
			{
				$subdir=($dir!="main")?"/".$dir:"";
				$images=glob($images_arr['product']['path'].$subdir."/".$pid."-*");
				foreach($images as $img)
				{
					delete_img($img);
				}
			}
			
			mysql_query("DELETE FROM products WHERE `prod_id`='$pid'");//remove product
			mysql_query("DELETE FROM fusion_options WHERE `prod_id`='$pid'");//unattach option attachments
			mysql_query("DELETE FROM fusion WHERE `iOwner_FK`='$pid' AND `vOwnerType`='product'");//unattach all immediate children*/
		}
		if($cid!=""){
			$imgQ=mysql_query("SELECT `image1` FROM categories WHERE `cat_id`='$cid'");
			list($img)=mysql_fetch_row($imgQ);
			foreach($images_arr['department']['images'] as $dir => $size)
			{
				$subdir=($dir!="main")?"/".$dir:"";
				delete_img($images_arr['department']['path'].$subdir."/".$img);
			}
			mysql_query("DELETE FROM categories WHERE `cat_id`='$cid'");//delete dept
			mysql_query("DELETE FROM fusion WHERE `iOwner_FK`='$cid' AND `vtype`='department'");//unattach children
		}
		$name=($_GET['id']>0&&isset($_GET['name'])&&$_GET['name']!="")?"&name=$_GET[name]":"";
		if(isset($_GET['report']))
		{
			header("Location: $mainbase/admin.php?p=reports&report=$_GET[report]".((isset($_GET['rpage']))?"&page=$_GET[rpage]&genfroogle=1":""));
		}
		else
		{
			header("Location: $mainbase/admin.php?p=builder&act=view&what=$what&id=$_GET[id]".$name."&genfroogle=1");
		}
	}
	/* ...............................BUILDER: ADDING/EDITING PRODUCT....................................*/
	else if(isset($_POST['prodform']))
	{
		$onoff=($_POST['iState']!=1)?0:1;
		$allowoffer=($_POST['allowoffer']!=1)?0:1;
		$exclude_discount=($_POST['exclude_discount']!=1)?0:1;
		$sale=($_POST['sale']!=1)?0:1;
		$taxable=($_POST['taxable']!=1)?0:1;
		$thedate=(isset($_POST['origdate']))?$_POST['origdate']:$date;
		$newfilename=$thedate.$_POST['sku'].".jpg";
		
		/* RENAME IMAGE IF SKU CHANGED*/
		/*if(isset($_POST['origsku']))//editing product
		{
			if($_POST['origsku']!=$_POST['sku'])//sku changed
			{
				foreach($_POST['imgtype'] as $tid => $tval)
				{
					$oldfile=$images_arr[$what]['path'].$tval."/".$_POST['old_img'];
					$newfile=$images_arr[$what]['path'].$tval."/".$newfilename;
					if(file_exists($oldfile)){rename($oldfile,$newfile);}
				}
			}
		}*/
		
		$founderrors.=emptyfieldscheck($_POST,array("title"=>"Please enter the product title","sku"=>"Please enter the product code","barcode"=>"Please enter the barcode","price"=>"Please enter the price (ex. VAT)","price_euro"=>"Please enter the euro price (ex. VAT)","seo_title"=>"Please enter the filename (URL)"));
		if(strlen($_POST['barcode'])<12){$founderrors.="Please enter a valid barcode (12-13 digits)";}
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		
		/*NO ERRORS - CONTINUE*/
		else
		{
			$shortdesc=mysql_real_escape_string(str_replace("�","",$_POST['shortdesc']));
			$metadesc=mysql_real_escape_string(str_replace("�","",$_POST['metadesc']));
			$content=mysql_real_escape_string(str_replace("�","",$_POST['content']));			
			/*ADD NEW PRODUCT TO SQL*/
			if($_POST['prodform']=="add"||$_POST['prodform']=="duplicate")
			{
				mysql_query("INSERT INTO products(`title`,`seo_title`,`shortdesc`,`metadesc`,`content`,`img_filename`,`sku`,`barcode`,`list_price`,`price`,`price_euro`,`list_price_euro`,`item_weight`,`shipnotes`,`exclude_discount`,`taxable`,`sale`,`date_created`,`admin_user`,`on_uk_list`,`on_ie_list`) VALUES('$postescaped[title]','$postescaped[seo_title]','$shortdesc','$metadesc','$content','','$postescaped[sku]','$postescaped[barcode]','$postescaped[list_price]','$postescaped[price]','$postescaped[price_euro]','$postescaped[list_price_euro]','$postescaped[item_weight]','$postescaped[shipnotes]','$exclude_discount','$taxable','$sale','$date','$postescaped[admin_id]','$postescaped[on_uk_list]','$postescaped[on_ie_list]')");
				$prodid=mysql_insert_id();
				mysql_query("INSERT INTO fusion(`iOwner_FK`,`iSubId_FK`,`vtype`,`iSort`,`iState`,`allowoffer`,`vOwnerType`) VALUES('$getescaped[oid]','$prodid','$getescaped[onitem]','$getescaped[nextsort]','$onoff','$allowoffer','$what')");
				$newfid=mysql_insert_id();
				/*UPLOAD IMAGES*/
				foreach($_FILES['uploadedfile']['tmp_name'] as $id => $tmp_name)
				{
					$douploads=fileupload($_POST['imgsize'][$id],$images_arr[$what]['path'],$tmp_name,$_FILES['uploadedfile']['name'][$id],array('jpg'),$prodid."-default.jpg",1,$prodid."-default.jpg",$_POST['imgtype'][$id]." Image",$id);
					if(strlen($douploads)>0){$founderrors.=$douploads."<br />";}
				}
			}
			
			/*EDIT PRODUCT IN SQL*/
			else
			{
				mysql_query("UPDATE products SET `title`='$postescaped[title]',`seo_title`='$postescaped[seo_title]',`shortdesc`='$shortdesc',`metadesc`='$metadesc',`content`='$content',`sku`='$postescaped[sku]',`barcode`='$postescaped[barcode]',`list_price`='$postescaped[list_price]',`price`='$postescaped[price]',`price_euro`='$postescaped[price_euro]',`list_price_euro`='$postescaped[list_price_euro]',`item_weight`='$postescaped[item_weight]',`shipnotes`='$postescaped[shipnotes]',`exclude_discount`='$postescaped[exclude_discount]',`taxable`='$postescaped[taxable]',`sale`='$postescaped[sale]',`date_edited`='$date',`admin_user`='$postescaped[admin_id]',`on_uk_list`='$postescaped[on_uk_list]',`on_ie_list`='$postescaped[on_ie_list]' WHERE `prod_id`='$getescaped[pid]'");
				mysql_query("UPDATE fusion SET `iState`='$onoff' WHERE `fusionId`='$postescaped[fusion]'");
			}
			/*REDIRECT TO ADD PRODUCT OPTION*/
			if($_POST['poopts']==1&&$_POST['prodform']=="add")
			{
				header("Location: $mainbase/admin.php?p=builder&act=attach_opts&id=$newfid&what=product&pid=$prodid&name=$_POST[title]&genfroogle=1");
			}
			
			/*ALL DONE - REDIRECT*/
			else
			{
				if(isset($_GET['report']))
				{
					header("Location: $mainbase/admin.php?p=reports&report=$_GET[report]".((isset($_GET['rpage']))?"&page=$_GET[rpage]&genfroogle=1":""));
				}
				else
				{
					header("Location: $mainbase/admin.php?p=builder&act=$act&what=$what&id=$_GET[id]&name=$_POST[name]&genfroogle=1");
				}
			}
		}
	}
	/* ..........................BUILDER: ADDING/EDITING DEPARTMENT..........................*/
	else if(isset($_POST['deptform']))
	{
		$onoff=($_POST['iState']!=1)?0:1;
		$displayed=($_POST['displayed']!=1)?0:1;
		$showmenuitem=($_POST['showmenuitem']!=1)?0:1;
		
		/* RENAME IMAGE IF TITLE CHANGED*/
		/*if(isset($_POST['origtitle']))//only posted if editing product
		{
			if($_POST['origtitle']!=$_POST['title'])//sku changed
			{
				$oldfile=$images_arr[$what]['path']."/".$_POST['old_img'];
				$newfile=$images_arr[$what]['path']."/".strtolower(str_replace(" ","_",$_POST['title'])).".jpg";
				if(file_exists($oldfile)){rename($oldfile,$newfile);}
			}
		}*/
		
		$founderrors.=emptyfieldscheck($_POST,array("title"=>"Please enter the department title"));
		
		/*UPLOAD IMAGE*/
		if($_POST['deptform']!="update" && strlen($founderrors)<1)
		{
			foreach($_FILES['uploadedfile']['tmp_name'] as $id => $tmp_name)
			{
				$douploads=fileupload($_POST['imgsize'][$id],$images_arr[$what]['path'],$tmp_name,$_FILES['uploadedfile']['name'][$id],array('jpg'),strtolower(str_replace(" ","_",$_POST['title'])).".jpg",1,$_POST['old_img'],"Main Image",$id);
				if(strlen($douploads)>0){$founderrors.=$douploads."<br />";}
			}
		}	
		
		if(strlen($founderrors)>0)
		{
			$_SESSION['error']=$founderrors;
		}
		
		/*NO ERRORS - CONTINUE*/
		else
		{
			/*ADD NEW DEPARTMENT TO SQL*/
			if($_POST['deptform']=="add"||$_POST['deptform']=="duplicate")
			{
				$newname=$postescaped['title'];
				mysql_query("INSERT INTO categories(`title`,`content`,`image1`,`date_created`,`displayed`,`showmenuitem`,`admin_user`) VALUES('$postescaped[title]','$postescaped[content]','".strtolower(str_replace(" ","_",$postescaped['title'])).".jpg','$date','$displayed','$showmenuitem','$postescaped[admin_id]')");
				$catid=mysql_insert_id();
				mysql_query("INSERT INTO fusion(iOwner_FK,iSubId_FK,vtype,iSort,iState,vOwnerType) VALUES('$getescaped[oid]','$catid','$getescaped[onitem]','$getescaped[nextsort]','$onoff','$what')");
			}
			
			/*EDIT DEPARTMENT IN SQL*/
			else
			{
				$newname=$getescaped['name'];
				mysql_query("UPDATE categories SET `title`='$postescaped[title]',`content`='$postescaped[content]',`date_edited`='$date',`displayed`='$displayed',`showmenuitem`='$showmenuitem',`admin_user`='$postescaped[admin_id]' WHERE `cat_id`='$cid'");
				mysql_query("UPDATE fusion SET `iState`='$onoff' WHERE `iOwner_FK`='$getescaped[oid]' AND `iSubId_FK`='$cid' AND `vtype`='department'");
			}
			
			/*ALL DONE - REDIRECT*/
			//header("Location: $mainbase/admin.php?p=builder&act=view&what=department&id=$_GET[id]&name=$_GET[name]&genfroogle=1");
		}
	}
}

/*...............................................................
......................... PRODUCT KITS ..........................
................................................................*/
else if($thepage=="packages")
{
	/* ........................PRODUCT KITS: ADD PRODUCT INTO KIT...................*/
	if(isset($_POST['item']))
	{
		$sorting=$postescaped['nextsort'];
		$info=explode("#",$postescaped['item']);
		mysql_query("INSERT INTO productkits(`kprod_id`,`iProdId_FK`,`item_qty`,`kit_sort`,`kit_sku`,`in_kit_list`) VALUES('$getescaped[kprod_id]','$info[0]','1','$sorting','$info[1]','2')");
		mysql_query("UPDATE products SET `kit`='2' WHERE `prod_id`='$getescaped[kprod_id]'");
		header("Location: admin.php?p=packages&act=edit&kprod_id=$_GET[kprod_id]&genfroogle=1");
	}
	else if($action=="edit")
	{
		/* ........................PRODUCT KITS: REMOVE PRODUCT FROM KIT...................*/
		if(isset($_GET['delete']))
		{
			mysql_query("DELETE FROM productkits WHERE `kit_id`='$getescaped[delete]'");
			header("Location: admin.php?p=packages&act=edit&kprod_id=$getescaped[kprod_id]&genfroogle=1");
		}
		/* ........................PRODUCT KITS: UPDATE QTY AND SORTING...................*/
		else if(isset($_POST['sort']))
		{
			$show=($_POST['in_kit_list']==1)?1:2;
			foreach($_POST['sort'] as $kitid => $sort)
			{
				mysql_query("UPDATE productkits SET `kit_sort`='$sort',`item_qty`='".$postescaped['qty'][$kitid]."',`in_kit_list`='$show' WHERE `kit_id`='$kitid'");
			}
			mysql_query("UPDATE products SET `kit`='$show' WHERE `prod_id`='$postescaped[prodid]'");
			header("Location: admin.php?p=packages&act=edit&kprod_id=$_GET[kprod_id]&genfroogle=1");
		}
		
	}
	/* ...................... PRODUCT KITS: NO DISASSEMBLE JOHNNY FIVE! ................*/
	else if($action=="disassemble"&&isset($_GET['delete']))
	{
		mysql_query("DELETE FROM productkits WHERE `kprod_id`='$getescaped[delete]'");
		mysql_query("UPDATE products SET `kit`='0' WHERE `prod_id`='$getescaped[delete]'");
		header("Location: admin.php?p=packages&genfroogle=1");
	}
}
/*...............................................................
........................... IMAGES ..............................
................................................................*/

else if($thepage=="images")
{
	/* ........................IMAGES: POST HANDLER FOR DELETING IMAGES...................*/
	if($action=="delete")
	{
		if(isset($_GET['imgtype'])&&$_GET['imgtype']!="")
		{
			$subdir=($_GET['imgtype']!="root")?"/".$_GET['imgtype']:"";
			delete_img($images_arr[$what]['path'].$subdir."/".$_GET['old_img']);
		}
		else
		{
			foreach($images_arr[$what]['images'] as $dir => $size)
			{
				$subdir=($dir!="main")?"/".$dir:"";
				delete_img($images_arr[$what]['path'].$subdir."/".$_GET['old_img']);
			}
		}
		if(!isset($_SESSION['error'])){$message="has been deleted successfully.";}
		if($what=="option_values")
		{
			mysql_query("UPDATE option_values SET `img_filename`='0' WHERE `optval_id`='$getescaped[optval_id]'");
		}
	}
	/* ...............................IMAGES: UPDATING IMAGES.............................*/
	else if($action=="update")
	{					
		//fileupload($imgsize,$filepath,$files_tmpname,$files_name,$allowed,$newname,$required,$oldname,$imgname,$loopid)
		if(isset($_POST['manyprodimages']))
		{
			//if(isset($_POST['filename']['default'])){mysql_query("UPDATE products SET img_filename='".$pid."-default.jpg' WHERE prod_id='$pid'");}
			foreach($postescaped['imgdel'] as $id => $yesno)
			{
				if($yesno==1){
					$imgname=str_replace("_".$_POST['imgtype'][$id],"",$id);//Black
					$prodimgpath=$images_arr['product']['path']."/".$_POST['imgtype'][$id];
					@unlink($prodimgpath."/".$_POST['filename'][$imgname]);
				}
			}
			if(isset($_POST['filename_alt'])){$filename=$_POST['filename_alt'];}
			foreach($_FILES['uploadedfile']['tmp_name'] as $id => $tmp_name)
			{
				if(strlen($tmp_name)>0)
				{
					$imgname=str_replace("_".$_POST['imgtype'][$id],"",$id);//Black
					$prodimgpath=$images_arr['product']['path']."/".$_POST['imgtype'][$id];
					if(!isset($_POST['filename_alt'])){$filename=$_POST['filename'][$imgname];}
					//echo "fileupload(".$_POST['imgsize'][$id].",$prodimgpath,$tmp_name,".$_FILES['uploadedfile']['name'][$id].",array('jpg'),".$filename.",0,".$filename.",$imgname,$id)<br />";
					$douploads=fileupload($_POST['imgsize'][$id],$prodimgpath,$tmp_name,$_FILES['uploadedfile']['name'][$id],array('jpg'),$filename,1,$filename,$imgname,$id);
					if(strlen($douploads)>0){$founderrors.=$douploads."<br />";}
				}
			}
			if(strlen($founderrors)>0){$_SESSION['error']=$founderrors;}
			else{/*header("Location: $mainbase/admin.php?p=images&act=view&id=".$_GET['id']."&what=product&pid=$pid");*/}
		}
		else if(isset($_FILES['uploadedfile']))
		{
			$col=($what=="department")?"image1":"img_filename";
			if($what=="department"){$table="categories";}
			if($what=="product"){$table="products";}
			if($what=="option_values"){$table="option_values";}
			
			/*UPLOAD IMAGE*/
			foreach($_FILES['uploadedfile']['tmp_name'] as $id => $tmp_name)
			{
				$imgname=($what=="option_values")?"this option":(isset($_POST['imgtype'])?$_POST['imgtype'][$id]:"Main")." Image";
				
				$prodimgpath=(isset($_POST['imgtype']))?$images_arr[$what]['path']."/".$_POST['imgtype'][$id]:$images_arr[$what]['path'];
				$douploads=fileupload($_POST['imgsize'][$id],$prodimgpath,$tmp_name,$_FILES['uploadedfile']['name'][$id],array('jpg'),$_POST['filename'],1,$_POST['old_img'],$imgname,$id);
				if(strlen($douploads)>0){$founderrors.=$douploads."<br />";}
			}
			if(strlen($founderrors)>0){$_SESSION['error']=$founderrors;}
			
			/*UPDATE IMAGE NAME IN SQL*/
			else{
				mysql_query("UPDATE $table SET $col='".$_POST['filename']."' WHERE $cp='$cpid'");
				$message="has been Updated.";
			}
		}
	}
}

/*...............................................................
......................... PRODUCT OPTIONS ....................... 
................................................................*/
else if($thepage=="product_options")
{
	/*....................PRODUCT OPTIONS: ADD PRODUCT OPTION AND VALUES.....................*/
	if($action=="add"&&isset($_POST['admin_id']))
	{
		//product_options:
		mysql_query("INSERT INTO product_options(`opt_name`,`description`,`state`,`date_created`,`date_edited`,`admin_user`) VALUES('$postescaped[opt_name]','$postescaped[description]','0','$date','$date','$postescaped[admin_id]')");
		$poptid=mysql_insert_id();
		$message="";
		$message.=emptyfieldscheck($_POST,array("description"=>"Please enter the description (eg: 'colour')","opt_name"=>"Please enter the title","variant_id"=>"Please choose the stock code for option:","item_edsc"=>"Please enter the option name for option:"));
		
		/*EACH OPTION VALUE*/
		foreach($_POST['variant_id'] as $id => $variant_id)
		{	
			$nolfm=str_replace(array("BAG","LFM"),"",$_POST['item_desc'][$id]);
			$removed=preg_replace("/[^A-Za-z.]/i","",$nolfm);
			$newfilename=(strlen($removed)<1||strlen($_FILES['uploadedfile']['tmp_name'][$id])<1)?"0":$poptid."_".$variant_id."_".strtolower($removed).".jpg";
			if(strlen($removed)<1)
			{
				$message.="Please check the option name you entered contains letters<br />";
			}
			
			/*NAME OK - CONTINUE UPLOAD IMAGE*/
			else
			{
				if(strlen($_FILES['uploadedfile']['tmp_name'][$id])<1)
				{
					$douploads=fileupload($_POST['imgsize'][$id],$images_arr[$what]['path'],$_FILES['uploadedfile']['tmp_name'][$id],$_FILES['uploadedfile']['name'][$id],array('jpg'),$newfilename,0,"","Option Swatch ID: ".($id+1),$id);
					if(strlen($douploads)>0){$message.=$douploads."<br />";}
				}
			}
			
			/*INSERT OPTION VALUE TO SQL*/
			mysql_query("INSERT INTO option_values(`opt_id`,`item_desc`,`price`,`price_euro`,`img_filename`,`vsort`,`variant_id`) VALUES('$poptid','".$postescaped['item_desc'][$id]."','".$_POST['price'][$id]."','".$_POST['price_euro'][$id]."','$newfilename','".($id+1)."','$variant_id')");
		}
		
		if(strlen($message)>0)
		{
			$_SESSION['error']=$message;
		}
		
		/*ALL DONE - REDIRECT*/
		else
		{
			header("Location: $mainbase/admin.php?p=product_options");
		}
	}
	/*......................PRODUCT OPTIONS: EDIT PRODUCT OPTION AND VALUES.................*/
	else if($action=="edit"&&isset($_POST['admin_id'])&&isset($_GET['opt_id']))
	{
		$message="";
		$message.=emptyfieldscheck($_POST,array("delete"=>"","description"=>"Please enter the description (eg: 'colour')","opt_name"=>"Please enter the title","variant_id"=>"Please choose the stock code for option:","item_desc"=>"Please enter the option name for option:"));
		
		if(strlen($message)>0)
		{
			$_SESSION['error']=$message;
		}
		else
		{
			/*ENTER MAIN PRODUCT OPTION TO SQL*/
			mysql_query("UPDATE product_options SET `opt_name`='$postescaped[opt_name]',`description`='$postescaped[description]',`date_edited`='$date',`admin_user`='$postescaped[admin_id]' WHERE `opt_id`='$getescaped[opt_id]'");
			
			/*EACH OPTION VALUE*/
			foreach($_POST['variant_id'] as $id => $variant_id)
			{
				/*DELETE OPTION VALUE IF CHECKED*/
				if($_POST['delete'][$id]==1)
				{
					$q=mysql_query("SELECT `img_filename` FROM option_values WHERE `optval_id`='$id'");
					$r=mysql_fetch_row($q);
					foreach($images_arr['product_options']['images'] as $dir => $size)
					{
						$subdir=($dir!="main")?"/".$dir:"";
						delete_img($images_arr['product_options']['path'].$subdir."/".$r['img_filename']);
					}
					mysql_query("DELETE FROM option_values WHERE `optval_id`='$id'");
				}
				else
				{
					$nolfm=str_replace(array("BAG","LFM"),"",$_POST['item_desc'][$id]);
					$removed=preg_replace("/[^A-Za-z.]/i","",$nolfm);
					$newimage=($removed=="")?"0":$_GET['opt_id']."_".$variant_id."_".strtolower($removed).".jpg";
					/*ADD ROW*/
					if($id=='addrow')
					{
						$douploads=fileupload($_POST['imgsize'][0],$images_arr['product_options']['path'],$_FILES['uploadedfile']['tmp_name'][0],$_FILES['uploadedfile']['name'][0],array('jpg'),$newimage,0,"","Option Swatch ID: ".($id+1),$id);
						
						if(strlen($douploads)>0)
						{
							$_SESSION['error']=$douploads;
						}
						else
						{
							mysql_query("INSERT INTO option_values(`opt_id`,`item_desc`,`price`,`price_euro`,`img_filename`,`vsort`,`variant_id`) VALUES('$getescaped[opt_id]','".$postescaped['item_desc'][$id]."','".$postescaped['price'][$id]."','".$postescaped['price_euro'][$id]."','$newimage','".$postescaped['vsort'][$id]."','$variant_id')");
						}
					}
					
					/*UPDATE OPTIONS*/
					else
					{
						mysql_query("UPDATE option_values SET `item_desc`='".$postescaped['item_desc'][$id]."',`price`='".$postescaped['price'][$id]."',`price_euro`='".$postescaped['price_euro'][$id]."',`variant_id`='$variant_id',`vsort`='".$postescaped['vsort'][$id]."' WHERE `optval_id`='".$id."'");
					}
				}
			}
			if(!isset($_SESSION['error']))
			{
				header("Location: $mainbase/admin.php?p=product_options&act=edit&opt_id=".$_GET['opt_id']);
			}
		}
	}
	/*.......................PRODUCT OPTIONS: DELETE WHOLE PRODUCT OPTION AND VALUES..................*/
	else if($action=="delete")
	{
		$q=mysql_query("SELECT `img_filename` FROM option_values WHERE `opt_id`='$getescaped[opt_id]'");
		while($r=mysql_fetch_row($q))
		{
			foreach($images_arr['product_options']['images'] as $dir => $size)
			{
				$subdir=($dir!="main")?"/".$dir:"";
				delete_img($images_arr['product_options']['path'].$subdir."/".$r['img_filename']);
			}
		}
		mysql_query("DELETE FROM product_options WHERE `opt_id`='$getescaped[opt_id]'");
		mysql_query("DELETE FROM option_values WHERE `opt_id`='$getescaped[opt_id]'");
		header("Location: $mainbase/admin.php?p=product_options");
	}
}

/*...............................................................
........................... FUNCTIONS ...........................
................................................................*/
$emailereg = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$";
function validemail($emailin,$field)
{	
	global $higherr, $emailereg;
	$returntxt="";
	if(!eregi($emailereg, $emailin)){$returntxt.="Please enter a valid email address (eg: user@host.com).<br />";array_push($higherr,$field);}
	return $returntxt;
}
function fileupload($imgsize,$filepath,$files_tmpname,$files_name,$allowed,$newname,$required,$oldname,$imgname,$loopid)
{
	global $higherr,$images_arr,$thepage;
	$error="";
	$dimensions=explode("x",$imgsize);
	$wid=$dimensions[0];
	$hei=$dimensions[1];
	$size = number_format(filesize($files_tmpname) / 1024,2);
	$filename = basename($files_name);
	$targetpath=$filepath."/";
	$extension = strtolower(getExtension($filename));
	list($actualwidth, $actualheight) = getimagesize($files_tmpname);
	$max_file_size=300;
	
	/*CHECK FILENAME EXITST*/
	if (strlen($files_name) < 1){
		$error="Please choose a file to upload for ".$files_tmpname.".";
	}/*CHECK FILENAME EXITST*/
	/*else if ($actualwidth>$wid||$actualheight>$hei){
		$error=ucwords($imgname)." file dimensions exceed limits (should be W:".$wid."px, H:".$hei."px). Please resize and try again.";
	}*/
	/*FILE EXISTS - CONTINUE*/
	else
	{
		if (!in_array(strtolower($extension),$allowed)){$error="File extension of ".$imgname." not allowed.";}
		if ($size > $max_file_size){$error=ucwords($imgname)." file too big.";}
		
		
		if(strlen($error)<1)
		{
			$target_path1 = $filepath."/" . $newname;
			if($files_tmpname)
			{
				//chmod($targetpath, 0777); 
				if(@file_exists($filepath."/" . $oldname)&&$oldname!=""){@unlink($filepath."/" . $oldname);}
				
				if(move_uploaded_file($files_tmpname, $target_path1)) 
				{ 
					/*RESIZE IMAGE*/
					//$error=resizeimg($target_path1,$size,$extension,$imgsize);
					
					/*ADDING PRODUCT OPTIONS - ADD SMALL IMAGE*/
					if($thepage=="product_options")
					{
						copy($target_path1,$filepath."/small/" . $newname);
						resizeimg($filepath."/small/".$newname,$size,$extension,$images_arr['product_options']['images']['small']);
					}
					//chmod($targetpath, 0755); 
				}
				else 
				{ 
					$error="There was an error uploading ".$imgname; 
				}	
			}
		}
	}
	if($required==1&&strlen($error)>0)
	{
		array_push($higherr,"uploadedfile_".$loopid);
	}
	if($required==1){	return $error; }else{ return "";}
}

function resizeimg($file_tmp,$file_size,$file_ext,$imgsize)
{
	$err="";
	$spo=explode("x",$imgsize);
	$maxwidth=$spo[0];
	$maxheight=$spo[1];
	$file_ext=strtolower($file_ext);
	
	if($file_size)
	{
		if($file_ext=="jpeg"||$file_ext=="jpg"){$new_img = imagecreatefromjpeg($file_tmp);}
		elseif($file_ext=="png"){$new_img = imagecreatefrompng($file_tmp);}
		elseif($file_ext=="gif"){$new_img = imagecreatefromgif($file_tmp);}
		
		/*LIST THE WIDTH AND HEIGHT AND KEEP THE HEIGHT RATIO*/
		list($width, $height) = getimagesize($file_tmp);
		
		/*CALCULATE THE IMAGE RATIO*/
		if($width>$height)
		{
			$imgratio=$width/$height;
			$newwidth = ($imgratio>1)?$maxwidth:$maxwidth*$imgratio;
			$newheight = ($imgratio>1)?$maxwidth/$imgratio:$maxwidth;
		}
		else
		{
			$imgratio=$height/$width;
			$newwidth = ($imgratio>1)?$maxwidth:$maxwidth/$imgratio;
			$newheight = ($imgratio>1)?$maxwidth*$imgratio:$maxwidth;
		}
		
		
		/*SIZE DOWN AGAIN TO KEEP WITHIN HEIGHT CONTRAINT*/
		if($newheight>$maxheight)
		{
			if($newwidth>$newheight)
			{
				$imgratio=$newwidth/$newheight;
				$newheight = ($imgratio>1)?$maxheight:$maxheight/$imgratio;
				$newwidth = ($imgratio>1)?$maxheight*$imgratio:$maxheight;
			}
			else
			{
				$imgratio=$newheight/$newwidth;
				$newheight = ($imgratio>1)?$maxheight:$maxheight*$imgratio;
				$newwidth = ($imgratio>1)?$maxheight/$imgratio:$maxheight;
			}
		}
		$newheight=round($newheight);
		$newwidth=round($newwidth);
		
		/*CHECK FUNCTION FOR RESIZE IMAGE.*/
		if (function_exists(imagecreatetruecolor)){
			$resized_img = imagecreatetruecolor($newwidth,$newheight);
		}
		else
		{
			$err="Could not resize image, do you have GD library ver 2+?";
		}
		
		/*DO THE RESIZE*/
		imagecopyresized($resized_img, $new_img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		/*SAVE THE IMAGE*/
		ImageJpeg ($resized_img,$file_tmp);
		ImageDestroy ($resized_img);
		ImageDestroy ($new_img);
	}
}
function getExtension($str) 
{         
	return pathinfo($str, PATHINFO_EXTENSION);
}
function emptyfieldscheck($postdata,$required)
{
	global $higherr;
	$returntxt="";
	foreach($postdata as $field => $value)
	{
		if(array_key_exists($field,$required))
		{
			if(is_array($value))
			{
				$x=1;
				foreach($value as $id => $arrval)
				{
					/*VALUE EMPTY? - ALERT UNLESS DELTING THIS ITEM*/
					if(strlen($arrval)<1&&$postdata['delete'][$id]!=1)
					{
						$returntxt.=$required[$field]." $x<br />";array_push($higherr,$field."_".$id);
					}
					$x++;
				}
			}
			else
			{
				if(strlen($value)<1){$returntxt.=$required[$field]."<br />";array_push($higherr,$field);}
			}
		}
	}
	return $returntxt;
}
function getletter($num)
{
	global $alpha;
	return substr($alpha,$num,1);
}
function delete_img($img)
{
	if(@file_exists($img)){if(!@unlink($img)){$_SESSION['error']="Could not delete the image (".$img.").";}}else{$_SESSION['error']="Image not found (".$img.").";}
}
function xlsBOF() {
	echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
	return;
}
function xlsEOF() {
	echo pack("ss", 0x0A, 0x00);
	return;
}
function xlsWriteNumber($Row, $Col, $Value) {
	echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
	echo pack("d", $Value);
	return;
}
function xlsWriteLabel($Row, $Col, $Value) {
	$L = strlen($Value);
	echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
	echo $Value;
	return;
} 
function xlsWrite($Row, $Col, $Value) {
	if(is_numeric($Value)) {
		xlsWriteNumber($Row, $Col, $Value);
	}else{
		xlsWriteLabel($Row, $Col, $Value);
	}
}
function genxml($querystring,$mailingfilename,$labels,$numtotext){
	$query = mysql_query($querystring);
	// Send Header
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");;
	header("Content-Disposition: attachment;filename=$mailingfilename"); // แล้วนี่ก็ชื่อไฟล์
	header("Content-Transfer-Encoding: binary ");

	// XLS Data Cell

	xlsBOF();
	foreach($labels as $col => $label)
	{
		xlsWrite(0,$col,ucwords($label));
	}
	
	$xlsRow = 2;
	while($result=mysql_fetch_assoc($query))
	{
		++$i;
		$col=0;
		foreach($result as $id => $field)
		{
			$value=is_numeric($field)&&array_key_exists($id,$numtotext)?$numtotext[$id][$field]:$field;
			xlsWrite($xlsRow,$col,$value);
			$col++;
		}
		$xlsRow++;
	}
	xlsEOF();
	exit();
}
?>