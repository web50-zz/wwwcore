ui.banner.banner = Ext.extend(ui.banner.banner_grid, {
	AddFlash: function(){
		var pid = this.getPid();
		if (pid > 0){
			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					var f = new ui.banner.banner_flash_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: function(){this.store.reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({banner_group_id: pid})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('banner', 'banner_flash_form');
		}else{
			showError('Не выбранна группа.');
		}
	},
	AddImage: function(){
		var pid = this.getPid();
		if (pid > 0){
			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					var f = new ui.banner.banner_image_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: function(){this.store.reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({banner_group_id: pid})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('banner', 'banner_image_form');
		}else{
			showError('Не выбранна группа.');
		}
	},
	Edit: function(){
		var row = this.getSelectionModel().getSelected();
		var id = row.get('id');
		var type = row.get('type');
		var app = new App({waitMsg: 'Edit form loading'});
		app.on({
			apploaded: function(){
				if (type == 1)
					var f = new ui.banner.banner_image_form();
				else if (type == 2)
					var f = new ui.banner.banner_flash_form();
				else
					return;
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
		if (type == 1)
			app.Load('banner', 'banner_image_form');
		else if (type == 2)
			app.Load('banner', 'banner_flash_form');
		else
			showError('Не изветный тип баннера');
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
	bttAddFlash: 'Flash',
	bttAddImage: 'Изображение',
	bttEdit: 'Изменить',
	bttDelete: 'Удалить',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить этот баннер?",

	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			tbar: [
				{iconCls: 'page_white_flash', text: this.bttAddFlash, handler: this.AddFlash, scope: this},
				{iconCls: 'page_white_picture', text: this.bttAddImage, handler: this.AddImage, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('banner')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.banner.banner.superclass.constructor.call(this, config);
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
			//render: function(){this.store.load({params:{start: 0, limit: this.pagerSize}})},
			dblrowclick: this.Edit,
			scope: this
		});
	}
});
