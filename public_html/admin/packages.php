<?
$act=(isset($_GET['act']))?$_GET['act']:"view";
$kprod_id=(isset($_GET['kprod_id']))?$_GET['kprod_id']:"";
if($kprod_id!="")
{
	$q=mysql_query("SELECT kit_sku,title,prod_id,kit_id,prod_id,in_kit_list FROM products LEFT JOIN productkits ON kprod_id=prod_id WHERE prod_id='$kprod_id'");
	$n=mysql_num_rows($q);
	$r=mysql_fetch_assoc($q);
}
?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if($act!="view"){?><a href="<?=$self?>"><? }?>Product Packages<? if($act!="view"){?></a><? }?><?=(($act!="view")?" &#187; ".(($kprod_id!="")?"Editing ".$r['title']:"Adding new package"):"")?></div>
<div id="main">
	<h2 id="pagetitle">Product Packages</h2>
	<div id="pagecontent">
	<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
	<!-- CONTENT -->
	<?
	switch($act)
	{
		case "add":
			?>
			<table class="details">
			<tr>
				<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> Build Package</td>
			</tr>
			<tr>
				<td style="width:50%;text-align:center">
				<form action="<?=$self?>&amp;act=add" method="post">
				<select name="dept" size="10" style="width:300px">
				<option value="0" <? if($_POST['dept']==0){?>selected="selected"<? }?>>Home Page</option>
				<option value="orphaned" <? if($_POST['dept']=="orphaned"){?>selected="selected"<? }?>>All orphan products</option>
				<option value="onlyinprods" <? if($_POST['dept']=="onlyinprods"){?>selected="selected"<? }?>>Products with only product(s) as parent</option>
				<?
				$deptsQ=mysql_query("SELECT fusionId,iOwner_FK,cat_id,title FROM categories as c JOIN fusion as f ON f.iSubId_FK=c.cat_id AND vtype='department' ORDER BY iOwner_FK,cat_id;");
				while($depts=mysql_fetch_assoc($deptsQ))
				{
					?>
					<option value="<?=$depts['cat_id']?>" <? if($_POST['dept']==$depts['cat_id']){?>selected="selected"<? }?>>
					<? if($depts['iOwner_FK']!=0){echo getparents($depts['iOwner_FK']); }?> / <?=$depts['title']?></option><?
				}
				?>
				</select>
				<div style="text-align:center"><input type="submit" name="submitdept" class="formbutton" value="View Items" /></div>
				</form>
				</td>
				<td style="width:50%;text-align:center">
				<form action="<?=$mainbase?>/admin.php" method="get">
				<input type="hidden" name="p" value="packages" />
				<input type="hidden" name="act" value="edit" />
				<select name="kprod_id" size="10" style="width:300px">
				<?
				if(isset($_POST['dept'])&&$_POST['dept']=="orphaned")
				{
					$sqlQ="SELECT p.title,f.iSubId_FK,prod_id,sku FROM products as p LEFT JOIN fusion as f ON f.iSubId_FK=p.prod_id WHERE f.iSubId_FK is null AND prod_id!='$kprod_id'";
				}
				else if(isset($_POST['dept'])&&$_POST['dept']=="onlyinprods")
				{
					$ids="";
					$notinacatQ=mysql_query("SELECT iSubId_FK FROM fusion WHERE vtype='product' AND vOwnerType='department' GROUP BY iSubId_FK");
					while($notinacat=mysql_fetch_assoc($notinacatQ)){if($ids!=""){$ids.=",";}$ids.="'$notinacat[iSubId_FK]'";}
					$sqlQ="SELECT title,iSubId_FK,prod_id,sku FROM fusion,products WHERE fusion.iSubId_FK=products.prod_id AND vtype='product' AND vOwnerType='product' AND iSubId_FK NOT IN($ids) AND prod_id!='$kprod_id' GROUP BY iSubId_FK";
				}
				else
				{
					$sqlQ="SELECT fusionId,iOwner_FK,prod_id,title,sku FROM products as p JOIN fusion as f ON f.iSubId_FK=p.prod_id AND vtype='product' AND vOwnerType='department' WHERE iOwner_FK='$_POST[dept]' AND prod_id!='$kprod_id' ORDER BY iSort;";
				}
				$itemsQ=mysql_query($sqlQ);
				while($items=mysql_fetch_assoc($itemsQ))
				{
					?>
					<option value="<?=$items['prod_id']?>"><?=$items['title']?></option>
					<?
				}
				?>
				</select>
				<div style="text-align:center"><input type="submit" name="submititem" class="formbutton" value="Build Package" /></div>
				</form>
				</td>
			</tr>			
			</table>
			</form>
			<p style="text-align:center"><a href="<?=$self?>">Return to package builder</a>
			<?
			break;
		case "edit":
			?>
			<form action="<?=$self?>&amp;act=edit&amp;kprod_id=<?=$kprod_id?>" method="post">
			<input type="hidden" name="prodid" value="<?=$kprod_id?>" />
			<table class="details">
			<tr>
				<td class="head" colspan="5"><?=$titleback." ".helplink($page)?> Build Package</td>
			</tr>
			<tr>
				<td class="infohead" colspan="5" style="vertical-align:middle"><div style="float:left;vertical-align:middle;line-height:160%">Current Package: <?=$r['title']?></div><div style="float:right;vertical-align:middle;line-height:160%">Show kit contents on product page <input type="checkbox" style="vertical-align:middle" name="in_kit_list" value="1" <? if($r['in_kit_list']==1){?>checked="checked"<? }?> /></div></td>
			</tr>
			<tr>
				<td class="subhead" style="width:12%">Code</td>
				<td class="subhead" style="width:65%">Product</td>
				<td class="subhead" style="width:5%">Qty</td>
				<td class="subhead" style="width:8%">Sort</td>
				<td class="subhead" style="width:10%;text-align:center">Remove</td>
			</tr>
			<?
			$q=mysql_query("SELECT kit_sku,title,prod_id,kit_id,prod_id,sku,item_qty,kit_sort,in_kit_list FROM productkits, products WHERE iProdId_FK=prod_id AND kprod_id='$kprod_id' ORDER BY kit_sort");
			$n=mysql_num_rows($q);
			while($r=mysql_fetch_assoc($q))
			{
				$row=(!isset($row)||$row=="1")?"0":"1";
				?>
				<tr class="row<?=$row?>">
					<td><?=$r['kit_sku']?></td>
					<td><?=$r['title']?> (<a href="admin.php?p=builder&amp;act=update&amp;onitem=product&amp;pid=<?=$r['prod_id']?>">Edit</a>)</td>
					<td><input type="text" name="qty[<?=$r['kit_id']?>]" value="<?=$r['item_qty']?>" class="formfields" /></td>
					<td><input type="text" name="sort[<?=$r['kit_id']?>]" value="<?=$r['kit_sort']?>" class="formfields" /></td>
					<td style="text-align:center"><a href="<?=$self?>&amp;act=edit&amp;kprod_id=<?=$kprod_id?>&amp;delete=<?=$r['kit_id']?>">Remove</a></td>
				</tr>
				<?
			}?>
			</table>
			<p class="submit"><input type="submit" name="packupdate" value="Update Package" class="formbutton" /></p>
			</form>
			
			<table class="details">
			<tr>
				<td class="head" colspan="2">Add to Package</td>
			</tr>
			<? if(isset($_GET['submititem'])){?>
			<tr>
				<td colspan="2" style="text-align:center" class="subhead">You must add atleast one item to build this new package</td>
			</tr>
			<? }?>
			<tr>
				<td style="width:50%;text-align:center">
				<form action="<?=$self?>&amp;act=edit&amp;kprod_id=<?=$kprod_id?>" method="post">
				<select name="dept" size="10" style="width:300px">
				<option value="0" <? if($_POST['dept']==0){?>selected="selected"<? }?>>Home Page</option>
				<option value="orphaned" <? if($_POST['dept']=="orphaned"){?>selected="selected"<? }?>>All orphan products</option>
				<option value="onlyinprods" <? if($_POST['dept']=="onlyinprods"){?>selected="selected"<? }?>>Products with only product(s) as parent</option>
				<?
				$deptsQ=mysql_query("SELECT fusionId,iOwner_FK,cat_id,title FROM categories as c JOIN fusion as f ON f.iSubId_FK=c.cat_id AND vtype='department' ORDER BY iOwner_FK,cat_id;");
				while($depts=mysql_fetch_assoc($deptsQ))
				{
					?>
					<option value="<?=$depts['cat_id']?>" <? if($_POST['dept']==$depts['cat_id']){?>selected="selected"<? }?>>
					<? if($depts['iOwner_FK']!=0){echo getparents($depts['iOwner_FK']); }?> / <?=$depts['title']?></option><?
				}
				?>
				</select>
				<div style="text-align:center"><input type="submit" name="submitdept" class="formbutton" value="View Items" /></div>
				</form>
				</td>
				<td style="width:50%;text-align:center">
				<form action="<?=$self?>&amp;act=edit&amp;kprod_id=<?=$kprod_id?>" method="post">
				<input type="hidden" name="nextsort" value="<?=($n+1)?>" />
				<select name="item" size="10" style="width:300px">
				<?
				if(isset($_POST['dept'])&&$_POST['dept']=="orphaned")
				{
					$sqlQ="SELECT p.title,f.iSubId_FK,prod_id,sku FROM products as p LEFT JOIN fusion as f ON f.iSubId_FK=p.prod_id WHERE f.iSubId_FK is null AND prod_id!='$kprod_id'";
				}
				else if(isset($_POST['dept'])&&$_POST['dept']=="onlyinprods")
				{
					$ids="";
					$notinacatQ=mysql_query("SELECT iSubId_FK FROM fusion WHERE vtype='product' AND vOwnerType='department' GROUP BY iSubId_FK");
					while($notinacat=mysql_fetch_assoc($notinacatQ)){if($ids!=""){$ids.=",";}$ids.="'$notinacat[iSubId_FK]'";}
					$sqlQ="SELECT title,iSubId_FK,prod_id,sku FROM fusion,products WHERE fusion.iSubId_FK=products.prod_id AND vtype='product' AND vOwnerType='product' AND iSubId_FK NOT IN($ids) AND prod_id!='$kprod_id' GROUP BY iSubId_FK";
				}
				else
				{
					$sqlQ="SELECT fusionId,iOwner_FK,prod_id,title,sku FROM products as p JOIN fusion as f ON f.iSubId_FK=p.prod_id AND vtype='product' AND vOwnerType='department' WHERE iOwner_FK='$_POST[dept]' AND prod_id!='$kprod_id' ORDER BY iSort;";
				}
				$itemsQ=mysql_query($sqlQ);
				while($items=mysql_fetch_assoc($itemsQ))
				{
					?>
					<option value="<?=$items['prod_id']."#".$items['sku']?>"><?=$items['title']?></option>
					<?
				}
				?>
				</select>
				<div style="text-align:center"><input type="submit" name="submititem" class="formbutton" value="Add to package" /></div>
				</form>
				</td>
			</tr>			
			</table>
			<p style="text-align:center"><a href="<?=$self?>">Return to package builder</a>
			<?
			break;
		case "disassemble":
			$q=mysql_query("SELECT title FROM products as p,productkits as pk WHERE p.prod_id=pk.kprod_id AND pk.kprod_id='$kprod_id'");
			list($title)=mysql_fetch_row($q);
			?>
			<table class="details">
			<tr>
				<td class="head">Package Disassemble</td>
			</tr>
			<tr>
				<td style="text-align:center">
				<!--<p><strong>No disassemble Johnny Five!</strong></p>-->
				<p>Package item: <?=$title?></p>
				<p>&quot;Unkit&quot; will disassemble the package and leave the main product as an individual product.</p>
				<p style="text-align:center"><a href="javascript:history.go(-1)">Cancel</a> | <a href="<?=$self?>&amp;act=disassemble&amp;delete=<?=$kprod_id?>">Unkit this item</a></p>
				</td>
			</tr>
			</table>
			<?
			break;
		default:
			$q=mysql_query("SELECT kit_sku,title,count(kprod_id) as prod_num,prod_id,kit_id,prod_id,kprod_id FROM productkits, products WHERE kprod_id=prod_id GROUP BY kprod_id");
			$n=mysql_num_rows($q);
			?>
			<table class="details">
			<tr>
				<td colspan="5" class="head"><div class="titles"><?=helplink($page)?> Product Packages</div><div class="links"><a href="<?=$self?>&amp;act=add">Create new package</a></div></td>
			</tr>
			<tr>
				<td colspan="5" class="infohead"><?=$n?> records found</td>
			</tr>
			<tr>
				<td class="subhead" style="width:12%">ID</td>
				<td class="subhead" style="width:65%">Name</td>
				<td class="subhead" style="width:8%;text-align:center">Products</td>
				<td class="subhead" style="width:5%;text-align:center">Edit</td>
				<td class="subhead" style="width:10%;text-align:center">Disassemble</td>
			</tr>
			<? 
			while($r=mysql_fetch_assoc($q)){
				$fq=mysql_query("SELECT fusionId FROM fusion,products WHERE products.prod_id=fusion.iSubId_FK and products.prod_id='$r[prod_id]'");
				list($fusionId)=mysql_fetch_row($fq);
				$row=(!isset($row)||$row=="1")?"0":"1";
				?>
				<tr class="row<?=$row?>">
					<td><?=$r['kit_sku']?></td>
					<td><a href="<?=$mainbase?>/admin.php?p=builder&amp;act=update&amp;what=department&amp;onitem=product&amp;id=<?=$fusionId?>&amp;pid=<?=$r['prod_id']?>"><?=$r['title']?></a></td>
					<td style="text-align:center"><?=$r['prod_num']?></td>
					<td style="text-align:center"><a href="<?=$self?>&amp;act=edit&amp;kprod_id=<?=$r['kprod_id']?>">Edit</a></td>
					<td style="text-align:center"><a href="<?=$self?>&amp;act=disassemble&amp;kprod_id=<?=$r['kprod_id']?>">Disassemble</a></td>
				</tr>
			<? }?>
			</table>
			<? 
		break;
	}
	?>
	<!-- /CONTENT -->
	</div>
</div>