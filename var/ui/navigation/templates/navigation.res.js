Ext.namespace("ui","Diesel");

ui.navigation = function(conf){
	this.prepare = function(){
		Ext.each(Ext.query(".make_q"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					this.getFrm(item.getAttribute('cid'));
				},
				scope: this
			})
		}, this);
	};
};

Ext.onReady(function(){
	if(!Diesel.navigation){
		Diesel.navigation = new ui.navigation();
		Diesel.navigation.prepare();
	};
});
