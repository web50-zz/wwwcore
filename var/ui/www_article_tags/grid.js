ui.www_article_tags.grid = Ext.extend(Ext.grid.EditorGridPanel, {
	clmnName: "Изображение",
	clmnTitle: "Название",

	pagerSize: 50,
	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'с {0} по {1}. Всего: {2}',

	setParams: function(params, reload){
		var s = this.getStore();
		params = params || {};
		for (var i in params){if(params[i] === ''){delete params[i]}}
		this.getStore().baseParams = params;
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
		return this.getStore().baseParams._sitem_id;
	},
	onRowMove: function(target, row){
		var x = row.data;
		var y = target.selections[0].data;
		Ext.Ajax.request({
			url: 'di/www_article_tags/reorder.do',
			method: 'post',
			params: {npos: y.order, opos: x.order, id: row.id, pid: this.getKey()},
			disableCaching: true,
			callback: function(options, success, response){
				if (success){
					this.fireEvent('rowmoved');
					this.getSelectionModel().selectRow(row);
				}else
					showError("Ошибка сохранения");
			},
			scope: this
		});
	},
	/**
	 * @constructor
	 */
	constructor: function(config){
		Ext.apply(this, {
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
					api: {
						read: 'di/www_article_tags/list.js',
						create: 'di/www_article_tags/set.js',
						update: 'di/www_article_tags/mset.js',
						destroy: 'di/www_article_tags/unset.js'
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
						'category_title'
					]
				),
				writer: new Ext.data.JsonWriter({
					encode: true,
					listful: true,
					writeAllFields: false
				}),
				autoLoad: true,
				remoteSort: true,
				sortInfo: {field: 'id', direction: 'ASC'}
			})
		});
		var fm = Ext.form;
		var size = function(value){
			return value ? Ext.util.Format.fileSize(value) : '0'
		};
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			autoExpandColumn: 'expand',
			enableDragDrop: true,
			ddGroup: 'www_article_tags',
			selModel: new Ext.grid.RowSelectionModel({singleSelect: false}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {sortable: true, width: 200},
				columns: [
					{header: this.clmnTitle, id: 'expand', dataIndex: 'category_title', editor: new fm.TextField({maxLength: 255, maxLengthText: 'Не больше 255 символов'})}
				]
			}),
			bbar: new Ext.PagingToolbar({
				pageSize: this.pagerSize,
				store: this.store,
				displayInfo: true,
				displayMsg: this.pagerDisplayMsg,
				emptyMsg: this.pagerEmptyMsg
			}),
			listeners: {
				render: function(){
					new Ext.dd.DropTarget(this.getView().mainBody, {
						ddGroup: 'www_article_tags',
						notifyDrop: function(ds, e, data){
							var sm = ds.grid.getSelectionModel();
							if (sm.hasSelection()){
								var row = sm.getSelected();
								var trg = ds.getDragData(e);
								if (row.id != trg.selections[0].id){
									ds.grid.fireEvent('rowmove', trg, row);
									return true;
								}else{
									return false;
								}
							}
						}
					});
				},
				rowmove: this.onRowMove,
				rowmoved: function(){this.getStore().load()},
				scope: this
			}
		});

		config = config || {};
		Ext.apply(this, config);
		ui.www_article_tags.grid.superclass.constructor.call(this, config);
		this.init(config);
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
