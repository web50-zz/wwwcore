ui.article_files.grid = Ext.extend(Ext.grid.EditorGridPanel, {
	setParams: function(params, reload){
		var s = this.getStore();
		params = params || {};
		for (var i in params){if(params[i] === ''){delete params[i]}}
		s.baseParams = params;
		if (reload) s.load({params:{start: 0, limit: this.pagerSize}});
	},
	applyParams: function(params, reload){
		var s = this.getStore();
		params = params || {};
		for (var i in params){if(params[i] === ''){delete params[i]}}
		Ext.apply(s.baseParams, params);
		if (reload) s.load({params:{start: 0, limit: this.pagerSize}});
	},
	getKey: function(){
		return this.getStore().baseParams._sarticle_id;
	},
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		var image = new Ext.XTemplate('<tpl if="real_name.length &gt; 0"><img src="{url}thumb-{real_name}" width="92" border="0"/></tpl>');
		image.compile();
		Ext.apply(this, {
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
					api: {
						read: 'di/article_files/list.js',
						create: 'di/article_files/set.js',
						update: 'di/article_files/mset.js',
						destroy: 'di/article_files/unset.js'
					}
				}),
				reader: new Ext.data.JsonReader({
						totalProperty: 'total',
						successProperty: 'success',
						idProperty: 'id',
						root: 'records',
						messageProperty: 'errors'
					}, [
						{name: 'id', type: 'int'},
						{name: 'order', type: 'int'},
						{name: 'file_type', type: 'int'},
						'title', 'real_name', 'url'
					]
				),
				writer: new Ext.data.JsonWriter({
					encode: true,
					listful: true,
					writeAllFields: false
				}),
				autoLoad: true,
				remoteSort: true,
				sortInfo: {field: 'id', direction: 'DESC'}
			}),
		});
		Ext.apply(this, {
			clmnTitle: 'Наименование',
			clmnImage: 'превью если есть',
			clmnFt: 'Тип',
			pagerSize: 50,
			pagerEmptyMsg: 'Нет компаний',
			pagerDisplayMsg: 'Комментарии с {0} по {1}. Всего: {2}'
		});
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			autoExpandColumn: 'expand',
			selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [
					{header: 'ID', dataIndex: 'id', hidden: true},
					{header: this.clmnImage, dataIndex: 'title', width: 120, xtype: 'templatecolumn', tpl: image},
					{header: this.clmnTitle,  dataIndex: 'title', id: 'expand'},
					{header: this.clmnFt, dataIndex: 'file_type', width: 120, renderer: function(v){return ui.article_files.types.getById(v).get('title')}}
				]
			})
		});
		Ext.apply(this, {
			bbar: new Ext.PagingToolbar({
				pageSize: this.pagerSize,
				store: this.store,
				displayInfo: true,
				displayMsg: this.pagerDisplayMsg,
				emptyMsg: this.pagerEmptyMsg
			})
		});
		config = config || {};
		Ext.apply(this, config);
		ui.article_files.grid.superclass.constructor.call(this, config);
		this.init();
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
