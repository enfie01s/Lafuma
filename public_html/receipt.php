<? 
if((!isset($_SESSION["VendorTxCode"])||strlen($_SESSION["VendorTxCode"])==0)&&(!isset($_GET['invoice'])||(isset($_GET['invoice'])&&($_SESSION['loggedin']==0))))
{
	?><br />Sorry, the online receipt for this transaction has expired. Please check your inbox for a copy of this receipt. You can also view your recent transactions <?=($_SESSION['loggedin']==0)?"<a href='$mainbase/index.php?p=customer_login'>":"<a href='$mainbase/index.php?p=my_account'>"?>here</a>.<p>&#160;<br />&#160;</p><?
}
else
{
$txcode=(isset($_GET['invoice']))?"`invoice`='".$_GET['invoice']."'":"`VendorTxCode`='".mysql_real_escape_string($_SESSION['VendorTxCode'])."'";
$adbilq=mysql_query("SELECT * FROM orders WHERE $txcode")or die("SELECT * FROM orders WHERE $txcode<br />".mysql_error());
$adbil=mysql_fetch_assoc($adbilq);
?>
<div class="review">
	<div class="pg_content_left">
		<h3>Order Status</h3>
		<p class="note"><?=$adbil['order_status']?></p>
	</div>
	<div class="pg_content_right">
		<h3>Order Comments</h3>
		<p class="note">
			<?=((strlen($adbil['comments'])>0)?$adbil['comments']:"None")?>
		</p>
	</div>
</div>
<div class="review">
	<div class="pg_content_left">
		<h3>Payment Method</h3>
		<p class="note"><?=$adbil['pay_method']=="paypal"?"PayPal":"Credit/Debit Card"?></p>
	</div>
	<div class="pg_content_right">
		<h3>Postage Method</h3>
		<p class="note">
			<?=$adbil['ship_description']?>
		</p>
	</div>
</div>
<div class="review">
	<div class="pg_content_left">
		<h3>Billing Address</h3>
		<address>
		<?=$adbil['firstname']?> <?=$adbil['lastname']?><br />
		<?=$adbil['address1']?><br />
		<?=((strlen($adbil['address2'])>0)?$adbil['address2']."<br />":"")?>
		<?=$adbil['city']?><br />
		<?=get_county($adbil['state'])?><br />
		<?=get_country($adbil['country'])?><br />
		<?=$adbil['postcode']?><br />
		<?=$adbil['email']?><br />
		<?=$adbil['phone']?>
		</address>
	</div>
	<div class="pg_content_right">
		<h3>Delivery Address</h3>
		<address>
		<? if($adbil['sameasbilling']==1){?>
		Same as billing address
		<? }else{?>
		<?=$adbil['alt_name']?><br />
		<?=$adbil['alt_address1']?><br />
		<?=((strlen($adbil['alt_address2'])>0)?$adbil['alt_address2']."<br />":"")?>
		<?=$adbil['alt_city']?><br />
		<?=get_county($adbil['alt_state'])?><br />
		<?=get_country($adbil['alt_country'])?><br />
		<?=$adbil['alt_postcode']?><br />
		<?=$adbil['alt_phone']?>
		<? }?>
		</address>
	</div>
</div>
<p>&#160;</p>
<? ordercontents("o.$txcode","100%");?>
<p id="printlink"><a onclick="window.print();return false" href="#">Print Invoice</a></p>
<p>
Lafuma UK<br />
Bear House,<br />
Concorde Way<br />
Fareham<br />
PO15 5RL<br />
Email: <a href="mailto:<?=$admin_email?>"><?=$admin_email?></a><br />
Tel: <?=$sales_phone?><br />
Hampshire<br />
United Kingdom<br />
</p>
<p>
vat. Registration No: <?=$vatreg?><br />
Company Registration No.: <?=$coreg?>
</p>
<p>Thank you for your order, we appreciate your custom.<br />
If you could spare a few moments, we would be very grateful if you could add review(s) of the products you have purchased.
</p>
<? }?>
