ui.www_slide.main = function(config){
	var slides = new ui.www_slide.slide({
		region: 'center'
	});
	var groups = new ui.www_slide.group({
		region: 'west',
		width: 300,
		split: true,
		listeners: {
			changenode: function(id, node){
				slides.applyParams({_spid: id}, true);
			}
		}
	});
	Ext.apply(this, config, {});
	ui.www_slide.main.superclass.constructor.call(this,{
		//title: 'Баннеры',
		layout: 'border',
		items: [groups, slides]
	});
};
Ext.extend(ui.www_slide.main, Ext.Panel, {});
