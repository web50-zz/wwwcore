ui.www_response.main = Ext.extend(ui.www_response.tree, {
	bttAdd: "Ответить",
	bttEdit: "Редактировать",
	bttDelete: "Удалить",

	addTitle: "Добавление отзыва",
	editTitle: "Редактирование отзыва",
	deleteTitle: "Удаление отзыва",
	cnfrmMsg: "Вы хотите удалить данные отзыв и все ответы на него?",

	Add: function(pid){
		var app = new App({waitMsg: this.frmLoading});
		app.on({
			apploaded: function(){
				var f = new ui.www_response.response_form();
				var w = new Ext.Window({title: this.addTitle, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(isNew, respData, formData){this.fireEvent('node_saved', isNew, respData, formData)},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({id: 0, pid: pid})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_response', 'response_form');
	},

	Edit: function(id){
		var app = new App({waitMsg: this.frmLoading});
		app.on({
			apploaded: function(){
				var f = new ui.www_response.response_form();
				var w = new Ext.Window({title: this.editTitle, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(isNew, respData, formData){this.fireEvent('node_saved', isNew, respData, formData)},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({id: id, pid: 0})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_response', 'response_form');
	},

	Move: function(tree, node, oldParent, newParent, index){
		Ext.Ajax.request({
			url: 'di/www_response/move.do',
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
	},

	Delete: function(id, title){
		Ext.Msg.confirm(this.deleteTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes") Ext.Ajax.request({
					url: 'di/www_response/unset.do',
					params: {_sid: id},
					callback: function(options, success, response){
						var d = Ext.util.JSON.decode(response.responseText);
						if (d.success)
							this.fireEvent('node_deleted', id);
						else
							showError('Во время удаления возникли ошибки.');
					},
					scope: this
				});
		}, this);
	},

	createTitle: function(data){
		return data.name+' &lt;'+data.email+'&gt; ['+data.created_datetime+']';
	},

	constructor: function(config){
		config = config || {};
		Ext.apply(this, {
			enableDD: true,
			tbar: [
				{iconCls: 'comment_add', text: 'Добавить', handler: this.Add.createDelegate(this, [1])},
				'->', {iconCls: 'help', handler: function(){showHelp('www_response_grid')}}
			]
		});
		Ext.apply(this, config);
		ui.www_response.main.superclass.constructor.call(this, config);
		this.on({
			node_saved: function(isNew, data, formData){
				if (isNew){
					var node = new Ext.tree.AsyncTreeNode({id: data.id, text: this.createTitle(data), expanded: true});
					this.getNodeById(formData.pid).appendChild(node);
				}else{
					var node = this.getNodeById(data.id);
					node.setText(this.createTitle(data));
				}
			},
			node_deleted: function(id){
				var node = this.getNodeById(id);
				node.remove();
				this.fireEvent('removenode', id);
			},
			contextmenu: function(node, e){
				var id = node.id;
				var cmenu = new Ext.menu.Menu({items: [
					{iconCls: 'comments_add', text: this.bttAdd, handler: this.Add.createDelegate(this, [id])},
					{iconCls: 'comment_edit', text: this.bttEdit, handler: this.Edit.createDelegate(this, [id])},
					{iconCls: 'comment_delete', text: this.bttDelete, handler: this.Delete.createDelegate(this, [id, node.text])}
				]});
				e.stopEvent();
				cmenu.showAt(e.getXY());
			},
			movenode: this.Move,
			scope: this
		})
	},

	/**
	 * To manually set default properties.
	 * 
	 * @param {Object} config Object containing all config options.
	 */
	configure: function(config){
		config = config || {};
		Ext.apply(this, config, config);
	},

	/**
	 * @private
	 * @param {Object} o Object containing all options.
	 *
	 * Initializes the box by inserting into DOM.
	 */
	init: function(o){
	}
});
