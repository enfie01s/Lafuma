<? if(basename($_SERVER['PHP_SELF'])!="index.php"){die("Access Denied");}//direct access security 
if($searchrows<1)
{
	?><p>&#160;</p><p>&#160;</p><p>&#160;</p><p>&#160;</p><?
}
else
{
	?><ol><?
	while($searchres=mysql_fetch_assoc($searchq))
	{
		?><li>
		<h2><a href='<?=$mainbase?>/index.php?p=products&amp;<?=($searchres['ftype']=='product'?"pid=":"cid=").$searchres['fid']?>'><?=$searchres['title']?></a> | <?=$searchres['ftype']?></h2>
		<?=$searchres['ftype']=='product'&&strlen($searchres['tdesc'])>0?htmlentities($searchres['tdesc'],ENT_QUOTES,"ISO-8859-1"):""?>
		</li><?
	}
	?></ol><?
}
?>