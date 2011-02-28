ui.structure.page_view_points = function(config){
	var frmW = 450;
	var frmH = 350;
	var fm = Ext.form;
	Ext.apply(this, config);
	var proxy = new Ext.data.HttpProxy({
		api: {
			read: 'di/ui_view_point/list.js',
			create: 'di/ui_view_point/set.js',
			update: 'di/ui_view_point/mset.js',
			destroy: 'di/ui_view_point/unset.js'
		}
	});
	// Typical JsonReader.  Notice additional meta-data params for defining the core attributes of your json-response
	var reader = new Ext.data.JsonReader({
			totalProperty: 'total',
			successProperty: 'success',
			idProperty: 'id',
			root: 'records',
			messageProperty: 'errors'
		},
		[{name: 'id', type: 'int'}, {name: 'view_point', type: 'int'}, 'title', 'cache_enabled', 'cache_timeout', 'ui_name', 'human_name', 'ui_call', 'ui_configure', 'order', 'deep_hide']
	);
	// Typical JsonWriter
	var writer = new Ext.data.JsonWriter({
		encode: true,
		listful: true,
		writeAllFields: false
	});
	// The data store
	var store = new Ext.data.Store({
		proxy: proxy,
		reader: reader,
		writer: writer
	});
	this.applyStore = function(data){
		Ext.apply(store.baseParams, data);
		store.load();
	}
	strYN = new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] });
	var fldYN = new fm.ComboBox({valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false, store: strYN});
	var clmnYN = function(value){return (value == 1) ? 'Да' : 'Нет'}
	columns = [
		{id: 'id', dataIndex: 'id', hidden: true},
		{header: this.clmnVPoint, id: 'view_point', dataIndex: 'view_point', sortable: true, width: 50, editor: new fm.NumberField({minValue: 0, maxValue: 255})},
		{header: this.clmnTitle, id: 'title', dataIndex: 'title', sortable: true, editor: new fm.TextField({maxLength: 255})},
		{header: this.clmnOrder, id: 'order', dataIndex: 'order', sortable: true, width: 50, editor: new fm.NumberField({minValue: 0, maxValue: 255})},
		{header: this.clmnDHide, id: 'deep_hide', dataIndex: 'deep_hide', sortable: true, width: 50, editor: fldYN, renderer: clmnYN},
		{header: this.clmnUIName, id: 'human_name', dataIndex: 'human_name', sortable: true, width: 150},
		{header: this.clmnUICall, id: 'ui_call', dataIndex: 'ui_call', sortable: true, width: 100},
		{header: this.clmnCache, id: 'cache_enabled', dataIndex: 'cache_enabled', sortable: true, width: 50, editor: fldYN, renderer: clmnYN},
		{header: this.clmnCacheTime, id: 'cache_timeout', dataIndex: 'cache_timeout', sortable: true, width: 50, editor: new fm.NumberField({minValue: 0, maxValue: 999999})}
	];
	var Add = function(){
		var f = new ui.structure.page_view_point_form();
		var w = new Ext.Window({title: this.addTitle, maximizable: true, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: function(){store.reload()},
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(0, store.baseParams._spid)});
	}.createDelegate(this);
	var Edit = function(){
		var id = this.getSelectionModel().getSelected().get('id');
		var f = new ui.structure.page_view_point_form();
		var w = new Ext.Window({title: this.editTitle, maximizable: true, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: function(){store.reload()},
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(id, store.baseParams._spid)});
	}.createDelegate(this);
	var multiSave = function(){
		this.store.save();
	}.createDelegate(this);
	var Delete = function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.store.remove(record);
			}
		}, this);
	}.createDelegate(this);
	var Launch = function(){
		var record = this.getSelectionModel().getSelected();
		adm.Launch(record.get('ui_name'), 'main', record.get('human_name'));
	}.createDelegate(this);
	var onCmenu = function(grid, rowIndex, e){
		grid.getSelectionModel().selectRow(rowIndex);
		var row = grid.getSelectionModel().getSelected();
		var id = row.get('id');
		var cmenu = new Ext.menu.Menu({items: [
			{iconCls: 'layout_edit', text: 'Редактировать', handler: Edit},
			{iconCls: 'layout_delete', text: 'Удалить', handler: Delete},
			'-', {iconCls: 'layout_content', text: 'Запустить', handler: Launch}
		]});
		e.stopEvent();  
		cmenu.showAt(e.getXY());
	}
	var reload = function(){
		store.load({params: {start: 0, limit: this.limit}});
	}.createDelegate(this);
	ui.structure.page_view_points.superclass.constructor.call(this, {
		store: store,
		columns: columns,
		loadMask: true,
		autoExpandColumn: 'title',
		stripeRows: true,
		autoScroll: true,
		selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
		tbar: [
			{iconCls: 'layout_add', text: 'Добавить', handler: Add},
			'->', {iconCls: 'help', handler: function(){showHelp('view-points')}}
		]
	});
	this.addEvents({
	});
	this.on({
		rowcontextmenu: onCmenu,
		scope: this
	});
};
Ext.extend(ui.structure.page_view_points, Ext.grid.EditorGridPanel, {
	limit: 20,

	addTitle: "Добавление ViewPoint",
	editTitle: "Редактирование ViewPoint",

	clmnOrder: "Пор. отобр.",
	clmnDHide: "Скрывать на подстраницах",
	clmnVPoint: "VP Num.",
	clmnTitle: "Наименование",
	clmnUIName: "Модуль",
	clmnUICall: "Вызов",
	clmnCache: "Исп. кэш",
	clmnCacheTime: "Кэш время",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить этот ViewPoint?",

	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}'
});
