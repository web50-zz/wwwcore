ui.www_article_comment.main = Ext.extend(Ext.Panel, {
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		this.grid = new ui.www_article_comment.main_grid({
			region: 'center'
		});
		var filter = new ui.www_article_comment.main_filter({
			region: 'west',
			width: 300,
			split: true,
			collapsible: true,
			listeners: {
				applied: function(data){
					this.grid.setParams(data, true);
				},
				scope: this
			}
		});
		Ext.apply(this, {
			layout: 'border',
			items: [
				filter,
				this.grid
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_comment.main.superclass.constructor.call(this, config);
	},
	getStore: function(){
		return this.grid.getStore();
	},
	/**
	 * To manually set default properties.
	 * 
	 * @param {Object} config Object containing all config options.
	 */
	configure: function(config)
	{
		config = config || {};
		Ext.apply(this, config, config);
	},

	/**
	 * @private
	 * @param {Object} o Object containing all options.
	 *
	 * Initializes the box by inserting into DOM.
	 */
	init: function(o)
	{
	}
});
