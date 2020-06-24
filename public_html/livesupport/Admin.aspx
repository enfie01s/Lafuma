<%@ Page Language="vb" AutoEventWireup="false" CodeBehind="Admin.aspx.vb" Inherits="LiveSupport.Admin" %>
<%@ Register TagName="OMHeader" TagPrefix="OM" Src="ctlHeader.ascx" %>
<%@ Register TagName="OMLabel" TagPrefix="OM" Src="ctlOM.ascx" %>
<%@ Register TagName="OMPoweredby" TagPrefix="OM" Src="ctlPoweredby.ascx" %>
<!DOCTYPE html>
<html>
<head>
	<title>Live Support</title>
	<link href="Styles/Site.css" rel="stylesheet" type="text/css" />
	<link href="Styles/font.css" rel="stylesheet" type="text/css" />
	<link href="Styles/backend.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="content">
    <OM:OMHeader runat="server" ID="OMHeader" />
		<div class="user-detail login_box">
			<form runat="server" id="frm">
			<div class="editor-label title">
				Admin / Manager Login</div>
			<div class="border">
				&nbsp;</div>
			<div class="editor-label">
				Username</div>
			<div class="editor-field">
				<asp:TextBox runat="server" ID="Username" Width="150px"></asp:TextBox>
				<asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ForeColor="Red"
					ErrorMessage="Required" ControlToValidate="Username"></asp:RequiredFieldValidator></div>
			<div class="editor-label">
				Password</div>
			<div class="editor-field">
				<asp:TextBox runat="server" ID="Password" Width="150px" TextMode="Password"></asp:TextBox>
				<asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ForeColor="Red"
					ErrorMessage="Required" ControlToValidate="Password"></asp:RequiredFieldValidator>
			</div>
			<div class="editor-label calign auto" style="width: 100%">
				<asp:Label runat="server" ID="lblError" ForeColor="Red"></asp:Label>&nbsp;<asp:Button
					runat="server" ID="Submit" Text="Submit" CssClass="button" />
			</div>
			<input type="hidden" runat="server" id="SessionKey" />
			</form>
			<div class="clear">
				&nbsp;</div>
		</div>
    <OM:OMPoweredby runat="server" ID="OMPoweredby" />
	</div>
	<OM:OMLabel runat="server" ID="ctlOMLabel" />
</body>
</html>
