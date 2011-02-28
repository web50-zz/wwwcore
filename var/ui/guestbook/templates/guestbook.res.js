Ext.namespace("ui.guestbook");
ui.guestbook = function(conf){

	this.collectButtons = function(){
		Ext.each(Ext.query(".newrecord"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					this.getFrm();
				},
				scope: this
			})
		}, this);
	}

	this.getFrm = function()
	{
		SplForm.show({formUrl:'/ui/guestbook/getfrm.do',saveUrl:'/ui/guestbook/save_form.do',form_id:'guestbook_form'});
	}
}

Ext.onReady(function(){
	FRONTLOADER.load('/js/ux/alertbox/js/Ext.ux.AlertBox.js','alertbox');
	FRONTLOADER.loadCss('/js/ux/alertbox/alertbox.css','alertboxcss');
	FRONTLOADER.load('/js/ux/splform/Ext.ux.SplForm.js','splform');
	FRONTLOADER.loadCss('/js/ux/splform/splform.css','splformcss');
	var guestbook = new ui.guestbook();
	guestbook.collectButtons();
});
