Ext.namespace("ui.faq");

ui.faq = function(conf){

	this.collectButtons = function(){
		Ext.each(Ext.query(".make_q"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					this.getFrm(item.getAttribute('cid'));
				},
				scope: this
			})
		}, this);
	};


	this.getFrm = function(cid)
	{
		SplForm.show({formUrl:'/ui/faq/getfrm.do',saveUrl:'/ui/faq/save_question.do',params:{cid:cid}});
	}
}

Ext.onReady(function(){
	FRONTLOADER.load('/js/ux/alertbox/js/Ext.ux.AlertBox.js','alertbox');
	FRONTLOADER.loadCss('/js/ux/alertbox/alertbox.css','alertboxcss');
	FRONTLOADER.load('/js/ux/splform/Ext.ux.SplForm.js','splform');
	FRONTLOADER.loadCss('/js/ux/splform/splform.css','splformcss');
	var faq = new ui.faq();
	faq.collectButtons();
});


