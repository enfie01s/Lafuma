<?
include "../content/config.php";
include("includes.php");
 
/**************************************************************************************************
* Sage Pay Direct Kit 3D Redirection inline frame
***************************************************************************************************

***************************************************************************************************
* Change history
* ==============
*
* 18/12/2007 - Nick Selby - New PHP version adapted from ASP
**************************************************************************************************/

$strACSURL=$_SESSION["ACSURL"];
$strPAReq=$_SESSION["PAReq"];
$strMD=$_SESSION["MD"];
$strVendorTxCode=$_SESSION["VendorTxCode"];
$_SESSION["PAReq"]="";
?>

<script language="Javascript" type="text/javascript"> function OnLoadEvent() { document.form.submit(); } </SCRIPT>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="../content/style.css">
<title>3D-Secure Redirect</title>
</head>

<body OnLoad="OnLoadEvent();">
<form name="form" action="<?=$strACSURL?>" method="post" target="3diframe"/>
		<input type="hidden" name="PaReq" value="<?=$strPAReq?>"/>
		<input type="hidden" name="TermUrl" value="<?=$strYourSiteFQDN . $strVirtualDir?>sagepay/3DCallBack.php?VendorTxCode=<?=$strVendorTxCode?>"/>
		<input type="hidden" name="MD" value="<?=$strMD?>"/>
		<NOSCRIPT> 
		<center><p>Please click button below to Authenticate your card</p><input type="submit" value="Go"/></p></center>
		</NOSCRIPT> 
		</form>
</body>
</html>

