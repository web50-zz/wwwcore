ui.structure.node_form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/structure/get.json',
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
				url: 'di/structure/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success)
						this.fireEvent('data_saved', !(f.findField('_sid').getValue() > 0), f.getValues(), d.data);
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
	lblVisible: 'Видимый',
	lblName: 'Имя',
	lblURI: 'uri',
	lblRedirect: 'Перенаправить',
	lblTheme: 'Тема',
	lblKeyw: 'META Ключевые слова',
	lblDescr: 'META Описание',
	lblTmpl: 'Шаблон',

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
			border: false, 
			frame: true,
			defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
			items: [
				{name: '_sid', xtype: 'hidden'},
				{name: 'pid', xtype: 'hidden'},
				{name: 'id', xtype: 'displayfield',fieldLabel:'Id'},
				{fieldLabel: this.lblTitle, name: 'title', allowBlank: false, maxLength: 64},
				{fieldLabel: this.lblVisible, hiddenName: 'hidden', value: 0, xtype: 'combo', width: 50, anchor: null,
					store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Да'], [1, 'Нет']] }),
					valueField: 'value', displayField: 'title',
					mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				{fieldLabel: this.lblName, name: 'name'},
				{fieldLabel: this.lblURI, name: 'uri', disabled: true},
				{fieldLabel: this.lblRedirect, name: 'redirect'},
				{fieldLabel: this.lblTheme, name: 'theme_overload'},
				{fieldLabel: this.lblKeyw, name: 'mkeywords', xtype:'textarea'},
				{fieldLabel: this.lblDescr, xtype:'textarea',name: 'mdescr'},
				{fieldLabel: this.lblTmpl, xtype: 'combo', hiddenName: 'template', value: 'default.html',
					store: new Ext.data.JsonStore({url: 'ui/structure/templates.do', fields: ['template']}),
					valueField: 'template', displayField: 'template',
					emptyText: 'Выберите шаблон...', typeAhead: true, triggerAction: 'all', selectOnFocus: true, editable: false
				}
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
		ui.structure.node_form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(isNew, formData, respData){
				this.getForm().setValues([{id: '_sid', value: respData.id}, {id: 'uri', value: respData.uri},{id: 'id', value: respData.id}]);
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
