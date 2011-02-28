Ext.namespace("ui.subscribe");

ui.subscribe = function(conf){

	this.collectButtons = function(){
		Ext.each(Ext.query(".subscrbtt"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					var el = Ext.fly('emailtocheck');
					var v = el.getValue(); 
					if( v == '')
					{
						AlertBox.show("Внимание", 'Вы не ввели e-mail', 'none', {dock: 'top'});
					}
					else
					{
						this.getFrm(v);
					}
				},
				scope: this
			})
		}, this);
	};
	
	this.getFrm = function(email)
	{
		SplForm.show({formUrl:'/ui/subscribe/getfrm.do',saveUrl:'/ui/subscribe/save_form.do',params:{email:email}});
	}
}

Ext.onReady(function(){
	FRONTLOADER.load('/js/ux/alertbox/js/Ext.ux.AlertBox.js','alertbox');
	FRONTLOADER.loadCss('/js/ux/alertbox/alertbox.css','alertboxcss');
	FRONTLOADER.load('/js/ux/splform/Ext.ux.SplForm.js','splform');
	FRONTLOADER.loadCss('/js/ux/splform/splform.css','splformcss');
	var sbcr = new ui.subscribe();
	sbcr.collectButtons();
});


