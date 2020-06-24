<?
$hist=array(
array("2008","CLIP SYSTEM INTRODUCED","The patented clip system was introduced by Lafuma in 2008.  The Batyline seat clips directly into the frame of the chair which makes the seat noticeably firmer and significantly reduces seat sagging."),
array("1992","THE POP UP CHAIR IS BORN","The challenge?  To make this type of product easier to fold and transport.  The principal characteristic of this model is an elastomer piece which allows it to fold automatically and which also acts as a shock absorber. With its unique and timeless design, it is equally comfortable for sitting or reclining.  An exceptionally clever product, which is compact as it folds up like an umbrella.  It is lightweight and can be stored easily when not in use. Over 400,000 of these famous chairs have been sold in the last 20 years."),
array("1961","RELAXATION: THE RELAX RECLINER","The famous multi-position chair seduces relaxation fans.  50 years later, the Anneyron factory in France produces 300,000 per year."),
array("1954","LAFUMA LAUNCHES CAMPING LINE","First camping furniture collection, born out of the brand's know how with metallic tubing.  The first diversification product for the company that co-founds the Spoga trade show for sport and camping in Germany."),
array("1930","THE THREE BROTHERS CREATE LAFUMA","Victor, Alfred and Gabriel Lafuma begin manufacturing bags in Anneyron (France).")
);
?>
<h2 id="pagetitle">History</h1>
<?
foreach($hist as $arid => $det)
{
	$fl=$arid%2==0?"right":"left";
	$pad=$arid%2==0?"left":"right";
	?>
	<div style="vertical-align:middle">
	<img src="content/img/history/<?=$det[0]?>.jpg" alt="" style="float:<?=$fl?>;padding-<?=$pad?>:10px;vertical-align:middle" />
	<span style="color:red;font-weight:bold;font-size:14px"><?=$det[0]?></span><br />
	<strong style="font-size:15px;"><?=$det[1]?></strong><br />
	<?=$det[2]?>
	</div>
	<div style="clear:both;margin-bottom:30px;"></div>
	<?
}
?>


 


 


 


 
