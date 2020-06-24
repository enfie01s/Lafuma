<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
include("./sagepay/includes.php");

?>
<!--Default Postage Method: <? //if($freepost==1){echo htmlspecialchars($freepostdesc,ENT_QUOTES,"ISO-8859-1");}else{ echo $defpostdesc." (to ".(WHICHLIST=="on_uk_list"?"UK":"Ireland").") ".($defpostprice>0?$currarr[$domainext][2].number_format($defpostprice,2):"FREE"); }?>-->
<?
if($strServerType=="DIRECT"){$postage_post=$securebase."/index.php?p=review";}
else{$postage_post=$securebase."/sagepay/transactionRegister.php";}
?>
<form action="<?=$postage_post?>" method="post">
	<div class="tabletitle">Select Postage Method</div>
	<table>
		<tr>
			<td>
			<? 
			if(isset($_SESSION['address_details']['delivery']['country']))
			{$which="pmd.`post_details_id` != '10' AND `availability` LIKE '%-".$_SESSION['address_details']['delivery']['country']."-%'";}
			else{
				$which="`availability` LIKE '%-".DEFAULTDEL."-%'";
			}
			$postageq=mysql_query("SELECT pm.`post_id` as post_id,pmd.`restraints` as restraints,pmd.`post_details_id` as post_details_id,pmd.`description` as description,`field3".PRICECUR."` as field3,`field1".PRICECUR."` as field1,`options` FROM postage_methods as pm,postage_method_details as pmd WHERE pm.`post_id`=pmd.`post_id` AND `status`='1' AND ".$which." ORDER BY `field3".PRICECUR."` ASC");
			while($postage=mysql_fetch_assoc($postageq))
			{
				if($postage['post_details_id']==$_SESSION['shipping']){list($price,$postdesc,$postid)=postagecalc($totalforpostage,$_SESSION['shipping']);$selected=$postid;}
			}
			if($selected!=$_SESSION['shipping'])
			{
				mysql_data_seek($postageq,0);
				$selected=1000;
				while($postage=mysql_fetch_assoc($postageq))
				{
					if($postage['field3']<$selected)
					{
						list($price,$postdesc,$postid)=postagecalc($totalforpostage,$_SESSION['shipping']);
						$selected=$postid;
					}
				}				
			}
			mysql_data_seek($postageq,0);
			while($postage=mysql_fetch_assoc($postageq))
			{				
				list($price,$postdesc,$postid)=postagecalc($totalforpostage,$postage['post_details_id']);
				$disable="";$bwhiteout="";$blwhiteout="";
				$restraints=explode("#",$postage['restraints']);
				if(strlen($restraints[0])>0)
				{
					$stamp=in_array($restraints[2],$days)/*stristr($restraints[2]," ")!=null*/?strtotime($restraints[2]):strtotime(date("l",strtotime("Today")).$restraints[2]);
					if(postage_expired($stamp)||array_key_exists(date("j-n-Y",$stamp+86400),$bankhols))
					{
						$disable="disabled='disabled'";$bwhiteout="style='color:#999999'";$blwhiteout="style='color:#a0bed1'";
					}
				}
				else if($postage['post_id']==5&&$postage['field1']>$totalforpostage)
				{
					$disable="disabled='disabled'";$bwhiteout="style='color:#999999'";$blwhiteout="style='color:#a0bed1'";
				}
				?>
				<input type="radio" name="shipping" value="<?=$postid?>" id="ship<?=$postid?>" <?=$selected==$postid?"checked='checked'":""?> <?=$disable?> />
				<label for="ship<?=$postid?>" <?=$bwhiteout?>>
				<?=$postdesc?>
				<?
				if(strlen($postage['restraints'])>0)
				{
					$restr=explode(" ",$restraints[2]);
					echo " (for orders placed ".$restraints[1]." ".(count($restr)>1?date("l g:ia\)",$stamp):date("g:ia\)",$stamp));
				}				
				?>
				<span <?=$blwhiteout?>><?=$price>0?$currarr[$domainext][2].number_format($price,2):"FREE"?></span></label>
				<? if(strlen($postage['options'])>0){$opties=explode("/",$postage['options']);?>
				<select name="option[<?=$postid?>]" onclick="document.getElementById('ship<?=$postid?>').checked=true">
				<? 
					$todayL=date("l");
					$week=604800;
					foreach($opties as $oi => $optie){
					$ddd=$optie;				
					if(in_array($optie,$daysofweek))
					{
						//if($optie=="monday"&&in_array(date("l"),array("Saturday","Sunday"))){$ddd="";}
						//else{
						$ddd=ucwords($optie)."  ";
						if($optie==strtolower($todayL)||array_key_exists(date("j-n-Y",strtotime($optie)),$bankhols))
						{$ddd.=date("jS M",strtotime($optie)+$week);}
						
						else if($optie=="monday"&&(in_array($todayL,array("Saturday","Sunday"))||($todayL=="Friday"&&date("U")>$deadline)))
						{$ddd.=date("jS M",strtotime($optie)+($week));}
						
						else if($optie==strtolower(date("l",strtotime("tomorrow")))&&date("U")>$deadline)
						{$ddd.=date("jS M",strtotime($optie)+$week);}
						
						else
						{$ddd.=date("jS M",strtotime($optie));}
						//}
					}
					if(strlen($ddd)>0){
					?>
					<option value="<?=$ddd?>" <? if($_SESSION['shipping']==$postid&&$_SESSION['shipping_opt']==$ddd){?>selected='selected'<? }?>><?=$ddd?></option>
				<? }}?>
				</select>
				<? }?>				
				<br />
				<? 
			}?>
			<br /><dfn>All orders are shipped from the UK. No orders will be shipped on weekends or bank holidays.</dfn>
			<? if($strServerType=="SERVER"){?>
			<textarea name="comments" id="comments" class="formfield" style="height:70px;width:100%;margin-top:20px;" onFocus="this.select()"><?=isset($_SESSION['comments'])?$_SESSION['comments']:"Special requirements"?></textarea><br /><dfn>* Please note: All goods MUST be signed for BY THE ADDRESSEE and will not be left with a neighbour or unattended.</dfn>
			<? }?>
			</td>
		</tr>
	</table>
	<br />
	<a href="javascript:history.go(-1);" title="Go back to the address page" style="float: left;" class="formbutton">Go back</a>
	<input type="submit" name="postmethod" value="Continue" class="formbutton" style="float:right" />
</form>

