ui.www_slide.group_form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		var id = parseInt(data.id) || 0;
		var pid = parseInt(data.pid) || 0;
		f.load({
			url: 'di/www_slide_group/get.json',
			params: {_sid: id, pid: pid},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				if (id > 0) f.setValues([{id: '_sid', value: id}]);
				if (pid > 0) f.setValues([{id: 'pid', value: pid}]);
				this.fireEvent("data_loaded", d.data, id);
			},
			scope:this
		});
		f.setValues(data);
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/www_slide_group/set.do',
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

	formWidth: 640,
	formHeight: 480,
	lblTitle: 'Наименование',
	lblComment: 'Комментарий',
	lblWidth: 'Ширина',
	lblHeight: 'Высота',
	lblId:'Id',

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
			labelWidth: 120,
			border: false, 
			frame: true,
			defaults: {xtype: 'textfield', width: 150, anchor: '100%'},
			items: [
				{name: '_sid', inputType: 'hidden'},
				{name: 'pid', xtype: 'hidden'},
				{fieldLabel: this.lblId, name: 'id', xtype: 'displayfield'},
				{fieldLabel: this.lblTitle, name: 'title', allowBlank: false, maxLength: 255},
				{fieldLabel: this.lblWidth, name: 'width', xtype: 'numberfield', allowDecimals: false},
				{fieldLabel: this.lblHeight, name: 'height', xtype: 'numberfield', allowDecimals: false},
				{fieldLabel: this.lblComment, name: 'comment', height: '100', xtype: 'htmleditor'}
			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			],
			keys: [
				//{key: [Ext.EventObject.ENTER], handler: this.Save, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.www_slide.group_form.superclass.constructor.call(this, config);
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
