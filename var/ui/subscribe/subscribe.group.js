ui.subscribe.group = function(config){
	Ext.apply(this, config);
	this.selModel = new Ext.grid.RowSelectionModel({singleSelect: true});
	var proxy = new Ext.data.HttpProxy({
		api: {
			read: 'di/subscribe/list.js',
			create: 'di/subscribe/set.js',
			update: 'di/subscribe/set.js',
			destroy: 'di/subscribe/unset.js'
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
		[{name: 'id', type: 'int'}, 'name']
	);
	// Typical JsonWriter
	var writer = new Ext.data.JsonWriter({
		encode: true,
		writeAllFields: false
	});
	// The data store
	var store = new Ext.data.Store({
		proxy: proxy,
		reader: reader,
		writer: writer
	});
	// Let's pretend we rendered our grid-columns with meta-data from our ORM framework.
	var columns = [
		{header: "ID", width: 50, sortable: true, dataIndex: 'id'},
		{header: this.colNameTitle, width: 200, sortable: true, dataIndex: 'name', id: 'name'}
	];
	var Add = function(){
		var f = new ui.subscribe.editForm();
		var w = new Ext.Window({title: this.addTitle, modal: true, layout: 'fit', width: 400, height: 150, items: f});
		f.on({
			saved: function(){store.reload()},
			cancelled: function(){w.destroy()}
		});
		w.show();
	}.createDelegate(this);
	var Edit = function(){
		var id = this.getSelectionModel().getSelected().get('id');
		var f = new ui.subscribe.editForm();
		var w = new Ext.Window({title: this.editTitle, modal: true, layout: 'fit', width: 400, height: 150, items: f});
		f.on({
			saved: function(){store.reload()},
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(id)});
	}.createDelegate(this);
	var Delete = function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.store.remove(record);
				this.getTopToolbar().findById("bttDel").disable();
				this.getTopToolbar().findById("bttEdt").disable();
				this.getTopToolbar().findById("bttPerm").disable();
			}
		}, this);
	}.createDelegate(this);
	
	var msgList = function(){
		        var record = this.getSelectionModel().getSelected().get('id');
			var msglist = new ui.subscribe.messages_list({region: 'west', split: true, width: 200});
			var w = new Ext.Window({title: "Сообщения", 
						modal: true, 
						layout: 'fit', 
						width: 800, 
						height: 600,
						items: [msglist]
			});
			msglist.applyStore({_ssubscr_id: record});
			w.show();
	}.createDelegate(this);


	var onCmenu = function(grid, rowIndex, e){
		this.getSelectionModel().selectRow(rowIndex);
		var cmenu = new Ext.menu.Menu({items: [
			{iconCls: 'pencil', text: this.bttMsgList, handler: msgList}
		]});
		e.stopEvent();  
		cmenu.showAt(e.getXY());
	}.createDelegate(this);

	ui.subscribe.group.superclass.constructor.call(this, {
		store: store,
		columns: columns,
		autoExpandColumn: 'name',
		tbar: [
			{text: this.bttAdd, iconCls: "group_add", handler: Add},
			{text: this.bttEdit, iconCls: "group_edit", handler: Edit, id: "bttEdt", disabled: true},
			{text: this.bttDelete, iconCls: "group_delete", handler: Delete, id: "bttDel", disabled: true},
			'->', {iconCls: 'help', handler: function(){showHelp('group')}}
		],
		bbar: new Ext.PagingToolbar({pageSize: this.limit, store: store, displayInfo: true})
	});
	this.addEvents(
	);
	this.on({
		rowclick: function(grid, rowIndex, ev){
			grid.getTopToolbar().findById("bttEdt").enable();
			grid.getTopToolbar().findById("bttDel").enable();
		},
		rowcontextmenu: onCmenu,
		render: function(){store.load({params:{start:0, limit: this.limit}})},
		scope: this
	})
};
Ext.extend(ui.subscribe.group, Ext.grid.GridPanel, {
	limit: 20,
	colNameTitle: "Наименование",

	addTitle: "Добавление рассылки",
	editTitle: "Изменение рассылки",
	

	bttAdd: "Добавить",
	bttEdit: "Изменить",
	bttDelete: "Удалить",
	bttMsgAdd: "Добавить сообщение",
	bttMsgList:"Список сообщений",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эт(и|у) групп(ы|у)?"
});
