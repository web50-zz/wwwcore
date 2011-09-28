ui.text.main = function(config){
	var frmW = 800;
	var frmH = 480;
	var fm = Ext.form;
	Ext.apply(this, config);
	var proxy = new Ext.data.HttpProxy({
		api: {
			read: 'di/text/list.js',
			create: 'di/text/set.js',
			update: 'di/text/mset.js',
			destroy: 'di/text/unset.js'
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
		[{name: 'id', type: 'int'}, 'title']
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
		writer: writer,
		remoteSort: true,
		sortInfo: {field: 'id', direction: 'DESC'}
	});
	columns = [
		{header: 'ID', id: 'id', dataIndex: 'id',sortable:true},
		{header: this.clmnTitle, id: 'title', dataIndex: 'title', sortable: true, editor: new fm.TextField({maxLength: 255, maxLengthText: 'Не больше 255 символов'})}
	];
	var Add = function(){
		var f = new ui.text.item_form();
		var w = new Ext.Window({title: this.addTitle, maximizable: true, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: function(){store.reload()},
			cancelled: function(){w.destroy()}
		});
		w.show();
	}.createDelegate(this);
	var Edit = function(){
		var id = this.getSelectionModel().getSelected().get('id');
		var f = new ui.text.item_form();
		var w = new Ext.Window({title: this.editTitle, maximizable: true, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: function(){store.reload()},
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(id)});
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
	var onCmenu = function(grid, rowIndex, e){
		grid.getSelectionModel().selectRow(rowIndex);
		var row = grid.getSelectionModel().getSelected();
		var id = row.get('id');
		var cmenu = new Ext.menu.Menu({items: [
			{iconCls: 'page_white_edit', text: 'Редактировать', handler: Edit},
			{iconCls: 'page_white_delete', text: 'Удалить', handler: Delete}
		]});
		e.stopEvent();  
		cmenu.showAt(e.getXY());
	}
	var reload = function(){
		store.load({params: {start: 0, limit: this.limit}});
	}.createDelegate(this);
	var srchField = new Ext.form.TextField();
	var srchType = new Ext.form.ComboBox({
		width: 100,
		store: new Ext.data.SimpleStore({fields: ['value', 'title'], data: [
			['title', 'Заголовок'],
			['content', 'Содержимое']
		]}), value: 'title',
		valueField: 'value', displayField: 'title', triggerAction: 'all', mode: 'local', editable: false
	});
	var srchBttOk = new Ext.Toolbar.Button({
		text: 'Найти',
		iconCls:'find',
		handler: function search_submit(){
			Ext.apply(store.baseParams, {field: srchType.getValue(), query: srchField.getValue()});
			reload();
		}
	})
	var srchBttCancel = new Ext.Toolbar.Button({
		text: 'Сбросить',
		iconCls:'cancel',
		handler: function search_submit(){
			srchType.setValue('title');
			srchField.setValue('');
			Ext.apply(store.baseParams, {field: '', query: ''});
			reload();
		}
	})
	ui.text.main.superclass.constructor.call(this, {
		store: store,
		columns: columns,
		loadMask: true,
		autoExpandColumn: 'title',
		stripeRows: true,
		autoScroll: true,
		selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
		tbar: [
			{iconCls: 'page_white_add', text: 'Добавить', handler: Add},
			'-', new Ext.Toolbar.TextItem ("Найти:"),
			srchType, srchField, srchBttOk, srchBttCancel,
			'->', {iconCls: 'help', handler: function(){showHelp('text')}}
		],
		bbar: new Ext.PagingToolbar({
			pageSize: this.limit,
			store: store,
			displayInfo: true,
			displayMsg: this.pagerDisplayMsg,
			emptyMsg: this.pagerEmptyMsg
		})
	});
	this.addEvents({
	});
	this.on({
		rowcontextmenu: onCmenu,
		render: function(){store.load({params:{start:0, limit: this.limit}})},
		scope: this
	});
};
Ext.extend(ui.text.main, Ext.grid.EditorGridPanel, {
	limit: 20,

	addTitle: "Добавление текста",
	editTitle: "Редактирование текста",

	clmnTitle: "Заголовок",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить этот текст?",

	pagerEmptyMsg: 'Нет записей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}'
});
