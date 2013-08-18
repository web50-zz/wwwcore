ui.www_article_tags.selector = Ext.extend(Ext.Panel, {
	formWidth: 815,
	formHeight: 500,

	loadText: 'Загрузка данных формы',

	lblCategory: 'Категория',

	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",
	ttlAvailable:'Доступные',
	ttlEnabled:'Назначенные',

	Load: function(data){
	/*
		var f = this.getForm();
		f.load({
			url: 'di/www_article_tags/get.json',
			params: {_sid: data._sid},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				this.fireEvent("data_loaded", d.data, data.id);
			},
			scope:this
		});
		f.setValues(data);
		*/
		var article_id =  data._sitem_id;
		var i1 = this.i1;
		var i2 = this.i2;
		i1.store.baseParams = {item_id: article_id};
		i2.store.baseParams = {_sitem_id: article_id};
		i2.addEvents('interfaces_added', 'interfaces_removed');
		i2.on('interfaces_added', function(){
			i1.reload();
			i2.reload();
		});
		i2.on('interfaces_removed', function(){
			i1.reload();
			i2.reload();
		});

		var i1DTEl =  i1.getView().scroller.dom;
		var i1DT = new Ext.dd.DropTarget(i1DTEl , {
			ddGroup: 'enabled',
			notifyDrop: function(ddSource, e, data){
				var ss = ddSource.dragData.selections;
				var epids = new Array();
				for (el in ss){
					var iid = parseInt(ss[el].id);
					if (iid > 0) epids.push(iid);
				}
				if (epids.length > 0){
					Ext.Ajax.request({
						url: 'di/www_article_tags/remove_tags.do',
						params: {article_id: article_id, epids: epids.join(",")},
						disableCaching: true,
						callback: function(options, success, response){
							var d = Ext.util.JSON.decode(response.responseText);
							if (!(success && d.success))
								showError(this.errDoSync);
							else{
								i2.fireEvent('interfaces_removed');
							}
						},
						scope: this
					});
				}
				return true
			}
		});
		var i2DTEl =  i2.getView().scroller.dom;
		var i2DT = new Ext.dd.DropTarget(i2DTEl , {
			ddGroup: 'available',
			notifyDrop: function(ddSource, e, data){
				var ss = ddSource.dragData.selections;
				var epids = new Array();
				for (el in ss){
					var iid = parseInt(ss[el].id);
					if (iid > 0) epids.push(iid);
				}
				if (epids.length > 0){
					Ext.Ajax.request({
						url: 'di/www_article_tags/add_tags.do',
						params: {article_id: article_id, epids: epids.join(",")},
						disableCaching: true,
						callback: function(options, success, response){
							var d = Ext.util.JSON.decode(response.responseText);
							if (!(success && d.success))
								showError(this.errDoSync);
							else{
								i2.fireEvent('interfaces_added');
							}
						},
						scope: this
					});
				}
				return true
			}
		});
	},

	Save: function(){
	},
	
	Cancel: function(){
		this.fireEvent('cancelled');
	},

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		this.i1 = new ui.www_article_tag_types.main({title: this.ttlAvailable, flex: 1,
			width:	400,
			ddGroup: 'available',
			enableDragDrop: true});
		this.i2 = new ui.www_article_tags.main({title: this.ttlEnabled, flex: 1,
			ddGroup: 'enabled',
			width:	400,
			enableDragDrop: true});

		Ext.apply(this, {
			border: false,
			layout: 'hbox', flex: 1, layoutConfig: {align: 'stretch'},
			items: [this.i1, this.i2],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
			Ext.apply(this, config);
		ui.www_article_tags.selector.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
			},
			data_loaded: function(data, id){
			},
			select_category: function(){
			},
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
