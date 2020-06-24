<? if(basename($_SERVER['PHP_SELF'])!="index.php"&&$_SERVER['REQUEST_METHOD']!="POST"){die("Access Denied");}//direct access security 
$strCart=$_SESSION['cart'];
if(!is_array($strCart)||count($strCart)==0) 
{
	redirection("$mainbase/index.php?p=shopping_cart");
	exit();
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
		echo "Time restrains for chosen postage method have expired, please go back and choose a different method";
	}
	else
	{
		?>
		<div class="review">
		<div class="pg_content_left"><h3>Payment Method</h3><p class="note">Chosen on the next screen</p></div>
		<div class="pg_content_right"><h3>Postage Method</h3><p class="note"><?=$freepost==1?"Free Delivery":$postage['description']?><?=strlen($_SESSION['shipping_opt'])>0?"<br /><strong>".ucwords($_SESSION['shipping_opt'])."</strong>":""?></p></div>
		</div>
		<div class="review">
		<div class="pg_content_left">
		<h3>Billing Address</h3>
		<address>
		<?
		foreach($_SESSION['address_details']['billing'] as $detail => $info)
		{
			echo (($detail=="county")?get_county($info):(($detail=="country")?get_country($info):$info)).(($detail!="firstname"&&$info!="")?"<br />":" ");
		}
		?>
		</address>
		</div>
		<div class="pg_content_right">
		<h3>Delivery Address</h3>
		<address>
		<?
		if($_SESSION['address_details']['delivery']['sameasbilling']==1)
		{
			echo "Same as billing";
		}
		else
		{
			foreach($_SESSION['address_details']['delivery'] as $detail => $info)
			{
				if($detail!="sameasbilling"){
					echo (($detail=="county")?get_county($info):(($detail=="country")?get_country($info):$info)).(($detail!="firstname"&&$info!="")?"<br />":" ");
				}
			}
		}
		?>
		</address>
		</div>
		</div>
		<? cartcontents(0);?>
		<br />
		<a href="javascript:history.go(-1);" title="Go back to the order review page" style="float: left;" class="formbutton">Go back</a>
		<form action="<?=$securebase?>/index.php?p=payment" method="post" style="float:right">
		<input type="submit" name="complete_order" value="Proceed to Payment" class="formbutton" />
		</form>
		<?
	}
}
?>