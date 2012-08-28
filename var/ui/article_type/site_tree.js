ui.article_type.tree = function(config){
	var frmW = 600;
	var frmH = 300;
	this.pid = 0;
	this.loader = new Ext.tree.TreeLoader({url: 'di/article_type/slice.json'});
	this.root = new Ext.tree.AsyncTreeNode({id: '0', draggable: false, expanded: true});
	this.rootVisible = false;
	this.autoScroll = true;
	this.loadMask = new Ext.LoadMask(Ext.getBody(), {msg: this.msgLoad});
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
		var f = new ui.article_type.node_form();
		var w = new Ext.Window({title: this.addTitle, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: afterSave,
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(0, pid)});
	}.createDelegate(this);
	var Edit = function(id){
		var f = new ui.article_type.node_form();
		var w = new Ext.Window({title: this.editTitle, modal: true, layout: 'fit', width: frmW, height: frmH, items: f});
		f.on({
			saved: afterSave,
			cancelled: function(){w.destroy()}
		});
		w.show(null, function(){f.Load(id)});
	}.createDelegate(this);
	var Move = function(tree, node, oldParent, newParent, index){
		Ext.Ajax.request({
			url: 'di/article_type/move.do',
			params: {_sid: node.id, pid: newParent.id, ind: index},
			disableCaching: true,
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (d.success == false) showError(d.errors);
			},
			failure: function(result, request){
				showError(this.errIntranal);
			},
			scope: this
		});
	}.createDelegate(this);
	var Delete = function(id,name){
		var self = this;
		Ext.Msg.confirm(this.msgDelTitle, this.msgDelete+(name || id)+'"?', function(btn){if (btn == "yes") self.deleteNode(id)});
	}.createDelegate(this);

	this.deleteNode = function(id){
		Ext.Ajax.request({
			url: 'di/article_type/unset.do',
			params: {_sid: id},
			callback: function(options, success, response){
				var d = Ext.util.JSON.decode(response.responseText);
				if (d.success)
					this.fireEvent('deleted', id);
				else
					showError(this.errDel);
			},
			scope: this
		})

	}
	var onCmenu = function(node, e){
		var id = node.id;
		var cmenu = new Ext.menu.Menu({items: [
			{iconCls: 'add', text: this.textAdd, handler: Add.createDelegate(this, [id])},
			{iconCls: 'pencil', text: this.textEdit, handler: Edit.createDelegate(this, [id])},
			{iconCls: 'delete', text: this.textDel, handler: Delete.createDelegate(this, [id, node.text])}
		]});
		e.stopEvent();
		cmenu.showAt(e.getXY());
	}.createDelegate(this)
	var onNodeClick = function(node, e){
		this.fireEvent('changenode', node.id, node);
	}.createDelegate(this);
	ui.article_type.tree.superclass.constructor.call(this,{
		enableDD: true,
		tbar: [
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
Ext.extend(ui.article_type.tree, Ext.tree.TreePanel, {
	msgLoad: "Загрузка данных...",
	msgDelTitle: 'Подтверждение.',
	msgDelete: 'Вы действительно хотите удалить страницу "',
	bttAdd: "Добавить",
	bttEdit: "Изменить",
	bttDelete: "Удалить",
	errInternal: 'Внутренняя ошибка сервера',
	errDel: 'Во время удаления возникли ошибки.',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эту страницу?",
	textAdd: 'Добавить',
	textEdit:'Редактировать',
	textDel:'Удалить',
	addTitle: "Добавление",
	editTitle: "Изменение"
});
