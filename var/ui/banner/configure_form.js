ui.banner.configure_form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		f.setValues(Ext.decode(data));
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			var config = {};
			var fData = f.getValues();
			for (var value in fData){
				if (!Ext.isEmpty(fData[value]))
					config[value] = fData[value];
			}
			this.fireEvent('saved', config);
		}
	},
	
	Cancel: function(){
		this.fireEvent('cancelled');
	},

	lblContent: 'Баннерная группа',
	bttSave: 'Применить',
	bttCancel: 'Отмена',
	errInputText: 'Корректно заполните все необходимые поля',

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		Ext.apply(this, {
			formWidth: 400,
			formHeight: 140
		});
		Ext.apply(this, {
			frame: true,
			border: false,
			labelAlign: 'right', 
			labelWidth: 140,
			defaults: {xtype: 'textfield', width: 150, anchor: '100%'},
			//layout: 'fit',
			items: [
				{fieldLabel: this.lblContent, hiddenName: '_spid', xtype: 'combo',
					store: new Ext.data.JsonStore({url: 'di/banner_group/available.json', fields: ['id', 'title'], autoLoad: true}),
					valueField: 'id', displayField: 'title',
					triggerAction: 'all', selectOnFocus: true, editable: false
				}
			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			],
			keys: [
				{key: [Ext.EventObject.ENTER], handler: this.Save, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.banner.configure_form.superclass.constructor.call(this, config);
		this.on({
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
	},

	reloadServices: function(data, id){
	}
});