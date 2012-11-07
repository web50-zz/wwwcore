ui.y_comp_files.form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/y_comp_files/get.json',
			params: {_sid: data.id},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				f.setValues([{id: '_sid', value: d.data.id}]);
				this.fireEvent("data_loaded", d.data, d.data.id);
			},
			scope:this
		});
		f.setValues(data);
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/y_comp_files/set.do',
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

	lblFile: 'Файл',
	lblTitle: 'Наименование',
	lblDescr: 'Описание',
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
		Ext.apply(this, {
			formWidth: 480,
			formHeight: 200
		});
		Ext.apply(this, {
			frame: true, 
			fileUpload: true,
			defaults: {xtype: 'textfield',anchor:'100%'},
			items: [
				{name: '_sid', inputType: 'hidden'},
				{fieldLabel: this.lblFile, name: 'file', xtype: 'fileuploadfield', buttonCfg: {text: '', iconCls: 'folder'}},
				{fieldLabel: this.lblTitle, name: 'title'},
				{fieldLabel:'Тип', hiddenName: 'file_type', value: 1, xtype: 'combo', anchor: null,
					store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Не определена'],[1, 'Лого компании']]}),
					valueField: 'value', displayField: 'title', mode: 'local',
					triggerAction: 'all', selectOnFocus: true, editable: false
			},
				{fieldLabel: this.lblDescr, name: 'description', xtype: 'textarea'}
			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.y_comp_files.form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
			},
			data_loaded: function(data, id){
				preview_tpl.overwrite(preview.body, data);
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
