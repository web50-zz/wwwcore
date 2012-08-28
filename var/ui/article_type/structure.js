ui.article_type.main = function(config){
	var self = this;
	var tree = new ui.article_type.tree({
		region:'center',
		listeners: {
			changenode: function(id, node){
			}
		}
	});
	Ext.apply(this, config, {});
	ui.article_type.main.superclass.constructor.call(this,{
		title: 'Типы статей',
		layout: 'border',
		items: [tree]
	});
};
Ext.extend(ui.article_type.main, Ext.Panel, {});
