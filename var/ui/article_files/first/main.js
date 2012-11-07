ui.y_comp_files.main = Ext.extend(Ext.grid.GridPanel, {
	Add: function(){
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.y_comp_files.form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){w.destroy(); this.getStore().reload()},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('y_comp_files', 'form');
	},
	Edit: function(){
		var row = this.getSelectionModel().getSelected();
		var id = row.get('id');
		if (id > 0){
			var app = new App({waitMsg: 'Загрузка формы'});
			app.on({
				apploaded: function(){
					var f = new ui.y_comp_files.form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: function(){w.destroy(); this.getStore().reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({id: id})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('y_comp_files', 'form');
		}
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
	titleAdd: 'Добавить',
	titleEdit: 'Изменить',
	clmnImage: 'превью если есть',
	clmnTitle: 'Наименование',
	clmnComment: 'Описание',
	clmnFt: 'Тип файла',
	bttRefresh: 'Обновить',
	bttAdd: 'Добавить',
	bttEdit: 'Редактировать',
	bttDelete: 'Удалить',
	/**
	 * @constructor
	 */
	constructor: function(config){
		var image = new Ext.XTemplate('<img src="{url}{real_name}" width="92" border="0"/>');
		image.compile();
		Ext.apply(this, {
			loadMask: true,
			stripeRows: true,
			autoScroll: true,
			enableDragDrop: true,
			ddGroup: 'reference-tissue',
			autoExpandColumn: 'expand',
			// The data store
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
					api: {
						read: 'di/y_comp_files/list.js',
						create: 'di/y_comp_files/set.js',
						update: 'di/y_comp_files/mset.js',
						destroy: 'di/y_comp_files/unset.js'
					},
					isteners: {
						exception: function(proxy, type, action, exception){
							showError(exception.reader.jsonData.errors);
						}
					}
				}),
				// Typical JsonReader.  Notice additional meta-data params for defining the core attributes of your json-response
				reader: new Ext.data.JsonReader({
						totalProperty: 'total',
						successProperty: 'success',
						idProperty: 'id',
						root: 'records',
						messageProperty: 'errors'
					}, [
						{name: 'id', type: 'int'},
						{name: 'order', type: 'int'},
						'title', 'comment','file_type', 'real_name', 'url'
					]
				),
				// Typical JsonWriter
				writer: new Ext.data.JsonWriter({
					encode: true,
					listful: true,
					writeAllFields: false
				}),
				autoLoad: true,
				remoteSort: true,
				sortInfo: {field: 'order', direction: 'ASC'}
			}),
			columns: [
				{dataIndex: 'id', hidden: true},
				{header: this.clmnImage, dataIndex: 'title', width: 120, xtype: 'templatecolumn', tpl: image},
				{header: this.clmnTitle, dataIndex: 'title', width: 200},
				{header: this.clmnFt, dataIndex: 'file_type', id: 'expand'}
			],
			sm: new Ext.grid.RowSelectionModel({  
				listeners: {
					singleSelect: true,
					//beforerowselect: function(sm,i,ke,row){
					//	//this.ddText = title_img(row.data.title, null, row);
					//	this.ddText = row.data.title;
					//},
					scope: this
				}  
			}), 
			tbar: [
				{iconCls: 'layout_add', text: this.bttAdd, handler: this.Add, scope: this},
				'-',
				{iconCls: 'arrow_refresh', text: this.bttRefresh, handler: function(){this.getStore().reload()}, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('reference-tissue')}}
			],
			listeners: {
				render: function(){
					new Ext.dd.DropTarget(this.getView().mainBody, {
						ddGroup: 'reference-tissue',
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
				rowcontextmenu: function(grid, rowIndex, e){
					grid.getSelectionModel().selectRow(rowIndex);
					var cmenu = new Ext.menu.Menu({items: [
						{iconCls: 'layout_edit', text: this.bttEdit, handler: this.Edit, scope: this},
						{iconCls: 'layout_delete', text: this.bttDelete, handler: this.Delete, scope: this}
					]});
					e.stopEvent();  
					cmenu.showAt(e.getXY());
				},
				rowmove: function(target, row){
					Ext.Ajax.request({
						url: 'di/y_comp_files/reorder.do',
						method: 'post',
						params: {npos: target.selections[0].data.order, opos: row.data.order, id: row.id},
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
				scope: this
			}
		});
		config = config || {};
		Ext.apply(this, config);
		ui.y_comp_files.main.superclass.constructor.call(this, config);
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
