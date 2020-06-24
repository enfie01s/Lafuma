(function ($) {
	$.addSoundAlert=function (d,p) {
		if(d.audio) { return false; }
		p=$.extend({
			visible: true
			 ,filename: 'Files/Alert/Message.wav'
			 ,type: 'audio/wav'
		},p);
		var _sountAlert={
			audio: null
			,create: function () {
				var test_audio=document.createElement("audio") //try and create sample audio element
				var audioSupport=(test_audio.play)?true:false;
				if(audioSupport) {
					$(d).html("<audio id='sountalert' controls='controls'><source src='"+p.filename+"' type='"+p.type+"' /></audio>");
					this.audio=$("#sountalert",d).get(0);
				}
			}
			,play: function () {
				try {
					if(this.audio) {
						this.audio.play();
					} else {
						d.style.display="";
						d.innerHTML="<embed loop='false' autostart='true' src='"+p.filename+"'></embed>";
					}
				} catch(e) {
					//alert(e);
				}
			}
		};
		_sountAlert.create();
		d.audio=_sountAlert;
	};
	var docloaded=false;
	$(document)
	.ready(function () { docloaded=true });
	$.fn.jqSoundAlert=function (p) {
		return this.each(function () {
			try {
				if(!docloaded) {
					var d=this;
					$(document).ready
					(
						function () {
							$.addSoundAlert(d,p);
						}
					);
				} else {
					$.addSoundAlert(this,p);
				}
			} catch(e) {
				alert(e);
			}
		});
	}
	$.fn.play=function () { return this.each(function () { this.audio.play(); }); };
})(jQuery);