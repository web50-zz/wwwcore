ui.www_slide.slide_grid = Ext.extend(Ext.grid.EditorGridPanel, {
	clmnPreview: 'Preview',
	clmnTitle: 'Наименование',

	pagerSize: 50,
	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}',
	getPid: function(){
		return parseInt(this.getStore().baseParams._spid, 10) || 0;
	},
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
			url: 'di/www_slide/reorder.do',
			method: 'post',
			params: {npos: y.order, opos: x.order, id: row.id, pid: this.getPid()},
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
	constructor: function(config)
	{
		Ext.apply(this, {
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
					api: {
						read: 'di/www_slide/list.js',
						create: 'di/www_slide/set.js',
						update: 'di/www_slide/mset.js',
						destroy: 'di/www_slide/unset.js'
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
						{name: 'type', type: 'int'},
						'title',
						'link',
						'comment',
						'real_name',
						'path'
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
		var preview = function(v, md, row){
			var cnt = '';
			var width = 400;
			var height = 200;
			//return ui.www_slide.content_type.getById(v).get('title');
			if (v == 1){// Изображение
				var tpl = new Ext.Template('<img src="{0}{1}" width="'+width+'"/>');
				cnt = tpl.apply([row.get('path'), row.get('real_name')]);
			}else if (v == 2){// Flash
				var tpl = new Ext.Template('<object width="'+width+'" height="'+height+'" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">',
					'<param name="wmode" value="opaque" />',
					'<param name="quality" value="high" />',
					'<param name="movie" value="{0}{1}" />',
					'<embed src="{0}{1}" width="'+width+'" height="'+height+'" wmode="opaque" type="application/x-shockwave-flash" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" embed="" />',
					'</object>');
				cnt = tpl.apply([row.get('path'), row.get('real_name')]);
			}else if (v == 3){// Внешнее видео
				var tpl = new Ext.Template('<object style="visibility: visible;"  data="{0}" type="application/x-shockwave-flash" width="'+width+'" height="'+height+'">',
						'<param value="#000000" name="bgcolor">',
						'<param vaue="true" name="allowfullscreen">',
						'</object>'
					);
				cnt = tpl.apply([row.get('link')]);
			}else if (v == 4){// Текст
				var tpl = new Ext.Template('<div style="height: '+height+'px; width: '+width+'px; white-space: normal !important;">{0}</div>');
				cnt = tpl.apply([row.get('comment')]);
			}else if (v == 5){// Локальное видео
				//var tpl = new Ext.Template('<img src="{0}{1}" width="200"/>');
				//cnt = tpl.apply([row.get('path'), row.get('real_name')]);
			}
			return cnt;
		};
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			autoExpandColumn: 'expand',
			enableDragDrop: true,
			ddGroup: 'www_slide',
			selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true,
					width: 410
				},
				columns: [
					{header: this.clmnPreview, dataIndex: 'type', renderer: preview},
					{header: this.clmnTitle, dataIndex: 'title', id: "expand"}
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
						ddGroup: 'www_slide',
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
		ui.www_slide.slide_grid.superclass.constructor.call(this, config);
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
