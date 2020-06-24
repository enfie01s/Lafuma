<? if(basename($_SERVER['PHP_SELF'])!="admin.php"){header("Location: ../admin.php");}//direct access security ?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if(strlen($action)>0){?><a href="<?=$self?>"><? }?>Promotions<? if(strlen($action)>0){?></a><? }?></div>
<div id="main">
	<h2 id="pagetitle">Promotions</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<? 
		switch($action){
			case "add":
			case "edit":
				$query=mysql_query("SELECT * FROM discounts WHERE discount_id='".$_GET['discount_id']."'");$result=mysql_fetch_assoc($query);
				if(!isset($_SESSION['promotions'])){$_SESSION['promotions']=array();}
				if(!isset($_SESSION['promotions']['postdata'])||isset($_POST['formid'])){$_SESSION['promotions']['postdata']=isset($_POST['formid'])?$_POST:$result;}
				
				$postdata=$_SESSION['promotions']['postdata'];
				$code=posted_value("code","","",$postdata);
				$discount=posted_value("discount","","",$postdata);
				$mintotal=posted_value("mintotal","","0",$postdata);
				$mintotal_euro=posted_value("mintotal_euro","","0",$postdata);
				$prodlist=posted_value("prodlist","","",$postdata);//1,34,28
				$prodlistarr=strlen($prodlist)>0?explode(",",$prodlist):array();
				$startraw=posted_value("date_start","",date("U"),$postdata);//not posted - result in unix, default in unix
				$start=isset($postdata['formid'])?$startraw:date("Y-m-d",$startraw);
				$endraw=posted_value("date_end","",date("U"),$postdata);
				$end=isset($postdata['formid'])?$endraw:date("Y-m-d",$endraw);
				$on_uk_list=$action=="edit"||isset($postdata['formid'])?is_selected("on_uk_list","","1",$postdata,"check"):"";
				$on_ie_list=$action=="edit"||isset($postdata['formid'])?is_selected("on_ie_list","","1",$postdata,"check"):"";
				$uselist=$action=="edit"||isset($postdata['formid'])?is_selected("uselist","","1",$postdata,"check"):"";
				$owned=!isset($_POST['dept'])||isset($_POST['backtodept'])?0:$_POST['dept'];
				?>
				<form action="<?=$self."&amp;act=".$action.(isset($_GET['discount_id'])?"&amp;discount_id=".$_GET['discount_id']."":"")?>" method="post">
				<input type="hidden" name="formid" value="<?=$action?>" />
				<input type="hidden" name="admin_id" value="<?=$uaa['admin_id']?>" />
				<input type="hidden" name="requiredf" value="code,discount,date_start,date_end" />
				<input type="hidden" name="requiredv" value="Discount code must be filled in,You must enter a discount value,Please specify a start date,Please specify the end date" />
				<table class="details">
					<tr>
						<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> <?=ucwords($action)?>ing Promotion</td>
					</tr>
					<tr>
						<td>Discount code</td>
						<td><input type="text" name="code" value="<?=$code?>" class="formfield" <?=highlighterrors($higherr,"code")?> /></td>
					</tr>
					<tr>
						<td>Discount value</td>
						<td><input type="text" name="discount" value="<?=$discount?>" class="formfields" <?=highlighterrors($higherr,"discount")?> />%</td>
					</tr>
					<tr>
						<td>Minimum order</td>
						<td>&#163;<input type="text" name="mintotal" value="<?=$mintotal?>" class="formfields" <?=highlighterrors($higherr,"mintotal")?> /> &#8364;<input type="text" name="mintotal_euro" value="<?=$mintotal_euro?>" class="formfields" <?=highlighterrors($higherr,"mintotal_euro")?> /></td>
					</tr>
					<tr>
						<td>Start date <dfn>(YYYY-MM-DD)</dfn></td>
						<td><input type="date" name="date_start" value="<?=$start?>" class="formfieldm" min="<?=date("Y-m-d",strtotime("1 year ago"))?>" <?=highlighterrors($higherr,"date_start")?> /></td>
					</tr>
					<tr>
						<td>End date <dfn>(YYYY-MM-DD)</dfn></td>
						<td><input type="date" name="date_end" value="<?=$end?>" class="formfieldm" <?=highlighterrors($higherr,"date_end")?> /></td>
					</tr>
					<tr>
						<td>On/Off</td>
						<td><input type="checkbox" name="on_uk_list" id="on_uk_list_label" value="1" <?=$on_uk_list?> /><label for="on_uk_list_label">UK Site</label> <input type="checkbox" name="on_ie_list" id="on_ie_list_label" value="1" <?=$on_ie_list?> /><label for="on_ie_list_label">IE Site</label></td>
					</tr>
					<tr>
						<td>Use products list <dfn>(Defaults to global if list is empty)</dfn></td>
						<td><input type="checkbox" name="uselist" value="1" <?=$uselist?> /></td>
					</tr>
					<tr>
						<td class="subhead"><?=!isset($_POST['dept'])||isset($_POST['backtodept'])?"Departments":"Products"?></td>
						<td class="subhead">Selected products list</td>
					</tr>
					<tr>
						<td>
						<? if(!isset($_POST['dept'])||isset($_POST['backtodept'])){
							$deptidlist=array();
							$deptidsQ=mysql_query("SELECT fusionId,iOwner_FK,cat_id,c.title FROM (products as p JOIN fusion as f on p.prod_id=f.iSubId_FK AND vOwnerType='department' AND vType='product' AND exclude_discount!='1') LEFT JOIN categories as c ON f.iOwner_FK=c.cat_id GROUP BY iOwner_FK ORDER BY iOwner_FK");
							while($deptids=mysql_fetch_assoc($deptidsQ))
							{
								$deptidlist[]=$deptids['iOwner_FK'];
							}
							?>
								<select name="dept" style="width:350px" size="10">
									<? if(in_array('0',$deptidlist)){?><option value="0" <? if($owned==0){?>selected="selected"<? }?>>Home Page</option><? }?>
									<?
									$deptsQ=mysql_query("SELECT fusionId,iOwner_FK,cat_id,title FROM categories as c JOIN fusion as f ON f.iSubId_FK=c.cat_id AND vtype='department' WHERE c.cat_id IN('".implode("','",$deptidlist)."') AND displayed=1 AND iState=1 ORDER BY iOwner_FK,cat_id");
									while($depts=mysql_fetch_assoc($deptsQ))
									{
										?>
										<option value="<?=$depts['cat_id']?>" <? if($owned==$depts['cat_id']){?>selected="selected"<? }?>>
										<? if($depts['iOwner_FK']!=0){echo getparents($depts['iOwner_FK']); }?> / <?=$depts['title']?></option><?
									}
									?>
								</select>
								<div style="text-align:center"><input type="submit" name="submitdept" class="formbutton" value="View Items" /></div>
											
						<? }else{
							$catparentq=mysql_query("SELECT fusionId,iOwner_FK,cat_id,title FROM categories as c JOIN fusion as f ON f.iSubId_FK=c.cat_id AND vtype='department' WHERE cat_id='$_POST[dept]'");
							$catparent=mysql_fetch_assoc($catparentq);
							?>
								<? if($owned!=0){?><input type="hidden" name="dept" value="<?=$owned?>" /><? }?>
								<select name="item[]" style="width:350px" multiple="multiple" size="10">
								<optgroup label="<? if($catparent['iOwner_FK']!=0){echo getparents($catparent['iOwner_FK'])." / "; }?><?=$catparent['title']?>">
								<?
								$itemsQ=mysql_query("SELECT fusionId,iOwner_FK,prod_id,title FROM products as p JOIN fusion as f ON f.iSubId_FK=p.prod_id AND vtype='product' AND vOwnerType='department' WHERE iOwner_FK='$_POST[dept]' AND exclude_discount!=1 ORDER BY iSort;");
								while($items=mysql_fetch_assoc($itemsQ))
								{
									?>
									<option value="<?=$items['prod_id']?>"><?=$items['title']?></option>
									<?
								}
								?>
								</optgroup>
								</select>
								<div style="text-align:center"><input type="submit" name="backtodept" class="formbutton" value="&#60;&#60; Back" /><input type="submit" name="submitdept" class="formbutton" value="Add &#62;&#62;" /></div>
						<? }?>
						</td>
						<td style="vertical-align:top">
							<?
							if(!isset($_SESSION['promotions']['items'])){$_SESSION['promotions']['items']=$prodlistarr;}
							if(isset($_POST['prodpop'])&&count($_POST['list'])>0)
							{
								foreach($_POST['list'] as $pop)
								{
									array_splice($_SESSION['promotions']['items'],array_search($pop,$_SESSION['promotions']['items']),1);
								}
							}
							if(isset($_POST['item']))
							{
								foreach($_POST['item'] as $prod){
									array_push($_SESSION['promotions']['items'],$prod);$_SESSION['promotions']['items']=array_unique($_SESSION['promotions']['items']);sort($_SESSION['promotions']['items']);
								}
							}
							$prods=isset($_SESSION['promotions']['items'])?implode("','",$_SESSION['promotions']['items']):array();
							?>
							<? if($owned!=0){?><input type="hidden" name="dept" value="<?=$owned?>" /><? }?>
							<select name="list[]" style="width:350px" multiple="multiple" size="10">
							<?
							$selectedq=mysql_query("SELECT title,prod_id FROM products WHERE prod_id IN('$prods')");
							while($selected=mysql_fetch_assoc($selectedq))
							{
								?><option value="<?=$selected['prod_id']?>"><?=$selected['title']?></option><?
							}
							?>
							</select>
							<div style="text-align:center"><input type="submit" name="prodpop" class="formbutton" value="Remove from list" /></div>
							
							<input type="hidden" name="prodlist" value="<?=implode(",",$_SESSION['promotions']['items'])?>" />
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="updatediscount" value="<?=$action=="edit"?"Update":"Add"?> discount" /></p>
				</form>
				<?
				break;
			default:
				$query=mysql_query("SELECT * FROM discounts ORDER BY date_end ASC");$num=mysql_num_rows($query);?>
				<form action="<?=$self?>" method="post">
				<input type="hidden" name="formid" value="stateupdate" />
				<input type="hidden" name="admin_id" value="<?=$uaa['admin_id']?>" />
				<table class="details">
					<tr>
						<td class="head" colspan="9"><div class="titles"><?=helplink($page)?> Promotions</div><div class="links"><a href="<?=$self?>&amp;act=add">Add new discount</a></div></td>
					</tr>
					<tr>
						<td class="infohead" colspan="9"><?=$num?> records found</td>
					</tr>
					<tr>
						<td class="subhead" style="width:31%">Code</td>
						<td class="subhead" style="width:5%">Discount</td>
						<td class="subhead" style="width:10%;text-align:center">Start</td>
						<td class="subhead" style="width:10%;text-align:center">End</td>
						<td class="subhead" style="width:10%;text-align:center">Build Date</td>
						<td class="subhead" style="width:7%;text-align:center">UK Site</td>
						<td class="subhead" style="width:7%;text-align:center">IE Site</td>
						<td class="subhead" style="width:5%;text-align:center">Edit</td>
						<td class="subhead" style="width:5%;text-align:center">Delete</td>
					</tr>
					<?
					while($result=mysql_fetch_assoc($query))
					{
						$row=!isset($row)||$row==1?0:1;
						?>
						<tr class="row<?=$row?>">
							<td><?=$result['code']?></td>
							<td><?=$result['discount']?>%</td>
							<td style="text-align:center"><?=date("d/m/Y",$result['date_start'])?></td>
							<td style="text-align:center"><?=date("d/m/Y",$result['date_end'])?></td>
							<td style="text-align:center"><?=date("d/m/Y",$result['date_created'])?></td>
							<td style="text-align:center"><input type="hidden" name="on_uk_list[<?=$result['discount_id']?>]" value="0" /><input type="checkbox" name="on_uk_list[<?=$result['discount_id']?>]" value="1" <? if($result['on_uk_list']==1){?>checked="checked"<? }?> /></td>
							<td style="text-align:center"><input type="hidden" name="on_ie_list[<?=$result['discount_id']?>]" value="0" /><input type="checkbox" name="on_ie_list[<?=$result['discount_id']?>]" value="1" <? if($result['on_ie_list']==1){?>checked="checked"<? }?> /></td>
							<td style="text-align:center"><a href="<?=$self?>&amp;act=edit&amp;discount_id=<?=$result['discount_id']?>">Edit</a></td>
							<td style="text-align:center"><a href="<?=$self?>&amp;delete_id=<?=$result['discount_id']?>" onclick="javascript:return decision('Are you sure you wish to delete this promotion?', '<?=$self?>&amp;delete_id=<?=$result['discount_id']?>')">Delete</a></td>
						</tr>
						<?
					}
					?>
					<tr>
						<td class="infohead" colspan="9"><?=$num?> records found</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" value="Update status" /></p>
				</form>
				<?
				break;
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>