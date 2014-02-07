ui.www_client.main = Ext.extend(ui.www_client.grid, {
	Add: function(){
		var app = new App({waitMsg: this.frmLoading});
		app.on({
			apploaded: function(){
				var f = new ui.www_client.item_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){this.store.reload()},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_client', 'item_form');
	},
	Edit: function(){
		var row = this.getSelectionModel().getSelected();
		var id = row.get('id');
		var app = new App({waitMsg: this.frmLoading});
		app.on({
			apploaded: function(){
				var f = new ui.www_client.item_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleEdit, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){this.store.reload()},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({id: id})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_client', 'item_form');
	},
	multiSave: function(){
		this.store.save();
	},
	Delete: function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.store.remove(record);
			}
		}, this);
	},

	frmLoading: 'Загрузка формы редактирования',
	titleAdd: 'Добавление клиента',
	titleEdit: 'Редактирование клиента',
	bttAdd: 'Создать',
	bttEdit: 'Изменить',
	bttDelete: 'Удалить',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить клиента?",

	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			tbar: [
				{iconCls: 'note_add', text: this.bttAdd, handler: this.Add, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('www_client')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_client.main.superclass.constructor.call(this, config);
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
				var cmenu = new Ext.menu.Menu({items: [
					{iconCls: 'note_edit', text: this.bttEdit, handler: this.Edit, scope: this},
					{iconCls: 'note_delete', text: this.bttDelete, handler: this.Delete, scope: this},
					'-'
				]});
				e.stopEvent();  
				cmenu.showAt(e.getXY());
			},
			render: function(){this.store.load({params:{start: 0, limit: this.pagerSize}})},
			dblrowclick: this.Edit,
			scope: this
		});
	}
});
