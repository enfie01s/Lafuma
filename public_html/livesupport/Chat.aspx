<%@ Page Language="vb" AutoEventWireup="false" CodeBehind="Chat.aspx.vb" Inherits="LiveSupport.Chat" %>

<%@ Register TagName="OMHeader" TagPrefix="OM" Src="ctlHeader.ascx" %>
<%@ Register TagName="OMPoweredby" TagPrefix="OM" Src="ctlPoweredby.ascx" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
	<title>Live Support</title>
	<script src="Scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
	<script src="Scripts/jquery.tmpl.min.js" type="text/javascript"></script>
	<script src="Scripts/jquery.highlight.js" type="text/javascript"></script>
	<script src="Scripts/bililiteRange.js" type="text/javascript"></script>
	<script src="Scripts/jquery.sendkeys.js" type="text/javascript"></script>
	<script src="Scripts/SoundAlert.js" type="text/javascript"></script>
	<script src="Scripts/Chat.js" type="text/javascript"></script>
	<link href="Styles/Site.css" rel="stylesheet" type="text/css" />
	<link href="Styles/font.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div class="chat-window">
		<div class="header-box">
			<OM:OMHeader runat="server" ID="OMHeader" />
			<div class="close-chat">
				<a href="javascript:_LSCHAT.closeLivesupport();" class="button">Close</a>
			</div>
		</div>
		<div class="message_box">
		</div>
		<div class="chat_box">
			<div class="btn_area">
				<div class="emo_caller">
					&nbsp;</div>
				<div class="operator_status" style="display:none">
					&nbsp;</div>
				<div class="allow_enter">
					<div style="float: left">
						<input type="checkbox" id="ae" name="ae" />
					</div>
					<div class="ae">
						&nbsp;</div>
				</div>
			</div>
			<div class="chat_area">
				<textarea class="message" rows="8" cols="10" name="m" id="m"></textarea>
			</div>
			<div class="send_area">
				<input type="hidden" value="karthi" name="ToUserName" id="ToUserName">
				<input type="submit" onclick="javascript:_LSCHAT.submitMsg(this);" class="sendbutton button"
					value="Send" name="s" id="s">
      </div>
      <OM:OMPoweredby runat="server" ID="OMPoweredby" />
		</div>
		<div class="emotion_box" style="bottom: 10px;">
		</div>
	  
  </div>
	<input type="hidden" runat="server" id="Question" name="Question" />
	<div id="sa" class="sound_alert">
	</div>
	<script id="messagetemplate" type="text/x-jquery-tmpl">
    <li id="M_${FromName.removeSpace()}" class="username {{if ME==true}}me{{/if}}">
        <div class="mbox">
			  {{if AppendUserName==true}}
					<div class="displayname">
						 ${DisplayName} says
					</div>
					<div class="time">
						${getTime(false)}
					</div>
				{{else}}
				   {{if getTime(true)!=""}}
					  <div class="time">
						  ${getTime(true)}
					  </div>
				    {{/if}}
        {{/if}}
				<div class="message">
					<div class="mes dot">
					</div>
					<p class="p">
					{{html formatMessage(Message)}}
					</p>
				</div>
        </div>
    </li>
	</script>
	<script id="emotiontemplate" type="text/x-jquery-tmpl">
			<ul>
				{{each(i,emo) _Emotions}}
						<li class="emotion" sym="${emo.sym}" title="${emo.tt}">
							<img src="images/smileys/${emo.img}" />
						</li>
				{{/each}}
			</ul>
	</script>
	<asp:Literal runat="server" ID="litScript"></asp:Literal>
</body>
</html>
