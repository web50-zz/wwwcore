ui.structure.page_view_point_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id, pid){
		var f = this.getForm();
		f.load({
			url: 'di/ui_view_point/get.json',
			params: {_sid: id, pid: pid},
			waitMsg: this.loadText,
			success: function(response, result, type){
				var fld = f.findField('ui_call');
				fld.store.baseParams = {_sinterface_name: f.findField('ui_name').getValue()};
				fld.store.load();
			}
		});
		if (id > 0) f.setValues([{id: '_sid', value: id}]);
		if (pid > 0) f.setValues([{id: 'pid', value: pid}]);
	}
	var Save = function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/ui_view_point/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success)
						this.fireEvent('saved', !(f.findField('_sid').getValue() > 0), f.getValues(), d.data);
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
	}.createDelegate(this);
	var Cancel = function(){
		this.fireEvent('cancelled');
	}.createDelegate(this);
	var moduleCfg = function(){
		var appName = this.getForm().findField('ui_name').getValue()
		var appFace = 'configure_form';
		if (Ext.isEmpty(appName)) return;
                var appClass = 'ui.'+appName+'.'+appFace;
		var app = new App();
		app.on('apploaded', function(){
			var f = eval('new '+appClass+'()');
			var w = new Ext.Window({title: 'Настройка страницы', modal: true, layout: 'fit', width: 480, height: 320, items: f});
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
		
	}.createDelegate(this);
	var diEP = new Ext.form.ComboBox({
		store: new Ext.data.JsonStore({url: 'di/entry_point/public.json', fields: ['name', 'human_name']}),
		fieldLabel: this.labelCalls, hiddenName: 'ui_call',
		allQuery: true,
		forceSelection: true,
		loadingText: this.loadText,
		emptyText: 'Укажите используемый вызов',
		valueField: 'name', displayField: 'human_name', triggerAction: 'all', selectOnFocus: true, editable: false
	});
	ui.structure.page_view_point_form.superclass.constructor.call(this,{
		frame: true, 
		labelWidth: 170,
		defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
		items: [
			{name: '_sid', xtype: 'hidden'},
			{name: 'pid', xtype: 'hidden'},
			{fieldLabel: this.labelTitle, name: 'title', maxLength: 255},
			new Ext.form.ComboBox({fieldLabel: this.labelModule, hiddenName: 'ui_name',
				store: new Ext.data.JsonStore({url: 'di/interface/public.json', fields: ['name', 'human_name'], autoLoad: true}),
				valueField: 'name', displayField: 'human_name', triggerAction: 'all', selectOnFocus: true, editable: false,
				listeners: {
					select: function(comdo, record, index){
						diEP.store.baseParams = {_sinterface_name: record.get('name')};
						diEP.clearValue();
						diEP.doQuery('', true);
					}
				}
			}),
			diEP,
			new Ext.form.TriggerField({fieldLabel: this.labelParams, name: 'ui_configure', triggerClass: 'x-form-edit-trigger', onTriggerClick: moduleCfg}),
			{fieldLabel: this.labelViewPoint, name: 'view_point', xtype: 'numberfield', width: 50, anchor: null},
			{fieldLabel: this.labelHasStructure, hiddenName: 'has_structure', value: 0, xtype: 'combo', width: 50, anchor: null,
				store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] }),
				valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
			},
			{fieldLabel: this.labelDeepHide, hiddenName: 'deep_hide', value: 0, xtype: 'combo', width: 50, anchor: null,
				store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] }),
				valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
			},
			{fieldLabel: this.labelOrder, name: 'order', xtype: 'numberfield', width: 50, anchor: null},
			{fieldLabel: this.labelCache, hiddenName: 'cache_enabled', value: 0, xtype: 'combo', width: 50, anchor: null,
				store: new Ext.data.SimpleStore({fields: ['value', 'title'], data: [[0, 'Нет'], [1, 'Да']] }),
				valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
			},
			{fieldLabel: this.labelCacheTime, name: 'cache_timeout', xtype: 'numberfield', decimalPrecision: 0, maxLength: 6, width: 50, anchor: null}
		],
		buttonAlign: 'right',
		buttons: [
			{iconCls: 'disk', text: this.bttSave, handler: Save},
			{iconCls: 'cancel', text: this.bttCancel, handler: Cancel}
		]
	});
	this.addEvents(
		"saved",
		"cancelled"
	);
	this.on({
		saved: function(isNew, formData, respData){
			this.getForm().setValues([{id: '_sid', value: respData.id}, {id: 'uri', value: respData.uri}]);
		},
		scope: this
	});
}
Ext.extend(ui.structure.page_view_point_form, Ext.form.FormPanel, {
	labelViewPoint: 'Точка вывода',
	labelTitle: 'Наименование',
	labelHasStructure: 'Имеет структуру',
	labelDeepHide: 'Скрывать на подстраницах',
	labelCache: 'Кэшировать',
	labelCacheTime: 'Хранить кэш (сек)',
	labelOrder: 'Порядок отображения',
	labelModule: 'Модуль',
	labelCalls: 'Вызов',
	labelParams: 'Параметры',

	loadText: 'Загрузка данных...',
	saveText: 'Сохранение данных...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером"
});
