<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security ?>
<h2 id="pagetitle">Site Map</h2>
<p>Please navigate your way round the site map by clicking on the various departments.</p>
<p><a href="<?=$mainbase?>/index.php?p=sitemap">Refresh list &#62;</a></p>
<ul id="sitemap">
	<li>
		<h3>Departments</h3>
		<ul>
			<li><a href="<?=$mainbase?>">Home Page</a></li>
			<? 
			$cats_query=mysql_query("SELECT * FROM fusion,categories WHERE fusion.`iSubId_FK`=categories.`cat_id` AND `iOwner_FK`='0' AND `iState`='1' AND `vType`='department' AND `displayed`='1' ORDER BY `iSort`")or die(mysql_error());
			while($cat=mysql_fetch_assoc($cats_query))
			{
				$subcats_query=mysql_query("SELECT * FROM fusion,categories WHERE fusion.`iSubId_FK`=categories.`cat_id` AND `iState`='1' AND `vType`='department' AND `displayed`='1' AND fusion.`iOwner_FK`='$cat[cat_id]' ORDER BY `iSort`")or die(mysql_error());$subcats_num=mysql_num_rows($subcats_query);
				$subprods_query=mysql_query("SELECT * FROM (fusion as f JOIN products as p ON f.`iSubId_FK`=p.`prod_id`) JOIN (((fusion_options as fo JOIN product_options as po ON po.`opt_id`=fo.`opt_id`) JOIN option_values as ov ON ov.`opt_id`=fo.`opt_id`) JOIN nav_stock as n ON ov.`variant_id`=n.`nav_skuvar` AND `nav_qty`>'0') ON p.`prod_id`=fo.`prod_id` WHERE `iState`='1' AND `vType`='product' AND f.`iOwner_FK`='$cat[cat_id]' GROUP BY p.`prod_id` ORDER BY `iSort`")or die(mysql_error());$subprods_num=mysql_num_rows($subprods_query);
				?>
				<li>
					<a href="<?=$mainbase?>/index.php?p=sitemap&amp;cat=<?=$cat['cat_id']?>&amp;catname=<?=strtolower(str_replace(" ","_",$cat['title']))?>"><span><?=$cat['title']?></span></a>
					<? if($subcats_num>0&&isset($_GET['cat'])&&$_GET['cat']==$cat['cat_id']){?>
					<ul>
						<li><h3>Sub Departments</h3>
							<ul>
								<? while($subcat=mysql_fetch_assoc($subcats_query)){?>
								<li><a href="<?=$mainbase?>/index.php?p=products&amp;cat=<?=$subcat['cat_id']?>&amp;catname=<?=strtolower(str_replace(" ","_",$subcat['title']))?>"><span><?=$subcat['title']?></span></a></li>
								<? }?>
							</ul>
						</li>
					</ul>
					<? }?>
					<? if($subprods_num>0&&isset($_GET['cat'])&&$_GET['cat']==$cat['cat_id']){?>
					<ul>
						<li><h3>Products</h3>
							<ul>
								<? while($subprod=mysql_fetch_assoc($subprods_query)){?>
								<li><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$subprod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",$subprod['seo_title'])?>"><span><?=$subprod['title']?></span></a></li>
								<? }?>
							</ul>
						</li>
					</ul>
					<? }?>
				</li>
			<? }?>
		</ul>
	</li>
	<li><h3>Information</h3>
		<ul>
			<li><a href="<?=$mainbase?>/index.php?p=frames_fabrics">Frames and Fabrics</a></li>
			<li><a href="<?=$mainbase?>/content/Lafuma-Product-list.pdf">Product Comparison</a></li>
		</ul>
	</li>
</ul>