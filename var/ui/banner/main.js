ui.banner.main = function(config){
	var banners = new ui.banner.banner({
		region: 'center',
		ddGroup: 'siteTree',
		enableDragDrop: true
	});
	var groups = new ui.banner.group({
		region: 'west',
		width: 300,
		split: true,
		listeners: {
			changenode: function(id, node){
				banners.applyParams({_spid: id}, true);
			}
		}
	});
	Ext.apply(this, config, {});
	ui.banner.main.superclass.constructor.call(this,{
		//title: 'Баннеры',
		layout: 'border',
		items: [groups, banners]
	});
};
Ext.extend(ui.banner.main, Ext.Panel, {});
