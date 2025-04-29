var modname = 'datapelanggan';
var modelclass = 'Datapelanggan';
var popupmode;

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
    // columnsAutoWidth: true,
    columnHidingEnabled: true,
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
        mode: "cell",
        allowAdding: false,
        allowUpdating: (admin == 1) ? true : false,
        allowDeleting: false,
    },
    scrolling: {
        mode: "virtual"
    },
    columns: [
        {
            caption: 'Action',
            width: 140,
            cellTemplate: function(container, options) {

                var reqid = options.data.id;
    
                $('<button class="btn btn-primary" id="btnreqid'+reqid+'"><i class="fa fa-search"></i></button>').on('dxclick', function(evt) {
                    evt.stopPropagation();
                
                            popup.option({
                                contentTemplate: () => popupContentTemplate(reqid,options),
                            });
                            popup.show();

                }).appendTo(container);
            
            }
        },
        {
			caption: "ODP Name",
			dataField: "odps.odpName",
            editorOptions: {
                readOnly: true
            }
        },
        {
			caption: "ODP Port",
            width: 80,
			dataField: "odps.portNumber",
            editorOptions: {
                readOnly: true
            }
        },
        {
			caption: "ODP Status",
			dataField: "odps.status",
            editorOptions: {
                readOnly: true
            }
        },
        {
            dataField: 'noInternet',
            dataType: "number",
        },
        {
            dataField: 'namaPelanggan',
        },
        {
            dataField: 'kontakServis',
            dataType: "number",
        },
        {
            dataField: 'alamat',
        },
        {
            dataField: 'kordinat',
            dataType: "string",
            cellTemplate: function(container, options) {
                var coordinates = options.value ? options.value.split(",") : [];
                var latitude = coordinates.length > 0 ? coordinates[0].trim() : "";
                var longitude = coordinates.length > 1 ? coordinates[1].trim() : "";
                
                var text = latitude && longitude ? "Lihat" : "";
                
                var link = $("<a>")
                .text(text)
                .attr("href", latitude && longitude ? "https://www.google.com/maps/search/?api=1&query=" + latitude + "," + longitude : "")
                .attr("target", "_blank");
                
                $(container).append(link);
              }
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
        runpopup();
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

// $('#btnadd').on('click',function(){
//     sendRequest(apiurl + "/"+modname, "POST", {requestStatus:0}).then(function(response){
//         const reqid = response.data.id;
//         popup.option({
//             contentTemplate: () => popupContentTemplate(reqid,options),
//         });
//         popup.show();
//     });
// })

const accordionItems = [
    {
        ID: 2,
        Title: '<i class="fas fa-file"> Supporting Document </i>',
        visible: true
    },
];

const updateVisibleById = (itemId, visible) => {
    accordionItems.forEach(item => {
      if (item.ID === itemId) {
        item.visible = visible;
      }
    });
  };

const popupContentTemplate = function (reqid,options) {


    popupid = reqid;

    const scrollView = $('<div />');

    scrollView.append("<hr>"),

    scrollView.append(

        $("<div>").dxAccordion({
            dataSource: accordionItems,
            animationDuration: 600,
            selectedItems: [accordionItems[0],accordionItems[1],accordionItems[2],accordionItems[3],accordionItems[4],accordionItems[5],accordionItems[6],accordionItems[7]],
            collapsible: true,
            multiple: true,
            itemTitleTemplate: function (data) {
                return '<small style="margin-bottom:10px !important ;">'+data.Title+'</small>'
            },
            itemTemplate: function (data) {
                if(data.ID == 2) {
                    var supporting = $("<div id='formattachment'>").dxDataGrid({    
                        dataSource: storewithmodule('attachmentrequest',modelclass,reqid),
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
                            useIcons:true,
                            mode: "popup",
                            allowAdding: true,
                            allowUpdating: true,
                            allowDeleting: true,
                        },
                        paging: { enabled: true, pageSize: 10 },
                        columns: [
                            { 
                                caption: 'File',
                                dataField: "path",
                                allowFiltering: false,
                                allowSorting: false,
                                cellTemplate: cellTemplate,
                                editCellTemplate: editCellTemplate,
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "remarks"
                            },
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGridAttachment = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGridAttachment.refresh();
                                    }
                                }
                            })
                        },
                    })

                    return supporting;
                }
            }
        })
    
    );

    scrollView.dxScrollView({
        width: '100%',
        height: '100%',
    })

    return scrollView;

};

function runpopup() {
    popup = $('#popup').dxPopup({
        contentTemplate: popupContentTemplate,
        container: '.content',
        showTitle: true,
        title: 'Detail Data Pelanggan',
        visible: false,
        dragEnabled: false,
        hideOnOutsideClick: false,
        showCloseButton: true,
        fullScreen : false,
        onShowing: function(e) {
        },
        onShown: function(e) {
        },
        onHidden: function(e) {
            dataGrid.refresh();
        },
        toolbarItems: [
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
        }]

    }).dxPopup('instance');
}


function cellTemplate(container, options) {
    container.append('<a href="/upload/'+options.value+'" target="_blank"><img src="/upload/'+options.value+'" height="50" width="70"></a>');
}

function editCellTemplate(cellElement, cellInfo) {
    let buttonElement = document.createElement("div");
    buttonElement.classList.add("retryButton");
    let retryButton = $(buttonElement).dxButton({
      text: "Retry",
      visible: false,
      onClick: function() {
        // The retry UI/API is not implemented. Use a private API as shown at T611719.
        for (var i = 0; i < fileUploader._files.length; i++) {
          delete fileUploader._files[i].uploadStarted;
        }
        fileUploader.upload();
      }
    }).dxButton("instance");
  
    $path = "";
    $adafile = "";
    let fileUploaderElement = document.createElement("div");
    let fileUploader = $(fileUploaderElement).dxFileUploader({
      multiple: false,
      accept: ".png,.jpg,.jpeg",
    //   accept: ".pptx,.ppt,.docx,.pdf,.xlsx,.csv,.png,.jpg,.jpeg",
      uploadMode: "instantly",
      name: "myFile",
      uploadUrl: apiurl + "/upload-berkas/"+modname,
      onValueChanged: function(e) {
        let reader = new FileReader();
        // reader.onload = function(args) {
        //   imageElement.setAttribute('src', args.target.result);
        // }
        reader.readAsDataURL(e.value[0]); // convert to base64 string
      },
      onUploaded: function(e){
          $path = e.request.response;
          $adafile = false;
          cellInfo.setValue(e.request.responseText);
          retryButton.option("visible", false);
      },
      onUploadError: function(e){
          $path = "";
          DevExpress.ui.notify(e.request.response,"error");
      }
    }).dxFileUploader("instance");
  
    let imageElement = document.createElement("img");
        imageElement.classList.add("uploadedImage");
        imageElement.setAttribute('src', "upload/" +cellInfo.value);
        imageElement.setAttribute('height', "50");
        
        cellElement.append(imageElement);
        cellElement.append(fileUploaderElement);
        cellElement.append(buttonElement);
  
  }