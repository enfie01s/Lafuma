var om_support={
	img: "Images/offline.png"
	,init: function () {
		var img=$("<img id='imgls' style='cursor:pointer;border:0;' src='"+om_support.img+"'/>");
		img
		.click(function () {
			var lwidth=800;
			var lheight=600;
			var lleft=(screen.availWidth/2)-(lwidth/2);
			var ltop=(screen.availHeight/2)-(lheight/2);
			var features="width="+lwidth+",height="+lheight+",left="+lleft+",top="+ltop+",location=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,status=yes";
			window.open("Default.aspx","LiveSupport",features);
		});
		$("body").empty().append(img);
	}
	,checkOnline: function () {
		var url="AjaxRequest.aspx?mode=checkonline";
		var imgls=$("#imgls").get(0);
		if(imgls) {
			om_support.img="Images/offline.png";
			imgls.src=om_support.img;
			try {
				$.get(url,function (data) {
					if(data=="true") {
						om_support.img="Images/online.png";
						imgls.src=om_support.img;
					}
				});
			} catch(e) { }
		}
	}
};
$(document).ready(function () {
	om_support.init();
	om_support.checkOnline();
});
