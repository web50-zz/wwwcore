ui.text.configure_form = function(config){
	Ext.apply(this, config);
	this.Load = function(data){
		var f = this.getForm();
		f.setValues(Ext.decode(data));
	}
	var Save = function(){
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
	}.createDelegate(this);
	var Cancel = function(){
		this.fireEvent('cancelled');
	}.createDelegate(this);
	ui.text.configure_form.superclass.constructor.call(this, {
		frame: true, 
		defaults: {xtype: 'textfield', width: 100, anchor: '100%'},
		items: [
			new Ext.form.ComboBox({
				store: new Ext.data.JsonStore({url: 'di/text/available.json', fields: ['id', 'title'], autoLoad: true}),
				fieldLabel: this.labelType, hiddenName: '_sid',
				valueField: 'id', displayField: 'title', triggerAction: 'all', selectOnFocus: true, editable: false
			})
		],
		buttonAlign: 'right',
		buttons: [
			{iconCls: 'accept', text: this.bttSave, handler: Save},
			{iconCls: 'cancel', text: this.bttCancel, handler: Cancel}
		]
	});
	this.addEvents(
		"saved",
		"cancelled"
	);
	this.on({
		saved: function(data){
			this.getForm().setValues([{id: '_sid', value: data.id}]);
		},
		scope: this
	})
}
Ext.extend(ui.text.configure_form , Ext.form.FormPanel, {
	labelType: 'Текст',

	bttSave: 'Применить',
	bttCancel: 'Отмена',
});
