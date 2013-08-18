ui.www_article_in_category.main = Ext.extend(ui.www_article_in_category.grid, {
	bttAdd: "Добавить",
	bttEdit: "Редактировать",
	bttDelete: "Удалить",

	addTitle: "Добавление",
	editTitle: "Редактирование",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить?",

	Load: function(data){
		this.setParams({}, true);
	},
	Add: function(){
		var app = new App({waitMsg: this.frmLoading});
		var pid = this.getKey();
		app.on({
			apploaded: function(){
				var f = new ui.www_article_type.category_selection();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: 400, height: 500, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					selected: function(data){this.addCategory(data)},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_type', 'category_selection');
	},
	multiSave: function(){
		this.store.save();
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
	addCategory: function(data){
		var pid = this.getKey();
		var params = {category_id: data.category_id, item_id: pid};
		Ext.Ajax.request({
			url: 'di/www_article_in_category/set.do',
			success: function(){this.store.reload()},
			failure: function(){},
			headers: {},
			params: params,
			scope: this
		});
	},
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			tbar: [
				{iconCls: 'note_add', text: this.bttAdd, handler: this.Add, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('www_article_in_category')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_in_category.main.superclass.constructor.call(this, config);
		this.on({
			rowcontextmenu: function(grid, rowIndex, e){
				grid.getSelectionModel().selectRow(rowIndex);
				var cmenu = new Ext.menu.Menu({items: [
					{iconCls: 'note_delete', text: this.bttDelete, handler: this.Delete, scope: this},
					'-'
				]});
				e.stopEvent();  
				cmenu.showAt(e.getXY());
			},
			dblrowclick: function(){},
			scope: this
		});
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
