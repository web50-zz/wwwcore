ui.structure.site_tree = function(config){
	var frmW = 400;
	var frmH = 300;
	this.pid = 0;
	this.loader = new Ext.tree.TreeLoader({url: 'di/structure/slice.json'});
	this.root = new Ext.tree.AsyncTreeNode({id: '0', draggable: false, expanded: true});
	this.rootVisible = false;
	this.autoScroll = true;
	this.loadMask = new Ext.LoadMask(Ext.getBody(), {msg: "Загрузка данных..."});
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
	var afterSave = function(isNew, formData, respData){
		if (isNew){
			var node = new Ext.tree.AsyncTreeNode({id: respData.id, text: formData.title, expanded: true});
			node.attributes.ui = formData.module;
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
		var f = new ui.structure.node_form();
		var w = new Ext.Window({title: this.addTitle, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: afterSave,
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(0, pid)});
	}.createDelegate(this);
	var Edit = function(id){
		var f = new ui.structure.node_form();
		var w = new Ext.Window({title: this.editTitle, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: afterSave,
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(id)});
	}.createDelegate(this);
	var Move = function(tree, node, oldParent, newParent, index){
		Ext.Ajax.request({
			url: 'di/structure/move.do',
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
		Ext.Ajax.request({
			url: 'di/structure/unset.do',
			params: {_sid: id},
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (d.success)
					this.fireEvent('deleted', id);
				else
					showError('Во время удаления возникли ошибки.');
			},
			scope: this
		})
	}.createDelegate(this);
	this.deleteNode = function(id, name){
		Ext.Msg.confirm('Подтверждение.', 'Вы действительно хотите удалить страницу "'+(name || id)+'"?', function(btn){if (btn == "yes") Delete(id)});
	}
	var onCmenu = function(node, e){
		var id = node.id;
		var cmenu = new Ext.menu.Menu({items: [
			{iconCls: 'add', text: 'Добавить', handler: Add.createDelegate(this, [id])},
			{iconCls: 'pencil', text: 'Редактировать', handler: Edit.createDelegate(this, [id])},
			{iconCls: 'delete', text: 'Удалить', handler: Delete.createDelegate(this, [id, node.text])}
		]});
		e.stopEvent();
		cmenu.showAt(e.getXY());
	}.createDelegate(this)
	var onNodeClick = function(node, e){
		this.fireEvent('changenode', node.id, node);
	}.createDelegate(this);
	ui.structure.site_tree.superclass.constructor.call(this,{
		enableDD: true,
		tbar: [
			{id: 'add', iconCls: 'add', text: 'Добавить', handler: Add.createDelegate(this, [0])},
			'->', {iconCls: 'help', handler: function(){showHelp('test')}}
		]
	});
	this.addEvents({
		loaded: true,
		changenode: true,
		removenode: true,
		changemodule: true,
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
Ext.extend(ui.structure.site_tree, Ext.tree.TreePanel, {
	bttAdd: "Добавить",
	bttEdit: "Изменить",
	bttDelete: "Удалить",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эту страницу?",

	addTitle: "Добавление страницы",
	editTitle: "Изменение страницы"
});
