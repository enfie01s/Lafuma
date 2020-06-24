<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; Administrators</div>
<div id="main">
	<h2 id="pagetitle">Administrators</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<? switch($action){
		case "edit":
		case "add":
			if($action=="edit"){
				$admins=mysql_query("SELECT * FROM admin_users as au JOIN admin_permissions as ap ON au.`admin_id`=ap.`user_id` WHERE `admin_id`='$_GET[auid]'");
				$admin=mysql_fetch_assoc($admins);
				$auths=explode(",",$admin['permissions']);
				$data=isset($_POST['username'])?$_POST:$admin;
			}else{
				$auths=isset($_POST['amods'])?array_keys($_POST['amods']):array();
				$data=isset($_POST['username'])?$_POST:array();
			}
			?>
			<form action="<?=$self?>&amp;act=<?=$action?><? if($action=="edit"){?>&amp;auid=<?=$_GET['auid']?><? }?>" method="post">
			<table class="details">
				<tr>
					<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> <?=$action=="add"?"Add":"Edit"?> Administrator</td>
				</tr>
				<tr>
					<td class="first"><label for="username">Username <span>*</span></label></td>
					<td><input type="text" name="username" id="username" value="<?=posted_value("username","","",$data)?>" class="formfield" <?=highlighterrors($higherr,"username")?> /></td>
				</tr>
				<tr>
					<td><label for="password"><?=$action=="add"?"":"New "?>Password<?=$action=="add"?" <span>*</span>":""?></label></td>
					<td><input type="password" name="password" id="password" value="" class="formfield" <?=highlighterrors($higherr,"password")?> /></td>
				</tr>
				<tr>
					<td><label for="email">Email <span>*</span></label></td>
					<td><input type="text" name="email" id="email" value="<?=posted_value("email","","",$data)?>" class="formfield" <?=highlighterrors($higherr,"email")?> /></td>
				</tr>
				<tr>
					<td class="subhead" colspan="2">Authorisations (check Administrator to allow access to administrators menu)</td>
				</tr>
				<tr>
				<? 
				$x=1;
				$totalmodules=count($modules)-1;
				$tdremainder=$totalmodules%2;
				foreach($modules as $modid => $modname)
				{
					if($modid>0)
					{
						?>
						<td>
						<input type="checkbox" name="amods[<?=$modid?>]" value="1" id="mods<?=$modid?>" <?=in_array($modid,$auths)?"checked='checked'":""?> /><label for="mods<?=$modid?>"><?=$modname?></label>
						</td>
						<? 
						if($x==$totalmodules&&$tdremainder==1){echo "<td></td>";}
						if($modid%2==0&&$x<$totalmodules){echo "</tr><tr>";}
						$x++;
					}
				}?>
				</tr>
			</table>
			<p class="submit"><input type="submit" value="<?=$action=="add"?"Add":"Update"?> account" /></p>
			</form>
			<?
			break;
		default:
			$admins=mysql_query("SELECT * FROM admin_users ORDER BY `date_lastin` DESC");
			$adminnum=mysql_num_rows($admins);
			?>
			<table class="details">
				<tr>
					<td class="head" colspan="6"><div class="titles"><?=helplink($page)?> Administrators</div><div class="links"><a href="<?=$self?>&amp;act=add">Add new user</a></div></td>
				</tr>
				<tr>
					<td class="infohead" colspan="6"><?=$adminnum?> users found</td>
				</tr>
				<tr>
					<td class="subhead">Username</td>
					<td class="subhead">Last edited</td>
					<td class="subhead">Signup date</td>
					<td class="subhead">Last Seen</td>
					<td class="subhead">Edit</td>
					<td class="subhead">Delete</td>
				</tr>
				<? while($admin=mysql_fetch_assoc($admins)){$row=!isset($row)||$row==1?0:1;?>
				<tr class="row<?=$row?>">
					<td><?=$admin['username']?></td>
					<td><?=strlen($admin['date_edited'])>0?date("F d, Y",$admin['date_edited']):"N/A"?></td>
					<td><?=date("F d, Y",$admin['date_created'])?></td>
					<td><?=date("F d, Y G:i",$admin['date_lastin'])?></td>
					<td><a href="<?=$self?>&amp;act=edit&amp;auid=<?=$admin['admin_id']?>">Edit</a></td>
					<td><? if($admin['super_admin']!=1){?><a href="<?=$self?>&amp;act=delete&amp;auid=<?=$admin['admin_id']?>" onclick="javascript:return decision('Are you sure you wish to delete this administrator?', '<?=$self?>&amp;act=delete&amp;auid=<?=$admin['admin_id']?>')">Delete</a><? }else{?>Super Admin<? }?></td>
				</tr>
				<? }?>
			</table>
			<? 
			break;
		}?>
		<!-- /CONTENT -->
	</div>
</div>
