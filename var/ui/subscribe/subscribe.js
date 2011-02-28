ui.subscribe.main = function(config){
	var loadMask = new Ext.LoadMask(Ext.getBody());
	Ext.apply(this, config);
	var group = new ui.subscribe.group({
		title: 'Рассылки',
		region: 'west',
		split: true,
		width: 300
	});
	var getSelectedGroup = function(){
		var s = group.getSelectionModel().getSelected();
		return (s) ? s.get('id') : 0;
	}
	var addUsers = function(){
		var gid = getSelectedGroup();
		if (gid > 0){
			var u = new ui.subscribe.subscriber_list();
			u.store.baseParams = {gid: gid, _sgid: 'null'};
			u.addEvents('users_added');
			u.on('users_added', u.reload);
			var w = new Ext.Window({title: "Choose users", modal: true, layout: 'fit', width: 640, height: 480, items: [u],
				tbar: [
					{text: this.bttAddUsers, iconCls: 'user_add', handler: function(){
						var sm = u.getSelectionModel();
						var ss = sm.getSelections();
						if (ss){
							var uids = new Array();
							for (el in ss){
								var uid = parseInt(ss[el].id);
								if (uid > 0) uids.push(uid);
							}
							if (uids.length > 0){
								Ext.Ajax.request({
									url: 'di/subscribe_user/add_users_to_group.do',
									params: {gid: gid, uids: uids.join(",")},
									disableCaching: true,
									callback: function(options, success, response){
										var d = Ext.util.JSON.decode(response.responseText);
										if (!(success && d.success))
											showError(this.errDoSync);
										else{
											this.fireEvent('users_added');
											u.fireEvent('users_added');
										}
									},
									scope: this
								});
							}
						}else{
							showError(this.errUserNotSelected);
						}
					}, scope: this},
					'->', {iconCls: 'help', handler: function(){showHelp('user-in-group')}}
				]
			});
			w.show();
		}else{
			showError(this.errGroupNotSelected);
		}
	}.createDelegate(this);
	var user = new ui.subscribe.subscriber_list({
		title: 'Пользователи',
		region: 'center',
		tbar: [{text: this.bttAddUsers, iconCls: 'user_add', handler: addUsers}]
	});
	var delUsers = function(){
		var gid = getSelectedGroup();
		if (gid > 0){
			var ss = user.getSelectionModel().getSelections();
			if (ss){
				var uids = new Array();
				for (el in ss){
					var uid = parseInt(ss[el].id);
					if (uid > 0) uids.push(uid);
				}
				if (uids.length > 0){
					Ext.Ajax.request({
						url: 'di/subscribe_user/remove_users_from_group.do',
						params: {gid: gid, uids: uids.join(",")},
						disableCaching: true,
						callback: function(options, success, response){
							var d = Ext.util.JSON.decode(response.responseText);
							if (!(success && d.success))
								showError(this.errDoSync);
							else
								this.fireEvent('users_deleted');
						},
						scope: this
					});
				}
			}else{
				showError(this.errUserNotSelected);
			}
		}
	}.createDelegate(this);

	var accountsList = function(){
	var aclist = new ui.subscribe.accounts_list({region: 'west', split: true, width: 200});
	var w = new Ext.Window({title: "Аккаунты", 
					modal: true, 
					layout: 'fit', 
					width: 800, 
					height: 600,
					items:[aclist]
	});
		w.show();
	}.createDelegate(this);


	user.getTopToolbar().add({text: this.bttRemoveUsers, iconCls: 'user_add', handler: delUsers});
	user.store.baseParams = {gid: 0, _ngid: 'null'};
	group.on({
		rowclick: function(grid, rowIndex, ev){
			user.store.baseParams = {gid: this.getSelectionModel().getSelected().get('id'), _ngid: 'null'};
			user.reload(true);
		}
	});
	ui.subscribe.main.superclass.constructor.call(this, {
		layout: 'border',
		tbar: new Ext.Toolbar({items:[
				{text: this.menuTitleUsers, iconCls: 'package_go',handler: accountsList}
		]}),
		items: [group, user]
	});
	this.addEvents(
		'users_added',
		'users_deleted'
	);
	this.on({
		users_added: function(){user.reload()},
		users_deleted: function(){user.reload()},
		scope: this
	});
};
Ext.extend(ui.subscribe.main, Ext.Panel, {
	menuTitleMain: 'Операции',
	menuTitleUsers: 'Аккаунты',
	menuTitleMessages: 'Сообщения',
	bttAddUsers: 'Добавить в рассылку', 
	bttRemoveUsers: 'Удалить из рассылки',
	bttAddMessage:'Создать сообщение',
	errDoSync: 'Error while modules syncronization',
	errGroupNotSelected: 'The group not selected',
	errUserNotSelected: 'The user(s) not selected'
});
