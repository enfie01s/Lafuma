var _sendMessageUrl="Service.aspx";
var _idleTime=(1000*300);
var _idleTimer;
var _isWindowActive=true;
var _isLogout=false;
var _isCloseLiveSupport=false;
var LSTYPE={
	Chat: 0
	,Logout: 10
	,Buzz: 15
	,OnlineUsers: 102
	,NoOperation: 16
	,ChangeStatus: 11
  ,ChatSession: 201
  ,ChatSessionCnt: 202
}
var _LSCHAT = {
  UserKey: ""
	, isRootPage: false
	, DisplayName: ""
	, EncKey: ""
	, SupportUsers: new Array()
	, SendQuestion: false
	, currentUserKey: ""
  , currentDepartmentID: 0
  , lastTitle: ""
	, init: function () {
	  this.getOnlineUsers();
	  _LSCHAT.linkToLS();
	  $("#sa").jqSoundAlert();
	  $("#m").focus();
	  var CHATBOX = $(".chat-window");
	  var emotionbox = $(".emotion_box", CHATBOX);
	  $("#emotiontemplate").tmpl("").appendTo(emotionbox);
	  var messagebox = $(".message_box", CHATBOX);
	  messagebox
				.hover(function () {
				  $(emotionbox)
					.removeClass("emotion_box_show");
				})
				.click(function () {
				  $(emotionbox)
					.removeClass("emotion_box_show");
				});
	  $(CHATBOX).hover(function () {
	  }, function () {
	    $(emotionbox)
					.removeClass("emotion_box_show");
	  });
	  $(".buzz_caller", CHATBOX)
			.click(function () {
			  _LSCHAT.chat("BUZZ!");
			});
	  $(".emo_caller", CHATBOX)
				.click(function () {
				  if ($(emotionbox).hasClass("emotion_box_show")) {
				    $(emotionbox)
						.removeClass("emotion_box_show")
						;
				  } else {
				    var b = parseInt(messagebox.css("bottom").replace("px", "")) - 8;
				    $(emotionbox)
						.addClass("emotion_box_show")
						.css("bottom", b)
						;
				  }
				});

	  $(".emotion", CHATBOX).click(function () {
	    var sym = $(this).attr("sym");
	    var m = $("#m", CHATBOX);
	    m.focus(); m.val(m.val() + " " + sym);
	    $(emotionbox)
					.removeClass("emotion_box_show");
	  });

	  var ae = $("#ae", CHATBOX).get(0);
	  var sbtn = $("#s", CHATBOX);
	  var msgtxt = $("#m", CHATBOX);
	  $(".ae", CHATBOX)
			.toggle(function () {
			  ae.checked = true;
			}, function () {
			  ae.checked = false;
			});
	  msgtxt
				.keydown(function (event) {
				  isCtrl = event.ctrlKey;
				  var isctrlenter = false;
				  var isenter = false;
				  var sendmsg = false;
				  if (event.keyCode == '13') {
				    if (isCtrl) {
				      isctrlenter = true;
				    } else {
				      isenter = true;
				    }
				    if (ae.checked == true && isctrlenter == true) {
				      sendmsg = true;
				    }
				    if (ae.checked == false && isctrlenter == true) {
				      msgtxt.sendkeys('{enter}');
				      return false;
				    }
				    if (ae.checked == false && isenter == true && isctrlenter == false) {
				      sendmsg = true;
				    }
				    if (sendmsg) {
				      var m = this.value;
				      this.value = "";
				      _LSCHAT.chat(m);
				      return false;
				    }
				  }
				})
				.keyup(function (event) {
				  isCtrl = false;
				});
	  setInterval(function () {
	    _LSCHAT.animateTitle();
	  }, 1000);
	}
  , startIdleTimer: function () {
    _LSCHAT.stopIdleTimer();
    _idleTimer = setTimeout(function () {
      _LSCHAT.logout(true);
    }, _idleTime);
  }
  , stopIdleTimer: function () {
    if (_idleTimer) {
      clearTimeout(_idleTimer);
    }
  }
  , clearLastTitle: function () {
    _LSCHAT.lastTitle = "";
  }
	, animateTitle: function () {
	  var m = "";
	  if (document.title == "Live Support") {
	    m = _LSCHAT.lastTitle;
	  } else {
	    m = "Live Support";
	  }
	  if (m == "" || m == null) {
	    m = "Live Support";
	  }
	  if (_isWindowActive == false) {
	    document.title = m;
	  } else {
	    document.title = "Live Support";
	  }
	}
	, addSupportUser: function (userkey, departmentID) {
	  this.SupportUsers[this.SupportUsers.length] = { UserKey: userkey, DepartmentID: departmentID };
	}
	, JQXHR: null
	, Connected: false
	, connectToServer: function () {
	  var dt = new Date();
	  var param = 'key=' + _LSCHAT.EncKey + '&dt=' + dt.getTime();
	  //XMLHttpRequest
	  _LSCHAT.JQXHR = $.ajax({
	    type: "get",
	    dataType: "json",
	    url: "httpbinding",
	    data: param,
	    success: function (msg) {
	      _LSCHAT.processData(msg);
	      if (_LSCHAT.Connected) {
	        _LSCHAT.connectToServer();
	      }
	    },
	    error: function (message) {
	      try {
	        _LSCHAT.logout(true);
	      } catch (e) { }
	      //alert("error: "+message.responseText);
	    }
	  });
	}
	, stopServer: function () {
	  _LSCHAT.JQXHR.abort();
	}
	, processData: function (data) {
	  _LSCHAT.startIdleTimer();

	  var lpacket;
	  switch (data.Type) {
	    case "Chat":
	      _LSCHAT.displayChatMessage(data.From, data.Message, data.From, "");
	      _LSCHAT.sendMessage(LSTYPE.ChatSession, data.From, "");
	      break;
	    //case "Buzz":           
	    //	_LSCHAT.displayChatMessage(data.From,"BUZZ!",data.From);           
	    //	break;           
	    case "Logout":
	      var u = UserCollection.getUser(data.From);
	      if (u) {
	        if (_LSCHAT.currentUserKey == u.UserKey) {
	          //  _LSCHAT.currentUserKey = "";
	          _LSCHAT.livesupportOfflineMessage();
	        }
	        UserCollection.removeUser(data.From);
	      }
	      break;
	    case "User":
	      if (_LSCHAT.currentUserKey == data.UserKey) {
	        $(".offline").remove();
	        $(".chat_box").show();
	      }
	      UserCollection.addUser(data);
	      break;
	    case "ChangeStatus":
	      if (_LSCHAT.currentUserKey == data.From) {
	        var h = "";
	        if ($.trim(data.Message) != "") {
	          var u = UserCollection.getUser(_LSCHAT.currentUserKey);
	          if (u) {
	            //h+=u.DisplayName+"&nbsp;("+data.Message+")";
	            h += "Operator's Status:&nbsp;" + data.Message;
	          }
	        }
	        $(".operator_status").hide().html(h);
	        if ($.trim(h) != "") {
	          $(".operator_status").show();
	        }
	      }
	      break;
	  }
	}
	, getDisplayName: function (userkey) {
	  var displayName = "";
	  try {
	    displayName = UserCollection.getUser(userkey).DisplayName;
	  } catch (e) { }
	  return displayName;
	}
	, displayChatMessage: function (fromuserkey, msg, key, advalue) {
	  try {
	    var data = this.getCWDataObject();
	    var tkey = (new Date).getTime();
	    data.DisplayName = _LSCHAT.getDisplayName(fromuserkey);
	    data.Title = data.DisplayName;
	    data.FromName = fromuserkey;
	    data.Key = key;
	    data.ToName = _LSCHAT.UserKey;
	    data.Message = msg;
	    var content = $(".message_box");
	    if ($.trim(data.DisplayName) == "") {
	      data.DisplayName = data.FromName;
	    }
	    this.appendMessage(content, data);
	  } catch (e) {
	    alert("displayChatMessage->" + e);
	  }
	}
	, appendMessage: function (content, data) {
	  var mbox = content;
	  var lastMessage = $(".username:last", content).get(0);
	  data.AppendUserName = true;
	  if (lastMessage) {
	    if (lastMessage.id == "M_" + data.FromName.removeSpace()) {
	      data.AppendUserName = false;
	    }
	  }
	  if (data.Message == "BUZZ!") {
	    data.Message = "<span class='BUZZ'>" + data.Message + "</span>";
	  }
	  if (isME(data.FromName) == false) {
	    _LSCHAT.lastTitle = data.DisplayName + " [New Message]!";
	  }
	  _LSCHAT.animateTitle();
	  $("#messagetemplate").tmpl(data).appendTo(content);
	  if (data.FromName != _LSCHAT.UserKey) {
	    $("#sa").play();
	  }
	  _LSCHAT.scrollObj(mbox);
	}
	, scrollObj: function (target) {
	  var t = $(target).get(0);
	  if (t) { t.scrollTop = t.scrollHeight; }
	}
	 , getCWDataObject: function () {
	   var data = {};
	   data.Message = "";
	   data.DisplayName = "";
	   data.ME = false;
	   data.Groups = new Array();
	   data.ISGC = false;
	   data.ISANN = false;
	   data.ISOFF = false;
	   data.GroupName = "";
	   data.FromName = "";
	   data.ToName = "";
	   data.GCKey = "";
	   data.Key = "";
	   data.SessionKey = _LSCHAT.EncKey;
	   data.Emotions = _Emotions;
	   data.Title = "";
	   data.ReceivedDate = "";
	   data.Subject = "";
	   return data;
	 }
	, getOnlineUsers: function () {
	  var type = LSTYPE.OnlineUsers;
	  var url = _sendMessageUrl + "?type=" + type + "&sessionkey=" + _LSCHAT.EncKey;
	  $.getJSON(url, function (data) {
	    _LSCHAT.processUsers(data);
	  });
	}
	, processUsers: function (data) {
	  $.each(data, function (i, user) {
	    UserCollection.addUser(user);
	  });
	  _LSCHAT.Connected = true;
	  _LSCHAT.connectToServer();
	  if (_LSCHAT.SendQuestion == false) {
	    _LSCHAT.SendQuestion = true;
	    var q = $("#Question").val();
	    var lq = "Question:";
	    _LSCHAT.chat(q, q.substring(q.indexOf(lq) + lq.length));
	    _LSCHAT.sendMessage(LSTYPE.ChatSessionCnt, _LSCHAT.currentUserKey, "");
	  }
	}
	, submitMsg: function (sendbutton) {
	  var chat_box = $(sendbutton).parents(".chat_box:first");
	  if (chat_box.get(0)) {
	    var msg = $("#m", chat_box).val();
	    _LSCHAT.chat(msg);
	    $("#m", chat_box).val("").focus();
	  }
	}
	, setOnlineUserName: function () {
	  try {
	    $.each(_LSCHAT.SupportUsers, function (j, su) {
	      if (su.UserKey != "") {
	        var user = UserCollection.getUser(su.UserKey);
	        if (user && _LSCHAT.currentUserKey == "") {
	          if (user.UserKey != "") {
	            _LSCHAT.currentUserKey = user.UserKey;
	            _LSCHAT.currentDepartmentID = su.DepartmentID;
	          }
	        }
	      }
	    });
	  } catch (e) {
	    alert(e);
	  }
	}
	, chat: function (msg, appendmsg) {
	  if (appendmsg == undefined) appendmsg = msg;
	  var content = $(".message_box");
	  if (_LSCHAT.currentUserKey != "") {
	    var currentUser = UserCollection.getUser(_LSCHAT.currentUserKey);
	    if (!currentUser) {
	      _LSCHAT.livesupportOfflineMessage();
	      return true;
	      //_LSCHAT.currentUserKey = "";
	      //this.setOnlineUserName();
	    }
	  }
	  if (_LSCHAT.currentUserKey == "") {
	    this.setOnlineUserName();
	  }
	  if ($.trim(_LSCHAT.currentUserKey) == "") {
	    _LSCHAT.livesupportOfflineMessage();
	    return true;
	  }
	  if ($.trim(msg) != "") {
	    var data = { "FromName": _LSCHAT.UserKey, "Message": appendmsg, "ME": true };
	    data.DisplayName = _LSCHAT.DisplayName;
	    _LSCHAT.appendMessage(content, data);
	    _LSCHAT.sendMessage(LSTYPE.Chat, _LSCHAT.currentUserKey, msg);
	  }
	  return false;
	}
	  , livesupportOfflineMessage: function () {
	    var content = $(".message_box");
	    if (!$(".offline").get(0)) {
	      $(content).append("<span class='offline'>&nbsp;&nbsp;&nbsp;-----&nbsp;Operator is offline&nbsp;------</span>");
	    }
	    $(".chat_box").hide();
	  }
	, sendMessage: function (type, dest, message, gckey) {
	  if (gckey == undefined) gckey = '';
	  var param = new Array();
	  if (message == "BUZZ!") { type = LSTYPE.Buzz; }
	  param[param.length] = { "name": "type", "value": type };
	  param[param.length] = { "name": "touser", "value": dest };
	  param[param.length] = { "name": "groupchatkey", "value": gckey };
	  param[param.length] = { "name": "sessionkey", "value": _LSCHAT.EncKey };
	  param[param.length] = { "name": "message", "value": message };
	  param[param.length] = { "name": "userkey", "value": _LSCHAT.UserKey };
	  param[param.length] = { "name": "departmentid", "value": _LSCHAT.currentDepartmentID };
	  $.post(_sendMessageUrl, param);
	  if (_isLogout == false) _LSCHAT.startIdleTimer();
	}
	, logout: function (sendtols) {
	  try {
	    _LSCHAT.Connected = false;
	    _isLogout = true;
	    if (sendtols) {
	      var param = new Array();
	      param[param.length] = { "name": "type", "value": LSTYPE.Logout };
	      param[param.length] = { "name": "userkey", "value": _LSCHAT.UserKey };
	      param[param.length] = { "name": "sessionkey", "value": _LSCHAT.EncKey };
	      param[param.length] = { "name": "touser", "value": _LSCHAT.currentUserKey };
	      $.post(_sendMessageUrl, param);
	      if (_isCloseLiveSupport) {
	        _isCloseLiveSupport = false;
	        window.close();
	      }
	    }
	    location.href = "Default.aspx";
	  }
	  catch (e) {
	    // alert(e); 
	  }
	}
	, closeLivesupport: function () {
	  if (confirm("Are you sure to close Live Support?")) {
	    _isCloseLiveSupport = true;
	    _LSCHAT.logout(true);
	  }
	}
  , linkToLS: function () {
    setInterval(function () {
      var param = new Array();
      param[param.length] = { "name": "type", "value": LSTYPE.NoOperation };
      param[param.length] = { "name": "sessionkey", "value": _LSCHAT.EncKey };
      $.post(_sendMessageUrl, param);
    }, (1000 * 60)); // One minute
  }
	, sendStatus: function (type, status) {
	  var param = new Array();
	  param[param.length] = { "name": "type", "value": type };
	  param[param.length] = { "name": "status", "value": status };
	  param[param.length] = { "name": "from", "value": _LSCHAT.UserKey };
	  param[param.length] = { "name": "sessionkey", "value": _LSCHAT.EncKey };
	  $.post(_sendMessageUrl, param);
	}
};
var UserCollection={
	Users: new Array(),
	addUser: function (data) {
		var U=UserCollection.Users[data.UserKey];
		if(!U) {
			U=new User();
		}
		U.UserKey=data.UserKey;
		U.DisplayName=data.DisplayName;
		U.GroupName=data.GroupName;
		U.UserStatus=data.UserStatus;
		U.UserGender=data.Gender;
		U.PersonalStatus=data.PersonalStatus;
		UserCollection.Users[data.UserKey]=U;
	},
	removeUser: function (key) {
		delete UserCollection.Users[key];
	},
	checkUserExists: function (key) {
		if(key in UserCollection.Users) {
			return true;
		} else {
			return false;
		}
	},
	getUser: function (key) {
		return UserCollection.Users[key];
	},
	getUsers: function () {
		return UserCollection.Users;
	},
	getGroupUsers: function (groupName) {
		var users=new Array();
		for(var key in UserCollection.Users) {
			var u=UserCollection.Users[key];
			if(u.GroupName==groupName) {
				users[users.length]=u;
			}
		}
		return users;
	},
	getGroups: function () {
		var groups=new Array();
		for(var key in UserCollection.Users) {
			var u=UserCollection.Users[key];
			var groupExist=false;
			$.each(groups,function (i,g) {
				if(g==u.GroupName) {
					groupExist=true;
				}
			});
			if(groupExist==false) {
				if(u.GroupName!=undefined) {
					groups[groups.length]=u.GroupName;
				}
			}
		}
		groups.sort();
		return groups;
	},
	deleteAllUsers: function () {
		UserCollection.Users=new Array();
	},
	deleteAllUsersExceptMe: function () {
		for(var key in UserCollection.Users) {
			if(key!=_LSCHAT.UserKey) {
				delete UserCollection.Users[key];
			}
		}
	}
}

function User() {
	this.UserKey='';
	this.DisplayName='';
	this.GroupName='';
	this.UserStatus='';
	this.UserGender='';
	this.PersonalStatus='';
}
var _Emotions=[
	{ sym: ":)",tt: "Smile",img: "smile.gif",regExp: ":\)" }
	,{ sym: "(VH)",tt: "Very Happy",img: "veryhappy.gif",regExp: "\(VH\)" }
	,{ sym: "(BT)",tt: "Baring Teeth",img: "Baring_teeth.gif",regExp: "\(BT\)" }
	,{ sym: "(WK)",tt: "Winking",img: "winking.gif",regExp: "\(WK\)" }
	,{ sym: "(SH)",tt: "Shocked",img: "shocked.gif",regExp: "\(SH\)" }
	,{ sym: "-t)",tt: "OMG",img: "omg.gif",regExp: "-t\)" }
	,{ sym: ":p",tt: "Tongue out",img: "tonque-out.gif",regExp: ":p" }
	,{ sym: ":w)",tt: "Nerd",img: "nerd.gif",regExp: ":w\)" }
	,{ sym: "(AN)",tt: "Angry",img: "Angry.gif",regExp: "\(AN\)" }
	,{ sym: "(AH)",tt: "Ashamed",img: "Ashamed.gif",regExp: "\(AH\)" }
	,{ sym: ":^)",tt: "I don't know",img: "I-dont-know.gif",regExp: ":\^\)" }
	,{ sym: ":-s",tt: "Confused",img: "confused.gif",regExp: ":-s" }
	,{ sym: ":')",tt: "Crying",img: "crying.gif",regExp: ":'\)" }
	,{ sym: ":(",tt: "Sad",img: "sad.gif",regExp: ":\(" }
	,{ sym: ":-#",tt: "Don't tell everyone",img: "Dont_tell_anyone.gif",regExp: ":-\#" }
	,{ sym: "(B)",tt: "Bye",img: "Bye.gif",regExp: "\(B\)" }
	,{ sym: "*-)",tt: "Thinking",img: "Thinking.gif",regExp: "\*-\)" }
	,{ sym: ":s)",tt: "Sorry",img: "sorry.gif",regExp: ":s\)" }
	,{ sym: "|-)",tt: "Sleepy",img: "Sleepy.gif",regExp: "-\)" }
	,{ sym: "+o(",tt: "Sick",img: "sick.gif",regExp: "\+o\(" }
	,{ sym: "(C)",tt: "Cool",img: "cool.gif",regExp: "\(C\)" }
	,{ sym: "-c)",tt: "Angel",img: "angel.gif",regExp: "-c\)" }
	,{ sym: "(D)",tt: "Devil",img: "devil.gif",regExp: "\(D\)" }
	,{ sym: ":o)",tt: "Party",img: "party.gif",regExp: ":o\)" }
	,{ sym: ";w)",tt: "Whistle",img: "whistle.gif",regExp: ";w\)" }
	,{ sym: "(BRB)",tt: "brb",img: "brb.gif",regExp: "\(BRB\)" }
	,{ sym: ":-*",tt: "Secret Telling",img: "secret.gif",regExp: ":-\*" }
	,{ sym: "-h)",tt: "Headache",img: "headache.gif",regExp: "-h\)" }
	,{ sym: "(GF)",tt: "Gift",img: "Gift.gif",regExp: "\(GF\)" }
	,{ sym: "(^)",tt: "Birthday Cake",img: "Birthday_cake.gif",regExp: "\(\^\)" }
	,{ sym: "(L)",tt: "Heart",img: "Heart.gif",regExp: "\(L\)" }
	,{ sym: "(U)",tt: "Broken Heart",img: "Broken_heart.gif",regExp: "\(U\)" }
	,{ sym: "(*)",tt: "Star",img: "Star.gif",regExp: "\(\*\)" }
	,{ sym: "(O)",tt: "Clock",img: "clock.gif",regExp: "\(O\)" }
	,{ sym: "(CF)",tt: "Coffee",img: "Coffee.gif",regExp: "\(CF\)" }
	,{ sym: "(PL)",tt: "Food",img: "food.gif",regExp: "\(PL\)" }
	,{ sym: "($)",tt: "Money",img: "Money.gif",regExp: "\(\$\)" }
	,{ sym: "(H5)",tt: "Clapping Hands",img: "Clapping_hands.gif",regExp: "\(H5\)" }
	,{ sym: "(YN)",tt: "FingersCrossed",img: "Fingerscrossed.gif",regExp: "\(YN\)" }
	,{ sym: "(SN)",tt: "Snail",img: "Snail.gif",regExp: "\(SN\)" }
	,{ sym: "(F)",tt: "Rose",img: "Rose.gif",regExp: "\(F\)" }
	,{ sym: "(W)",tt: "Wilted Rose",img: "Wilted_rose.gif",regExp: "\(W\)" }
	,{ sym: "(SO)",tt: "Play",img: "play.gif",regExp: "\(SO\)" }
	,{ sym: "-i)",tt: "Idea",img: "idea.gif",regExp: "-i\)" }
	,{ sym: "(BR)",tt: "Beer",img: "Beer.gif",regExp: "\(BR\)" }
	,{ sym: "(P)",tt: "Phone",img: "phone.gif",regExp: "\(P\)" }
	,{ sym: "(Y)",tt: "Thumbs Up",img: "Thumbs_up.gif",regExp: "\(Y\)" }
	,{ sym: "(N)",tt: "Thumbs Down",img: "Thumbs_down.gif",regExp: "\(N\)" }
];

	$.extend(window, {
	  formatMessage: function (m) {
	    try {
	      m = m.replace(/\n/g, '<br/>');
	      var d = $("<div>" + m + "</div>");
	      $.each(_Emotions, function (i, emo) {
	        var li = '<img src="images/smileys/' + emo.img + '" title="' + emo.tt + '" class="memo" />';
	        d.highlight(emo.sym, li);
	      });
	      $("a", d).attr("target", "_blank");
	      $(".highlight", d).each(function () {
	        $(this).before($(this).attr("re")).remove();
	      });
	      var filter = /(http:\/\/|www\.)+[A-Za-z0-9\.-]{3,}\.[A-Za-z]{2,4}/gi; //http://www.yahoo.com or www.yahoo.com
	      d.highlightURL(filter);
	      return d.html();
	    } catch (e) {
	      alert(e.Message);
	      return m;
	    }
	  }
    , isME: function (userkey) {
      return (userkey == _LSCHAT.UserKey);
    }
    , getSessionKey: function () {
      return _LSCHAT.EncKey;
    }, getTime: function (checkLast) {
      var currentTime = new Date();
      var hours = currentTime.getHours();
      var minutes = currentTime.getMinutes();
      var seconds = currentTime.getSeconds();
      var suffix = "AM";
      if (hours >= 12) {
        suffix = "PM";
        hours = hours - 12;
      }
      if (hours == 0) {
        hours = 12;
      }
      if (minutes < 10) {
        minutes = "0" + minutes;
      }
      if (seconds < 10) {
        seconds = "0" + seconds;
      }
      var t = hours + ":" + minutes + " " + suffix;
      if (checkLast == true) {
        var lt = $(".time:last").html();
        if ($.trim(lt) == t) {
          return "";
        } else {
          return t;
        }
      } else {
        return t;
      }
    }
	});

  (function () {
    jQuery.fn.highlightURL = function (str) {
      var regex = new RegExp(str);
      return this.each(function () {
        this.innerHTML = this.innerHTML.replace(regex, function (matched) { return "<a href='" + (((matched.indexOf("http://") < 0) && (matched.indexOf("HTTP://") < 0))? "http://" + matched : matched)  + "' target='_blank'>" + matched + "</a>"; });
      });
    };
  })();

String.prototype.removeSpace=function () {
	return this.replace(" ","").replace("@","").replace(".","");
}
$(document)
	.click(function () { _LSCHAT.clearLastTitle(); })
	.hover(function () { _LSCHAT.clearLastTitle(); })
  .focus(function () { _isWindowActive=true; })
  .mousemove(function () {	_isWindowActive=true;})
  .keypress(function () {	_isWindowActive=true;})
  .blur(function () {	_isWindowActive=false;})
  .bind("beforeunload",function (event) {
    if(_isLogout==false && _LSCHAT.isRootPage==false) {return "This action will exit Live Support.";}
   });
