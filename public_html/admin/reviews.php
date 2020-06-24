<? $revid=isset($_GET['rid'])?$getescaped['rid']:"";?>
<div id="bread">You are here: <a href="<?=$mainbase?>/admin.php">Admin Home</a> &#187; <? if(strlen($revid)>0){?><a href="<?=$self?>"><? }?>Reviews<? if(strlen($revid)>0){?></a><? }?><?=(strlen($revid)>0?" &#187; Replying to review":"")?></div>
<div id="main">
	<h2 id="pagetitle">Reviews</h2>
	<div id="pagecontent">
		<? if(isset($_SESSION['error'])){?><div id="errorbox"><p>Error</p><?=$_SESSION['error']?></div><? unset($_SESSION['error']); }?>
		<!-- CONTENT -->
		<?
		switch($action)
		{
			case "reply":				
				$enqs=mysql_query("SELECT (SELECT comment FROM customerreviews WHERE owner_id=cr.cust_rev_id) as resp,email,firstname,lastname,cr.title as ctitle,p.title as ptitle,cr.date_created,cr.state,rank,cr.comment,cust_rev_id FROM (customerreviews as cr JOIN customers as c ON cr.cust_id=c.cust_id) LEFT JOIN products as p ON p.prod_id=cr.item_id WHERE `cust_rev_id`=$revid AND `owner_id`=0");
				$enq=mysql_fetch_assoc($enqs);
				
				?>
				<form action="<?=$self?>&amp;act=reply&amp;rid=<?=$enq['cust_rev_id']?>" method="post">
				<input name="what" value="<?=isset($enq['resp'])?"UPDATE":"INSERT"?>" type="hidden" />
				<table class="details">
					<tr>
						<td class="head" colspan="2"><strong>Review of <?=$enq['ptitle']?></strong> by <?=$enq['firstname']." ".$enq['lastname']?></td>
					</tr>
					<tr>
						<td class="subhead" colspan="2">Comments</td>
					</tr>
					<tr class="row_light"><td colspan="2"><strong><?=$enq['ctitle']?></strong><br /><?=$enq['comment']?></td></tr>
					<tr>
						<td class="subhead" colspan="2">Response</td>
					</tr>
					<tr class="row_light"><td colspan="2">
					<textarea name="comment" style="width:100%"><?=$enq['resp']?></textarea>
					</td></tr>
				</table>
				<p class="submit"><input type="submit" value="Submit Reply" /></p>
				</form>
				<?
				break;
			default:
				?>
				<form action="<?=$self?>" method="post">
				<table class="details">
					<tr>
						<td class="head" colspan="7">Reviews</td>
					</tr>
					<tr>
						<td class="subhead">Name</td>
						<td class="subhead">Product</td>
						<td class="subhead">Title</td>
						<td class="subhead" style="text-align:center">Rating</td>
						<td class="subhead">Date</td>
						<td class="subhead" style="text-align:center">Reply</td>
						<td class="subhead" style="text-align:center">Delete</td>
					</tr>
					<? 
					$enqs=mysql_query("SELECT (SELECT comment FROM customerreviews WHERE owner_id=cr.cust_rev_id) as resp,email,firstname,lastname,cr.title as ctitle,p.title as ptitle,cr.date_created,cr.state,rank,cr.comment,cust_rev_id,cr.cust_id FROM (customerreviews as cr JOIN customers as c ON cr.cust_id=c.cust_id) LEFT JOIN products as p ON p.prod_id=cr.item_id WHERE `owner_id`=0 ORDER BY `date_created` DESC");
					while($enq=mysql_fetch_assoc($enqs)){
					$row=!isset($row)||$row==1?0:1;
					?>
					<tr class="row<?=$row?>">
						<td><a href="mailto:<?=$enq['email']?>" title="Send email">&#9993;</a> <a href="admin.php?p=customers&amp;act=view&amp;cust_id=<?=$enq['cust_id']?>"><?=$enq['firstname']." ".$enq['lastname']?></a></td>
						<td><?=$enq['ptitle']?></td>
						<td><a title="<?=$enq['comment']?>" style="cursor:help;color:#555"><?=$enq['ctitle']?></a></td>
						<td style="text-align:center"><?=$enq['rank']?></td>
						<td><?=date("d/m/Y",$enq['date_created'])?></td>
						<td style="text-align:center"><a href="admin.php?p=reviews&amp;act=reply&amp;rid=<?=$enq['cust_rev_id']?>" title="<?=isset($enq['resp'])?"Edit":"Add"?>" style="font-size:20px;line-height:10px"><?=isset($enq['resp'])?"&#9997;":"+"?></a></td>
						<td style="text-align:center"><input type="checkbox" name="delrev[<?=$enq['cust_rev_id']?>]" value="1" /></td>
					</tr>
					<? }?>
				</table>
				<p class="submit"><input type="submit" value="Delete selected" onclick="return decision('Are you sure you wish to delete the selected reviews?','<?=$self?>')" /></p>
				</form>
				<?
				break;
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>