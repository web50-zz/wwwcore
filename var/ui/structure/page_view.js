ui.structure.page_view = function(config){
	var appFace = 'main';
	Ext.apply(this, config, {});
	var loadPageConfiguration = function(pid, node){
		Ext.Ajax.request({
			url: 'di/ui_view_point/page_configuration.json',
			params: {_spid: pid},
			disableCaching: true,
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (success && d.success)
					this.fireEvent('cfgloaded', pid, node, d.data);
				else
					showError("Ошибка во время загрузки данных");
			},
			scope: this
		});
	}.createDelegate(this);
	var onCfgLoaded = function(pid, node, cfg){
		var pageId = 'page-'+pid;
		var page = this.getComponent(pageId);
		if (!page){
			page = new ui.structure.page_view_point({id: pageId, pid: pid, node: node});
			page.initConfiguration(cfg);
			this.insert(0, page);
		}
		this.getLayout().setActiveItem(pageId)
	}.createDelegate(this);
	this.newPage = function(pid, node){
		var pageId = 'page-'+pid;
		var page = this.getComponent(pageId);
		if (!page){
			loadPageConfiguration(pid, node);
		}else{
			this.getLayout().setActiveItem(pageId)
		}
	}
	this.delPage = function(pid){
		var page = this.getComponent('page-'+pid);
		if (page) page.destroy();
	}
	this.addBtt = function(){
		var p = this.getLayout().activeItem;
		if (p) p.addViewPoint();
		else showError("Page NOT selected");
	}
	this.cfgBtt = function(){
		var p = this.getLayout().activeItem;
		if (p) p.cfgViewPoint();
		else showError("Page NOT selected");
	}
	ui.structure.page_view.superclass.constructor.call(this,{
		layout: 'card',
		tbar: [
			{iconCls: 'add', text: 'Добавить', handler: this.addBtt, scope: this},
			{iconCls: 'page_white_wrench', text: 'Кофигурировать', handler: this.cfgBtt, scope: this},
			'->', {iconCls: 'help', handler: function(){showHelp('test')}}
		]
	});
	this.addEvents({
		cfgloaded: true
	});
	this.on({
		cfgloaded: onCfgLoaded,
		scope: this
	});
};
Ext.extend(ui.structure.page_view, Ext.Panel, {});
