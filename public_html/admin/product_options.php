<?
if(basename($_SERVER['PHP_SELF'])!="admin.php"){header("Location: ../admin.php?p=builder");}//direct access security 

$theaction=((substr($action,-1,1)=="e")?substr($action,0,strlen($action)-1):$action)."ing";//adding/updating/deleting
?>

<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <?=(($action=="add"||$action=="edit")?"<a href='$self'>":"")?>Product Options<?=(($action=="add"||$action=="edit")?"</a>":"")?> <?=($action=="add"||$action=="edit")?"&#187; ".ucwords($theaction)." option":""?></div>
<div id="main">
	<h2 id="pagetitle">Standard Option Builder</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? }?>
		<!-- CONTENT -->
		<?
		switch($_GET['act'])
		{
			case "add":
			case "edit":
				if(!isset($_POST['inum'])&&$_GET['act']=="add")//how many items?
				{
					?>
					<table cellpadding="0" cellspacing="1" class="details" width="80%">
					<tr>
						<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> Product Options</td>
					</tr>
					<tr>
						<td>Enter the number of items there are for this option</td>
						<td>
							<form action="<?=$self?>&amp;act=add" method="post">
							<input type="text" name="inum" class="formfieldm" value="" />
							<input type="submit" name="submit" class="formbutton" value="Build Option" />
							</form>
						</td>
					</tr>
					</table>
					<?
				}
				else//option builder
				{
					$itemarr=array();
					$vararray=array();
					$vararray['variant_id']=array();
					$vararray['item_desc']=array();
					$vararray['price']=array();
					$lastsort=0;
					if(isset($_GET['opt_id'])){
						$itemarrQ=mysql_query("SELECT * FROM product_options as po WHERE po.opt_id='$_GET[opt_id]'");
						$itemarr=mysql_fetch_assoc($itemarrQ);
						$valuesQ=mysql_query("SELECT * FROM option_values as ov WHERE ov.opt_id='$itemarr[opt_id]' ORDER BY vsort");
						$inum=mysql_num_rows($valuesQ);
						while($values=mysql_fetch_assoc($valuesQ))
						{
							$vararray['variant_id'][$values['optval_id']]=$values['variant_id'];
							$vararray['item_desc'][$values['optval_id']]=$values['item_desc'];
							$vararray['price'][$values['optval_id']]=$values['price'];
							$vararray['price_euro'][$values['optval_id']]=$values['price_euro'];
							$vararray['img_filename'][$values['optval_id']]=$values['img_filename'];
							$vararray['vsort'][$values['optval_id']]=$values['vsort'];
							$lastsort=$values['vsort'];
						}
						if(isset($_GET['addrow'])||(isset($_POST['inum'])&&$inum<$_POST['inum']))
						{
							$vararray['variant_id']['addrow']=(isset($_POST['variant_id']))?$_POST['variant_id']['addrow']:"";
							$vararray['item_desc']['addrow']=(isset($_POST['item_desc']))?$_POST['item_desc']['addrow']:"";
							$vararray['price']['addrow']=(isset($_POST['price']))?$_POST['price']['addrow']:"0";
							$vararray['price_euro']['addrow']=(isset($_POST['price_euro']))?$_POST['price_euro']['addrow']:"0";
							$vararray['img_filename']['addrow']=(isset($_POST['img_filename']))?$_POST['img_filename']['addrow']:"";
							$vararray['vsort']['addrow']=(isset($_POST['vsort']))?$_POST['vsort']['addrow']:$lastsort+1;
						}
					}
					else if(isset($_SESSION['error'])||!isset($_GET['opt_id']))
					{
						for($x=0;$x<$_POST['inum'];$x++)
						{
							$vararray['variant_id'][$x]=(isset($_POST['variant_id']))?$_POST['variant_id'][$x]:"";
							$vararray['item_desc'][$x]=(isset($_POST['item_desc']))?$_POST['item_desc'][$x]:"";
							$vararray['price'][$x]=(isset($_POST['price']))?$_POST['price'][$x]:"0";
							$vararray['price_euro'][$x]=(isset($_POST['price_euro']))?$_POST['price_euro'][$x]:"0";
							$vararray['img_filename'][$x]=(isset($_POST['img_filename']))?$_POST['img_filename'][$x]:"";
							$vararray['vsort'][$x]=(isset($_POST['vsort']))?$_POST['vsort'][$x]:$_POST['inum'];
						}
					}
					$formarr=($action=="add"||isset($_SESSION['error']))?$_POST:$itemarr;
					$navsQ=mysql_query("SELECT nav_skuvar,nav_sku,nav_description,nav_variant_desc,nav_variant,nav_qty FROM nav_stock ORDER BY nav_skuvar");
					?>
					<form action="<?=$self?>&amp;act=<?=$_GET['act']?><?=((isset($_GET['opt_id']))?"&amp;opt_id=".$_GET['opt_id']:"")?>" method="post" enctype="multipart/form-data">
					<input type="hidden" name="admin_id" value="<?=$uaa['admin_id']?>" />
					<input type="hidden" name="inum" value="<?=((isset($_GET['opt_id']))?((isset($_GET['addrow']))?$inum+1:$inum):$_POST['inum'])?>" />
					
					<table class="details">
					<tr>
						<td class="head" colspan="<?=(($_GET['act']=="edit")?"6":"5")?>"><?=$titleback." ".helplink($page)?> Standard Option Builder</td>
					</tr>
					<? if($_GET['act']=="edit"){?>
					<tr>
						<td class="infohead" colspan="<?=(($_GET['act']=="edit")?"6":"5")?>">Last updated on: <?=date("F d\, Y h:i:s A",$itemarr['date_edited'])?></td>
					</tr>
					<? }?>
					<tr>
						<td>Description<br /><dfn>(eg:&#160;"Colour")</dfn></td>
						<td><input type="text" name="description" class="formfield" <?=highlighterrors($higherr,"description")?> value="<?=$formarr['description']?>" /> </td>
						<td colspan="<?=(($_GET['act']=="edit")?"4":"3")?>"></td>
					</tr>
					<tr>
						<td>Title</td>
						<td><input type="text" name="opt_name" class="formfield" <?=highlighterrors($higherr,"opt_name")?> value="<?=$formarr['opt_name']?>" /></td>
						<td colspan="<?=(($_GET['act']=="edit")?"4":"3")?>"></td>
					</tr>
					<tr>
						<td class="subhead" style="width:5%"><?=(($_GET['act']=="edit")?"Sort":"ID")?></td>
						<td class="subhead" style="width:50%">Stock Code/Variant</td>
						<td class="subhead" style="width:10%">Option Name</td>
						<td class="subhead" style="width:20%;text-align:center">Option Swatch</td>
						<td class="subhead" style="width:15%">Added Cost</td>
						<? if($_GET['act']=="edit"){?><td class="subhead">Delete</td><? }?>
					</tr>
					<? 
					$x=0;
					foreach($vararray['variant_id'] as $id => $value)
					{
						?>
						<tr>
							<td>
							<? if($_GET['act']=="edit"){?>
								<input type="text" name="vsort[<?=$id?>]" class="formfields" <?=highlighterrors($higherr,"vsort_".$id)?> value="<?=$vararray['vsort'][$id]?>" />
							<? }else{?>
								<?=($x+1)?>
							<? }?>
							</td>
							<td>
							<select name="variant_id[<?=$id?>]" class="formfield" style="width:340px" <?=highlighterrors($higherr,"variant_id_".$id)?>>
							<option value="">Please select...</option>
							<? while($navs=mysql_fetch_row($navsQ)){ 
							$navs3=trim($navs[3]);
							$desc=(substr($navs3,-3,3)==", U")?substr($navs3,0,(strlen($navs3)-3)):$navs3;
							?>
								<option value="<?=$navs[0]?>" <?=(($vararray['variant_id'][$id]==$navs[0])?"selected='selected'":"")?>><?=$navs[1]?> (<?=str_replace(array("V000","V00"),array("V","V"),$navs[4])?>) <?=$navs[2]?> <?=$desc?> (<?=$navs[5]?>)</option>
							<? }mysql_data_seek($navsQ,0);/*reset for next row*/?>
							</select>
							</td>
							<td><input type="text" name="item_desc[<?=$id?>]" class="formfieldm" <?=highlighterrors($higherr,"item_desc_".$id)?> value="<?=$vararray['item_desc'][$id]?>" /></td>
							<td style="text-align:center">
							<? if(isset($_GET['opt_id'])&&$id!='addrow'){?>
								<a href="<?=$mainbase?>/admin.php?p=images&amp;what=option_values&amp;imgsize=<?=$images_arr["product_options"]['images']['main']?>&amp;optval_id=<?=$id?>"><? if($vararray['img_filename'][$id]!='0'&&strlen($vararray['img_filename'][$id])>0){?>Edit<? }else{?>Add<? }?> Image</a>
							<? }else{?>
								<input type="hidden" name="imgsize[]" value="<?=$images_arr["product_options"]['images']['main']?>" />
								<input type="file" name="uploadedfile[]" <?=highlighterrors($higherr,"uploadedfile_".$id)?> value="" />
							<? }?>
							</td>
							<td>&#163;<input type="text" name="price[<?=$id?>]" class="formfields" value="<?=((isset($vararray['price'][$id]))?$vararray['price'][$id]:'0')?>" /><br />&#8364;<input type="text" name="price_euro[<?=$id?>]" class="formfields" value="<?=((isset($vararray['price_euro'][$id]))?$vararray['price_euro'][$id]:'0')?>" /></td>
						<? if($_GET['act']=="edit"&&$id!="addrow"){?><td><input type="checkbox" name="delete[<?=$id?>]" value="1" /></td><? }?>
						</tr>
						<?
						$x++;
					}
					?>
					<? if($_GET['act']=="edit"){?>
					
					<tr>
						<td class="infohead" colspan="<?=(($_GET['act']=="edit")?"6":"5")?>">
						<div style="float:left"><?=$inum?> records found</div>
						<div style="float:right"><a href="<?=$self?>&amp;act=edit&amp;opt_id=<?=$_GET['opt_id']?>&amp;addrow=1">Add another row</a> | <a href="<?=$self?>">Return to option builder</a></div>
						</td>
					</tr>
					<? }?>
					</table>
					<p class="submit"><input type="submit" name="submit" class="formbutton" value="<?=(($_GET['act']=="edit")?"Update":"Create")?> Option" /></p>
					</form>
					<?
				}
				break;
			default:
				$searched=(isset($_POST['search']))?$_POST['search']:"";
				if(isset($_POST['search'])){$where="WHERE opt_name LIKE '%".$_POST['search']."%'";}
				$pgnums=pagenums("SELECT * FROM product_options $where ORDER BY opt_name",$self,30,5);
				$query=$pgnums[0];
				$optsQ=mysql_query($query);
				?>
				<table class="details">
				<tr>
					<td class="head" colspan="4"><div class="titles"><?=helplink($page)?> Standard Options</div><div class="links"><a href="<?=$self?>&amp;act=add">Add new option</a></div></td>
				</tr>
				<? if(strlen($pgnums[1])>0){?>
				<tr>
					<td class="infohead" colspan="4"><?=$pgnums[1]?></td>
				</tr>
				<? }?>
				<tr>
					<td colspan="4" style="vertical-align:middle">
					<div style="float:left"><form action="<?=$self?>" method="post">Search <input type="text" name="search" value="<?=$searched?>" style="vertical-align:middle" /> <input type="submit" name="submit" class="formbutton" value="Search" style="vertical-align:middle" /></form></div>
					<div style="float:right;vertical-align:middle;line-height:200%"><a href="<?=$self?>">View all</a></div>
					</td>
				</tr>
				<tr>
					<td class="subhead" style="width:70%">Option title</td>
					<td class="subhead" style="width:10%;text-align:center">Description</td>
					<td class="subhead" style="width:10%;text-align:center">Edit</td>
					<td class="subhead" style="width:10%;text-align:center">Delete</td>
				</tr>
				<?
				while($opts=mysql_fetch_assoc($optsQ))
				{
					$row=(!isset($row)||$row=="1")?"0":"1";
					?>
					<tr class="row<?=$row?>">
						<td><?=$opts['opt_name']?></td>
						<td style="text-align:center"><?=$opts['description']?></td>
						<td style="text-align:center"><a href="<?=$self?>&amp;act=edit&amp;opt_id=<?=$opts['opt_id']?>">Edit</a></td>
						<td style="text-align:center"><a href="<?=$self?>&amp;act=delete&amp;opt_id=<?=$opts['opt_id']?>" onclick="return confirm('Are you sure you wish to delete this product option and all option values?')">Delete</a></td>
					</tr>
					<?
				}
				?>
				</table>
				<?
				break;
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>
<? unset($_SESSION['error']); ?>