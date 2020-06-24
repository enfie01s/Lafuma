var _Department;
var _DepartmentUser;
var _AjaxUrl="AjaxRequest.aspx";
$(document).ready(function () {
	_Department=$("#Department")
	.dialog({
		height: 430,
		width: 620,
		title: "Department",
		autoOpen: false,
		modal: true
	});
$("#LiveUserGroupKey").focus();
	_SETTING.loadUsers();
	_SETTING.loadGroups();
	_SETTING.loadGrid();
});
var _SETTING = {
  init: false
	, usersData: null
	, groups: new Array()
	, open: function (id) {
	  _SETTING.clearSelectedOptions();
	  _Department.dialog("open");
	  $(":input[type='text']", _Department).val("");
	  $(".ms2side__div").remove();

	  var searchable = $("#searchable");
	  var livevisitorgroup = $("#LiveUserGroupKey option:selected").text();
	  $("optgroup option", searchable).removeAttr("livevisitor");
	  $("optgroup[label='" + livevisitorgroup + "'] option", searchable).attr("livevisitor", "true");

	  searchable.multiselect2side("destroy");
	  searchable
		.show()
		.multiselect2side({
		  optGroupSearch: "Group: ",
		  search: "<img src='Images/search.gif' />"
		})
		.hide()
		;
	  var frm = $("#frmDepartment");
	  var loading = $("#LoadSetting");
	  $("#DepartmentName", frm).val("");
	  $("#id", frm).val(0);
	  var ddl = $("#searchablems2side__dx").get(0);
	  ddl.options.length = null;
	  loading.html("Loading...");
	  var leftopt = $("#searchablems2side__sx").get(0);
	  $.each(leftopt.options, function (z, opt) {
	    opt.selected = false;
	  });
	  $.ajax({
	    type: "GET",
	    url: _AjaxUrl + "?mode=depatment&id=" + id,
	    cache: false,
	    dataType: "JSON",
	    success: function (data) {
	      loading.empty();
	      if (_SETTING.processData(data)) {
	        if (data.Result != null) {
	          $("#DepartmentName", frm).val(data.Result.DepartmentName);
	          $("#id", frm).val(data.Result.DepartmentID);
	          var listItem;
	          try {
	            $.each(data.Result.Users, function (i, u) {
	              listItem = new Option(u.DisplayName, u.UserKey, false, false);
	              ddl.options[ddl.options.length] = listItem;
	              _SETTING.removeUser(u.UserKey);
	              $("#searchable option[value='" + u.UserKey + "']").attr('selected', 'selected');
	            });
	          } catch (e) {
	            alert(e);
	          }
	        }
	      }
	    },
	    error: function (data) { alert(data); }
	  });
	}
	, closeDialog: function () {
	  _Department.dialog("close");
	}
	, setGridClass: function () {
	  var t = $("#DepartmentList");
	  $("tr:odd", "tbody").removeClass("row").reomveClass("arow").addClass("row");
	  $("tr:even", "tbody").removeClass("row").reomveClass("arow").addClass("arow");
	}
	, removeUser: function (name) {
	  var ddlleft = $("#searchablems2side__sx").get(0);
	  $.each(ddlleft.options, function (i, opt) {
	    if (opt != undefined) {
	      if (opt.value == name) {
	        ddlleft.options[i] = null;
	      }
	    }
	  });
	}
	, loadSetting: function () {
	  var loading = $("#LoadSetting");
	  loading.html("Loading...");
	  $.ajax({
	    type: "GET",
	    url: _AjaxUrl + "?mode=settinglist",
	    cache: false,
	    dataType: "JSON",
	    success: function (data) {
	      loading.empty();
	      if (_SETTING.processData(data)) {
	        $.each(data.Result, function (i, item) {
	          $(":input[name='" + item.Name + "']").val(item.Value);
	        });
	      }
	    },
	    error: function (data) { alert(data); }
	  });
	}
	, loadUsers: function () {
	  var loading = $("#LoadSetting");
	  var ddl = $('#searchable').get(0);
	  ddl.options.length = null;
	  //loading.html("Loading...");
	  $.ajax({
	    type: "GET",
	    url: _AjaxUrl + "?mode=userslist",
	    cache: false,
	    dataType: "JSON",
	    success: function (data) {
	      loading.empty();
	      _SETTING.usersData = data;
	      _SETTING.loadUsersDDL();
	    },
	    error: function (data) { alert(data); }
	  });
	}
	, loadGroups: function () {
	  var loading = $("#LoadSetting");
	  var ddl = $('#LiveUserGroupKey').get(0);
	  ddl.options.length = null;
	  //loading.html("Loading...");
	  $.ajax({
	    type: "GET",
	    url: _AjaxUrl + "?mode=grouplist",
	    cache: false,
	    dataType: "JSON",
	    success: function (data) {
	      loading.empty();
	      ddl = $('#LiveUserGroupKey').get(0);
	      _SETTING.logGroupsDDL(data, ddl);
	      _SETTING.loadSetting();
	    },
	    error: function (data) { alert(data); }
	  });
	}
	, logGroupsDDL: function (data, ddl) {
	  ddl.options.length = null;
	  var opt = document.createElement("option");
	  opt.value = "";
	  opt.appendChild(document.createTextNode("--Select One--"));
	  ddl.appendChild(opt);
	  if (_SETTING.processData(data)) {
	    $.each(data.Result, function (i, g) {
	      // create options and attach to optgroups
	      var opt = document.createElement("option");
	      opt.value = g.GroupKey;
	      opt.appendChild(document.createTextNode(g.GroupName));
	      ddl.appendChild(opt);
	    });
	  }
	}
	, loadUsersDDL: function () {
	  try {
	    var data = _SETTING.usersData;
	    var ddl = $('#searchable').get(0);
	    ddl.options.length = null;
	    if (_SETTING.processData(data)) {
	      var groups = new Array();
	      $.each(data.Result, function (i, item) {
	        _SETTING.addUser(item);
	      });
	      $.each(_SETTING.groups, function (i, g) {
	        // create optgroups
	        var gru = document.createElement("optgroup");
	        gru.label = g.GroupName;
	        $.each(g.Users, function (i, u) {
	          // create options and attach to optgroups
	          var opt = document.createElement("option");
	          opt.value = u.UserKey;
	          opt.appendChild(document.createTextNode(u.DisplayName));
	          gru.appendChild(opt);
	        });
	        ddl.appendChild(gru);
	      });
	    }
	  } catch (e) {
	    alert("loadUsersDDL->" + e);
	  }
	}
  , clearSelectedOptions: function () {
    var ddl = $('#searchable').get(0);
    $.each(ddl.options, function (i, opt) {
      if (opt != undefined) {
        opt.selected = false;
      }
    });
  }
	, addUser: function (data) {
	  if (data.GroupName == "") {
	    data.GroupName = "Unknown";
	  }
	  var d = null;
	  $.each(this.groups, function (i, g) {
	    if (g.GroupName == data.GroupName) {
	      d = g;
	    }
	  });
	  if (d == null) {
	    d = { GroupName: data.GroupName, Users: new Array() };
	    this.groups[this.groups.length] = d;
	  }
	  d.Users[d.Users.length] = { UserKey: data.UserKey, DisplayName: data.DisplayName };
	}
	, saveSetting: function () {
	  this.save($("#frmSetting"), function () {
	    alert("Settings saved");
	  });
	}
	, saveDepartment: function () {
	  var ddl = $("#searchablems2side__dx", $("#frmDepartment")).get(0);
	  $.each(ddl.options, function (i, opt) {
	    if (opt != undefined) {
	      opt.selected = true;
	    }
	  });
	  this.save($("#frmDepartment"), function () {
	    //alert("Department saved");
	    _Department.dialog("close");
	    _SETTING.loadGrid();
	  });
	}
	, saveDepartmentUser: function () {
	  this.save($("#frmDepartment"), function () {
	    alert("Department user saved");
	  });
	}
	, processData: function (data) {
	  if (data.Error != "") {
	    if (data.Error == "SESSION EXPIRED") {
	      location.href = "Admin.aspx";
	    } else {
	      alert(data.Error);
	    }
	    return false;
	  }
	  return true;
	}
	, save: function (frm, onsuccess) {
	  var loading = $("#spn_setting_loading", frm);
	  loading.html("Saving...");
	  $.ajax({
	    type: "POST",
	    url: _AjaxUrl,
	    cache: false,
	    data: frm.serializeArray(),
	    dataType: "JSON",
	    success: function (data) {
	      loading.empty();
	      if (_SETTING.processData(data)) {
	        if (onsuccess) {
	          onsuccess();
	        }
	      }
	    },
	    error: function (data) { alert(data); }
	  });
	}
	, loadGrid: function () {
	  var loading = $("#Loading");
	  loading.html("Loading...");
	  var t = $("#DepartmentList");
	  $("tbody", t).remove();
	  $.ajax({
	    type: "POST",
	    url: _AjaxUrl + "?mode=depatmentlist",
	    cache: false,
	    dataType: "JSON",
	    success: function (data) {
	      loading.empty();
	      if (_SETTING.processData(data)) {
	        var tbody = document.createElement("tbody");
	        $("#GridTemplate").tmpl(data).appendTo(tbody);
	        $(t).append(tbody);
	      }
	    },
	    error: function (data) { alert(data); }
	  });
	}
	, deleteDepartment: function (id, that) {
	  if (confirm("Are you sure you want to delete this department?")) {
	    var loading = $("#LoadSetting");
	    loading.html("Loading...");
	    var tr = $(that).parents("tr:first");
	    $.ajax({
	      type: "GET",
	      url: _AjaxUrl + "?mode=deletedepartment&id=" + id,
	      cache: false,
	      dataType: "JSON",
	      success: function (data) {
	        loading.empty();
	        if (_SETTING.processData(data)) {
	          //alert("Department deleted");
	          $(tr).remove();
	          _SETTING.loadGrid();
	        }
	      },
	      error: function (data) { alert(data); }
	    });
	  }
	}
};

$.extend(window,{
	getUserNames: function (users) {
		var n="";
		$.each(users, function (i, u) {
			n+=u.DisplayName+", ";
		});
		if(n!="") {
			n=n.substr(0,n.length-2);
		}
		return n;
	}
});