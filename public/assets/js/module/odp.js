var modname = 'odp';

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}

var dataGrid = $("#gridContainer").dxDataGrid({    
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
        useIcons:true,
        mode: "batch",
        allowAdding: true,
        allowUpdating: true,
        allowDeleting: true,
    },
    scrolling: {
        mode: "virtual"
    },
    columns: [ 
        { 
			dataField: "odpName",
            sortOrder: "asc",
            lookup: {
                dataSource: ['ODP-BRU-FAH-001','ODP-BRU-FAH-002','ODP-BRU-FAH-003']
            },
            allowSorting: false,
            validationRules: [{ type: "required" }],
        },
        { 
			dataField: "portNumber",
            sortOrder: "asc",
			dataType: "number",
            lookup: {
                dataSource: [1,2,3,4,5,6,7,8]
            },
            allowSorting: false,
            validationRules: [{ type: "required" }],
        },
        { 
            dataField: "status",
            lookup: {
                dataSource: ['Available','Existing','Broken']
            },
            allowSorting: false,
            validationRules: [{ type: "required" }],
        },
    ],
    export: {
        enabled: true,
        fileName: modname,
        excelFilterEnabled: true,
        allowExportSelectedData: true
    },
    onContentReady: function(e){
        moveEditColumnToLeft(e.component);
    },
    onToolbarPreparing: function(e) {
        dataGrid = e.component;

        e.toolbarOptions.items.unshift({						
            location: "after",
            widget: "dxButton",
            options: {
                hint: "Refresh Data",
                icon: "refresh",
                onClick: function() {
                    dataGrid.refresh();
                }
            }
        })
    },
}).dxDataGrid("instance");