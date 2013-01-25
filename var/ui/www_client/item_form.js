ui.www_client.item_form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_client/get.json',
			params: {_sid: data.id},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				f.setValues([{id: '_sid', value: data.id}]);
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
				url: 'di/www_client/set.do',
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

	formWidth: 800,
	formHeight: 400,

	loadText: 'Загрузка данных формы',
	saveText: 'Сохранение...',
	bttSave: 'Сохранить',
	bttCancel: 'Отмена',
	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		Ext.apply(this, {
			labelAlign: 'right', 
			labelWidth: 150,
			frame: true,
			border: false,
			fileUpload: true,
			defaults: {xtype: 'textfield', width: 150, anchor: '100%'},
			items: [
				{name: '_sid', inputType: 'hidden'},
				{fieldLabel: 'Логотип', name: 'file', xtype: 'fileuploadfield', buttonCfg: {text: '', iconCls: 'folder'}},
				{fieldLabel: 'Клиент', name: 'client_name', allowBlank: false, maxLength: 128},
				{fieldLabel: 'Ссылка', name: 'link', allowBlank: false, maxLength: 255},
				{fieldLabel: 'Описание', name: 'description', xtype: 'htmleditor', height: 200}
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
		ui.www_client.item_form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
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
