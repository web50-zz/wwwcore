ui.www_article_search.main = Ext.extend(Ext.Panel, {
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		var grid = new ui.www_article.main({
			region: 'center'
		});
		var filter = new ui.www_article_search.main_filter({
			region: 'west',
			width: 300,
			split: true,
			collapsible: true,
			listeners: {
				applied: function(data){
					if(data._stitle != '')
					{
						data._stitle = '%'+data._stitle+'%';
					}
					grid.setParams(data, true);
				}
			}
		});
		grid.on({
			recommended: function(){
			},
			unrecommended: function(){
			},
			scope: this
		});
		Ext.apply(this, {
			layout: 'border',
			items: [
				filter,
				grid
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_search.main.superclass.constructor.call(this, config);
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
