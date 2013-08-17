ui.www_career.item_form = function(config){
	Ext.apply(this, config);
	this.Load = function(id){
		var f = this.getForm();
		f.load({
			url: 'di/www_career/get.json',
			params: {_sid: id},
			waitMsg: this.loadText
		});
		f.setValues([{id: '_sid', value: id}]);
	}
	var Save = function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/www_career/set.do',
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

	
	var  filesList = function(b,d){
		var fm = this.getForm();
		var vals = fm.getValues();
		if(!(vals._sid>0)){
			showError(this.msgNotDefined);
			return;
		}
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_career_files.main();
				f.setParams({'_swww_career_id':vals._sid});
				var w = new Ext.Window({iconCls: b.iconCls, title: b.text, maximizable: true, modal: true, layout: 'fit', width: 500, height: 600, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_career_files', 'main');
	}.createDelegate(this);
	
	ui.www_career.item_form.superclass.constructor.call(this, {
		border: false,
		layout: 'fit',
		fileUpload: true,
		items: [
			{xtype: 'tabpanel', activeItem: 0, border: false, defferedRender: false,
				defaults: {hideMode: 'offsets', frame: false}, items: [
					{id: 'tab-main', title: this.ttlTab1, autoScroll: true, layout: 'form',
						border: false,
						frame: true,
						labelWidth: 200,
						labelAlign: 'right',
						autoScroll:true,
						defaults: {xtype: 'textfield', width: 150, anchor: '98%'},
						items: [

								{name: '_sid', inputType: 'hidden'},
								{fieldLabel: this.lblFile, name: 'file', xtype: 'fileuploadfield', buttonCfg: {text: '', iconCls: 'folder'}},
								{fieldLabel: this.lblTitle, name: 'title', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
								{fieldLabel: this.lblAuthor, name: 'position', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
								{fieldLabel: this.lblURI, name: 'uri', maxLength: 255, maxLengthText: 'Не больше 255 символов',allowBlank:false},
								{fieldLabel: 'Контактная Персона', name: 'contact_person'},
								{fieldLabel: 'Контактный Мобильный', name: 'mobile_phone'},
								{fieldLabel: 'Контактный Телефон/Факс', name: 'phone_fax'},
								{fieldLabel: 'Адрес', name: 'address'},
								{fieldLabel: 'E-mail', name: 'email'},
								{fieldLabel: 'Описание', name: 'titles', xtype: 'htmleditor'}, 
								{fieldLabel: 'Обязанности', name: 'practices', xtype: 'htmleditor'}, 
								{fieldLabel: 'Требования', name: 'requirements', xtype: 'htmleditor'}, 
								{fieldLabel: 'Условия', name: 'conditions', xtype: 'htmleditor'}, 
					]},
					{id: 'tab-two', title: this.ttlTab2, autoScroll: true, layout: 'form',
						border: false,
						frame: true,
						labelWidth: 200,
						labelAlign: 'right',
						defaults: {xtype: 'textfield', width: 150, anchor: '98%'},
						items: [
								{fieldLabel: this.lblTitle_eng, name: 'title_eng', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
								{fieldLabel: this.lblAuthor_eng, name: 'position_eng', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
								{fieldLabel: 'Контактная Персона', name: 'contact_person_eng'},
								{fieldLabel: 'Контактный Адрес eng', name: 'address_eng'},
								{fieldLabel: 'Описание eng', name: 'titles_eng', xtype: 'htmleditor'}, 
								{fieldLabel: 'Обязанности eng', name: 'practices_eng', xtype: 'htmleditor'}, 
								{fieldLabel: 'Требования eng', name: 'requirements', xtype: 'htmleditor'}, 
								{fieldLabel: 'Условия eng', name: 'conditions_eng', xtype: 'htmleditor'} 
					]}
				]
			}
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
Ext.extend(ui.www_career.item_form , Ext.form.FormPanel, {
	lblTitle: 'Название вакансии',
	lblTitle_eng: 'Название вакансии  eng',
	lblFile: 'Изображение',
	lblAuthor: 'Должность',
	lblAuthor_eng: 'Должность англ',
	loadText: 'Загрузка данных формы',
	lblURI: 'URI',
	ttlTab1: 'Для Ru версии',
	ttlTab2: 'Для Eng версии',
	saveText: 'Сохранение...',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',
	bttFiles: 'Файлы',
	msgNotDefined:'Сохраните статью для начала',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером"
});
