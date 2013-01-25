ui.www_article_comment.main_filter= Ext.extend(Ext.form.FormPanel, {
	ApplyFilter: function(){
		this.fireEvent('applied', this.getForm().getValues());
	},
	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		Ext.apply(this, {
			layout: 'form',
			border: false,
			frame: true,
			autoScroll: true,
			defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
			labelWidth: 100,
			labelAlign: 'right',
			items: [
				{fieldLabel: 'Пользователь', name: '_sname'},
				{fieldLabel: 'Статья', name: '_stitle'},
				{title: 'Дата комментария', xtype: 'fieldset', defaults: {xtype: 'datefield', width: 100, anchor: null, format: 'Y-m-d'}, items: [
					{fieldLabel: 'с', name: 'created_from'},
					{fieldLabel: 'по', name: 'created_to'}
				]}
			],
			buttonAlign: 'center',
			buttons: [
				{iconCls: 'clean', text: 'Применить', handler: this.ApplyFilter, scope: this},
				{iconCls: 'cancel', text: 'Сбросить', handler: function(){
					this.getForm().reset();
					this.ApplyFilter();
				}, scope: this}
			],
			keys: [
				{key: [Ext.EventObject.ENTER], handler: this.ApplyFilter, scope: this}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article_comment.main_filter.superclass.constructor.call(this, config);
	},

	/**
	 * To manually set default properties.
	 * 
	 * @param {Object} config Object containing all config options.
	 */
	configure: function(config)
	{
		config = config || {};
		Ext.apply(this, config, config);
	},

	/**
	 * @private
	 * @param {Object} o Object containing all options.
	 *
	 * Initializes the box by inserting into DOM.
	 */
	init: function(o)
	{
	}
});
