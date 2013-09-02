ui.www_article_files.main = Ext.extend(ui.www_article_files.grid, {
	Load: function(data){
		this.setParams({}, true);
	},
	Add: function(){
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_files.item_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){this.getStore().reload()},
					cancelled: function(){w.destroy()},
					scope: this
				});
				var wid = this.getKey();
				w.show(null, function(){f.Load({item_id: wid})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_files', 'item_form');
	},
	Edit: function(){
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var id = this.getSelectionModel().getSelected().get('id');
				var f = new ui.www_article_files.item_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleEdt, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
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
		app.Load('www_article_files', 'item_form');
	},
	multiSave: function(){
		this.getStore().save();
	},
	Delete: function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.getStore().remove(record);
			}
		}, this);
	},
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			formWidth: 640,
			formHeight: 480,

			addTitle: "Добавление",
			editTitle: "Редактирование",

			cnfrmTitle: "Подтверждение",
			cnfrmMsg: "Вы действительно хотите удалить?",

			bttAdd: 'Добавить',
			bttForm: 'Изменить',
			bttDelete: 'Удалить'
		});
		Ext.apply(this, {
			enableDragDrop: true,
			ddGroup: 'rt',
			tbar: [
				{iconCls: 'comment_add', text: this.bttAdd, handler: this.Add, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('www_article_files')}}
			]
		});
		Ext.apply(this, {
			listeners: {
				rowcontextmenu: function(grid, rowIndex, e){
					grid.getSelectionModel().selectRow(rowIndex);
					var cmenu = new Ext.menu.Menu({items: [
						{iconCls: 'comment_edit', text: this.bttForm, handler: this.Edit, scope: this},
						{iconCls: 'comment_delete', text: this.bttDelete, handler: this.Delete, scope: this}
					]});
					e.stopEvent();  
					cmenu.showAt(e.getXY());
				},
				render: function(){
					new Ext.dd.DropTarget(this.getView().mainBody, {
						ddGroup: 'rt',
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
				rowmove: function(target, row){
					Ext.Ajax.request({
						url: 'di/www_article_files/reorder.do',
						method: 'post',
						params: {npos: target.selections[0].data.order, opos: row.data.order, id: row.id, cid: this.getKey()},
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
				rowmoved: function(){this.getStore().reload()},
				rowdblclick:function(grid,rowIndex,e){
							grid.getSelectionModel().selectRow(rowIndex);
							var row = grid.getSelectionModel().getSelected();
							this.fireEvent('row_selected',row);
				},
				scope: this
			}
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_files.main.superclass.constructor.call(this, config);
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
