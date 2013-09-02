ui.www_article_type.node_form = Ext.extend(Ext.form.FormPanel, {
	formWidth: 400,
	formHeight: 320,

	fldTitle: 'Наименование',
	fldName: 'Лат. Имя стр.',
	fldURI: 'URI',
	fldVisible: 'Видимый',
	loadText: 'Загрузка данных формы',
	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	textTitleNote:'Не более 64 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",

	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_article_type/get.json',
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
				url: 'di/www_article_type/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success){
						this.fireEvent('data_saved', !(f.findField('_sid').getValue() > 0), d.data, f.getValues());
						console
						this.reloadServices(d,d.data.id);
					}else{
						showError(d.errors);
					}
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
			//frame: true, 
			//labelWidth: 100, 
			//defaults: {xtype: 'textfield', width: 200, anchor: '100%'},
			border: false,
			layout: 'fit',
			items: [
				{xtype: 'tabpanel', activeItem: 0, border: false, defferedRender: false,
				defaults: {hideMode: 'offsets', frame: false}, items: [
					{id: 'tab-main', title: 'Общее', autoScroll: true, layout: 'form', border: false, frame: true,
					labelWidth: 150, defaults: {xtype: 'textfield', width: 200, anchor: '97%'},
					items: [
						{name: '_sid', inputType: 'hidden'},
						{name: 'pid', xtype: 'hidden'},
						{fieldLabel: this.fldTitle, name: 'title', allowBlank: false, blankText: this.blankText, maxLength: 64, maxLengthText: this.textTitleNote},
						{fieldLabel: this.fldVisible, hiddenName: 'published', value: 1, xtype: 'combo', width: 50, anchor: null,
							store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[1, 'Да'], [0, 'Нет']] }),
							valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
						},
						{fieldLabel: this.fldName, name: 'name'},
						{fieldLabel: this.fldURI, name: 'uri', disabled: true}
					]}
				], listeners: {
					beforetabchange: function(form, newTab, curTab){
						var id = this.getForm().findField('_sid').getValue();
						return ((newTab.id != 'tab-main' && id > 0) || newTab.id == 'tab-main');
					},
					scope: this
				}}
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
		ui.www_article_type.node_form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(isNew, data, id){
				this.getForm().setValues([{id: '_sid', value: data.id}, {id: 'uri', value: data.uri}]);
				this.reloadServices(data, id);
			},
			data_loaded: function(data, id){
				this.reloadServices(data, id);
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
	},

	reloadServices: function(data, id){
	}
});
