<? $postid=isset($_GET['postid'])?$_GET['postid']:"";?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if($postid!=""){?><a href="<?=$self?>"><? }?>Postage &amp; Packing<? if($postid!=""){?></a> &#187; Editing postage options<? }?></div>
<div id="main">
	<h2 id="pagetitle">Postage &amp; Packing</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<? 
		switch($action){
			case "edit":
				?>
				<form action="<?=$self?>&amp;act=edit&amp;postid=<?=$postid?><?=isset($_GET['addrow'])?'&amp;addrow=1':''?>" method="post">
				<table class="details">
				<?
				$titleadded=0;
				$pdetailsq=mysql_query("SELECT * FROM postage_methods as pm LEFT JOIN postage_method_details as pmd ON  pm.post_id=pmd.post_id WHERE pm.post_id='$postid'");
				$coupostarr=array();
				$coupostq=mysql_query("SELECT cshortname FROM countries");
				$on=array();
				while($pdetails=mysql_fetch_assoc($pdetailsq))
				{
					$pdid=$pdetails['post_details_id'];
					$pdesc=posted_value("description",$pdid,$pdetails['description'],$_POST);
					$prestraints=posted_value("restraints",$pdid,$pdetails['restraints'],$_POST);
					$opts=posted_value("options",$pdid,$pdetails['options'],$_POST);
					$pfield1=posted_value("field1",$pdid,$pdetails['field1'],$_POST);
					$pfield2=posted_value("field2",$pdid,$pdetails['field2'],$_POST);
					$pfield3=posted_value("field3",$pdid,$pdetails['field3'],$_POST);
					$pfield1_euro=posted_value("field1_euro",$pdid,$pdetails['field1_euro'],$_POST);
					$pfield2_euro=posted_value("field2_euro",$pdid,$pdetails['field2_euro'],$_POST);
					$pfield3_euro=posted_value("field3_euro",$pdid,$pdetails['field3_euro'],$_POST);
					$countrypostage=explode(",",$pdetails['availability']);//array("-GB-","-IE-")
					if($titleadded==0)
					{
						$titleadded=1;
						?>
						<tr>
							<td class="head" colspan="<?=7+mysql_num_rows($coupostq)?>"><div class="titles"><?=$titleback." ".helplink($page)?> <?=ucwords($pdetails['methodname'])?></div><div class="links"><a href="<?=$self?>&amp;act=edit&amp;postid=<?=$postid?>&amp;addrow=1">Add new row</a></div></td>
						</tr>
						<tr>
							<td colspan="<?=7+mysql_num_rows($coupostq)?>"><?=$pdetails['helptext']?></td>
						</tr>
						<tr>
							<td class="subhead" style="width:5%">ID</td>
							<td class="subhead" style="width:<? if($postid==6){?>30<? }else{?>53<? }?>%">Description</td>
							<td class="subhead" style="width:<? if($postid==6){?>15<? }else{?>12<? }?>%"><? if($postid==6){?>Special<? }else{?>Range Start<? }?></td>
							<? if($postid==6){?><td class="subhead" style="width:20%">Options</td><? }?>
							<td class="subhead" style="width:10%"><? if($postid==6){?>First item<? }else{?>Range End<? }?></td>
							<td class="subhead" style="width:10%"><? if($postid==6){?>Each additional<? }else{?>Value<? }?></td>
							<? while(list($coupost)=mysql_fetch_row($coupostq)){?>
							<td class="subhead" style="width:5%"><?=$coupost?> Post</td>
							<? }?>
							<td class="subhead" style="width:5%">Delete</td>
						</tr>
						<? 
					}
					?>
					<tr>
						<td><?=$pdetails['post_details_id']?><input type="hidden" name="pdid[]" value="<?=$pdid?>" /></td>
						<td><input type="text" name="description[<?=$pdid?>]" value="<?=htmlentities($pdesc,ENT_QUOTES,"UTF-8")?>" class="formfield" style="width:<?=$postid==5?"260":"170"?>px" <?=highlighterrors($higherr,"description_".$pdid)?> /></td>
						<? if($postid==6){?>
							<td>Before 
							<? $rvals=explode("#",$prestraints);$rvaldaytime=stristr($rvals[2]," ")!=null?explode(" ",$rvals[2]):array('0'=>'','1'=>$rvals[2]);?>
							<input type="hidden" name="restraint0[<?=$pdid?>]" value="time" />
							<input type="hidden" name="restraint1[<?=$pdid?>]" value="before" />
							<!--<select name="restraint1[<?//=$pdetails['post_details_id']?>]">
							<option value="">-When-</option>
							<option value="before" <? //if($rvals[1]=="before"){?>selected="selected"<? //}?>>Before</option>
							<option value="after" <? //if($rvals[1]=="after"){?>selected="selected"<? //}?>>After</option>
							</select>-->
							<select name="restraint2[<?=$pdid?>]">
							<option value="">-Day-</option>
							<? for($x=0;$x<7;$x++){?><option value="<?=ucwords($daysofweek[$x])?>" <? if(ucwords($daysofweek[$x])==$rvaldaytime[0]){?>selected="selected"<? }?>><?=ucwords($daysofweek[$x])?></option><? }?>
							</select>
							<select name="restraint3[<?=$pdid?>]">
							<option value="">-Time-</option>
							<? for($x=0;$x<24;$x++){?><option value="<?=($x<9?'0':'').$x?>:00" <? if(($x<9?'0':'').$x.":00"==$rvaldaytime[1]){?>selected="selected"<? }?>><?=$x?>:00</option><? }?>
							</select>
							</td>
							<td><input type="text" name="options[<?=$pdid?>]" value="<?=$opts?>" class="formfieldm" <?=highlighterrors($higherr,"options_".$pdid)?> /></td>
							<td>&#163;<input type="text" name="field1[<?=$pdid?>]" value="<?=$pfield1?>" class="formfields" <?=highlighterrors($higherr,"field1_".$pdid)?> /><br />&#8364;<input type="text" name="field1_euro[<?=$pdid?>]" value="<?=$pfield1_euro?>" class="formfields" <?=highlighterrors($higherr,"field1_euro_".$pdid)?> /></td>
							<td>&#163;<input type="text" name="field2[<?=$pdid?>]" value="<?=$pfield2?>" class="formfields" <?=highlighterrors($higherr,"field2_".$pdid)?> /><br />&#8364;<input type="text" name="field2_euro[<?=$pdid?>]" value="<?=$pfield2_euro?>" class="formfields" <?=highlighterrors($higherr,"field2_euro_".$pdid)?> /><input type="hidden" name="field3[<?=$pdid?>]" value="<?=$pfield1?>" /><input type="hidden" name="field3_euro[<?=$pdid?>]" value="<?=$pfield1_euro?>" /></td>
						<? }else{?>
							<td><dfn>Between</dfn><br />&#163;<input type="text" name="field1[<?=$pdid?>]" value="<?=$pfield1?>" class="formfields" <?=highlighterrors($higherr,"field1_".$pdid)?> /><br />&#8364;<input type="text" name="field1_euro[<?=$pdid?>]" value="<?=$pfield1_euro?>" class="formfields" <?=highlighterrors($higherr,"field1_euro_".$pdid)?> /></td>
							<td><dfn>and</dfn><br />&#163;<input type="text" name="field2[<?=$pdid?>]" value="<?=$pfield2?>" class="formfields" <?=highlighterrors($higherr,"field2_".$pdid)?> /><br />&#8364;<input type="text" name="field2_euro[<?=$pdid?>]" value="<?=$pfield2_euro?>" class="formfields" <?=highlighterrors($higherr,"field2_euro_".$pdid)?> /></td>
							<td><dfn>charge</dfn><br />&#163;<input type="text" name="field3[<?=$pdid?>]" value="<?=$pfield3?>" class="formfields" <?=highlighterrors($higherr,"field3_".$pdid)?> /><br />&#8364;<input type="text" name="field3_euro[<?=$pdid?>]" value="<?=$pfield3_euro?>" class="formfields" <?=highlighterrors($higherr,"field3_euro_".$pdid)?> /></td>
						<? }?>
						<? mysql_data_seek($coupostq,0);while(list($coupost)=mysql_fetch_row($coupostq)){
						$cselected=in_array("-".$coupost."-",$countrypostage)||$_POST['avail'][$pdid]==1?"checked='checked'":"";
						?>
							<td><input type="hidden" name="avail[<?=$pdid?>][<?=$coupost?>]" value="0" /><input type="checkbox" name="avail[<?=$pdid?>][<?=$coupost?>]" value="1" <?=$cselected?> /></td>
						<? }?>			
						
						<td><input type="checkbox" name="delete[<?=$pdid?>]" value="1" /></td>
					</tr>
					<?
				}
				if(isset($_GET['addrow']))
				{
					?>
					<tr>
						<td>New type<input type="hidden" name="pdid[]" value="new" /></td>
						<td><input type="text" name="description[new]" value="<?=posted_value("description","new","",$_POST)?>" class="formfield" <?=highlighterrors($higherr,"description_new")?> /></td>
						<? if($postid==6){?>
							<td>Before 
								<input type="hidden" name="restraint0[new]" value="time" />
								<input type="hidden" name="restraint1[new]" value="before" />
								<!--<select name="restraint1[new]">
								<option value="">-When-</option>
								<option value="before">Before</option>
								<option value="after">After</option>
								</select>-->
								<select name="restraint2[new]">
								<option value="">-Day-</option>
								<? for($x=0;$x<7;$x++){?><option value="<?=ucwords($daysofweek[$x])?>" <? if(ucwords($daysofweek[$x])==$rvaldaytime[0]){?>selected="selected"<? }?>><?=ucwords($daysofweek[$x])?></option><? }?>
								</select>
								<select name="restraint3[new]">
								<option value="">-Time-</option>
								<? for($x=0;$x<24;$x++){?><option value="<?=($x<9?'0':'').$x?>:00" <? if(($x<9?'0':'').$x.":00"==$rvaldaytime[1]){?>selected="selected"<? }?>><?=$x?>:00</option><? }?>
								</select>
							</td>
							<td>&#163;<input type="text" name="field1[new]" value="<?=posted_value("field1","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field1_new")?> /><br />&#8364;<input type="text" name="field1_euro[new]" value="<?=posted_value("field1_euro","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field1_euro_new")?> /></td>
							<td>&#163;<input type="text" name="field2[new]" value="<?=posted_value("field2","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field2_new")?> /><br />&#8364;<input type="text" name="field2_euro[new]" value="<?=posted_value("field2_euro","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field2_euro_new")?> /></td>
						<? }else{?>
							<td><dfn>Between</dfn><br />&#163;<input type="text" name="field1[new]" value="<?=posted_value("field1","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field1_new")?> /><br />&#8364;<input type="text" name="field1_euro[new]" value="<?=posted_value("field1_euro","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field1_euro_new")?> /></td>
							<td><dfn>and</dfn><br />&#163;<input type="text" name="field2[new]" value="<?=posted_value("field2","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field2_new")?> /><br />&#8364;<input type="text" name="field2_euro[new]" value="<?=posted_value("field2_euro","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field2_euro_new")?> /></td>
							<td><dfn>charge</dfn><br />&#163;<input type="text" name="field3[new]" value="<?=posted_value("field3","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field3_new")?> /><br />&#8364;<input type="text" name="field3_euro[new]" value="<?=posted_value("field3_euro","new","",$_POST)?>" class="formfields" <?=highlighterrors($higherr,"field3_euro_new")?> /></td>
						<? }?>
						<? mysql_data_seek($coupostq,0);while(list($coupost)=mysql_fetch_row($coupostq)){
						$cselected=$_POST['avail'][$pdid]==1?"checked='checked'":"";
						?>
							<td><input type="hidden" name="avail[new][<?=$coupost?>]" value="0" /><input type="checkbox" name="avail[new][<?=$coupost?>]" value="1" <?=$cselected?> /></td>
						<? }?>	
						<td>&#160;</td>
					</tr>
					<?
				}
				?>
				</table>
				<p class="submit"><input type="submit" value="Save Changes" /></p>
				</form>
				<?
				break;
			default:
				$status=array();
				$ppq=mysql_query("SELECT post_id,status FROM postage_methods WHERE post_id IN('5','6','7')");
				while($pp=mysql_fetch_row($ppq)){$status[$pp[0]]=$pp[1];}
				?>
				<form action="<?=$self?>" method="post">
				<table class="details">
					<tr>
						<td class="head" colspan="3"><?=helplink($page)?> Postage &amp; Packing</td>
					</tr>
					<tr>
						<td class="subhead">Status</td>
						<td class="subhead">Postage method</td>
						<td class="subhead">Description</td>
					</tr>
					<tr>
						<td><input type="radio" name="status[]" value="5" <?=$status[5]==1?"checked='checked'":""?> /></td>
						<td><a href="<?=$self?>&amp;act=edit&amp;postid=5">Postal Range</a></td>
						<td>Postage charge based on range of total order</td>
					</tr>
					<tr>
						<td><input type="radio" name="status[]" value="7" <?=$status[7]==1?"checked='checked'":""?> /></td>
						<td>No Postage</td>
						<td>All orders will have Free postage</td>
					</tr>
					<tr>
						<td class="subhead" colspan="3">Postage options below can work in conjunction with any method above</td>
					</tr>
					<tr>
						<td><input type="checkbox" name="status[]" value="6" <?=$status[6]==1?"checked='checked'":""?> /></td>
						<td><a href="<?=$self?>&amp;act=edit&amp;postid=6">Special Rate</a></td>
						<td>Setup special rates which users can select during checkout</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" value="Apply Changes" /></p>
				</form>
				<?
				break;
		}?>
		<!-- /CONTENT -->
	</div>
</div>