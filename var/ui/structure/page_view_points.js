ui.structure.page_view_points = Ext.extend(Ext.grid.EditorGridPanel, {
	clmnVPoint: "VP Num.",
	clmnTitle: "Наименование",
	clmnOrder: "Пор. отобр.",
	clmnHasStrc: "Струк.",
	clmnDHide: "Скрывать на подстраницах",
	clmnUIName: "Модуль",
	clmnUICall: "Вызов",
	clmnCache: "Исп. кэш",
	clmnCacheTime: "Кэш время",

	titleAdd: 'Добавление Блока',
	titleEdit: 'Редактирование Блока',
	bttAdd: 'Создать',
	bttEdit: 'Изменить',
	bttDelete: 'Удалить',
	bttLaunch: 'Запустить',
	bttSetSave: 'Сохранить сет',
	bttSetLoad: 'Загрузить сет',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить этот блок?",

	pagerSize: 50,
	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}',
	setParams: function(params, reload){
		var s = this.getStore();
		params = params || {};
		for (var i in params){if(params[i] === ''){delete params[i]}}
		this.getStore().baseParams = params;
		if (reload) s.load({params:{start: 0, limit: this.pagerSize}});

		var pid = this.getKey();
		var tb = this.getTopToolbar();
		if (!(pid > 0))
			Ext.each(tb.find('utype', 'button'), function(btt){btt.disable()});
		else
			Ext.each(tb.find('utype', 'button'), function(btt){btt.enable()});
	},
	applyParams: function(params, reload){
		var s = this.getStore();
		params = params || {};
		for (var i in params){if(params[i] === ''){delete params[i]}}
		Ext.apply(s.baseParams, params);
		if (reload) s.load({params:{start: 0, limit: this.pagerSize}});

		var pid = this.getKey();
		var tb = this.getTopToolbar();
		if (!(pid > 0))
			Ext.each(tb.find('utype', 'button'), function(btt){btt.disable()});
		else
			Ext.each(tb.find('utype', 'button'), function(btt){btt.enable()});
	},
	getKey: function(){
		return this.getStore().baseParams._spid;
	},
	operation: {
		Add: function(){
			var pid = this.getKey();
			if (!(pid > 0)) return false;

			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure.page_view_point_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: function(){this.getStore().reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({page_id: pid})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure', 'page_view_point_form');
		},
		Edit: function(){
			var id = this.getSelectionModel().getSelected().get('id');
			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure.page_view_point_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: function(){this.getStore().reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({id: id})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure', 'page_view_point_form');
		},
		multiSave: function(){
			this.getStore().save();
		},
		Delete: function(){
			var record = this.getSelectionModel().getSelections();
			if (!record) return false;

			Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
				if (btn == "yes") this.getStore().remove(record);
			}, this);
		},
		Launch: function(){
			var record = this.getSelectionModel().getSelected();
			this.appContainer.Launch(record.get('ui_name'), 'main', record.get('human_name'));
		},
		SaveCfg: function(){
			var pid = this.getKey();
			var app = new App({waitMsg: 'Loading sets UI...'});
			app.on({
				apploaded: function(){
					var f = new ui.structure_presets.main({pid: pid});
					f.AddSingle();
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure_presets', 'main');
		},
		LoadCfg: function(){

			var pid = this.getKey();
			var app = new App({waitMsg: 'Loading sets UI...'});
			app.on({
				apploaded: function(){
					var f = new ui.structure_presets.main({pid: pid});
					f.store.load();
					var w = new Ext.Window({title: this.addTitle, maximizable: true, modal: true, layout: 'fit', width: 600, height: 500, items: f});
					f.on({
						saved: function(){this.store.reload()},
						vploaded:function(){this.store.reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show();
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure_presets', 'main');
		}
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
						read: 'di/ui_view_point/list.js',
						create: 'di/ui_view_point/set.js',
						update: 'di/ui_view_point/mset.js',
						destroy: 'di/ui_view_point/unset.js'
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
						{name: 'view_point', type: 'int'},
						'title',
						{name: 'cache_enabled', type: 'int'},
						{name: 'cache_timeout', type: 'int'},
						{name: 'has_structure', type: 'int'},
						'ui_name',
						'human_name',
						'ui_call',
						'ui_configure',
						{name: 'order', type: 'int'},
						{name: 'deep_hide', type: 'int'}
					]
				),
				writer: new Ext.data.JsonWriter({
					encode: true,
					listful: true,
					writeAllFields: false
				}),
				remoteSort: true
			})
		});
		var fm = Ext.form;
		var strYN = new Ext.data.SimpleStore({fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']]});
		var fldYN = new fm.ComboBox({valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false, store: strYN});
		var clmnYN = function(value){return (value == 1) ? 'Да' : 'Нет'}
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			autoExpandColumn: 'expand',
			selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true,
					width: 120
				},
				columns: [
					{header: 'ID', dataIndex: 'id', hidden: true},
					{header: this.clmnVPoint, dataIndex: 'view_point', sortable: true, width: 50, editor: new fm.NumberField({minValue: 0, maxValue: 255})},
					{header: this.clmnTitle, dataIndex: 'title', id: 'expand', sortable: true, editor: new fm.TextField({maxLength: 255})},
					{header: this.clmnOrder, dataIndex: 'order', sortable: true, width: 50, editor: new fm.NumberField({minValue: 0, maxValue: 255})},
					{header: this.clmnHasStrc, dataIndex: 'has_structure', sortable: true, width: 50, editor: fldYN, renderer: clmnYN},
					{header: this.clmnDHide, dataIndex: 'deep_hide', sortable: true, width: 50, editor: fldYN, renderer: clmnYN},
					{header: this.clmnUIName, dataIndex: 'human_name', sortable: true, width: 150},
					{header: this.clmnUICall, dataIndex: 'ui_call', sortable: true, width: 100},
					{header: this.clmnCache, dataIndex: 'cache_enabled', sortable: true, width: 50, editor: fldYN, renderer: clmnYN},
					{header: this.clmnCacheTime, dataIndex: 'cache_timeout', sortable: true, width: 50, editor: new fm.NumberField({minValue: 0, maxValue: 999999})}
				]
			}),
			tbar: [
				{iconCls: 'layout_add', text: this.bttAdd, handler: this.operation.Add, scope: this, utype: 'button', disabled: true},
				{iconCls: 'layout_edit', text: this.bttSetSave, handler: this.operation.SaveCfg, scope: this, utype: 'button', disabled: true},
				{iconCls: 'layout_link', text: this.bttSetLoad, handler: this.operation.LoadCfg, scope: this, utype: 'button', disabled: true},
				'->', {iconCls: 'help', handler: function(){showHelp('view-points')}}
			],
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
		ui.structure.page_view_points.superclass.constructor.call(this, config);
		this.init();
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
		this.on({
			rowcontextmenu: function(grid, rowIndex, e){
				grid.getSelectionModel().selectRow(rowIndex);
				var row = grid.getSelectionModel().getSelected();
				var id = row.get('id');
				var cmenu = new Ext.menu.Menu({items: [
					{iconCls: 'layout_edit', text: this.bttEdit, handler: this.operation.Edit, scope: this},
					{iconCls: 'layout_delete', text: this.bttDelete, handler: this.operation.Delete, scope: this}
					//,'-', {iconCls: 'layout_content', text: this.bttLaunch, handler: this.operation.Launch, scope: this}
				]});
				e.stopEvent();  
				cmenu.showAt(e.getXY());
			},
			//render: function(){this.store.load({params:{start: 0, limit: this.pagerSize}})},
			dblrowclick: this.operation.Edit,
			scope: this
		});
	}
});
