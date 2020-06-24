<?
if(basename($_SERVER['PHP_SELF'])!="admin.php"){header("Location: ../admin.php");}//direct access security 

$what=(isset($_GET['what']))?$_GET['what']:"";
$action=(isset($_GET['act']))?$_GET['act']:"";

if($what!="option_values")
{
	$owner=(isset($_GET['id']))?$getescaped['id']:0;
	$onitem=(isset($_GET['onitem']))?$getescaped['onitem']:"";
	$actwhatid="act=$action&amp;what=$what&amp;id=$owner";
	$message=(isset($message))?$message:"";
	$name=(!isset($_GET['name']))?"Home Page":$_GET['name'];
	$imgname=($_GET['imgtype']=="small"||$what=="department")?"A":(($_GET['imgtype']=="medium")?"B":"C");
	$crumbQ=mysql_query("SELECT `iOwner_FK`,`iSubId_FK` FROM fusion WHERE `fusionId`='$owner'");
	list($fusionOwner,$fusionSub)=mysql_fetch_row($crumbQ);
	$fusionOwner=($fusionOwner==null)?0:$fusionOwner;
	
	$cpid=(isset($_GET['cid']))?$_GET['cid']:$_GET['pid'];
	$cp=(isset($_GET['cid']))?"cid":"pid";
	$path="content/img/".(($what=="department")?"categories":"products");
	$formaction="$self&amp;act=update&amp;id=$owner&amp;what=$what&amp;$cp=$cpid".($what=="department"?"&amp;imgtype=$_GET[imgtype]&amp;imgsize=$_GET[imgsize]":"");
}
else
{
	$cpid=(isset($_GET['optval_id']))?$_GET['optval_id']:"";
	$cp=(isset($_GET['optval_id']))?"optval_id":"";
	$path="content/img/products/options";
	$formaction="$self&amp;act=update&amp;what=$what&amp;imgsize=$_GET[imgsize]&amp;optval_id=$_GET[optval_id]";
}
if($what=="product")
{
	$queryS="SELECT p.`prod_id` as prod_id,p.`img_filename` as img_filename,p.`title` as title,`sku`,p.`date_created` as date_created,`item_desc`, `variant_id` FROM products as p,fusion_options as fo,option_values as ov,product_options as po,nav_stock WHERE p.`prod_id`=fo.`prod_id` AND po.`opt_id`=fo.`opt_id` AND ov.`opt_id`=fo.`opt_id` AND ov.`variant_id`=nav_stock.`nav_skuvar` AND p.`prod_id`='$_GET[pid]' ORDER BY ov.`vsort`";
}else if($what=="department")
{
	$queryS="SELECT `cat_id`,`image1`,`title` FROM categories WHERE `cat_id`='$_GET[cid]'";
}else if($what=="option_values")
{
	$queryS="SELECT `opt_name` as title,po.`opt_id`,`img_filename`,`item_desc`,`date_created`,`variant_id` FROM product_options as po,option_values as ov WHERE po.`opt_id`=ov.`opt_id` AND ov.`optval_id`='$_GET[optval_id]'";
}

$query=mysql_query($queryS) or die("Error");
$resultnum=mysql_num_rows($query);
$result=mysql_fetch_assoc($query);

if($what=="option_values")
{
	$imgname=$result['item_desc']." swatch";
	$nolfm=str_replace(array("BAG","LFM"),"",$result['item_desc']);
	$removed=preg_replace("/[^A-Za-z.]/i","",$nolfm);
	$newname=(strlen($removed)<1)?"0":$result['opt_id']."_".$result['variant_id']."_".strtolower($removed).".jpg";
}
else
{
	$newname=strtolower(($what=="department"?str_replace(" ","_",$result['title']):$result['date_created'].$result['sku']).".jpg");
}
$cpimg=(isset($_GET['cid']))?"image1":"img_filename";
?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <a href="<?=$mainbase?>/admin.php?p=<? if($what!="option_values"){?>builder">Shop Builder</a> <?=getcrumbs($fusionOwner)?>  &#187; <a href="<?=$mainbase?>/admin.php?p=builder&amp;act=view&amp;what=<?=$what?>&amp;id=<?=$owner?>&amp;name=<?=urlencode($result['title'])?>"><? }else{?>product_options">Product Options</a> &#187; <a href="<?=$mainbase?>/admin.php?p=product_options&amp;act=edit&amp;opt_id=<?=$result['opt_id']?>">
<? }?>

<?=$result['title']?></a></div>

<div id="main">
	<h2 id="pagetitle">Image Manager</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? }?>
		<!-- CONTENT -->
		<? 
		/*if($what=="product")
		{
			$x=0;
			foreach($images_arr[$what]['images'] as $dir => $size)
			{
				if($x>0){?> | <? }?>
				<a href="<?=$self?>&amp;act=view&amp;id=<?=$owner?>&amp;what=<?=$what?>&amp;<?=$cp?>=<?=$cpid?>&amp;imgtype=<?=$dir?>&amp;imgsize=<?=$size?>"><?=ucwords($dir)?> Image</a>
				<? 
				$x++;
			}
			?>
			<br />
			<?
		}*/
		if($resultnum<1){?><dfn>Please ensure you have attached the option to the product <a href="admin.php?p=builder&amp;act=attach_opts&amp;id=<?=$owner?>&amp;what=product&amp;pid=<?=$cpid?>&amp;name=<?=urlencode($result['title'])?>">HERE</a></dfn><? }
		?>
		<form enctype="multipart/form-data" action="<?=$formaction?>" method="post">
		<table class="details">
		<tr>
			<td colspan="2" class="head"><div class="titles"><?=$result['title']?><?  if($what!="product"){?>: Image <?=$imgname?><? }?> <?=$message?></div><div class="links">(Tick images to delete)</div></td>
		</tr>
		<? if($what!="product"){?>
		<tr>
			<td style="vertical-align:top"><label for="new">Choose a new image</label></td>
			<td>
			
			<? if($what!="option_values"){?>
			<input type="hidden" name="imgtype[]" value="<?=$_GET['imgtype']?>" />
			<? }?>
			<input type="hidden" name="old_img" value="<?=$result[$cpimg]?>" />
			<input type="hidden" name="filename" value="<?=$newname?>" />
			<input type="hidden" name="imgsize[]" value="<?=$_GET['imgsize']?>" />
			<input type="file" name="uploadedfile[]" id="new" />
			<p><input type="submit" name="<?=$imgname?>" class="formbutton" value="Upload Image" /></p>
			
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top">Current image</td>
			<td>
				<div><img src="<?=$path.(($what=="department")?"/$result[image1]":"/$result[img_filename]")?>" alt="" /></div>
				<p><a href="<?=$self?>&amp;act=delete&amp;id=<?=$owner?>&amp;what=<?=$what?>&amp;<?=$cp?>=<?=$cpid?>&amp;imgtype=<?=$_GET['imgtype']?>&amp;imgsize=<?=$_GET['imgsize']?>&amp;old_img=<?=$result[$cpimg]?>" class="formbutton" style="padding:2px 5px;">Delete Image</a></p>
				
			</td>
		</tr>
		<? }else{?>
			<tr>
				<td style="vertical-align:middle">
				Upload image set for option:
				<select name="filename_alt">				
				<?
				@mysql_data_seek($query,0);
				$xx=0;
				while($result=mysql_fetch_assoc($query))
				{
					extract($result);
					if($xx==0){?><option value="<?=$prod_id."-default.jpg"?>">Default</option><? }
					$imgname=$prod_id."-".strtolower($variant_id);
					?><option value="<?=$imgname.".jpg"?>"><?=$item_desc?></option><?
					$xx++;
				}
				?>
				</select><br /><br /><br />&nbsp;
				</td>
				<td style="text-align:right">
				<? krsort($images_arr["product"]["images"]);
				foreach($images_arr["product"]["images"] as $type => $size){
						$sizebits=explode("x",$size);
						$dimensions="W:".$sizebits[0]."px, H:".$sizebits[1]."px";
						?>
						<strong><?=ucwords($type)?></strong> (<?=$dimensions?>):
						<input type="hidden" name="imgtype[<?=$type?>]" value="<?=$type?>" /><!-- was $item_desc_$type -->
						<input type="hidden" name="imgsize[<?=$type?>]" value="<?=$size?>" />
						<input type="file" name="uploadedfile[<?=$type?>]" id="<?=$type?>" /><br />
				<? }?>
				<p class="submit"><input type="submit" name="<?=$imgname?>" class="formbutton" value="Update/Delete Images" /></p>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top">Default image:</td>
				<td style="text-align:right"><input type="hidden" name="manyprodimages" value="1" />
					<? 
					ksort($images_arr["product"]["images"]);
					foreach($images_arr["product"]["images"] as $type => $size)
					{
						$typepath="./content/img/products/".$type."/";
						$imgname=$prod_id."-default";
						$fname=$typepath.$imgname.".jpg";
						$item_desc="default";
						?>
						<div style="float:right;border:1px solid #ccc;padding:4px;text-align:center;margin-left:4px;width:110px;background:#FFF">
							<? if(!file_exists($fname)){?><p style='height:47px;padding-top:20px;color:#777'>No Image Found<br />Use form above</p><? }else{?><img src="<?=$path."/".$type."/".$imgname.".jpg"?>" alt="" <? if($type!="small"){?>style="height:98px"<? }?> /><? }?><br />
							<?=ucwords($type)?><? if(file_exists($typepath.$imgname.".jpg")){?> <a href="<?=$typepath.$imgname.".jpg"?>" target="_blank">[View]</a> 
							<input type="hidden" name="filename[<?=$item_desc?>]" value="<?=$imgname.".jpg"?>" />
							<input type="hidden" name="imgtype[<?=$item_desc?>_<?=$type?>]" value="<?=$type?>" />
							<input type="hidden" name="imgdel[<?=$item_desc?>_<?=$type?>]" value="0" />
							<input type="checkbox" name="imgdel[<?=$item_desc?>_<?=$type?>]" value="1" /><? }?>
						</div>
						<? 
					}?>
					<div class="clear"></div>
				</td>
			</tr>
			<? 
			@mysql_data_seek($query,0);
			while($result=mysql_fetch_assoc($query))
			{
				extract($result);
				$imgname=$prod_id."-".strtolower($variant_id);
				?>
				<tr>
					<td style="vertical-align:top"><?=$item_desc?>:</td>
					<td style="text-align:right">
					
					<? 
					ksort($images_arr["product"]["images"]);
					foreach($images_arr["product"]["images"] as $type => $size)
					{
						$typepath="./content/img/products/".$type."/";
						$fname=$typepath.$imgname.".jpg";
						?>
						<div style="float:right;border:1px solid #ccc;padding:4px;text-align:center;margin-left:4px;width:110px;background:#FFF">
							<? if(!file_exists($fname)){?><p style='height:47px;padding-top:20px;color:#777'>No Image Found<br />Use form above</p><? }else{?><img src="<?=$fname?>" alt="" <? if($type!="small"){?>style="height:98px"<? }?> /><? }?><br />
							<?=ucwords($type)?><? if(file_exists($fname)){?> <a href="<?=$fname?>" target="_blank">[View]</a> 
							<input type="hidden" name="filename[<?=$item_desc?>]" value="<?=$imgname.".jpg"?>" />
							<input type="hidden" name="imgtype[<?=$item_desc?>_<?=$type?>]" value="<?=$type?>" />
							<input type="hidden" name="imgdel[<?=$item_desc?>_<?=$type?>]" value="0" />
							<input type="checkbox" name="imgdel[<?=$item_desc?>_<?=$type?>]" value="1" /><? }?>
						</div>
						<? 
					}?>
					<div class="clear"></div>
					</td>
				</tr>
				<?
			}
		}?>		
		</table>
		</form>
		<!-- /CONTENT -->
	</div>
</div>
<? unset($_SESSION['error']);?>