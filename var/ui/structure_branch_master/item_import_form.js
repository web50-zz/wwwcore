ui.structure_branch_master.item_import_form = Ext.extend(Ext.form.FormPanel, {

	loadText: 'Загрузка данных формы',
	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",

	Load: function(input){
		var f = this.getForm();
		if(input.id>0){
			var params = {'_sid': input.id};
		};
		f.load({
			url: 'di/structure_branch_master/get.json',
			params:params ,
			waitMsg: this.loadText,
			success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success){
						f.setValues([{id: '_sid', value: d.data.id}]);
						this.fireEvent('afterloaddata');
					}else{
						showError(d.errors);
					}
				},
			scope:this
		});
	},
	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/structure_branch_master/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success)
						this.fireEvent('saved', d.data);
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
	frame: true, 
	defaults: {xtype: 'textfield', width: 100, anchor: '99%'},
	labelWidth:150,
	buttonAlign: 'right',
	autoScroll: true,

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		Ext.apply(this, config, {
			fileUpload: true,
			defaults: {xtype: 'textfield', width: 200, anchor: '100%'},
			items: [
				{name: '_sid', xtype: 'hidden'},
				{fieldLabel: 'Файл', name: 'file', xtype: 'fileuploadfield', buttonCfg: {text: '', iconCls: 'folder'}},
				{fieldLabel: 'Название', name: 'title'}
				],
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
		ui.structure_branch_master.item_import_form.superclass.constructor.call(this, config);
		if(this.pid > 0){
				this.getForm().setValues([{id: 'pid', value: this.pid}]);
		};
		this.addEvents(
			"afterloaddata",
			"saved",
			"cancelled"
		);
		this.on({
			saved: function(data){
				this.getForm().setValues([{id: '_sid', value: data.id}]);
			},
			render:function(){},
			afterrender: function(){}, 
			afterloaddata: function(){}, 
			scope: this
		});
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
