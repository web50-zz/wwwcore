ui.www_article_tags.main = Ext.extend(ui.www_article_tags.grid, {
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
	reload: function(full){
		if (full == true){
			var bb = this.getBottomToolbar();
			bb.doLoad(0);
		}else{
			var bb = this.getBottomToolbar();
			bb.doLoad(bb.cursor);
		}
	},

	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			tbar: [
				'->', {iconCls: 'help', handler: function(){showHelp('www_article_tags')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_tags.main.superclass.constructor.call(this, config);
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
