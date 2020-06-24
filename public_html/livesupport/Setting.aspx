<%@ Page Language="vb" AutoEventWireup="false" CodeBehind="Setting.aspx.vb" Inherits="LiveSupport.Setting_" %>
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
	<link href="Styles/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
	<script src="Scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
	<script src="Scripts/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
	<script src="Scripts/jquery.tmpl.min.js" type="text/javascript"></script>
	<script src="Scripts/jquery.multiselect2side.js" type="text/javascript"></script>
	<script src="Scripts/Setting.js" type="text/javascript"></script>
</head>
<body>
	<div id="content" class="static-content">
    <OM:OMHeader runat="server" ID="OMHeader" />
		<div class="user-detail setting_box">
			<form id="frmSetting" onsubmit="return false">
			<div class="editor-label title">
				<b>Live Support Settings</b></div>
			<div class="editor-field" style="float:right;padding:10px;">
				<a href="Logout.aspx" class="button">Logout</a>
			</div>
			<div class="editor-label">SupportScript
        Website visitors who sign in through Live Support will be added temporarily in User Master.
      </div>
			<div class="editor-label editor-field" id="LoadSetting">
			</div>
      <div class="editor-label">
        Specify the Group name under which Live Support visitors should be added
      </div>
			<div class="editor-label">
				Live Visitor(s) Group Name</div>
			<div class="editor-field">
				<select id="LiveUserGroupKey" name="LiveUserGroupKey" style="width: 254px;">
					<option value="">--Select One--</option>
				</select>
			</div>
      <div class="editor-label">
        Specify any manager role user account (Required for Live Support plugin to add Live Website Visitor in User Master)
      </div>
			<div class="editor-label">
				Authentication UserName</div>
			<div class="editor-field">
				<input type="text" name="AuthenticationUserName" id="AuthenticationUserName" />
			</div>
			<div class="editor-label">
				Authentication Password</div>
			<div class="editor-field">
				<input type="password" name="AuthenticationPassword" id="AuthenticationPassword" />
			</div>
			<div class="editor-label ralign" id="spn_setting_loading">
			</div>
			<div class="editor-field">
				<input type="button" value="Save" class="button" onclick="javascript:_SETTING.saveSetting();" />
			</div>
			<input type="hidden" name="mode" value="savesetting" />
			</form>
			<div class="clear">
				&nbsp;</div>
		</div>
		<div class="user-detail setting_box">
			<div class="editor-label title">
				<b>Support Departments & Operators</b></div>
			<div class="editor-field ralign right_col">
				<input type="button" value="Add Department" class="button" onclick="javascript:_SETTING.open(0);" />
			</div>
			<div class="grid_box">
				<table cellspacing="0" cellpadding="0" id="DepartmentList" class="grid">
					<thead>
						<tr>
							<th style="width: 30%">
								Department Name
							</th>
							<th>
								Operators
							</th>
							<th style="width: 10%">
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="clear">
				&nbsp;</div>
		</div>
		<div class="user-detail setting_box" id="Department">
			<form id="frmDepartment" onsubmit="return false">
			<div class="editor-label" style="width: auto">
				Department Name</div>
			<div class="editor-field">
				<input type="text" id="DepartmentName" name="DepartmentName" />
			</div>
			<div class="editor-label">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td>
						</td>
					</tr>
				</table>
				Operators</div>
			<div class="editor-field auto">
				<select name="searchable[]" id='searchable' multiple='multiple'>
					<option value='1'>Option strawberry 1 - India</option>
					<option value='2'>Option apricot 2 - Italy</option>
					<option value='997'>Option pineapple 997 - Mexico</option>
					<option value='998'>Option pear 998 - Mexico</option>
					<option value='999'>Option strawberry 999 - China</option>
					<option value='1000'>Option melon 1000 - Mexico</option>
				</select>
			</div>
			<div class="editor-label calign auto" style="width: 100%">
				<span id="spn_setting_loading"></span>
				<input type="button" value="Submit" class="button" onclick="javascript:_SETTING.saveDepartment();" />
				&nbsp;&nbsp;
				<input type="button" value="Close" class="button" onclick="javascript:_SETTING.closeDialog();" />
			</div>
			<input type="hidden" name="id" id="id" value="0" />
			<input type="hidden" name="mode" value="savedepartment" />
			</form>
			<div class="clear">
				&nbsp;</div>
		</div>
    <div class="user-detail setting_box">
			<div class="editor-label title">
				<b>Scripts</b></div>
      <div class="editor-label">
        To install Live Support, Copy below code and paste it to your website source code.
      </div>
			<div class="editor-label">
				Live Support Script</div>
			<div class="editor-field">
				<textarea runat="server" id="SupportScript" rows="4" cols="40" readonly="readonly"></textarea>
			</div>
			<input type="hidden" name="mode" value="savesetting" />
			<div class="clear">
				&nbsp;</div>
		</div>
    <OM:OMPoweredby runat="server" ID="OMPoweredby" />
	</div>
  
	<OM:OMLabel runat="server" ID="ctlOMLabel" />
	<script id="GridTemplate" type="text/x-jquery-tmpl">
		{{each(i,row) Result}}
<tr id="Row${row.DepartmentID}" {{if i%2>0}}class="row"{{else}}class="arow"{{/if}}>
	<td>
		${DepartmentName}
	</td>
	<td>
		${getUserNames(row.Users)}
	</td>
	<td style="text-align:right;white-space:nowrap;">
		<img src='Images/Edit.gif' style='cursor:pointer' onclick="javascript:_SETTING.open(${DepartmentID});" />
		&nbsp;&nbsp;
		<img src='Images/Delete.png' style='cursor:pointer' onclick="javascript:_SETTING.deleteDepartment(${DepartmentID},this);" />
	</td>
</tr>
	{{/each}}
</script>
</body>
</html>
