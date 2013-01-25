ui.www_article_comment.main_grid = Ext.extend(ui.www_article_comment.grid, {
	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить этот комментарий?",
	Load: function(data){
		this.setParams({}, true);
	},
	Edit: function(){
		var row = this.getSelectionModel().getSelected();
		var id = row.get('id');
		var app = new App({waitMsg: 'Edit form loading'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_comment.item_form();
				var w = new Ext.Window({iconCls: this.iconCls, title: 'Редактирование', maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){this.store.reload()},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({id: id})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_comment', 'item_form');
	},
	Delete: function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.getStore().remove(record);
			}
		}, this);
	},
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			listeners: {
				rowcontextmenu: function(grid, rowIndex, e){
					grid.getSelectionModel().selectRow(rowIndex);
					var row = this.getSelectionModel().getSelected();
					var rcm = row.get('recommended');
					var items = new Array();
					items.push({iconCls: 'page_edit', text: 'Редактировать', handler: this.Edit, scope: this});
					items.push({iconCls: 'page_delete', text: 'Удалить', handler: this.Delete, scope: this});
					var cmenu = new Ext.menu.Menu({items: items});
					e.stopEvent();  
					cmenu.showAt(e.getXY());
				},
				scope: this
			}
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_comment.main_grid.superclass.constructor.call(this, config);
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
