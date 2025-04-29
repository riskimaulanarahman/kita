var modname = 'produkrequest';
var modelclass = 'Produk';
var popupmode;

function moveEditColumnToLeft(dataGrid) {
	dataGrid.columnOption('command:edit', {
		visibleIndex: -1,
		width: 80,
	});
}

var dataGrid = $('#gridContainer')
	.dxDataGrid({
		dataSource: store(modname),
		// keyExpr: 'id',
		// parentIdExpr: 'parentID',
		allowColumnReordering: true,
		allowColumnResizing: true,
		columnHidingEnabled: true,
		rowAlternationEnabled: false,
		wordWrapEnabled: true,
		autoExpandAll: true,
		showBorders: true,
		filterRow: { visible: true },
		filterPanel: { visible: true },
		headerFilter: { visible: true },
		// selection: {
		//     mode: 'multiple',
		//     recursive: true,
		// },
		searchPanel: {
			visible: true,
			width: 240,
			placeholder: 'Search...',
		},
		editing: {
			useIcons: true,
			mode: 'popup',
			allowAdding: false,
			allowUpdating: false,
			allowDeleting: true,
		},
		scrolling: {
			mode: 'virtual',
		},
		pager: {
			visible: false,
			showInfo: true,
		},
		columns: [
			{
				caption: 'Action',
				width: 140,
				cellTemplate: function (container, options) {
					var isMine = options.data.isMine;
					var isPendingOnMe = options.data.isPendingOnMe;
					var reqid = options.data.id;
					var reqstatus = options.data.requestStatus;
					var mode =
						reqstatus == 0 || (reqstatus == 2 && isMine == 1)
							? 'edit'
							: reqstatus == 1 &&
							  ((isMine == 0 && isPendingOnMe == 1) ||
									(isMine == 1 && isPendingOnMe == 1))
							? 'approval'
							: 'view';
					var arrColor = [
						'btn-secondary',
						mode == 'approval' && reqstatus == 1 ? 'btn-danger' : 'btn-primary',
						'btn-warning',
						'btn-success',
						'btn-danger',
					];

					var viewIcon =
						mode == 'approval' && reqstatus == 1 ? 'fa-check' : 'fa-search';

					$(
						'<button class="btn ' +
							arrColor[reqstatus] +
							'" id="btnreqid' +
							reqid +
							'"><i class="fa ' +
							viewIcon +
							'"></i></button>',
					)
						.on('dxclick', function (evt) {
							evt.stopPropagation();

							popup.option({
								contentTemplate: () =>
									popupContentTemplate(reqid, mode, options),
							});
							popup.show();
						})
						.appendTo(container);
					if (
						(reqstatus == 1 || reqstatus == 2) &&
						isMine == 1 &&
						(isPendingOnMe == 0 || isPendingOnMe == null)
					) {
						$(
							'<button class="btn btn-danger" id="btnreqid' +
								reqid +
								'" style="margin-left: 3px;">Cancel</button>',
						)
							.on('dxclick', function (evt) {
								evt.stopPropagation();

								var result = confirm(
									'Are you sure you want to cancel this submission ?',
								);

								if (result) {
									sendRequest(
										apiurl + '/submissionrequest/' + reqid + '/' + modelclass,
										'POST',
										{
											requestStatus: 0,
											action: 'submission',
											approvalAction: 0,
										},
									).then(function (response) {
										if (response.status != 'error') {
											dataGrid.refresh();
										}
									});
								} else {
									alert('Cancelled.');
								}
							})
							.appendTo(container);
					}
				},
			},
			{
				caption: 'Code',
				dataField: 'code',
				width: 180,
				sortOrder: 'desc',
			},
			{
				dataField: 'user.fullname',
				caption: 'Creator Name',
				width: 180,
			},
			{
				dataField: 'requestStatus',
				encodeHtml: false,
				allowFiltering: false,
				allowHeaderFiltering: true,
				customizeText: function (e) {
					var arrText = [
						"<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
						"<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
						"<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
						"<span class='btn btn-success btn-xs btn-status'>Approved</span>",
						"<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
					];
					return arrText[e.value];
				},
			},
			{
				dataField: 'phase',
				encodeHtml: false,
				allowFiltering: false,
				allowHeaderFiltering: true,
				customizeText: function (e) {
					var arrText = [
						"<span class='btn btn-info btn-xs btn-status'>Draft</span>",
						"<span class='btn btn-info btn-xs btn-status'>Pengajuan Produk</span>",
						"<span class='btn btn-info btn-xs btn-status'>Pengajuan Sampel</span>",
						"<span class='btn btn-info btn-xs btn-status'>Perjanjian Perusahaan</span>",
						"<span class='btn btn-info btn-xs btn-status'>Pembayaran Listing</span>",
						"<span class='btn btn-info btn-xs btn-status'>PO Barang diProses</span>",
						"<span class='btn btn-info btn-xs btn-status'>PO Barang diTerima</span>",
						"<span class='btn btn-info btn-xs btn-status'>PO Barang diKirim</span>",
						"<span class='btn btn-info btn-xs btn-status'>Listing Selesai</span>",
					];
					return arrText[e.value];
				},
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
			runpopup();
		},
		onCellPrepared: function (e) {
			if (e.rowType == 'data') {
				if (e.data.isParent === 1) {
					e.cellElement.css('background', 'rgba(128, 128, 0,0.1)');
				}
			}
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
		onDataErrorOccurred: function (e) {
			// Menampilkan pesan kesalahan
			console.log('Terjadi kesalahan saat memuat data (0):', e.error.message);

			// Memuat ulang Page
			// location.reload();
		},
	})
	.dxDataGrid('instance');

$('#btnadd').on('click', function () {
	sendRequest(apiurl + '/' + modname, 'POST', { requestStatus: 0 }).then(
		function (response) {
			const reqid = response.data.id;
			const mode = 'add';
			const options = { data: { isMine: 1 } };
			popup.option({
				contentTemplate: () => popupContentTemplate(reqid, mode, options),
			});
			popup.show();
		},
	);
});

const accordionItems = [
	{
		ID: 1,
		Title: '<i class="far fa-newspaper"> Form Data </i>',
		visible: true,
	},
	{
		ID: 6,
		Title: '<i class="fas fa-list-ul"> Produk </i>',
		visible: true,
	},
	{
		ID: 5,
		Title: '<i class="fas fa-users"> Sampel </i>',
		visible: false,
	},
	{
		ID: 7,
		Title: '<i class="fas fa-users"> Perjanjian Perusahaan </i>',
		visible: false,
	},
	{
		ID: 8,
		Title: '<i class="fas fa-users"> Pembayaran </i>',
		visible: false,
	},
	{
		ID: 2,
		Title: '<i class="fas fa-file"> Supporting Document </i>',
		visible: true,
	},
	{
		ID: 3,
		Title: '<i class="fas fa-list-ul"> Approver List </i>',
		visible: true,
	},
	{
		ID: 4,
		Title: '<i class="fas fa-history"> History </i>',
		visible: true,
	},
];

const updateVisibleById = (itemId, visible) => {
	accordionItems.forEach((item) => {
		if (item.ID === itemId) {
			item.visible = visible;
		}
	});
};

const popupContentTemplate = function (reqid, mode, options) {
	var isMine = options.data.isMine;
	var isPendingOnMe = options.data.isPendingOnMe;

	popupid = reqid;

	const scrollView = $('<div />');

	if (
		(isMine == 1 || isPendingOnMe == 1) &&
		(mode == 'add' || mode == 'edit' || mode == 'approval')
	) {
		if (isPendingOnMe == 1 && mode == 'approval') {
			var approvalOptions =
				'<div class="row">' +
				'<div class="col-md-6">' +
				'<label for="remarks">Approval Action :</label>' +
				'<div class="form-check">' +
				'<input class="form-check-input" type="radio" name="approvalaction" id="rappraction1" value="3">' +
				'<label class="form-check-label" for="rappraction1">' +
				'Approved' +
				'</label>' +
				'</div>' +
				'<div class="form-check">' +
				'<input class="form-check-input" type="radio" name="approvalaction" id="rappraction2" value="2">' +
				'<label class="form-check-label" for="rappraction2">' +
				'Reworked' +
				'</label>' +
				'</div>' +
				'<div class="form-check mb-3">' +
				'<input class="form-check-input" type="radio" name="approvalaction" id="rappraction3" value="4">' +
				'<label class="form-check-label" for="rappraction3">' +
				'Rejected' +
				'</label>' +
				'</div>' +
				'</div>' +
				'<div class="col-md-6">' +
				'<div class="form-group">' +
				'<label for="remarks">Remarks :</label>' +
				'<textarea class="form-control" id="remarks" rows="3"></textarea>' +
				'</div>' +
				'</div>' +
				'</div><hr>';
		} else {
			var approvalOptions = '';
		}

		scrollView.append(
			'<div class="row">' +
				'<div class="col-lg-12">' +
				'<div class="card">' +
				'<div class="card-header">' +
				'<h5 class="card-title">Form Action</h5>' +
				'</div>' +
				'<div class="card-body" style="border-bottom-color: darkseagreen !important;border-left-color: darkseagreen;">' +
				approvalOptions +
				'<button id="btn-submit" type="button" onClick="btnreqsubmit(' +
				reqid +
				",'" +
				mode +
				'\')" class="btn btn-success waves-effect btn-label waves-light m-1"><i class="bx bx-check-double label-icon"></i> Submit Submission</button>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'</div>',
		);
	}

	// "<span class='btn btn-info btn-xs btn-status'>Draft</span>",
	// "<span class='btn btn-info btn-xs btn-status'>Pengajuan Produk</span>",
	// "<span class='btn btn-info btn-xs btn-status'>Pengajuan Sampel</span>",
	// "<span class='btn btn-info btn-xs btn-status'>Perjanjian Perusahaan</span>",
	// "<span class='btn btn-info btn-xs btn-status'>Pembayaran Listing</span>",
	// "<span class='btn btn-info btn-xs btn-status'>PO Barang diProses</span>",
	// "<span class='btn btn-info btn-xs btn-status'>PO Barang diTerima</span>",
	// "<span class='btn btn-info btn-xs btn-status'>PO Barang diKirim</span>",
	// "<span class='btn btn-info btn-xs btn-status'>Listing Selesai</span>",

	updateVisibleById(5, options.data.phase >= 2); // sampel
	updateVisibleById(7, options.data.phase >= 3); // perjanjian perusahaan
	updateVisibleById(8, options.data.phase >= 4); // pembayaran listing

	// if (options.data.phase == 2) {
	// 	updateVisibleById(5, true);
	// } else {
	// 	updateVisibleById(5, false);
	// }

	// if (options.data.phase == 3) {
	// 	updateVisibleById(7, true);
	// } else {
	// 	updateVisibleById(7, false);
	// }

	scrollView.append('<hr>'),
		scrollView.append(
			$('<div>').dxAccordion({
				dataSource: accordionItems,
				animationDuration: 600,
				selectedItems: [
					accordionItems[0],
					accordionItems[1],
					accordionItems[2],
					accordionItems[3],
					accordionItems[4],
					accordionItems[5],
					accordionItems[6],
					accordionItems[7],
				],
				collapsible: true,
				multiple: true,
				itemTitleTemplate: function (data) {
					return (
						'<small style="margin-bottom:10px !important ;">' +
						data.Title +
						'</small>'
					);
				},
				itemTemplate: function (data) {
					var container = $('<div>');
					if (data.ID == 1) {
						if (mode == 'add' || mode == 'edit') {
							$("<span style='color:red;font-size:11pt'>")
								.html(
									'Silahkan lengkapi <b><i style="color:black;font-weight:bold" class="far fa-newspaper"> Details Data </i></b> dan tekan tombol <span style="color:black;font-weight:bold"><i class="bx bx-check-double label-icon"></i> Submit Submission</span> untuk melakukan pengajuan',
								)
								.appendTo(container);
						}
						var formData = $("<div id='formdata'>")
							.dxDataGrid({
								dataSource: storedetail(modname, reqid),
								allowColumnReordering: true,
								allowColumnResizing: true,
								columnsAutoWidth: true,
								rowAlternationEnabled: true,
								wordWrapEnabled: true,
								showBorders: true,
								showColumnLines: true,
								filterRow: { visible: false },
								filterPanel: { visible: false },
								headerFilter: { visible: false },
								searchPanel: {
									visible: false,
									width: 240,
									placeholder: 'Search...',
								},
								sorting: {
									mode: 'none', // or "multiple" | "none"
								},
								editing: {
									useIcons: true,
									mode: 'batch',
									allowAdding: false,
									allowUpdating: false,
									// allowUpdating:
									// 	(isMine == 1 && mode == 'edit') || mode == 'add'
									// 		? true
									// 		: admin == 1 || developer
									// 		? true
									// 		: false,
									allowDeleting: false,
								},
								scrolling: {
									mode: 'virtual',
								},
								columns: [
									{
										caption: 'Code',
										dataField: 'code',
										allowFiltering: false,
										allowHeaderFiltering: false,
										editorOptions: {
											readOnly: true,
										},
									},
									{
										dataField: 'requestStatus',
										encodeHtml: false,
										allowFiltering: false,
										allowHeaderFiltering: true,
										customizeText: function (e) {
											var arrText = [
												"<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
												"<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
												"<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
												"<span class='btn btn-success btn-xs btn-status'>Approved</span>",
												"<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
											];
											return arrText[e.value];
										},
										editorOptions: {
											readOnly: true,
										},
									},
									{
										dataField: 'phase',
										encodeHtml: false,
										allowFiltering: false,
										allowHeaderFiltering: true,
										customizeText: function (e) {
											var arrText = [
												"<span class='btn btn-info btn-xs btn-status'>Draft</span>",
												"<span class='btn btn-info btn-xs btn-status'>Pengajuan Produk</span>",
												"<span class='btn btn-info btn-xs btn-status'>Pengajuan Sampel</span>",
												"<span class='btn btn-info btn-xs btn-status'>Perjanjian Perusahaan</span>",
												"<span class='btn btn-info btn-xs btn-status'>Pembayaran Listing</span>",
												"<span class='btn btn-info btn-xs btn-status'>PO Barang diProses</span>",
												"<span class='btn btn-info btn-xs btn-status'>PO Barang diTerima</span>",
												"<span class='btn btn-info btn-xs btn-status'>PO Barang diKirim</span>",
												"<span class='btn btn-info btn-xs btn-status'>Listing Selesai</span>",
											];
											return arrText[e.value];
										},
										editorOptions: {
											readOnly: true,
										},
									},
									{
										dataField: 'created_at',
										dataType: 'date',
										format: 'dd-MM-yyyy',
										editorOptions: {
											readOnly: true,
										},
									},
								],
								export: {
									enabled: false,
									fileName: modname,
									excelFilterEnabled: true,
									allowExportSelectedData: true,
								},
								onInitialized: function (e) {
									dataGrid1 = e.component;
								},
								onContentReady: function (e) {
									moveEditColumnToLeft(e.component);
								},
								onInitNewRow: function (e) {},
								onToolbarPreparing: function (e) {
									e.toolbarOptions.items.unshift({
										location: 'after',
										widget: 'dxButton',
										options: {
											hint: 'Refresh Data',
											icon: 'refresh',
											onClick: function () {
												dataGrid1.refresh();
												dataGriddetail1.refresh();
												dataGriddetail2.refresh();
												dataGriddetail3.refresh();
											},
										},
									});
								},
								onEditorPreparing: function (e) {
									if (
										e.dataField == 'nameSystem' &&
										e.parentType == 'dataRow'
									) {
										e.editorName = 'dxDropDownBox';
										e.editorOptions.dropDownOptions = {
											height: 500,
											width: 600,
										};
										e.editorOptions.contentTemplate = function (
											args,
											container,
										) {
											const $dataGrid = $('<div>').dxTreeView({
												dataSource: args.component.option('dataSource'),
												dataStructure: 'plain',
												keyExpr: 'id',
												parentIdExpr: 'parentID',
												selectionMode: 'single',
												displayExpr: 'nameSystem',
												selectByClick: true,
												selectNodesRecursive: false,
												onItemSelectionChanged(selectedItems) {
													const selectedKeys =
														selectedItems.component.getSelectedNodeKeys();
													const hasSelection = selectedKeys.length;

													args.component.option(
														'value',
														hasSelection ? selectedKeys[0] : null,
													);
													if (hasSelection !== 0) {
														args.component.close();
													}
												},
											});

											var dataGrid = $dataGrid.dxTreeView('instance');

											args.component.on('valueChanged', function (e) {
												var value = e.value;
												dataGrid.selectItem(value);
												e.component.close();
											});

											return $dataGrid;
										};
									}
								},
								onCellPrepared: function (e) {
									if (e.column.index == 0 && e.rowType == 'data') {
										if (e.data.code === null) {
											$('#formdata').dxDataGrid(
												'columnOption',
												'code',
												'visible',
												false,
											);
										} else {
											$('#formdata').dxDataGrid(
												'columnOption',
												'code',
												'visible',
												true,
											);
										}
									}
									if (e.column.index == 0 && e.rowType == 'data') {
										if (e.data.requestStatus == 3 || admin == 1) {
											$('#formdata').dxDataGrid(
												'columnOption',
												'ticketStatus',
												'visible',
												true,
											);
										}
									}
									if (
										e.rowType == 'data' &&
										e.column.index > 0 &&
										e.column.index < 4
									) {
										if (
											e.value === '' ||
											e.value === null ||
											e.value === undefined ||
											/^\s*$/.test(e.value)
										) {
											e.cellElement.css({
												backgroundColor: '#ffe6e6',
												border: '0.5px solid #f56e6e',
											});
										}
									}
								},
								onDataErrorOccurred: function (e) {
									// Menampilkan pesan kesalahan
									console.log(
										'Terjadi kesalahan saat memuat data (1):',
										e.error.message,
									);

									// Memuat ulang DataGrid
									dataGrid1.refresh();
								},
							})
							.appendTo(container);
						return container;
					} else if (data.ID == 6) {
						return (formData = $("<div id='formdetail'>").dxDataGrid({
							dataSource: storewithmodule('produkdetail', modelclass, reqid),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							showColumnLines: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							sorting: {
								mode: 'none', // or "multiple" | "none"
							},
							editing: {
								useIcons: true,
								mode: 'cell',
								allowAdding:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
								allowUpdating:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
								allowDeleting:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
							},
							scrolling: {
								rowRenderingMode: 'virtual',
							},
							paging: {
								pageSize: 15,
							},
							pager: {
								visible: true,
								allowedPageSizes: [5, 15, 'all'],
								showPageSizeSelector: true,
								showInfo: true,
								showNavigationButtons: true,
							},
							columns: [
								{
									dataField: 'nama_produk',
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'deskripsi_produk',
									editorType: 'dxTextArea',
									editorOptions: {
										height: 50,
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'harga_beli',
									dataType: 'number',
									format: 'fixedPoint',
									editorOptions: {
										format: 'fixedPoint',
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'harga_jual_pasaran',
									dataType: 'number',
									format: 'fixedPoint',
									editorOptions: {
										format: 'fixedPoint',
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'isi_perkarton',
									dataType: 'number',
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'minimal_order',
									dataType: 'number',
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'barkode',
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'status_produk',
									lookup: {
										dataSource: ['Waiting', 'Approved', 'Rejected'],
									},
									editorOptions: {
										readOnly: admin == 1 ? false : true,
									},
									visible: options.data.requestStatus == 0 ? false : true,
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGriddetail1 = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onToolbarPreparing: function (e) {
								// e.toolbarOptions.items.unshift({
								// 	location: 'after',
								// 	widget: 'dxButton',
								// 	options: {
								// 		hint: 'Refresh Data',
								// 		icon: 'refresh',
								// 		onClick: function () {
								// 			dataGriddetail.refresh();
								// 		},
								// 	},
								// });
							},
							onEditorPreparing: function (e) {},
							onCellPrepared: function (e) {
								// if (e.rowType == 'data' && e.column.index == 5) {
								// 	if (
								// 		e.value === '' ||
								// 		e.value === null ||
								// 		e.value === undefined ||
								// 		/^\s*$/.test(e.value)
								// 	) {
								// 		e.cellElement.css({
								// 			backgroundColor: '#ffe6e6',
								// 			border: '0.5px solid #f56e6e',
								// 		});
								// 	}
								// }
							},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (6):',
									e.error.message,
								);

								// Memuat ulang DataGrid
								// dataGriddetail.refresh();
							},
						}));
					} else if (data.ID == 5) {
						return (formData = $("<div id='formdetailsampel'>").dxDataGrid({
							dataSource: storewithmodule('sampeldetail', modelclass, reqid),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							showColumnLines: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							sorting: {
								mode: 'none', // or "multiple" | "none"
							},
							editing: {
								useIcons: true,
								mode: 'cell',
								allowAdding:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
								allowUpdating:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
								allowDeleting:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
							},
							scrolling: {
								rowRenderingMode: 'virtual',
							},
							paging: {
								pageSize: 15,
							},
							pager: {
								visible: true,
								allowedPageSizes: [5, 15, 'all'],
								showPageSizeSelector: true,
								showInfo: true,
								showNavigationButtons: true,
							},
							columns: [
								{
									dataField: 'product_id',
									caption: 'Nama Produk',
									lookup: {
										dataSource: listOption(
											'/list-produk/' + reqid,
											'id',
											'nama_produk',
										),
										valueExpr: 'id',
										displayExpr: 'nama_produk',
									},
								},
								{
									dataField: 'quantity',
									dataType: 'number',
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'status_sampel',
									lookup: {
										dataSource: ['Waiting', 'Approved', 'Rejected'],
									},
									editorOptions: {
										readOnly: admin == 1 ? false : true,
									},
									visible: options.data.requestStatus == 0 ? false : true,
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGriddetail2 = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onToolbarPreparing: function (e) {},
							onEditorPreparing: function (e) {},
							onCellPrepared: function (e) {},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (6):',
									e.error.message,
								);
							},
						}));
					} else if (data.ID == 7) {
						return (formData = $("<div id='formdetailperjanjian'>").dxDataGrid({
							dataSource: storewithmodule(
								'perjanjiandetail',
								modelclass,
								reqid,
							),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							showColumnLines: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							sorting: {
								mode: 'none', // or "multiple" | "none"
							},
							editing: {
								useIcons: true,
								mode: 'cell',
								allowAdding: admin == 1 ? true : false,
								allowUpdating:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
								allowDeleting: admin == 1 ? true : false,
							},
							scrolling: {
								rowRenderingMode: 'virtual',
							},
							paging: {
								pageSize: 15,
							},
							pager: {
								visible: true,
								allowedPageSizes: [5, 15, 'all'],
								showPageSizeSelector: true,
								showInfo: true,
								showNavigationButtons: true,
							},
							columns: [
								{
									dataField: 'question_1',
									caption: 'Produk bisa retur rusak atau ED',
									lookup: {
										dataSource: ['Yes', 'No'],
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'question_2',
									caption: 'Produk bisa tukar guling',
									lookup: {
										dataSource: ['Yes', 'No'],
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'question_3',
									caption: 'Promo yang di tawarkan',
									dataType: 'string',
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'question_4',
									caption: 'Support yang di tawarkan',
									dataType: 'string',
									validationRules: [{ type: 'required' }],
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGriddetail3 = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onToolbarPreparing: function (e) {},
							onEditorPreparing: function (e) {},
							onCellPrepared: function (e) {},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (7):',
									e.error.message,
								);
							},
						}));
					} else if (data.ID == 8) {
						return (formData = $("<div id='formdetailpembayaran'>").dxDataGrid({
							dataSource: storewithmodule(
								'pembayarandetail',
								modelclass,
								reqid,
							),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							showColumnLines: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							sorting: {
								mode: 'none', // or "multiple" | "none"
							},
							editing: {
								useIcons: true,
								mode: 'cell',
								allowAdding: admin == 1 ? true : false,
								allowUpdating:
									(isMine == 1 && mode == 'edit') || mode == 'add'
										? true
										: admin == 1
										? true
										: false,
								allowDeleting: admin == 1 ? true : false,
							},
							scrolling: {
								rowRenderingMode: 'virtual',
							},
							paging: {
								pageSize: 15,
							},
							pager: {
								visible: true,
								allowedPageSizes: [5, 15, 'all'],
								showPageSizeSelector: true,
								showInfo: true,
								showNavigationButtons: true,
							},
							columns: [
								{
									dataField: 'total_product',
									caption: 'Jumlah Produk',
									editorOptions: {
										readOnly: true,
									},
								},
								{
									dataField: 'payment_type',
									caption: 'Tipe Pembayaran',
									lookup: {
										dataSource: ['Potong Tagihan', 'Transfer'],
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'pph_type',
									caption: 'PPH',
									lookup: {
										dataSource: ['PPH23', 'Non PPH23'],
									},
									validationRules: [{ type: 'required' }],
								},
								{
									dataField: 'amount',
									dataType: 'number',
									editorOptions: {
										readOnly: true,
									},
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGriddetail3 = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onToolbarPreparing: function (e) {
								e.toolbarOptions.items.push({
									location: 'before',
									widget: 'dxButton',
									options: {
										text: 'Download Invoice',
										icon: 'download',
										onClick: function () {
											downloadInvoice();
										},
										elementAttr: {
											class: 'custom-download-button', // Tambahkan kelas CSS
										},
									},
								});
							},
							onEditorPreparing: function (e) {},
							onCellPrepared: function (e) {},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (7):',
									e.error.message,
								);
							},
						}));

						function downloadInvoice() {
							// Gantilah URL berikut dengan endpoint yang sesuai untuk mengunduh invoice
							const url = 'api/download-invoice/' + reqid;
							window.open(url, '_blank');
						}
					} else if (data.ID == 2) {
						var supporting = $("<div id='formattachment'>").dxDataGrid({
							dataSource: storewithmodule(
								'attachmentrequest',
								modelclass,
								reqid,
							),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							editing: {
								useIcons: true,
								mode: 'popup',
								allowAdding: (
									isMine == 1 && mode == 'view'
										? true
										: (isMine == 1 && mode == 'edit') || mode == 'add'
								)
									? true
									: admin == 1
									? true
									: false,
								allowUpdating: false,
								allowDeleting: (
									isMine == 1 && mode == 'view'
										? true
										: (isMine == 1 && mode == 'edit') || mode == 'add'
								)
									? true
									: admin == 1
									? true
									: false,
							},
							paging: { enabled: true, pageSize: 10 },
							columns: [
								{
									caption: 'Lampiran',
									dataField: 'path',
									allowFiltering: false,
									allowSorting: false,
									cellTemplate: cellTemplate,
									editCellTemplate: editCellTemplate,
									validationRules: [{ type: 'required' }],
								},
								{
									caption: 'Remarks',
									dataField: 'remarks',
									lookup: {
										dataSource: ['Produk', 'Bukti Pembayaran', 'PO'],
									},
									validationRules: [{ type: 'required' }],
								},
								{
									caption: 'Deskripsi',
									dataField: 'description',
									validationRules: [{ type: 'required' }],
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGridAttachment = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onInitNewRow: function (e) {},
							onToolbarPreparing: function (e) {
								e.toolbarOptions.items.unshift({
									location: 'after',
									widget: 'dxButton',
									options: {
										hint: 'Refresh Data',
										icon: 'refresh',
										onClick: function () {
											dataGridAttachment.refresh();
										},
									},
								});
							},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (2):',
									e.error.message,
								);

								// Memuat ulang DataGrid
								dataGridAttachment.refresh();
							},
						});

						return supporting;
					} else if (data.ID == 3) {
						return $("<div id='formapproverlist'>").dxDataGrid({
							dataSource: storewithmodule(
								'approverlistrequest',
								modelclass,
								reqid,
							),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							editing: {
								useIcons: true,
								mode: 'cell',
								allowAdding: admin == 1 ? true : false,
								allowUpdating: admin == 1 ? true : false,
								allowDeleting: admin == 1 ? true : false,
							},
							scrolling: {
								mode: 'virtual',
							},
							columns: [
								{
									caption: 'Fullname',
									dataField: 'approver_id',
									lookup: {
										dataSource: listOption(
											'/list-approver/' + modelclass,
											'id',
											'fullname',
										),
										valueExpr: 'id',
										displayExpr: 'fullname',
									},
									validationRules: [
										{
											type: 'required',
										},
									],
								},
								{
									dataField: 'ApprovalType',
									editorOptions: {
										readOnly: true,
									},
								},
								// {
								// 	dataField: 'approvalDate',
								// 	dataType: 'datetime',
								// 	format: 'dd-MM-yyyy hh:mm:ss',
								// },
								{
									caption: 'Approval Status',
									dataField: 'approvalAction',
									encodeHtml: false,
									allowFiltering: false,
									allowHeaderFiltering: true,
									customizeText: function (e) {
										var arrText = [
											"<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
											"<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
											"<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
											"<span class='btn btn-success btn-xs btn-status'>Approved</span>",
											"<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
										];
										return arrText[e.value];
									},
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGridApproverList = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onInitNewRow: function (e) {},
							onEditorPreparing: function (e) {
								if (e.dataField == 'approver_id' && e.parentType == 'dataRow') {
									e.editorName = 'dxDropDownBox';
									e.editorOptions.dropDownOptions = {
										height: 500,
										width: 600,
									};
									e.editorOptions.contentTemplate = function (args, container) {
										var value = args.component.option('value'),
											$dataGrid = $('<div>').dxDataGrid({
												width: '100%',
												dataSource: args.component.option('dataSource'),
												keyExpr: 'id',
												columns: ['fullname', 'ApprovalType'],
												hoverStateEnabled: true,
												paging: { enabled: true, pageSize: 10 },
												filterRow: { visible: true },
												height: '90%',
												showRowLines: true,
												showBorders: true,
												selection: { mode: 'single' },
												selectedRowKeys: [value],
												focusedRowEnabled: true,
												focusedRowKey: args.component.option('value'),
												searchPanel: {
													visible: true,
													width: 265,
													placeholder: 'Search...',
												},
												onSelectionChanged: function (selectedItems) {
													const keys = selectedItems.selectedRowKeys;
													const hasSelection = keys.length;
													args.component.option(
														'value',
														hasSelection ? keys[0] : null,
													);
													if (hasSelection !== 0) {
														args.component.close();
													}
												},
											});

										var dataGrid = $dataGrid.dxDataGrid('instance');

										args.component.on('valueChanged', function (args) {
											var value = args.value;

											dataGrid.selectRows(value, false);
										});
										container.append($dataGrid);
										$('<div>')
											.dxButton({
												text: 'Close',

												onClick: function (ev) {
													args.component.close();
												},
											})
											.css({ float: 'right', marginTop: '10px' })
											.appendTo(container);
										return container;
									};
								}
							},
							onToolbarPreparing: function (e) {
								e.toolbarOptions.items.unshift({
									location: 'after',
									widget: 'dxButton',
									options: {
										hint: 'Refresh Data',
										icon: 'refresh',
										onClick: function () {
											dataGridApproverList.refresh();
										},
									},
								});
							},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (3):',
									e.error.message,
								);

								// Memuat ulang DataGrid
								dataGridApproverList.refresh();
							},
						});
					} else if (data.ID == 4) {
						return $("<div id='formhistorylist'>").dxDataGrid({
							dataSource: storewithmodule(
								'approverlisthistory',
								modelclass,
								reqid,
							),
							allowColumnReordering: true,
							allowColumnResizing: true,
							columnsAutoWidth: true,
							rowAlternationEnabled: true,
							wordWrapEnabled: true,
							showBorders: true,
							filterRow: { visible: false },
							filterPanel: { visible: false },
							headerFilter: { visible: false },
							searchPanel: {
								visible: true,
								width: 240,
								placeholder: 'Search...',
							},
							editing: {
								useIcons: true,
								mode: 'cell',
								allowAdding: false,
								allowUpdating: false,
								allowDeleting: false,
							},
							paging: { enabled: true, pageSize: 10 },
							columns: [
								{
									dataField: 'fullname',
								},
								{
									caption: 'Type',
									dataField: 'approvalType',
								},
								{
									caption: 'Date',
									dataField: 'approvalDate',
									dataType: 'datetime',
									format: 'dd-MM-yyyy hh:mm:ss',
								},
								{
									caption: 'Action',
									dataField: 'approvalAction',
									encodeHtml: false,
									allowFiltering: false,
									allowHeaderFiltering: true,
									customizeText: function (e) {
										var arrText = [
											"<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
											"<span class='btn btn-primary btn-xs btn-status'>Submitted</span>",
											"<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
											"<span class='btn btn-success btn-xs btn-status'>Approved</span>",
											"<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
											"<span class='btn btn-secondary btn-xs btn-status'>Cancelled</span>",
										];
										return arrText[e.value];
									},
								},
								{
									dataField: 'remarks',
								},
							],
							export: {
								enabled: false,
								fileName: modname,
								excelFilterEnabled: true,
								allowExportSelectedData: true,
							},
							onInitialized: function (e) {
								dataGridApproverHistory = e.component;
							},
							onContentReady: function (e) {
								moveEditColumnToLeft(e.component);
							},
							onInitNewRow: function (e) {},
							onToolbarPreparing: function (e) {
								e.toolbarOptions.items.unshift({
									location: 'after',
									widget: 'dxButton',
									options: {
										hint: 'Refresh Data',
										icon: 'refresh',
										onClick: function () {
											dataGridApproverHistory.refresh();
										},
									},
								});
							},
							onDataErrorOccurred: function (e) {
								// Menampilkan pesan kesalahan
								console.log(
									'Terjadi kesalahan saat memuat data (4):',
									e.error.message,
								);

								// Memuat ulang DataGrid
								dataGridApproverHistory.refresh();
							},
						});
					}
				},
			}),
		);

	scrollView.dxScrollView({
		width: '100%',
		height: '100%',
	});

	return scrollView;
};

function btnreqsubmit(reqid, mode) {
	var btnSubmit = $('#btn-submit');
	btnSubmit.prop('disabled', true);
	var actionForm = mode == 'approval' ? 'approval' : 'submission';

	if (mode == 'approval') {
		var valapprovalAction = $('input[name="approvalaction"]:checked').val(); // mengambil nilai dari radio button
		var valremarks = $('#remarks').val(); // mengambil nilai dari text area
		if (!valapprovalAction) {
			alert('Please select approval action.');
			btnSubmit.prop('disabled', false);
			return false;
		} else if (!valremarks) {
			alert('Please enter remarks.');
			btnSubmit.prop('disabled', false);
			return false;
		}
	}

	var valApprovalType =
		valapprovalAction == 3
			? 'Approved'
			: valapprovalAction == 2
			? 'Reworked'
			: valapprovalAction == 4
			? 'Rejected'
			: '';

	confirmAndSendSubmission(
		reqid,
		modelclass,
		actionForm,
		valapprovalAction,
		valApprovalType,
		valremarks,
	);

	// var result = confirm('Are you sure you want to send this submission ?');
	// if (result) {
	// 	showLoadingScreen();
	// 	sendRequest(
	// 		apiurl + '/submissionrequest/' + reqid + '/' + modelclass,
	// 		'POST',
	// 		{
	// 			requestStatus: 1,
	// 			action: actionForm,
	// 			approvalAction:
	// 				valapprovalAction == null ? 1 : parseInt(valapprovalAction),
	// 			approvalType: valApprovalType,
	// 			remarks: valremarks,
	// 		},
	// 	).then(function (response) {
	// 		if (response.status == 'error') {
	// 			btnSubmit.prop('disabled', false);
	// 			hideLoadingScreen();
	// 		} else {
	// 			popup.hide();
	// 			hideLoadingScreen();
	// 		}
	// 	});
	// } else {
	// 	btnSubmit.prop('disabled', false);
	// 	alert('Cancelled.');
	// 	hideLoadingScreen();
	// }
}

function runpopup() {
	popup = $('#popup')
		.dxPopup({
			contentTemplate: popupContentTemplate,
			container: '.content',
			showTitle: true,
			title: 'Submission Detail',
			visible: false,
			dragEnabled: false,
			hideOnOutsideClick: false,
			showCloseButton: true,
			fullScreen: false,
			onShowing: function (e) {},
			onShown: function (e) {},
			onHidden: function (e) {
				dataGrid.refresh();
			},
			toolbarItems: [
				{
					widget: 'dxButton',
					toolbar: 'bottom', // Set the button to the bottom toolbar
					location: 'after',
					options: {
						text: 'Fullscreen',
						onClick: function () {
							if (popup.option('fullScreen')) {
								popup.option('fullScreen', false);
								this.option('text', 'Enable Fullscreen');
							} else {
								popup.option('fullScreen', true);
								this.option('text', 'Disable Fullscreen');
							}
						},
					},
				},
				{
					widget: 'dxButton',
					toolbar: 'bottom',
					location: 'after',
					options: {
						text: 'Close',
						onClick() {
							popup.hide();
						},
					},
				},
			],
		})
		.dxPopup('instance');
}

function cellTemplate(container, options) {
	container.append(
		'<a href="upload/' +
			options.value +
			'" target="_blank"><img src="assets/images/showfile.png" height="50" width="70"></a>',
	);
}

function editCellTemplate(cellElement, cellInfo) {
	let buttonElement = document.createElement('div');
	buttonElement.classList.add('retryButton');
	let retryButton = $(buttonElement)
		.dxButton({
			text: 'Retry',
			visible: false,
			onClick: function () {
				// The retry UI/API is not implemented. Use a private API as shown at T611719.
				for (var i = 0; i < fileUploader._files.length; i++) {
					delete fileUploader._files[i].uploadStarted;
				}
				fileUploader.upload();
			},
		})
		.dxButton('instance');

	$path = '';
	$adafile = '';
	let fileUploaderElement = document.createElement('div');
	let fileUploader = $(fileUploaderElement)
		.dxFileUploader({
			multiple: false,
			accept: '.pptx,.ppt,.docx,.pdf,.xlsx,.csv,.png,.jpg,.jpeg,.zip',
			uploadMode: 'instantly',
			name: 'myFile',
			uploadUrl: apiurl + '/upload-berkas/' + modname,
			onValueChanged: function (e) {
				let reader = new FileReader();
				reader.onload = function (args) {
					imageElement.setAttribute('src', args.target.result);
				};
				reader.readAsDataURL(e.value[0]); // convert to base64 string
			},
			onUploaded: function (e) {
				$path = e.request.response;
				$adafile = false;
				cellInfo.setValue(e.request.responseText);
				retryButton.option('visible', false);
			},
			onUploadError: function (e) {
				$path = '';
				DevExpress.ui.notify(e.request.response, 'error');
			},
		})
		.dxFileUploader('instance');

	// let imageElement = document.createElement("img");
	//     imageElement.classList.add("uploadedImage");
	//     imageElement.setAttribute('src', "upload/" +cellInfo.value);
	//     imageElement.setAttribute('height', "50");

	//     cellElement.append(imageElement);
	cellElement.append(fileUploaderElement);
	cellElement.append(buttonElement);
}
