ui.faq.main = function(config, vp){
	Ext.apply(this, config);
	//if (vp && vp.ui_configure) store.baseParams = vp.ui_configure;
	var partsl = new ui.faq.parts_list({region:'west', split: true, width: 300});
	var faql = new ui.faq.list({region: 'center'}, vp);
	faql.store.baseParams = {_sfaq_part_id: 0};
	partsl.on({
		rowclick: function(partsl, rowIndex, ev){
		faql.store.baseParams = {_sfaq_part_id: this.getSelectionModel().getSelected().get('id')};
		faql.store.reload(true);
		}
	});

	ui.faq.main.superclass.constructor.call(this, {
		layout: 'border',
		items: [partsl, faql]
	});
};
Ext.extend(ui.faq.main, Ext.Panel, {
});
