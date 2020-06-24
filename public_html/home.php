<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security

$timer=$deadline-date("U");
$unixremain=$timer%3600;
$dhrs=($timer-$unixremain)/3600;
$dmins=floor($unixremain/60);
$xmas=date("U")<=strtotime("25 December 2012")?"_xmas":"";

$ontext="Orders in the next<br />".($dhrs>0?$dhrs." hours, ":"").$dmins ." minutes<br />for next day delivery*";
$offtext="Same day dispatch*<br />on all orders<br />placed before ".date("ga",$deadline)."!";
$datematch=date("j-n-Y");
$weekend=in_array(strtolower(date("D")),array("sat","sun"))?1:0;
$bankhol=array_key_exists($datematch,$bankhols)?1:0;
$maxdiscountq=mysql_query("SELECT MAX(`list_price".PRICECUR."`) FROM products WHERE `".WHICHLIST."` = '1'");
list($maxdiscount)=mysql_fetch_row($maxdiscountq);
?><?=$deviceType=="phone"?"":$hometop?>
<!--<div style="float:left">
	<div style="float:left;margin:0 10px;"><img src="content/img/main/lorry.jpg" alt="" /></div>
	<div style="float:right;margin-top:10px;font-weight:bold">
		<div id='jscount'>
		<? /* if($bankhol==0&&$weekend==0){*/?>
		<noscript><? //=$dmins>0 || $dhrs>0?$ontext:$offtext?></noscript>
		<? //}else{echo "Same day dispatch*<br />unavailable due to:<br />".$bankhols[$datematch];}?>
		</div>
		<? //if($bankhol==0&&$weekend==0){?>
		<script type="text/javascript">
		var deadline=<? //=date("H",$deadline)?>;
		function startTime()
		{
			var today=new Date();
			var h=today.getHours();
			var m=60-today.getMinutes();
			var s=60-today.getSeconds();
			h=deadline-h;
			if(m<60){h=h-1;}
			if(s<60){m=m-1;}
			hrs=(h>0?h+" hour"+(h>1?"s":""):"");
			mns=(m<60&&m>0?(hrs.length>0?", ":"")+m+" minute"+(m>1?"s":"")+" ":"");
			scs=(h<=0?(s==60?"0":s)+" second"+(s>1?"s":""):"");
			hms=hrs+mns+scs;
			if(h>-1)
			{
				document.getElementById('jscount').innerHTML="Order in the next<br />"+hms+"<br />for next day delivery*";
				t=setTimeout('startTime()',500);
			}
			else
			{
				document.getElementById('jscount').innerHTML="Same day dispatch*<br />on all orders<br />placed before <?=date("ga",$deadline)?>!";
			}
		}
		startTime();
		</script>
		<? //}?>
	</div>
	<div class="clear"></div>
</div>-->
<div class="clear"></div>
<div id="homeimg">
<!--<div style="color:red;font-weight:bold;font-size:18px;padding: 0 0 14px 0;text-align:center">LACING CORDS SOLD OUT - MORE STOCK ARRIVING SOON</div>-->
<a href="<?=$mainbase?>/index.php?p=products&amp;cat=43"><img src="content/img/main/anytime_main_screen.jpg" alt="" /></a>


</div>
<div>
<?
$toppicksq=mysql_query("SELECT p.`title` as title,`price".PRICECUR."` as price,`list_price".PRICECUR."` as list_price,`img_filename`,`rank`,`cust_rev_id`,`fusionId`,p.`prod_id` as prod_id,`seo_title` FROM products as p,customerreviews as c,fusion as f WHERE p.`prod_id`=c.`item_id` AND p.`prod_id`=f.`iSubId_FK` AND `vtype`='product' AND `iState`='1' AND `rank`>'3' AND `".WHICHLIST."` = '1' GROUP BY p.`prod_id` ORDER BY `rank` DESC,RAND() LIMIT 0,3");
if(mysql_num_rows($toppicksq)>0){?><h2>A selection of our top rated products</h2><? }
while($toppicks=mysql_fetch_array($toppicksq))
{
	?>
	<div style="width:33%;text-align:center;float:left;line-height:120%;">
		<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$toppicks['fusionId']?>&amp;prodname=<?=str_replace("_","%20",$toppicks['seo_title'])?>"><img src="content/img/products/small/<?=$toppicks['prod_id']?>-default.jpg" alt="" /></a><br />
		<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$toppicks['fusionId']?>&amp;prodname=<?=str_replace("_","%20",$toppicks['seo_title'])?>"><?=$toppicks['title']?></a><br />
		<?=$currarr[$domainext][2]?><?=addvat($toppicks['price'])?><?=$toppicks['list_price']>0?" <span style='text-decoration:line-through'>RRP: ".$currarr[$domainext][2].number_format($toppicks['list_price'],2)."</span>":""?>
	</div>
	<?
}
?><div class="clear"></div></div><?
//get 4 special offers
//list_products("SELECT fusionId,iOwner_FK,p.prod_id,p.title,p.list_price,p.sale,p.shortdesc,p.price,p.img_filename,SUM(nav_qty),FLOOR(MIN(nav_qty/item_qty)) as pack FROM fusion as f,products as p LEFT JOIN productkits as pk ON pk.kprod_id=p.prod_id,nav_stock as n,option_values as ov,fusion_options as fo,product_options as po WHERE f.iSubId_FK=p.prod_id AND p.prod_id=fo.prod_id AND po.opt_id=fo.opt_id AND ov.opt_id=fo.opt_id AND ov.variant_id=n.nav_skuvar AND iOwner_FK='0' AND nav_qty>0 AND vType='product' AND iState=1 GROUP BY p.prod_id ORDER BY f.iSort",4,"Special Offers"); 
mysql_query("CREATE OR REPLACE VIEW prods AS SELECT `fusionId`,`iOwner_FK`,p.`prod_id`,p.`title`,p.`list_price".PRICECUR."` as list_price,p.`sale`,p.`shortdesc`,p.`price".PRICECUR."` as price,`seo_title`,f.`iSort` FROM (fusion as f,products as p LEFT JOIN productkits as pk ON pk.`kprod_id`=p.`prod_id`) LEFT JOIN (((fusion_options as fo JOIN product_options as po ON po.`opt_id`=fo.`opt_id`) JOIN option_values as ov ON ov.`opt_id`=fo.`opt_id`)  JOIN nav_stock as n ON ov.`variant_id`=n.`nav_skuvar`) ON p.`prod_id`=fo.`prod_id` WHERE f.`iSubId_FK`=p.`prod_id` AND `iOwner_FK`='0' AND `vOwnerType`='department' AND `vType`='product' AND `iState`='1' AND `nav_qty`>'0' AND `".WHICHLIST."`='1' GROUP BY p.`prod_id` ORDER BY f.`iSort`");

list_products("SELECT `fusionId`,`iOwner_FK`,`prod_id`,p.`title`,`list_price`,`sale`,`shortdesc`,`price`,`seo_title`,AVG(`rank`) as avgrank,count(`rank`) as `totalrevs` FROM prods as p LEFT JOIN customerreviews as cr ON p.`prod_id`=cr.`item_id` GROUP BY p.`prod_id` ORDER BY p.`iSort`",4,"Suggested Products","lrgthumbs",2);
?><br />
<div>
	<span class="orangebold">Quality...</span><br />
	Lafuma, famous for the manufacture of backpacks, have used their knowledge of how to combine strong fabrics with lightweight frames in the development of their range of outdoor tubular furniture. Combine this with the French flair for design and you have a range of furniture that surpasses all others. All Lafuma furniture is manufactured in their modern production plant in Anneyron, in the Dr&ocirc;me region of France. <br /><br />
			
	<span class="orangebold">Style...</span><br />
	The frames, in steel or aluminium, are individual design innovations. In addition to the elegant lines of the frames, our padded, batyline and airlon fabrics are carefully chosen to offer the height of comfort whilst adding a touch of style to your outdoor space. Lafuma is also elegant enough to be used all year round at home, in a conservatory or garden house.<br /><br />
			
	<span class="orangebold">Comfort...</span><br />
	Lafuma use only the highest grade components in the manufacture of their products. This creates furniture that is as luxurious and relaxing to use, as it is pleasing to look at. Lafuma's range is highly versatile, offering a comprehensive range of models, which are perfect for use in the garden, by the pool or on the patio. Its innovative relaxers, sunbeds, chairs and tables are all lightweight, effortlessly foldable and easy to store, making them perfect for those on the move. <br />
</div>
