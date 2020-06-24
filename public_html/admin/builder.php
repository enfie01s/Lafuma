<? 
if(basename($_SERVER['PHP_SELF'])!="admin.php"){header("Location: ../admin.php?p=builder");}//direct access security 

$owner=(isset($_GET['id']))?$getescaped['id']:0;
$what=(isset($_GET['what']))?$getescaped['what']:"";
$action=(isset($_GET['act']))?$getescaped['act']:"";
$onitem=(isset($_GET['onitem']))?$getescaped['onitem']:"";
$actwhatid="act=$action&amp;what=$what&amp;id=$owner";
$name=(!isset($_GET['name']))?"Home Page":$getescaped['name'];

$crumbQ=mysql_query("SELECT `iOwner_FK`,`iSubId_FK` FROM fusion WHERE `fusionId`='$owner'");
list($fusionOwner,$fusionSub)=mysql_fetch_row($crumbQ);
$fusionOwner=($fusionOwner==null)?0:$fusionOwner;
$theaction=((substr($action,-1,1)=="e")?substr($action,0,strlen($action)-1):$action)."ing";//adding/updating/deleting
if($action=="update"&&!isset($_GET['name']))
{
	$tbl=($what=="product")?"products":"categories";
	$matchid=($what=="product")?"prod_id":"cat_id";
	$nameQ=mysql_query("SELECT title FROM `$tbl` WHERE `$matchid` ='$fusionSub'");
	list($ptitle)=mysql_fetch_row($nameQ);
	$name=$ptitle;
}
$breadname=$name!="Home Page"?$name:"";
$thecrumbs=getcrumbs($fusionOwner);
$expcrumbs=explode(" #187; ",$thecrumbs);
preg_match( "/(?<=&amp;id=)([0-9]*)/",   $expcrumbs[0], $idcrumb);
?>
<script type="text/javascript">
<!--[CDATA[
function roundit(n,dec) {
	n = parseFloat(n);
	if(!isNaN(n)){
		if(!dec) var dec= 0;
		var factor= Math.pow(10,dec);
		return Math.floor(n*factor+((n*factor*10)%10>=5?1:0))/factor;
	}else{
		return n;
	}
}
function calcvat(thvat)
{
	cur=document.getElementById("calcvatfield");
	newval=cur.innerHTML/((thvat / 100)+1);
	newval=roundit(newval,2);
	document.getElementById("calcvatresult").innerHTML=newval;
}
//]]-->
</script>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <?=(($action!="")?"<a href='$self'>":"")?>Shop Builder<?=(($action!="")?"</a>":"")?> <?=$thecrumbs?> 
<? if($action!="view"&&$breadname!=""){?> &#187; <a href="<?=$self?>&amp;act=view&amp;what=<?=$what?>&amp;id=<?=$owner?>&amp;name=<?=urlencode($breadname)?>"><?=$breadname?></a><? }else if($breadname!=""){echo " &#187; ".$breadname; } if($onitem!=""&&$action!="view"){?> &#187; <?=ucwords($theaction)?> <? echo $onitem;}?></div>
<div id="main">
	<h2 id="pagetitle">Shop Builder</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? }?>
		<!-- CONTENT -->
		<? 
		switch($action)
		{
			case "attach":/* associate product to category*/
				$owned=(!isset($_POST['dept']))?0:$_POST['dept'];
				$thesub=$fusionSub!=""?$fusionSub:0;
				?>
				<table class="details">
					<tr>
						<td class="head" colspan="2"><?=$titleback." ".helplink("attach")?> Associate Items to <?=$name?></td>
					</tr>
					<tr>
						<td class="subhead" width="50%">Departments</td>
						<td class="subhead" width="50%">Items</td>
					</tr>
					<tr>
						<td>
						<form action="<?=$self?>&amp;<?=$actwhatid?>&amp;name=<?=urlencode($name)?>&amp;nextsort=<?=$_GET['nextsort']?>" method="post">
							<select name="dept" style="width:350px" size="25">
								<option value="0" <? if($owned==0){?>selected="selected"<? }?>>Home Page</option>
								<option value="orphaned" <? if($_POST['dept']=="orphaned"){?>selected="selected"<? }?>>All orphan products</option>
								<option value="onlyinprods" <? if($_POST['dept']=="onlyinprods"){?>selected="selected"<? }?>>Products with only product(s) as parent</option>
								<?
								$deptsQ=mysql_query("SELECT `fusionId`,`iOwner_FK`,`cat_id`,`title` FROM categories as c JOIN fusion as f ON f.`iSubId_FK`=c.`cat_id` AND `vtype`='department' ORDER BY `iOwner_FK`,`cat_id`");
								while($depts=mysql_fetch_assoc($deptsQ))
								{
									?>
									<option value="<?=$depts['cat_id']?>" <? if($owned==$depts['cat_id']){?>selected="selected"<? }?>>
									<? if($depts['iOwner_FK']!=0){echo getparents($depts['iOwner_FK']); }?> / <?=$depts['title']?></option><?
								}
								?>
							</select>
							<div style="text-align:center"><input type="submit" name="submitdept" class="formbutton" value="View Items" /></div>
						</form>
						</td>
						<td>
						<form action="<?=$self?>&amp;<?=$actwhatid?>&amp;oid=<?=$thesub?>&amp;name=<?=urlencode($name)?>" method="post">
							<input type="hidden" name="nextsort" value="<?=$_GET['nextsort']?>" />
							<select name="item[]" style="width:350px" multiple="multiple" size="25">
							<?
							if(isset($_POST['dept'])&&$_POST['dept']=="orphaned")
							{
								$sqlQ="SELECT p.`title`,f.`iSubId_FK`,`prod_id` FROM products as p LEFT JOIN fusion as f ON f.`iSubId_FK`=p.`prod_id` WHERE f.`iSubId_FK` is null";
							}
							else if(isset($_POST['dept'])&&$_POST['dept']=="onlyinprods")
							{
								$ids="";
								$notinacatQ=mysql_query("SELECT `iSubId_FK` FROM fusion WHERE `vtype`='product' AND `vOwnerType`='department' GROUP BY `iSubId_FK`");
								while($notinacat=mysql_fetch_assoc($notinacatQ)){if($ids!=""){$ids.=",";}$ids.="'$notinacat[iSubId_FK]'";}
								$sqlQ="SELECT `title`,`iSubId_FK`,`prod_id` FROM fusion,products WHERE fusion.`iSubId_FK`=products.`prod_id` AND `vtype`='product' AND `vOwnerType`='product' AND `iSubId_FK` NOT IN($ids) GROUP BY `iSubId_FK`";
							}
							else
							{
								$sqlQ="SELECT `fusionId`,`iOwner_FK`,`prod_id`,`title` FROM products as p JOIN fusion as f ON f.`iSubId_FK`=p.`prod_id` AND `vtype`='product' AND `vOwnerType`='department' WHERE `iOwner_FK`='$postescaped[dept]' ORDER BY `iSort`";
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
							<div style="text-align:center"><input type="submit" name="submitdept" class="formbutton" value="Attach Item(s)" /></div>
						</form>
						</td>
					</tr>
				</table>
				<br />
				<table class="details">
					<tr>
						<td class="head" colspan="4">Currently attached items</td>
					</tr>
					<tr>
						<td class="subhead" style="width:480px">Item title</td>
						<td class="subhead" style="text-align:center">On/Off</td>
						<td class="subhead" style="text-align:center">Type</td>
						<td class="subhead" style="text-align:center">Remove</td>
					</tr>
					<?
						$attachedQ=mysql_query("SELECT p.`title`,f.`iSubId_FK`,f.`vtype`,f.`fusionId` as fid,`iState` FROM fusion as f JOIN products as p ON f.`iSubId_FK`=p.`prod_id` AND f.`vtype`='product' WHERE f.`iOwner_FK`='$thesub'");
						$attchednum=mysql_num_rows($attachedQ);
						while($attached=mysql_fetch_assoc($attachedQ))
						{
							$row=((!isset($row)||$row=="1")?"0":"1");
							?>
							<tr class="row<?=$row?>">
								<td><a href="<?=$self?>&amp;act=view&amp;what=product&amp;id=<?=$attached['fid']?>&amp;name=<?=urlencode($attached['title'])?>"><?=$attached['title']?></a></td>
								<td style="text-align:center"><?=(($attached['iState']==0)?"Off":"On")?></td>
								<td style="text-align:center"><?=ucwords($attached['vtype'])?></td>
								<td style="text-align:center"><a href="<?=$self?>&amp;unattach=<?=$attached['fid']?>&amp;<?=$actwhatid?>&amp;name=<?=urlencode($name)?>">Remove</a></td>
							</tr>
							<?
						}
						if($attchednum==0){?>
					<tr>
						<td colspan="3" style="text-align:center">No product exists under <strong>
							<?=$name?>
							</strong></td>
					</tr>
					<? }?>
				</table>
				<?
				break;
			case "attach_opts":
				$where="";
				if(!isset($_SESSION['cur_opts']))
				{
					$curids="";
					$_SESSION['cur_opts']=array();
					$currentQ=mysql_query("SELECT * FROM product_options as po,fusion_options as fo WHERE fo.`opt_id`=po.`opt_id` AND fo.`prod_id`='$getescaped[pid]' ORDER BY fo.`vsort`");
					$x=0;
					while($current=mysql_fetch_assoc($currentQ))
					{
						$_SESSION['cur_opts'][$x]=array($current['opt_id'],$current['description']." (".$current['opt_name'].")",$current['vsort']);
						if(strlen($curids)>1){$curids.=",";}$curids.="'$current[opt_id]'";
						$x++;
					}
					$where=strlen($curids)>0?"WHERE `opt_id` NOT IN($curids)":"";
				}
				
				$_SESSION['avail_opts']=array();
				$availQ=mysql_query("SELECT * FROM product_options $where");
				$x=0;
				while($avail=mysql_fetch_assoc($availQ))
				{
					$_SESSION['avail_opts'][$x]=array($avail['opt_id'],$avail['description']." (".$avail['opt_name'].")",$x);
					$x++;
				}
				?>
				<form action="<?=$self?>&amp;act=attach_opts&amp;id=<?=$owner?>&amp;what=product&amp;pid=<?=$_GET['pid']?>&amp;name=<?=urlencode($name)?>" method="post">
					<table style="width:80%" class="details">
					<tr>
						<td class="head" colspan="2"><?=$titleback." ".helplink("attach")?> Attach options</td>
					</tr>
					<tr>
						<td class="infohead" colspan="2">
						<div style="float:left">Product: <?=$name?></div>
						<div style="float:right"><?=count($_SESSION['cur_opts'])?> Current option<?=((count($_SESSION['cur_opts'])>1)?"s":"")?></div>
						</td>
					</tr>
					<tr>
						<td class="subhead" style="text-align:center" width="50%">Available Options</td>
						<td class="subhead" style="text-align:center" width="50%">Current Options</td>
					</tr>
					<tr>
						<td style="text-align:center">
						<select name="avail" size="10" style="width:270px">
						<? 
						foreach($_SESSION['avail_opts'] as $aid => $aarray)
						{
							?>
							<option value="<?=$aid?>" <? if($aid==0){?>selected="selected"<? }?>><?=$aarray[1]?></option>
							<?
						}
						?>
						</select>
						<p style="text-align:center"><input type="submit" name="opt_unatt" class="formbutton" value="&#60;&#60;" /> <input type="submit" name="opt_att" class="formbutton" value="&#62;&#62;" /></p>
						</td>
						<td style="text-align:center">
						<select name="current" size="10" style="width:270px">
						<? 
						foreach($_SESSION['cur_opts'] as $id => $array)
						{
							?>
							<option value="<?=$id?>" <? if((isset($highlight)&&$id==$highlight)||(!isset($highlight)&&$id==$_POST['current'])){?>selected="selected"<? }?>><?=$array[1]?></option>
							<?
						}
						?>
						</select>
						<p style="text-align:center"><input type="submit" name="opt_ord_up" class="formbutton" value="UP" /> <input type="submit" name="opt_ord_dn" class="formbutton" value="DN" /></p>
						</td>
					</tr>			
					<tr>
						<td colspan="2" style="text-align:center">
						<input type="submit" name="save_attachments" class="formbutton" value="Save changes" />
						<br /><? if(!isset($successattach)){?>You must save your changes<? }else{echo $successattach;}?>
						<p style="text-align:center"><a href="<?=$self?>&amp;act=update&amp;what=department&amp;onitem=product&amp;id=<?=$fusionOwner?>&pid=<?=$_GET['pid']?>">Return to product</a></p>
						</td>
					</tr>
					</table>
				</form>
				<?
				break;
			case "add":
			case "update":
			case "duplicate":
				/* WYSIWYG ITEMS */
				$rootpath = "../../../../";
				$uploaddir = $onitem=="product"?"content/img/products/descriptions/":"content/img/categories/";//relative to $rootpath
				$imagebaseurl = $prefixurl;
				if($action=="update"||$action=="duplicate")
				{
					if($onitem=="product")
					{
						$itemarrQS="SELECT * FROM products as p LEFT JOIN fusion as f ON p.`prod_id`=f.`iSubId_FK` AND `iOwner_FK`='$fusionSub' WHERE `prod_id`='$getescaped[pid]'";
					}
					else//dept builder
					{
						$itemarrQS="SELECT * FROM categories as c LEFT JOIN fusion as f ON c.`cat_id`=f.`iSubId_FK` and `iOwner_FK`='$fusionOwner' WHERE `cat_id`='$getescaped[cid]'";
					}
					$itemarrQ=mysql_query($itemarrQS);
					$itemarr=mysql_fetch_assoc($itemarrQ);
				}
				$formarr=($action=="add"||isset($_SESSION['error']))?$_POST:$itemarr;
				?>
				<script type="text/javascript">var imageParams="<?=$uploaddir?>|<?=$mainbase?>|<?=$rootpath?>"</script>
				<script type="text/javascript" src="content/wysiwyg/scripts/wysiwyg.js"></script>
				<script type="text/javascript" src="content/wysiwyg/scripts/wysiwyg-settings.js"></script>
				<script type="text/javascript"> WYSIWYG.attach('content', small);</script>
				<?
				/* /WYSIWYG ITEMS */
				
				$reportgets=(isset($_GET['report']))?"&amp;report=$_GET[report]".((isset($_GET['rpage']))?"&amp;rpage=$_GET[rpage]":""):"";
				?>
				<form enctype="multipart/form-data" action="<?=$self?>&amp;act=<?=$action?>&amp;what=<?=$what?>&amp;onitem=<?=$onitem?>&amp;id=<?=$owner?>&amp;oid=<?=$fusionSub?><?=((isset($_GET['pid']))?"&amp;pid=$_GET[pid]":"&amp;cid=$_GET[cid]")?>&amp;name=<?=urlencode($name)?><?=$reportgets?>&amp;nextsort=<?=$_GET['nextsort']?>" method="post">
					<input type="hidden" name="admin_id" value="<?=$uaa['admin_id']?>" />
				<?
				if($onitem=="product")
				{
					?>
					<input type="hidden" name="prodform" value="<?=$action?>" />
					<? if($action=="update"){?>
					<input type="hidden" name="origsku" value="<?=$formarr['sku']?>" />
					<input type="hidden" name="origdate" value="<?=$formarr['date_created']?>" />
					<? }if($action=="update"||$action=="duplicate"){?>
					<input type="hidden" name="old_img" value="<?=$formarr['img_filename']?>" />
					<input type="hidden" name="fusion" value="<?=$formarr['fusionId']?>" />
					<input type="hidden" name="name" value="<?=urlencode($name)?>" />
					<? }?>
					<table class="details" style="width:85%">
					<tr>
						<td class="head" colspan="2"><div style="float:left"><?=$titleback." ".helplink("product")?> Product Builder</div><? if($onitem=="product"&&$action!="add"){?><div style="float:right"><a href="<?=$self?>&amp;act=attach_opts&amp;id=<?=$itemarr['fusionId']?>&amp;what=product&amp;pid=<?=$itemarr['prod_id']?>&amp;name=<?=urlencode($itemarr['title'])?>">Standard Options</a></div><? }?></td>
					</tr>
					<tr>
						<td class="first"><label for="title">Product title</label> <span class="reqd">*</span></td>
						<td><input type="text" name="title" id="title" class="formfield" <?=highlighterrors($higherr,"title")?> value="<?=((count($formarr)>0)?$formarr['title']:"")?>" /></td>
					</tr>
					<tr>
						<td><label for="sku">Product code</label> <span class="reqd">*</span></td>
						<td>
						<select name="sku" id="sku" class="formfield" <? if(in_array("sku",$higherr)){?>style="border:1px solid red;"<? }?>>
						<option value="">Please select...</option>
						<?
						$skusQ=mysql_query("SELECT `nav_sku` FROM nav_stock GROUP BY `nav_sku`");
						while($skus=mysql_fetch_assoc($skusQ))
						{
							?>
							<option value="<?=$skus['nav_sku']?>" <?=((count($formarr)>0&&$formarr['sku']==$skus['nav_sku'])?"selected='selected'":"")?>><?=$skus['nav_sku']?></option>
							<?
						}
						?>
						</select>
						</td>
					</tr>
					<tr>
						<td><label for="barcode">Barcode</label> <span class="reqd">*</span></td>
						<td><input type="text" name="barcode" id="barcode" class="formfieldm" <? if(in_array("barcode",$higherr)){?>style="border:1px solid red;"<? }?> value="<?=((count($formarr)>0)?$formarr['barcode']:"")?>" /></td>
					</tr>
					<tr>
						<td><label for="price">GBP Price (EX Vat)</label> <span class="reqd">*</span><br />
						<dfn>Formula: Price (inc vat) &#247; <?=round(($vat/100)+1,2)?></dfn></td>
						<td><input type="text" name="price" id="price" class="formfieldm" <? if(in_array("price",$higherr)){?>style="border:1px solid red;"<? }?> value="<?=((count($formarr)>0)?$formarr['price']:"")?>" /> 
						<? if(isset($formarr['price'])){
							$vatprice=getvat($formarr['price'],"net");?>
						(<?=$vatprice[0]?> Inc <?=$vat?>% Vat)
						<? }?>
						
						</td>
					</tr>
					<tr>
						<td><label for="price_euro">Euro Price (EX Vat)</label> <span class="reqd">*</span><br />
						<dfn>Formula: Price (inc vat) &#247; <?=round(($vat/100)+1,2)?></dfn></td>
						<td><input type="text" name="price_euro" id="price_euro" class="formfieldm" <? if(in_array("price_euro",$higherr)){?>style="border:1px solid red;"<? }?> value="<?=((count($formarr)>0)?$formarr['price_euro']:"")?>" /> 
						<? if(isset($formarr['price_euro'])){
							$vatprice=getvat($formarr['price_euro'],"net");?>
						(<?=$vatprice[0]?> Inc <?=$vat?>% Vat)
						<? }?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><div style="margin-top:1px;">
						<div style="width:148px;height:16px;float:left;position:relative;top:2px;left:0;">
						<strong>Calculation tool:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inc Vat: 
						</div>
						<div contenteditable="true" style="padding-left:2px;width:50px;height:16px;float:left;margin-right:5px;" class="formfields" id="calcvatfield" onkeyup="javascript: calcvat(<?=$vat?>)"></div>
						<div style="width:40px;height:16px;float:left;position:relative;top:2px;left:0;"> Ex Vat: </div>
						<div style="width:60px;height:16px;float:left;">
							<div id="calcvatresult" style="padding-left:2px;width:50px;height:16px;float:left;margin:0;" class="formfields"></div>
							<div class="clear"></div>
						</div>
						</td>
					</tr>
					<tr>
						<td class="subhead" colspan="2"><strong>Sale Controls</strong></td>
					</tr>
					<tr>
						<td><label for="list_price">OLD Price GBP (Inc Vat)</label><br /><dfn>(Price to show crossed out on sale)</dfn></td>
						<td><input type="text" name="list_price" id="list_price" class="formfieldm" value="<?=((count($formarr)>0)?$formarr['list_price']:"")?>" /></td>
					</tr>
					<tr>
						<td><label for="list_price_euro">OLD Price EURO (Inc Vat)</label><br /><dfn>(Price to show crossed out on sale)</dfn></td>
						<td><input type="text" name="list_price_euro" id="list_price_euro" class="formfieldm" value="<?=((count($formarr)>0)?$formarr['list_price_euro']:"")?>" /></td>
					</tr>
					<tr>
						<td><label for="exclude_discount">Exclude from discounts</label></td>
						<td><input type="checkbox" name="exclude_discount" id="exclude_discount" value="1" <?=((count($formarr)>0&&$formarr['exclude_discount']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td class="subhead" colspan="2"><strong>Postage &amp; Supplier</strong></td>
					</tr>
					<tr>
						<td><label for="item_weight">Item Weight</label></td>
						<td><input type="text" name="item_weight" id="item_weight" class="formfieldm" value="<?=((count($formarr)>0)?$formarr['item_weight']:"")?>" /></td>
					</tr>
					<tr>
						<td><label for="shipnotes">Product notes</label></td>
						<td><input type="text" name="shipnotes" id="shipnotes" class="formfield" value="<?=((count($formarr)>0)?$formarr['shipnotes']:"")?>" /></td>
					</tr>
					<tr>
						<td class="subhead" colspan="2"><strong>Images</strong></td>
					</tr>
					<? if(($onitem=="product"&&$action!="update")||$onitem!="product"){$x=1;foreach($images_arr['product']['images'] as $dir => $size){$type=($dir!="main")?$dir:"";?>
						<tr>
							<td><label for="pimage<?=$x?>"><?=ucwords($dir)?> Image</label> <? if($action=="add"){?><span class="reqd">*</span><? }?></td>
							<td>
							<? if($action!="update"){?>
							<input type="hidden" name="imgtype[]" value="<?=$type?>" /><input type="hidden" name="imgsize[]" value="<?=$size?>" /><input type="file" name="uploadedfile[]" id="pimage<?=$x?>" size="45" <? if(in_array($dir,$higherr)){?>style="border:1px solid red;"<? }?> value="" />
							<? }else{?>
							<a href="<?=$mainbase?>/admin.php?p=images&amp;act=view&amp;id=<?=$itemarr['fusionId']?>&amp;what=product&amp;pid=<?=$_GET['pid']?>&amp;imgtype=<?=$type?>&amp;imgsize=<?=$size?>">Edit Image</a>
							<? }?>
							</td>
						</tr>
					<? $x++;}}else{?>
					<tr>
						<td>Images</td>
						<td><a href="<?=$mainbase?>/admin.php?p=images&amp;act=view&amp;id=<?=$itemarr['fusionId']?>&amp;what=product&amp;pid=<?=$_GET['pid']?>">Edit Images</a></td>
					</tr>
					<? }?>
					<tr>
						<td class="subhead" colspan="2"><strong>Search engine optimisation</strong></td>
					</tr>
					<tr>
						<td><label for="seo_title">File name (URL)</label> <span class="reqd">*</span></td>
						<td><input type="text" name="seo_title" id="seo_title" class="formfield" <? if(in_array("seo_title",$higherr)){?>style="border:1px solid red;"<? }?> value="<?=((count($formarr)>0)?$formarr['seo_title']:"")?>" /></td>
					</tr>
					<tr>
						<td style="vertical-align:top"><label for="metadesc">Meta description</label><br />(Leave blank for global description)</td>
						<td><textarea name="metadesc" id="metadesc" style="width:300px;height:80px" rows="4" cols="8"><?=count($formarr)>0?$formarr['metadesc']:""?></textarea></td>
					</tr>
					<tr>
						<td class="subhead" colspan="2"><strong>Status &amp; description</strong></td>
					</tr>
					<tr>
						<td><label for="iState">Visible this Dept</label></td>
						<td><input type="checkbox" name="iState" id="iState" value="1" <?=(((count($formarr)>0&&$formarr['iState']==1)||!isset($_POST))?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td><label for="on_uk_list_id">Show on UK site</label></td>
						<td><input type="checkbox" name="on_uk_list" id="on_uk_list_id" value="1" <?=((count($formarr)>0&&$formarr['on_uk_list']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td><label for="on_ie_list_id">Show on Ireland site</label></td>
						<td><input type="checkbox" name="on_ie_list" id="on_ie_list_id" value="1" <?=((count($formarr)>0&&$formarr['on_ie_list']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<? if($onitem=="product"&&$action=="add"){?>
					<tr>
						<td><label for="allowoffer">Allow pop-up offer</label><br />(If customer adds a product which this item is attached to, system will pop up a message offering this item for purchase)</td>
						<td style="vertical-align:top"><input type="checkbox" name="allowoffer" id="allowoffer" value="1" <?=(((count($formarr)>0&&$formarr['allowoffer']==1)||!isset($_POST))?"checked='checked'":"")?> /></td>
					</tr>
					<? }?>
					<tr>
						<td><label for="taxable">VAT/Tax</label></td>
						<td><input type="checkbox" name="taxable" id="taxable" value="1" <?=((count($formarr)>0&&$formarr['taxable']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td><label for="sale">On sale</label></td>
						<td><input type="checkbox" name="sale" id="sale" value="1" <?=((count($formarr)>0&&$formarr['sale']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<? if($action!="update"){?>
					<tr>
						<td><label for="poopts">Add product options after add</label></td>
						<td><input type="checkbox" name="poopts" id="poopts" value="1" <?=((count($formarr)>0&&$formarr['poopts']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<? }?>
					<tr>
						<td style="vertical-align:top"><label for="shortdesc">Short description</label></td>
						<td><textarea name="shortdesc" id="shortdesc" style="width:300px;height:80px" rows="4" cols="8"><?=count($formarr)>0?htmlentities($formarr['shortdesc'],ENT_QUOTES,"ISO-8859-1"):""?></textarea></td>
					</tr>
					<tr>
						<td colspan="2"><label for="content">Long description</label></td>
					</tr>
					<tr>
						<td colspan="2"><textarea name="content" id="content" style="height: 300px; width: 590px;color:#333333;background:#ffffff" rows="15" cols="15"><?=count($formarr)>0?$formarr['content']:""?></textarea></td>
					</tr>
					</table>
					<p style="text-align:right;width:85%"><input type="submit" name="paddsubmit" class="formbutton" value="<?=ucwords($action)?> product" /></p>
					<?
				}
				else
				{
					?>
					<input type="hidden" name="deptform" value="<?=$action?>" />
					<? if($action=="update"||$action=="duplicate"){?>
					<input type="hidden" name="old_img" value="<?=$itemarr['image1']?>" />
					<? }if($action=="update"){?>
					<input type="hidden" name="origtitle" value="<?=$itemarr['title']?>" />
					<? }?>
					<table class="details" style="width:85%">
					<tr>
						<td class="head" colspan="2"><?=$titleback." ".helplink("dept")?> Department Builder</td>
					</tr>
					<tr>
						<td class="first"><label for="title">Department title</label> <span class="reqd">*</span></td>
						<td><input type="text" name="title" id="title" class="formfield" <? if(in_array("title",$higherr)){?>style="border:1px solid red;"<? }?> value="<?=((count($formarr)>0)?$formarr['title']:"")?>" /></td>
					</tr>
					<? $x=1;foreach($images_arr['department']['images'] as $dir => $size){$type=($dir!="main")?$dir:"";?>
						<tr>
							<td><label for="image<?=$x?>"><?=ucwords($dir)?> Image</label></td>
							<td>	
							<? if($action!="update"){?>
							<input type="hidden" name="imgsize[]" value="<?=$size?>" /><input type="hidden" name="imgtype[]" value="<?=$type?>" /><input type="file" name="uploadedfile[]" id="image<?=$x?>" <? if(in_array("image".$x,$higherr)){?>style="border:1px solid red;"<? }?> value="" />
							<? }else{?>
							<a href="<?=$mainbase?>/admin.php?p=images&amp;act=view&amp;id=<?=$owner?>&amp;what=department&amp;cid=<?=$_GET['cid']?>&amp;imgtype=<?=$type?>&amp;imgsize=<?=$size?>">Edit Image</a>
							<? }?></td>
						</tr>
					<? $x++;}?>
					<tr>
						<td class="subhead" colspan="2">Status &amp; description</td>
					</tr>
					<tr>
						<td><label for="iState">On/Off</label></td>
						<td><input type="checkbox" name="iState" id="iState" value="1" <?=((count($formarr)>0&&$formarr['iState']==1)||!isset($_POST)?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td><label for="displayed">Show in side menu</label></td>
						<td><input type="checkbox" name="displayed" id="displayed" value="1" <?=((count($formarr)>0&&$formarr['displayed']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td><label for="showmenuitem">Show on home page</label></td>
						<td><input type="checkbox" name="showmenuitem" id="showmenuitem" value="1" <?=((count($formarr)>0&&$formarr['showmenuitem']==1)?"checked='checked'":"")?> /></td>
					</tr>
					<tr>
						<td colspan="2"><label for="content">Long description</label></td>
					</tr>
					<tr>
						<td colspan="2"><textarea name="content" id="content" style="height: 300px; width: 590px;color:#333333;background:#ffffff" rows="10" cols="15"><?=count($formarr)>0?$formarr['content']:""?></textarea></td>
					</tr>
					</table>
					<p style="text-align:right;width:85%"><input type="submit" name="daddsubmit" class="formbutton" value="<?=ucwords($action)?> <?=$onitem?>" /></p>
					<?
				}
				?>
				</form>
				<?
				break;
			case "delete":
				if(isset($_GET['fid'])&&$_GET['fid']!="")//fid not null unless orphan
				{
					$detailsQ=mysql_query("SELECT `iOwner_FK`,`fusionId`,categories.`title` as ctitle FROM (fusion JOIN products ON products.`prod_id`=fusion.`iSubId_FK`) LEFT JOIN categories ON categories.`cat_id`=fusion.`iOwner_FK` WHERE `fusionId`='$getescaped[fid]'");
					$details=mysql_fetch_assoc($detailsQ);
					$newname=($details['iOwner_FK']==0)?"":"&amp;name=".urlencode($details['ctitle']);
				}
				else
				{
					$newname="";
				}
				$toreports=(isset($_GET['report']))?"&amp;report=$_GET[report]".((isset($_GET['rpage']))?"&amp;rpage=$_GET[rpage]":""):"";
				$addfid=(isset($_GET['fid'])&&$_GET['fid']!=null)?"&amp;fid=$_GET[fid]":"";
				$addpcid=($onitem=="product")?"&amp;pid=$_GET[pid]":"&amp;cid=$_GET[cid]";
				?>
				<table class="details">
				<tr>
					<td class="head">Status update for <?=$_GET['name']?></td>
				</tr>
				<tr>
					<td style="text-align:center">
					<p>Do you really want to delete this <?=$onitem?>?</p>
					<? if(isset($_GET['fid'])&&$_GET['fid']!=""&&!isset($_GET['report'])&&$onitem!="department"){?><a href="<?=$self?>&amp;act=delete&amp;what=department&amp;id=<?=$details['fusionId']?>&amp;unattach=<?=$_GET['fid']?><?=$newname?><?=$toreports?>">Delete Instance</a> | <? }?><a href="<?=$self?>&amp;act=delete&amp;what=department&amp;id=<?=$owner?><?=$addfid?><?=$addpcid?>&amp;dodelete=1<?=$newname?><?=$toreports?>">Delete Everything</a> | <a href="javascript:history.back();">Cancel</a>
					</td>
				</tr>
				</table>
				<?
				break;
			default:// $action=="view"
				if($what!="product")
				{
					/*sub cats of cats*/
					$csubcQS="SELECT f.`fusionId` as fid,f.`iOwner_FK`,c.`cat_id` as cid,c.`title`,f.`iSubId_FK`,f.`iSort` as sort,f.`iState` as onoff 
					FROM fusion as f,categories as c
					WHERE f.`iSubId_FK`=c.`cat_id` AND f.`iOwner_FK`='$fusionSub' AND `vtype`='department' ORDER BY f.`iSort`";
					$csubcQ=mysql_query($csubcQS) or die("Query failed: '$csubcQS' with error:<br />'".mysql_error()."'");
					$csubcR=mysql_num_rows($csubcQ);
					?>
					<form action="<?=$mainbase?>/admin.php?<?=str_replace("&","&amp;",$_SERVER['QUERY_STRING'])?>" method="post">
						<table class="details">
							<tr>
								<td class="head" colspan="7"><div class="titles"><a href="<?=$self?>&amp;act=add&amp;what=department&amp;onitem=department&amp;id=<?=$owner?>&amp;name=<?=urlencode($name)?>&amp;nextsort=<?=$csubcR?>">Add department</a> to <?=$name?></div><? if($owner>0){?><div class="links"><a href="<?=$mainbase?>/admin.php?p=builder&act=update&what=department&onitem=department&id=<?=$fusionOwner?>&cid=<?=$fusionSub?>">Edit <?=$name?> Department</a></div><? }?></td>
							</tr>
							<tr>
								<td class="subhead">Preview</td>
								<td class="subhead">Sort</td>
								<td class="subhead" style="width:380px;">Title</td>
								<td class="subhead">On/Off</td>
								<td class="subhead">Duplicate</td>
								<td class="subhead">Edit</td>
								<td class="subhead">Delete</td>
							</tr>
							<?
							while($csubc=mysql_fetch_assoc($csubcQ))
							{
								$row=(!isset($row)||$row=="1")?"0":"1";
								?>
								<tr class="row<?=$row?>">
									<td><a href="<?=$mainbase?>/index.php?p=products&amp;cat=<?=$csubc['cid']?>">Preview</a></td>
									<td><input type="text" name="sort[<?=$csubc['fid']?>]" value="<?=$csubc['sort']?>" class="formfield" style="width:20px;" /></td>
									<td><a href="<?=$self?>&amp;act=view&amp;what=department&amp;id=<?=$csubc['fid']?>&amp;name=<?=urlencode($csubc['title'])?>"><?=$csubc['title']?></a></td>
									<td style="text-align:center"><input type="checkbox" name="onoff[<?=$csubc['fid']?>]" value="1" <?=(($csubc['onoff']==1)?"checked='checked'":"")?> /></td>
									<td><a href="<?=$self?>&amp;act=duplicate&amp;what=department&amp;onitem=department&amp;id=<?=$owner?>&amp;cid=<?=$csubc['iSubId_FK']?>&amp;nextsort=<?=$csubcR?>">Duplicate</a></td>
									<td><a href="<?=$self?>&amp;act=update&amp;what=department&amp;onitem=department&amp;id=<?=$owner?>&amp;cid=<?=$csubc['iSubId_FK']?>">Edit</a></td>
									<td><a href="<?=$self?>&amp;act=delete&amp;what=department&amp;onitem=department&amp;id=<?=$owner?>&amp;name=<?=urlencode($csubc['title'])?>&amp;fid=<?=$csubc['fid']?>&amp;cid=<?=$csubc['iSubId_FK']?>&amp;oid=<?=$owner?>">Delete</a></td>
								</tr>
								<? 
							}
							if($csubcR==0){?><tr><td colspan="7" style="text-align:center">No department exists under <strong><?=$name?></strong></td></tr><? }?>
						</table>
						<? if($csubcR>0){?><p class="submit"><input type="submit" name="csubcsubmit" class="formbutton" value="Apply Changes" /></p><? }?>
					</form>
					<?
					/*sub products of cats*/
					$csubpQS="
					SELECT p.`prod_id`,f.`fusionId` as fid,f.`iOwner_FK`,p.`title` as title,f.`iSubId_FK`,f.`iSort` as sort,f.`iState` as onoff,p.`on_uk_list`,p.`on_ie_list`,SUM(n.`nav_qty`) as qty
 FROM (((products as p JOIN fusion as f ON f.`iSubId_FK`=p.`prod_id`) LEFT JOIN fusion_options as fo ON fo.`prod_id`=p.`prod_id`) LEFT JOIN option_values as ov ON ov.`opt_id`=fo.`opt_id`) LEFT JOIN nav_stock as n ON n.`nav_skuvar`=ov.`variant_id`
 WHERE f.`iOwner_FK`='$fusionSub' AND `vtype`='product' AND `vOwnerType`='department'
 GROUP BY `prod_id` ORDER BY f.`iSort`";
					//echo $csubpQS;
					$csubpQ=mysql_query($csubpQS) or die("Query failed: '$csubpQS' with error:<br />'".mysql_error()."'");
					$csubpR=mysql_num_rows($csubpQ);
					?>
					<br />
					<form action="<?=$mainbase?>/admin.php?<?=str_replace("&","&amp;",$_SERVER['QUERY_STRING'])?>" method="post">
						<table class="details">
							<tr>
								<td class="head" colspan="9"><div class="titles"><a href="<?=$self?>&amp;act=add&amp;what=department&amp;onitem=product&amp;id=<?=$owner?>&amp;name=<?=urlencode($name)?>&amp;nextsort=<?=$csubpR?>">Add product</a> to <?=$name?></div><div class="links"><a href="<?=$self?>&amp;act=attach&amp;what=department&amp;id=<?=$owner?>&amp;name=<?=urlencode($name)?>&amp;nextsort=<?=$csubpR?>">Associate Product(s)</a></div></td>
							</tr>
							<tr>
								<td class="subhead">Preview</td>
								<td class="subhead">Sort</td>
								<td class="subhead" style="width:380px;"><div style="float:left">Title</div><div style="float:right">Stock</div></td>
								<td class="subhead" style="text-align:center">Visible in this dept</td>
								<td class="subhead" style="text-align:center">UK Site</td>
								<td class="subhead" style="text-align:center">IE Site</td>
								<td class="subhead" style="text-align:center">Duplicate</td>
								<td class="subhead" style="text-align:center">Edit</td>
								<td class="subhead" style="text-align:center">Delete</td>
							</tr>
							<?
							while($csubp=mysql_fetch_assoc($csubpQ))
							{
								$row=(!isset($row)||$row=="1")?"0":"1";
								?>
								<tr class="row<?=$row?>">
									<td><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$csubp['fid']?>">Preview</a></td>
									<td><input type="text" name="sort[<?=$csubp['fid']?>]" value="<?=$csubp['sort']?>" style="width:20px;" /></td>
									<td>
									<div style="float:left"><a href="<?=$self?>&amp;act=view&amp;what=product&amp;id=<?=$csubp['fid']?>&amp;name=<?=urlencode($csubp['title'])?>"><?=$csubp['title']?></a></div>
									<div style="float:right">(<?=$csubp['qty']?>)</div>
									</td>
									<td style="text-align:center"><input type="checkbox" name="onoff[<?=$csubp['fid']?>]" value="1" <?=($csubp['onoff']==1?"checked='checked'":"")?> /></td><td style="text-align:center"><input type="checkbox" name="on_uk_list[<?=$csubp['fid']?>]" value="1" <?=($csubp['on_uk_list']==1?"checked='checked'":"")?> /></td><td style="text-align:center"><input type="checkbox" name="on_ie_list[<?=$csubp['fid']?>]" value="1" <?=($csubp['on_ie_list']==1?"checked='checked'":"")?> /></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;act=duplicate&amp;what=department&amp;onitem=product&amp;id=<?=$owner?>&amp;pid=<?=$csubp['iSubId_FK']?>&amp;nextsort=<?=$csubpR?>">Duplicate</a></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;act=update&amp;what=department&amp;onitem=product&amp;id=<?=$owner?>&amp;pid=<?=$csubp['iSubId_FK']?>">Edit</a></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;act=delete&amp;what=department&amp;onitem=product&amp;id=<?=$owner?>&amp;name=<?=urlencode($csubp['title'])?>&amp;fid=<?=$csubp['fid']?>&amp;pid=<?=$csubp['iSubId_FK']?>">Delete</a></td>
								</tr>
								<? 
							}
							if($csubpR==0){?><tr><td colspan="9" style="text-align:center">No product exists under <strong><?=$name?></strong></td></tr><? }?>
						</table>
						<? if($csubpR>0){?><p class="submit"><input type="submit" name="csubpsubmit" class="formbutton" value="Apply Changes" /></p><? }?>
					</form>
					<?
				}else{/*list products in product*/
					$psubpQS="SELECT p.`prod_id` as prod_id,p.`on_uk_list`,p.`on_ie_list`,f.`fusionId` as fid,f.`iOwner_FK`,p.`title` as title,f.`iSubId_FK`,f.`iSort` as sort,f.`iState` as onoff, `allowoffer` 
					FROM products as p LEFT JOIN fusion as f ON (f.`iSubId_FK`=p.`prod_id`) AND f.`vType`='product' AND f.`vOwnerType`='product'
					WHERE f.`iOwner_FK`='$fusionSub' ORDER BY f.`iSort`";
					$psubpQ=mysql_query($psubpQS) or die("Query failed: '$psubpQS' with error:<br />'".mysql_error()."'");
					$psubpR=mysql_num_rows($psubpQ);
					?>
					<form action="<?=$mainbase?>/admin.php?<?=str_replace("&","&amp;",$_SERVER['QUERY_STRING'])?>" method="post">
						<table class="details">
							<tr>
								<td class="head" colspan="10"><div class="titles"><a href="<?=$self?>&amp;act=add&amp;what=product&amp;onitem=product&amp;id=<?=$owner?>&amp;name=<?=urlencode($name)?>&amp;nextsort=<?=$psubpR?>">Add product</a> to <?=$name?></div><div class="links"><a href="<?=$self?>&amp;act=attach&amp;what=product&amp;id=<?=$owner?>&amp;name=<?=urlencode($name)?>&amp;nextsort=<?=$psubpR?>">Associate Product(s)</a> | <a href="<?=$mainbase?>/admin.php?p=builder&amp;act=update&amp;what=department&amp;onitem=product&amp;id=<?=$owner?>&amp;pid=<?=$fusionSub?>&amp;name=<?=urlencode($name)?>">Edit <?=$name?></a></div></td>
							</tr>
							<tr>
								<td class="subhead">Preview</td>
								<td class="subhead">Sort</td>
								<td class="subhead" style="width:380px;">Title</td>
								<td class="subhead" style="text-align:center">Popup Offer</td>
								<td class="subhead" style="text-align:center">On/Off</td>
								<td class="subhead" style="text-align:center">UK Site</td>
								<td class="subhead" style="text-align:center">IE Site</td>
								<td class="subhead">Duplicate</td>
								<td class="subhead">Edit</td>
								<td class="subhead">Delete</td>
							</tr>
							<?
							
							while($psubp=mysql_fetch_assoc($psubpQ))
							{
								$row=(!isset($row)||$row=="1")?"0":"1";
								?>
								<tr class="row<?=$row?>">
									<td><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$psubp['fid']?>">Preview</a></td>
									<td><input type="text" name="sort[<?=$psubp['fid']?>]" value="<?=$psubp['sort']?>" style="width:20px;" /></td>
									<td><a href="<?=$self?>&amp;act=view&amp;what=product&amp;id=<?=$psubp['fid']?>&amp;pid=<?=$psubp['iSubId_FK']?>&amp;name=<?=urlencode($psubp['title'])?>">
										<?=$psubp['title']?>
										</a></td>
									<td style="text-align:center"><input type="checkbox" name="allowoffer[<?=$psubp['fid']?>]" value="1" <?=($psubp['allowoffer']==1?"checked='checked'":"")?> /></td><td style="text-align:center"><input type="checkbox" name="onoff[<?=$psubp['fid']?>]" value="1" <?=($psubp['onoff']==1?"checked='checked'":"")?> /></td>
									<td style="text-align:center"><input type="checkbox" name="on_uk_list[<?=$psubp['fid']?>]" value="1" <?=($psubp['on_uk_list']==1?"checked='checked'":"")?> /></td>
									<td style="text-align:center"><input type="checkbox" name="on_ie_list[<?=$psubp['fid']?>]" value="1" <?=($psubp['on_ie_list']==1?"checked='checked'":"")?> /></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;act=duplicate&amp;what=product&amp;onitem=product&amp;id=<?=$owner?>&amp;pid=<?=$csubp['iSubId_FK']?>&amp;nextsort=<?=$csubpR?>">Duplicate</a></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;act=update&amp;what=product&amp;onitem=product&amp;id=<?=$owner?>&amp;pid=<?=$psubp['iSubId_FK']?>">Edit</a></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;act=delete&amp;what=product&amp;onitem=product&amp;id=<?=$owner?>&amp;name=<?=urlencode($psubp['title'])?>&amp;fid=<?=$psubp['fid']?>&amp;pid=<?=$psubp['iSubId_FK']?>">Delete</a></td>
								</tr>
								<? 
							}
							if($psubpR==0){?><tr><td colspan="10" style="text-align:center">No product exists under <strong><?=$name?></strong></td></tr><? }?>
						</table>
						<? if($psubpR>0){?><p class="submit"><input type="submit" name="psubpsubmit" class="formbutton" value="Apply Changes" /></p><? }?>
					</form>
					<br />
					<table class="details">
						<tr>
							<td class="head" colspan="3">Currently attached to:</td>
						</tr>
						<tr>
							<td class="subhead" style="width:480px">Item title</td>
							<td class="subhead" style="text-align:center">Type</td>
							<td class="subhead" style="text-align:center">Remove</td>
						</tr>
						<? 
							$attachedQ=mysql_query("SELECT p.`title` as ptitle,c.`title` as ctitle,f.`iSubId_FK`,f.`vtype`,f.`fusionId` as fid,`vOwnerType`,`iOwner_FK` FROM (fusion as f LEFT JOIN products as p ON f.`iOwner_FK`=p.`prod_id` AND f.`vOwnerType`='product') LEFT JOIN categories as c ON c.`cat_id`=f.`iOwner_FK` AND f.`vOwnerType`='department' WHERE f.`iSubId_FK`='$fusionSub' AND f.`vtype`='product' ORDER BY `vOwnerType`");
							$attchednum=mysql_num_rows($attachedQ);
							while($attached=mysql_fetch_assoc($attachedQ))
							{
								$row=(!isset($row)||$row=="1")?"0":"1";
								if($attached['vOwnerType']=="department")
								{
									$getfid=mysql_query("SELECT `fusionId` FROM fusion WHERE `iSubId_FK`='$attached[iOwner_FK]' AND `vtype`='department'");
									list($deptfid)=mysql_fetch_row($getfid);
								}
								else if($attached['vOwnerType']=="product")
								{
									$getfid=mysql_query("SELECT iOwner_FK,title,fusionId FROM fusion as f LEFT JOIN categories as c ON f.iOwner_FK=c.cat_id WHERE iSubId_FK='$attached[iOwner_FK]' AND vOwnerType='department'");
									list($cat_id,$ctitle,$fuid)=mysql_fetch_row($getfid);
									$cattitle=$cat_id!=0?$ctitle:"home";
								}
								$ownerfid=($attached['vOwnerType']=="department")?(($attached['iOwner_FK']==0)?0:$deptfid):$fuid;
								$title=($attached['vOwnerType']=="product")?$attached['ptitle']:(($attached['iOwner_FK']==0)?"Home Page":$attached['ctitle']);
								?>
								<tr class="row<?=$row?>">
									<td><?=strlen($cattitle)>0?"<a href='".$mainbase."/admin.php?p=builder&amp;act=view&amp;what=department&amp;id=".$cat_id."&amp;name=".urlencode($cattitle)."'>".ucwords($cattitle)."</a>"." / ":""?> <a href="<?=$self?>&amp;act=view&amp;what=<?=$attached['vOwnerType']?>&amp;id=<?=$ownerfid?>&amp;name=<?=urlencode($title)?>"><?=$title?></a></td>
									<td style="text-align:center"><?=ucwords($attached['vOwnerType'])?></td>
									<td style="text-align:center"><a href="<?=$self?>&amp;unattach=<?=$attached['fid']?>&amp;<?=$actwhatid?>&amp;name=<?=urlencode($name)?>">Remove</a></td>
								</tr>
								<?
							}
							if($attchednum==0){?>
						<tr>
							<td colspan="3" style="text-align:center"><strong><?=$name?></strong> does not exists under any products or departments</td>
						</tr>
						<? }?>
					</table>
					<?
			}
			break;
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>
<? unset($_SESSION['error']); ?>