ui.news.item_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id){
		var f = this.getForm();
		f.load({
			url: 'di/news/get.json',
			params: {_sid: id},
			waitMsg: this.loadText
		});
		f.setValues([{id: '_sid', value: id}]);
	}
	var Save = function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/news/set.do',
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
	}.createDelegate(this);
	var Cancel = function(){
		this.fireEvent('cancelled');
	}.createDelegate(this);
	ui.news.item_form.superclass.constructor.call(this, {
		frame: true, 
		fileUpload: true,
		labelWidth: 100, 
		defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
		items: [
			{name: '_sid', inputType: 'hidden'},
			{fieldLabel: this.lblFile, name: 'file', xtype: 'fileuploadfield', buttonCfg: {text: '', iconCls: 'folder'}},
			{fieldLabel:'Категория', hiddenName: 'category', value: 1, xtype: 'combo', anchor: null,
					store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[0, 'Не определена'],[1, '1'], [2, '2']]}),
					valueField: 'value', displayField: 'title', mode: 'local',
					triggerAction: 'all', selectOnFocus: true, editable: false
			},
			{fieldLabel: this.lblTitle, name: 'title', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
			{fieldLabel: this.lblRlsDate, name: 'release_date', anchor: null, format: 'Y-m-d', allowBlank: false, xtype: 'datefield'},
			{fieldLabel: this.lblSource, name: 'source', maxLength: 64, maxLengthText: 'Не больше 64 символов'},
			{fieldLabel: this.lblAuthor, name: 'author', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
			{hideLabel: true, name: 'content', xtype: 'ckeditor', CKConfig: {
				height: 260,
				filebrowserImageBrowseUrl: 'ui/file_manager/browser.html'
			}}
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
		saved: function(data){
			this.getForm().setValues([{id: '_sid', value: data.id}]);
		},
		scope: this
	})
}
Ext.extend(ui.news.item_form , Ext.form.FormPanel, {
	lblTitle: 'Заголовок',
	lblFile: 'Изображение',
	lblRlsDate: 'Дата релиза',
	lblSource: 'Источник',
	lblAuthor: 'Автор',
	loadText: 'Загрузка данных формы',

	saveText: 'Сохранение...',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером"
});
