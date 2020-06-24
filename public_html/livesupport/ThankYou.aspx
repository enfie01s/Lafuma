<%@ Page Language="vb" AutoEventWireup="false" CodeBehind="ThankYou.aspx.vb" Inherits="LiveSupport.ThankYou" %>
<%@ Register TagName="OMPoweredby" TagPrefix="OM" Src="ctlPoweredby.ascx" %>
<!DOCTYPE html>
<html>
<head>
	<title>Live Support</title>
	<link href="Styles/Site.css" rel="stylesheet" type="text/css" />
	<link href="Styles/font.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="content">
		<div id="header">
			<h1>
				Thank You</h1>
		</div>
		<div class="user-detail">
			<div style="clear: both">
				Thank you. An operator will respond to your request as soon as possible.
			</div>
			<div class="clear">
				&nbsp;</div>
		</div>
    <OM:OMPoweredby runat="server" ID="OMPoweredby" />
	</div>
</body>
</html>
