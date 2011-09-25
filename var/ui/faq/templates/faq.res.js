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
	var faq = new ui.faq();
	faq.collectButtons();
});


