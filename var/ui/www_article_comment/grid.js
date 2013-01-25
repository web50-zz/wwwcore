ui.www_article_comment.grid = Ext.extend(Ext.grid.EditorGridPanel, {
	clmnTitle: 'Наименование',
	clmnReg: 'Регион',
	clmnCty: 'Город',
	clmnZastr: 'Застройщик',
	clmnCreat: 'Дата внесения',
	clmnChang: 'Дата изменения',
	pagerSize: 500,
	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}',
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
		return this.getStore().baseParams._scompany_id;
	},
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
					api: {
						create: 'di/www_article_comment/set.js',
						update: 'di/www_article_comment/mset.js',
						destroy: 'di/www_article_comment/unset.js',
						read: 'di/www_article_comment/list.js'
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
						{name: 'created_datetime', type: 'date', dateFormat: 'Y-m-d H:i:s'},
						{name: 'public', type: 'int'},
						{name: 'article_id', type: 'int'},
						'title',
						'name'
					]
				),
				writer: new Ext.data.JsonWriter({
					encode: true,
					listful: true,
					writeAllFields: false
				}),
				baseParams: {_nrecflag: 'null', start: 0, limit: this.pagerSize},
				autoLoad: true,
				remoteSort: true,
				sortInfo: {field: 'created_datetime', direction: 'DESC'}
			}),
		});
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			autoExpandColumn: 'expand',
			selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					width: 120
					//,sortable: true
				},
				columns: [
					{header: 'Дата', dataIndex: 'created_datetime', xtype: 'datecolumn', format: 'd M Y H:i', align: 'center'},
					//{header: 'Опубликоано', dataIndex: 'public', width: 50, renderer: function(v){return (v > 0) ? '<img src="/ico/ok.png" border="0" />' : ''}, align: 'center'},
					{header: 'Пользователь', dataIndex: 'name'},
					{header: 'Статья', dataIndex: 'title', width: 200, id: 'expand'}
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
		ui.www_article_comment.grid.superclass.constructor.call(this, config);
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
