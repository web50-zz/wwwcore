ui.structure.page_view_point_form = Ext.extend(Ext.form.FormPanel, {
	formWidth: 450,
	formHeight: 350,

	lblViewPoint: 'Точка вывода',
	lblTitle: 'Наименование',
	lblHasStructure: 'Имеет структуру',
	lblDeepHide: 'Скрывать на подстраницах',
	lblCache: 'Кэшировать',
	lblCacheTime: 'Хранить кэш (сек)',
	lblOrder: 'Порядок отображения',
	lblModule: 'Модуль',
	lblCalls: 'Вызов',
	lblParams: 'Параметры',

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
			url: 'di/ui_view_point/get.json',
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
				url: 'di/ui_view_point/set.do',
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
	moduleCfg: function(){
		var appName = this.getForm().findField('ui_name').getValue()
		var appFace = 'configure_form';
		if (Ext.isEmpty(appName)) return;
		var app = new App();
		app.on('apploaded', function(){
			var f = eval('new ui.'+appName+'.'+appFace+'()');
			var w = new Ext.Window({title: 'Настройка страницы', modal: true, layout: 'fit', width: (f.formWidth || 480), height: (f.formHeight || 320), items: f});
			f.on({
				saved: function(data){
					this.getForm().findField('ui_configure').setValue(Ext.encode(data));
					w.destroy();
				},
				cancelled: function(){w.destroy()},
				scope: this
			});
			w.show(null, function(){
				f.Load(this.getForm().findField('ui_configure').getValue());
			}, this);
		}, this);
		app.Load(appName, appFace);
		
	},

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		var diEP = new Ext.form.ComboBox();
		Ext.apply(this, {
			labelAlign: 'right', 
			labelWidth: 170,
			border: false, 
			frame: true,
			defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
			items: [
				{name: '_sid', xtype: 'hidden'},
				{name: 'page_id', xtype: 'hidden'},
				{fieldLabel: this.lblTitle, name: 'title', maxLength: 255},
				{fieldLabel: this.lblModule, hiddenName: 'ui_name', xtype: 'combo',
					store: new Ext.data.JsonStore({url: 'di/interface/public.json', fields: ['name', 'human_name'], autoLoad: true}),
					valueField: 'name', displayField: 'human_name', triggerAction: 'all', selectOnFocus: true, editable: false,
					listeners: {
						select: function(comdo, record, index){
							var f = this.getForm();
							var fld = f.findField('ui_call');
							fld.getStore().baseParams = {_sinterface_name: f.findField('ui_name').getValue()};
							fld.store.baseParams = {_sinterface_name: record.get('name')};
							fld.clearValue();
							fld.doQuery('', true);
						},
						scope: this
					}
				},
				{fieldLabel: this.lblCalls, hiddenName: 'ui_call', xtype: 'combo',
					store: new Ext.data.JsonStore({url: 'di/entry_point/public.json', fields: ['name', 'human_name']}),
					allQuery: true, forceSelection: true, loadingText: this.loadText, emptyText: 'Укажите используемый вызов',
					valueField: 'name', displayField: 'human_name', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				new Ext.form.TriggerField({fieldLabel: this.lblParams, name: 'ui_configure', triggerClass: 'x-form-edit-trigger', onTriggerClick: this.moduleCfg.createDelegate(this)}),
				{fieldLabel: this.lblViewPoint, name: 'view_point', xtype: 'numberfield', width: 50, anchor: null},
				{fieldLabel: this.lblHasStructure, hiddenName: 'has_structure', value: 0, xtype: 'combo', width: 50, anchor: null,
					store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] }),
					valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				{fieldLabel: this.lblDeepHide, hiddenName: 'deep_hide', value: 0, xtype: 'combo', width: 50, anchor: null,
					store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] }),
					valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				{fieldLabel: this.lblOrder, name: 'order', xtype: 'numberfield', width: 50, anchor: null},
				{fieldLabel: this.lblCache, hiddenName: 'cache_enabled', value: 0, xtype: 'combo', width: 50, anchor: null,
					store: new Ext.data.SimpleStore({fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] }),
					valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				{fieldLabel: this.lblCacheTime, name: 'cache_timeout', xtype: 'numberfield', decimalPrecision: 0, maxLength: 6, width: 50, anchor: null}
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
		ui.structure.page_view_point_form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(isNew, formData, respData){
				this.getForm().setValues(respData);
			},
			data_loaded: function(data, id){
				var f = this.getForm();
				f.findField('ui_call').getStore().baseParams = {_sinterface_name: f.findField('ui_name').getValue()};
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
