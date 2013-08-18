ui.www_article_type.tree = Ext.extend(Ext.tree.TreePanel, {
	reload: function(id){
		if (id){
			var node = this.getNodeById(id);
			if (node){
				if (!node.expanded)
					node.expand()
				else
					node.reload();
			}
		}else if (this.root.rendered == true)
			this.root.reload();
	},

	constructor: function(config){
		config = config || {};
		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({url: 'di/www_article_type/slice.json'}),
			root: new Ext.tree.AsyncTreeNode({id: '1', draggable: false, expanded: true}),
			rootVisible: false,
			autoScroll: true,
			loadMask: new Ext.LoadMask(Ext.getBody(), {msg: "Загрузка данных..."}),
		});
		Ext.apply(this, config);
		ui.www_article_type.tree.superclass.constructor.call(this, config);
		this.on({
			click: function(node, e){
				this.fireEvent('node_changed', node.id, node);
			},
			scope: this
		})
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
