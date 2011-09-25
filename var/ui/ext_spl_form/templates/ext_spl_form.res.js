Ext.ns('Ext.ux');

/**
 * @class Ext.ux.SplForm
 * @extends Ext.util.Observable
 * Simple form combo with all interacts, and server side form template
 * @author 9* all.universe9@gmail.com 
 * @version 1.2
 * @singleton
 *
 */

Ext.ux.SplForm = Ext.extend(Ext.util.Observable, {

	wrapid: 'frmwrap',
	wrapcls : 'frmwrap',
	overid:'oeover',
	overlaycls:'oeoverlay',
	button_close_cls : '.closebt',
	button_submit_cls: '.sbbt',
	form_id: 'ffqf',
	form_cls: '.'+ this.form_id,
	params:{},
	path:'/js/ux/splform/',
	theme:'splform',
	dwidth:400,
	dheight:250,
	/**
     * @constructor
     */
	constructor: function(config)
	{
		config = config || {};
		Ext.apply(this, config);
		Ext.ux.SplForm.superclass.constructor.call(this, config);
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
		this.configure(o);
                if(o.content){
		          this.makeFrmWindow(o.content);
		          return;
		};
		// 9* preload css disabled this.loadCss(this.theme,'splformcss');
		Ext.Ajax.request({
			url: this.formUrl,
			scope: this,
			params: this.params,
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				if(obj.code == '400')
				{
					AlertBox.show(" ", obj.error, 'none', {dock: 'top'});
				}
				if(obj.code == '200')
				{
					this.makeFrmWindow(obj.form);
				}
			},
			 failure: function(response, opts) {
					 console.log(' Error ' + response.status);
			}
		});
	},

	makeFrmWindow : function(resp)
	{	
		if(this.frm == true){
			return;
		}
		var dh = Ext.DomHelper;
		var overlay={
			id:this.overid,
			tag:'div',
			cls:this.overlaycls
		};
		var over = dh.append(document.body,overlay);
		over.innerHTML = '&nbsp;';
		var spec ={
		id:this.wrapid,
		tag:'div',
		cls:this.wrapcls
		};
		var newel = dh.append(document.body,spec);

		newel.innerHTML = resp;
		var el = Ext.fly(this.wrapcls);
		if(this.width){
			el.setWidth(this.width);
		}
		else {
			el.setWidth(this.dwidth);
		}
		if(this.height){
			el.setHeight(this.height);
		}
		else {
			el.setHeight(this.dheight);
		}

		this.frm = true;	
		Ext.each(Ext.query(this.button_close_cls), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
				var el1 = Ext.fly(this.wrapid);
				if(el1){
					el1.remove();
				}
				var el2 = Ext.fly(this.overid);
				if(el2){
					el2.remove();
				}
				this.frm = false;
				this.height = false;
				this.width = false;
				},
				scope: this
			})
		},this);
		
		Ext.each(Ext.query(this.button_submit_cls), function(item, index, allItems){
			Ext.get(item).on({
				click: function(ev, el, opt){
					this.handleSubmit();
				},
				scope: this
			})
		}, this);
		this.afterMakeFrm();
	},
	afterMakeFrm: function()
	{
	},
// close window here or redirect to elsewhere
	authism : function()
	{
			var el1 = Ext.fly(this.wrapid);
			if(el1){
				el1.remove();
				}
			var el2 = Ext.fly(this.overid);
			if(el2){
				el2.remove();
			}
			this.frm = false;
			this.height = false;
			this.width = false;
//		window.location="";
	},
// 9* subit the box and handle errors. After submit page will be refreshed
	handleSubmit : function(){
		Ext.each(Ext.query(".req",Ext.fly(this.form_cls)), function(item, index, allItems){
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
			url: this.saveUrl,
			form: this.form_id,
			scope: this,
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				if(obj.code == '400')
				{
					AlertBox.show(" ", obj.error, 'none', {dock: 'top'});
				}
				if(obj.code == '200')
				{
					this.authism();
				}
			},
			 failure: function(response, opts) {
					 console.log(' Error ' + response.status);
			}
		});
	},

	showSpinner :  function(){
		el = Ext.fly(document.body).insertFirst({
		tag: 'div',
		cls: 'spinner',
		id:  'spinner',
		html: 'cоединение'
		});
		el.setLeft(document.documentElement.clientWidth/2);
		el.setTop(document.documentElement.clientHeight/2.5);
	},
	hideSpinner :  function(){
		Ext.fly('spinner').remove();
	},

	show: function(options)
	{
		options = options || {};
		this.init(options);
	},
	
// optionally auto preloads own css  Disabled this time	
	loadCss: function(theme,id){
		var d = document; 
		var caller = this.path+theme+'.css';
		if (!d.getElementById(id))
		{
			var h  = d.getElementsByTagName('head')[0];
			var l  = d.createElement('link');
			l.id   = id;
			l.rel  = 'stylesheet';
			l.type = 'text/css';
			l.href = caller;
			l.media = 'all';
			h.appendChild(l);
		}
	}


});

var SplForm = new Ext.ux.SplForm();
