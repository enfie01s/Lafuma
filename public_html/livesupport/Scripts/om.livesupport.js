var omlivesupport = {
  open: function (from) {
    var lwidth = 480;
    var lheight = 620;
    var lleft = (screen.availWidth / 2) - (lwidth / 2);
    var ltop = (screen.availHeight / 2) - (lheight / 2);
    var features = "width=" + lwidth + ",height=" + lheight + ",left=" + lleft + ",top=" + ltop + ",location=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,status=yes";
    window.open(om_url + "/Default.aspx?from=" + from, "LiveSupportWindow", features);
  }
};