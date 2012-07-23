ui.banner.locale = function(face){
	switch(face){
		case 'main':
			Ext.override(ui.banner.main, {
			});
		break;
		case 'configure_form':
			Ext.override(ui.banner.configure_form, {
				bttSave: 'Apply',
				bttCancel: 'Cancel',
				errInputText: 'Correctly fill out all required fields'
			});
		break;
	}
}
