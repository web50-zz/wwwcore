ui.www_slide.group = function(config){
	this.pid = 0;
	Ext.apply(this, config, {});
	this.reload = function(id){
		if (id){
			var node = this.getNodeById(id);
			if (node){
				if (!node.expanded)
					node.expand()
				else
					node.reload();
			}
		}else if (this.root.rendered == true)
			this.root.reload();
	}
	var afterSave = function(isNew, respData, formData){
		if (isNew){
			var node = new Ext.tree.AsyncTreeNode({id: respData.id, text: formData.title, expanded: true});
			this.getNodeById(formData.pid).appendChild(node);
		}else{
			var node = this.getNodeById(respData.id);
			if (node.attributes.ui != formData.module){
				node.attributes.ui = formData.module;
				this.fireEvent('changemodule', respData.id, node);
			}
			node.setText(formData.title);
		}
	}.createDelegate(this);
	var afterDelete = function(id){
		var node = this.getNodeById(id);
		node.remove();
		this.fireEvent('removenode', id);
	}.createDelegate(this);
	var Add = function(pid){
		var app = new App({waitMsg: 'Edit form loading'});
		app.on({
			apploaded: function(){
				var f = new ui.www_slide.group_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: afterSave,
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({pid: pid})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_slide', 'group_form');
	}.createDelegate(this);
	var Edit = function(id){
		var app = new App({waitMsg: 'Edit form loading'});
		app.on({
			apploaded: function(){
				var f = new ui.www_slide.group_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleEdit, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: afterSave,
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({id: id})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_slide', 'group_form');
	}.createDelegate(this);
	var Move = function(tree, node, oldParent, newParent, index){
		Ext.Ajax.request({
			url: 'di/www_slide_group/move.do',
			params: {_sid: node.id, pid: newParent.id, ind: index},
			disableCaching: true,
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (d.success == false) showError(d.errors);
			},
			failure: function(result, request){
				showError('Внутренняя ошибка сервера');
			},
			scope: this
		});
	}.createDelegate(this);
	var Delete = function(id){
		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				Ext.Ajax.request({
					url: 'di/www_slide_group/unset.do',
					params: {_sid: id},
					callback: function(options, success, response){
						var d = Ext.util.JSON.decode(response.responseText);
						if (d.success)
							this.fireEvent('deleted', id);
						else
							showError('Во время удаления возникли ошибки.');
					},
					scope: this
				});
			}
		}, this);
	}.createDelegate(this);
	this.deleteNode = function(id, name){
		Ext.Msg.confirm('Подтверждение.', 'Вы действительно хотите удалить страницу "'+(name || id)+'"?', function(btn){if (btn == "yes") Delete(id)});
	}
	var onCmenu = function(node, e){
		var id = node.id;
		var cmenu = new Ext.menu.Menu({items: [
			{iconCls: 'add', text: this.bttAdd, handler: Add.createDelegate(this, [id])},
			{iconCls: 'pencil', text: this.bttEdit, handler: Edit.createDelegate(this, [id])},
			{iconCls: 'delete', text: this.bttDelete, handler: Delete.createDelegate(this, [id, node.text])}
		]});
		e.stopEvent();
		cmenu.showAt(e.getXY());
	}.createDelegate(this)
	var onNodeClick = function(node, e){
		this.fireEvent('changenode', node.id, node);
	}.createDelegate(this);
	ui.www_slide.group.superclass.constructor.call(this,{
		loader: new Ext.tree.TreeLoader({url: 'di/www_slide_group/slice.json'}),
		root: new Ext.tree.AsyncTreeNode({id: '1', draggable: false, expanded: true}),
		rootVisible: false,
		autoScroll: true,
		loadMask: new Ext.LoadMask(Ext.getBody(), {msg: "Загрузка данных..."}),
		enableDD: true,
		tbar: [
			{id: 'add', iconCls: 'add', text: 'Добавить', handler: Add.createDelegate(this, [1])},
			'->', {iconCls: 'help', handler: function(){showHelp('www_slide-group')}}
		]
	});
	this.addEvents({
		loaded: true,
		changenode: true,
		removenode: true,
		saved: true,
		deleted: true
	});
	this.on({
		contextmenu: onCmenu,
		movenode: Move,
		click: onNodeClick,
		saved: afterSave,
		deleted: afterDelete,
		scope: this
	});
};
Ext.extend(ui.www_slide.group, Ext.tree.TreePanel, {
	formWidth: 500,
	formHeight: 400,

	bttAdd: "Добавить",
	bttEdit: "Изменить",
	bttDelete: "Удалить",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эту страницу?",

	addTitle: "Добавление страницы",
	editTitle: "Изменение страницы",

	cnfrmTitle: "Удаление слайдера",
	cnfrmMsg: "Данный слайдер и всё его содержимое будет удалено. Вы уверены?"
});
