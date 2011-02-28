ui.subscribe.message_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id,subscr_id){
		var f = this.getForm();
		f.load({
			url: 'di/subscribe_messages/get.json',
			params: {_sid: id},
			waitMsg: this.loadText
		});
		if(subscr_id>0)
		{
			f.setValues([{id: '_sid', value: id},{id: 'subscr_id', value: subscr_id}]);
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
				url: 'di/subscribe_messages/set.do',
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
	ui.subscribe.message_form.superclass.constructor.call(this, {
		frame: true, 
		defaults: {xtype: 'textfield'},
		items: [
			{name: '_sid', xtype: 'hidden'},
			{name: 'subscr_id', xtype: 'hidden'},
			{fieldLabel: 'Разослать', hiddenName: 'subscr_sheduled_to_send', value: 0,xtype:'combo',width:50,anchor:null,
				valueField: 'value',
				displayField: 'subscr_sheduled_to_send',
				mode: 'local',
				triggerAction: 'all',
				selectOnFocus: true,
				editable: false,
				store: new Ext.data.SimpleStore({ fields: ['value', 'subscr_sheduled_to_send'], data: [[0, 'Нет'], [1, 'Да']] })
			},
			{fieldLabel: this.labelTitle, name: 'subscr_title', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, maxLength: 255, maxLengthText: this.maxLengthText},
			{xtype:'htmleditor', fieldLabel: this.labelBody, name: 'subscr_message_body', width: 100, anchor: '100%', allowBlank: false, blankText: this.blankText, height:500}

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
Ext.extend(ui.subscribe.message_form, Ext.form.FormPanel, {
	labelTitle: 'Заголовок',
	labelBody: 'Сообщение',
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
