ui.www_slide.slide = Ext.extend(ui.www_slide.slide_grid, {
	titleAdd: 'Добавление слайда',
	titleEdit: 'Редактирование слайда',
	bttAddFlash: 'Flash',
	bttAddImage: 'Изображение',
	bttAddVideo: 'Видео внешнее',
	bttAddVideo2: 'Видео локалльное',
	bttAddText: 'Текст',
	bttEdit: 'Изменить',
	bttDelete: 'Удалить',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить этот баннер?",

	AddFlash: function(){
		this.Add(1);
	},
	AddImage: function(){
		this.Add(2);
	},
	AddVideo: function(){
		this.Add(3);
	},
	AddText: function(){
		this.Add(4);
	},
	AddVideo2: function(){
		this.Add(5);
	},
	Add: function(type){
		var pid = this.getPid();
		if (pid > 0){
			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					if (type == 1){
						var f = new ui.www_slide.slide_image_form();
						var title = this.titleAdd+' ('+this.bttAddFlash+')';
					}else if (type == 2){
						var f = new ui.www_slide.slide_flash_form();
						var title = this.titleAdd+' ('+this.bttAddImage+')';
					}else if (type == 3){
						var f = new ui.www_slide.slide_video_form();
						var title = this.titleAdd+' ('+this.bttAddVideo+')';
					}else if (type == 4){
						var f = new ui.www_slide.slide_text_form();
						var title = this.titleAdd+' ('+this.bttAddText+')';
					}else if (type == 5){
						var f = new ui.www_slide.slide_video2_form();
						var title = this.titleAdd+' ('+this.bttAddVideo2+')';
					}else
						return;
					var w = new Ext.Window({iconCls: this.iconCls, title: title, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: function(){this.store.reload()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({slide_group_id: pid})});
				},
				apperror: showError,
				scope: this
			});
			if (type == 1)
				app.Load('www_slide', 'slide_image_form');
			else if (type == 2)
				app.Load('www_slide', 'slide_flash_form');
			else if (type == 3)
				app.Load('www_slide', 'slide_video_form');
			else if (type == 4)
				app.Load('www_slide', 'slide_text_form');
			else if (type == 5)
				app.Load('www_slide', 'slide_video2_form');
			else
				showError('Не изветный тип баннера');
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
				if (type == 1){
					var f = new ui.www_slide.slide_image_form();
					var title = this.titleEdit+' ('+this.bttAddFlash+')';
				}else if (type == 2){
					var f = new ui.www_slide.slide_flash_form();
					var title = this.titleEdit+' ('+this.bttAddImage+')';
				}else if (type == 3){
					var f = new ui.www_slide.slide_video_form();
					var title = this.titleEdit+' ('+this.bttAddVideo+')';
				}else if (type == 4){
					var f = new ui.www_slide.slide_text_form();
					var title = this.titleEdit+' ('+this.bttAddText+')';
				}else if (type == 5){
					var f = new ui.www_slide.slide_video2_form();
					var title = this.titleEdit+' ('+this.bttAddVideo2+')';
				}else
					return;
				var w = new Ext.Window({iconCls: this.iconCls, title: title, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
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
			app.Load('www_slide', 'slide_image_form');
		else if (type == 2)
			app.Load('www_slide', 'slide_flash_form');
		else if (type == 3)
			app.Load('www_slide', 'slide_video_form');
		else if (type == 4)
			app.Load('www_slide', 'slide_text_form');
		else if (type == 5)
			app.Load('www_slide', 'slide_video2_form');
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

	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			tbar: [
				{iconCls: 'page_white_flash', text: this.bttAddFlash, handler: this.AddFlash, scope: this},
				{iconCls: 'page_white_picture', text: this.bttAddImage, handler: this.AddImage, scope: this},
				{iconCls: 'page_white_star', text: this.bttAddVideo, handler: this.AddVideo, scope: this},
				{iconCls: 'page_white_star', text: this.bttAddVideo2, handler: this.AddVideo2, scope: this},
				{iconCls: 'page_white_text', text: this.bttAddText, handler: this.AddText, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('www_slide')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_slide.slide.superclass.constructor.call(this, config);
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
