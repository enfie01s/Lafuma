<? $contactid=isset($_GET['eid'])?$getescaped['eid']:"";?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if(strlen($contactid)>0){?><a href="<?=$self?>"><? }?>Enquiries<? if(strlen($contactid)>0){?></a><? }?><?=(strlen($contactid)>0?" &#187; Viewing enquiry":"")?></div>
<div id="main">
	<h2 id="pagetitle">Enquiries</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<?
		switch($action)
		{
			case "view":
				$enqs=mysql_query("SELECT * FROM contactus WHERE `contactus_id`='$contactid'");
				$enq=mysql_fetch_assoc($enqs);
				?>
				<table class="details">
					<tr>
						<td class="head" colspan="2"><?=$titleback." ".helplink($page)?> Enquiries</td>
					</tr>
					<tr class="row1">
						<td class="first">Name:</td>
						<td><?=$enq['name']?></td>
					</tr>
					<tr class="row0">
						<td>Email Address:</td>
						<td><a href="mailto:<?=$enq['email']?>?subject=Thank%20you%20for%20your%20email%20to%20Lafuma%20UK&amp;body=%0D%0D----------------------------------%0DYour original enquiry:%0D<?=$enq['comments']?>"><?=$enq['email']?></a></td>
					</tr>
					<tr class="row1">
						<td>Address1:</td>
						<td><?=$enq['address1']?></td>
					</tr>
					<tr class="row0">
						<td>Address2:</td>
						<td><?=$enq['address2']?></td>
					</tr>
					<tr class="row1">
						<td>Telephone:</td>
						<td><?=$enq['workphone']?></td>
					</tr>
					<tr class="row0">
						<td>Country:</td>
						<td><?=$enq['country']?></td>
					</tr>
					<tr class="row1">
						<td style="vertical-align:top">Request:</td>
						<td><?=htmlentities($enq['comments'],ENT_QUOTES,"UTF-8")?></td>
					</tr>
				</table>
				<p class="submit"><a href="<?=$self?>&amp;act=delete&amp;eid=<?=$contactid?>" onclick="return decision('Are you sure you wish to delete this enquiry?','<?=$self?>')">Delete this enquiry</a></p>
				<?
				break;
			default:
				?>
				<form action="<?=$self?>" method="post">
				<table class="details">
					<tr>
						<td class="head" colspan="5"><?=helplink($page)?> Enquiries</td>
					</tr>
					<tr>
						<td class="subhead">Name</td>
						<td class="subhead">Email</td>
						<td class="subhead">Country</td>
						<td class="subhead">Date</td>
						<td class="subhead">Delete</td>
					</tr>
					<? 
					$enqs=mysql_query("SELECT * FROM contactus ORDER BY `date_created` ASC");
					while($enq=mysql_fetch_assoc($enqs)){
					$row=!isset($row)||$row==1?0:1;
					?>
					<tr class="row<?=$row?>">
						<td><a href="<?=$self?>&amp;act=view&amp;eid=<?=$enq['contactus_id']?>"><?=$enq['name']?></a></td>
						<td><?=$enq['email']?></td>
						<td><?=$enq['country']?></td>
						<td><?=date("d/m/Y",$enq['date_created'])?></td>
						<td><input type="checkbox" name="delenq[<?=$enq['contactus_id']?>]" value="1" /></td>
					</tr>
					<? }?>
				</table>
				<p class="submit"><input type="submit" value="Delete enquiries" onclick="return decision('Are you sure you wish to delete the selected enquiries?','<?=$self?>')" /></p>
				</form>
				<?
				break;
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>