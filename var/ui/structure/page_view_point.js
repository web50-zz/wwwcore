ui.structure.page_view_point = function(config){
	var appFace = 'main';
	Ext.apply(this, config, {});
	var onVPClose = function(page){
		
	}.createDelegate(this);
	this.initVP = function(vp, recreate){
		var appName = vp.ui_name;
		if (Ext.isEmpty(appName)) return;
                var appClass = 'ui.'+appName+'.'+appFace;
		var pageId = 'page-'+this.pid+'-'+vp.id;
		var config = {id: pageId, vpid: vp.id, title: '['+vp.view_point+'] '+vp.title, closable: true};
		vp.ui_configure = (vp.ui_configure) ? Ext.decode(vp.ui_configure) : {};
		var app = new App();
		app.on('apploaded', function(){
			var page = this.getComponent(pageId);
			if (page){
				if (recreate){
					var active = (this.getActiveTab() == page);
					this.remove(pageId);
					page = eval('new '+appClass+'(config, vp)');
					this.insert(0, page).on('close', this.delViewPoint, this);
					if (active) this.setActiveTab(pageId)
					this.fireEvent('view-point-inited');
				}
			}else{
				page = eval('new '+appClass+'(config, vp)');
				this.add(page).on({
					beforeclose: this.delViewPoint,
					scope: this
				});
				this.setActiveTab(pageId)
				this.fireEvent('view-point-inited');
			}
		}, this);
		app.on('deperror', function(){
			var page = this.getComponent(pageId);
			Ext.apply(config, {
				frame: true
			});
			if (page){
				if (recreate){
					var active = (this.getActiveTab() == page);
					this.remove(pageId);
					page = new Ext.Panel(config);
					this.insert(0, page).on('beforeclose', this.delViewPoint, this);
					if (active) this.setActiveTab(pageId)
					this.fireEvent('view-point-inited');
				}
			}else{
				page = new Ext.Panel(config);
				this.add(page).on({
					beforeclose: this.delViewPoint,
					scope: this
				});
				this.setActiveTab(pageId)
				this.fireEvent('view-point-inited');
			}
		}, this);
		app.Load(appName, appFace);
	}
	this.initConfiguration = function(cfg){
		var x = function(){
			this.un('view-point-inited', x, this);
			this.initConfiguration(cfg);
		};
		var vp = cfg.shift();
		if (vp){
			this.on('view-point-inited', x, this);
			this.initVP(vp);
		}
	}
	this.addViewPoint = function(conf){
		Ext.Ajax.request({
			url: 'di/ui_view_point/set.json',
			params: {pid: this.pid, ui_name: 'text'},
			disableCaching: true,
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (success && d.success)
					this.fireEvent('view-point-added', d.data);
				else
					showError("Ошибка при добавлении View Point");
			},
			scope: this
		});
	}
	this.cfgViewPoint = function(){
		var vp = this.getActiveTab();
		if (vp){
			var f = new ui.structure.page_view_point_form();
			var w = new Ext.Window({title: "Конфигурация View Point", modal: true, layout: 'fit', width: 640, height: 480, items: f});
			f.on({
				saved: function(isNew, form, resp){
					this.initVP(resp, true);
					w.destroy();
				},
				cancelled: function(){w.destroy()},
				scope: this
			});
			w.show(null, function(){f.Load(vp.vpid, this.pid)}, this);
		}else{
			showError("View Point NOT selected");
		}
	}
	this.delViewPoint = function(page){
		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				Ext.Ajax.request({
					url: 'di/ui_view_point/unset.json',
					params: {_sid: page.vpid},
					disableCaching: true,
					callback: function(options, success, response){
						var d = Ext.util.JSON.decode(response.responseText);
						if (success && d.success)
							this.fireEvent('view-point-deleted', page);
						else
							showError("Ошибка при удалении View Point");
					},
					scope: this
				});
			}
		}, this);
		return false;
	}
	ui.structure.page_view_point.superclass.constructor.call(this,{
		enableTabScroll: true
	});
	this.addEvents({
		'view-point-inited': true,
		'view-point-added': true,
		'view-point-deleted': true
	});
	this.on({
		'view-point-added': function(vp){
			this.initVP(vp);
		},
		'view-point-deleted': function(page){
			this.remove(page);
		},
		scope: this
	})
};
Ext.extend(ui.structure.page_view_point, Ext.TabPanel, {
	cnfrmTitle: 'Удаление ViewPoint',
	cnfrmMsg: 'Вы действительно хотите удалить ViewPoint'
});
