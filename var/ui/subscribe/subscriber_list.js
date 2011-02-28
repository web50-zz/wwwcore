ui.subscribe.subscriber_list = function(config){
	Ext.apply(this, config);
	var proxy = new Ext.data.HttpProxy({
		api: {
			read: 'di/subscribe_accounts/user_in_group.json'
		}
	});
	// Typical JsonReader.  Notice additional meta-data params for defining the core attributes of your json-response
	var reader = new Ext.data.JsonReader({
			totalProperty: 'total',
			successProperty: 'success',
			idProperty: 'id',
			root: 'records',
			messageProperty: 'errors'
		},
		[{name: 'id', type: 'int'}, 'email', 'name']
	);
	// The data store
	this.store = new Ext.data.Store({
		proxy: proxy,
		reader: reader
	});
	this.reload = function(full){
		if (full == true){
			var bb = this.getBottomToolbar();
			bb.doLoad(0);
		}else{
			var bb = this.getBottomToolbar();
			bb.doLoad(bb.cursor);
		}
	};
	ui.subscribe.subscriber_list.superclass.constructor.call(this,{
		columns: columns = [
			{id: 'id', dataIndex: 'id', header: 'ID', align: 'right', width: 50},
			{id: 'email', dataIndex: 'email', header: this.labelEmail, width: 150},
			{id: 'name', dataIndex: 'name', header:  this.labelName}
		],
		loadMask: true,
		autoExpandColumn: 'name',
		bbar: new Ext.PagingToolbar({
			pageSize: this.limit,
			store: this.store,
			displayInfo: true,
			displayMsg: this.pagerDisplayMsg,
			emptyMsg: this.pagerEmptyMsg
		})
	});
	this.on({
		render: function(){this.store.load({params:{start:0, limit: this.limit}})},
		scope: this
	})
};
Ext.extend(ui.subscribe.subscriber_list, Ext.grid.GridPanel, {
	limit: 20,

	labelName: 'Имя',
	labelEmail: 'email',

	pagerEmptyMsg: 'Нет пользователей',
	pagerDisplayMsg: 'Записи с {0} по {1}. Всего: {2}'
});
