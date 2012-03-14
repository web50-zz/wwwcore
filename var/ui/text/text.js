ui.text.main = Ext.extend(ui.text.grid, {
	Add: function(){
		var app = new App({waitMsg: 'Edit form loading'});
		app.on({
			apploaded: function(){
				var f = new ui.text.item_form();
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
		app.Load('text', 'item_form');
	},
	Edit: function(){
		var row = this.getSelectionModel().getSelected();
		var id = row.get('id');
		var app = new App({waitMsg: 'Edit form loading'});
		app.on({
			apploaded: function(){
				var f = new ui.text.item_form();
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
		app.Load('text', 'item_form');
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

	titleAdd: 'Добавление текстовки',
	titleEdit: 'Редактирование текстовки',
	bttAdd: 'Создать',
	bttEdit: 'Изменить',
	bttDelete: 'Удалить',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эту текстовку?",

	srchSelTitle: "Заголовок",
	srchSelContent: "Содержимое",
	srchBttSearch: "Поиск",
	srchBttCancel: "Сбросить",
	srchTxtFind: "Найти: ",

	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		var srchField = new Ext.form.TextField();
		var srchType = new Ext.form.ComboBox({
			width: 100,
			store: new Ext.data.SimpleStore({fields: ['value', 'title'], data: [
				['title', this.srchSelTitle],
				['content', this.srchSelContent]
			]}), value: 'title',
			valueField: 'value', displayField: 'title', triggerAction: 'all', mode: 'local', editable: false
		});
		var srchBttOk = new Ext.Toolbar.Button({
			text: this.srchBttSearch,
			iconCls:'find',
			handler: function search_submit(){
				this.setParams({field: srchType.getValue(), query: srchField.getValue()}, true);
			},
			scope: this
		});
		var srchBttCancel = new Ext.Toolbar.Button({
			text: this.srchBttCancel,
			iconCls:'cancel',
			handler: function search_submit(){
				srchType.setValue('title');
				srchField.setValue('');
				this.setParams({field: '', query: ''}, true);
			},
			scope: this
		});
		Ext.apply(this, {
			tbar: [
				{iconCls: 'note_add', text: this.bttAdd, handler: this.Add, scope: this},
				'-', new Ext.Toolbar.TextItem(this.srchTxtFind),
				srchType, srchField, srchBttOk, srchBttCancel,
				'->', {iconCls: 'help', handler: function(){showHelp('text')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.text.main.superclass.constructor.call(this, config);
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
					'-',
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
