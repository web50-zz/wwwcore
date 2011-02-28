ui.guestbook.guestbook_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id){
		var f = this.getForm();
		f.load({
			url: 'di/guestbook/get.json',
			params: {_sid: id},
			waitMsg: this.loadText
		});
		f.setValues([{id: '_sid', value: id}]);
	}
	var Save = function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/guestbook/set.do',
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
	ui.guestbook.guestbook_form.superclass.constructor.call(this, {
		frame: true, 
		defaults: {xtype: 'textfield'},
		items: [
			{name: '_sid', xtype: 'hidden'},
			{xtype: 'displayfield', fieldLabel:this.labelCreated , name: 'gb_created_datetime'},
			{fieldLabel: this.labelName, name: 'gb_author_name', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{fieldLabel: this.labelEmail, name: 'gb_author_email', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{fieldLabel: this.labelLocation, name: 'gb_author_location', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{xtype: 'htmleditor',ieldLabel: this.labelRecord, name: 'gb_record', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{xtype: 'htmleditor',ieldLabel: this.labelAnswer, name: 'gb_answer', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText}
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
Ext.extend(ui.guestbook.guestbook_form, Ext.form.FormPanel, {
	labelName: 'Имя',
	labelEmail: 'Email',
	labelRecord: 'Запись',
	labelAnswer:'Ответ',
	labelLocation:'Местоположение',
	labelCreated:'Создано',

	loadText: 'Загрузка данных формы',
	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером"
});
