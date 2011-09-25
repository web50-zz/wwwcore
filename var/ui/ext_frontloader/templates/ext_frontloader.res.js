var front = function(config){
	this.load = function(caller,id){
		var d = document;
		if(!d.getElementById(id))
		{
			var j = d.createElement('script');
			var h = d.getElementsByTagName('head')[0];
			j.id = id;
			j.src = caller;
			j.type = 'text/javascript';
			h.appendChild(j);
		}
	}

	this.loadCss = function(caller,id){
		var d = document; 
		if (!d.getElementById(id))
		{
			var h  = d.getElementsByTagName('head')[0];
			var l  = d.createElement('link');
			l.id   = id;
			l.rel  = 'stylesheet';
			l.type = 'text/css';
			l.href = caller;
			l.media = 'all';
			h.appendChild(l);
		}
	}
}
Ext.onReady(function(){
	FRONTLOADER = new front();
});
