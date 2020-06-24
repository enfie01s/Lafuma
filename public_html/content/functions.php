<?php
if(!in_array(basename($_SERVER['PHP_SELF']),array("index.php","admin.php","auth.php","aauth.php","convertoldDB.php","login.php","paypalCallback.php","notificationPage.php","transactionRegister.php"))){die("Access Denied for ".basename($_SERVER['PHP_SELF']));}//direct access security
function prezero($digit)
{
	$return=strlen($digit)<2?"0".$digit:$digit;
	return $return;
}
function getvat($in,$intype="net")
{
	global $vat;
	if($intype=="net")
	{
		$a=number_format($in+($vat*($in/100)),2);//in + vat
		$b=number_format($a-$in,2,".","");//raw vat
	}
	else if($intype="gross")
	{
		$a=number_format($in/(($vat/100)+1),2,".","");//in - vat
		$b=number_format($in-$a,2,".","");//raw vat
	}
	return array($a,$b);
}
function currencyprice($price,$ex="")
{
	return $price;
}
function trimtext($text,$chars,$link="")
{
	if(strlen($text) > $chars)
	{
		$text=substr($text,0,$chars);
		$lastspace=strrpos($text," ");
		$text=substr($text,0,$lastspace);
		$text.="...";
		$text .= strlen($link)>0?" <a href='$link'>Read&#160;More&#160;&#62;&#62;</a>":"";
	}
	return $text;
}
function stars($rank,$size="")
{
	$floorrank=floor($rank);
	$starsoff=5-$floorrank;
	$starson=5-$starsoff;
	if($rank-$floorrank>0&&$rank-$floorrank<1){$starsoff-=1;}
	for($on=0;$on<$starson;$on++){
	?><span style="font-size:170%;color:#ddd319"><img src="content/img/main/star<?=$size?>.png" alt="&#9733;" style="vertical-align:middle" /></span><?
	}if($rank-$floorrank>0&&$rank-$floorrank<1){
	?><span style="font-size:170%;color:#ddd319"><img src="content/img/main/halfstar<?=$size?>.png" alt="/" style="vertical-align:middle" /></span><?
	}for($off=0;$off<$starsoff;$off++){
	?><span style="font-size:150%;color:#bbb"><img src="content/img/main/emptystar<?=$size?>.png" alt="&#9734;" style="vertical-align:middle" /></span><? 
	}
}
function helplink($pg)
{
	global $mainbase;
	return "<a href='$mainbase/admin.php?p=help&amp;pg=$pg' target='_blank'><img src='$mainbase/content/img/main/question-mark.gif' alt='?' /></a>";
}
function list_categories($query)
{
	global $per_row, $page, $mainbase, $securebase,$images_arr,$db,$deviceType;
	$loop=0;
	$per_row=$deviceType=="phone"?2:$per_row;
	$cats_query=mysql_query($query,$db)or die(mysql_error());
	$count_cats=mysql_num_rows($cats_query);
	if($count_cats>0)
	{ 
		?>
		<table style="margin-left:auto;margin-right:auto;" id="homeprods">
		<?php 
	}
	while($cat=mysql_fetch_assoc($cats_query))
	{
		$loop++;
		if($loop%$per_row==1){echo "<tr>";}
		?>
		<td><a href="<?=$mainbase?>/index.php?p=products&amp;cat=<?=$cat['cat_id']?>&amp;catname=<?=urlencode(strtolower(str_replace(" ","_",$cat['title'])))?>"><img src="<?=$images_arr['department']['path']."/".$cat['image1']?>" alt="<?=$cat['title']?>" /></a><p><a href="<?=$mainbase?>/index.php?p=products&amp;cat=<?=$cat['cat_id']?>&amp;catname=<?=urlencode(strtolower(str_replace(" ","_",$cat['title'])))?>"><?=$cat['title']?></a></p></td>
		<?
		if($loop==$count_cats&&$count_cats%$per_row<$per_row&&$count_cats%$per_row>0){echo "<td colspan='".($per_row-($count_cats%$per_row))."'>&#160;</td>"; }
		if($loop%$per_row==0||$loop==$count_cats){echo "</tr>";}
		if($loop==$per_row&&$page=="home"){?><tr><td colspan="<?=$per_row?>"><span class="orangebold">SAME DAY DISPATCH! - on all orders placed before 12pm</span></td></tr><? }
	}
	if($count_cats>0)
	{ 
		?></table><?
	}
}
function list_products($query,$limit,$header,$class="",$cols="")
{
	global $parentcat, $prods_per_page, $maxpagelinks, $mainbase, $securebase,$images_arr,$page,$db,$pid,$_SESSION,$the_array,$currarr,$domainext,$deviceType;
	$ignore=array("page");
	$qstring="";
	$keytotal=count($_GET)-count($ignore);
	$class=strlen($class)>0?$class:"product";
	$cols=strlen($cols)>0?$cols:2;
	$cols=$deviceType=="phone"&&$class!="prodthumbs"?1:$cols;
	$l=0;
	foreach($_GET as $key => $val){if(!in_array($key,$ignore)){if($l<=$keytotal){$qstring.="&amp;";}$qstring.=$key."=".$val;}$l++;}//remove ignored keys
	$taklimit=" LIMIT ".$limit;
	if($limit=="")
	{
		$takquery=pagenums($query,"index.php?".$qstring,$prods_per_page,$maxpagelinks);
		$final_query=$takquery[0];
	}
	else
	{
		$final_query=$query.$taklimit;
	}
	$prods_query=mysql_query($final_query,$db) or die("Query error<br />$final_query<br /><br />".mysql_error());
	$prods_count=mysql_num_rows($prods_query);
	
	if($prods_count>0){
		if($class==="offerprods"){?><div id="offerprod"><div style="position:absolute;right:5px;top:0px;font-weight:bold;font-size:110%;color:#888;">CLOSE <a href="<?=$mainbase?>/index.php?<?=str_replace("&offerprod=1","",$_SERVER['QUERY_STRING'])?>" style="color:#000">(X)</a></div><? }?>
		<h2><?=$header?></h2>
		<? if(($prods_count>6&&!in_array($class,array("prodthumbs","offerprods")))||isset($_GET['page'])){?>
		<div style="float:left"><?=$takquery[1]?></div>
		<div style="float:right">
			<div id="sorting"> Sort By: 
				<form action="<?=$mainbase?>/index.php" method="get" style="display:inline;vertical-align:middle">
				<input type="hidden" name="p" value="<?=$page?>" />
				<input type="hidden" name="cat" value="<?=$_GET['cat']?>" />
				<select name="sort" class="formselect" style="width:auto">
					<option value="p.iSort"<? if((isset($_GET['sort'])&&$_GET['sort']=="p.iSort")||!isset($_GET['sort'])){?> selected="selected"<? }?>>Default</option>
					<option value="p.title"<? if(isset($_GET['sort'])&&$_GET['sort']=="p.title"){?> selected="selected"<? }?>>Name</option>
					<option value="avgrank"<? if(isset($_GET['sort'])&&$_GET['sort']=="avgrank"){?> selected="selected"<? }?>>Rating</option>
					<option value="p.price"<? if(isset($_GET['sort'])&&$_GET['sort']=="p.price"){?> selected="selected"<? }?>>Price</option>
				</select>
				<select name="ascdesc" class="formselect" style="width:auto">
					<option value="ASC"<? if((isset($_GET['ascdesc'])&&$_GET['ascdesc']=="ASC")||!isset($_GET['ascdesc'])){?> selected="selected"<? }?>>Low - High</option>
					<option value="DESC"<? if(isset($_GET['ascdesc'])&&$_GET['ascdesc']=="DESC"){?> selected="selected"<? }?>>High - Low</option>
				</select>
				<input type="submit" value="Go" class="formbutton" style="padding-top:1px;padding-bottom:1px;vertical-align:inherit" />
				</form>
			</div>
		</div>
		<div class="clear"></div>
		<? }
		?>
		<table class="<?=$class?>">
		<tr>
		<?
		$row=0;
		while($prod=mysql_fetch_assoc($prods_query))
		{
			if(file_exists($images_arr['product']['path']."/small/".$prod['prod_id']."-default.jpg"))
			{
				$newdims=getnewdimensions(65,65,$images_arr['product']['path']."/small/".$prod['prod_id']."-default.jpg");
				$imgsrc="src='".$images_arr['product']['path']."/small/".$prod['prod_id']."-default.jpg"."'".($class=="prodthumbs"?" style='width:".$newdims[0]."px;height:".$newdims[1]."px;'":"");
			}
			else
			{
				$imgsrc="src='".$images_arr['product']['path']."/small/imgmissing.gif'".($class=="prodthumbs"?" style='width:65px;height65px;'":" style='width:78px;height:75px;'");
			}
			
			$row++;
			switch($class){
				case "prodthumbs":
					?>
					<td>
					<div class="pimg"><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>" title="<?=$prod['title']?>"><img <?=$imgsrc?> alt='' /></a></div>
					</td>
					<?=(($row%$cols==0&&$row<$prods_count)?"</tr><tr>":(($row==$prods_count&&$row%$cols!=0)?"<td class='empty'>&#160;</td>":""));
					break;
				case "lrgthumbs":
					?>
					<td class="pimg">
					<div style="position:relative;top:0px;left:0px;">
						<div style="text-align:left"><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>" class="orangebold"><?=$prod['title']?></a></div><br />
						<div style="position:relative;width:78px;float:left">
							<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>" title="<?=$prod['title']?>">
							<img <?=$imgsrc?> alt='' />
							</a><br />
							Average Rating:<br />
							<?=stars($prod['avgrank'],"small")?><br />
							(<?=$prod['totalrevs']?> Review<?=$prod['totalrevs']==0||$prod['totalrevs']>1?"s":""?>)
							<? if($prod['sale']==1){?><div class="salebadge"><img src="content/img/main/sale_icon.png" alt="On Sale!" /></div><? }?>
						</div>
						<div style="float:left;text-align:left;margin-left:10px;margin-bottom:35px;width:180px;">
							<?=trimtext(str_replace("\n","<br />",htmlentities($prod['shortdesc'],ENT_QUOTES,"ISO-8859-1")),150)?>
						</div>
						<div class="clear"></div>	
						<div style="text-align: right;position:absolute;right:0px;bottom:0px;">
							<div class="pprice"><?=$currarr[$domainext][2]?><?=addvat($prod['price'])?>
							<? if($prod['list_price']!=0){?> <span style="text-decoration:line-through">RRP: <?=$currarr[$domainext][2]?><?=number_format($prod['list_price'],2)?></span><? }?><br />
							</div>
							<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>">View details &#187;</a>
						</div>
					</div>
					</td>
					<?=(($row%$cols==0&&$row<$prods_count)?"</tr><tr>":(($row==$prods_count&&$row%$cols!=0)?"<td class='empty'>&#160;</td>":""));
					break;
				case "offerprods":
					?>
					<td class="pimg"><div style="position:relative;top:0px;left:0px;">
						<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>"><img src="<?=$images_arr['product']['path']?>/small/<?=$prod['prod_id']?>-default.jpg" alt="<?=$prod['title']?>" /></a>
						<? if($prod['sale']==1){?><div class="salebadge"><img src="content/img/main/sale_icon.png" alt="On Sale!" /></div><? }?>
						</div>
					</td>
					<td class="pinfo">
						<div>
						<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>" class="orangebold"><?=$prod['title']?></a> <span class="pprice"><?=$currarr[$domainext][2]?><?=addvat($prod['price'])?><? if($prod['list_price']!=0){?> <span style="text-decoration:line-through">RRP: <?=$currarr[$domainext][2]?><?=number_format($prod['list_price'],2)?></span><? }?></span><br />
						<?=trimtext(strip_tags($prod['content']),100,"$mainbase/index.php?p=products&amp;pid=$prod[fusionId]&amp;prodname=".str_replace("_","%20",urlencode($prod['seo_title'])))?>
						<? if($prod['iState']==0){?><br /><dfn>Available for purchase due to your addition of <?=$the_array['title']?> to your basket.</dfn><? }?>
						</div>
					</td>
					<?=$row%$cols==0&&$row<$prods_count?"</tr><tr>":"";
					break;
				default:
					?>
					<td class="pimg"><div style="position:relative;top:0px;left:0px;">
						<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>"><img src="<?=$images_arr['product']['path']?>/small/<?=$prod['prod_id']?>-default.jpg" alt="<?=$prod['title']?>" /></a><br />
						Average Rating:<br />
						<?=stars($prod['avgrank'],"small")?><br />
						(<?=$prod['totalrevs']?> Review<?=$prod['totalrevs']==0||$prod['totalrevs']>1?"s":""?>)
						<? if($prod['sale']==1){?><div class="salebadge"><img src="content/img/main/sale_icon.png" alt="On Sale!" /></div><? }?>
						</div>
					</td>
					<td class="pinfo">
						<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>" class="orangebold"><?=$prod['title']?></a><br />
						<span class="pprice">
							<?=$currarr[$domainext][2]?><?=addvat($prod['price'])?><?
							if($prod['list_price']!=0){?> <span style="text-decoration:line-through">RRP: <?=$currarr[$domainext][2]?><?=number_format($prod['list_price'],2)?></span><? }?>
						</span><br />
						<?=str_replace("\n","<br />",htmlentities($prod['shortdesc'],ENT_QUOTES,"ISO-8859-1"))?><br />
						<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prod['fusionId']?>&amp;prodname=<?=str_replace("_","%20",urlencode($prod['seo_title']))?>">More information &#187;</a>
					</td>
					<?=$row%$cols==0&&$row<$prods_count?"</tr><tr>":"";
					break;
			}
		}
		?>
		</tr>
		</table>
		<?
		if(($prods_count>2&&!in_array($class,array("prodthumbs","offerprods")))||isset($_GET['page'])){?><div style="text-align:left;margin-top:3px;"><?=$takquery[1]?></div><? } if($class==="offerprods"){?></div><? }
	}else if($header!="Suggested Products"&& $header!="Special Offers"&&$class!=="offerprods"&&$_GET['cat']!=10){
		?>No products found<?
	}
}
function getnewdimensions($maxwidth,$maxheight,$file_tmp)
{
	/*LIST THE WIDTH AND HEIGHT AND KEEP THE HEIGHT RATIO*/
	list($width, $height) = getimagesize($file_tmp);
	
	/*CALCULATE THE IMAGE RATIO*/
	if($width>$height)
	{
		$imgratio=$width/$height;
		$newwidth = ($imgratio>1)?$maxwidth:$maxwidth*$imgratio;
		$newheight = ($imgratio>1)?$maxwidth/$imgratio:$maxwidth;
	}
	else
	{
		$imgratio=$height/$width;
		$newwidth = ($imgratio>1)?$maxwidth:$maxwidth/$imgratio;
		$newheight = ($imgratio>1)?$maxwidth*$imgratio:$maxwidth;
	}
	
	
	/*SIZE DOWN AGAIN TO KEEP WITHIN HEIGHT CONTRAINT*/
	if($newheight>$maxheight)
	{
		if($newwidth>$newheight)
		{
			$imgratio=$newwidth/$newheight;
			$newheight = ($imgratio>1)?$maxheight:$maxheight/$imgratio;
			$newwidth = ($imgratio>1)?$maxheight*$imgratio:$maxheight;
		}
		else
		{
			$imgratio=$newheight/$newwidth;
			$newheight = ($imgratio>1)?$maxheight:$maxheight*$imgratio;
			$newwidth = ($imgratio>1)?$maxheight/$imgratio:$maxheight;
		}
	}
	$newheight=round($newheight);
	$newwidth=round($newwidth);
	return array($newwidth,$newheight);
}	
function addvat($price,$ex="")
{
	global $vat;
	return number_format($price+($vat*($price/100)),2)." inc. vat.";
}
function pagenums($query,$inurl,$prods_per_page,$maxpagelinks,$forceseltype='')
{
	global $db;
	$prods_query1=mysql_query($query,$db) or die(mysql_error());
	$prods_num=mysql_num_rows($prods_query1);
	$pgnum=(isset($_GET['page'])&&$_GET['page']>0)?$_GET['page']:1;
	$pgstart = ($pgnum > 0 && (($pgnum-1)*$prods_per_page) <= $prods_num) ? (($pgnum-1)*$prods_per_page) : 0;
	$pgend = ($pgstart+$prods_per_page >= $prods_num) ? $prods_num : $pgstart+$prods_per_page;
	if($prods_num > $prods_per_page)
	{
		$totalpages = ceil($prods_num/$prods_per_page);//raw pages
		$seltype = strlen($forceseltype)>0?$forceseltype:($totalpages > ($maxpagelinks*2) ? 1 : 0);
		$backlink = $pgnum > 1&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "| &#160;<a href='$inurl&amp;page=".($pgnum-1)."'>BACK</a>" : "";
		$nextlink = $pgnum < $totalpages&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "&#160;<a href='$inurl&amp;page=".($pgnum+1)."'>NEXT</a>&#160;|" : "";
		$firstlink =$pgnum > 1&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "<a href='$inurl&amp;page=1'>&#171; FIRST</a>" : ($pgnum <= 1&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages))?"<span style='color:#999999'>&#171; FIRST</span>":"");
		$lastlink = $pgnum < $totalpages&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages)) ? "<a href='$inurl&amp;page=".($totalpages)."'>&#160;LAST &#187;</a>" : ($pgnum >= $totalpages&&($seltype==1||($seltype==0&&$maxpagelinks<$totalpages))?"<span style='color:#999999'>&#160;LAST &#187;</span>":"");
		$paginationstart = $pgnum > ceil($maxpagelinks/2) && !($totalpages < $maxpagelinks && $pgnum == $totalpages) ? (($pgnum < $totalpages-floor($maxpagelinks/2)) ? $pgnum-($maxpagelinks-3) : ($totalpages-$maxpagelinks+1)) : 1;		
		$pgnumbers = "";
		if($seltype==0)
		{ 
			for($p=$paginationstart;$p<=$totalpages && $p < $paginationstart+$maxpagelinks;$p++)
			{
				if($p == $pgnum){
					$pgnumbers.="<span class='pagelinkon'>$p</span>";
				}else{
					$pgnumbers.="<a href='$inurl&amp;page=$p' class='pagelink'>$p</a>";
				}
			}
		}
		else
		{
			$pgnumbers="(Page <form action='$inurl' method='get' style='display:inline-block' name='pageform' class='pageform'><select name='page' onchange='location.href=this.options[selectedIndex].value'>";
			for($p=1;$p<=$totalpages;$p++)
			{
				$ss=($p == $pgnum)?"selected='selected'":"";
				$pgnumbers.="<option value='".$inurl."&amp;page=".$p."' $ss>$p</option>";
			}
			$pgnumbers.="</select></form> of ".$totalpages.")";
		}
		$pagesdisplay="<div class='pagination'>";
		if($seltype==0){$pagesdisplay.="<span class='desc'>$totalpages PAGES:</span> "; }
		$pagesdisplay.="$firstlink $backlink $pgnumbers $nextlink $lastlink</div>";
		if(basename($_SERVER['PHP_SELF'])=="admin.php"){
			$pagesdisplay.="<div class='paginationshowing'>Showing: ".($pgstart+1)." to $pgend of $prods_num</div>";
		}
		$pagesdisplay.="<div class='clear'></div>";
	}
	$toreturn=array($query." LIMIT ".(($pgnum-1)*$prods_per_page).",$prods_per_page",$pagesdisplay);
	return $toreturn;
}
function getcrumbs($pid)
{
	global $mainbase,$db,$crumbsep;
	$parent="";
	if($pid!=""&&$pid!=0)
	{
		$pquery="SELECT * FROM categories,fusion WHERE iSubId_FK='$pid' AND fusion.iSubId_FK=categories.cat_id AND vType='department' AND vOwnerType='department' LIMIT 1";
		$parents_query=mysql_query($pquery,$db);
		$parents=mysql_fetch_assoc($parents_query);
		$parents_num=mysql_num_rows($parents_query);
		if($parents_num>0)
		{		
			if($parents['iOwner_FK']!=0){$parent.=getcrumbs($parents['iOwner_FK']);}//recursively get parents
				if(basename($_SERVER['PHP_SELF'])=="admin.php"){
					$parent.=" $crumbsep <a href='$mainbase/admin.php?p=builder&amp;act=view&amp;what=department&amp;id=$parents[fusionId]&amp;name=".urlencode($parents['title'])."'>$parents[title]</a>";
				}
				else
				{
					$parent.=" $crumbsep <a href='$mainbase/index.php?p=products&amp;cat=$parents[cat_id]'>$parents[title]</a>";
				}
		}
	}
	return $parent;
}
function formrows($details,$required,$selects,$radios,$checkboxes,$match,$formname,$textarea=array())
{
	global $errorlist, $page,$db;
	$row=0;
	foreach($details as $name => $title)
	{
		$matching=is_array($match)?$match[str_replace($formname,"",$name)]:$match;
		$highlight=(array_key_exists($name,$errorlist)||(basename($_SERVER['PHP_SELF'])=="admin.php"&&in_array($name,$errorlist)))?" style='border:1px solid red;'":"";
		?>
		<tr>
			<td<? if($row==0){?> class="first"<? }if(in_array($name,$textarea)){?>style="vertical-align:top"<? }?>><label for="<?=$name.$formname?>"><?=$title?><? if(in_array(str_replace($formname,"",$name),$required)){?> <span>*</span><? }?></label></td>
			<td>
			<? 
			/* ------- DROPDOWNS ------- */
			if(array_key_exists($name,$selects)){?>
				<select name="<?=$name?>" id="<?=$name.$formname?>" class="formselect"<?=$highlight?>>
				<option value="">Select Below</option>
				<? 
				$query=mysql_query($selects[$name],$db);
				while($result=mysql_fetch_row($query))
				{
					if(str_replace($formname,"",$name)=="country"&&$formname!="updateform"){
					if($matching=="100"){$matching="GB";}					
					else if($matching=="297"){$matching="IE";}	
					else if($matching=="298"){$matching="IM";}
					}
					?>
					<option value="<?=$result[0]?>" <?=($result[0]==$matching)?"selected='selected'":""?>><?=$result[1]?></option>
					<?
				}
				?>
				</select>
			<? 
			/* ------- RADIOS ------- */
			}else if(array_key_exists($name,$radios)){
				$radioslist=explode(",",$radios[$name]);
				foreach($radioslist as $rad)
				{
					$radvals=explode(":",$rad);
					?>
					<label for="<?=$name.$radvals[0]?>"><input type="radio" name="<?=$name?>" id="<?=$name.$radvals[0]?>" value="<?=$radvals[0]?>" <?=($matching==$radvals[0])?"checked='checked'":""?><?=$highlight?> /> <?=$radvals[1]?></label> 
					<? 
				}
			/* ------- CHECKBOXES ------- */
			}else if(array_key_exists($name,$checkboxes)){
				$checkboxeslist=explode(",",$checkboxes[$name]);
				foreach($checkboxeslist as $checkbox)
				{
					$checkboxvals=explode(":",$checkbox);
					?>
					<label for="<?=$name.$checkboxvals[0]?>"><input type="checkbox" name="<?=$name?>[]" id="<?=$name.$checkboxvals[0]?>" value="<?=$checkboxvals[0]?>"<?=$name=="matchbilling"?" onchange='matchbill()'":""?> <?=($matching==$checkboxvals[0])?"checked='checked'":""?><?=$highlight?> /> <?=$checkboxvals[1]?></label> 
					<? 
				}
			/* ------- INPUTS ------- */
			}else if(in_array($name,$textarea)){
				?><textarea name="<?=$name?>" id="<?=$name.$formname?>" onfocus="this.select()" class="formfield" rows="3" cols="5"<?=$highlight?>><?=$matching?></textarea><?
			/* ------- BIRTHDATE ------- */
			}else if($name=="dob"){
				$vardob=isset($_POST[$name])?$_POST[$name]:(isset($_GET[$name])?$_GET[$name]:"");
				?><script type="text/javascript">DateInput('<?=$name?>', true, 'DD/MM/YYYY','<?=strlen($vardob)>0?$vardob:date("d/m/Y")?>')</script>
					<noscript><input type="text" name="<?=$name?>" value="<?=strlen($vardob)>0?date("d/m/Y",$name):date("d/m/Y")?>"<?=$highlight?> /></noscript><?
			/* ------- INPUTS ------- */
			}else{?>
				<input type="<?=(($name=="pass"||$name=="password")?"password":"text")?>" name="<?=$name?>" id="<?=$name.$formname?>" value="<?=$matching?>" onfocus="this.select()" class="formfield"<?=$highlight?> />
			<? }?>
			</td>
		</tr>
		<?
		$row++;
	}
}
function get_variant_qty($variants)
{
	global $db;
	$vquantity=array();
	foreach($variants as $variant)
	{
		$vars=explode("-v-",$variant);
		$nav_stock=mysql_query("SELECT nav_qty FROM nav_stock WHERE nav_sku='$vars[0]' AND nav_variant='$vars[1]'",$db);
		list($qty)=mysql_fetch_row($nav_stock);
		$vquantity[$variant]=$qty;
	}
	return $vquantity;
}
/* oid = order_id if this is a pack */
function colourchooser($prodid,$qty_per_item,$totalitems,$omit,$nochanger="",$arrayprodid="",$oid=0)
{
	global $images_arr,$page,$extraimg,$db,$mainbase;
	?><script type="text/javascript">var medimgs=[];</script><?
	$skuvarname=$arrayprodid==""?"skuvariant[$prodid]":"skuvariant[$arrayprodid][$prodid]";
	$extraimg=array();
	//$opts_q=mysql_query("SELECT * FROM product_options,nav_stock WHERE product_options.variant_id=nav_stock.nav_skuvar AND nav_qty > 0 AND prod_id='$prodid' ORDER BY prod_opt_id");
	$option="";
	$ovalid="";
	$selected="none";
	$adinv=(basename($_SERVER['PHP_SELF'])=="admin.php"&&$page=="invoices")?1:0;
	if($adinv)
	{
		$selected="this";
		$selectedoptq=mysql_query("SELECT *,CONCAT(op.sku,'-v-',op.variant_id) as variant_id FROM orderproducts as op LEFT JOIN option_values as ov ON CONCAT(op.sku,'-v-',op.variant_id)=ov.variant_id WHERE order_prod_id='$omit'",$db);
		$selopt=mysql_fetch_assoc($selectedoptq);
		if($oid>0){
			$selectedoptq=mysql_query("SELECT * FROM orderkits as k LEFT JOIN option_values as ov ON k.okit_skuvar=ov.variant_id WHERE order_id='$oid' AND order_prod_id='$omit'",$db);
			$selopt=mysql_fetch_assoc($selectedoptq);
		}
		$ovalid="AND variant_id!='$selopt[variant_id]'";
		$chosencolor=$selopt['oitem'];
		$option="<option value='$omit' selected='selected'>$chosencolor (Current Choice)</option>";
	}else{$ovalid=" AND `".WHICHLIST."` = '1'";}
	$qqq="SELECT p.`prod_id` as prod_id,ov.`item_desc` as item,`nav_qty`,`nav_skuvar`,ov.`img_filename` as image FROM products as p,fusion_options as fo,option_values as ov,product_options as po,nav_stock WHERE p.`prod_id`=fo.`prod_id` AND po.`opt_id`=fo.`opt_id` AND ov.`opt_id`=fo.`opt_id` AND ov.`variant_id`=nav_stock.`nav_skuvar` AND `nav_qty` > 0 AND p.`prod_id`='$prodid' $ovalid ORDER BY ov.vsort";
	$opts_q=mysql_query($qqq,$db)or die("Error $qqq<br />".mysql_error());
	$opts_num=mysql_num_rows($opts_q);
	if($opts_num>0)
	{
		if($totalitems==1){?>
			<label for="skuvariant<?=$prodid?>">Colour</label><?=(($adinv)?" ":"<br />")?>
		<? }
		
		if($adinv)
		{?><input type="hidden" name="popttoorderopt[<?=$prodid?>]" value="<?=$omit?>" /><? }?>
		<span class="hidefromprint">
		<select name="<?=$skuvarname?>" id="skuvariant<?=$prodid?>" class="formfield"<? if($adinv==0&&$page=="products"&&$nochanger==""){?> onchange="javascript:swapimage('thumbnail','<?=$prodid?>-'+this.options[this.selectedIndex].value)"<? }?>>
		<? if(!$adinv){?><option value="" selected="selected">-- Select Colour --</option><? }?>
		<?=$option?>
		<?
		while($opt=mysql_fetch_assoc($opts_q))
		{
			if(floor($opt['nav_qty']/$qty_per_item)>=1)
			{
				$item=str_replace(array("(",")"),"",$opt['item']);
				$stripos=stripos($item,"LFM");
				if(!$stripos){$stripos=stripos($item,"60");}
				$nums[0]=($stripos)?substr($item,0,$stripos):$item;
				$color=trim($nums[0]);
				?><option value="<?=$opt['nav_skuvar']."-qty-".$qty_per_item?>"><?=$color?> (<?=floor($opt['nav_qty']/$qty_per_item)>5?"in":"low"?> stock)</option><? 			
			}
		}
		mysql_data_seek($opts_q,0);//reset index to 0 so we can reuse the $opts_q query.
		?>
		</select></span><span class="hidefromweb"><?=$chosencolor?></span><?=(($adinv)?" ":"<br />")?>
		<? if($totalitems==1){?><br /><?=(($adinv)?"":"<br />")?><? }else{?>&#160;<? }?>
		<? 
		$row=($adinv)?0:1;
		if($adinv&&$selopt['img_filename']!="0"&&strlen($selopt['img_filename'])>0)
		{
			?>
			<span class="hidefromprint"><a href="#" onclick="document.productoptions.skuvariant<?=$prodid?>[<?=$row?>].selected=true;" title="<?=$selopt['item']?>" style="vertical-align:text-bottom"><img src="content/img/main/leaf-mask-small.png" style="vertical-align:text-bottom;background: url('<?=$images_arr['product_options']['path']?>/small/<?=$selopt['img_filename']?>')" alt="<?=$selopt['item']?>" /></a></span>
			<?
			$row++;
		}
		while($opt=mysql_fetch_assoc($opts_q))
		{/* HERE */
			$img=$prodid."-".strtolower($opt['nav_skuvar']).".jpg";
			$medimg="./".$images_arr['product']['path']."/medium/".$img;
			$dorollovers=is_file($medimg)?1:0;
			if($dorollovers){ 
				?><script type="text/javascript">medimgs.push("<?=$img?>");</script><?
			}
			if($opt['image']!="0")
			{
				if(floor($opt['nav_qty']/$qty_per_item)>=1){?><span class="hidefromprint"><a href="#" onclick="document.productoptions.skuvariant<?=$prodid?>[<?=$row?>].selected=true;<? if($adinv==0&&$page=="products"&&$dorollovers){?>swapimage('thumbnail','<?=$img?>')<? }?>" title="<?=$opt['item']?>" style="vertical-align:text-bottom"<? if($adinv==0&&$page=="products"&&$dorollovers){?> onmouseover="javascript:swapimage('thumbnail','<?=$img?>')" onmouseout="javascript:returnimage('thumbnail')"<? }?>><img src="content/img/main/leaf-mask<? if($totalitems>1||$adinv){?>-small<? }?>.png" style="vertical-align:text-bottom;background: url('<?=$images_arr['product_options']['path']?>/<? if($totalitems>1||$adinv){?>small/<? }?><?=$opt['image']?>')" alt="<?=$opt['item']?>" /></a></span><? 
					$row++;
				}
			}
			$extraimg[]=array("image"=>strtolower($prodid."-".$opt['nav_skuvar'].".jpg"),"name"=>$opt['item']);
		}
	}
	else if($adinv)
	{
		?>
		<label for="skuvariant<?=$prodid?>">Colour</label> 
		<select name="<?=$skuvarname?>" id="skuvariant<?=$prodid?>" class="formfield">
		<?=$option?>
		</select>
		<? if($selopt['img_filename']!="0"){?>
		<br />
		<a href="#" onclick="document.productoptions.skuvariant<?=$prodid?>[0].selected=true;" title="<?=$selopt['oitem']?>" style="vertical-align:text-bottom"><img src="content/img/main/leaf-mask-small.png" style="background: url('<?=$images_arr['product_options']['path']?>/small/<?=$selopt['img_filename']?>')" alt="<?=$selopt['oitem']?>" style="vertical-align:text-bottom" /></a> 
		<?
		}
	}
	else//if no option associated so set default
	{
		$sq=mysql_query("SELECT nav_skuvar FROM products as p,nav_stock as n WHERE p.sku=n.nav_sku AND p.prod_id='$prodid' AND nav_qty>0 AND ".WHICHLIST." = 1 ORDER BY nav_skuvar ASC",$db);
		list($prodsku)=mysql_fetch_row($sq);
		?><input type="hidden" name="<?=$skuvarname?>" value="<?=$prodsku?>-qty-<?=$qty_per_item?>" /><?
	}
}
function get_country($code)
{
	global $db;
	$country="Unknown Country";
	$where=is_numeric($code)?"country_id":"cshortname";
	$countryq=@mysql_query("SELECT countryname FROM countries WHERE ".$where."='$code'",$db);
	list($country)=@mysql_fetch_row($countryq);
	return $country;
}
function get_county($code)
{
	global $db;
	$county="Unknown County";
	$countyq=@mysql_query("SELECT countyname FROM counties WHERE county_id='$code'",$db);
	list($county)=@mysql_fetch_row($countyq);
	return $county;
}
function postage_expired($stamp)
{
	$expired=((date("w")>date("w",$stamp) || (date("w")>=date("w",$stamp) && date("A")>=date("A",$stamp) && date("H")>=date("H",$stamp) && date("i")>=date("i",$stamp))))?1:0;
	return $expired;
}

function getcurr($subj)
{
	global $currarr,$currkeys;
	$crl=count($currarr);
	for($x=0;$x<$crl;$x++)
	{
		if(strlen(array_search($subj,$currarr[$currkeys[$x]]))>0){return $currkeys[$x];}
	}
}
function ordercontents($where,$width)
{
	global $mainbase, $securebase, $page,$db,$currarr,$domainext,$currencylang;
?>
<table style="width:<?=$width?>px" class="details">
	<tr>
		<td class="head" style="text-align:center">Quantity</td>
		<td class="head">Product</td>
		<td class="head" style="text-align:right">Price (+ VAT)</td>
		<td class="head" style="text-align:right">Sub Total</td>
	</tr>
	<? 
	$runtotal=0;
	$removefromdiscount=0;
	$discount=0;
	$sstring="SELECT qty,prod_id,order_prod_id,sku,oitem,oname,title,op.price as price,o.discount as odiscount, op.discount as opdiscount,discount_code,ship_description,ship_total,total_price,tax_rate,tax_price,fusionId,goptid,exclude_discount,o.order_id,exchrate,currency FROM orders as o,orderproducts as op LEFT JOIN fusion as f ON op.prod_id=f.iSubId_FK WHERE $where AND o.order_id=op.order_id GROUP BY op.order_prod_id";
	
	$orderq=mysql_query($sstring,$db);
	$curren=array();
	while($order=mysql_fetch_assoc($orderq))
	{
		if(count($curren)<1){$curren=$currencylang[$order['currency']];}
		$iQuantity=$order['qty'];
		$iProductId=$order['prod_id'];
		$orderkitq=mysql_query("SELECT fusionId,kit_title,item_qty,oname,oitem,prod_id,order_prod_id FROM orderkits as ok LEFT JOIN fusion as f ON ok.prod_id=f.iSubId_FK AND vtype='product' WHERE order_prod_id='$order[order_prod_id]' GROUP BY okit_id",$db);
		$ispack=mysql_num_rows($orderkitq);
		
		?>
		<tr>
			<td style="vertical-align:top;text-align:center"><? if(basename($_SERVER['PHP_SELF'])=="admin.php"&&$page=="invoices"){?>
			<input type="hidden" name="price[<?=$order['order_prod_id']?>]" value="<?=$order['price']?>" />
			<input type="text" name="qty[<?=$order['order_prod_id']?>]" value="<? }?><?=$order['qty']?><? if(basename($_SERVER['PHP_SELF'])=="admin.php"&&$page=="invoices"){?>" class="formfields" style="text-align:center" /><? }?><? if($order['exclude_discount']==1){?><br /><dfn style="font-size:90%;font-style:italic;color:#CD071E">Discount exempt</dfn><? }?></td>
			<td style="vertical-align:top"><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$order['fusionId']?>"><?=$order['title']." (".$order['sku'].")"?></a>
				<? if($ispack==0){?>
					<br />
					<? if(basename($_SERVER['PHP_SELF'])=="admin.php"&&$page=="invoices")
					{
						colourChooser($iProductId,1,1,$order['order_prod_id']);
					}
					else
					{
						echo ucwords($order['oname']).": ".$order['oitem'];
					}
				}else{?>
					<div class="pack_contents"> <strong>Pack Contents</strong><br />
						<? while($orderkit=mysql_fetch_assoc($orderkitq))
						{
							?>
							<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$orderkit['fusionId']?>"><?=$orderkit['kit_title']?></a> (<?=$orderkit['item_qty']?>)<br />
							<?
							if(basename($_SERVER['PHP_SELF'])=="admin.php"&&$page=="invoices")
							{
								colourChooser($orderkit['prod_id'],1,1,$orderkit['order_prod_id'],'','',$order['order_id']);
							}
							else
							{
								echo ucwords($orderkit['oname']).": ".$orderkit['oitem'];
							}
							?>
							<br />
							<? 
						}?>
					</div>
				<? }
				$priceaddvat=$order['price']+($order['tax_rate']*($order['price']/100));
				?>
			</td>
			<td style="vertical-align:top;text-align:right"><span class="price"><?=$curren[2].number_format($priceaddvat,2)?></span></td>
			<td style="vertical-align:top;text-align:right"><span class="price"><?=$curren[2].number_format($priceaddvat*$order['qty'],2)?></span></td>
		</tr>
		<?
		$itemprice=$priceaddvat*$order['qty'];
		$runtotal+=$itemprice;
		$odiscount=$order['odiscount'];//discount percentage
		$odiscountcode=$order['discount_code'];
		$oshipdesc=$order['ship_description'];
		$oshiptotal=$order['ship_total'];
		$ototalprice=$order['total_price'];
		$otaxrate=$order['tax_rate'];
		$otaxprice=$order['tax_price'];
		$discount+=$order['opdiscount']*$order['qty'];
	}
	//$discount=(($runtotal-$removefromdiscount)/100)*$odiscount;
	
	?>
	<tr>
		<td colspan="3" style="text-align:right;border-top:6px solid #DDDDDD"><strong>Sub Total</strong></td>
		<td style="text-align:right;border-top:6px solid #DDDDDD"><span class="price"><?=$curren[2].number_format($runtotal,2)?></span></td>
	</tr>
	<? if(strlen($odiscountcode)>0&&$odiscountcode!="discount code"){?>
	<tr>
		<td colspan="3" style="text-align:right"><strong><?=$odiscount?>% Discount (<?=$odiscountcode?>)</strong></td>
		<td style="text-align:right"><span class="price">- <?=$curren[2].number_format($discount,2)?></span></td>
	</tr>
	<? }?>	
	<tr>
		<td colspan="3" style="text-align:right"><strong>Postage Method: <?=$oshipdesc?></strong></td>
		<td style="text-align:right"><span class="price"><?=$curren[2].number_format($oshiptotal,2)?></span></td>
	</tr>
	<tr>
		<td colspan="3" style="text-align:right" class="head"><strong>Total:</strong></td>
		<td style="text-align:right" class="head"><span class="price"><?=$curren[2].number_format($ototalprice,2)?></span></td>
	</tr>
</table>
<div style="text-align:right"><dfn>Current Total includes VAT (@<?=$otaxrate?>%) of <?=$curren[2].number_format($otaxprice,2)?></dfn></div>
<?
}

function cartcontents($showremove)
{
	global $basket_total, $defaultpostage, $vat, $vattoadd, $sub_total, $defaultpostdesc, $discount, $mainbase, $securebase,$db,$currarr,$domainext;
	?>
	<table class="details" style="margin-top:30px">
	<tr>
		<td class="head">Quantity</td>
		<td class="head">Product</td>
		<td class="head" style="text-align:right">Unit Price</td>
		<td class="head" style="text-align:right">Total</td>
		<? if($showremove==1){?><td class="head" style="text-align:center">Remove</td><? }?>
	</tr>
	<? 
	$price_runnin=0;
	foreach($_SESSION['cart'] as $id => $cart)//each cart prod
	{
		/*code to add message telling customer or additional product removals*/
		$rcart_ids=array();
		$showmsg="";
		foreach($_SESSION['cart'] as $rid => $rcartitems){if(!in_array($rcartitems['prod_id'],$rcart_ids)&&$rid!=$id){array_push($rcart_ids,$rcartitems['prod_id']);}}
		$msgbits=array();
		foreach($_SESSION['cart'] as $ccid => $cartitems)
		{
			$allowedmatches=!is_array($cartitems['allowlist'])||count($cartitems['allowlist'])<1?1:count(array_intersect($cartitems['allowlist'],$rcart_ids));
			if($allowedmatches<1){$msgbits[]=$cartitems['title'];}
		}
		if(count($msgbits)>0){$showmsg.=implode(" & ",$msgbits);}
		if(strlen($showmsg)>1&&count($msgbits)>0){$showmsg.=" will also be removed as ".(count($msgbits)>1?"the are":"it is")." available only in conjunction with certain products.";}
		/*code to add message telling customer or additional product removals*/
		$skuvars="";
		foreach($cart['skuvariant'] as $ident => $newsku)
		{
			$expsku=explode("-qty-",$newsku);
			$skuvars.=(($skuvars!="")?",":"")."'".$expsku[0]."'";
		}
		$query=($cart['ispack']==1)?"SELECT * FROM products as p LEFT JOIN fusion as f ON f.iSubId_FK=p.prod_id WHERE p.prod_id='$cart[prod_id]' AND ".WHICHLIST." = 1;":"SELECT * FROM products as p LEFT JOIN fusion as f ON f.iSubId_FK=p.prod_id AND vtype='product' WHERE p.prod_id='$cart[prod_id]' AND ".WHICHLIST." = 1";
		$prodinfoq=mysql_query($query,$db);
		$prodinfo=mysql_fetch_assoc($prodinfoq);
		?>
		<tr>
			<td style="vertical-align:top"><? if($showremove==1){?>Quantity <input type="text" name="qty[<?=$id?>]" value="<?=$cart['qty']?>" style="width:20px;" class="formfield" /><? }else{echo $cart['qty']; }?><? if($cart['exclude_discount']==1){?><br /><dfn style="font-size:90%;font-style:italic;color:#CD071E">Discount exempt</dfn><? }?></td>
			<td style="vertical-align:top">
				<a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$prodinfo['fusionId']?>"><?=$prodinfo['title']." (".$prodinfo['sku'].")"?></a>
				<? if($cart['ispack']==0){$choice=variants($skuvars);?>
					<br /><?=((is_array($choice))?ucwords($choice['description']).": ".$choice['item_desc']:"")?>
				<? }else{?>
				<div class="pack_contents">
					<strong>Pack Contents</strong><br />
					<? foreach($cart['skuvariant'] as $prod_id => $skuvar)
					{
						$expsku=explode("-qty-",$skuvar);
						$packq=mysql_query("SELECT fusionId,title,item_qty FROM (products as p JOIN productkits as pk ON pk.iProdId_FK=p.prod_id) LEFT JOIN fusion as f ON f.iSubId_FK=p.prod_id AND vtype='product' WHERE p.prod_id='$prod_id' AND pk.kprod_id='$cart[prod_id]'",$db);
						$pack=mysql_fetch_assoc($packq);
						$pchoice=variants("'".$expsku[0]."'");
						?>
						<? if(strlen($pack['fusionId'])>0){?><a href="<?=$mainbase?>/index.php?p=products&amp;pid=<?=$pack['fusionId']?>"><? }?><?=$pack['title']?><? if(strlen($pack['fusionId'])>0){?></a><? }?> (<?=$pack['item_qty']?>)<br /><?=is_array($pchoice)?ucwords($pchoice['description']).": ".$pchoice['item_desc']:""?><br />
						<? 
					}?>
				</div>
				<? }?>
			</td>
			<? $price_n_vat=$cart['price']+(($cart['price']/100)*$vat);$price_n_vat_n_qty=$price_n_vat*$cart['qty'];$price_runnin+=$price_n_vat_n_qty;?>
			<td style="vertical-align:top;text-align:right"><span class="price"><?=$currarr[$domainext][2]?><?=number_format($price_n_vat,2)?></span></td>
			<td style="vertical-align:top;text-align:right"><span class="price"><?=$currarr[$domainext][2]?><?=number_format($price_n_vat_n_qty,2)?></span></td>
			<? if($showremove==1){?><td style="vertical-align:top;text-align:center"><a href="<?=$mainbase?>/index.php?p=shopping_basket&amp;remove_item=<?=$id?>" id="<?=$id?>"><img src="content/img/main/remove_item.bmp" alt="Remove" /></a>
			<script type="text/javascript">
			//<[CDATA[
			$('#<?=$id?>').click(function() {
				if(confirm('Are you sure you want to delete <?=$prodinfo['title'].((is_array($choice))?" - ".ucwords($choice['description']).": ".$choice['item_desc']:"")?>?<? if(strlen($showmsg)>0){?>\r\n\r\n<?=$showmsg?><? }?>')) return true;else return false;
			}
			)
			//]]>
			</script>
				</td><? }?>
		</tr>
		<?
	}
	?>
	<tr>
		<td colspan="3" style="text-align:right;border-top:6px solid #DDDDDD"><strong>Sub-Total</strong></td>
		<td style="text-align:right;border-top:6px solid #DDDDDD"><span class="price"><?=$currarr[$domainext][2]?><?=number_format($price_runnin,2)?></span></td>
		<? if($showremove==1){?><td style="border-top:6px solid #DDDDDD">&#160;</td><? }?>
	</tr>
	<? if(isset($_SESSION['discount_code'])&&strlen($_SESSION['discount_code'])>0){?>
	<tr>
		<td colspan="3" style="text-align:right"><strong><?=$_SESSION['discount_amount']?>% Discount (<?=$_SESSION['discount_code']?>)</strong></td>
		<td style="text-align:right"><span class="price">- <?=$currarr[$domainext][2]?><?=number_format($discount,2)?></span></td>
		<? if($showremove==1){?><td>&#160;</td><? }?>
	</tr>
	<? }?>
	<tr>
		<? list($postcost,$postdesc,$postid)=postagecalc($sub_total,$_SESSION['shipping']);?>		
		<td colspan="3" style="text-align:right"><strong>Postage Method: <?=$postdesc?></strong></td>
		<td style="text-align:right"><span class="price"><?=$currarr[$domainext][2]?><?=number_format($postcost,2)?></span></td> 
		<? if($showremove==1){?><td>&#160;</td><? }?>
	</tr>
	<tr>
		<td colspan="3" style="text-align:right" class="head"><strong>Current Total</strong></td>
		<td style="text-align:right" class="head"><span class="price"><?=$currarr[$domainext][2]?><?=number_format($basket_total,2)?></span></td>
		<? if($showremove==1){?><td class="head">&#160;</td><? }?>
	</tr>
	</table>
	<div style="text-align:right"><dfn>Current Total includes VAT (@<?=$vat?>%) of <?=$currarr[$domainext][2]?><?=number_format($vattoadd,2)?></dfn></div>
	<?
}
function postagecalc($cost,$posttype)
{	
	global $db,$vat,$currarr,$domainext;
	if(isset($_SESSION['address_details']['delivery']['country']))
	{$which="availability LIKE '%-".$_SESSION['address_details']['delivery']['country']."-%'";}
	else{
		$which="availability LIKE '%-".DEFAULTDEL."-%'";
	}
	//$posttype 0 = free post
	$amount=number_format($cost+($vat*($cost/100)),2,".","");
	$return=array();
	$string="SELECT * FROM postage_methods as pm JOIN postage_method_details as pmd ON pm.post_id=pmd.post_id WHERE pmd.post_details_id='$posttype'
	";
	$postq=mysql_query($string,$db) or die("Mysql query '$string' failed with error:<br />".mysql_error());
	$post=mysql_fetch_assoc($postq);
	$post_descrip=htmlspecialchars($post['description'],ENT_QUOTES,"ISO-8859-1");
	switch($post['post_id'])
	{
		case 6://special rate
		case 7://free delivery
			$return=array($post['field3'.PRICECUR],$post['description'],$post['post_details_id']);
			if($_SESSION['shipping']==$posttype){$_SESSION['postdesc']=$post_descrip;}
			break;
		case 8://by weight
			$return=array($post['field3'.PRICECUR],$post['description'],$post['post_details_id']);
			if($_SESSION['shipping']==$posttype){$_SESSION['postdesc']=$post_descrip;}
			break;
		default://range		
			$thepostq=mysql_query("SELECT * FROM postage_methods as pm JOIN postage_method_details as pmd ON pm.post_id=pmd.post_id WHERE pmd.post_id='$post[post_id]' AND `field1".PRICECUR."` <= $amount AND (`field2".PRICECUR."` >= $amount OR `field2".PRICECUR."` < 1) AND ".$which." ORDER BY `field1".PRICECUR."` DESC",$db);
			$thepost=mysql_fetch_assoc($thepostq);
			if(mysql_num_rows($thepostq)<1)
			{				
				$return=array($post['field3'.PRICECUR],$post_descrip,$post['post_details_id']);
				if($_SESSION['shipping']==$posttype){$_SESSION['postdesc']=$post_descrip;}
			}
			else{
				$thepost_descrip=htmlspecialchars($thepost['description'],ENT_QUOTES,"ISO-8859-1").($thepost['field3'.PRICECUR]<1?" (On orders of ".$currarr[$domainext][2].number_format($thepost['field1'.PRICECUR]+0.01,0)." and over)":"");
				if($_SESSION['shipping']==$posttype){$_SESSION['postdesc']=$thepost_descrip;}
				$return=array($thepost['field3'.PRICECUR],$thepost_descrip,$thepost['post_details_id']);
			}
			break;
		//(($amount>$defaultpost['field2'])?$defaultpost['field1']:$defaultpost['field3'])
	}
	return $return;
}
function variants($skuvar)
{
	global $db,$_GET;
	$str="SELECT description,item_desc FROM option_values as ov,product_options as po WHERE po.opt_id=ov.opt_id AND variant_id IN(".$skuvar.")";
	$svq=mysql_query($str,$db)/* or die("Error on query: $str<br />".mysql_error())*/;
	if(isset($_GET['showerr'])&&mysql_error()){echo "Error on query: ".$str."<br />".mysql_error(); }
	$svn=mysql_num_rows($svq);
	$sv=mysql_fetch_assoc($svq);
	return $sv;
}
function redirection($url)
{
	if (!headers_sent())
		header('Location: '.$url);
	else
	{
		echo '<script type="text/javascript">';
		echo 'window.location.href="'.$url.'";';
		echo '</script>';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
		echo '</noscript>';
	}
}
function hashandsalt($in1,$in2)
{
	$salt=hash("sha256",$in1.$in2);
	return hash("sha256",$in2.$salt);
}
function getparents($pid)
{
	global $mainbase,$db;
	$parent="";
	if($pid!=""&&$pid!=0)
	{
		$pquery="SELECT * FROM categories,fusion WHERE iSubId_FK='$pid' AND fusion.iSubId_FK=categories.cat_id AND vType='department' AND vOwnerType='department' LIMIT 1";
		$parents_query=mysql_query($pquery,$db);
		$parents=mysql_fetch_assoc($parents_query);
		$parents_num=mysql_num_rows($parents_query);
		if($parents_num>0)
		{		
			if($parents['iOwner_FK']!=0){$parent.=getcrumbs($parents['iOwner_FK']);}//recursively get parents
				$parent.="/ $parents[title] ";
		}
	}
	return $parent;
}
function highlighterrors($errorarray,$field)
{
	$dohighlight="";
	if(in_array($field,$errorarray)){$dohighlight="style='border:1px solid red;'"; }
	return $dohighlight;
}
function idhighlighterrors($errorarray,$field,$elementid)
{
	if(in_array($field,$errorarray)){foreach($elementid as $cssid){?><style type="text/css">#<?=$cssid?> {border:1px solid red;}</style><? }}
}
function theparent($fusionId)
{
	global $db;
	$q=mysql_query("SELECT iOwner_FK,vOwnerType FROM fusion WHERE fusionId='$fusionId'",$db);
	list($owner,$type)=mysql_fetch_row($q);
	$qq=mysql_query("SELECT fusionId FROM fusion WHERE iSubId_FK='$owner' AND vtype='$type'",$db);
	list($fuse)=mysql_fetch_row($qq);
	return $fuse;
}

function parentsstring($fusionId,$loop)
{
	global $db;
	if($loop==0){$string="";}
	$q=mysql_query("SELECT iOwner_FK,vOwnerType FROM fusion WHERE fusionId='$fusionId'",$db);
	list($owner,$type)=mysql_fetch_row($q);
	$pp=mysql_query("SELECT fusionId,iOwner_FK,iSubId_FK FROM fusion WHERE iSubId_FK='$owner' AND vtype='$type'",$db);
	$pn=mysql_num_rows($pp);
	$qq=mysql_fetch_assoc($pp);
	if($pn>0){
		$string.=implode(",",$qq);
		if($qq['iOwner_FK']!=0){$string.="#".parentsstring($qq['fusionId'],$loop++);}//recursively get parents
	}
	return $string;
}
function parentsarray($fusionId,$loop)
{
	$fusearray=array();
	$string=parentsstring($fusionId,0);
	if($string!=""){$fusearray=explode("#",$string);}
	foreach($fusearray as $id => $commas)
	{
		$exp=explode(",",$commas);
		$fusearray[$id]=array("fid"=>$exp[0],"own"=>$exp[1],"sub"=>$exp[2]);
	}
	return $fusearray;
}
function relative_date($format,$time) 
{
	$today = strtotime(date('M j, Y'));
	$reldays = ($time - $today)/86400;
	if ($reldays >= 0 && $reldays < 1) {
			return 'today';
	} else if ($reldays >= 1 && $reldays < 2) {
			return 'tomorrow';
	} else if ($reldays >= -1 && $reldays < 0) {
			return 'yesterday';
	}
	if (abs($reldays) < 7) {
			if ($reldays > 0) {
					$reldays = floor($reldays);
					return 'in ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
			} else {
					$reldays = abs(floor($reldays));
					return $reldays . ' day'  . ($reldays != 1 ? 's' : '') . ' ago';
			}
	}
	if (abs($reldays) < 182) {
			return date($format,$time);
	} else {
			return date($format.(stristr(strtolower($format),"y")?"":", Y"),$time);//add year if not already included
	}
}//'l, F j, Y'

function is_selected($field,$id,$value,$postdata,$type)
{
	$selected=($type=="check")?"checked='checked'":"selected='selected'";
	if(is_array($postdata)&&is_array($postdata[$field]))
	{
		return (array_key_exists($field,$postdata)&&strtolower($postdata[$field][$id])==strtolower($value))?$selected:"";
	}
	else if(is_array($postdata))
	{
		return (array_key_exists($field,$postdata)&&strtolower($postdata[$field])==strtolower($value))?$selected:"";
	}
}
function posted_value($field,$id,$alt,$postdata)
{
	if(isset($postdata[$field])){return (is_array($postdata[$field]))?$postdata[$field][$id]:$postdata[$field];}
	else{return $alt;}
}
function geocode($address)
{
	$xml="";
	$f = fopen( 'http://maps.googleapis.com/maps/api/geocode/xml?address='.urlencode($address).'&sensor=false', 'r' );
	if(!$f){echo "error";}
	while( $data = fread( $f, 4096 ) ) { $xml .= $data; }
	fclose( $f );
	preg_match_all( "/\<location\>(.*?)\<\/location\>/s", $xml, $locations );
	foreach($locations[1] as $location)
	{
		preg_match_all( "/\<lng\>(.*?)\<\/lng\>/", $location, $lng );
		preg_match_all( "/\<lat\>(.*?)\<\/lat\>/", $location, $lat );
	}
	return array($lat[1][0],$lng[1][0]);
}
function mapmarkers($info,$inc)
{
	global $latlng_daterenew;
	$dblatlng=1;
	$address=ucwords(strtolower($info['Address1'])).",";
	if(strlen($info['Address2'])>0){$address.=ucwords(strtolower($info['Address2'])).",";}
	if(strlen($info['City'])>0){$address.=ucwords(strtolower($info['City'])).",";}
	if(strlen($info['County'])>0){$address.=ucwords(strtolower($info['County'])).",";}
	if(strlen($info['Postcode'])>0){$address.=$info['Postcode'].",";}
	$address.="UK";
	if($info['acid']==null||$info['dateadded']<date("U")-(86400*$latlng_daterenew))
	{
		$latlng=geocode($address);
		if(strlen($latlng[0])<1){$latlng=geocode($info['Address1'].",".$info['Postcode'].",UK");}
		if(strlen($latlng[0])>0){mysql_query("INSERT INTO global.dealerlistings_latlng(acid,lat,lng,dateadded)VALUES('".$info['accountid']."','".$latlng[0]."','".$latlng[1]."','".date("U")."')");$dblatlng=0;}
	}
	if($dblatlng==1)
	{
		$latlng=array($info['lat'],$info['lng']);
	}
	$img="red";//$info['Lafuma_p'] == "True"?"red":"orange";
	$infocontent="<div><strong>".htmlspecialchars($info['Account'],ENT_QUOTES)."<\/strong>";
	if ($info['Lafuma_p'] == "True") {
		$infocontent.="<span style='color:#0091B6; font-weight:bold;'>P<\/span>";
	}
	$infocontent.="<br \/>".str_replace(",",", ",htmlspecialchars($address,ENT_QUOTES));
	if(strlen($info['Mainphone'])>0){$infocontent.="<br \/>Tel: ".htmlspecialchars($info['Mainphone'],ENT_QUOTES);}
	if(strlen($info['Email'])>0){$infocontent.="<br \/>Email: <a href=\"mailto:".htmlspecialchars($info['Email'],ENT_QUOTES)."\">".htmlspecialchars($info['Email'],ENT_QUOTES)."<\/a>";}
	if(strlen($info['Webaddress'])>0&&$info['Webaddress']!='0'){$infocontent.="<br \/>Web: <a href=\"http://".str_replace("http://","",htmlspecialchars($info['Webaddress'],ENT_QUOTES))."\" target=\"_blank\">".htmlspecialchars($info['Webaddress'],ENT_QUOTES)."<\/a>";}
	$infocontent.="<\/div>";
	?>
	<script type="text/javascript">
	//<![CDATA[
	var <?=str_replace(" ","",preg_replace("/[^a-zA-Z0-9\s]/", "", htmlspecialchars($info['Account'],ENT_QUOTES)))?> = new google.maps.LatLng(<?=$latlng[0]?>, <?=$latlng[1]?>);
	addMarker(<?=str_replace(" ","",preg_replace("/[^a-zA-Z0-9\s]/", "", htmlspecialchars($info['Account'],ENT_QUOTES)))?>,'content/img/mapmarkers/<?=$img?>/<?=$inc?>.png','<?=htmlspecialchars($info['Account'],ENT_QUOTES)?>','<?=$infocontent?>');
	//]]>
	</script>
	<?
}
function whiteboxSTART($width,$height)
{
	$width=strlen($width)>0?"width:".$width."px;":"";
	$height=strlen($height)>0?"height:".$height."px;":"";/*
	?>
	<div style="<?=$width?>height:4px;background:url(content/img/main/search-t.jpg) repeat-x top left">
		<div style="float:left;width:6px;height:4px;vertical-align:top"><img src="content/img/main/search-tl.jpg" alt="" style="vertical-align:top" /></div>
		<div style="float:right;width:5px;height:4px;vertical-align:top"><img src="content/img/main/search-tr.jpg" alt="" style="vertical-align:top" /></div>
		<div class="clear"></div>
	</div>
	<div style="<?=$width?><?=$height?>background:#FFF;">
		<div style="float:left;<?=$height?>background:url(content/img/main/search-l.jpg) repeat-y top left"><img src="content/img/main/search-l.jpg" alt="" /></div>
		<div style="float:left;<?=$height?>">
		<?
	*/
	?>
	<span class="b1"></span><span class="b2"></span><span class="b3"></span><span class="b4"></span>
    <div class="contentb">
        <div style="<?=$height?>"><?
}
function whiteboxEND($width,$height)
{
	$width=strlen($width)>0?"width:".$width."px;":"";
	$height=strlen($height)>0?"height:".$height."px;":"";/*
		?>
		</div>
		<div style="float:right;<?=$height?>background:url(content/img/main/search-r.jpg) repeat-y top left"><img src="content/img/main/search-r.jpg" alt="" /></div>
		<div class="clear"></div>
	</div>
	<div style="<?=$width?>height:3px;background:url(content/img/main/search-b.jpg) repeat-x top left">
		<div style="float:left;width:6px;height:3px;vertical-align:top"><img src="content/img/main/search-bl.jpg" alt="" style="vertical-align:top" /></div>
		<div style="float:right;width:5px;height:3px;vertical-align:top"><img src="content/img/main/search-br.jpg" alt="" style="vertical-align:top" /></div>
		<div class="clear"></div>
	</div>
	<?
	*/
	?></div>
    </div>
<span class="b4"></span><span class="b3"></span><span class="b2"></span><span class="b1"></span><?
}
function mysql_real_extracted($inputarray)
{
	$extracted_array=array();
	foreach($inputarray as $field => $value)
	{
		$extracted_array[$field]=is_array($value)?mysql_real_extracted($value):mysql_real_escape_string($value);
	}
	return $extracted_array;
}
function lamysql_error($error,$query)
{
	echo "<div class='notify'><span style='text-decoration:underline'>There was an error with the MYSQL query</span><br /><br /><span>Error:</span> &quot;$error&quot;";
	if(0)
	{
		echo "<br /><br /><span>Query:</span><br />$query</div>";
	}
}
function checkprodstock($pid,$kit)
{
	$stockcheck=0;
	if($kit==1 || $kit ==2)
	{
		$stockcheckQ=mysql_query("SELECT SUM(ns.nav_qty) FROM
 (((
(products as p JOIN fusion as f ON f.`iSubId_FK`=p.`prod_id` AND `iState`='1')
 JOIN productkits as pk ON pk.`kprod_id`=p.`prod_id`)
 JOIN fusion_options as fo ON fo.`prod_id`=pk.`iProdId_FK`)
 JOIN option_values as ov ON ov.opt_id=fo.opt_id)
 JOIN nav_stock as ns ON ns.nav_skuvar=ov.variant_id
 WHERE f.`fusionId`=".$pid." AND p.`".WHICHLIST."`='1'");
	}
	else
	{
		$stockcheckQ=mysql_query("SELECT SUM(ns.`nav_qty`) FROM
	 (((products as p JOIN fusion as f ON f.`iSubId_FK`=p.`prod_id` AND `iState`='1') JOIN fusion_options as fo ON fo.`prod_id`=p.`prod_id`)
	 JOIN option_values as ov ON ov.`opt_id`=fo.`opt_id`)
	 JOIN nav_stock as ns ON ns.`nav_skuvar`=ov.`variant_id`
	 WHERE f.`fusionId`=".$pid." AND `".WHICHLIST."`='1'");
	}
	list($stockcheck)=mysql_fetch_row($stockcheckQ);
	return $stockcheck;
}

?>