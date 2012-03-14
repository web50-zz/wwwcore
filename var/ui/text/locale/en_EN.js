ui.text.locale = function(face){
	switch(face){
		case 'main':
			Ext.override(ui.text.main, {
				titleAdd: 'Add',
				titleEdit: 'Edit',
				bttAdd: 'Add',
				bttEdit: 'Edit',
				bttDelete: 'Delete',
				cnfrmTitle: "Confirm",
				cnfrmMsg: "Are you sure you want to delete this record?",

				srchSelTitle: "Title",
				srchSelContent: "Content",
				srchBttSearch: "Search",
				srchBttCancel: "Reset",
				srchTxtFind: "Find: "
			});
		break;
		case 'grid':
			Ext.override(ui.text.grid, {
				clmnTitle: "Title",
				pagerEmptyMsg: 'Empty',
				pagerDisplayMsg: 'Records {0} - {1} of {2}'
			});
		break;
		case 'item_form':
			Ext.override(ui.text.item_form, {
				lblTitle: 'Title',

				loadText: 'Loading form data',

				saveText: 'Saving...',
				bttSave: 'Save',
				bttCancel: 'Cancel',
				errSaveText: 'Error while saving',
				errInputText: 'Correctly fill out all required fields',
				errConnectionText: "Error communicating with server"
			});
		break;
		case 'configure_form':
			Ext.override(ui.text.configure_form, {
				lblContent: 'Content',
				lblTitleHide: 'Hide title',
				bttSave: 'Apply',
				bttCancel: 'Cancel',
				errInputText: 'Correctly fill out all required fields'
			});
		break;
	}
}
