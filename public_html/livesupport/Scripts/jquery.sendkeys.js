(function($){

$.fn.sendkeys = function (x, opts){
	return this.each( function(){
		var localkeys = $.extend({}, opts, $(this).data('sendkeys')); // allow for element-specific key functions
		// most elements to not keep track of their selection when they lose focus, so we have to do it for them
		var rng = $.data (this, 'sendkeys.selection');
		if (!rng){
			rng = bililiteRange(this).bounds('selection');
			$.data(this, 'sendkeys.selection', rng);
			$(this).bind('mouseup.sendkeys', function(){
				// we have to update the saved range. The routines here update the bounds with each press, but actual keypresses and mouseclicks do not
				$.data(this, 'sendkeys.selection').bounds('selection');
			}).bind('keyup.sendkeys', function(evt){
				// restore the selection if we got here with a tab (a click should select what was clicked on)
				if (evt.which == 9){
					// there's a flash of selection when we restore the focus, but I don't know how to avoid that.
					$.data(this, 'sendkeys.selection').select();
				}else{
					$.data(this, 'sendkeys.selection').bounds('selection');
				}	
			});
		}
		this.focus();
		if (typeof x === 'undefined') return; // no string, so we just set up the event handlers
		x.replace(/\n/g, '{enter}'). // turn line feeds into explicit break insertions
		  replace(/{[^}]*}|[^{]+/g, function(s){
			(localkeys[s] || $.fn.sendkeys.defaults[s] || $.fn.sendkeys.defaults.simplechar)(rng, s);
		  });
		$(this).trigger({type: 'sendkeys', which: x});
	});
}; // sendkeys


// add the functions publicly so they can be overridden
$.fn.sendkeys.defaults = {
	simplechar: function (rng, s){
		rng.text(s, 'end');
		for (var i =0; i < s.length; ++i){
			var x = s.charCodeAt(i);
			// a bit of cheating: rng._el is the element associated with rng.
			$(rng._el).trigger({type: 'keypress', keyCode: x, which: x, charCode: x});
		}
	},
	'{{}': function (rng){
		$.fn.sendkeys.defaults.simplechar (rng, '{')
	},
	'{enter}': function (rng){
		rng.insertEOL();
		var b = rng.bounds();
		rng.select();
		var x = '\n'.charCodeAt(0);
		$(rng._el).trigger({type: 'keypress', keyCode: x, which: x, charCode: x});
	},
	'{backspace}': function (rng){
		var b = rng.bounds();
		if (b[0] == b[1]) rng.bounds([b[0]-1, b[0]]); // no characters selected; it's just an insertion point. Remove the previous character
		rng.text('', 'end'); // delete the characters and update the selection
	},
	'{del}': function (rng){
		var b = rng.bounds();
		if (b[0] == b[1]) rng.bounds([b[0], b[0]+1]); // no characters selected; it's just an insertion point. Remove the next character
		rng.text('', 'end'); // delete the characters and update the selection
	},
	'{rightarrow}':  function (rng){
		var b = rng.bounds();
		if (b[0] == b[1]) ++b[1]; // no characters selected; it's just an insertion point. Move to the right
		rng.bounds([b[1], b[1]]).select();
	},
	'{leftarrow}': function (rng){
		var b = rng.bounds();
		if (b[0] == b[1]) --b[0]; // no characters selected; it's just an insertion point. Move to the left
		rng.bounds([b[0], b[0]]).select();
	},
	'{selectall}' : function (rng){
		rng.bounds('all').select();
	}
};

})(jQuery)