<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; Delete Void Invoices</div>
<div id="main">
	<h2 id="pagetitle">Void Invoices</h2>
	<div id="pagecontent">
		<!-- CONTENT -->
		<?
		if(isset($_GET['sure']))
		{
			$oids=array();
			$voids=mysql_query("SELECT orders.order_id,orders.invoice,orders.date_ordered,orders.cust_id,orders.firstname,orders.lastname,orders.pay_method,orders.pay_status,orders.iorder_status FROM (orders LEFT JOIN orderproducts ON  orderproducts.order_id=orders.order_id) LEFT JOIN orderkits ON orderkits.order_id=orders.order_id WHERE orders.order_status='void' GROUP BY orders.order_id");echo mysql_error();
			$num_voids=mysql_num_rows($voids);
			while($void=mysql_fetch_row($voids)){if(!in_array($void[0],$oids)){$oids[]=$void[0];}}
			mysql_query("DELETE FROM orders WHERE order_id IN('".implode("','",$oids)."')");
			mysql_query("DELETE FROM orderproducts WHERE order_id IN('".implode("','",$oids)."')");
			mysql_query("DELETE FROM orderkits WHERE order_id IN('".implode("','",$oids)."')");
			mysql_query("DELETE FROM ordership WHERE order_id IN('".implode("','",$oids)."')");
			echo "Successfully deleted all void orders and their shipping records";
		}
		else{
			$voids=mysql_query("SELECT orders.order_id,orders.invoice,orders.date_ordered,orders.cust_id,orders.firstname,orders.lastname,orders.pay_method,orders.pay_status,orders.iorder_status FROM (orders LEFT JOIN orderproducts ON  orderproducts.order_id=orders.order_id) LEFT JOIN orderkits ON orderkits.order_id=orders.order_id WHERE orders.order_status='void' GROUP BY orders.order_id");echo mysql_error();
			$num_voids=mysql_num_rows($voids);
			?>
			<table class="details">
			<tr>
				<td class="head" colspan="8">Invoices</td>
			</tr>
			<tr>
				<td class="subhead" style="width:7%;text-align:center">Invoice</td>
				<td class="subhead" style="width:20%;text-align:center">Order Date</td>
				<td class="subhead" style="width:20%">Customer</td>
				<td class="subhead" style="width:8%;text-align:center">Method</td>
				<td class="subhead" style="width:5%;text-align:center">Status</td>
				<td class="subhead" style="width:15%;text-align:center">Status</td>
				<td class="subhead" style="width:15%;text-align:center">Status</td>
				<td class="subhead" style="width:10%;text-align:center">View</td>
			</tr>
			<? if($num_voids>0){while($inv=mysql_fetch_assoc($voids)){$row=((!isset($row)||$row=="1")?"0":"1");?>
			<tr class="row<?=$row?>">
				<td style="text-align:center"><?=$inv['invoice']?></td>
				<td style="text-align:center"><?=date("F j\, Y",$inv['date_ordered'])?></td>
				<td><? if(strlen($inv['cust_id'])>0){?><a href="<?=$mainbase?>/admin.php?p=customers&amp;act=view&amp;cust_id=<?=$inv['cust_id']?>"><? }?><?=ucwords($inv['firstname']." ".$inv['lastname'])?><? if(strlen($inv['cust_id'])>0){?></a><? }?></td>
				<td style="text-align:center"><?=$inv['pay_method']?></td>
				<td style="text-align:center"><?=(($inv['pay_status']==1)?"Paid":"Unpaid")?></td>
				<td style="text-align:center">VOID</td>
				<td style="text-align:center"><?=(($inv['iorder_status']==1)?"Complete":"Incomplete")?></td>
				<td style="text-align:center"><a href="admin.php?p=invoices&amp;act=view&amp;invoice=<?=$inv['invoice']?>">View</a></td>
			</tr>
			<? }}else{?>
			<tr>
				<td class="row0" colspan="8" style="text-align:center">No void orders found</td>
			</tr>
			<? }?>
			</table>
			<? if($num_voids>0){?><p class="submit">Delete ALL records of the above void orders <a href="<?=$mainbase?>/admin.php?p=voids&amp;sure=1">Delete Now</a></p><? }?>
			<? 
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>