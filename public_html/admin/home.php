<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a></div>
<div id="main">
	<h2 id="pagetitle">Admin Home</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<?
		$todayam=date("U",mktime(0,0,0,date("n"),date("j"),date("Y")));
		$tomorrowam=$todayam+86400;
		
		$osq=mysql_query("SELECT count(CASE WHEN `currency`='GBP' THEN `order_id` END),SUM(CASE WHEN `currency`='GBP' THEN `total_price` END),AVG(CASE WHEN `currency`='GBP' THEN `total_price` END),MAX(CASE WHEN `currency`='GBP' THEN `total_price` END),count(CASE WHEN `currency`='EUR' THEN `order_id` END),SUM(CASE WHEN `currency`='EUR' THEN `total_price` END),AVG(CASE WHEN `currency`='EUR' THEN `total_price` END),MAX(CASE WHEN `currency`='EUR' THEN `total_price` END) FROM orders WHERE `date_ordered` >= '$todayam'");
		list($totalordersg,$totalg,$largestg,$averageg,$totalorderse,$totale,$largesteg,$averagee)=mysql_fetch_row($osq);
		$overalltotal=$totalordersg+$totalorderse;
		?>
		<table class="details">
		<tr>
			<td class="head" colspan="3">Today's Order Summary<?=$overalltotal>0?" of $overalltotal orders":""?> (<?=date("j / n / Y")?>)</td>
		</tr>
		<tr>
			<td class="subhead">Description</td>
			<td class="subhead">GBP Value</td>
			<td class="subhead">EUR Value</td>
		</tr>
		<tr>
			<td class="first">Orders today:</td>
			<td><?=(($totalg==0)?"0":"<a href='$mainbase/admin.php?p=invoices&amp;from=$todayam&amp;to=$tomorrowam&amp;ssortby=invoice&amp;sstatus=all&amp;ssortdir=DESC&amp;curr=GBP'>$totalordersg</a>")?></td>
			<td><?=(($totale==0)?"0":"<a href='$mainbase/admin.php?p=invoices&amp;from=$todayam&amp;to=$tomorrowam&amp;ssortby=invoice&amp;sstatus=all&amp;ssortdir=DESC&amp;curr=EUR'>$totalorderse</a>")?></td>
		</tr>
		<tr>
			<td class="first">Sales today:</td>
			<td>&#163;<?=(($totalg==0)?"0.00":number_format($totalg,2))?></td>
			<td><?=$currencylang[EUR][2]?><?=(($totale==0)?"0.00":number_format($totale,2))?></td>
		</tr>
		<tr>
			<td class="first">Largest Order:</td>
			<td>&#163;<?=(($largestg==0)?"0.00":number_format($largestg,2))?></td>
			<td><?=$currencylang[EUR][2]?><?=(($largeste==0)?"0.00":number_format($largeste,2))?></td>
		</tr>
		<tr>
			<td class="first">Average Order:</td>
			<td>&#163;<?=(($averageg==0)?"0.00":number_format($averageg,2))?></td>
			<td><?=$currencylang[EUR][2]?><?=(($averagee==0)?"0.00":number_format($averagee,2))?></td>
		</tr>
		</table>
		<table class="details" style="margin-top:20px">
		<tr>
			<td class="head" colspan="2">Site Summary</td>
		</tr>
		<tr>
			<td class="first">View all products</td>
			<td><a href="<?=$mainbase?>/admin.php?p=reports&amp;report=products">Product report</a></td>
		</tr>
		<tr>
			<td class="first">View stock levels</td>
			<td><a href="<?=$mainbase?>/admin.php?p=reports&amp;report=stock">Stock report</a></td>
		</tr>
		</table>
		<!-- /CONTENT -->
	</div>
</div>
