<?
$reporting=isset($_GET['report'])?$_GET['report']:"";
$what=isset($_POST['what'])?$_POST['what']:(isset($_GET['what'])?$_GET['what']:"");
$from_site=isset($_POST['from_site'])?$_POST['from_site']:(isset($_GET['from_site'])?$_GET['from_site']:"");
$curr=isset($_POST['curr'])?$_POST['curr']:(isset($_GET['curr'])?$_GET['curr']:"GBP");
$varfrom=isset($_POST['from'])?$_POST['from']:(isset($_GET['from'])?$_GET['from']:"");
$varto=isset($_POST['to'])?$_POST['to']:(isset($_GET['to'])?$_GET['to']:"");
$sstatus=isset($_POST['sstatus'])?$_POST['sstatus']:(isset($_GET['sstatus'])?$_GET['sstatus']:"");
if(strlen($varfrom)>0&&isset($_GET['showgen'])){
	$error="";
	if(strlen($varfrom)<1){$error.="Order from date is empty<br />";$higherr[]="from";}
	if(strlen($varto)<1){$error.="Order to date is empty<br />";$higherr[]="to";}
	if(strlen($error)<1){
		$fromexp=explode("-",$varfrom);
		$from=mktime(0,0,0,prezero($fromexp[1]),prezero($fromexp[2]),$fromexp[0]);
		
		$toexp=explode("-",$varto);
		$to=mktime(23,59,59,prezero($toexp[1]),prezero($toexp[2]),$toexp[0]);
		
		if($from>=$to){$error.="Order from date must be less than order to date<br />";$higherr[]="from";$higherr[]="to";}
	}
	if(strlen($error)>1){$_SESSION['error']=$error;}
}
?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if(isset($_GET['report'])){?><a href="<?=$self?>"><? }?>Reports<? if(isset($_GET['report'])){?></a><? }?><?=((isset($_GET['report']))?" &#187; ".ucwords($_GET['report'])." Report":"")?></div>
<div id="main">
	<h2 id="pagetitle">Reports</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<?
		switch($reporting)
		{
			case "stock":
			case "products":
				mysql_query("CREATE OR REPLACE VIEW pf AS SELECT p.prod_id,p.title,f.fusionId,f.iOwner_FK,p.sku,f.iState FROM products as p LEFT JOIN fusion as f ON p.prod_id=f.iSubId_FK AND vType='product' GROUP BY p.prod_id");
				if($reporting=="stock")
				{
					$sqlQ="
					SELECT title,pf.prod_id,item_desc,description,n.nav_qty as total,pf.fusionId as fid,pf.iState as onoff,n.nav_sku as sku FROM ((pf LEFT JOIN fusion_options as fo ON fo.prod_id=pf.prod_id) LEFT JOIN (option_values as ov JOIN product_options as po ON po.opt_id=ov.opt_id) ON ov.opt_id=fo.opt_id) LEFT JOIN nav_stock as n ON n.nav_skuvar=ov.variant_id WHERE pf.fusionId is not null ORDER BY pf.prod_id";
				}
				else if($reporting=="products")
				{
					$sqlQ="
					SELECT pf.prod_id as prod_id,pf.sku as sku,pf.title as title,pf.fusionId as fid,pf.iOwner_FK as owner,ov.variant_id,SUM(n.nav_qty) as total 
					FROM (pf LEFT JOIN (fusion_options as fo JOIN option_values as ov ON ov.opt_id=fo.opt_id) ON fo.prod_id=pf.prod_id) LEFT JOIN nav_stock as n ON n.nav_skuvar=ov.variant_id 
					GROUP BY pf.prod_id ORDER BY pf.title
					";
				}
				$pgnumsarray=pagenums($sqlQ,$mainbase."/admin.php?p=reports&amp;report=".$reporting,30,5);//div float left and clear
				
				$sqlQ=$pgnumsarray[0];
				$reportq=mysql_query($sqlQ) or die("Query failed: '$sqlQ'<br />".mysql_error());
				$reportnums=mysql_num_rows($reportq);
				?>
				<table class="details">
				<tr>
					<td class="head" colspan="<?=(($reporting=="stock")?"7":"5")?>"><?=$titleback." ".helplink($page)?> <?=ucwords($reporting)?> Report</td>
				</tr>
				<tr>
					<td class="infohead" colspan="<?=(($reporting=="stock")?"7":"5")?>"><?=$pgnumsarray[1]?></td>
				</tr>
				<? if($reporting=="products"){?>
				<tr>
					<td colspan="5">Search Stuff</td>
				</tr>
				<? }?>
				<tr>
					<td class="subhead"><?=(($reporting=="stock")?"On/Off":"ID")?></td>
					<td class="subhead">Product name</td>
					<?=(($reporting=="stock")?"<td class='subhead'>Type</td><td class='subhead'>Description</td>":"")?>
					<td class="subhead">Stock</td>
					<td class="subhead">Edit</td>
					<td class="subhead">Delete</td>
				</tr>
					
				<?
				while($report=mysql_fetch_assoc($reportq))
				{$row=!isset($row)||$row==1?0:1;
					?>
					<tr class="row<?=$row?>">
						<td><?=(($reporting=="stock")?(($report['onoff']==1)?"<span style='color:green'>On</span>":"<span style='color:red'>Off</span>"):$report['sku'])?></td>
						<td><?=(($report['fid']!=null)?"<a href='$mainbase/admin.php?p=builder&amp;act=view&amp;what=product&amp;id=$report[fid]&amp;pid=$report[prod_id]&amp;name=".urlencode($report['title'])."'>$report[title]</a>":$report['title']." <i>(Orphan product)</i>")?></td>
						<?=(($reporting=="stock")?"<td>$report[description]</td><td>$report[item_desc]</td>":"")?>
						<td><?=(($report['total']>0)?$report['total']:"0")?></td>
						<td style="text-align:center"><a href="<?=$mainbase?>/admin.php?p=builder&amp;act=update&amp;what=product&amp;onitem=product&amp;id=<?=$report['fid']?>&amp;pid=<?=$report['prod_id']?>&amp;report=<?=$reporting?><?=((isset($_GET['page']))?"&amp;rpage=$_GET[page]":"")?>">Edit</a></td>
										<td style="text-align:center"><a href="<?=$mainbase?>/admin.php?p=builder&amp;act=delete&amp;what=product&amp;onitem=product&amp;id=<?=$report['fid']?>&amp;fid=<?=$report['fid']?>&amp;pid=<?=$report['prod_id']?>&amp;name=<?=urlencode($report['title'])?>&amp;report=<?=$reporting?><?=((isset($_GET['page']))?"&amp;rpage=$_GET[page]":"")?>">Delete</a></td>
					</tr>
					<?
				}
				?>
				</table>
				<?
				break;
			case "order":
				if(isset($_GET['showgen']))
				{
					idhighlighterrors($higherr,"from",array("from_Month_ID","from_Day_ID","from_Year_ID"));
					idhighlighterrors($higherr,"to",array("to_Month_ID","to_Day_ID","to_Year_ID"));
					$pbdstyle=$what=="Ordered products by date"||$what=="Products ordered by date"?"style='display:inline'":"style='display:none'";
					$obdstyle=$what=="Ordered products by date"||$what=="Products ordered by date"?"style='display:none'":"style='display:inline'";
					$eurogbpstyle=$what=="Ordered products by date"||$what=="Products ordered by date"?"style='display:inline'":"style='display:none'";
					$eurogbp1style=$what=="Ordered products by date"||$what=="Products ordered by date"?"style='display:none'":"style='display:inline'";
					?>
					<script type="text/javascript">
					function hideshow(hide,show)
					{
						document.getElementById(hide).style.display='none';
						document.getElementById(show).style.display='inline';
					}
					</script>
					
					<form action="<?=$self?>&amp;report=order&amp;showgen=1" method="post">
					<table class="details">
						<tr>
							<td class="head" colspan="2"><div class="titles"><?=$titleback." ".helplink($page)?> Order Report</div><div class="links"><a href="<?=$self?>">Return to reports</a></div></td>
						</tr>
						<tr>
							<td class="first">Order Status:</td>
							<td>
								<div id="pbd" <?=$pbdstyle?>><i>Order status not needed for this report</i></div>
								<div id="obd" <?=$obdstyle?>>
								<select name="sstatus" class="formfieldm">
								<option value="all" <? if(strlen($sstatus)>0&&$sstatus=="all"){?>selected="selected"<? }?>>All</option>
								<? foreach($orderstatuses as $dbstatus => $displaystatus){if($dbstatus!="Pending"){?>
									<option value="<?=$dbstatus?>" <? if($sstatus==$dbstatus){?>selected="selected"<? }?>><?=$displaystatus?></option>
								<? }}?>
								</select> 
								</div>
							</td>
						</tr>
						<tr>
							<td>Order From <dfn>(yyyy-mm-dd)</dfn></td>
							<td><input type="date" name="from" value="<?=strlen($varfrom)>0?date("Y-m-d",$from):date("Y-m-d")?>" <?=highlighterrors($higherr,"from")?> /></td>
						</tr>
						<tr>
							<td>Order To <dfn>(yyyy-mm-dd)</dfn></td>
							<td><input type="date" name="to" value="<?=strlen($varto)>0?date("Y-m-d",$to):date("Y-m-d")?>" max="<?=date("Y-m-d")?>" <?=highlighterrors($higherr,"to")?> /></td>
						</tr>
						<tr>
							<td></td>
							<td>
							<input type="radio" name="what" value="Orders by date" id="1" onclick="javascript:hideshow('pbd','obd');hideshow('eurogbp','eurogbp1')" <?=strlen($what)>0?is_selected("what","","Orders by date",(isset($_POST['what'])?$_POST:$_GET),"check"):"checked='checked'"?> /><label for="1">Orders by date</label><br />
							<input type="radio" name="what" value="Ordered products by date" id="2" onclick="javascript:hideshow('obd','pbd');hideshow('eurogbp1','eurogbp')" <?=strlen($what)>0?is_selected("what","","Ordered products by date",(isset($_POST['what'])?$_POST:$_GET),"check"):""?> /><label for="2">Ordered products by date</label><br />
							<input type="radio" name="what" value="Products ordered by date" id="3" onclick="javascript:hideshow('obd','pbd');hideshow('eurogbp1','eurogbp')" <?=strlen($what)>0?is_selected("what","","Products ordered by date",(isset($_POST['what'])?$_POST:$_GET),"check"):""?> /><label for="3">Products ordered by date</label><br />
							<input type="radio" name="from_site" id="fromsiteuk" value="uk" <?=strlen($from_site)>0?is_selected("from_site","","uk",(isset($_POST['from_site'])?$_POST:$_GET),"check"):"checked='checked'"?> /><label for="fromsiteuk"> UK Site</label> <input type="radio" name="from_site" id="fromsiteie" value="ie" <?=strlen($from_site)>0?is_selected("from_site","","ie",(isset($_POST['from_site'])?$_POST:$_GET),"check"):""?> /><label for="fromsiteie"> IE Site</label> <input type="radio" name="from_site" id="fromsiteall" value="all" <?=strlen($from_site)>0?is_selected("from_site","","all",(isset($_POST['from_site'])?$_POST:$_GET),"check"):""?> /><label for="fromsiteie"> Both</label><br />
							<div id="eurogbp1" <?=$eurogbp1style?>><i style="padding-left:5px">Currency not needed for this report</i></div>
							<div id="eurogbp" <?=$eurogbpstyle?>><input type="radio" name="curr" id="currgbp" value="GBP" <?=strlen($curr)>0&&$curr!="GBP"?is_selected("curr","","GBP",(isset($_POST['curr'])?$_POST:$_GET),"check"):"checked='checked'"?> /><label for="currgbp"> GBP</label> <input type="radio" name="curr" id="curreuro" value="EUR" <?=strlen($curr)>0?is_selected("curr","","EUR",(isset($_POST['curr'])?$_POST:$_GET),"check"):""?> /><label for="curreuro"> EUR</label></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" value="Generate Report" class="formbutton" /></td>
						</tr>
					</table>
					</form>
					<br />
					<?
				}
				if(!isset($_GET['showgen'])||(strlen($varfrom)>0&&strlen($error)<1))
				{
					$rangefrom=strlen($varfrom)>0?"WHERE date_ordered >='$from'":"WHERE date_ordered >='".strtotime("today")."'";
					$rangeto=strlen($varto)>0?(strlen($varfrom)>0?"AND":"WHERE")." date_ordered <='$to'":"";
					$status=strlen($sstatus)>0&&$sstatus!="all"?(strlen($varfrom)>0?"AND":"WHERE")." order_status ='".$sstatus."'":"";
					$fsite=$from_site=="all"?"":" AND from_site='$from_site'";
					$tcurr=" AND currency='$curr'";
					$fsitetitle=$from_site=="all"?"":strtoupper($from_site);
					if($what=="Ordered products by date")
					{
						$pgnums=pagenums("SELECT invoice,qty,title,price,iorder_status,date_ordered FROM orders as o JOIN orderproducts as op ON o.order_id=op.order_id $rangefrom $rangeto $fsite $tcurr ORDER BY iorder_status DESC,invoice ASC,title ASC",$self."&amp;report=order&amp;showgen=1&amp;sstatus=$sstatus&amp;from=".urlencode($varfrom)."&amp;to=".urlencode($varto)."&amp;what=$what",30,5);
						$query=$pgnums[0];
						$todayq=mysql_query($query);
						$rowcount=mysql_num_rows($todayq);
						?>
						<table class="details">
						<tr>
							<td class="head" colspan="7"><?=$fsitetitle." ".$what?></td>
						</tr>
						<? if(strlen($pgnums[1])>0){?>
						<tr>
							<td class="infohead" colspan="7"><?=$pgnums[1]?></td>
						</tr>
						<? }?>
						<tr>
							<td class="subhead" style="width:5%">Invoice</td>
							<td class="subhead" width="6%" style="text-align:center">Ordered</td>
							<td class="subhead" style="width:5%;text-align:center">Qty</td>
							<td class="subhead" width="50%">Description</td>
							<td class="subhead" width="12%" style="text-align:center">Price (-VAT)</td>
							<td class="subhead" width="12%" style="text-align:center">Price (+VAT)</td>
							<td class="subhead" style="width:10%;text-align:center">Status</td>
						</tr>
						<? while($today=mysql_fetch_assoc($todayq)){$row=!isset($row)||$row==1?0:1;?>
							<tr class="row<?=$row?>">
								<td><a href="<?=$mainbase?>/admin.php?p=invoices&amp;act=view&amp;invoice=<?=$today['invoice']?>"><?=$today['invoice']?></a></td>
								<td style="text-align:center"><?=date("d/m/Y",$today['date_ordered'])?></td>
								<td style="text-align:center"><?=$today['qty']?></td>
								<td><?=$today['title']?></td>
								<td style="text-align:center"><?=$currencylang[$curr][2]?><?=number_format($today['price'],2)?></td>
								<td style="text-align:center"><?=$currencylang[$curr][2]?><?=number_format(addvat($today['price']),2)?></td>
								<td style="text-align:center"><?=$today['iorder_status']==1?"Complete":"Incomplete"?></td>
							</tr>
						<? }if($rowcount==0){?>
							<tr>
								<td colspan="7" style="text-align:center">No products found for this time period</td>
							</tr>
						<? }?>
						</table>
						<?
					}
					else if($what=="Products ordered by date")
					{
						$pgnums=pagenums("SELECT SUM(qty) as qty,title,SUM(price*qty) as price,MIN(price) as unitprice,prod_id,iOwner_FK FROM (orders as o JOIN orderproducts as op USING(order_id)) LEFT JOIN fusion as f ON f.iSubId_FK=op.prod_id $rangefrom $rangeto $fsite $tcurr GROUP BY op.prod_id ORDER BY SUM(qty) DESC,iorder_status DESC,invoice ASC,title ASC",$self."&amp;report=order&amp;showgen=1&amp;sstatus=$sstatus&amp;from=".urlencode($varfrom)."&amp;to=".urlencode($varto)."&amp;what=$what",30,5);						
						
						$query=$pgnums[0];
						$todayq=mysql_query($query);
						$rowcount=mysql_num_rows($todayq);
						?>
						<dfn>* Based on price at time of ordering including any discount</dfn>
						<table class="details">
						<tr>
							<td class="head" colspan="5"><div class="titles"><?=$fsitetitle." ".$what?></div></td>
						</tr>
						<? if(strlen($pgnums[1])>0){?>
						<tr class="infohead">
							<td colspan="5"><?=$pgnums[1]?></td>
						</tr>
						<? }?>
						<tr>
							<td class="subhead" style="width:15%;text-align:center">Qty</td>
							<td class="subhead" width="30%">Description</td>
							<td class="subhead" width="15%" style="text-align:right">Minimum Unit Price*</td>
							<td class="subhead" width="15%" style="text-align:right">Total Sales*</td>
							<td class="subhead" width="15%" style="text-align:right">Total Sales* (+VAT)</td>
						</tr>
						<? 
						$invloop="";
						while($today=mysql_fetch_assoc($todayq))
						{
							if($invloop!=$today['invoice']){
								$row_class=!isset($row_class)||$row_class=="row_dark"?"row_light":"row_dark";
								$num_rowsq=mysql_query("SELECT order_id FROM cart_orderproducts WHERE order_id='$today[order_id]'",CARTDB);
								$num_rows=mysql_num_rows($num_rowsq);
								$tdheight=20*$num_rows;
							}
							?>
							<tr class="<?=$row_class?>">
								<td style="text-align:center"><span><?=$today['qty']?></span></td>
								<td><a href="admin.php?p=builder&act=update&what=department&onitem=product&id=<?=$today['iOwner_FK']?>&pid=<?=$today['prod_id']?>"><?=$today['title']?></a></td>
								<td style="text-align:right"><span><?=$currencylang[$curr][2]?><?=number_format($today['unitprice'],2)?></span></td>
								<td style="text-align:right"><span><?=$currencylang[$curr][2]?><?=number_format($today['price'],2)?></span></td>
								<td style="text-align:right"><span><?=$currencylang[$curr][2]?><?=addvat($today['price'])?></span></td>
							</tr>
							<? 
							if($invloop!=$today['invoice']){$invloop=$today['invoice'];}
						}
						if($rowcount==0){?>
							<tr class="row_light">
								<td colspan="5" style="text-align:center">No products found for this time period</td>
							</tr>
						<? }?>
						</table>
						<?
					}
					else
					{
						$todaycq=mysql_query("SELECT count(CASE WHEN `currency`='GBP' THEN `order_id` END),SUM(CASE WHEN `currency`='GBP' THEN `total_price` END),AVG(CASE WHEN `currency`='GBP' THEN `total_price` END),MAX(CASE WHEN `currency`='GBP' THEN `total_price` END),count(CASE WHEN `currency`='EUR' THEN `order_id` END),SUM(CASE WHEN `currency`='EUR' THEN `total_price` END),AVG(CASE WHEN `currency`='EUR' THEN `total_price` END),MAX(CASE WHEN `currency`='EUR' THEN `total_price` END) FROM orders $rangefrom $rangeto $status $fsite AND iorder_status=1");
						$todayiq=mysql_query("SELECT count(CASE WHEN `currency`='GBP' THEN `order_id` END),SUM(CASE WHEN `currency`='GBP' THEN `total_price` END),AVG(CASE WHEN `currency`='GBP' THEN `total_price` END),MAX(CASE WHEN `currency`='GBP' THEN `total_price` END),count(CASE WHEN `currency`='EUR' THEN `order_id` END),SUM(CASE WHEN `currency`='EUR' THEN `total_price` END),AVG(CASE WHEN `currency`='EUR' THEN `total_price` END),MAX(CASE WHEN `currency`='EUR' THEN `total_price` END) FROM orders $rangefrom $rangeto $status $fsite AND iorder_status=0");
						
						$todayc=mysql_fetch_row($todaycq);
						$todayi=mysql_fetch_row($todayiq);
						
						?>
						<table class="details">
							<tr>
								<td class="head" colspan="3"><? if(!isset($_GET['showgen'])){?><?=$titleback." ".helplink($page)?> Today's <?=$fsitetitle?> Orders<? }else{echo $fsitetitle." ".$what;}?></td>
							</tr>
							<? if(strlen($varfrom)>0){?>
							<tr>
								<td class="infohead" colspan="3"><?=($todayc[0]+$todayi[0])?> orders between <?=date("d/m/Y",$from)?> and <?=date("d/m/Y",$to)?></td>
							</tr>
							<? }?>
							<tr>
								<td class="subhead">Description</td>
								<td class="subhead">GBP Value</td>
								<td class="subhead">EUR Value</td>
								
							</tr>
							<? if(strlen($varfrom)<1){?>
							<tr>
								<td>Orders today</td>
								<td><?=($todayc[0]+$todayi[0])?></td>								
								<td><?=($todayc[4]+$todayi[4])?></td>
							</tr>
							<? }?>
							<tr>
								<td colspan="3"><strong>Complete Orders</strong></td>
							</tr>
							<tr>
								<td class="first">Total orders</td>
								<td><? if($todayc[0]>0){?><a href="<?=$mainbase?>/admin.php?p=invoices&amp;from=<?=strlen($from)>0?$from:strtotime("today")?>&amp;to=<?=strlen($to)>0?$to:strtotime("tomorrow")?>&amp;istatus=1&amp;curr=<?=$curr?>&amp;sstatus=all"><? }?><?=$todayc[0]?><? if($todayc[0]>0){?></a><? }?></td>
								<td><? if($todayc[4]>0){?><a href="<?=$mainbase?>/admin.php?p=invoices&amp;from=<?=strlen($from)>0?$from:strtotime("today")?>&amp;to=<?=strlen($to)>0?$to:strtotime("tomorrow")?>&amp;istatus=1&amp;curr=EUR&amp;sstatus=all"><? }?><?=$todayc[4]?><? if($todayc[4]>0){?></a><? }?></td>
							</tr>
							<tr>
								<td>Total Sales</td>
								<td><?=$currencylang[GBP][2]?><?=number_format($todayc[1],2)?></td>
								<td><?=$currencylang[EUR][2]?><?=number_format($todayc[5],2)?></td>
							</tr>
							<tr>
								<td>Average Order</td>
								<td><?=$currencylang[GBP][2]?><?=number_format($todayc[2],2)?></td>
								<td><?=$currencylang[EUR][2]?><?=number_format($todayc[6],2)?></td>
							</tr>
							<tr>
								<td>Largest Order</td>
								<td><?=$currencylang[GBP][2]?><?=number_format($todayc[3],2)?></td>
								<td><?=$currencylang[EUR][2]?><?=number_format($todayc[7],2)?></td>
							</tr>
							<tr>
								<td colspan="3"><strong>Incomplete Orders</strong></td>
							</tr>
							<tr>
								<td>Total orders</td>
								<td><? if($todayi[0]>0){?><a href="<?=$mainbase?>/admin.php?p=invoices&amp;from=<?=strlen($from)>0?$from:strtotime("today")?>&amp;to=<?=strlen($to)>0?$to:strtotime("tomorrow")?>&amp;istatus=0&amp;curr=<?=$curr?>&amp;sstatus=all"><? }?><?=$todayi[0]?><? if($todayi[0]>0){?></a><? }?></td>
								<td><? if($todayi[4]>0){?><a href="<?=$mainbase?>/admin.php?p=invoices&amp;from=<?=strlen($from)>0?$from:strtotime("today")?>&amp;to=<?=strlen($to)>0?$to:strtotime("tomorrow")?>&amp;istatus=0&amp;curr=EUR&amp;sstatus=all"><? }?><?=$todayi[4]?><? if($todayi[4]>0){?></a><? }?></td>
							</tr>
							<tr>
								<td>Total Sales</td>
								<td><?=$currencylang[GBP][2]?><?=number_format($todayi[1],2)?></td>
								<td><?=$currencylang[EUR][2]?><?=number_format($todayi[5],2)?></td>
							</tr>
							<tr>
								<td>Average Order</td>
								<td><?=$currencylang[GBP][2]?><?=number_format($todayi[2],2)?></td>
								<td><?=$currencylang[EUR][2]?><?=number_format($todayi[6],2)?></td>
							</tr>
							<tr>
								<td>Largest Order</td>
								<td><?=$currencylang[GBP][2]?><?=number_format($todayi[3],2)?></td>
								<td><?=$currencylang[EUR][2]?><?=number_format($todayi[7],2)?></td>
							</tr>
						</table>
						<?
					}
				}
				break;
			default:			
				?>
				<table class="details">
					<tr>
						<td class="head" colspan="2"><?=helplink($page)?> Reports</td>
					</tr>
					<tr>
						<td class="first"><a href="<?=$mainbase?>/admin.php?p=reports&amp;report=order&amp;showgen=1">Order report</a></td>
						<td>Choose specific start and end dates to view total sales</td>
					</tr>
					<tr>
						<td><a href="<?=$self?>&amp;report=products">Product report</a></td>
						<td>View all products in your shop</td>
					</tr>
					<tr>
						<td><a href="<?=$self?>&amp;report=stock">Stock report</a></td>
						<td>View stock levels for your products</td>
					</tr>
					<tr>
						<td><a href="<?=$mainbase?>/admin.php?p=reports&amp;report=order">Today's Orders</a></td>
						<td>View total sales for todays date</td>
					</tr>
				</table>
				<? 
				break;
		}?>
		<!-- /CONTENT -->
	</div>
</div>