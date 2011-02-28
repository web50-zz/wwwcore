ui.faq.faq_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id,part_id){
		var f = this.getForm();
		f.load({
			url: 'di/faq/get.json',
			params: {_sid: id},
			waitMsg: this.loadText
		});
		if(part_id>0)
		{
			f.setValues([{id: '_sid', value: id},{id: 'faq_part_id', value: part_id}]);
		}
		else
		{
			f.setValues([{id: '_sid', value: id}]);
		}

	}
	var Save = function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/faq/set.do',
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
	ui.faq.faq_form.superclass.constructor.call(this, {
		frame: true, 
		defaults: {xtype: 'textfield'},
		items: [
			{name: '_sid', xtype: 'hidden'},
			{xtype: 'displayfield', fieldLabel:this.labelCreated , name: 'faq_created_datetime'},
			{xtype: 'displayfield', fieldLabel:this.labelChanged , name: 'faq_changed_datetime'},
			{name: 'faq_part_id', xtype: 'hidden'},
			{fieldLabel: this.labelName, name: 'faq_question_author_name', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{fieldLabel: this.labelEmail, name: 'faq_question_author_email', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{xtype:'htmleditor', fieldLabel: this.labelQuestion, name: 'faq_question', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, height:100},
			{xtype:'htmleditor', fieldLabel: this.labelAnswer, name: 'faq_answer', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, height:300}
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
Ext.extend(ui.faq.faq_form, Ext.form.FormPanel, {
	labelName: 'Имя автора',
	labelEmail: 'email автора',
	labelQuestion: 'Вопрос',
	labelAnswer: 'Ответ',
	labelCreated: 'Создано',
	labelChanged: 'Изменено',
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
