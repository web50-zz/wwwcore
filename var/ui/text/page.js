ui.text.main = function(config, vp){
	var formW = 800;
	var formH = 580;
	Ext.apply(this, config);
	this.loadPage = function(){
		if (vp.ui_configure._sid > 0){
			Ext.Ajax.request({
				url: 'di/text/get.json',
				params: {_sid: vp.ui_configure._sid},
				disableCaching: true,
				callback: function(options, success, response){
					var d = Ext.util.JSON.decode(response.responseText);
					if (success && d.success)
						this.body.update(d.data.content, false, function(){
							this.fireEvent('loaded');
						}.createDelegate(this));
					else
						showError(this.errLoadText);
				},
				scope: this
			});
		}
	}
	this.linkPage = function(id){
		Ext.Ajax.request({
			url: 'di/ui_view_point/apply.json',
			params: {_sid: vp.id, ui_configure: '{"_sid": "'+id+'"}'},
			disableCaching: true,
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (success && d.success)
					this.fireEvent('linked', id);
				else
					showError(this.errLinkText);
			},
			scope: this
		});
	}
	var Edit = function(){
		var id = (vp.ui_configure._sid || 0);
		var f = new ui.text.item_form();
		var w = new Ext.Window({title: this.editTitle, modal: true, layout: 'fit', width: formW, height: formH, items: f});
		f.on({
			saved: function(data){
				if (!vp.ui_configure._sid){
					vp.ui_configure._sid = data.id;
					this.linkPage(data.id);
				}
				this.loadPage();
			},
			cancelled: function(){w.destroy()},
			scope: this
		});
		w.show(null, function(){f.Load((vp.ui_configure._sid || 0))}, this);
	}.createDelegate(this);
	ui.text.main.superclass.constructor.call(this, {
		tbar: new Ext.Toolbar({items:[
			{iconCls: 'page_edit', text: 'Изменить', handler: Edit, scope: this}
		]}),
	});
	this.addEvents({
		loaded: true,
		linked: true,
		saved: true
	});
	this.on({
		render: this.loadPage,
		loaded: this.syncSize,
		scope: this
	});
};
Ext.extend(ui.text.main, Ext.Panel, {
	errLoadText: 'Ошибка при загрузке контента',
	errLinkText: 'Ошибка при линковке контента'
});
