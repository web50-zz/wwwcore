ui.structure.main = Ext.extend(Ext.Panel, {
	/**
	 * @constructor
	 */
	constructor: function(config){
		var view = new ui.structure.page_view_points({region: 'center'});
		var tree = new ui.structure.site_tree({
			region: 'west',
			width: 300,
			split: true,
			listeners: {
				changenode: function(id, node){
					view.applyParams({_spid: id}, true);
				}
			}
		});
		Ext.apply(this, {
			layout: 'border',
			items: [tree, view]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.structure.main.superclass.constructor.call(this, config);
	},

	/**
	 * To manually set default properties.
	 * 
	 * @param {Object} config Object containing all config options.
	 */
	configure: function(config){
		config = config || {};
		Ext.apply(this, config, config);
	},

	/**
	 * @private
	 * @param {Object} o Object containing all options.
	 *
	 * Initializes the box by inserting into DOM.
	 */
	init: function(o){
	}
});
