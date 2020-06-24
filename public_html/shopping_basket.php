<?
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
if(!isset($_SESSION['backto'])){$_SESSION['backto']=str_replace("&","&amp;",$_SERVER['HTTP_REFERER']);}//for continue shopping
?><h2 id="pagetitle">Shopping Basket</h2><?
if(isset($_GET['rawcart'])){?><pre>===COPY FROM HERE===<br /><? print_r($_SESSION['cart']);?><br />===COPY TO HERE===</pre><? }
if(isset($_SESSION['cart'])&&count($_SESSION['cart'])>0&&is_array($_SESSION['cart']))
{
	?>
	<form action="<?=$mainbase?>/index.php?p=shopping_basket" method="post">
	<input type="hidden" name="identifier" value="update_cart" />
	<input type="hidden" name="backto" value="<?=$_SESSION['backto']?>" />
	<input type="hidden" name="checkout" value="<?=(($_SESSION['loggedin']!=0)?"$securebase/index.php?p=checkout_address":"$securebase/index.php?p=checkout_login")?>" />
	<? 
	cartcontents(1);
	?><br /><dfn>Please note, you will be able to amend your Postage Method before payment is taken</dfn><?
	$added_sugsq=mysql_query("SELECT * FROM fusion as f JOIN products as p ON f.`iSubId_FK`=p.`prod_id` WHERE `iOwner_FK` IN('".implode("','",$cart_ids)."') AND `prod_id` NOT IN('".implode("','",$cart_ids)."') AND `vtype`='product' AND `vOwnerType`='product' AND `allowoffer`='1' ORDER BY f.`iSort`");
	$added_sugs=mysql_num_rows($added_sugsq);
	if($added_sugs>0){?>
	<div><a href="<?=$mainbase?>/index.php?p=shopping_basket&amp;offerprod=1">View additional product suggestions</a> (exclusively in conjunction with products you have in your basket).</div>
	<? }
	if(isset($_GET['offerprod']))
	{list_products("SELECT * FROM fusion as f JOIN products as p ON f.`iSubId_FK`=p.`prod_id` WHERE `iOwner_FK` IN('".implode("','",$cart_ids)."') AND `prod_id` NOT IN('".implode("','",$cart_ids)."') AND `vtype`='product' AND `vOwnerType`='product' AND `allowoffer`='1' ORDER BY f.`iSort`","","We have found these additional suggestions for you.","offerprods",1);}
	?>
	<p style="float:left"><img src="content/img/main/mastercard.gif" alt="Mastercard" /> <img src="content/img/main/visa.gif" alt="Visa" /> <img src="content/img/main/maestro.gif" alt="Maestro" /> <img src="content/img/main/solo.gif" alt="Solo" /><? if(PAYPALON==1){?> <img src="content/img/main/paypal.gif" alt="PayPal" /><? }?></p>
	<input type="hidden" name="basket_total" value="<?=number_format($sub_total+$vattoadd,2)?>" />
	<p style="float:right"><strong>Discount Code:</strong> <input type="text" name="discount" value="" /> <input type="submit" name="mode" class="formbutton" value="Apply" /><?=$discountinfo?></p>
	<p class="actions" style="clear:both">
	<input name="mode" value="Update Basket" type="submit" class="formbutton" /> <input name="mode" value="Empty Basket" type="submit" class="formbutton" id="emptybasket" /> <input name="mode" value="Start Checkout" type="submit" class="formbutton" />
	</p>
	</form>
	<script>
	//<[CDATA[
	$('#emptybasket').click(function() {
		if(confirm('Are you sure you want to empty the basket?')) return true;else return false;
	}
	)
	//]]>
	</script>
	<?
}
else
{
	?>Your shopping basket is empty, <a href="<?=$mainbase?>">please return to the home page.</a><p>&#160;<br />&#160;</p><?
}
unset($_SESSION['backto']);
?>