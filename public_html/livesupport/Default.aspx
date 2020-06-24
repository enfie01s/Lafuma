<%@ Page Language="vb" AutoEventWireup="false" CodeBehind="Default.aspx.vb" Inherits="LiveSupport._Default" %>

<%@ Register TagName="OMHeader" TagPrefix="OM" Src="ctlHeader.ascx" %>
<%@ Register TagName="OMLabel" TagPrefix="OM" Src="ctlOM.ascx" %>
<%@ Register TagName="OMPoweredby" TagPrefix="OM" Src="ctlPoweredby.ascx" %>
<!DOCTYPE html>
<html>
<head>
  <title>Live Support</title>
  <script src="Scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
  <script src="Scripts/Chat.js" type="text/javascript"></script>
  <link href="Styles/Site.css" rel="stylesheet" type="text/css" />
  <link href="Styles/font.css" rel="stylesheet" type="text/css" />
</head>
<body>
  <div id="content">
    <OM:OMHeader runat="server" ID="OMHeader" />
    <div class="user-detail">
      <div style="clear: both">
        <asp:Label runat="server" ID="lblError" ForeColor="Red" Visible="false"></asp:Label>
      </div>
      <div class="clear">
        &nbsp;</div>
      <form runat="server" id="frm">
      <div class="editor-label">
        Name</div>
      <div class="editor-field">
        <asp:TextBox runat="server" ID="Name"></asp:TextBox>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator1" runat="server" ForeColor="Red"
          ErrorMessage="Required" ControlToValidate="Name" SetFocusOnError="true"></asp:RequiredFieldValidator></div>
      <div class="editor-label">
        Email</div>
      <div class="editor-field">
        <asp:TextBox runat="server" ID="Email"></asp:TextBox>
        <asp:RegularExpressionValidator ID="revtxtEmail" runat="server" ControlToValidate="Email"
          ForeColor="Red" Display="Dynamic" ErrorMessage="Invalid Email!" ValidationExpression="\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*"
          SetFocusOnError="true"></asp:RegularExpressionValidator>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator2" runat="server" ForeColor="Red"
          ErrorMessage="Required" ControlToValidate="Email" SetFocusOnError="true" Display="Dynamic"></asp:RequiredFieldValidator>
      </div>
      <div class="editor-label" style="position: relative;">
        Phone <span class="optional">(optional)</span></div>
      <div class="editor-field">
        <asp:TextBox runat="server" ID="Phone"></asp:TextBox></div>
      <div class="editor-label">
        Department
      </div>
      <div class="editor-field">
        <asp:DropDownList ID="ddlDepartment" runat="server">
        </asp:DropDownList>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator5" runat="server" ForeColor="Red"
          ErrorMessage="Required" ControlToValidate="ddlDepartment" SetFocusOnError="true"
          Display="Dynamic" InitialValue="0"></asp:RequiredFieldValidator>
        <span id="spnloading"></span>
      </div>
      <div class="editor-label clearspace auto" style="width: 100%">
        <div style="float: left">
          Message</div>
        <div style="float: right; padding-right: 24px;">
          <asp:RequiredFieldValidator ID="RequiredFieldValidator3" runat="server" ForeColor="Red"
            ErrorMessage="Required" ControlToValidate="Question" SetFocusOnError="true"></asp:RequiredFieldValidator></div>
      </div>
      <div class="editor-label">
        <asp:TextBox runat="server" ID="Question" TextMode="MultiLine" Rows="5" Columns="35"></asp:TextBox>
      </div>
      <div class="editor-label">
      </div>
      <div class="editor-field">
        <img id="imgCaptcha" src="Captcha.aspx" /></div>
      <div class="editor-label">
        Type the code
      </div>
      <div class="editor-field">
        <asp:TextBox ID="txtCaptchaCode" runat="server" MaxLength="10"></asp:TextBox>
        <asp:RequiredFieldValidator ID="RequiredFieldValidator4" runat="server" ForeColor="Red"
          ErrorMessage="Required" ControlToValidate="txtCaptchaCode" Display="Dynamic" SetFocusOnError="true"></asp:RequiredFieldValidator>
        <asp:CustomValidator ID="CustomValidator1" runat="server" ControlToValidate="txtCaptchaCode"
          OnServerValidate="CheckCaptcha" ErrorMessage="Invalid Code" Display="Dynamic" ForeColor="Red"
          SetFocusOnError="true"></asp:CustomValidator>
      </div>
      <div class="editor-label calign auto" style="width: 100%">
        <asp:Button runat="server" ID="Submit" Text="Submit" CssClass="button" />
      </div>
      <input type="hidden" runat="server" id="SessionKey" />
      </form>
      <div class="clear">
        &nbsp;</div>
    </div>
    <OM:OMPoweredby runat="server" ID="OMPoweredby" />
  </div>
  <OM:OMLabel runat="server" ID="ctlOMLabel" />
  
  <script type="text/javascript">
    _LSCHAT.processUsers = function (data) {
      try {
        var ddloption;
        var loading = $("#spnloading");
        loading.empty();
        $.each(data, function (i, user) {
          $.each(_LSCHAT.SupportUsers, function (j, su) {
            if (user.UserKey.toLowerCase() == su.UserKey.toLowerCase()) {
              ddloption = $("#ddlDepartment option[value=" + su.DepartmentID + "]");
              ddloption.text(ddloption.text().replace("Offline", "Online"));
            }
          });
        });
        if ($("#ddlDepartment option:contains(Online)").length == 1) {
          $($("#ddlDepartment option:contains(Online)").get(0)).attr('selected', 'selected');
        } else {
          $("#ddlDepartment option:first").attr('selected', 'selected');
        }
      } catch (e) {
        //alert(e);
      }
    };
    function init() {
      var ddl = $("#ddlDepartment").get(0);
      if (ddl.length >= 2) {
        ddl.selectedIndex = 1;
      }
      var loading = $("#spnloading");
      loading.html("Loading...");
      $(".depatment_status").html("Offline");
      $("#Name").focus();
      _LSCHAT.isRootPage = true;
      _LSCHAT.EncKey = '<%=SessionKey.Value%>';
      _LSCHAT.getOnlineUsers();
    }
  </script>
  <asp:Literal runat="server" ID="litScript"></asp:Literal>
</body>
</html>
