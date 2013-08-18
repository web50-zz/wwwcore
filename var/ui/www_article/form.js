ui.www_article.form = Ext.extend(Ext.form.FormPanel, {
	formWidth: 900,
	formHeight: 600,

	loadText: 'Загрузка данных формы',
	lblTitle: 'Заголовок',
	lblFile: 'Изображение',
	lblRlsDate: 'Дата релиза',
	lblSource: 'Источник',
	lblAuthor: 'Автор',
	loadText: 'Загрузка данных формы',
	lblURI: 'URI',
	lblPublished: 'Опубликовано',
	lblPostType:'Тип публикации',

	lblId: "Id",
	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",
	errIdNodSet: 'Сохраните запись',
	msgNotDefined: 'Операция не активна, пока не сохранена форма',
	bttFiles: 'Файлы',
	bttComments: 'Комментарии',
	bttCategory: 'Входит в категории',
	bttTags:'Тэги',

	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_article/get.json',
			params: {_sid: data._sid},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				this.fireEvent("data_loaded", d.data, data.id);
			},
			scope:this
		});
		f.setValues(data);
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/www_article/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success)
						this.fireEvent('data_saved', d.data, d.data.id);
					else
						showError(d.errors);
				},
				failure: function(form, action){
					switch (action.failureType){
						case Ext.form.Action.CLIENT_INVALID:
							showError(this.errInputText);
						break;
						case Ext.form.Action.CONNECT_FAILURE:
							showError(this.errConnectionText);
						break;
						case Ext.form.Action.SERVER_INVALID:
							showError(action.result.errors);
					}
				},
				scope: this
			});
		}
	},
	
	Cancel: function(){
		this.fireEvent('cancelled');
	},

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		var tb = new Ext.Toolbar({
			enableOverflow: true,
			items: [
				{iconCls: 'chart_organisation', text: this.bttCategory, handler: this.itemCategory, scope: this},
				{iconCls: 'application_view_tile', text: this.bttFiles, handler: this.filesList, scope: this},
				{iconCls: 'application_view_tile', text: this.bttComments, handler: this.commentsList, scope: this},
				{iconCls: 'application_view_tile', text: this.bttTags, handler: this.tagsList, scope: this}
			]
		});
		Ext.apply(this, {
			layout: 'fit',
			tbar: tb,
			items: [{
					layout: 'form',
					frame: true, 
					labelWidth: 100,
					labelAlign: 'right',
					autoScroll: true,
					defaults: {xtype: 'textfield', width: 80, anchor: '98%'},
					items: [
						{name: '_sid', xtype: 'hidden'},
						{fieldLabel: this.lblId, name: 'id', xtype: 'displayfield'},
						{fieldLabel: this.lblTitle, name: 'title', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
						{fieldLabel: this.lblPostType, hiddenName: 'post_type', xtype: 'combo', allowBlank: false,
							valueField: 'id', displayField: 'title', value: '1', emptyText: '', 
							store: new Ext.data.JsonStore({url: 'di/www_article_post_types/type_list.json', root: 'records', fields: ['id', 'title'], autoLoad: true,
								listeners: {
									load: function(store,ops){
										var f = this.getForm().findField('post_type');
										f.setValue(f.getValue());
									}, 
									beforeload:function(store,ops){
									},
									scope: this
								}
							}),
							mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
						},

						{fieldLabel: this.lblPublished, hiddenName: 'published', value: 1, xtype: 'combo', width: 50, anchor: null,
							store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[1, 'Да'], [0, 'Нет']] }),
							valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
						},
						{fieldLabel: this.lblRlsDate, name: 'release_date', width: 100, format: 'Y-m-d H:i:s', allowBlank: false, xtype: 'datefield'},
						{fieldLabel: this.lblSource, name: 'source', maxLength: 64, maxLengthText: 'Не больше 64 символов'},
						{fieldLabel: this.lblAuthor, name: 'author', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
						{fieldLabel: this.lblURI, name: 'uri', maxLength: 255, maxLengthText: 'Не больше 255 символов',allowBlank:false},
						{fieldLabel: this.lblCategory, xtype: 'compositefield', items: [
							{xtype: 'button', iconCls: 'add', text:'Добавить изображение из загруженных',listeners: {click: function(){this.fireEvent('insert_image')}, scope: this}},
							{xtype: 'displayfield', name: 'some_image'}
						]},
						{hideLabel: true, name: 'content', xtype: 'ckeditor', CKConfig: {
							height: 260,
							filebrowserImageBrowseUrl: 'ui/file_manager/browser.html'
						}}
					]
			}],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.www_article.form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
			},
			data_loaded: function(data, id){
			},
			insert_image: function(){
				var vals = this.getForm().getValues();
				if(!(vals._sid >0)){
					showError(this.msgNotDefined);
					return;
				}
				this.image_selector(vals._sid);
			},
			scope: this
		})
	},
	image_selector: function(id){
		if(!(id>0)){
			showError(this.msgNotDefined);
			return;
		}
		var fm = this.getForm();
		var textar = fm.findField('content');
		var ck_id = textar.id;
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_files.main();
				f.setParams({'_sitem_id':id});
				var w = new Ext.Window({iconCls: 'application_view_tile', title: this.bttFile, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					row_selected:function(row){
						var real_name  =  row.get('real_name');
						var url = row.get('url');
						var path = url + real_name;
						CKEDITOR.instances[ck_id].insertHtml('<img src="'+path+'"/>');
						w.destroy();
					},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_files', 'main');
	
	},
	filesList: function(b, e){
		var fm = this.getForm();
		var vals = fm.getValues();
		if(!(vals._sid>0)){
			showError(this.msgNotDefined);
			return;
		}
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_files.main();
				f.setParams({'_sitem_id':vals._sid});
				var w = new Ext.Window({iconCls: b.iconCls, title: b.text, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_files', 'main');
	},
	tagsList: function(b, e){
		var fm = this.getForm();
		var vals = fm.getValues();
		if(!(vals._sid>0)){
			showError(this.msgNotDefined);
			return;
		}
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_tags.selector();
				var w = new Ext.Window({iconCls: b.iconCls, title: b.text, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){
					f.Load({'_sitem_id':vals._sid});
				});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_tags', 'selector');
	},

	itemCategory: function(b, e){
		var fm = this.getForm();
		var vals = fm.getValues();
		if(!(vals._sid>0)){
			showError(this.msgNotDefined);
			return;
		}
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_in_category.main();
				f.setParams({'_sitem_id':vals._sid});
				var w = new Ext.Window({iconCls: b.iconCls, title: b.text, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_in_category', 'main');
	},
	commentsList: function(b, e){
		var fm = this.getForm();
		var vals = fm.getValues();
		if(!(vals._sid>0)){
			showError(this.msgNotDefined);
			return;
		}
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_comment.main_grid();
				f.setParams({'_sarticle_id':vals._sid});
				var w = new Ext.Window({iconCls: b.iconCls, title: b.text, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_comment', 'main_grid');
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
