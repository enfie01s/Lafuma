<? 
function bulkdepsatchupdate()
{
	// sort databases
	$hosta = 'localhost';
	$usera = 'gmk';
	$passa = 'cwuio745bjd';
	$namea = 'gmk_global';
	$dba = mysql_connect($hosta,$usera,$passa,true);
	mysql_select_db($namea,$dba);
	$host = 'localhost';
	$user = 'lafuma';
	$pass = 'Klsjfsd873sdd';
	$name = 'lafuma_main';
	$db = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$db);	
	// end 
$sql=mysql_query("SELECT * FROM orders WHERE order_status<>'Shipped'",$db) or die("Error $sql<br />".mysql_error());;
while($row=mysql_fetch_array($sql))
{	
	$sqlk=mysql_query("SELECT * FROM despatch_details WHERE order_ref LIKE '%".$row['invoice']."%' AND customer_no='ZZWEB'",$dba)  or die("Error $sqlk<br />".mysql_error());
	$opts = mysql_fetch_array($sqlk);
	if ($opts['ship_ref']!==NULL)
	{ $opts['ship_agent']=="PARCELFORC"?$courier="Parcelforce":"";
	mysql_query("UPDATE `orders` SET `pay_status`='1',`iorder_status`='1',`order_status`='Shipped' WHERE `order_id`='".$row['order_id']."'");
				$datebits=explode("/",$opts['ship_date']);
				$day=($datebits[0]<10&&strlen($datebits[0])>1)?substr($datebits[0],1,1):$datebits[0];
				$month=($datebits[1]<10&&strlen($datebits[1])>1)?substr($datebits[1],1,1):$datebits[1];
				$year=(strlen($datebits[2])==2)?"20".$datebits[2]:$datebits[2];
				$shipdate=date("U",mktime(0,0,0,$month,$day,$year));
	
	$query1="INSERT INTO `ordership`(`order_id`,`shipper`,`tracking`,`date_shipped`)VALUES('".$row['order_id']."','".$courier."','".$opts['ship_ref']."','".$shipdate."')";
	mysql_query($query1);
	
	$sqlka=mysql_query("SELECT * FROM customers WHERE cust_id='".$row['cust_id']."'",$db)  or die("Error $sqlka<br />".mysql_error());
	$cust = mysql_fetch_array($sqlka);
	$row['cust_id']=="0"?$cust['mailing']="0":"";
	
	include "content/vars.php";
	
	$subject="Your order from Lafuma UK has shipped";
	$headers = "From: Lafuma UK <sales@llc-ltd.co.uk>\r\n";
	$headers .= "Reply-To: sales@llc-ltd.co.uk\r\n";
	$headers .= "Return-Path: sales@llc-ltd.co.uk\r\n";
	$headers .= "MIME-Version: 1.0\r\nContent-Type: $conttype; charset=UTF-8\r\n";
	$message="Dear ".$row['firstname']." ".$row['lastname'].",".$br[$cust['mailing']].
	$br[$cust['mailing']].
	"Invoice Number: ".$row['invoice'].$br[$cust['mailing']].
	$br[$cust['mailing']].
	"We are pleased to inform you that your order has been sent.".$br[$cust['mailing']].
	"================================================".$br[$cust['mailing']].
	"Method: $courier ".$br[$cust['mailing']].
	"Tracking Number: ".$opts['ship_ref'].$br[$cust['mailing']];
	
	$message.=$br[$cust['mailing']].
	"You can check the status of your order by going to the url below ".$br[$cust['mailing']].
	"http://www.parcelforce.com/track-trace?trackNumber=".$opts['ship_ref'].$br[$cust['mailing']];
	
	$message.="=================================================".$br[$cust['mailing']].
	"Thank you for your business. ".$br[$cust['mailing']].
	"Lafuma UK".$br[$cust['mailing']].
	"Bear House,".$br[$cust['mailing']].
	"Concorde Way,".$br[$cust['mailing']].
	"Fareham,".$br[$cust['mailing']].
	"Hampshire,".$br[$cust['mailing']].
	"PO15 5RL".$br[$cust['mailing']].
	"United Kingdom".$br[$cust['mailing']].
	"Email: sales@llc-ltd.co.uk".$br[$cust['mailing']].
	"Tel: 01489 557600".$br[$cust['mailing']].
	$br[$cust['mailing']].
	"vat. Registration No: GB795030523".$br[$cust['mailing']].
	"Company Registration No.: 4379849".$br[$cust['mailing']].
	"=================================================".$br[$cust['mailing']];
	$to=$row['email'];
	@mail($to,$subject,$message,$headers,"-f"."sales@llc-ltd.co.uk");
	
	}
	
}



} 

$_GET['bulk']=="set"?bulkdepsatchupdate():"";


$act=isset($_GET['act'])?$getescaped['act']:"";
$os=isset($_GET['sstatus'])?$getescaped['sstatus']:"New";
$sby=isset($_GET['ssortby'])?$getescaped['ssortby']:"invoice";
$order_status=isset($_GET['sstatus'])&&$_GET['sstatus']=="all"?"":"WHERE order_status='$os'";
$sortby=!isset($_GET['ssortby'])?"orders.invoice":"orders.".$getescaped['ssortby'];
$curr=isset($_POST['curr'])?$_POST['curr']:(isset($_GET['curr'])?$_GET['curr']:"");
$pcurr=strlen($curr)>0?"currency='".$curr."'":"";
$sort_direction=!isset($_GET['ssortdir'])?"DESC":$getescaped['ssortdir'];
$invoice=isset($_GET['invoice'])?$getescaped['invoice']:"";
$rangefrom=isset($_GET['from'])?(($order_status!="")?"AND":"WHERE")." date_ordered >='".$getescaped['from']."'":"";
$rangeto=isset($_GET['to'])?(($rangefrom!=""||$order_status!="")?"AND":"WHERE")." date_ordered <='".$getescaped['to']."'":"";
$istatus=isset($_GET['istatus'])?(($rangefrom!=""||$rangeto!=""||$order_status!="")?"AND":"WHERE")." iorder_status=".$getescaped['istatus']:"";
$tcurr=isset($pcurr)&&strlen($pcurr)>0?(($rangefrom!=""||$rangeto!=""||$order_status!=""||$istatus!="")?"AND ":"WHERE ").$pcurr:$pcurr;

?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if($act!=""){?><a href="<?=$self?>"><? }?>Invoices<? if($act!=""){?></a><? }?><?=(($invoice!=""&&$act=="view")?" &#187; Invoice: ".$_GET['invoice']:(($act=="update"||$act=="updatemany")?" &#187; Tracking Information":""))?></div>
<div id="main">
	<h2 id="pagetitle">Invoices</h2>
	<div id="pagecontent">
	<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
	<!-- CONTENT -->
	<?
	switch($act)
	{
		case "view":
			$invq=mysql_query("SELECT o.`cust_id`,o.`order_id`,`pay_status`,o.`Status`,`pay_method`,`iorder_status`,`order_status`,o.`firstname`,o.`lastname`,o.`address1`,o.`address2`,o.`city`,o.`state`,o.`postcode`,o.`country`,o.`phone`,`sameasbilling`,`alt_name`,`alt_address1`,`alt_address2`,`alt_city`,`alt_state`,`alt_postcode`,`alt_country`,`alt_phone`,`ship_description`,`shipper`,`tracking`,FROM_UNIXTIME(`date_ordered`,'%d/%m/%Y') as date_ordered,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y') as date_shipped,`currency`,`exchrate`,o.`CardType`,o.`Last4Digits` FROM orders AS o LEFT JOIN customers AS c ON c.`cust_id`=o.`cust_id` AND o.`cust_id`!='0' LEFT JOIN ordership AS os on o.`order_id`=os.`order_id` WHERE `invoice`='$invoice'");
			$inv=mysql_fetch_assoc($invq);
			
			?>
			<table cellpadding="0" cellspacing="0" class="details" width="100%">
			<tr>
				<td colspan="2" class="head"><div class="titles"><?=$titleback." ".helplink($page)?> Invoice Details</div><div class="links"><a href="#" onclick="window.print();return false">Print Invoice</a></div></td>
			</tr>
			<form action="<?=$self?>&amp;act=update&amp;invoice=<?=$invoice?>" method="post">
			<input type="hidden" name="order_id" value="<?=$inv['order_id']?>" />
			<tr>
				<td width="50%" class="hidefromprint"><input type="checkbox" name="opaid" id="opaid" value="1" <? if($inv['pay_status']==1){?>checked="checked"<? }?> /> <label for="opaid">Mark Order as paid</label><br /><input type="checkbox" name="ocomp" id="ocomp" value="1" <? if($inv['iorder_status']==1){?>checked="checked"<? }?> /> <label for="ocomp">Mark order as complete</label></td>
				<td width="50%" class="hidefromprint">
				
				<select name="newstatus" class="formfieldm">
				<? foreach($orderstatuses as $dbstatus => $displaystatus){if($dbstatus!="Pending"){?>
					<option value="<?=$dbstatus?>" <? if($inv['order_status']==$dbstatus){?>selected="selected"<? }?>><?=$displaystatus?></option>
				<? }}?>
				</select> <input type="submit" name="updateinv" value="Update Invoice" class="formbutton" /></td>
			</tr>
			</form>
			<tr>
				<td class="subhead" colspan="2">Payment Status Message</td>
			</tr>
			<tr>
				<td colspan="2"><?=$inv['Status']?></td>
			</tr>
			<tr>
				<td class="subhead"><strong>Invoice:</strong> <?=$invoice?></td>
				<td class="subhead"><strong>Order date:</strong> <?=$inv['date_ordered']?></td>
			</tr>
			<tr>
				<td colspan="2">
				<div style="width:33%;float:left;">
					<strong>Bill to:</strong><br />
					<?=($inv['cust_id']!=0?"<a href='$mainbase/admin.php?p=customers&amp;act=view&amp;cust_id=$inv[cust_id]'>":"").ucwords($inv['firstname']." ".$inv['lastname']).($inv['cust_id']!=0?"</a>":"")?><br />
					<?=$inv['address1']?><br />
					<?=((strlen($inv['address2'])>0)?$inv['address2']."<br />":"")?>
					<?=$inv['city']?><br />
					<?=get_county($inv['state'])?><br />
					<?=$inv['postcode']?><br />
					<?=get_country($inv['country'])?><br />
					<?=$inv['phone']?>
				</div>
				<div style="width:33%;float:left;">
					<strong>Deliver to:</strong><br />
					<? if($inv['sameasbilling']==1){?>
					Same as billing address
					<? }else{?>
					<?=$inv['alt_name']?><br />
					<?=$inv['alt_address1']?><br />
					<?=((strlen($inv['alt_address2'])>0)?$inv['alt_address2']."<br />":"")?>
					<?=$inv['alt_city']?><br />
					<?=get_county($inv['alt_state'])?><br />
					<?=$inv['alt_postcode']?><br />
					<?=get_country($inv['alt_country'])?><br />
					<?=$inv['alt_phone']?>
					<? }?>
				</div>
				<div style="width:33%;float:left;">
					<strong>Payment method (<?=$inv['currency']?>):</strong><br />
					<? if($inv['pay_status']==1){ if($inv['pay_method']=="paypal"){?>Paypal<? }else{?>
				<?=$inv['CardType']=="MC"?"Mastercard":$inv['CardType']?> <?=strlen($inv['Last4Digits'])>0?($inv['CardType']=="AMEX"?"**** ****** *":"**** **** **** ").$inv['Last4Digits']:""?>
				
				<? }}else{?>Unpaid<? }?>
					<br /><br />
					<strong>Postage method:</strong><br />
					<?=$inv['ship_description']?>
					<? if($inv['shipper']){
						$turl1=array_key_exists($inv['shipper'],$postaltracking)&&$inv['tracking']?"<a href='".$postaltracking[$inv['shipper']].$inv['tracking']."' target='_blank'>":"";
						$turl2=array_key_exists($inv['shipper'],$postaltracking)&&$inv['tracking']?"</a>":"";
						?>
						<br /><br />
						<strong>Shipped: <?=$inv['date_shipped']?></strong><br />
						Carrier: <?=$inv['shipper']?><br />
						Tracking: <?=$turl1.(($inv['tracking'])?$inv['tracking']:"Not available").$turl2?>
					<? }?>
				</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="subhead"><strong>Customer comments</strong></td>
			</tr>
			<tr>
				<td colspan="2"><?=((strlen($inv['comments'])>0)?$inv['comments']:"None")?></td>
			</tr>
			</table>
			<br />
			<form action="<?=$self?>&amp;act=view&amp;invoice=<?=$invoice?>" method="post" id="productoptions" name="productoptions">
			<? ordercontents("invoice='$invoice'","100%");?>
			<p class="submit"><input type="submit" name="updateorder" value="Update Order" /></p>
			</form>
			<?
			break;
		default:
			$toupdate=""; 
			$invoices=array();
			if($act=="updatemany"&&in_array("Shipped",$_POST['inv']))
			{
				foreach($_POST['order_id'] as $tinvoice => $oid)
				{
					if($_POST['inv'][$tinvoice]=="Shipped")
					{
						if($toupdate!=""){$toupdate.=",";}
						$toupdate.="'$tinvoice'";
					}
				}
				$changedq=mysql_query("SELECT `invoice` FROM orders WHERE `order_status`!='Shipped' AND `invoice` IN($toupdate)");
				while($changed=mysql_fetch_row($changedq)){$invoices[$changed[0]]=$_POST['order_id'][$changed[0]];}
			}
			/*if(($act=="updatemany"&&in_array("Delete",$_POST['inv']))||($act=="update"&&strtolower($_POST['newstatus'])=="delete"))
			{
				?>
				<table class="details">
				<tr>
					<td class="head">Status update for <?=((isset($_POST['inv']))?"multiple orders":"invoice: ".$invoice)?></td>
				</tr>
				<tr>
					<td style="text-align:center">
					<form action="<?=$self.$invsort?>" method="post" name="deletemany">
					<p>Are you sure you want to delete <?=isset($_POST['inv'])?"these orders":"this order"?>?</p>
					<? 
					if(isset($_POST['inv']))
					{
						foreach($_POST['inv'] as $invnum => $newstat)
						{
							if($newstat=="Delete"){
							?><input type="hidden" name="delinv[]" value="<?=$invnum?>" />
							Invoice: <?=$invnum?><br /><?
							}
						}
					}
					else
					{
						if(strtolower($_POST['newstatus'])=="delete"){
							?><input type="hidden" name="delinv[]" value="<?=$invoice?>" />
							Invoice: <?=$invoice?><br /><?
						}
					}
					?>
					<p><a href="<?=$self.$invsort?>">Cancel</a> | <a href="javascript:document.deletemany.submit();">Delete <?=isset($_POST['inv'])?"orders":"order"?></a></p>
					</form>
					</td>
				</tr>
				</table>
				<?
			}
			else */
			if(($act=="update"&&strtolower($_POST['newstatus'])=="shipped")||($act=="updatemany"&&count($invoices)>0))/* setting shipped */
			{
				if(count($invoices)<2){
					if(count($invoices)<1)
					{
						$invoice=$_GET['invoice'];
						$oid=is_array($_POST['order_id'])?$_POST['order_id'][$invoice]:$_POST['order_id'];
					}
					else
					{
						$invoice=key($invoices);
						$oid=$invoices[$invoice];
					}
					$shipq=mysql_query("SELECT `shipper`,`tracking`,FROM_UNIXTIME(`date_shipped`,'%d/%m/%Y') as date_shipped FROM ordership WHERE `order_id`='$oid'");
					$ship=mysql_fetch_assoc($shipq);
					$arr=isset($_POST['invoice'])?$_POST:(isset($ship)?$ship:"");
					$shipper2=!in_array($ship['shipper'],$postal)&&!isset($_POST['invoice'])?"shipper":"shipper2";
					?>
					<form action="<?=$self.$invsort?>&amp;act=<?=$act?>&amp;invoice=<?=$invoice?>" method="post">
					<input type="hidden" name="newstatus" value="Shipped" />
					<input type="hidden" name="order_id[<?=$invoice?>]" value="<?=$oid?>" />
					<input type="hidden" name="inv[<?=$invoice?>]" value="Shipped" />
					<input type="hidden" name="invoice[<?=$oid?>]" value="<?=$invoice?>" />
					<table class="details">
					<tr> 
						<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> Tracking Information for invoice: <?=$invoice?></td>
					</tr>
					<tr>
						<td colspan="2">
							If you wish to provide your customer with a tracking number from one of the major companies, select the sender and then place the tracking number in the space provided.
							If you select 'notify customer' a confirmation email will be sent to the customer with their tracking number.
							Otherwise, <a href="<?=$self?>&amp;act=view&amp;invoice=<?=$invoice?>">return</a> to the invoice.
						</td>
					</tr>
					<tr>
						<td class="first">Method</td>
						<td>
							<select name="shipper[<?=$oid?>]" <?=highlighterrors($higherr,"shipper_".$oid)?>>
								<option value="" <?=is_selected("shipper",$oid,"",$arr,"select")?>> --- Select method --- </option>
								<? foreach($postal as $carrier){?>
								<option value="<?=$carrier?>" <?=is_selected("shipper",$oid,$carrier,$arr,"select")?>><?=$carrier?></option>
								<? }?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Other</td>
						<td><input name="shipper2[<?=$oid?>]" size="18" value="<?=posted_value($shipper2,$oid,"",$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"shipper2_".$oid)?> /></td>
					</tr>
					<tr>
			
						<td>Date Posted</td>
						<td><input name="date_shipped[<?=$oid?>]" size="18" value="<?=posted_value("date_shipped",$oid,date("d/m/Y"),$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"ship_date_".$oid)?> /> <dfn>(dd/mm/yyyy)</dfn></td>
					</tr>
					<tr>
						<td>Tracking Number</td>
						<td><input name="tracking[<?=$oid?>]" value="<?=posted_value("tracking",$oid,"",$arr)?>" size="18" maxlength="20" type="text" /></td>
					</tr>
					<tr>
			
						<td>Notification</td>
						<td><input class="checkbox" name="notify[<?=$oid?>]" value="1" type="checkbox" <?=is_selected("notify",$oid,"1",$_POST,"check")?> />Notify customer by email?</td>
					</tr>
					</table>
					<p class="submit"><input value="Process" type="submit"></p>
					</form>
					<?
				}
				else
				{
					?>
					<form action="<?=$self.$invsort?>&amp;act=updatemany&amp;invoice=<?=$invoice?>" method="post">
					<table class="details">
					<tr> 
						<td class="head" colspan="6"><?=$titleback." ".helplink($page)?> Tracking Information for multiple invoices</td>
					</tr>
					<tr>
						<td colspan="6">
							If you wish to provide your customers with a tracking number from one of the major companies, select the sender and then place the tracking number in the space provided.
							If you select 'notify customer' a confirmation email will be sent to the customer with their tracking number.
						</td>
					</tr>
					<tr>
						<td class="subhead" style="width:10%;text-align:center">Invoice</td>
						<td class="subhead" width="23%">Method</td>
						<td class="subhead" style="width:15%">Other</td>
						<td class="subhead" width="22%">Date Posted <dfn>(dd/mm/yyyy)</dfn></td>
						<td class="subhead" style="width:15%">Tracking Number</td>
						<td class="subhead" style="width:15%">Notify customer?</td>
					</tr>
					<? foreach($invoices as $invoice => $oid){
						$shipq=mysql_query("SELECT shipper,tracking,FROM_UNIXTIME(date_shipped,'%d/%m/%Y') as date_shipped FROM ordership WHERE order_id='$oid'");
						$ship=mysql_fetch_assoc($shipq);
						$arr=isset($_POST['invoice'])?$_POST:(isset($ship)?$ship:"");
						//shipper2 posted value ok
						//shipper2 shows shipper from sql where not in postal arr 
						$shipper2=!in_array($ship['shipper'],$postal)&&!isset($_POST['invoice'])?"shipper":"shipper2";
						?>
						<input type="hidden" name="order_id[<?=$invoice?>]" value="<?=$oid?>" />
						<input type="hidden" name="inv[<?=$invoice?>]" value="Shipped" />
						<input type="hidden" name="invoice[<?=$oid?>]" value="<?=$invoice?>" />
						<tr>
						<td style="text-align:center"><?=$invoice?></td>
						<td>
							<select name="shipper[<?=$oid?>]" <?=highlighterrors($higherr,"shipper_".$oid)?>>
								<option value="" <?=is_selected("shipper",$oid,"",$arr,"select")?>>- Select method -</option>
								<? foreach($postal as $carrier){?>
								<option value="<?=$carrier?>" <?=is_selected("shipper",$oid,$carrier,$arr,"select")?>><?=$carrier?></option>
								<? }?>
							</select>
						</td>
						<td><input name="shipper2[<?=$oid?>]" size="18" value="<?=posted_value($shipper2,$oid,"",$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"shipper2_".$oid)?> class="formfieldm" /></td>
						<td><input name="date_shipped[<?=$oid?>]" size="18" value="<?=posted_value("date_shipped",$oid,date("d/m/Y"),$arr)?>" maxlength="20" type="text"  <?=highlighterrors($higherr,"ship_date_".$oid)?> class="formfieldm" /></td>
						<td><input name="tracking[<?=$oid?>]" value="<?=posted_value("tracking",$oid,"",$arr)?>" size="18" maxlength="20" type="text" class="formfieldm" /></td>
						<td style="text-align:center"><input class="checkbox" name="notify[<?=$oid?>]" value="1" type="checkbox" <?=is_selected("notify",$oid,"1",$_POST,"check")?> /></td>
						</tr>
					<? }?>
					</table>
					<p class="submit"><input value="Process" type="submit"></p>
					</form>
					<?
				}
			}
			else
			{
				$pgnums=pagenums("SELECT order_id,invoice,date_ordered,pay_method,orders.firstname,orders.lastname,customers.cust_id as custid,order_status,pay_status,iorder_status FROM orders LEFT JOIN customers ON orders.cust_id=customers.cust_id $order_status $rangefrom $rangeto $istatus $tcurr ORDER BY $sortby $sort_direction","admin.php?p=invoices".$invsort,30,5);
				$query=$pgnums[0];
				$fromtxt=isset($_GET['from'])?relative_date("F j\, Y",$_GET['from']):"";
				$totxt=isset($_GET['to'])!=""?relative_date("F j\, Y",$_GET['to']):"Now";
				?>
				<table class="details">
				<tr>
					<td class="head"><?=helplink($page)?><?=strlen($curr)>0?" ".$curr:""?> Invoices <?=ucwords($fromtxt)?><?=(($fromtxt!="today")?" to ".ucwords($totxt):"")?></td>
				</tr>
				<? if(strlen($pgnums[1])>0){?>
				<tr>
					<td class="infohead"><?=$pgnums[1]?></td>
				</tr>
				<? }?>
				<tr>
					<td><form action="<?=$self?>" method="get"><input type="hidden" name="p" value="invoices" /><input type="hidden" name="act" value="view" />View invoice number <input type="text" name="invoice" value="" class="formfields" /><input type="submit" name="previewinv" value="Preview" class="formbutton" /></form></td>
				</tr>
				<tr>
					<td>
                    <div style="float:right;padding:3px 5px 0px 5px; height:20px;  background-color:#CD071E; color:#FFF;"><a style="color:#FFF;" href="admin.php?p=invoices&amp;bulk=set">Bulk Dispatch</a></div>
                  
                    
					<form action="<?=$self?>" method="get">
					<input type="hidden" name="p" value="invoices" />
					Sort invoices by 
					<select name="ssortby" class="formfieldm">
					<option value="invoice" <? if($sby=="invoice"){?>selected="selected"<? }?>>Invoice</option>
					<option value="date_ordered" <? if($sby=="date_ordered"){?>selected="selected"<? }?>>Order Date</option>
					<option value="pay_method" <? if($sby=="pay_method"){?>selected="selected"<? }?>>Payment Method</option>
					<option value="pay_status" <? if($sby=="pay_status"){?>selected="selected"<? }?>>Payment Status</option>
					<option value="lastname" <? if($sby=="lastname"){?>selected="selected"<? }?>>Last name</option>
					<option value="city" <? if($sby=="city"){?>selected="selected"<? }?>>City</option>
					<option value="state" <? if($sby=="state"){?>selected="selected"<? }?>>County/State</option>
					<option value="country" <? if($sby=="country"){?>selected="selected"<? }?>>Country</option>
					<option value="alt_city" <? if($sby=="alt_city"){?>selected="selected"<? }?>>Postage City</option>
					<option value="alt_state" <? if($sby=="alt_state"){?>selected="selected"<? }?>>Postage County</option>
					<option value="alt_country" <? if($sby=="alt_country"){?>selected="selected"<? }?>>Postage Country</option>
					</select> 
					Status 
					<select name="sstatus" class="formfieldm">
					<? foreach($orderstatuses as $dbstatus => $displaystatus){?>
						<option value="<?=$dbstatus?>" <? if($os==$dbstatus){?>selected="selected"<? }?>><?=$displaystatus?></option>
					<? }?>
					<option value="all" <? if($os=="all"){?>selected="selected"<? }?>>All Orders</option>
					</select> 
					<input type="radio" name="ssortdir" value="DESC" id="desc" <? if($sort_direction=="DESC"){?>checked="checked"<? }?> /><label for="desc"> desc.</label> <input type="radio" name="ssortdir" value="ASC" id="asc" <? if($sort_direction=="ASC"){?>checked="checked"<? }?> /><label for="asc"> asc.</label> 
					<input type="submit" value="Sort" class="formbutton" />
					</form>
					
                    
                    </td>
				</tr>
				</table>
				<form action="<?=$self.$invsort?>&amp;act=updatemany" method="post">
				<table class="details">
				<tr>
					<td class="subhead" style="width:7%;text-align:center">Invoice</td>
					<td class="subhead" style="width:15%;text-align:center">Order Date</td>
					<td class="subhead" style="width:20%">Customer</td>
					<td class="subhead" style="width:18%;text-align:center">Method</td>
					<td class="subhead" style="width:10%;text-align:center">Pay Status</td>
					<td class="subhead" style="width:15%;text-align:center">Order State</td>
					<td class="subhead" style="width:10%;text-align:center">Status</td>
					<td class="subhead" style="width:5%;text-align:center">View</td>
				</tr>
				<? 
				$invQ=mysql_query($query)or die("Error: $query<br />".mysql_error());
				$invNum=mysql_num_rows($invQ);
				while($inv=mysql_fetch_assoc($invQ))
				{
					$row=((!isset($row)||$row=="1")?"0":"1");
					?>
					<tr class="row<?=$row?>">
						<td style="text-align:center"><?=$inv['invoice']?></td>
						<td style="text-align:center"><?=date("F j\, Y",$inv['date_ordered'])?></td>
						<td><? if(strlen($inv['custid'])>0){?><a href="<?=$mainbase?>/admin.php?p=customers&amp;act=view&amp;cust_id=<?=$inv['custid']?>"><? }?><?=ucwords($inv['firstname']." ".$inv['lastname'])?><? if(strlen($inv['custid'])>0){?></a><? }?></td>
						<td style="text-align:center"><?=isset($cardtypes[strtolower($inv['pay_method'])])?$cardtypes[$inv['pay_method']]:$inv['pay_method']?></td>
						<td style="text-align:center"><?=(($inv['pay_status']==1)?"Paid":"Unpaid")?></td>
						<td style="text-align:center">
						<input type="hidden" name="order_id[<?=$inv['invoice']?>]" value="<?=$inv['order_id']?>" />
						<select name="inv[<?=$inv['invoice']?>]" class="formfieldm">
						<? foreach($orderstatuses as $dbstatus => $displaystatus){?>
						<option value="<?=$dbstatus?>" <? if($inv['order_status']==$dbstatus){?>selected="selected"<? }?>><?=$displaystatus?></option>
						<? }?>
						</select>
						</td>
						<td style="text-align:center"><?=(($inv['iorder_status']==1)?"Complete":"Incomplete")?></td>
						<td style="text-align:center"><a href="<?=$self?>&amp;act=view&amp;invoice=<?=$inv['invoice']?>">View</a></td>
					</tr>
					<? 
				}
				if($invNum==0){?>
				<tr><td colspan="8" style="text-align:center">No invoices found for this time period</td></tr>
				<? }?>
				<? if(strlen($pgnums[1])>0){?>
				<tr>
					<td class="infohead" colspan="8"><?=$pgnums[1]?></td>
				</tr>
				<? }?>
				<tr>
					<td class="subhead" colspan="8" style="text-align:right"><input type="submit" value="Update Orders" class="formbutton" /></td>
				</tr>
				</table>
				</form>
			<?
		}
		break;
	}?>
		<!-- /CONTENT -->
	</div>
</div>
		