<?
$sizes=array("small","medium","large");
$productsq=mysql_query("SELECT `prod_id`,`img_filename` FROM products WHERE `".WHICHLIST."` = '1'");
while($products=mysql_fetch_assoc($productsq))
{
	extract($products);
	foreach($sizes as $size)
	{
		$old="./content/img/products/".$size."/".$img_filename;
		$new="./content/img/products/".$size."/".$prod_id."-default.jpg";
		//echo "copy(".$old.",".$new.")<br />";
		//copy($old,$new);
		//unlink("./content/img/products/".$size."/".strtolower($image));
	}
	mysql_query("UPDATE products SET `img_filename`='".$prod_id."-default.jpg' WHERE `prod_id`='$prod_id'");
}
/*
$ignore=array("_notes","Thumbs.db","index.php");

foreach($sizes as $size)
{
	chmod("./content/img/products/".$size,0777);
	$thissize=glob("./content/img/products/".$size."/*.JPG");
	
	foreach($thissize as $sz)
	{
		if(!in_array($sz,$ignore))
		{
			chmod($sz,0777);
			rename($sz,str_replace($size."/",$size."/done/",strtolower($sz)));
			
		}
	}
}*/

?>