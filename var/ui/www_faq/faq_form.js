ui.www_faq.faq_form = Ext.extend(Ext.form.FormPanel, {
	formWidth: 700,
	formHeight: 600,

	fldDate: 'Дата',
	fldName: 'Коротко',
	fldEmail: 'e-mail',
	fldComment: 'Содержание',
	fldFio:'Фио',
	fldPhone:'Телефон',

	loadText: 'Загрузка данных формы',
	saveText: 'Сохранение...',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",

	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_faq/get.json',
			params: {_sid: data.id},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				if (data.id > 0) f.setValues([{id: '_sid', value: data.id}]);
				if (data.pid > 0) f.setValues([{id: 'pid', value: data.pid}]);
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
				url: 'di/www_faq/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success)
						this.fireEvent('data_saved', !(f.findField('_sid').getValue() > 0), d.data, f.getValues());
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
		var tpl = new Ext.Template('<b>{created_datetime}</b>');
		tpl.compile();
		Ext.apply(this, {
			frame: true, 
			labelWidth: 100, 
			defaults: {xtype: 'textfield', width: 200, anchor: '100%'},
			items: [
				{name: '_sid', inputType: 'hidden'},
				{name: 'pid', xtype: 'hidden'},
				{fieldLabel: this.fldDate, name: 'created_datetime', xtype: 'displayfield', tpl: tpl},
				{fieldLabel: this.fldName, name: 'name', allowBlank: false, maxLength: 255},
				{fieldLabel: this.fldEmail, name: 'email', maxLength: 255},
				{fieldLabel: this.fldFio, name: 'fio', maxLength: 255},
				{fieldLabel: this.fldPhone, name: 'phone', maxLength: 255},
				{fieldLabel: this.fldComment, name: 'comment', height: '450',  xtype: 'ckeditor', CKConfig: {height: 60, toolbar: 'Basic'}}
			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			],
			keys: [
				{key: [Ext.EventObject.ENTER], handler: this.Save, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.www_faq.faq_form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(isNew, data, id){
				this.getForm().setValues({_sid: data.id});
			},
			data_loaded: function(data, id){
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
