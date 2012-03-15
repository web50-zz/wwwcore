ui.structure.locale = function(face){
	switch(face){
		case 'site_tree':
			Ext.override(ui.structure.site_tree, {
				titleAdd: 'Add',
				titleEdit: 'Edit',

				bttAdd: 'Add',
				bttEdit: 'Edit',
				bttDelete: 'Delete',

				cnfrmTitle: "Confirm",
				cnfrmMsg: "Are you sure you want to delete this page?",

				msgLoading: "Data loading...",
				msgDeleteError: "Delete error.",
				msgServerError: "Server-side error.",
			});
		break;
		case 'node_form':
			Ext.override(ui.structure.node_form, {
				lblTitle: 'Title',
				lblVisible: 'Visible',
				lblName: 'Name',
				lblURI: 'uri',
				lblRedirect: 'Redirect',
				lblTheme: 'Theme',
				lblKeyw: 'META keywords',
				lblDescr: 'META description',
				lblTmpl: 'Template',

				loadText: 'Loading form data',

				saveText: 'Saving...',
				bttSave: 'Save',
				bttCancel: 'Cancel',
				errSaveText: 'Error while saving',
				errInputText: 'Correctly fill out all required fields',
				errConnectionText: "Error communicating with server"
			});
		break;
		case 'page_view_points':
			Ext.override(ui.structure.page_view_points, {
				clmnVPoint: "VP Num.",
				clmnTitle: "Title",
				clmnOrder: "Ord",
				clmnHasStrc: "Strc",
				clmnDHide: "Hide",
				clmnUIName: "Module",
				clmnUICall: "Call",
				clmnCache: "Use cache",
				clmnCacheTime: "Cache sec",

				titleAdd: 'Add block',
				titleEdit: 'Edit block',
				bttAdd: 'Add',
				bttEdit: 'Edit',
				bttDelete: 'Delete',
				bttLaunch: 'Launch',
				bttSetSave: 'Save set',
				bttSetLoad: 'Load set',
				cnfrmTitle: "Confirm",
				cnfrmMsg: "Are you sure you want to delete this block?",

				pagerEmptyMsg: 'Empty',
				pagerDisplayMsg: 'Records {0} - {1} of {2}'
			});
		break;
		case 'page_view_point_form':
			Ext.override(ui.structure.page_view_point_form, {
				lblViewPoint: 'View Point #',
				lblTitle: 'Title',
				lblHasStructure: 'Hase the structure',
				lblDeepHide: 'Hide in sub-page',
				lblCache: 'Use cache',
				lblCacheTime: 'Keep cache (sec)',
				lblOrder: 'Output order',
				lblModule: 'Module',
				lblCalls: 'Call',
				lblParams: 'Parameters',

				loadText: 'Loading...',
				saveText: 'Saving...',
				bttSave: 'Save',
				bttCancel: 'Cancel',
				errSaveText: 'Error while saving',
				errInputText: 'Correctly fill out all required fields',
				errConnectionText: "Error communicating with server"
			});
		break;
	}
}
