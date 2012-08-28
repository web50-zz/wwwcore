ui.article_type.node_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id, pid){
		var f = this.getForm();
		f.load({
			url: 'di/article_type/get.json',
			params: {_sid: id, pid: pid},
			waitMsg: this.loadText,
			success: function(form, action){
				var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success){
						if(d.data.preview != '')
						{
						}
					}else{
						showError(d.errors);
					}
				},
			scope:this

		});
		if (id > 0) f.setValues([{id: '_sid', value: id}]);
		if (pid > 0) f.setValues([{id: 'pid', value: pid}]);
	}
	var Save = function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/article_type/set.do',
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
		var appName = this.getForm().findField('module').getValue()
		var appFace = 'configure_form';
		if (Ext.isEmpty(appName)) return;
                var appClass = 'ui.'+appName+'.'+appFace;
		var app = new App();
		app.on('apploaded', function(){
			var f = eval('new '+appClass+'()');
			var w = new Ext.Window({title: this.formSettingsTitle, modal: true, layout: 'fit', width: 480, height: 320, items: f});
			f.on({
				saved: function(data){
					this.getForm().findField('params').setValue(Ext.encode(data));
					w.destroy();
				},
				cancelled: function(){w.destroy()},
				scope: this
			});
			w.show(null, function(){
				f.Load(this.getForm().findField('params').getValue());
			}, this);
		}, this);
		app.Load(appName, appFace);
		
	}.createDelegate(this);
	ui.article_type.node_form.superclass.constructor.call(this,{
		frame: true,
		labelWidth: 120,
		defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
		items: [
			{name: '_sid', xtype: 'hidden'},
			{name: 'pid', xtype: 'hidden'},
			{fieldLabel: this.labelTitle, name: 'title', allowBlank: false, blankText: this.blankText, maxLength: 64, maxLengthText: this.textTitleNote},
			{fieldLabel: this.labelTag, name: 'name'},
			{fieldLabel: 'URI', name: 'uri', disabled: true}
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
Ext.extend(ui.article_type.node_form, Ext.form.FormPanel, {
	labelTitle: 'Наименование',
	textTitleNote:'Не более 64 символов',
	labelTag:'Тэг',
	fldComment: 'Описание',
	loadText: 'Загрузка данных формы',
	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',
	formSettingsTitle: 'Форма',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером"
});
