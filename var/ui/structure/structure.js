ui.structure.main = function(config){
	var self = this;
	var view = new ui.structure.page_view_points({region: 'center'});
	var tree = new ui.structure.site_tree({
		region: 'west',
		width: 300,
		split: true,
		listeners: {
			changenode: function(id, node){
				view.applyStore({_spid: id});
			}
		}
	});
	/*
	var view = new ui.structure.page_view({region: 'center'});
	tree.on({
		changenode: function(pid, node){
			view.newPage(pid, node);
		},
		changemodule: function(pid, node){
			view.newPage(pid, node, true);
		},
		removenode: function(pid, node){
			view.delPage(pid);
		}
	});
	*/
	Ext.apply(this, config, {});
	ui.structure.main.superclass.constructor.call(this,{
		title: 'Структура сайта',
		layout: 'border',
		items: [tree, view]
	});
};
Ext.extend(ui.structure.main, Ext.Panel, {});
