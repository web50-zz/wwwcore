ui.article.main = function(config){
	var self = this;
	this.cid = 0;
	this.pid = 0;
	this.autoScroll = true;
	Ext.apply(this, config);
	var Save = function(data){
		Ext.Ajax.request({
			url: 'di/article/set.do',
			params: data,
			disableCaching: true,
			callback: function(options, success, response){
				if (success)
					self.fireEvent('saved');
				else
					showError("Ошибка сохранения");
			}
		});
	}
	var Submit = function(f){
		f.submit({
			url: 'di/article/set.do',
			waitMsg: 'Сохранение...',
			success: function(form, action){
				var d = Ext.util.JSON.decode(action.response.responseText);
				if (d.success){
					self.cid = d.data.id;
					self.fireEvent('saved');
				}else
					showError(d.errors);
			},
			failure: function(form, response){
				showError('Ошибка сохранения.');
			}
		});
	}
	var getForm = function(data){
		return new Ext.FormPanel({
			frame: true, 
			defaults: {xtype: 'textfield'},
			buttonAlign: 'right',
			items: [
				{name: '_sid', inputType: 'hidden', value: self.cid},
				{name: 'pid', inputType: 'hidden', value: self.pid},
				{fieldLabel: 'Название', name: 'title', width: '98%', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
				{fieldLabel: 'Дата', name: 'release_date', format: 'Y-m-d', allowBlank: false, xtype: 'datefield'},
				{fieldLabel: 'Источник', name: 'source', width: '98%', maxLength: 64, maxLengthText: 'Не больше 64 символов'},
				{fieldLabel: 'Автор', name: 'author', width: '98%', maxLength: 255, maxLengthText: 'Не больше 255 символов'},
				{hideLabel: true, name: 'content', xtype: 'ckeditor', CKConfig: {
					height: 260,
					filebrowserImageBrowseUrl: 'ui/file_manager/browser.html'
				}}
			]
		});
		
	}
	this.editPage = function(){
		var fp = getForm();
		var w = new Ext.Window({title: 'Редактирование', modal: true, layout: 'form', width: 800, height: 620, items: fp});
		var submit = function(){
			var f = fp.getForm();
			if (f.isValid()) Submit(f);
		}
		fp.addButton({iconCls: 'disk', text: 'Сохранить', handler: submit, scope: this});
		fp.addButton({iconCls: 'cancel', text: 'Отмена', handler: function(){w.destroy()}});
		this.on('saved', function(){w.destroy()}, this, {single: true});
		w.show(null, function(){
			fp.getForm().load({
				url: 'di/article/item.json',
				params: {_sid: this.cid},
				waitMsg: 'Загрузка...'
			});
		}, this);
	}
	this.savePage = function(data){
		Save(data);
	}
	this.loadPage = function(){
		Ext.Ajax.request({
			url: 'di/article/get.json',
			params: {_spid: this.pid},
			disableCaching: true,
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (success && d.success){
					if (d.data){
						this.cid = d.data.id;
						this.body.update(d.data.content, false, function(){
							this.syncSize();
							this.fireEvent('loaded');
						}.createDelegate(this));
					}
				}else
					showError("Ошибка во время загрузки данных");
			},
			scope: this
		});
	}
	ui.article.main.superclass.constructor.call(this, {
		tbar: new Ext.Toolbar({items:[
			{iconCls: 'page_edit', text: 'Изменить', handler: this.editPage, scope: this}
		]}),
	});
	this.addEvents({
		loaded: true,
		saved: true
	});
	this.on({
		render: this.loadPage,
		saved: this.loadPage,
		scope: this
	});
};
Ext.extend(ui.article.main, Ext.Panel, {});
