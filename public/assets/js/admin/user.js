var modname = 'user';

function moveEditColumnToLeft(dataGrid) {
	dataGrid.columnOption('command:edit', {
		visibleIndex: -1,
		width: 80,
	});
}

var dataGrid = $('#gridContainer')
	.dxDataGrid({
		dataSource: store(modname),
		allowColumnReordering: true,
		allowColumnResizing: true,
		columnsAutoWidth: true,
		rowAlternationEnabled: true,
		wordWrapEnabled: true,
		showBorders: true,
		filterRow: { visible: true },
		filterPanel: { visible: true },
		headerFilter: { visible: true },
		searchPanel: {
			visible: true,
			width: 240,
			placeholder: 'Search...',
		},
		editing: {
			useIcons: true,
			mode: 'cell',
			allowAdding: true,
			allowUpdating: true,
			allowDeleting: true,
		},
		scrolling: {
			mode: 'virtual',
		},
		columns: [
			{
				dataField: 'company_name',
				sortOrder: 'asc',
			},
			{
				dataField: 'fullname',
			},
			{
				dataField: 'username',
			},
			{
				dataField: 'email',
			},
			// {
			// 	dataField: "passtxt",
			// },
			{
				dataField: 'isAdmin',
				dataType: 'boolean',
			},
		],
		export: {
			enabled: true,
			fileName: modname,
			excelFilterEnabled: true,
			allowExportSelectedData: true,
		},
		onContentReady: function (e) {
			moveEditColumnToLeft(e.component);
		},
		onToolbarPreparing: function (e) {
			dataGrid = e.component;

			e.toolbarOptions.items.unshift({
				location: 'after',
				widget: 'dxButton',
				options: {
					hint: 'Refresh Data',
					icon: 'refresh',
					onClick: function () {
						dataGrid.refresh();
					},
				},
			});
		},
	})
	.dxDataGrid('instance');
