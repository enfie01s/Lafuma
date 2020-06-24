<? 
if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security
$parents_num=0;
//$the_array set in index.php

/* PRODUCT LIST */
if(isset($_GET['cat']))
{
	$order=isset($_GET['sort'])?$_GET['sort']:"p.`iSort`";
	$ascdesc=isset($_GET['ascdesc'])?$_GET['ascdesc']:"ASC";
	?><h2 id="pagetitle"><?=$the_array['title']?></h2><?
	if(1){list_categories("SELECT * FROM fusion,categories WHERE `iOwner_FK`='".mysql_real_escape_string($_GET['cat'])."' AND `vOwnerType`='department' AND `vType`='department' AND fusion.`iSubId_FK`=categories.`cat_id` AND `iState`='1' ORDER BY `iSort`");}
	?><div id="catcontent"><?
	echo $the_array['content'];
	?></div><?
	mysql_query("CREATE OR REPLACE VIEW prods AS SELECT `fusionId`,`iOwner_FK`,p.`prod_id`,p.`title`,p.`list_price".PRICECUR."` as list_price,p.`sale`,p.`shortdesc`,p.`price".PRICECUR."` as price,`seo_title`,f.`iSort` FROM ((fusion as f JOIN products as p ON f.`iSubId_FK`=p.`prod_id` AND `iOwner_FK`='$_GET[cat]' AND `vOwnerType`='department' AND `vType`='product' AND `iState`='1' AND `".WHICHLIST."`='1') LEFT JOIN productkits as pk ON pk.`kprod_id`=p.`prod_id`) JOIN (((fusion_options as fo JOIN product_options as po ON po.`opt_id`=fo.`opt_id`) JOIN option_values as ov ON ov.`opt_id`=fo.`opt_id`) JOIN nav_stock as n ON ov.`variant_id`=n.`nav_skuvar` AND `nav_qty`>0) ON p.`prod_id`=fo.`prod_id` GROUP BY p.`prod_id` ORDER BY f.`iSort`");
	list_products("SELECT `fusionId`,`iOwner_FK`,`prod_id`,p.`title`,`list_price`,`sale`,`shortdesc`,`price`,`seo_title`,AVG(`rank`) as avgrank,count(`rank`) as totalrevs FROM prods as p LEFT JOIN customerreviews as cr ON p.`prod_id`=cr.`item_id` GROUP BY p.`prod_id` ORDER BY $order $ascdesc","","","",1);
}
/* /PRODUCT LIST */
/* INDIVIDUAL PRODUCT */
else if(isset($_GET['pid']))
{
	?><h2 id="pagetitle"><?=$title?></h2><?
		//debug($allowlist);
		//print_r($allowlist);
	/* STOCK CHECK */	
	if(checkprodstock($_GET['pid'],$the_array['kit'])>0&&($the_array['iState']==1||(count(array_intersect($allowlist,$cart_ids))>0&&$the_array['allowoffer']==1)))
	{
		$medsrc=file_exists($images_arr['product']['path']."/medium/".$the_array['prod_id']."-default.jpg")?"src='".$images_arr['product']['path']."/medium/".$the_array['prod_id']."-default.jpg'":"src='".$images_arr['product']['path']."/medium/imgmissing.gif' style='width:146px;height:165px;'";
		
		$lrgsrc=file_exists($images_arr['product']['path']."/large/".$the_array['prod_id']."-default.jpg")?$images_arr['product']['path']."/large/".$the_array['prod_id']."-default.jpg":$images_arr['product']['path']."/large/imgmissing.gif";
		?>
		<div id="mainimg">
		<a rel="thumbnail" href="<?=$lrgsrc?>" id="thumbnail"><img <?=$medsrc?> alt="<?=$the_array['title']?>" id="thumbnail_image" /></a>
		<p><a rel="thumbnail" href="<?=$lrgsrc?>" id="thumbnail_large">View Larger Image</a></p>
		<p>
		Average rating: <?=stars($the_array['avgrank'])?> 
		<a href="#reviews">(<?=$the_array['countrevs']?> review<?=$the_array['countrevs']>1||$the_array['countrevs']<1?"s":""?>)</a>
		</p>
		</div>
		<div id="choices">
			<form id="productoptions" name="productoptions" method="post" action="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$pid?>">
			<input type="hidden" name="returnpage" value="<?=$mainbase?>/index.php?<?=urlencode($_SERVER['QUERY_STRING'])?>" />
			<input type="hidden" name="prod_id" value="<?=$the_array['prod_id']?>" />
			<input type="hidden" name="sku" value="<?=$the_array['sku']?>" />
			<input type="hidden" name="ispack" value="<?=$the_array['kit']?>" />
			<input type="hidden" name="identifier" value="add_to_cart" />
			<input type="hidden" name="price" value="<?=$the_array['price']?>" />
			<input type="hidden" name="allowlist" value="<?=implode(",",$allowlist)?>" />
			<input type="hidden" name="title" value="<?=$the_array['title']?>" />
			<input type="hidden" name="exclude_discount" value="<?=$the_array['exclude_discount']?>" />
			<? if($the_array['sale']==1){?><p class="highlight">On Sale!</p><? }?>
			<p class="price">Price: <?=$currarr[$domainext][2]?><?=addvat($the_array['price'])?></p>
			<? if($the_array['list_price']!=0){?><p><span style="text-decoration:line-through">RRP: <?=$currarr[$domainext][2]?><?=number_format($the_array['list_price'],2)?></span></p><? }
			
			$kitq=mysql_query("SELECT * FROM productkits,products WHERE `kprod_id`='$the_array[prod_id]' AND productkits.`iProdId_FK`=products.`prod_id` AND `".WHICHLIST."`='1' ORDER BY `kit_sort`");
			$kitsfound=mysql_num_rows($kitq);
			if($the_array['kit']==1)
			{
				if($kitsfound>0)
				{
					?>
					<h3>Items in Package</h3>
					<?
					while($kit=mysql_fetch_assoc($kitq))
					{
						$fusionidq=mysql_query("SELECT fusionId FROM fusion WHERE `iSubId_FK`='$kit[iProdId_FK]' AND `vType`='product' AND `iState`='1'");
						list($fusionId)=mysql_fetch_row($fusionidq);
						?>
						<input type="hidden" name="item_qty" value="<?=$kit['item_qty']?>" />
						<a href='<?=$mainbase?>/index.php?p=products&amp;pid=<?=$fusionId?>'><?=$kit['title']?></a> (<?=$kit['item_qty']?>)<br />
						<? colourChooser($kit['prod_id'],$kit['item_qty'],$kitsfound,0);
						?><br /><?
					}
				}
			}
			else
			{
				$kitskus=array();
				if($kitsfound>1)
				{
					while($hkits=mysql_fetch_assoc($kitq))
					{	
						if($hkits['prod_id']!=$the_array['prod_id'])
						{	
							$sq=mysql_query("SELECT `nav_skuvar` FROM products as p,nav_stock as n WHERE p.`sku`=n.`nav_sku` AND p.`prod_id`='$hkits[prod_id]' AND `nav_qty`>'0' AND `".WHICHLIST."` = '1' ORDER BY `nav_skuvar` ASC");
							list($prodsku)=mysql_fetch_row($sq);
							?><input type="hidden" name="skuvariant[<?=$hkits['prod_id']?>]" value="<?=$prodsku?>-qty-<?=$hkits['item_qty']?>" /><? 
						}
						array_push($kitskus,$hkits['prod_id']);
					}
				}
				if(in_array($the_array['prod_id'],$kitskus)||$kitsfound<2)
				{
					if($kitsfound>0){
						$kit=mysql_fetch_assoc($kitq);
						$kit_qty=$kit['item_qty'];
						$kit_item=$kit['iProdId_FK'];
					}else{$kit_qty=1;$kit_item=$the_array['prod_id'];}
					colourChooser($kit_item,$kit_qty,1,0);
				}
			}
			?>
			
			<br /><br />
           
		
			<div id="addbasket">QTY <input type="text" name="quantity" style="width:20px;" class="formfield" value="1" /> <input type="submit" name="submit" value="Add to Basket" class="formbutton" /><br />
			
            <div class="clear"></div><?
			if($the_array['exclude_discount']==1){?><p style="margin-top:5px"><dfn style="font-size:90%;font-style:italic;color:#CD071E;line-height:100%">Discount exempt (this product is already on special offer, therefore further discounts will not apply to this product).</dfn></p><? }?></div>
			</form>			
			<? if(isset($_SESSION['cartupdate']))
			{
				?>
				<div style="width:125px;float:left;margin-left:5px;color:red"><?=$_SESSION['cartupdate'];?></div>
				<div class="clear"></div>
				<? 
				if(isset($_SESSION['offerprod'])&&$_SESSION['offerprod']==$the_array['prod_id'])
				{
					list_products("SELECT * FROM fusion as f JOIN products as p ON f.`iSubId_FK`=p.`prod_id` WHERE `iOwner_FK`='".$the_array['prod_id']."' AND prod_id NOT IN('".implode("','",$cart_ids)."') AND vtype='product' AND vOwnerType='product' AND allowoffer=1 AND ".WHICHLIST."=1 ORDER BY f.iSort","","We have found these additional suggestions for you.","offerprods",1);
				}
				if($_SESSION['pageloads']>1)
				{
					unset($_SESSION['cartupdate']);
					unset($_SESSION['offerprod']);
					$_SESSION['pageloads']=0;
				}
			}
			$thumbcols=$deviceType=="phone"?4:5;
			list_products("SELECT `fusionId`,`iOwner_FK`,p.`prod_id`,p.`title`,p.`list_price".PRICECUR."` as list_price,p.`sale`,p.`shortdesc`,p.`price".PRICECUR."` as price,p.`img_filename`,SUM(`nav_qty`),FLOOR(MIN(`nav_qty`/`item_qty`)) as pack,`seo_title` FROM fusion as f,products as p LEFT JOIN productkits as pk ON pk.`kprod_id`=p.`prod_id`,`nav_stock` as n,`option_values` as ov,`fusion_options` as fo,`product_options` as po WHERE f.`iSubId_FK`=p.`prod_id` AND p.`prod_id`=fo.`prod_id` AND po.`opt_id`=fo.`opt_id` AND ov.`opt_id`=fo.`opt_id` AND ov.`variant_id`=n.`nav_skuvar` AND `iOwner_FK`='$the_array[prod_id]' AND `nav_qty`>0 AND `vOwnerType`='product' AND `vType`='product' AND `iState`='1' AND `".WHICHLIST."`='1' GROUP BY p.`prod_id` ORDER BY f.`iSort`","","Suggested Products","prodthumbs",$thumbcols);?>
		</div>
		<div id="details"><h2>Product Details</h2><?=str_replace(array("&gt;","&lt;","&amp;nbsp;"),array(">","<"," "),htmlentities($the_array['content'],ENT_QUOTES,"UTF-8"))?></div>
		<? 
		$moreimgs=glob($images_arr['product']['path']."/small/".$the_array['prod_id']."-*.jpg");
		if(count($moreimgs)>0||count($extraimg)>1)
		{			
			?>
			<div id="alt_images">
			<? 
		}
		if(count($extraimg)>1)
		{
			$extraimgcount=0;
			foreach($extraimg as $image)
			{
				if(file_exists($images_arr['product']['path']."/small/".$image['image'])){
				if($extraimgcount==0){$extraimgcount=1;?><h2>Additional Photos</h2>(Click to enlarge)<br /><? }
				?><div style="border:1px solid #ccc;margin-right:5px;margin-bottom:5px;float:left;text-align:center"><a rel="thumbnail" href="<?=$images_arr['product']['path']."/large/".$image['image']?>" style="display:inline-block;padding:4px 4px 0;text-decoration:none;"><img src="<?=$images_arr['product']['path']."/small/".$image['image']?>" alt="<?=$image['name']?>" /><br /><?=$image['name']?></a></div><?
				}
			}			
		}	
		//print_r($alreadyshown);
		foreach($moreimgs as $image)
		{
			$basenm=basename($image);
			if(substr_count($basenm,"lfm")==0&&$basenm!=$the_array['prod_id']."-default.jpg")
			{
				$imgtitle=str_replace(array($the_array['prod_id']."-","_",".jpg"),array(""," ",""),$basenm);
				if($extraimgcount==0){$extraimgcount=1;?><h2>Additional Photos</h2>(Click to enlarge)<br /><? }
				?><div style="border:1px solid #ccc;margin-right:5px;margin-bottom:5px;float:left;text-align:center"><a rel="thumbnail" href="<?=$images_arr['product']['path']."/large/".basename($image)?>" style="display:inline-block;padding:4px 4px 0;text-decoration:none;"><img src="<?=$image?>" alt="<?=$imgtitle?>" /><br /><?=$imgtitle?></a></div><?
			}
		}
		if(count($moreimgs)>0||count($extraimg)>1)
		{
			?>
			<div class="clear"></div>
			</div>
			<? 
		}
		?>
		
		<h2>Customer Reviews</h2>
		<div id="reviews">
			<ul>
				<li>
				<? 
				$rev_q=mysql_query("SELECT * FROM customerreviews as cr LEFT JOIN customers as c ON cr.`cust_id`=c.`cust_id` WHERE cr.`item_id`='$the_array[prod_id]' AND cr.`state`='1' AND `owner_id`=0");
				$rev_num=mysql_num_rows($rev_q);
				$link=$_SESSION['loggedin']!=0?"products&amp;pid=".$pid."&amp;reviewform=1#review":"customer_login&amp;to_p=products&amp;to_pid=".$pid."&amp;to_reviewform=1&amp;hash=review";
				?>
				<a href="<?=$mainbase?>/index.php?p=<?=$link?>"><?=$rev_num==0?"Be the first to review this product":"Write a review"?></a>
				</li>
			</ul>
			<? if(isset($_GET['reviewform'])&&$_SESSION['loggedin']!=0){?>
			<a name="review"></a>
			<form class="global-form" action="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$pid?>&amp;reviewform=1#review" method="post">
				<input type="hidden" name="shouldbeempty" value="" />
				<input type="hidden" name="item_id" value="<?=$the_array['prod_id']?>" />
				<table style="width:340px;">
					<tr>
						<td class="first"><label for="title">Review Title</label></td>
						<td><input class="formfield" type="text" name="title" id="title" onfocus="this.select()" value="review title" maxlength="40" <?=highlighterrors($higherr,"title")?> /></td>
					</tr>
					<tr>
						<td><label for="rank">Ranking</label></td>
						<td>
							<select class="formfield" id="rank" name="rank">
								<option value="5" selected="selected">Excellent</option>
								<option value="4">Good</option>
								<option value="3">Fair</option>
								<option value="2">Poor</option>
								<option value="1">Horrible</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top"><label for="comment">Your Review</label></td>
						<td><textarea class="formfield" id="comment" name="comment" rows="5" cols="30" onfocus="this.select()" <?=highlighterrors($higherr,"comment")?>>Your review</textarea></td>
					</tr>
					<tr>
						<td><label for="display">Show my Name</label></td>
						<td><input type="hidden" name="display" value="0" /><input type="checkbox" class="formradio" id="display" name="display" value="1" /></td>
					</tr>
				</table>
				<? if(count($higherr)>0){?><dfn style="color:red;display:block">Please fill in all fields</dfn><? }?>
				<input type="submit" class="formbutton" value="Add Review" />
			</form>
			<? } 
			
			if(!isset($_GET['reviewform']))
			{
				$revcount=1;
				while($rev=mysql_fetch_assoc($rev_q))
				{
					?>
					<p>
					<span class="orangebold"><?=ucfirst($rev['title'])?></span><br />
					<strong>Rating:</strong> <?=stars($rev['rank'])?><br />
					<? if($rev['display']==1){?><strong>Submitted By:</strong> <?=$rev['firstname']." ".$rev['lastname']?><br /><? }?>
					<?=ucfirst($rev['comment'])?><br />
					<strong>Submitted On:</strong> <?=date("d/m/Y",$rev['date_created'])?><br />
					<strong>Review:</strong> <?=$revcount++?> of <?=$rev_num?>
					<?
					$revr_q=mysql_query("SELECT * FROM customerreviews as cr WHERE cr.`item_id`='$the_array[prod_id]' AND cr.`state`='1' AND `owner_id`='".$rev['cust_rev_id']."'");
					if(mysql_num_rows($revr_q)>0)
					{
						$revr=mysql_fetch_assoc($revr_q);
						?><br /><span style="display:inline-block;background: none repeat scroll 0 0 #eee;border: 1px solid #ddd;border-left: 6px solid #ddd;color: #777777;padding: 3px 5px;width:100%">
						<strong>Lafuma's Response:</strong> <?=$revr['comment']?>
						</span><?
					}
					?>
					</p>
					<? 
				}
			}?>
		</div>
		<script type="text/javascript">
		/* <![CDATA[ */
		var oldimage;
		var oldhref;
		function swapimage(id,thenewimg)
		{
			anchorobj=document.getElementById(id);
			newimg=(thenewimg.indexOf("-")+1 >= thenewimg.length)?thenewimg+"default.jpg":thenewimg;
			if(newimg.length>3){
				imageobj=document.getElementById(id+"_image");
				anchorlargeobj=document.getElementById(id+"_large");
				oldimage=imageobj.src;
				oldhref=anchorobj.href;
				imgPath=oldimage.slice(0,oldimage.lastIndexOf("/")+1);
				anchorPath=oldhref.slice(0,oldhref.lastIndexOf("/")+1);
				qtyChar=newimg.lastIndexOf("-qty");
				newimage=qtyChar==-1?newimg:newimg.slice(0,qtyChar)+".jpg";
				//set objects
				imgFile=newimage.toLowerCase();
				if(in_array(imgFile,medimgs)){
					imageobj.src=imgPath+imgFile;
					anchorobj.href=anchorPath+imgFile;
					anchorlargeobj.href=anchorobj.href;
				}
			}
		}
		function returnimage(id)
		{
			imageobj=document.getElementById(id+"_image").src=oldimage;
			anchorobj=document.getElementById(id).href=oldhref;
			anchorlargeobj=document.getElementById(id+"_large").href=oldhref;
		}
		function in_array(needle, haystack) {
			for(var i in haystack) {
					if(haystack[i] == needle) return true;
			}
			return false;
		}
		/* ]]> */
		</script>
		<? 
	}
	else
	{
		echo "Sorry, this product is currently unavailable.";
	}
}
/* /INDIVIDUAL PRODUCT */
?>