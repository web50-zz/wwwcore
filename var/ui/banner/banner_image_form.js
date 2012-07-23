ui.banner.banner_image_form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		if (data.id > 0){
			f.load({
				url: 'di/banner/get.json',
				params: {_sid: data.id},
				waitMsg: this.loadText,
				success: function(frm, act){
					var d = Ext.util.JSON.decode(act.response.responseText);
					f.setValues([{id: '_sid', value: data.id}]);
					this.fireEvent("data_loaded", d.data, data.id);
				},
				scope:this
			});
		}
		f.setValues(data);
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/banner/set.do',
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

	formWidth: 640,
	formHeight: 480,
	lblLink: 'Ссылка',
	lblTarget: 'Цель',
	lblTitle: 'Наименование',
	lblFile: 'Баннер',
	lblComment: 'Комментарий',

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
			layout: 'form',
			fileUpload: true,
			defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
			items: [
				{name: '_sid', xtype: 'hidden'},
				{name: 'banner_group_id', xtype: 'hidden'},
				{name: 'type', xtype: 'hidden', value: 1},
				{fieldLabel: this.lblFile, name: 'file', xtype: 'fileuploadfield', buttonCfg: {text: '', iconCls: 'folder'}},
				{fieldLabel: this.lblLink, name: 'link'},
				{fieldLabel: this.lblTarget, hiddenName: 'target', xtype: 'combo', width: 50,
					store: new Ext.data.SimpleStore({fields: ['value', 'title'], data: [
						['_blank', 'Новое окно (_blank)'],
						['_self', 'Текущее окно (_self)'],
						['_parent', 'Фрейм-родитель (_parent)'],
						['_top', 'В окне без фреймов (_top)']
					]}),
					valueField: 'value', displayField: 'title', value: '_blank',
					mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				{fieldLabel: this.lblTitle, name: 'title'},
				{fieldLabel: this.lblComment, name: 'comment', height: '100', xtype: 'htmleditor'}
			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.banner.banner_image_form.superclass.constructor.call(this, config);
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
