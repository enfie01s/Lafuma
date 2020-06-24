<?
/*if(!in_array(basename($_SERVER['PHP_SELF']),array("admin.php"))){die("Access Denied");}//direct access security

$doc = new DOMDocument();
$date_generated = $doc->createComment( "Generated: ".date("l jS F Y H:i") );
$doc->appendChild($date_generated);

$doc->formatOutput = true; 
$rss = $doc->createElement("rss");
$rss->setAttribute("version","2.0");
$rss->setAttribute("xmlns:g","http://base.google.com/ns/1.0");
$rss->setAttribute("xmlns:c","http://base.google.com/cns/1.0");

$r = $doc->createElement( "channel" ); 
	$rss->appendChild( $r ); 
	
	$title = $doc->createElement( "title" ); 
	$title->appendChild($doc->createTextNode( "Lafuma UK Products" )); 
	$r->appendChild( $title ); 
	
	$link = $doc->createElement( "link" ); 
	$link->appendChild($doc->createTextNode( "http://www.lafuma.co.uk" )); 
	$r->appendChild( $link ); 
	
	$desc = $doc->createElement( "description" ); 
	$desc->appendChild($doc->createTextNode( "Official Lafuma UK Website. Shop online for Lafuma Garden Furniture, Camping Furniture, Chairs, Sunbeds, Recliners, Relaxers, Camping Tables, Loungers and Lafuma RSX and RSXA Chairs." )); 
	$r->appendChild( $desc ); 
	
	$elements=array("title","link","description","g:image_link","g:price","g:condition","g:availability","g:id","g:product_type","g:quantity","g:brand","g:mpn","g:gtin","g:google_product_category","g:product_review_average","g:product_review_count");
	
	$cats=mysql_query("SELECT * FROM categories WHERE `displayed`='1'");
	while($cat=mysql_fetch_assoc($cats))
	{	
		$froogle_prods=mysql_query("SELECT * FROM products as p JOIN fusion as f ON p.`prod_id`=f.`iSubId_FK` AND f.`vtype`='product' AND f.`iState`='1' AND `".WHICHLIST."` = '1' WHERE `iOwner_FK`='$cat[cat_id]' AND `vOwnerType`='department'");
		while($froogle_prod=mysql_fetch_assoc($froogle_prods))
		{
			$stocks=mysql_query("SELECT SUM(`nav_qty`) FROM nav_stock WHERE `nav_sku`='$froogle_prod[sku]'");
			list($stock)=mysql_fetch_row($stocks);
			$stock=($stock==""||!isset($stock))?0:$stock;
			$avail=$stock>0?"in stock":"out of stock";
			$totalrevs=0;
			$reviewsq=@mysql_query("SELECT AVG(`rank`) as avgrank,count(`rank`) as totalrevs FROM products as p LEFT JOIN customerreviews as cr ON p.`prod_id`=cr.`item_id` WHERE p.`prod_id`='$froogle_prod[prod_id]' AND `".WHICHLIST."` = '1' GROUP BY p.`prod_id`");
			@list($avgrank,$totalrevs)=@mysql_fetch_row($reviewsq);
			
			$bcode=strlen($froogle_prod['barcode'])>11?$froogle_prod['barcode']:str_repeat(0,12);
			$link=str_replace(" ","%20","http://www.lafuma.co.uk/index.php?p=products&pid=".$froogle_prod['fusionId']."&prodname=".str_replace("_","%20",$froogle_prod['seo_title']));
			$desc=strip_tags(str_replace(array("<br />","<br>","\'","&acute;","&#160;"),array("\r\n","\r\n","'","'"," "),$froogle_prod['content']));
			$imglink=str_replace(" ","%20","http://www.lafuma.co.uk/content/img/products/medium/".$froogle_prod['prod_id']."-default.jpg");
			$price=number_format($froogle_prod['price']+($vat*($froogle_prod['price']/100)),2);
			$dept=str_replace("Lafuma ","",$cat['title']);
			
			$values=array($froogle_prod['title'],$link,$desc,$imglink,$price,"New",$avail,$froogle_prod['fusionId'],$dept,$stock,"Lafuma",$froogle_prod['sku'],$bcode,"Furniture &gt; Outdoor Furniture",$avgrank,$totalrevs);
			
			$h = $doc->createElement( "item" );// item start			
			foreach($elements as $eid => $element)
			{
				if(!(($element=="g:product_review_average"||$element=="g:product_review_count")&&$totalrevs<1)){
				$el = $doc->createElement( $element ); 
				$el->appendChild($doc->createTextNode( $values[$eid] )); 
				$h->appendChild( $el );
				}
			}			
			$r->appendChild( $h ); //item end
		}
	}
$doc->appendChild($rss);
$doc->save('froogle.xml');

$put=0;
if(file_exists("froogle.xml"))
{
	$conn = ftp_connect($froogle_serv) or die("Could not connect to Froogle FTP");
	ftp_login($conn,$froogle_user,$froogle_pass);
	ftp_set_option($conn,FTP_TIMEOUT_SEC,120);
	$put=ftp_put($conn,"froogle.xml","froogle.xml",FTP_ASCII);
	ftp_close($conn);
}
else
{
	$put="File hasn't been generated";
}
if($put==1&&$islocal!=1)
{
	if($uaa['super_admin']==1)
	{
		echo "<div style='text-align:center'>Successfully updated products on Google Products ".date("l jS F Y H:i")."</div>";
	}
}
else
{
	if($uaa['super_admin']==1)
	{
		echo "<div style='text-align:center'>There was an error while attempting to update products on Google Products</div>";
	}
	else if($islocal==0&&$uaa['super_admin']==1){mail("senfield@gmk.co.uk","Error updating Google Products","There was an error while updating Google Products","From: Lafuma UK <sales@llc-ltd.co.uk>\r\nReply-To: sales@llc-ltd.co.uk\r\n");}
}*/
?>