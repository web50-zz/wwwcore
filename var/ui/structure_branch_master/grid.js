ui.structure_branch_master.grid = Ext.extend(Ext.grid.EditorGridPanel, {
	lblName: 'Имя',
	lblType: 'Тип',
	lblValue: 'Значение',
	lblCmmnt: 'Комментарий',
	titleImport: 'Импорт',
	addTitle: 'Редактировать',
	cnfrmTitle:'',
	cnfrmMsg:'Удалить?',
	formWidth: 500,
	formHeight: 150 ,

	loadMask: true,
	stripeRows: true,
	autoScroll: true,
	limit:20,
	selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
	store: new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
			api: {
				read: 'di/structure_branch_master/list.js',
				create: 'di/structure_branch_master/set.js',
				update: 'di/structure_branch_master/mset.js',
				destroy: 'di/structure_branch_master/unset.js'
			}
		}),
		reader: new Ext.data.JsonReader({
				totalProperty: 'total',
				successProperty: 'success',
				idProperty: 'id',
				root: 'records',
				messageProperty: 'errors'
			},
			[
				{name: 'id', type: 'int'}, 
				{name: 'created_datetime', type: 'date', dateFormat: 'Y-m-d H:i:s'}, 
				'str_creator_name', 
				'title'
			]
		),
		writer: new Ext.data.JsonWriter({
			encode: true,
			listful: true,
			writeAllFields: false
		}),
		remoteSort: true,
		sortInfo: {field: 'name', direction: 'ASC'}
	}),
	Add: function(){
		var f = new ui.structure_branch_master.item_form({pid:this.pid});
		var w = new Ext.Window({title: this.addTitle, maximizable: true, modal: true, layout: 'fit', width: this.formWidth, height: this.formHeight, items: f});
		f.on({
			saved: function(){this.store.reload()},
			cancelled: function(){w.destroy()},
			scope: this
		});
		w.show();
	},
	Import: function(){
		var f = new ui.structure_branch_master.item_import_form({pid:this.pid});
		var w = new Ext.Window({title: this.titleImport, maximizable: true, modal: true, layout: 'fit', width: this.formWidth, height: this.formHeight, items: f});
		f.on({
			saved: function(){this.store.reload()},
			cancelled: function(){w.destroy()},
			scope: this
		});
		w.show();
	},
	attachTo: function(mode){
		var id = this.getSelectionModel().getSelected().get('id');
		var toId =  this.attachToId;
		var type = 0;
		if(mode >0){
			type = mode;
		}
		var params = {sid: id,pid:toId, type:type};
		Ext.Ajax.request({
			url: 'di/structure_branch_master/attach.json',
			success: function(r){
					var d = Ext.util.JSON.decode(r.responseText);
					if (d.success == true){
						//this.store.reload();
						this.fireEvent('branchloaded',d);
					}else{
						showError(d.errors);
					}
				},
			failure: function(){},
			params: params,
			scope:this
		});
	},
	attachToFull: function(){
		this.attachTo(1);
	},
	attachToDown: function(){
		this.attachTo(2);
	},
	Export: function(){
		var id = this.getSelectionModel().getSelected().get('id');
		if(id>0){
			window.location.href = './di/structure_branch_master/export.do?_sid='+id;
		}
	},
	AddSingle:function(){
		var f = new ui.structure_branch_master.item_form({pid:this.pid});
		var w = new Ext.Window({title: this.addTitle, maximizable: true, modal: true, layout: 'fit', width: this.formWidth, height: this.formHeight, items: f});
		f.on({
			cancelled: function(){w.destroy()},
			scope: this
		});
		w.show();
	},
	Edit: function(){
		var id = this.getSelectionModel().getSelected().get('id');
		var f = new ui.structure_branch_master.item_form({});
		var w = new Ext.Window({title: this.editTitle, maximizable: true, modal: true, layout: 'fit', width: this.formWidth, height: this.formHeight, items: f});
		f.on({
			saved: function(){this.getStore().reload()},
			cancelled: function(){w.destroy()},
			scope: this
		});
		w.show(null, function(){f.Load({id:id})});
	},
	Delete: function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.store.remove(record);
			}
		}, this);
	},
	Load:function(input){
		var id = this.getSelectionModel().getSelected().get('id');
		if(input.type){
			var type =  input.type;
		}else{
			var type = 'add';
		}

		var pid = this.pid;
		if(pid>0 && id>0){
			var params = {id: id,pid:this.pid,type:type};
				Ext.Ajax.request({
					url: 'di/structure_branch_master/load.json',
					success: function(r){
							var d = Ext.util.JSON.decode(r.responseText);
							if (d.success == true){
								//this.store.reload();
							}else{
								showError(d.errors);
							}
						},
					failure: function(){},
					params: params,
					scope:this
					});
			this.fireEvent('vploaded');
		}
	},
	LoadClean:function(){
		this.Load({type:'loadclean'});
	},

	constructor: function(config)
	{
		config = config || {};
		Ext.apply(this, config, {
			columns: [
				{header:  'ID', id: 'id', dataIndex: 'id', sortable: true, width: 70},
				{header:  'Добавлено', id: 'created_datetime', dataIndex: 'created_datetime', sortable: true, width: 120, xtype: 'datecolumn', format: 'd M Y H:i'},
				{header:  'Добавил', id: 'str_creator_name', dataIndex: 'str_creator_name', sortable: true, width: 100},
				{header:  'Наименование', id: 'title', dataIndex: 'title', sortable: true, width: 250}
			]
		});
		ui.structure_branch_master.grid.superclass.constructor.call(this, config);
		
		this.on({
			vploaded:function(){},
			scope: this
		});
	},

	configure: function(config)
	{
		config = config || {};
		Ext.apply(this, config, config);
	},

	init: function(o)
	{
	}
});
