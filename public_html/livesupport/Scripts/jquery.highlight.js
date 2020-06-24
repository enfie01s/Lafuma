jQuery.fn.highlight=function (pat,replace) {
	function innerHighlight(node,pat,replace) {
		var skip=0;
		if(node.nodeType==3) {
			var pos=node.data.toUpperCase().indexOf(pat);
			if(pos>=0) {
				var spannode=document.createElement('span');
				spannode.className='highlight';
				$(spannode).attr("re",replace);
				var middlebit=node.splitText(pos);
				var endbit=middlebit.splitText(pat.length);
				var middleclone=middlebit.cloneNode(true);
				spannode.appendChild(middleclone);
				middlebit.parentNode.replaceChild(spannode,middlebit);
				skip=1;
			}
		}
		else if(node.nodeType==1&&node.childNodes&&!/(script|style)/i.test(node.tagName)) {
			for(var i=0;i<node.childNodes.length;++i) {
				i+=innerHighlight(node.childNodes[i],pat,replace);
			}
		}
		return skip;
	}
	return this.each(function () {
		innerHighlight(this,pat.toUpperCase(),replace);
	});
};

jQuery.fn.removeHighlight=function () {
	return this.find("span.highlight").each(function () {
		this.parentNode.firstChild.nodeName;
		with(this.parentNode) {
			replaceChild(this.firstChild,this);
			normalize();
		}
	}).end();
};