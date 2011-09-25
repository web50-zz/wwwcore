Ext.namespace("ui.profile");

ui.profile = function(conf){

	this.collectButtons = function(){
		Ext.each(Ext.query(".cpd"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
						this.getPform();
				},
				scope: this
			})
		}, this);
		Ext.each(Ext.query(".cpda"), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
						var id  = el.getAttribute('cid');
						this.getOrder(id);
				},
				scope: this
			})
		}, this);


	};

	this.init = function(){
		Ext.each(Ext.query(".ld"), function(item, index, allItems){
				Ext.get(item).on({
					click: function(ev, el, opt){
						var el = Ext.fly('pd');
						if(!el)
						{
							this.getPinfo();
						}
					},
					scope: this
				})
			}, this);
			Ext.each(Ext.query(".sec"), function(item, index, allItems){
				Ext.get(item).on({
					click: function(ev, el, opt){
							this.getSecFrm();
					},
					scope: this
				})
			}, this);
			Ext.each(Ext.query(".zak"), function(item, index, allItems){
			Ext.get(item).on({
					click: function(ev, el, opt){
						var el = Ext.fly('od');
						if(!el)
						{
							this.getZinfo();
						}
					},
					scope: this
				})
			}, this);
		this.collectButtons();
	}

	
	this.getPform = function(){
		SplForm.show({formUrl:'/ui/profile/get_pform.do',saveUrl:'/ui/profile/save_pform.do',width:500,
				height:600,
				afterMakeFrm: this.afterLoadPform
		});
	}

	this.afterLoadPform = function()
	{
		if(Ext.fly('clnt_country'))
		{
			Ext.EventManager.on('clnt_country', 'change', refreshRegs);
		}
		
	}

	var refreshRegs = function()
	{
		Ext.Ajax.request({
			url: '/ui/profile/get_regs.do',
			form: 'ffqf',
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

	this.getOrder = function(id){
		SplForm.show({formUrl:'/ui/profile/get_order.do',saveUrl:'',params:{_sid:id},width:500,height:500});
	}


	this.getPinfo = function(){
			this.getData({url:'/ui/profile/client_info_part.get',current:'od'});	
		}

	this.getZinfo = function(){
			this.getData({url:'/ui/profile/client_orders_part.get',current:'pd'});	
		}

	this.getData = function(u)
	{
		Ext.Ajax.request({
			url: u.url,
			scope: this,
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				if(obj.code == '400'){
					AlertBox.show("Внимание", obj.report, 'none', {dock: 'top'});
				}
				if(obj.code == '200'){	
					var el = Ext.fly(u.current);
					el.remove();
					Ext.DomHelper.insertFirst('pcontent',obj.payload);
					this.collectButtons();
				}
			},
			 failure: function(response, opts) {
					 console.log(' Error ' + response.status);
			}
		});

	}

	this.getSecFrm = function(email)
	{
		SplForm.show({formUrl:'/ui/profile/get_passform.do',saveUrl:'/ui/profile/save_passform.do'});
	}
}

Ext.onReady(function(){
	this.ui_profile = new ui.profile();
	this.ui_profile.init();
});


