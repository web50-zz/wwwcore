ui.www_article_in_category.form = Ext.extend(Ext.form.FormPanel, {
	formWidth: 400,
	formHeight: 100,

	loadText: 'Загрузка данных формы',

	lblCategory: 'Категория',

	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",

	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_article_in_category/get.json',
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
				url: 'di/www_article_in_category/set.do',
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
		Ext.apply(this, {
			labelAlign: 'right', 
			labelWidth: 70,
			frame: true,
			border: false,
			defaults: {xtype: 'textfield', width: 150, anchor: '100%'},
			items: [
				{name: '_sid', xtype: 'hidden'},
				{name: 'item_id', xtype: 'hidden'},
				{name: 'category_id', xtype: 'numberfield', inputType: 'hidden', value: 0},
				{fieldLabel: this.lblCategory, xtype: 'compositefield', items: [
					{xtype: 'button', iconCls: 'add', listeners: {click: function(){this.fireEvent('select_category')}, scope: this}},
					{xtype: 'displayfield', name: 'category_title'}
				]}

			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.www_article_in_category.form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
			},
			data_loaded: function(data, id){
			},
			select_category: function(){
				var app = new App();
				app.on({
					apploaded: function(){
						var bf = this.getForm();
						var f = new ui.m2_category.category_selection();
						var w = new Ext.Window({title: "Выбор категории", maximizable: true, modal: true, layout: 'fit', width: 640, height: 480, items: f});
						f.on({
							selected: function(data){
								bf.setValues([
									{id: 'category_id', value: data.category_id},
									{id: 'category_title', value: data.category_title}
								]);
								w.close();
							},
							scope: this
						});
						w.show();
					},
					apperror: showError,
					scope: this
				});
				app.Load('m2_category', 'category_selection');
			},
			scope: this
		})
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
