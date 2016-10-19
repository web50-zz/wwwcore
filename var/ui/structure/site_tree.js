ui.structure.site_tree = Ext.extend(Ext.tree.TreePanel, {
	titleAdd: "Добавление страницы",
	titleEdit: "Изменение страницы",
	titlePresets: 'Пресеты',

	bttAdd: "Добавить",
	bttEdit: "Изменить",
	bttDelete: "Удалить",
	bttSaveBranch:'Сохранить ветку',
	bttLoadBranch:'Загрузить ветку',
	bttMaster: 'Конфиги',
	bttSearchReindex :'Переиндексировать поиск',
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эту страницу?",

	msgLoading: "Загрузка данных...",
	msgDeleteError: "Во время удаления возникли ошибки.",
	msgServerError: "Внутренняя ошибка сервера",
	msgRestrictedError:'Страницу запрещено удалять',

	operation: {
		Reload: function(s,id){
			if (id){
				var node = s.getNodeById(id);
				if (node){
					node.expand()
					if (!node.expanded){
				//9* expand operation  seems to be necessary anyway  		node.expand()
					}else{
						node.reload();
					}
					s.fireEvent('changenode', node.id, node);
				}
			}else if (s.root.rendered == true){
				s.root.reload();
			}
		},
		Saved: function(isNew, formData, respData){
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
		},
		Add: function(pid){
			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure.node_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: this.operation.Saved,
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({id: 0, pid: pid})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure', 'node_form');
		},
		Edit: function(id){
			var app = new App({waitMsg: 'Edit form loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure.node_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titleEdit, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
					f.on({
						data_saved: this.operation.Saved,
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.Load({id: id})});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure', 'node_form');
		},
		Move: function(tree, node, oldParent, newParent, index){
			Ext.Ajax.request({
				url: 'di/structure/move.do',
				params: {_sid: node.id, pid: newParent.id, ind: index},
				disableCaching: true,
				callback: function(options, success, response){
					var d = Ext.util.JSON.decode(response.responseText);
					if (d.success == false) showError(d.errors);
				},
				failure: function(result, request){
					showError(this.msgServerError);
				},
				scope: this
			});
		},
		Delete: function(id){
			if(id == 1){
				showError(this.msgRestrictedError);
				return;
			}
			Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
				if (btn == "yes"){
					Ext.Ajax.request({
						url: 'di/structure/unset.do',
						params: {_sid: id},
						callback: function(options, success, response){
							var d = Ext.util.JSON.decode(response.responseText);
							if (d.success){
								this.fireEvent('deleted', id);
								this.fireEvent('changenode', id);
							}else{
								showError(this.msgDeleteError);
							}
						},
						scope: this
					})
				}
			}, this);
		},
		searchReindex: function(){
			Ext.Msg.confirm(this.cnfrmTitle, 'Переиндексировать таблицу поиска? Это может занять некоторое время.', function(btn){
				if (btn == "yes"){
					Ext.Ajax.request({
						url: 'di/search/collect.do',
						params: {_sid: id},
						callback: function(options, success, response){
							var d = Ext.util.JSON.decode(response.responseText);
							if (d.success){
								Ext.Msg.alert('',d.msg);
							}else{
								showError('Проблема индексации');
							}
						},
						scope: this
					})
				}
			}, this);
		},

		Master: function(){
			var app = new App({waitMsg: 'Presets grid loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure_branch_master.main();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titlePresets, maximizable: true, modal: true, layout: 'fit', width: 600, height: 500, items: f});
					f.on({
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure_branch_master', 'main');
		}, 
		saveBranch: function(id){
			var app = new App({waitMsg: 'Presets grid loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure_branch_master.item_form();
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titlePresets, maximizable: true, modal: true, layout: 'fit', width: 400, height: 100, items: f});
					f.on({
						saved: function(){w.destroy()},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){f.setPid(id)});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure_branch_master', 'main');
		}, 
		loadBranch: function(id){
			var app = new App({waitMsg: 'Presets grid loading'});
			app.on({
				apploaded: function(){
					var f = new ui.structure_branch_master.selector();
					f.attachToId = id;
					var w = new Ext.Window({iconCls: this.iconCls, title: this.titlePresets, maximizable: true, modal: true, layout: 'fit', width: 600, height: 500, items: f});
					f.on({
						branchLoaded: function(data){
									w.destroy();
									this.operation.Reload(this,id);
									var node = this.getNodeById(id);
									if(data.sync == true)
									{
										node.setText(data.root_title);
									}
								},
						cancelled: function(){w.destroy()},
						scope: this
					});
					w.show(null, function(){});
				},
				apperror: showError,
				scope: this
			});
			app.Load('structure_branch_master', 'selector');
		} 


	},
	constructor: function(config){
		config = config || {};
		Ext.apply(this, {
			loader: new Ext.tree.TreeLoader({url: 'di/structure/slice.json'}),
			root: new Ext.tree.AsyncTreeNode({id: '0', draggable: false, expanded: true}),
			rootVisible: false,
			autoScroll: true,
			loadMask: new Ext.LoadMask(Ext.getBody(), {msg: this.msgLoading}),
			enableDD: true,
			tbar: [
			//	{id: 'add', iconCls: 'add', text: this.bttAdd, handler: this.operation.Add.createDelegate(this, [0])},
				{id: 'master', iconCls: 'add', text: this.bttMaster, handler: this.operation.Master.createDelegate(this, [0])},
				{id: 'rearchreindex', iconCls: 'add', text: this.bttSearchReindex, handler: this.operation.searchReindex.createDelegate(this, [0])},
				'->', {iconCls: 'help', handler: function(){showHelp('structure-tree')}}
			]
		});
		Ext.apply(this, config);
		ui.structure.site_tree.superclass.constructor.call(this, config);
		this.init();
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
		this.on({
			click: function(node, e){
				this.fireEvent('changenode', node.id, node);
			},
			contextmenu: function(node, e){
				var id = node.id;
				var cmenu = new Ext.menu.Menu({items: [
					{iconCls: 'add', text: this.bttAdd, handler: this.operation.Add.createDelegate(this, [id])},
					{iconCls: 'pencil', text: this.bttEdit, handler: this.operation.Edit.createDelegate(this, [id])},
					{iconCls: 'pencil', text: this.bttSaveBranch, handler: this.operation.saveBranch.createDelegate(this, [id])},
					{iconCls: 'pencil', text: this.bttLoadBranch, handler: this.operation.loadBranch.createDelegate(this, [id])},
					{iconCls: 'delete', text: this.bttDelete, handler: this.operation.Delete.createDelegate(this, [id, node.text])}
				]});
				e.stopEvent();
				cmenu.showAt(e.getXY());
			},
			movenode: this.operation.Move,
			deleted: function(id){
				var node = this.getNodeById(id);
				node.remove();
				this.fireEvent('removenode', id);
			},
			scope: this
		});
	}
});
