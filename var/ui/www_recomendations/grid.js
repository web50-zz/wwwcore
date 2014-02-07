ui.www_recomendations.grid = Ext.extend(Ext.grid.EditorGridPanel, {
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
	onRowMove: function(target, row){
		var x = row.data;
		var y = target.selections[0].data;
		Ext.Ajax.request({
			url: 'di/www_recomendations/reorder.do',
			method: 'post',
			params: {npos: y.order, opos: x.order, id: row.id},
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

	pagerSize: 50,
	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}',
	/**
	 * @constructor
	 */
	constructor: function(config){
		Ext.apply(this, {
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
					api: {
						read: 'di/www_recomendations/list.js',
						create: 'di/www_recomendations/set.js',
						update: 'di/www_recomendations/mset.js',
						destroy: 'di/www_recomendations/unset.js'
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
						'preview',
						'client_name',
						'link'
					]
				),
				writer: new Ext.data.JsonWriter({
					encode: true,
					listful: true,
					writeAllFields: false
				}),
				remoteSort: true,
				sortInfo: {field: 'order', direction: 'ASC'}
			})
		});
		var image = new Ext.XTemplate('<img src="{preview}" height="150" border="0"/>');
		image.compile();
		var drag = function(preview, name){
			return  '<img src="'+preview+'" height="150" align="left">'+'<b style="font-size: 13px;">'+name+'</b><br>'
		}
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			autoExpandColumn: 'expand',
			enableDragDrop: true,
			ddGroup: 'www_recomendations',
			selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
			sm: new Ext.grid.RowSelectionModel({  
				listeners: {
					singleSelect: true,
					beforerowselect: function(sm, i, ke, row){
						this.ddText = drag(row.data.preview, row.data.client_name);
					},
					scope: this
				}  
			}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true,
					width: 200
				},
				columns: [
					{header: 'Логотип', dataIndex: 'preview', xtype: 'templatecolumn', tpl: image},
					{header: 'Клиент', dataIndex: 'client_name', id: 'expand'}
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
						ddGroup: 'www_recomendations',
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
		ui.www_recomendations.grid.superclass.constructor.call(this, config);
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
