Ext.namespace("ui.pub_auth");

ui.pub_auth = function(conf){

	this.collectButtons = function(){
		Ext.each(Ext.query(".pswremind"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					var e = Ext.fly('login').getValue();
					if(!e){
						AlertBox.show("Внимание", 'Вы не ввели login', 'none', {dock: 'top'});
					}
					else{
						this.getFrm({email:e});
					}
				},
				scope: this
			})
		}, this);
	};


	this.getFrm = function(o)
	{
		SplForm.show({formUrl:'/ui/pub_auth/getfrm.do',saveUrl:'/ui/pub_auth/save_form.do',params:o});
	}
}

Ext.onReady(function(){
	var pub_auth = new ui.pub_auth();
	pub_auth.collectButtons();
});


