Ext.namespace("ui.registration");

ui.registration = function(conf){

	this.collectButtons = function(){
		Ext.each(Ext.query(".submbutt"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					this.handleSubmit();
				},
				scope: this
			})
		}, this);
		Ext.each(Ext.query(".startreg"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					this.initReg();
				},
				scope: this
			})
		}, this);

	}

	this.preparations = function()
	{
		if(Ext.fly('clnt_country'))
		{
			Ext.EventManager.on('clnt_country', 'change', this.refreshRegs);
			Ext.EventManager.on('clnt_region', 'change', this.alarm);
		}
	}
	this.alarm = function()
	{
//	 alert('eee');
	}

	this.refreshRegs = function(){
		Ext.Ajax.request({
			url: '/ui/registration/get_regs.do',
			form: 'regform',
			scope: this,
			success: function(response, opts) {
					Ext.DomHelper.useDom = true;
					Ext.fly('clnt_region').remove();
					var obj = Ext.decode(response.responseText);
					var dh = Ext.DomHelper; 
					dh.insertAfter('reg_wrap',obj);
			},
			 failure: function(response, opts) {
					 console.log(' Error ' + response.status);
			}
		});
	}

	this.handleSubmit = function(){
		Ext.each(Ext.query(".req",Ext.fly('.regform')), function(item, index, allItems){
			var el = Ext.get(item);
			if(el.getValue() == '')
			{
				var elt = Ext.fly(el.getAttribute('fldttlid'));
				el.replaceClass('field','field_error');
				elt.replaceClass('field_name','field_name_error');
			}
			else
			{
				var elt = Ext.fly(el.getAttribute('fldttlid'));
				el.replaceClass('field_error','field');
				elt.replaceClass('field_name_error','field_name');
			}
		}, this);
		Ext.Ajax.on('beforerequest', this.showSpinner, this);
		Ext.Ajax.on('requestcomplete', this.hideSpinner, this);
		Ext.Ajax.on('requestexception', this.hideSpinner, this);
		Ext.Ajax.request({
			url: '/ui/registration/register.do',
			form: 'regform',
			scope: this,
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				if(obj.code == '400')
				{
				//	Ext.fly('errortext').dom.innerHTML = obj.error;
					this.oo(obj.error);
				}
				else
				{
				//	Ext.fly('errortext').dom.innerHTML = '';
				}
				if(obj.code == '200')
				{
				//	Ext.fly('report').dom.innerHTML = obj.report;
					this.authism();
				}
				else
				{
				//	Ext.fly('report').dom.innerHTML = '';
				}
			},
			 failure: function(response, opts) {
					 console.log(' Error ' + response.status);
			}
		});
	}

	this.initReg = function(){
		Ext.Ajax.request({
			url: '/ui/registration/registration_form.do',
			scope:this,
			success: function(response,opts){
			Ext.fly('registrwrap').update(response.responseText);
			this.collectButtons();
			this.preparations();
			},
					failure: function(response,opts){
							alert('failure');
						}
				});
	}

	this.showSpinner =  function(){
		el = Ext.fly('registr').insertFirst({
		tag: 'div',
		cls: 'spinner',
		id: 'spinner',
		html: 'cоединение'
		});
		el.setLeft(document.documentElement.clientWidth/2);
		el.setTop(document.documentElement.clientHeight/2.5);
	}
	this.hideSpinner =  function(){
		Ext.fly('spinner').remove();
	}

	this.authism = function()
	{
		Ext.Ajax.request({
			url: '/ui/registration/register.do',
			scope:this,
			success: function(response,opts){
						window.location="";
						},
					failure: function(response,opts){
							alert('failure');
						},
					params: {user: Ext.fly('email').getValue(),secret: Ext.fly('passwd').getValue()}
				});
	}

	this.oo = function(text){
		AlertBox.show("Внимание", text, 'none', {dock: 'top'});
	}
}

Ext.onReady(function(){
	FRONTLOADER.load('/js/ux/alertbox/js/Ext.ux.AlertBox.js','alertbox');
	registration = new ui.registration();
	registration.collectButtons();
	registration.preparations();
});
